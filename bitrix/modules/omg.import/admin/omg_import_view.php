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

if( $reqID > 0 && !$arImport ){
	CAdminMessage::ShowMessage( array( 'MESSAGE' => GetMessage( 'ERROR_NOT_FOUND_IMPORT_TITLE' ), 'DETAILS' => GetMessage( 'ERROR_NOT_FOUND_IMPORT' ), 'TYPE' => 'ERROR' ) );
}else{
	\CJSCore::Init( array( 'jquery' ) );

	$arErrors = array();

	$arTabs = array();
	$arTabs[] = array(
		'DIV' => 'edit1',
		'TAB' => GetMessage( 'SETTINGS' ),
		'ICON' => '',
		'TITLE' => GetMessage( 'SETTINGS_DETAIL' )
	);

	$arTabs[] = array(
		'DIV' => 'edit2',
		'TAB' => GetMessage( 'FORMAT' ),
		'ICON' => '',
		'TITLE' => GetMessage( 'FORMAT_DETAIL' )
	);
	
	$arTabs[] = array(
		'DIV' => 'edit3',
		'TAB' => GetMessage( 'FIELDS_SETTINGS' ),
		'ICON' => '',
		'TITLE' => GetMessage( 'FIELDS_SETTINGS_DETAIL' )
	);
	
	$reqSave = isset( $request['save'] ) ? $request['save'] : '';
	$reqApply = isset( $request['apply'] ) ? $request['apply'] : '';

	$reqNAME = '';
	if( isset( $arImport['NAME'] ) ){
		$reqNAME = trim( $arImport['NAME'] );
	}
	if( isset( $request['NAME'] ) ){
		$reqNAME = trim( $request['NAME'] );
	}

	$reqFILE = '';
	if( isset( $arImport['FILE'] ) ){
		$reqFILE = trim( $arImport['FILE'] );
	}
	if( isset( $request['FILE'] ) ){
		$reqFILE = trim( $request['FILE'] );
	}
	
	$reqFILETYPE = '';
	if( isset( $arImport['FILE_TYPE'] ) ){
		$reqFILETYPE = trim( $arImport['FILE_TYPE'] );
	}
	if( isset( $request['FILE'] ) ){
		$ar = explode( '.', $request['FILE'] );
		$reqFILETYPE = $ar[count( $ar ) - 1];
	}
	
	$reqIMAGES = '';
	if( isset( $arImport['IMAGES_DIR'] ) ){
		$reqIMAGES = trim( $arImport['IMAGES_DIR'] );
	}
	if( isset( $request['IMAGES_DIR'] ) ){
		$reqIMAGES = trim( $request['IMAGES_DIR'] );
	}
	if( !$reqIMAGES ){
		$reqIMAGES = '/upload/images/';
	}

	$reqTYPE = '';
	if( isset( $arImport['TYPE'] ) ){
		$reqTYPE = trim( $arImport['TYPE'] );
	}
	if( isset( $request['TYPE'] ) ){
		$reqTYPE = trim( $request['TYPE'] );
	}
	
	$reqIBLOCK = '';
	if( isset( $arImport['IBLOCK_ID'] ) ){
		$reqIBLOCK = intval( $arImport['IBLOCK_ID'] );
	}
	if( isset( $request['IBLOCK_ID'] ) ){
		$reqIBLOCK = intval( $request['IBLOCK_ID'] );
	}
	
	$reqSECTION = '';
	if( isset( $arImport['SECTION_ID'] ) ){
		$reqSECTION = intval( $arImport['SECTION_ID'] );
	}
	if( isset( $request['SECTION_ID'] ) ){
		$reqSECTION = intval( $request['SECTION_ID'] );
	}
	
	$reqFORM = '';
	if( isset( $arImport['FORM_ID'] ) ){
		$reqFORM = intval( $arImport['FORM_ID'] );
	}
	if( isset( $request['IBLOCK_ID'] ) ){
		$reqFORM = intval( $request['FORM_ID'] );
	}

	$reqSTEP = '';
	if( isset( $arImport['STEP'] ) ){
		$reqSTEP = intval( $arImport['STEP'] );
	}
	if( isset( $request['STEP'] ) ){
		$reqSTEP = intval( $request['STEP'] );
	}
	if( !$reqSTEP ){
		$reqSTEP = 30;
	}

	$reqITEMS = '';
	if( isset( $arImport['MISSING_ITEMS'] ) ){
		$reqITEMS = trim( $arImport['MISSING_ITEMS'] );
	}
	if( isset( $request['MISSING_ITEMS'] ) ){
		$reqITEMS = trim( $request['MISSING_ITEMS'] );
	}

	$reqSECTIONS = '';
	if( isset( $arImport['MISSING_SECTIONS'] ) ){
		$reqSECTIONS = trim( $arImport['MISSING_SECTIONS'] );
	}
	if( isset( $request['MISSING_SECTIONS'] ) ){
		$reqSECTIONS = trim( $request['MISSING_SECTIONS'] );
	}
	
	$reqOFFERS = '';
	if( isset( $arImport['MISSING_OFFERS'] ) ){
		$reqOFFERS = trim( $arImport['MISSING_OFFERS'] );
	}
	if( isset( $request['MISSING_OFFERS'] ) ){
		$reqOFFERS = trim( $request['MISSING_OFFERS'] );
	}
	
	$reqDELIMETER = '';
	if( isset( $arImport['DELIMETER'] ) ){
		$reqDELIMETER = $arImport['DELIMETER'];
	}
	if( isset( $request['DELIMETER'] ) ){
		$reqDELIMETER = $request['DELIMETER'];
	}
	
	$reqFIELDS = '';
	if( isset( $arImport['FIELDS'] ) ){
		$reqFIELDS = json_decode( $arImport['FIELDS'] );
	}
	if( isset( $request['FIELDS'] ) ){
		$reqFIELDS = $request['FIELDS'];
	}
	
	$reqELEMENTS_CHUNK = '';
	if( isset( $arImport['ELEMENTS_CHUNK'] ) ){
		$reqELEMENTS_CHUNK = $arImport['ELEMENTS_CHUNK'];
	}
	if( isset( $request['ELEMENTS_CHUNK'] ) ){
		$reqELEMENTS_CHUNK = $request['ELEMENTS_CHUNK'];
	}
	
	$reqELEMENT_CHUNK = '';
	if( isset( $arImport['ELEMENT_CHUNK'] ) ){
		$reqELEMENT_CHUNK = $arImport['ELEMENT_CHUNK'];
	}
	if( isset( $request['ELEMENT_CHUNK'] ) ){
		$reqELEMENT_CHUNK = $request['ELEMENT_CHUNK'];
	}
	
	$reqSECTIONS_CHUNK = '';
	if( isset( $arImport['SECTIONS_CHUNK'] ) ){
		$reqSECTIONS_CHUNK = $arImport['SECTIONS_CHUNK'];
	}
	if( isset( $request['SECTIONS_CHUNK'] ) ){
		$reqSECTIONS_CHUNK = $request['SECTIONS_CHUNK'];
	}
	
	$reqSECTION_CHUNK = '';
	if( isset( $arImport['SECTION_CHUNK'] ) ){
		$reqSECTION_CHUNK = $arImport['SECTION_CHUNK'];
	}
	if( isset( $request['SECTIONS_CHUNK'] ) ){
		$reqSECTION_CHUNK = $request['SECTION_CHUNK'];
	}
	
	$reqOFFERS_CHUNK = '';
	if( isset( $arImport['OFFERS_CHUNK'] ) ){
		$reqOFFERS_CHUNK = $arImport['OFFERS_CHUNK'];
	}
	if( isset( $request['OFFERS_CHUNK'] ) ){
		$reqOFFERS_CHUNK = $request['OFFERS_CHUNK'];
	}
	
	$reqOFFER_CHUNK = '';
	if( isset( $arImport['OFFER_CHUNK'] ) ){
		$reqOFFER_CHUNK = $arImport['OFFER_CHUNK'];
	}
	if( isset( $request['OFFER_CHUNK'] ) ){
		$reqOFFER_CHUNK = $request['OFFER_CHUNK'];
	}
	?>

	<script>
		function submitForm(){
			var d = BX.findChild( document, { attribute: { 'name': 'apply' } }, true );
			if( d )
				d.click();
		}
	</script>
	
	<?
	$tabControl = new CAdminForm( 'omg_import_view', $arTabs );
	
	if( $reqTYPE == 'user' ){
		$typeClass = 'omgUserData';
	}elseif( $reqTYPE == 'iblock' ){
		$typeClass = 'omgIblockData';
	}elseif( $reqTYPE == 'form' ){
		$typeClass = 'omgFormData';
	}
	
	if( $typeClass ){
		$typeObj = new $typeClass;
	}

	if( $reqFILETYPE == 'csv' ){
		$fileClass = 'omgCsvData';
	}/*elseif( $reqFILETYPE == 'xml' ){
		$fileClass = 'omgXmlData';
	}elseif( $reqFILETYPE == 'json' ){
		$fileClass = 'omgJsonData';
	}elseif( $reqFILETYPE == 'xls' ){
		$fileClass = 'omgXlsData';
	}*/
	
	if( $fileClass ){
		$fileObj = new $fileClass;
	}elseif( $reqFILETYPE ){
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
	
	if( $_SERVER['REQUEST_METHOD'] == 'POST' && ( strlen( $reqSave ) > 0 || strlen( $reqApply ) > 0 ) && check_bitrix_sessid() ){
		if( strlen( $reqNAME ) <= 0 )
			$arErrors[] = GetMessage( 'ERROR_NAME' );
		
		if( strlen( $reqFILE ) <= 0 ){
			$arErrors[] = GetMessage( 'ERROR_FILE_EMPTY' );
		}elseif( !preg_match( '/^(http|https|ftp|ftps):\\/\\//', $reqFILE ) ){
			if( !file_exists( $_SERVER['DOCUMENT_ROOT'].$reqFILE ) ){
				$arErrors[] = GetMessage( 'ERROR_FILE_NOT_EXISTS' );
			}
		}
		
		if( strlen( $reqTYPE ) <= 0 )
			$arErrors[] = GetMessage( 'ERROR_TYPE' );
		
		if( $reqTYPE == 'iblock' && intval( $reqIBLOCK ) <= 0 )
			$arErrors[] = GetMessage( 'ERROR_IBLOCK' );
		
		if( $reqTYPE == 'form' && intval( $reqFORM ) <= 0 )
			$arErrors[] = GetMessage( 'ERROR_FORM' );
		
		if( $arImport['FILE'] && $reqFILETYPE == 'csv' && strlen( $reqDELIMETER ) <= 0 )
			$arErrors[] = GetMessage( 'ERROR_DELIMETER' );
		
		/*if( $reqFILETYPE == 'xml' ){
			if( !$reqELEMENTS_CHUNK ){
				$arErrors[] = GetMessage( 'ERROR_ELEMENTS_CHUNK' );
			}
			
			if( !$reqELEMENT_CHUNK ){
				$arErrors[] = GetMessage( 'ERROR_ELEMENT_CHUNK' );
			}
			
			if( $reqTYPE == 'iblock' ){
				if( $arSKU ){
					if( !$reqOFFERS_CHUNK ){
						$arErrors[] = GetMessage( 'ERROR_OFFERS_CHUNK' );
					}
					
					if( !$reqOFFER_CHUNK ){
						$arErrors[] = GetMessage( 'ERROR_OFFER_CHUNK' );
					}
				}
			}
		}*/
		
		if( !$arErrors ){
			$arFields = array(
				'ACTIVE' => isset( $request['ACTIVE'] ) ? 'Y' : 'N',
				'NAME' => $reqNAME,
				'FILE' => $reqFILE,
				'FILE_TYPE' => $reqFILETYPE,
				'IMAGES_DIR' => $reqIMAGES,
				'TYPE' => $reqTYPE,
				'STEP' => $reqSTEP,
				'MISSING_ITEMS' => $reqITEMS,
				'MISSING_SECTIONS' => $reqSECTIONS,
				'MISSING_OFFERS' => $reqOFFERS,
				
				//iblock
				'IBLOCK_ID' => $reqIBLOCK,
				'SECTION_ID' => $reqSECTION,
				
				//form
				'FORM_ID' => $reqFORM,
				
				// csv
				'FIRST_NAMES' => isset( $request['FIRST_NAMES'] ) ? 'Y' : 'N',
				'DELIMETER' => $reqDELIMETER,
				'FIELDS' => json_encode( $reqFIELDS ),
				
				//xml
				'ELEMENTS_CHUNK' => $reqELEMENTS_CHUNK,
				'ELEMENT_CHUNK' => $reqELEMENT_CHUNK,
				'SECTIONS_CHUNK' => $reqSECTIONS_CHUNK,
				'SECTION_CHUNK' => $reqSECTION_CHUNK,
				'OFFERS_CHUNK' => $reqOFFERS_CHUNK,
				'OFFER_CHUNK' => $reqOFFER_CHUNK
			);
			
			if( $reqID > 0 ){
				if( !\OmgImportTable::update( $reqID, $arFields ) ){
					$errors = $res->getErrors();
					if( $errors ){
						foreach( $errors as $error ){
							$arErrors[] = $error->getMessage();
						}
					}else{
						$arErrors[] = GetMessage( 'ERROR_UPDATE' );
					}
				}
			}else{
				$res = \OmgImportTable::add( $arFields );
				$reqID = $res->getId();
				if( $reqID <= 0 ){
					$errors = $res->getErrors();
					if( $errors ){
						foreach( $errors as $error ){
							$arErrors[] = $error->getMessage();
						}
					}else{
						$arErrors[] = GetMessage( 'ERROR_ADD' );
					}
				}
			}

			if( !$arErrors ){
				if( strlen( $apply ) > 0 )
					LocalRedirect( 'omg_import_view.php?ID='.$reqID.'&'.$tabControl->ActiveTabParam() );
				else
					LocalRedirect( 'omg_import_index.php' );
			}
		}
	}

	$APPLICATION->SetTitle( $reqID > 0 ? GetMessage( 'CHANGE_IMPORT', array( '#NAME#' => $arImport['NAME'] ) ) : GetMessage( 'NEW_IMPORT' ) );

	require( $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php' );

	$arMenu = array(
		array(
			'TEXT' => GetMessage( 'MENU_LIST' ),
			'LINK' => '/bitrix/admin/omg_import_index.php',
			'ICON' => 'btn_list'
		)
	);

	if( $reqID > 0 ){
		$arMenu[] = array( 'SEPARATOR' => 'Y' );

		$arMenu[] = array(
			'TEXT' => GetMessage( 'MENU_NEW' ),
			'LINK' => '/bitrix/admin/omg_import_view.php',
			'ICON' => 'btn_new'
		);

		$arMenu[] = array(
			'TEXT' => GetMessage( 'MENU_DELETE' ),
			'LINK' => 'javascript:if( confirm( \''.GetMessage( 'MENU_DELETE_QUESTION' ).'\' ) ) window.location=\'/bitrix/admin/omg_import_index.php?action=delete&ID[]='.$reqID.'&'.bitrix_sessid_get().'#tb\';',
			'WARNING' => 'Y',
			'ICON' => 'btn_delete'
		);
	}
	
	$arWarnings = array();
	if( $reqFIELDS && !$arErrors && $typeObj ){
		$arWarnings = array_merge( $arWarnings, $typeObj->checkFields() );
	}
	
	if( $fileObj && ( $bFileExternal = preg_match( '/^(http|https|ftp|ftps):\\/\\//', $reqFILE ) ) || ( $bImagesExternal = preg_match( '/^(http|https|ftp|ftps):\\/\\//', $reqIMAGES ) ) ){
		if( $fileObj->checkCurl() ){
			if( $bFileExternal ){
				$arWarnings = array_merge( $arWarnings, $fileObj->checkStatus( $reqFILE ) );
			}
			
			if( $bImagesExternal ){
				$arWarnings = array_merge( $arWarnings, $fileObj->checkStatus( $reqIMAGES ) );
			}
		}else{
			$arWarnings[] = $fileObj->getCurlError();
		}
	}
	
	$context = new CAdminContextMenu( $arMenu );
	$context->Show();

	if( count( $arErrors ) ){
		$e = new CAdminException( array( array( 'text' => implode( '<br />', $arErrors ) ) ) );
		$message = new CAdminMessage( GetMessage( 'ERROR_SAVE' ), $e );
		echo $message->Show();
	}
	
	if( count( $arWarnings ) ){
		$e = new CAdminException( array( array( 'text' => implode( '<br />', $arWarnings ) ) ) );
		$message = new CAdminMessage( GetMessage( 'WARNING_SAVE' ), $e );
		echo $message->Show();
	}

	$tabControl->BeginEpilogContent();
	?>

	<?=bitrix_sessid_post()?>
	<input type="hidden" name="Update" value="Y" />
	<input type="hidden" name="ID" value="<?=$reqID?>" />
	<?
	$tabControl->EndEpilogContent();

	$tabControl->Begin(array(
		'FORM_ACTION' => $APPLICATION->GetCurPage().'?ID='.intval( $reqID ).'&lang='.LANG
	));

	$tabControl->BeginNextFormTab();

	if( $reqID > 0 ){
		$tabControl->AddViewField( 'ID', 'ID', $reqID );
	}

	$tabControl->AddCheckBoxField( 'ACTIVE', GetMessage( 'FIELD_ACTIVE' ), false, 'Y', $arImport['ACTIVE'] !== 'N' );
	$tabControl->AddEditField( 'NAME', GetMessage( 'FIELD_NAME' ), true, array( 'size' => 40, 'maxlength' => 255 ), $reqNAME );
	$tabControl->AddEditField( 'FILE', GetMessage( 'FIELD_FILE' ), true, array( 'size' => 40 ), $reqFILE );
	
	$tabControl->BeginCustomField( 'FILE', GetMessage( 'FIELD_FILE' ) );
	?>
		<tr>
			<td><b><?=$tabControl->GetCustomLabelHTML()?></b></td>
			<td>
				<input type="text" name="FILE" value="<?=htmlspecialcharsbx( $reqFILE )?>" size="30" />
				<input type="button" value="<?=GetMessage( 'FIELD_FILE_OPEN' )?>" onclick="fileFind()" />
				<?CAdminFileDialog::ShowScript( array(
					'event' => 'fileFind',
					'arResultDest' => array(
						'FORM_NAME' => 'omg_import_view_form',
						'FORM_ELEMENT_NAME' => 'FILE',
					) ,
					'arPath' => array(
						'SITE' => SITE_ID,
						'PATH' => '/'.COption::GetOptionString( 'main', 'upload_dir', 'upload' ),
					) ,
					'select' => 'F', // F - file only, D - folder only
					'operation' => 'O', // O - open, S - save
					'showUploadTab' => true,
					'showAddToMenuTab' => false,
					'fileFilter' => 'csv',//'csv,xml,json,xls',
					'allowAllFiles' => true,
					'SaveConfig' => true
				) );?>
			</td>
		</tr>
	<?
	$tabControl->EndCustomField( 'FILE' );

	$arTypes = array(
		'' => GetMessage( 'FIELD_TYPE_EMPTY' ),
		'user' => GetMessage( 'FIELD_TYPE_USER' ),
		'iblock' => GetMessage( 'FIELD_TYPE_IBLOCK' )
	);
	
	$arIblocks = array(
		'' => GetMessage( 'FIELD_IBLOCK_ID_EMPTY' )
	);
	$rsIblock = \CIBlock::GetList( array( 'ID' => 'ASC' ) );
	while( $arIblock = $rsIblock->fetch() ){
		$arIblocks[$arIblock['ID']] = $arIblock['NAME'].' ('.$arIblock['ID'].')';
	}
	
	$arSections = array(
		0 => GetMessage( 'FIELD_SECTION_ID_ROOT' )
	);
	$rsSection = \CIBlockSection::GetList( array( 'ID' => 'ASC' ), array( 'IBLOCK_ID' => $reqIBLOCK ) );
	while( $arSection = $rsSection->fetch() ){
		$arSections[$arSection['ID']] = $arSection['NAME'].' ('.$arSection['ID'].')';
	}
	
	/*if( \Bitrix\Main\Loader::includeModule( 'form' ) ){
		$arTypes['form'] = GetMessage( 'FIELD_TYPE_FORM' );
		
		$arForms = array(
			'' => GetMessage( 'FIELD_FORM_ID_EMPTY' ),
		);
		$rsForm = \CForm::GetList( $by = 's_id', $order = 'desc', array(), $filtered );
		while( $arForm = $rsForm->fetch() ){
			$arForms[$arForm['ID']] = $arForm['NAME'].' ('.$arForm['ID'].')';
		}
	}*/

	$tabControl->AddDropDownField( 'TYPE', GetMessage( 'FIELD_TYPE' ), true, $arTypes, $reqTYPE );
	
	$tabControl->AddDropDownField( 'IBLOCK_ID', GetMessage( 'FIELD_IBLOCK_ID' ), true, $arIblocks, $reqIBLOCK );
	$tabControl->AddDropDownField( 'SECTION_ID', GetMessage( 'FIELD_SECTION_ID' ), true, $arSections, $reqSECTION );
	
	//$tabControl->AddDropDownField( 'FORM_ID', GetMessage( 'FIELD_FORM_ID' ), true, $arForms, $reqFORM );
	
	$tabControl->BeginNextFormTab();
	
	if( $reqFILETYPE == 'csv' ){
		$tabControl->AddCheckBoxField( 'FIRST_NAMES', GetMessage( 'FIELD_FIRST_NAMES' ), false, 'Y', $arImport['FIRST_NAMES'] !== 'N' );
		
		$arDelimeters = array(
			'' => GetMessage( 'DELIMETER_EMPTY' ),
			'TZP' => GetMessage( 'DELIMETER_TZP' ),
			'ZPT' => GetMessage( 'DELIMETER_ZPT' ),
			'TAB' => GetMessage( 'DELIMETER_TAB' ),
			'SPS' => GetMessage( 'DELIMETER_SPS' )
		);
		$tabControl->AddDropDownField( 'DELIMETER', GetMessage( 'FIELD_DELIMETER' ), true, $arDelimeters, $reqDELIMETER );
	}elseif( $reqFILETYPE == 'xml' ){
		/*$arChunks = array(
			'' => GetMessage( 'CHUNK_EMPTY' )
		);
		
		$reader = new SimpleXMLReader;
		$reader->open( $_SERVER['DOCUMENT_ROOT'].$arImport['FILE'] );
		$reader->parse();
		$tmp = $reader->getNodesXpath();
		$reader->close();
		
		foreach( $tmp as $key => $value ){
			$arChunks[$key] = $value;
		}
		
		$tabControl->AddDropDownField( 'ELEMENTS_CHUNK', GetMessage( 'FIELD_ELEMENTS_CHUNK' ), true, $arChunks, $reqELEMENTS_CHUNK );
		$tabControl->AddDropDownField( 'ELEMENT_CHUNK', GetMessage( 'FIELD_ELEMENT_CHUNK' ), true, $arChunks, $reqELEMENT_CHUNK );
		
		if( $arImport['TYPE'] == 'iblock' ){
			$tabControl->AddDropDownField( 'SECTIONS_CHUNK', GetMessage( 'FIELD_SECTIONS_CHUNK' ), false, $arChunks, $reqSECTIONS_CHUNK );
			$tabControl->AddDropDownField( 'SECTION_CHUNK', GetMessage( 'FIELD_SECTION_CHUNK' ), false, $arChunks, $reqSECTION_CHUNK );
			
			if( $arSKU ){
				$tabControl->AddDropDownField( 'OFFERS_CHUNK', GetMessage( 'FIELD_OFFERS_CHUNK' ), true, $arChunks, $reqOFFERS_CHUNK );
				$tabControl->AddDropDownField( 'OFFER_CHUNK', GetMessage( 'FIELD_OFFER_CHUNK' ), true, $arChunks, $reqOFFER_CHUNK );
			}
		}*/
	}elseif( $reqFILETYPE == 'json' ){
		
	}elseif( $reqFILETYPE == 'xls' ){
		
	}
	
	$tabControl->AddSection( 'SECTION_ADDITIONAL', GetMessage( 'SECTION_ADDITIONAL' ) );

	$tabControl->BeginCustomField( 'IMAGES_DIR', GetMessage( 'FIELD_IMAGES_DIR' ) );
	?>
		<tr>
			<td><?=$tabControl->GetCustomLabelHTML()?></td>
			<td>
				<input type="text" name="IMAGES_DIR" value="<?=htmlspecialcharsbx( $reqIMAGES )?>" size="30" />
				<input type="button" value="<?=GetMessage( 'FIELD_FILE_OPEN' )?>" onclick="dirFind()" />
				<?CAdminFileDialog::ShowScript( array(
					'event' => 'dirFind',
					'arResultDest' => array(
						'FORM_NAME' => 'omg_import_view_form',
						'FORM_ELEMENT_NAME' => 'IMAGES_DIR',
					) ,
					'arPath' => array(
						'SITE' => SITE_ID,
						'PATH' => '/'.COption::GetOptionString( 'main', 'upload_dir', 'upload' ),
					) ,
					'select' => 'D', // F - file only, D - folder only
					'operation' => 'O', // O - open, S - save
					'showUploadTab' => true,
					'showAddToMenuTab' => false,
					'fileFilter' => '',
					'allowAllFiles' => true,
					'SaveConfig' => true
				) );?>
			</td>
		</tr>
	<?
	$tabControl->EndCustomField( 'IMAGES_DIR' );
	
	$tabControl->AddEditField( 'STEP', GetMessage( 'FIELD_STEP' ), false, array( 'size' => 40 ), $reqSTEP );

	$arMissingItems = array(
		'0' => GetMessage( 'MISSING_ITEMS_NOTHING' ),
		'U' => GetMessage( 'MISSING_ITEMS_DEACTIVATE' ),
		'D' => GetMessage( 'MISSING_ITEMS_DELETE' )
	);
	if( \Bitrix\Main\Loader::includeModule( 'catalog' ) && $reqTYPE == 'iblock' ){
		if( $bCatalog && $arCatalog ){
			$arMissingItems['C'] = GetMessage('MISSING_ITEMS_COUNT');
		}
	}
	
	$arMissingSections = array(
		'0' => GetMessage( 'MISSING_SECTIONS_NOTHING' ),
		'U' => GetMessage( 'MISSING_SECTIONS_DEACTIVATE' ),
		'D' => GetMessage( 'MISSING_SECTIONS_DELETE' )
	);
	
	if( $reqTYPE == 'user' ){
		$tabControl->AddDropDownField( 'MISSING_SECTIONS', GetMessage( 'FIELD_MISSING_GROUPS_USER' ), false, $arMissingSections, $reqSECTIONS );
		$tabControl->AddDropDownField( 'MISSING_ITEMS', GetMessage( 'FIELD_MISSING_ITEMS_USER' ), false, $arMissingItems, $reqITEMS );
	}elseif( $reqTYPE == 'iblock' ){
		$tabControl->AddDropDownField( 'MISSING_SECTIONS', GetMessage( 'FIELD_MISSING_SECTIONS_IBLOCK' ), false, $arMissingSections, $reqSECTIONS );
		$tabControl->AddDropDownField( 'MISSING_ITEMS', GetMessage( 'FIELD_MISSING_ITEMS_IBLOCK' ), false, $arMissingItems, $reqITEMS );
		if( $bCatalog && $arSKU ){
			$tabControl->AddDropDownField( 'MISSING_OFFERS', GetMessage( 'FIELD_MISSING_OFFERS_IBLOCK' ), false, $arMissingItems, $reqOFFERS );
		}
	}
	
	$tabControl->AddSection( 'SECTION_EXAMPLE', GetMessage( 'SECTION_EXAMPLE' ) );
	$tabControl->BeginCustomField( 'EXAMPLE', '' );
	
	if( $fileObj && $arImport['FILE'] ){
		$sContent = $fileObj->getDemo();
	}
	?>
		<tr>
			<td colspan="2">
				<textarea wrap="OFF" rows="10" cols="80" style="width: 100%"><?=$sContent?></textarea>
			</td>
		</tr>
	<?
	$tabControl->EndCustomField( 'EXAMPLE' );
	
	$tabControl->BeginNextFormTab();
	
	if( $fileObj && $arImport['FILE'] ){
		if( $arDataFileFields = $fileObj->getFields() ){
			$tabControl->AddSection( 'SECTION_CSV_FIELDS', GetMessage( 'SECTION_CSV_FIELDS' ) );
			
			$tabControl->BeginCustomField( 'FIELDS', '' );
			echo $fileObj->printFields( $arDataFileFields, $typeObj->getFields() );
			$tabControl->EndCustomField( 'FIELDS' );
		}
	}else{
		echo ' ';
	}
	
	$tabControl->Buttons( array(
		'btnSaveAndAdd' => true,
		'back_url' => 'omg_import_index.php?lang='.LANG,
	), '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="exec_button" href="omg_import_exec.php?ID='.$arImport['ID'].'&lang='.LANG.'">'.GetMessage( "BUTTON_START" ).'</a>' );

	$tabControl->Show();
	?>
	<script>
		$(document).ready(function(){
			var ob_exec_button = $( '#exec_button' );
			
			<?if( !$arImport['FILE'] ){?>
				omg_import_view.DisableTab( 'edit2' );
				omg_import_view.DisableTab( 'edit3' );
				ob_exec_button.hide();
			<?}elseif(
				( $arImport['FILE_TYPE'] == 'csv' && !$arImport['DELIMETER'] ) ||
				( $arImport['FILE_TYPE'] == 'xml' && ( !$arImport['CHUNK_ELEMENTS'] || !$arImport['CHUNK_ELEMENT'] ) )
			){?>
				omg_import_view.DisableTab( 'edit3' );
				ob_exec_button.hide();
			<?}elseif( $typeObj->checkFields() ){?>
				ob_exec_button.hide();
			<?}?>
			
			var ob_iblock_settings = $( '#tr_IBLOCK_ID' );
			var ob_section_settings = $( '#tr_SECTION_ID' );
			<?if( $arImport['TYPE'] != 'iblock' && $_REQUEST['TYPE'] != 'iblock' ){?>
				ob_iblock_settings.hide();
				ob_section_settings.hide();
			<?}?>
			var ob_form_settings = $( '#tr_FORM_ID' );
			<?if( $arImport['TYPE'] != 'form' && $_REQUEST['TYPE'] != 'form' ){?>
				ob_form_settings.hide();
			<?}?>
			
			$( 'select[name="TYPE"]' ).change( function(){
				var $this = $(this);
				var value = $this.val();
				if( value == 'iblock' ){
					ob_iblock_settings.show();
					ob_section_settings.show();
				}else{
					ob_iblock_settings.hide();
					ob_section_settings.hide();
				}
				
				if( value == 'form' ){
					ob_form_settings.show();
				}else{
					ob_form_settings.hide();
				}
			} );
		});
	</script>
<?
}

require( $DOCUMENT_ROOT.'/bitrix/modules/main/include/epilog_admin.php' );
?>
