<?
///////////////////////////////////////////////
//   Copyright (c) 2012-2014 PHP Solutions   //
//   Поддержка: support@phpsolutions.ru      //
///////////////////////////////////////////////

if(!$USER->IsAdmin()) return;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
$APPLICATION->SetAdditionalCSS("/bitrix/js/phpsolutions.backtotop/backtotop.css");

define( 'MODULE_ID', 'phpsolutions.backtotop' ) ;

$rsSites = CSite::GetList( $by="sort", $order="desc", Array( "ACTIVE" => "Y" ) ) ;
while( $t = $rsSites->GetNext()){
	$sites["REFERENCE"][] = $t[NAME];
	$sites["REFERENCE_ID"][] = $t[LID];
}	
if( count( $sites ) == 0 ){
	$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_ERROR_NO_SITE" ) ;
	$mess_type = "ERROR" ;
	CAdminMessage::ShowMessage(array(
	   "MESSAGE"=> $message,
	   "TYPE"=> $mess_type,
	));
	return ;
}

if( strlen( $_REQUEST["phpsolutions_backtotop_site"]) > 0 ){	
	$_SESSION['phpsolutions_backtotop_site'] = htmlspecialcharsbx($_REQUEST["phpsolutions_backtotop_site"]);	
}
if(strlen($_SESSION['phpsolutions_backtotop_site'])>0){
	$site_id = $_SESSION['phpsolutions_backtotop_site'];
}
else{
	$site_id = $sites[ "REFERENCE_ID" ][ 0 ] ;
}

$message = '' ;
$mess_type = "OK" ;

$options_names = array(
	"phpsolutions_backtotop_jquery",
	"phpsolutions_backtotop_pos_y",
	"phpsolutions_backtotop_pos_x",
	"phpsolutions_backtotop_button_opacity",
	"phpsolutions_backtotop_position",
	"phpsolutions_backtotop_selected_image",
	"phpsolutions_backtotop_upload",
	"phpsolutions_backtotop_exclude_url",
	"phpsolutions_backtotop_skip",
	"phpsolutions_backtotop_scroll_speed",
) ;

// ---------------------  сброс настроек -------------------------

if( $reset && strlen( $site_id ) > 0 && $site_id != 'ru' && check_bitrix_sessid() ){
	COption::RemoveOption( MODULE_ID ) ;
	$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_SETTINGS_RESTORED" ) ;
}

// ---------------------  удаление картинки -------------------------

