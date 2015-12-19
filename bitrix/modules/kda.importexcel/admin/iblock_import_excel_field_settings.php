<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
CModule::IncludeModule('iblock');
CModule::IncludeModule('kda.importexcel');
$bCurrency = CModule::IncludeModule("currency");
IncludeModuleLangFile(__FILE__);
CJSCore::Init(array('kda_importexcel'));

$fl = new CKDAFieldList();
$arFieldGroups = $fl->GetFields($_REQUEST['IBLOCK_ID']);
$arFields = array();
if(is_array($arFieldGroups))
{
	foreach($arFieldGroups as $arGroup)
	{
		if(is_array($arGroup['items']))
		{
			$arFields = array_merge($arFields, $arGroup['items']);
		}
	}
}

$field = $_REQUEST['field'];
$obJSPopup = new CJSPopup();
$obJSPopup->ShowTitlebar(GetMessage("KDA_IE_SETTING_UPLOAD_FIELD").($arFields[$field] ? ' "'.$arFields[$field].'"' : ''));

$oProfile = new CKDAImportProfile();
$oProfile->ApplyExtra($PEXTRASETTINGS, $_REQUEST['PROFILE_ID']);

if($_POST['action']=='save_margin_template')
{
	$arMarginTemplates = CKDAImportExtrasettings::SaveMarginTemplate($_POST);
}
elseif($_POST['action']=='delete_margin_template')
{
	$arMarginTemplates = CKDAImportExtrasettings::DeleteMarginTemplate($_POST['template_id']);
}
elseif($_POST['action']=='save' && is_array($_POST['EXTRASETTINGS']))
{
	CKDAImportExtrasettings::HandleParams($PEXTRASETTINGS, $_POST['EXTRASETTINGS']);
	$oProfile->UpdateExtra($_REQUEST['PROFILE_ID'], $PEXTRASETTINGS);
	
	preg_match_all('/\[(\d+)\]/', $_GET['field_name'], $keys);
	$oid = 'field_settings_'.$keys[1][0].'_'.$keys[1][1];
	if(!empty($PEXTRASETTINGS[$keys[1][0]][$keys[1][1]])) echo '<script>$("#'.$oid.'").removeClass("inactive");</script>';
	else echo '<script>$("#'.$oid.'").addClass("inactive");</script>';
	echo '<script>BX.WindowManager.Get().Close();</script>';
	die();
}

$bPrice = false;
if(strncmp($field, "ICAT_PRICE", 10) == 0 || $field=="ICAT_PURCHASING_PRICE")
{
	$bPrice = true;
	if($bCurrency)
	{
		$arCurrency = array();
		$lcur = CCurrency::GetList(($by="sort"), ($order1="asc"), LANGUAGE_ID);
		while($arr = $lcur->Fetch())
		{
			$arCurrency[] = array(
				'CURRENCY' => $arr['CURRENCY'],
				'FULL_NAME' => $arr['FULL_NAME']
			);
		}
	}
}

