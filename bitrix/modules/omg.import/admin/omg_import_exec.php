<?
use \Bitrix\Main\Application;

require_once( $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php' );
require_once( $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php' );

$module_id = 'omg.import';
\Bitrix\Main\Loader::includeModule( $module_id );

IncludeModuleLangFile( __FILE__ );

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

$reqID = intval( $request['ID'] );

$arImport = array();
if( $reqID > 0 ){
	$arImport = \OmgImportTable::getList( array( 'filter' => array( 'ID' => $reqID ) ) )->fetch();
}

if( !$arImport ){
	CAdminMessage::ShowMessage( array( 'MESSAGE' => GetMessage( 'ERROR_NOT_FOUND_IMPORT_TITLE' ), 'DETAILS' => GetMessage( 'ERROR_NOT_FOUND_IMPORT' ), 'TYPE' => 'ERROR' ) );
}else{
	\CJSCore::Init( array( 'jquery' ) );
	if( $_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['import'] == 'Y' && check_bitrix_sessid() ){
		$GLOBALS['APPLICATION']->RestartBuffer();
		
		if( $_POST && is_array( $_POST ) ){
			$NS = $_POST;
		}else{
			$NS = array(
				'ID' => $_REQUEST['ID'],
				'time_limit' => $arImport['STEP']
			);
		}
		
		$import = new \omgImport( $NS );
		if( $import->setReader( $arImport['FILE_TYPE'], $arImport['FILE'] ) && $import->setWriter( $arImport['TYPE'] ) ){
			$import->reader->setImport( $arImport );
			$import->writer->setImport( $arImport );
			$import->exec();
		}
		
		$arStatus = $import->getStatus();
		$arInfo = $import->getInfo();
		$arErrors = $import->getErrors();
		$arWarnings = $import->getWarnings();
		$localNS = $import->getNS();
		
		if( $arInfo ){
			$info = implode( '<br/>', $arInfo );
			if( SITE_CHARSET != 'UTF-8' ){
				$info = iconv( SITE_CHARSET, 'UTF-8', $info );
			}
			
			$NS = $import->getNS();
			
			echo json_encode( array(
				'ok' => true,
				'info' => $info,
				'NS' => $NS,
				'stop' => true
			) );
		}elseif( $arStatus ){
			$status = implode( '<br/>', $arStatus );
			if( SITE_CHARSET != 'UTF-8' ){
				$status = iconv( SITE_CHARSET, 'UTF-8', $status );
			}
			
			$warning = implode( '<br/>', $arWarnings );
			if( SITE_CHARSET != 'UTF-8' ){
				$warning = iconv( SITE_CHARSET, 'UTF-8', $warning );
			}
			
			$NS = $import->getNS();
			
			echo json_encode( array(
				'ok' => true,
				'status' => $status,
				'info' => '',
				'warning' => $warning,
				'NS' => $NS,
				'stop' => $NS['TYPE'] == 'stop'
			) );
		}elseif( $arErrors ){
			$error = implode( '<br/>', $arErrors );
			if( SITE_CHARSET != 'UTF-8' ){
				$error = iconv( SITE_CHARSET, 'UTF-8', $error );
			}
			
			echo json_encode( array(
				'error' => $error
			) );
		}else{
			echo json_encode( array(
				'error' => 'unknown error'
			) );
		}
		
		die();
	}
	
	$APPLICATION->SetTitle( $reqID > 0 ? GetMessage( 'IMPORT_EXEC', array( '#NAME#' => $arImport['NAME'] ) ) : GetMessage( 'IMPORT_NOT_FOUND' ) );
	require( $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php' );
	
	if( strlen( $arImport['FILE'] ) <= 0 ){
		$arErrors[] = GetMessage( 'ERROR_FILE_EMPTY' );
	}elseif( !preg_match( '/^(http|https|ftp|ftps):\\/\\//', $arImport['FILE'] ) ){
		if( !file_exists( $_SERVER['DOCUMENT_ROOT'].$arImport['FILE'] ) ){
			$arErrors[] = GetMessage( 'ERROR_FILE_NOT_EXISTS' );
		}
	}
	
	if( strlen( $arImport['TYPE'] ) <= 0 )
		$arErrors[] = GetMessage( 'ERROR_TYPE' );
	
	if( $arImport['TYPE'] == 'iblock' && intval( $arImport['IBLOCK_ID'] ) <= 0 )
		$arErrors[] = GetMessage( 'ERROR_IBLOCK' );
	
	if( $arImport['TYPE'] == 'form' && intval( $arImport['FORM_ID'] ) <= 0 )
		$arErrors[] = GetMessage( 'ERROR_FORM' );
	
	if( $arImport['FILE'] && $arImport['FILE_TYPE'] == 'csv' && strlen( $arImport['DELIMETER'] ) <= 0 )
		$arErrors[] = GetMessage( 'ERROR_DELIMETER' );
	
	if( $arImport['TYPE'] == 'iblock' ){
		$bCatalog = \Bitrix\Main\Loader::includeModule( 'catalog' );
		$arSKU = array();
		$arCatalog = array();
		$arSkuCatalog = array();
		if( $bCatalog ){
			$arSKU = \CCatalogSKU::GetInfoByProductIBlock( $arImport['IBLOCK_ID'] );
			$arCatalog = \CCatalog::getList( array(), array( 'ID' => $arImport['IBLOCK_ID'] ) )->fetch();
			if( $arSKU ){
				$arSkuCatalog = \CCatalog::getList( array(), array( 'ID' => $arSKU['IBLOCK_ID'] ) )->fetch();
			}
		}
	}
	
	if( $arImport['FILE_TYPE'] == 'xml' ){
		if( !$arImport['ELEMENTS_CHUNK'] ){
			$arErrors[] = GetMessage( 'ERROR_ELEMENTS_CHUNK' );
		}
		
		if( !$arImport['ELEMENT_CHUNK'] ){
			$arErrors[] = GetMessage( 'ERROR_ELEMENT_CHUNK' );
		}
		
		if( $arImport['TYPE'] == 'iblock' ){
			if( $arSKU ){
				if( !$arImport['OFFER_CHUNK'] ){
					$arErrors[] = GetMessage( 'ERROR_OFFERS_CHUNK' );
				}
				
				if( !$arImport['OFFER_CHUNK'] ){
					$arErrors[] = GetMessage( 'ERROR_OFFER_CHUNK' );
				}
			}
		}
	}
	
	if( $arImport['TYPE'] == 'user' ){
		$typeClass = 'omgUserData';
	}elseif( $arImport['TYPE'] == 'iblock' ){
		$typeClass = 'omgIblockData';
	}elseif( $arImport['TYPE'] == 'form' ){
		$typeClass = 'omgFormData';
	}
	
	if( $typeClass ){
		$typeObj = new $typeClass;
	}
	
	if( $arImport['FILE_TYPE'] == 'csv' ){
		$fileClass = 'omgCsvData';
	}/*elseif( $arImport['FILE_TYPE'] == 'xml' ){
		$fileClass = 'omgXmlData';
	}elseif( $arImport['FILE_TYPE'] == 'json' ){
		$fileClass = 'omgJsonData';
	}elseif( $arImport['FILE_TYPE'] == 'xls' ){
		$fileClass = 'omgXlsData';
	}*/
	
	if( $fileClass ){
		$fileObj = new $fileClass;
	}else{
		$arErrors[] = GetMessage( 'ERROR_FILE_TYPE' );
	}
	
	unset( $_SESSION['OMG_IMPORT_INFO'] );
	if( $fileObj ){
		$fileObj->setImport( $arImport );
		$fileObj->setRequest( $request );
	}
	if( $typeObj ){
		$typeObj->setImport( $arImport );
		$typeObj->setRequest( $request );
	}
	
	$arWarnings = array();
	if( $arImport['FIELDS'] && !$arErrors && $typeObj ){
		$arWarnings = array_merge( $arWarnings, $typeObj->checkFields() );
	}
	
	if( $fileObj && ( $bFileExternal = preg_match( '/^(http|https|ftp|ftps):\\/\\//', $arImport['FILE'] ) ) || ( $bImagesExternal = preg_match( '/^(http|https|ftp|ftps):\\/\\//', $arImport['IMAGES_DIR']  ) ) ){
		if( $fileObj->checkCurl() ){
			if( $bFileExternal ){
				$arWarnings = array_merge( $arWarnings, $fileObj->checkStatus( $arImport['FILE'] ) );
			}
			
			if( $bImagesExternal ){
				$arWarnings = array_merge( $arWarnings, $fileObj->checkStatus( $arImport['IMAGES_DIR'] ) );
			}
		}else{
			$arWarnings[] = $fileObj->getCurlError();
		}
	}
	
	if( count( $arErrors ) ){
		$e = new CAdminException( array( array( 'text' => implode( '<br />', $arErrors ) ) ) );
		$message = new CAdminMessage( GetMessage( 'ERROR_SAVE' ), $e );
		echo $message->Show();
	}elseif( count( $arWarnings ) ){
		$e = new CAdminException( array( array( 'text' => implode( '<br />', $arWarnings ) ) ) );
		$message = new CAdminMessage( GetMessage( 'WARNING_SAVE' ), $e );
		echo $message->Show();
	}else{?>
		<div class="progress-box">
			<table width="100%">
				<tr>
					<td width="30%">
						<div class="js-local">
							<div class="title">
								<?=GetMessage( 'LOCAL_TITLE' )?>:
							</div>
							<div class="progress-bar blue stripes">
								<span class="line" style="width: 0px">
									<span class="text">0%</span>
								</span>
								<span class="text">0%</span>
							</div>
						</div>
						<div class="js-global">
							<div class="title">
								<?=GetMessage( 'GLOBAL_TITLE' )?>:
							</div>
							<div class="progress-bar blue stripes" style="margin: 0;">
								<span class="line" style="width: 0px">
									<span class="text">0%</span>
								</span>
								<span class="text">0%</span>
							</div>
						</div>
					</td>
					<td width="70%">
						<div class="title">
							<?=GetMessage( 'LOGS_TITLE' )?>:
							<a class="download js-download" style="display: none;"><?=GetMessage( 'LOGS_DOWNLOAD' )?></a>
						</div>
						<div class="logs">
							<?=GetMessage( 'LOGS_START' )?>
						</div>
					</td>
				</tr>
			</table>
		</div>
		
		<div class="progress-button-block">
			<input class="js-start adm-btn-save" type="button" name="save" value="<?=GetMessage( 'START_IMPORT' )?>" title="<?=GetMessage( 'START_IMPORT_TITLE' )?>" />
			<input class="js-stop" disabled="disabled" type="button" value="<?=GetMessage( 'STOP_IMPORT' )?>" name="cancel" title="<?=GetMessage( 'STOP_IMPORT_TITLE' )?>" />
		</div>
		
		<script type="text/javascript">
			var running = false;
			
			function setPercent( lp, gp ){
				var local_bar = $('.js-local .progress-bar');
				var global_bar = $('.js-global .progress-bar');
				
				var p100 = $('.progress-bar').width();
				
				if( lp == undefined ){
					lp = 0;
				}
				
				if( gp == undefined ){
					gp = 0 + '%';
				}
				
				local_bar.find( '.line' ).css( 'width', ( p100 / 100 * lp ) + 'px' );
				global_bar.find( '.line' ).css( 'width', ( p100 / 100 * gp ) + 'px' );
				
				local_bar.find( '.text' ).text( lp + '%' );
				global_bar.find( '.text' ).text( gp + '%' );
			}
			
			function log( text ){
				var log = $( '.progress-box .logs' );
				
				if( text != undefined && text != '' ){
					log.html( log.html() + text + '<br/>' );
					log[0].scrollTop = log[0].scrollHeight;
				}
			}
			
			function showDownload(){
				$( '.js-download' ).show();
			}
			
			function doNext( NS ){
				var start = $('.js-start');
				var stop = $('.js-stop');
				
				var queryString = 'ID=<?=$arImport['ID']?>&import=Y' + '&<?=bitrix_sessid_get()?>',
					block = $('.adm-detail-content:visible' ),
					id = block.attr('id');
				
				if( !NS ){
					$( '.progress-box .logs' ).html( '' );
				}
				
				if( running ){
					$.ajax({
						type: 'POST',
						url: 'omg_import_exec.php?' + queryString,
						data: NS
					}).done( function( result ){
						var json = JSON.parse( result );
						
						if( json.error ){
							log( '<span style="color: red;">' + json.error + '</span>' );
							
							start.removeAttr( 'disabled' );
							stop.attr( 'disabled', 'disabled' );
							running = false;
						}
						
						if( json.ok ){
							if( json.warning ){
								log( '<span style="color: #6495ED;">' + json.warning + '</span>' );
							}
							log( json.status );
							if( json.info ){
								log( '<span style="color: green;">' + json.info + '</span>' );
							}
							
							setPercent( json.NS.LOCAL_PERCENT, json.NS.GLOBAL_PERCENT );
							
							if( json.stop ){
								start.removeAttr( 'disabled' );
								stop.attr( 'disabled', 'disabled' );
								running = false;
								showDownload();
							}else if( json.NS ){
								doNext( json.NS );
							}
						}
					}).fail( function( jqXHR ){
						log( '<span style="color: red">Request failed: ' + jqXHR.statusText + '</span>' );
						start.removeAttr( 'disabled' );
						stop.attr( 'disabled', 'disabled' );
						running = false;
					});
				}
			}
			
			String.prototype.replaceAll = function( search, replace ){
				return this.split(search).join(replace);
			}
			
			$(document).ready( function(){
				var start = $('.js-start');
				var stop = $('.js-stop');
				start.on( 'click', function(){
					start.attr( 'disabled', 'disabled' );
					stop.removeAttr( 'disabled' );
					running = true;
					doNext();
				} );

				stop.on( 'click', function(){
					start.removeAttr( 'disabled' );
					stop.attr( 'disabled', 'disabled' );
					running = false;
				} );
				
				$( '.js-download' ).on( 'click', function(){
					var text = $( '.logs' ).html();
					text = text.replaceAll( '<br>', "\n" );
					text = text.replaceAll( '<span style="color: #6495ED;">', '' );
					text = text.replaceAll( '<span style="color: green;">', '' );
					text = text.replaceAll( '</span>', '' );
					text = text.replaceAll( '<b>', '' );
					text = text.replaceAll( '</b>', '' );
					
					$( this ).attr( 'href', 'data:application/csv;charset=utf-8,' + encodeURIComponent( text ) );
					$( this ).attr( 'download', 'log.txt' );
				} );
			} );
		</script>
		
		<style>
			.progress-box{
				background: white;
				border-radius: 5px;
				padding: 50px 35px;
			}
			
			.progress-box td{
				vertical-align: top;
				padding: 0 15px;
			}
			
			.progress-box .logs{
				height: 120px;
				padding: 5px;
				overflow-x: hidden;
				overflow-y: scroll;
				border: 1px solid black;
			}
			
			.progress-button-block{
				margin-top: 20px;
			}
			
			.progress-button-block input{
				margin-right: 20px;
			}
			
			.progress-box .title{
				font-size: 18px;
				margin-bottom: 20px;
			}
			
			.progress-bar{
				background-color: #1a1a1a;
				height: 25px;
				padding: 5px;
				width: 300px;
				margin-bottom: 20px;
				border-radius: 5px;
				box-shadow: 0 1px 5px #000 inset, 0 1px 0 #444;
				position: relative;
				text-align: center;
			}
			
			.progress-bar span.text{
				height: 25px;
				line-height: 28px;
				color: white;
				font-size: 17px;
				font-weight: bold;
			}
			
			.progress-bar span.line{
				overflow: hidden;
				display: inline-block;
				height: 25px;
				width: 200px;
				border-radius: 3px;
				box-shadow: 0 1px 0 rgba(255, 255, 255, .5) inset;
				transition: width .4s ease-in-out;
				position: absolute;
				top: 5px;
				left: 5px;
			}
			
			.progress-bar span.line .text{
				display: block;
				position: absolute;
				top: 0;
				left: 0;
				width: 300px;
				color: black;
			}
			
			.blue span.line{
				background-color: #34c2e3;
			}

			.orange span.line{
				background-color: #fecf23;
				background-image: -webkit-gradient(linear, left top, left bottom, from(#fecf23), to(#fd9215));
				background-image: -webkit-linear-gradient(top, #fecf23, #fd9215);
				background-image: -moz-linear-gradient(top, #fecf23, #fd9215);
				background-image: -ms-linear-gradient(top, #fecf23, #fd9215);
				background-image: -o-linear-gradient(top, #fecf23, #fd9215);
				background-image: linear-gradient(top, #fecf23, #fd9215);
			}       

			.green span.line{
				background-color: #a5df41;
				background-image: -webkit-gradient(linear, left top, left bottom, from(#a5df41), to(#4ca916));
				background-image: -webkit-linear-gradient(top, #a5df41, #4ca916);
				background-image: -moz-linear-gradient(top, #a5df41, #4ca916);
				background-image: -ms-linear-gradient(top, #a5df41, #4ca916);
				background-image: -o-linear-gradient(top, #a5df41, #4ca916);
				background-image: linear-gradient(top, #a5df41, #4ca916);
			}

			.stripes span.line{
				background-size: 30px 30px;
				background-image: -webkit-gradient(linear, left top, right bottom, color-stop(.25, rgba(255, 255, 255, .15)), color-stop(.25, transparent), color-stop(.5, transparent), color-stop(.5, rgba(255, 255, 255, .15)), color-stop(.75, rgba(255, 255, 255, .15)), color-stop(.75, transparent), to(transparent));
				background-image: -webkit-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
				background-image: -moz-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
				background-image: -ms-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
				background-image: -o-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
				background-image: linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);            

				-webkit-animation: animate-stripes 3s linear infinite;
				-moz-animation: animate-stripes 3s linear infinite;
			}

			@-webkit-keyframes animate-stripes{
				0% {background-position: 0 0;} 100% {background-position: 60px 0;}
			}

			@-moz-keyframes animate-stripes{
				0% {background-position: 0 0;} 100% {background-position: 60px 0;}
			}

			.shine span.line{
				position: relative;
			}

			.shine span.line::after{
				content: '';
				opacity: 0;
				position: absolute;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				background: #fff;
				border-radius: 3px;                     

				-webkit-animation: animate-shine 2s ease-out infinite;
				-moz-animation: animate-shine 2s ease-out infinite;
			}

			@-webkit-keyframes animate-shine{
				0% {opacity: 0; width: 0;}
				50% {opacity: .5;}
				100% {opacity: 0; width: 95%;}
			}

			@-moz-keyframes animate-shine{
				0% {opacity: 0; width: 0;}
				50% {opacity: .5;}
				100% {opacity: 0; width: 95%;}
			}

			.glow span.line{
				box-shadow: 0 5px 5px rgba(255, 255, 255, .7) inset, 0 -5px 5px rgba(255, 255, 255, .7) inset;

				-webkit-animation: animate-glow 1s ease-out infinite;
				-moz-animation: animate-glow 1s ease-out infinite;
			}

			@-webkit-keyframes animate-glow{
				0% { -webkit-box-shadow: 0 5px 5px rgba(255, 255, 255, .7) inset, 0 -5px 5px rgba(255, 255, 255, .7) inset;}
				50% { -webkit-box-shadow: 0 5px 5px rgba(255, 255, 255, .3) inset, 0 -5px 5px rgba(255, 255, 255, .3) inset;}
				100% { -webkit-box-shadow: 0 5px 5px rgba(255, 255, 255, .7) inset, 0 -5px 5px rgba(255, 255, 255, .7) inset;}
			}

			@-moz-keyframes animate-glow{
				0% { -moz-box-shadow: 0 5px 5px rgba(255, 255, 255, .7) inset, 0 -5px 5px rgba(255, 255, 255, .7) inset;}
				50% { -moz-box-shadow: 0 5px 5px rgba(255, 255, 255, .3) inset, 0 -5px 5px rgba(255, 255, 255, .3) inset;}
				100% { -moz-box-shadow: 0 5px 5px rgba(255, 255, 255, .7) inset, 0 -5px 5px rgba(255, 255, 255, .7) inset;}
			}
			
			.download{
				float: right;
				cursor: pointer;
			}
			
			.download:hover{
				opacity: 0.8;
			}
		<style>
	<?}
}

require( $DOCUMENT_ROOT.'/bitrix/modules/main/include/epilog_admin.php' );
?>