if( strlen( $delete_image ) > 0 && check_bitrix_sessid() ){
	$arFiles = scandir($_SERVER['DOCUMENT_ROOT'].'/bitrix/images/phpsolutions.backtotop/' ) ; 
	$arFiles = array_values( $arFiles ) ;
	foreach( $arFiles as $file ){
		if( $file == $delete_image ){
			if( unlink( $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/phpsolutions.backtotop/'.$file ) ){
				$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_IMAGE_REMOVED" ) ;
				$mess_type = "OK" ;
			}
			else{
				$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_IMAGE_REMOVAL_ERROR" ) ;
				$mess_type = "ERROR" ;
			}
			break ;
		}
	}
	if( $message == '' ){
		$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_IMAGE_NOT_FOUND" ) ;
		$mess_type = "ERROR" ;
	}
}

// ---------------------  сохранение настроек -------------------------

if( strlen( $apply ) > 0 && strlen( $site_id ) > 0 && $site_id != 'ru' && check_bitrix_sessid() ){
	$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_SETTINGS_UPDATED" ) ;
	$mess_type = "OK" ;
	
	foreach( $options_names as $name ){		
		
		if( $name == 'phpsolutions_backtotop_upload' ){
			if( strlen( $_FILES[ 'phpsolutions_backtotop_upload' ][ "tmp_name" ] ) > 0 ){
				if( strpos( $_FILES[ 'phpsolutions_backtotop_upload' ][ 'type' ], 'image' ) === 0 ){
					$tmp_name = $_FILES[ 'phpsolutions_backtotop_upload' ][ "tmp_name" ];
					$filename = $_FILES[ 'phpsolutions_backtotop_upload' ][ "name" ];
					$extension = strrchr( $filename, "." ) ;
					$filename = 'img_'.time().$extension ;
					move_uploaded_file( $tmp_name, $_SERVER['DOCUMENT_ROOT']."/bitrix/images/phpsolutions.backtotop/".$filename ) ;
					$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_FILE_UPLOADED" ) ;
					$mess_type = "OK" ;
				}
				else{
					$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_FILE_ERROR" ) ;	
					$mess_type = "ERROR" ;	
				}		
			}
		}
		
		elseif( strlen( $_REQUEST[$name] ) > 0 ){
			if( $name == 'phpsolutions_backtotop_pos_y' || $name == 'phpsolutions_backtotop_pos_x' || $name == 'phpsolutions_backtotop_skip' ){
				if( (int)$_REQUEST[ $name ] > 0 ){
					COption::SetOptionString( MODULE_ID, $name.'_'.$site_id, (int)$_REQUEST[ $name ] ) ;
				}
				else{ 					
					$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_INT_ERROR" ) ;	
					$mess_type = "ERROR" ;	
				}
			}
			elseif( $name == 'phpsolutions_backtotop_button_opacity' ){
				if( (int)$_REQUEST[ $name ] > 0 && (int)$_REQUEST[ $name ] <= 100 ){
					COption::SetOptionString( MODULE_ID, $name.'_'.$site_id, (int)$_REQUEST[ $name ] ) ;
				}
				else{ 					
					$message = GetMessage( "PHPSOLUTIONS_BACKTOTOP_OPACITY_ERROR" ) ;	
					$mess_type = "ERROR" ;	
				}
			}
			else{
				COption::SetOptionString(MODULE_ID, $name.'_'.$site_id, htmlspecialcharsbx($_REQUEST[$name]));			
			}
		}	
	}
}

// ---------------------  получение настроек -------------------------
{
$options = array(		
	"phpsolutions_backtotop_jquery" => Array(
		COption::GetOptionString(MODULE_ID, 'phpsolutions_backtotop_jquery'.'_'.$site_id, "N" ),
		array(
			"REFERENCE" => array(GetMessage('PHPSOLUTIONS_BACKTOTOP_ENABLE_JQUERY_YES'), GetMessage('PHPSOLUTIONS_BACKTOTOP_ENABLE_JQUERY_NO')),"REFERENCE_ID" => array('Y','N')
		)
	),
	"phpsolutions_backtotop_button_opacity" => 
		COption::GetOptionString( MODULE_ID, 'phpsolutions_backtotop_button_opacity'.'_'.$site_id, "80" ),
	"phpsolutions_backtotop_position" => Array(
		COption::GetOptionString(MODULE_ID, 'phpsolutions_backtotop_position'.'_'.$site_id, "bottom-right"),
		array(
			"REFERENCE" =>array(
				GetMessage('PHPSOLUTIONS_BACKTOTOP_TOP_LEFT'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_TOP_CENTER'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_TOP_RIGHT'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_MIDDLE_LEFT'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_MIDDLE_CENTER'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_MIDDLE_RIGHT'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_BOTTOM_LEFT'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_BOTTOM_CENTER'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_BOTTOM_RIGHT'),
			),
			"REFERENCE_ID" => array(
				'top-left',
				'top-center',
				'top-right',
				'middle-left',
				'middle-center',
				'middle-right',
				'bottom-left',
				'bottom-center',
				'bottom-right'
			)
		)
	),
	"phpsolutions_backtotop_pos_y" => 
		COption::GetOptionString( MODULE_ID, 'phpsolutions_backtotop_pos_y'.'_'.$site_id, "30" ),
	"phpsolutions_backtotop_pos_x" => 
		COption::GetOptionString( MODULE_ID, 'phpsolutions_backtotop_pos_x'.'_'.$site_id, "30" ),
	"phpsolutions_backtotop_selected_image" =>
		COption::GetOptionString( MODULE_ID, 'phpsolutions_backtotop_selected_image'.'_'.$site_id, "/bitrix/images/phpsolutions.backtotop/backtotop1.png" ),
	"phpsolutions_backtotop_exclude_url" =>
		COption::GetOptionString( MODULE_ID, 'phpsolutions_backtotop_exclude_url'.'_'.$site_id, "" ),
	"phpsolutions_backtotop_skip" =>
		COption::GetOptionString( MODULE_ID, 'phpsolutions_backtotop_skip'.'_'.$site_id, "1000" ),
	"phpsolutions_backtotop_scroll_speed" => Array(
		COption::GetOptionString(MODULE_ID, 'phpsolutions_backtotop_scroll_speed'.'_'.$site_id, "normal"),
		array(
			"REFERENCE" => array(
				GetMessage('PHPSOLUTIONS_BACKTOTOP_SPEED_SLOW'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_SPEED_NORMAL'),
				GetMessage('PHPSOLUTIONS_BACKTOTOP_SPEED_FAST')
			),
			"REFERENCE_ID" => array(
				'slow',
				'normal',
				'fast'
			)
		)
	),
);
}
// ---------------------  вывод формы -------------------------

if( $message != '' ){
	CAdminMessage::ShowMessage(array(
	   "MESSAGE"=> $message,
	   "TYPE"=> $mess_type,
	));
}

$tabControl = new CAdminTabControl(
	'tabControl',
	array(
		array(
		'DIV' => 'edit1',
		'TAB' => GetMessage('MAIN_TAB_SET'),
		'TITLE' => GetMessage('MAIN_TAB_TITLE_SET')
		),
	)
);
$tabControl->Begin();

?>

<form id="edit1" enctype="multipart/form-data" name="phpsolutions_backtotop" method='POST' action='<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>'>
<?=bitrix_sessid_post();?>
<?$tabControl->BeginNextTab();?>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name adm-detail-content-cell-l'> <? ShowJSHint( GetMessage('PHPSOLUTIONS_BACKTOTOP_SITE_HINT') ) ; ?> <label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_SITE')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<?echo SelectBoxFromArray(
			"phpsolutions_backtotop_site",
			$sites,
			$site_id,
			"",
			"onchange='change_site( this.value );'"
			)
		?>
	</td>
</tr>	
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name adm-detail-content-cell-l'> <? ShowJSHint( GetMessage('PHPSOLUTIONS_BACKTOTOP_INCLUDE_JQUERY_HINT') ) ; ?> <label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_INCLUDE_JQUERY')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<? echo SelectBoxFromArray( "phpsolutions_backtotop_jquery", $options["phpsolutions_backtotop_jquery"][ 1 ], $options["phpsolutions_backtotop_jquery"][ 0 ] ) ?>
	</td>