$bPicture = false;
if(strncmp($field, "IP_PROP", 7) == 0)
{
	$propId = intval(substr($field, 7));
	$dbRes = CIBlockProperty::GetList(array(), array('ID'=>$propId));
	if($arProp = $dbRes->Fetch())
	{
		if($arProp['PROPERTY_TYPE']=='F')
		{
			$bPicture = true;
		}
	}
}
?>
<form action="" method="post" enctype="multipart/form-data" name="field_settings">
	<input type="hidden" name="action" value="save">
	<table width="100%">
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_IE_SETTINGS_FILTER"); ?></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_IE_SETTINGS_FILTER_UPLOAD");?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				$fName = 'EXTRA'.str_replace('[FIELDS_LIST]', '', $_GET['field_name']).'[UPLOAD_VALUES]';
				$arVals = array();
				if(is_array($PEXTRASETTINGS))
				{
					eval('$arVals = $P'.$fName.';');
				}
				$fName .= '[]';
				if(is_array($arVals) && count($arVals) > 0)
				{
					foreach($arVals as $k=>$v)
					{
						echo '<div><input type="text" name="'.$fName.'" value="'.htmlspecialchars($v).'"></div>';
					}
				}
				else
				{
					echo '<div><input type="text" name="'.$fName.'" value=""></div>';
				}
				?>
				<a href="javascript:void(0)" onclick="ESettings.AddValue(this)"><?echo GetMessage("KDA_IE_ADD_VALUE");?></a>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_IE_SETTINGS_FILTER_NOT_UPLOAD");?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				$fName = 'EXTRA'.str_replace('[FIELDS_LIST]', '', $_GET['field_name']).'[NOT_UPLOAD_VALUES]';
				$arVals = array();
				if(is_array($PEXTRASETTINGS))
				{
					eval('$arVals = $P'.$fName.';');
				}
				$fName .= '[]';
				if(is_array($arVals) && count($arVals) > 0)
				{
					foreach($arVals as $k=>$v)
					{
						echo '<div><input type="text" name="'.$fName.'" value="'.htmlspecialchars($v).'"></div>';
					}
				}
				else
				{
					echo '<div><input type="text" name="'.$fName.'" value=""></div>';
				}
				?>
				<a href="javascript:void(0)" onclick="ESettings.AddValue(this)"><?echo GetMessage("KDA_IE_ADD_VALUE");?></a>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_IE_USE_FILTER_FOR_DEACTIVATE");?>:</td>
			<td class="adm-detail-content-cell-r">
				<?
				$fName = 'EXTRA'.str_replace('[FIELDS_LIST]', '', $_GET['field_name']).'[USE_FILTER_FOR_DEACTIVATE]';
				eval('$val = $P'.$fName.';');
				?>
				<input type="checkbox" name="<?=$fName?>" value="Y" <?=($val=='Y' ? 'checked' : '')?>>
			</td>
		</tr>
		
		<?if($bPrice){
			$fName = 'EXTRA'.str_replace('[FIELDS_LIST]', '', $_GET['field_name']).'[MARGINS]';
			eval('$val = $P'.$fName.';');
			$arMarginTemplates = CKDAImportExtrasettings::GetMarginTemplates(($pfile=''));
			$showMargin = true;
			if($_POST['action']=='load_margin_template' && is_array($arMarginTemplates[$_POST['template_id']]))
			{
				$val = $arMarginTemplates[$_POST['template_id']]['MARGINS'];
			}
			if(!is_array($val) || count($val)==0)
			{
				$showMargin = false;
				$val = array(
					'TYPE' => 1,
					'PERCENT' => '',
					'PRICE_FROM' => '',
					'PRICE_TO' => ''
				);
			}
			?>
			<tr class="heading">
				<td colspan="2">
					<div class="kda-ie-settings-header-links">
						<div class="kda-ie-settings-header-links-inner">
							<a href="javascript:void(0)" onclick="ESettings.ShowMarginTemplateBlockLoad(this)"><?echo GetMessage("KDA_IE_SETTINGS_LOAD_TEMPLATE"); ?></a> /
							<a href="javascript:void(0)" onclick="ESettings.ShowMarginTemplateBlock(this)"><?echo GetMessage("KDA_IE_SETTINGS_SAVE_TEMPLATE"); ?></a>
						</div>
						<div class="kda-ie-settings-margin-templates" id="margin_templates">
							<div class="kda-ie-settings-margin-templates-inner">
								<?echo GetMessage("KDA_IE_SETTINGS_MARGIN_CHOOSE_EXISTS_TEMPLATE"); ?><br>
								<select name="MARGIN_TEMPLATE_ID">
									<option value=""><?echo GetMessage("KDA_IE_SETTINGS_MARGIN_NOT_CHOOSE"); ?></option>
									<?
									foreach($arMarginTemplates as $key=>$template)
									{
										?><option value="<?=$key?>"><?=$template['TITLE']?></option><?
									}
									?>
								</select><br>
								<?echo GetMessage("KDA_IE_SETTINGS_MARGIN_NEW_TEMPLATE"); ?><br>
								<input type="text" name="MARGIN_TEMPLATE_NAME" value="" placeholder="<?echo GetMessage("KDA_IE_SETTINGS_MARGIN_TEMPLATE_NAME"); ?>"><br>
								<input type="submit" onclick="return ESettings.SaveMarginTemplate(this, '<?echo GetMessage("KDA_IE_SETTINGS_TEMPLATE_SAVED"); ?>');" name="save" value="<?echo GetMessage("KDA_IE_SETTINGS_SAVE_BTN"); ?>">
							</div>
						</div>
						<div class="kda-ie-settings-margin-templates" id="margin_templates_load">
							<div class="kda-ie-settings-margin-templates-inner">
								<?echo GetMessage("KDA_IE_SETTINGS_MARGIN_CHOOSE_TEMPLATE"); ?><br>
								<select name="MARGIN_TEMPLATE_ID">
									<option value=""><?echo GetMessage("KDA_IE_SETTINGS_MARGIN_NOT_CHOOSE"); ?></option>
									<?
									foreach($arMarginTemplates as $key=>$template)
									{
										?><option value="<?=$key?>"><?=$template['TITLE']?></option><?
									}
									?>
								</select><br>
								<a href="javascript:void(0)" onclick="ESettings.RemoveMarginTemplate(this, '<?echo GetMessage("KDA_IE_SETTINGS_TEMPLATE_DELETED"); ?>')" title="<?echo GetMessage("KDA_IE_SETTINGS_DELETE"); ?>" class="delete"></a>
								<input type="submit" onclick="return ESettings.LoadMarginTemplate(this);" name="save" value="<?echo GetMessage("KDA_IE_SETTINGS_LOAD_BTN"); ?>">
							</div>
						</div>
					</div>
					<?echo GetMessage("KDA_IE_SETTINGS_MARGIN_TITLE"); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="kda-ie-settings-margin-container">
					<div id="settings_margins">
						<?
						foreach($val as $k=>$v)
						{
						?>
							<div class="kda-ie-settings-margin" style="display: <?=($showMargin ? 'block' : 'none')?>;">
								<?echo GetMessage("KDA_IE_SETTINGS_APPLY"); ?> <select name="<?=$fName?>[TYPE][]"><option value="1" <?=($v['TYPE']==1 ? 'selected' : '')?>><?echo GetMessage("KDA_IE_SETTINGS_APPLY_MARGIN"); ?></option><option value="-1" <?=($v['TYPE']==-1 ? 'selected' : '')?>><?echo GetMessage("KDA_IE_SETTINGS_APPLY_DISCOUNT"); ?></option></select>
								<input type="text" name="<?=$fName?>[PERCENT][]" value="<?=htmlspecialcharsex($v['PERCENT'])?>">%
								<?echo GetMessage("KDA_IE_SETTINGS_AT_PRICE"); ?> <?echo GetMessage("KDA_IE_SETTINGS_FROM"); ?> <input type="text" name="<?=$fName?>[PRICE_FROM][]" value="<?=htmlspecialcharsex($v['PRICE_FROM'])?>">
								<?echo GetMessage("KDA_IE_SETTINGS_TO"); ?> <input type="text" name="<?=$fName?>[PRICE_TO][]" value="<?=htmlspecialcharsex($v['PRICE_TO'])?>">
								<a href="javascript:void(0)" onclick="ESettings.RemoveMargin(this)" title="<?echo GetMessage("KDA_IE_SETTINGS_DELETE"); ?>" class="delete"></a>
							</div>
						<?
						}
						?>
						<input type="button" value="<?echo GetMessage("KDA_IE_SETTINGS_ADD_MARGIN_DISCOUNT"); ?>" onclick="ESettings.AddMargin(this)">
					</div>
				</td>
			</tr>
		<?}
		
		
		
		
		if($bPicture)
		{
			$arFieldNames = array(
				'SCALE',
				'WIDTH',
				'HEIGHT',
				'IGNORE_ERRORS_DIV',
				'IGNORE_ERRORS',
				'METHOD_DIV',
				'METHOD',
				'COMPRESSION',
				'USE_WATERMARK_FILE',
				'WATERMARK_FILE',
				'WATERMARK_FILE_ALPHA',
				'WATERMARK_FILE_POSITION',
				'USE_WATERMARK_TEXT',
				'WATERMARK_TEXT',
				'WATERMARK_TEXT_FONT',
				'WATERMARK_TEXT_COLOR',
				'WATERMARK_TEXT_SIZE',
				'WATERMARK_TEXT_POSITION',
			);
			$arFields = array();
			foreach($arFieldNames as $k=>$field)
			{
				$arFields[$field] = array(
					'NAME' => 'EXTRA'.str_replace('[FIELDS_LIST]', '', $_GET['field_name']).'[PICTURE_PROCESSING]['.$field.']',
					'VALUE' => eval('return $PEXTRA'.str_replace('[FIELDS_LIST]', '', $_GET['field_name']).'[PICTURE_PROCESSING]['.$field.'];')
				);
			}
			?>
			<tr class="heading">
				<td colspan="2"><?echo GetMessage("KDA_IE_SETTINGS_PICTURE_PROCESSING"); ?></td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"></td>
				<td class="adm-detail-content-cell-r">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="<?echo $arFields['SCALE']['NAME']?>"
							name="<?echo $arFields['SCALE']['NAME']?>"
							<?
							if($arFields['SCALE']['VALUE']==="Y")
								echo "checked";
							?>
							onclick="
								BX('DIV_<?echo $arFields['WIDTH']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['HEIGHT']['NAME']?>').style.display =
								/*BX('DIV_<?echo $arFields['IGNORE_ERRORS_DIV']['NAME']?>').style.display =*/
								BX('DIV_<?echo $arFields['METHOD_DIV']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['COMPRESSION']['NAME']?>').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="<?echo $arFields['SCALE']['NAME']?>"
						><?echo GetMessage("KDA_IE_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WIDTH']['NAME']?>"
					style="padding-left:16px;display:<?
						echo ($arFields['SCALE']['VALUE']==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WIDTH")?>:&nbsp;<input name="<?echo $arFields['WIDTH']['NAME']?>" type="text" value="<?echo htmlspecialcharsbx($arFields['WIDTH']['VALUE'])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['HEIGHT']['NAME']?>"
					style="padding-left:16px;display:<?
						echo ($arFields['SCALE']['VALUE']==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_HEIGHT")?>:&nbsp;<input name="<?echo $arFields['HEIGHT']['NAME']?>" type="text" value="<?echo htmlspecialcharsbx($arFields['HEIGHT']['VALUE'])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['IGNORE_ERRORS_DIV']['NAME']?>"
					style="padding-left:16px;display:<?
						//echo ($arFields['SCALE']['VALUE']==="Y")? 'block': 'none';
						echo 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="<?echo $arFields['IGNORE_ERRORS']['NAME']?>"
							name="<?echo $arFields['IGNORE_ERRORS']['NAME']?>"
							<?
							if($arFields['IGNORE_ERRORS']['VALUE']==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="<?echo $arFields['IGNORE_ERRORS']['NAME']?>"
						><?echo GetMessage("KDA_IE_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['METHOD_DIV']['NAME']?>"
					style="padding-left:16px;display:<?
						echo ($arFields['SCALE']['VALUE']==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="<?echo $arFields['METHOD']['NAME']?>"
							name="<?echo $arFields['METHOD']['NAME']?>"
							<?
								if($arFields['METHOD']['VALUE']==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="<?echo $arFields['METHOD']['NAME']?>"
						><?echo GetMessage("KDA_IE_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['COMPRESSION']['NAME']?>"
					style="padding-left:16px;display:<?
						echo ($arFields['SCALE']['VALUE']==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_COMPRESSION")?>:&nbsp;<input
						name="<?echo $arFields['COMPRESSION']['NAME']?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['COMPRESSION']['VALUE'])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="<?echo $arFields['USE_WATERMARK_FILE']['NAME']?>"
							name="<?echo $arFields['USE_WATERMARK_FILE']['NAME']?>"
							<?
							if($arFields['USE_WATERMARK_FILE']['VALUE']==="Y")
								echo "checked";
							?>
							onclick="
								BX('DIV_<?echo $arFields['USE_WATERMARK_FILE']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['WATERMARK_FILE_ALPHA']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['WATERMARK_FILE_POSITION']['NAME']?>').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="<?echo $arFields['USE_WATERMARK_FILE']['NAME']?>"
						><?echo GetMessage("KDA_IE_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['USE_WATERMARK_FILE']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_FILE']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClick".strtr($_GET['field_name'], array('['=>'_', ']'=>'_')),
						"arResultDest" => array("ELEMENT_ID" => strtr($arFields['WATERMARK_FILE']['NAME'], array('['=>'_', ']'=>'_'))),
						"arPath" => array("PATH" => GetDirPath($arFields['WATERMARK_FILE']['VALUE'])),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="<?echo $arFields['WATERMARK_FILE']['NAME']?>"
						id="<?echo strtr($arFields['WATERMARK_FILE']['NAME'], array('['=>'_', ']'=>'_'))?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['WATERMARK_FILE']['VALUE'])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?echo strtr($_GET['field_name'], array('['=>'_', ']'=>'_'))?>()">
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WATERMARK_FILE_ALPHA']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_FILE']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="<?echo $arFields['WATERMARK_FILE_ALPHA']['NAME']?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['WATERMARK_FILE_ALPHA']['VALUE'])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WATERMARK_FILE_POSITION']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_FILE']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						$arFields['WATERMARK_FILE_POSITION']['NAME'],
						IBlockGetWatermarkPositions(),
						"",
						$arFields['WATERMARK_FILE_POSITION']['VALUE']
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="<?echo $arFields['USE_WATERMARK_TEXT']['NAME']?>"
							name="<?echo $arFields['USE_WATERMARK_TEXT']['NAME']?>"
							<?
							if($arFields['USE_WATERMARK_TEXT']['VALUE']==="Y")
								echo "checked";
							?>
							onclick="
								BX('DIV_<?echo $arFields['USE_WATERMARK_TEXT']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['WATERMARK_TEXT_FONT']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['WATERMARK_TEXT_COLOR']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['WATERMARK_TEXT_SIZE']['NAME']?>').style.display =
								BX('DIV_<?echo $arFields['WATERMARK_TEXT_POSITION']['NAME']?>').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="<?echo $arFields['USE_WATERMARK_TEXT']['NAME']?>"
						><?echo GetMessage("KDA_IE_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['USE_WATERMARK_TEXT']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_TEXT']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="<?echo $arFields['WATERMARK_TEXT']['NAME']?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['WATERMARK_TEXT']['VALUE'])?>"
						size="35"
					>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClickFont".strtr($_GET['field_name'], array('['=>'_', ']'=>'_')),
						"arResultDest" => array("ELEMENT_ID" => strtr($arFields['WATERMARK_TEXT_FONT']['NAME'], array('['=>'_', ']'=>'_'))),
						"arPath" => array("PATH" => GetDirPath($arFields['WATERMARK_TEXT_FONT']['VALUE'])),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WATERMARK_TEXT_FONT']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_TEXT']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="<?echo $arFields['WATERMARK_TEXT_FONT']['NAME']?>"
						id="<?echo strtr($arFields['WATERMARK_TEXT_FONT']['NAME'], array('['=>'_', ']'=>'_'))?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['WATERMARK_TEXT_FONT']['VALUE'])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?echo strtr($_GET['field_name'], array('['=>'_', ']'=>'_'))?>()"
					>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WATERMARK_TEXT_COLOR']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_TEXT']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="<?echo $arFields['WATERMARK_TEXT_COLOR']['NAME']?>"
						id="<?echo $arFields['WATERMARK_TEXT_COLOR']['NAME']?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['WATERMARK_TEXT_COLOR']['VALUE'])?>"
						size="7"
					><script>
						function <?echo $_GET['field_name']?>WATERMARK_TEXT_COLOR(color)
						{
							BX('<?echo $arFields['WATERMARK_TEXT_COLOR']['NAME']?>').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							array(
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $_GET['field_name']."WATERMARK_TEXT_COLOR",
							)
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WATERMARK_TEXT_SIZE']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['USE_WATERMARK_TEXT']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="<?echo $arFields['WATERMARK_TEXT_SIZE']['NAME']?>"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields['WATERMARK_TEXT_SIZE']['VALUE'])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="DIV_<?echo $arFields['WATERMARK_TEXT_POSITION']['NAME']?>"
					style="padding-left:16px;display:<?
						if($arFields['WATERMARK_TEXT_POSITION']['VALUE']==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("KDA_IE_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						$arFields['WATERMARK_TEXT_POSITION']['NAME'],
						IBlockGetWatermarkPositions(),
						"",
						$arFields['WATERMARK_TEXT_POSITION']['VALUE']
					);?>
				</div>
				</td>
			</tr>
		<?}?>
		
		
		
		
		
		<?/*if($bPrice && !empty($arCurrency)){?>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_IE_FIELD_CURRENCY");?>:</td>
				<td class="adm-detail-content-cell-r">			
					<select name="CURRENT_CURRENCY">
					<?
					$lcur = CCurrency::GetList(($by="sort"), ($order1="asc"), LANGUAGE_ID);
					foreach($arCurrency as $item)
					{
						?><option value="<?echo $item['CURRENCY']?>">[<?echo $item['CURRENCY']?>] <?echo $item['FULL_NAME']?></option><?
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_IE_CONVERT_CURRENCY");?>:</td>
				<td class="adm-detail-content-cell-r">			
					<select name="CONVERT_CURRENCY">
						<option value=""><?echo GetMessage("KDA_IE_CONVERT_NO_CHOOSE");?></option>
					<?
					$lcur = CCurrency::GetList(($by="sort"), ($order1="asc"), LANGUAGE_ID);
					foreach($arCurrency as $item)
					{
						?><option value="<?echo $item['CURRENCY']?>">[<?echo $item['CURRENCY']?>] <?echo $item['FULL_NAME']?></option><?
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l"><?echo GetMessage("KDA_IE_PRICE_MARGIN");?>:</td>
				<td class="adm-detail-content-cell-r">			
					<input type="text" name="PRICE_MARGIN" value="0" size="5"> %
				</td>
			</tr>
		<?}*/?>
	</table>
</form>