</tr>	
<tr class="field-str">
    <td valign='middle' align="center" colspan="2" width='100%' class=''><label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_GALLERY')?>: &nbsp;</label></td>
</tr> 
<script language='JavaScript'>
function show_close_button( id ){
    document.getElementById( "close_"+id ).style.visibility = 'visible' ;
}
function hide_close_button( id ){
    document.getElementById( "close_"+id ).style.visibility = 'hidden' ;
}
</script>
<tr class="field-str">
	<td align='center' valign='middle' width='100%' colspan="2">
	<input id="selected_img" type="hidden" name="phpsolutions_backtotop_selected_image" value="">	
	<div class="gallery_block">
		<?		
		$arFile = scandir($_SERVER['DOCUMENT_ROOT'].'/bitrix/images/phpsolutions.backtotop/'); 
		$arFile = array_values( $arFile ) ;
		foreach( $arFile as $key => $file ){
			if( $key > 1){
				if( $file == 'del.png' ) continue ;
				$size = getimagesize( $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/phpsolutions.backtotop/'.$file ) ;
				?>
				<div id="image_div<?=$key;?>"
					onclick="select_image('/bitrix/images/phpsolutions.backtotop/<?=$file;?>','<?=$key;?>')"
					class="img_gallery <?= ( $options["phpsolutions_backtotop_selected_image"] =='/bitrix/images/phpsolutions.backtotop/'.$file ) ? 'selected_file' : '' ; ?>"
					onmouseover="show_close_button(<?=$key;?>);"
					onmouseout="hide_close_button(<?=$key;?>);"
					>		
					<img src="<?='/bitrix/images/phpsolutions.backtotop/'.$file;?>"
					alt="<?=GetMessage('PHPSOLUTIONS_BACKTOTOP_FILENAME')?> <?=$file;?><?="\n"?><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_SIZE')?> <?= $size[ 0 ].'x'.$size[ 1 ] ?> <?=GetMessage('PHPSOLUTIONS_BACKTOTOP_PIX')?>"
					title="<?=GetMessage('PHPSOLUTIONS_BACKTOTOP_FILENAME')?> <?=$file;?><?="\n"?><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_SIZE')?> <?= $size[ 0 ].'x'.$size[ 1 ] ?> <?=GetMessage('PHPSOLUTIONS_BACKTOTOP_PIX')?>">
					<div class="helper"></div>
				</div>
				<img id="close_<?=$key;?>" src="<?='/bitrix/images/phpsolutions.backtotop/del.png';?>" class="close_button"
					onclick="delete_image('<?=$file;?>','<?=$key;?>')"
					onmouseover="show_close_button(<?=$key;?>);"
					onmouseout="hide_close_button(<?=$key;?>);"
					alt="<?=GetMessage('PHPSOLUTIONS_BACKTOTOP_REMOVE_THIS_IMAGE')?>" title="<?=GetMessage('PHPSOLUTIONS_BACKTOTOP_REMOVE_THIS_IMAGE')?>"
					>
				<?
			}
		}
		?>
	</div>		
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name adm-detail-content-cell-l'><label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_FILE')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<input type="file" name="phpsolutions_backtotop_upload">
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' align="center" colspan="2" width='100%' class=''> <? ShowJSHint( GetMessage('PHPSOLUTIONS_BACKTOTOP_EXCLUDE_URL_HINT') ) ; ?> <label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_EXCLUDE_URL')?>: &nbsp;</label></td>
</tr> 
<tr class="field-str">
    <td align="center" valign='middle' width='100%' colspan="2">
		<textarea cols="100" rows="5" name="phpsolutions_backtotop_exclude_url"><?=$options["phpsolutions_backtotop_exclude_url"];?></textarea>
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name'> <? ShowJSHint( GetMessage('PHPSOLUTIONS_BACKTOTOP_TRANSPARENCY_HINT') ) ; ?> <label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_TRANSPARENCY')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<input type="text" size="5" maxlength="255" value="<?=$options["phpsolutions_backtotop_button_opacity"];?>" name="phpsolutions_backtotop_button_opacity">
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name'><label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_POSITION')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<?echo SelectBoxFromArray( "phpsolutions_backtotop_position", $options["phpsolutions_backtotop_position"][ 1 ], $options["phpsolutions_backtotop_position"][ 0 ] ) ?>
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name'> <? ShowJSHint( GetMessage('PHPSOLUTIONS_BACKTOTOP_SKIP_HINT') ) ; ?> <label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_SKIP')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<input type="text" size="6" maxlength="255" value="<?=$options["phpsolutions_backtotop_skip"]?>" name="phpsolutions_backtotop_skip"> px
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name'><label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_VERTICAL_MARGIN')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<input type="text" size="5" maxlength="255" value="<?=$options["phpsolutions_backtotop_pos_y"]?>" name="phpsolutions_backtotop_pos_y"> px
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name'><label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_HORIZONTAL_MARGIN')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<input type="text" size="5" maxlength="255" value="<?=$options["phpsolutions_backtotop_pos_x"]?>" name="phpsolutions_backtotop_pos_x"> px
	</td>
</tr>
<tr class="field-str">
    <td valign='middle' width='50%' class='field-name'><label><?=GetMessage('PHPSOLUTIONS_BACKTOTOP_SCROLL_SPEED')?>: &nbsp;</label></td>
    <td valign='middle' width='50%'>
		<? echo SelectBoxFromArray( "phpsolutions_backtotop_scroll_speed", $options["phpsolutions_backtotop_scroll_speed"][ 1 ], $options["phpsolutions_backtotop_scroll_speed"][ 0 ] ) ?>
	</td>
</tr>
<?$tabControl->Buttons();?>
<script language='JavaScript'>
function confirm_reset(){
    if(confirm('<?echo AddSlashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')){
        document.getElementById( "resetparam" ).value = '1' ;
		document.forms[ "edit1" ].submit() ;
	}
}
</script>
<input id="resetparam" type="hidden" name="reset" value="0">
<input id="delimageparam" type="hidden" name="delete_image">
<input type="submit" name="apply" value="<?echo GetMessage('MAIN_APPLY')?>">
<input type="button" onclick="confirm_reset();" title='<?echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>' value='<?echo GetMessage('MAIN_RESTORE_DEFAULTS')?>'>
<?
$tabControl->End();
CUtil::InitJSCore(Array("jquery"));
?>		
</form>
<script>
function change_site( sid ){
	window.location.href = '<?= $APPLICATION->GetCurPage().'?mid='.urlencode($mid).'&lang='.urlencode(LANGUAGE_ID).'&back_url_settings='.urlencode($_REQUEST['back_url_settings']).'&'.$tabControl->ActiveTabParam() ?>' + '&phpsolutions_backtotop_site=' + sid ;
}
function select_image( link, id ){
	$( '#selected_img' ).attr( "value", link );	
	$( '.field-str td div div' ).removeClass("selected_file");	
	$( '#image_div'+id ).addClass("selected_file");	
}
function delete_image( file ){
    if( confirm( '<?echo AddSlashes(GetMessage('PHPSOLUTIONS_BACKTOTOP_REMOVE_SELECTED_IMAGE'))?>' ) ){
        document.getElementById( "delimageparam" ).value = file ;
		document.forms[ "edit1" ].submit() ;
	}
}
</script>