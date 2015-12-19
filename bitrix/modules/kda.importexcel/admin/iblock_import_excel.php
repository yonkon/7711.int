<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule('kda.importexcel');
$bCatalog = CModule::IncludeModule('catalog');
$bCurrency = CModule::IncludeModule("currency");
CJSCore::Init(array('kda_importexcel'));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

include_once(dirname(__FILE__).'/../install/demo.php');
if (kda_importexcel_demo_expired()) {
	require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	kda_importexcel_show_demo();
	require ($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
	die();
}


if(strlen($PROFILE_ID) > 0 && $PROFILE_ID!=='new')
{
	$oProfile = new CKDAImportProfile();
	$oProfile->Apply($SETTINGS_DEFAULT, $SETTINGS, $PROFILE_ID);
	$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);
	
	/*New file storage*/
	if($SETTINGS_DEFAULT['URL_DATA_FILE'] && !$SETTINGS_DEFAULT["DATA_FILE"])
	{
		$filepath = $_SERVER["DOCUMENT_ROOT"].$SETTINGS_DEFAULT['URL_DATA_FILE'];
		if(!file_exists($filepath))
		{
			if(defined("BX_UTF")) $filepath = $APPLICATION->ConvertCharsetArray($filepath, LANG_CHARSET, 'CP1251');
			else $filepath = $APPLICATION->ConvertCharsetArray($filepath, LANG_CHARSET, 'UTF-8');
		}
		$arFile = CFile::MakeFileArray($filepath);
		$fid = CFile::SaveFile($arFile, 'kda.importexcel');
		$SETTINGS_DEFAULT["DATA_FILE"] = $fid;
		$oProfile->Update($PROFILE_ID, $SETTINGS_DEFAULT, $SETTINGS);
	}
	/*/New file storage*/
}

$SHOW_FIRST_LINES = 10;
$SETTINGS_DEFAULT['IBLOCK_ID'] = intval($SETTINGS_DEFAULT['IBLOCK_ID']);
$STEP = intval($STEP);
if ($STEP <= 0)
	$STEP = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["backButton"]) && strlen($_POST["backButton"]) > 0)
	$STEP = $STEP - 2;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["backButton2"]) && strlen($_POST["backButton2"]) > 0)
	$STEP = 1;


$strError = "";
$line_num = 0;
$correct_lines = 0;
$error_lines = 0;
$killed_lines = 0;
$io = CBXVirtualIo::GetInstance();

function ShowTblLine($data, $list, $line, $checked = true)
{
	?><tr><td>
			<input type="hidden" name="SETTINGS[IMPORT_LINE][<?echo $list;?>][<?echo $line;?>]" value="0">
			<input type="checkbox" name="SETTINGS[IMPORT_LINE][<?echo $list;?>][<?echo $line;?>]" value="1" <?if($checked){echo 'checked';}?>>
		</td><?
		foreach($data as $row)
		{
		?><td><div class="cell"><div class="cell_inner"><?echo $row;?></div></div></td><?
		}
	?></tr><?
}
/////////////////////////////////////////////////////////////////////
if ($REQUEST_METHOD == "POST" && $MODE=='AJAX')
{
	if($ACTION=='GET_SECTION_LIST')
	{
		$fl = new CKDAFieldList($SETTINGS_DEFAULT);
		$APPLICATION->RestartBuffer();
		?><div><?
		$fl->ShowSelectSections($IBLOCK_ID, 'sections');
		$fl->ShowSelectFields($IBLOCK_ID, 'fields');
		?></div><?
		die();
	}
	
	if($ACTION=='GET_UID')
	{
		$fl = new CKDAFieldList($SETTINGS_DEFAULT);
		$APPLICATION->RestartBuffer();
		?><div><?
		$fl->ShowSelectUidFields($IBLOCK_ID, 'fields[]');
		$OFFERS_IBLOCK_ID = CKDAImportExcel::GetOfferIblock($IBLOCK_ID);
		if($OFFERS_IBLOCK_ID)
		{
			$fl->ShowSelectUidFields($OFFERS_IBLOCK_ID, 'fields_sku[]', false, 'OFFER_');
		}
		else
		{
			echo '<select name="fields_sku[]" multiple></select>';
		}
		?></div><?
		die();
	}
	
	if($ACTION=='DELETE_PROFILE')
	{
		$fl = new CKDAImportProfile();
		$fl->Delete($_REQUEST['ID']);
		die();
	}
	
	if($ACTION=='RENAME_PROFILE')
	{
		$fl = new CKDAImportProfile();
		$fl->Rename($_REQUEST['ID'], $_REQUEST['NAME']);
		die();
	}
}

if ($REQUEST_METHOD == "POST" && $STEP > 1 && check_bitrix_sessid())
{
	//*****************************************************************//	
	if ($STEP > 1)
	{
		//*****************************************************************//		
		if (strlen($strError) <= 0)
		{
			$DATA_FILE_NAME = "";
			if((isset($_FILES["DATA_FILE"]) && $_FILES["DATA_FILE"]["tmp_name"]) || (isset($_POST['DATA_FILE']) && $_POST['DATA_FILE'] && !is_numeric($_POST['DATA_FILE'])))
			{
				$fid = 0;
				if(isset($_FILES["DATA_FILE"]) && is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
				{
					$fid = CFile::SaveFile($_FILES["DATA_FILE"], 'kda.importexcel');
				}
				elseif(isset($_POST['DATA_FILE']) && strlen($_POST['DATA_FILE']) > 0)
				{
					if(strpos($_POST['DATA_FILE'], '/')===0) 
					{
						$filepath = $_SERVER["DOCUMENT_ROOT"].$_POST['DATA_FILE'];
						if(!file_exists($filepath))
						{
							if(defined("BX_UTF")) $filepath = $APPLICATION->ConvertCharsetArray($filepath, LANG_CHARSET, 'CP1251');
							else $filepath = $APPLICATION->ConvertCharsetArray($filepath, LANG_CHARSET, 'UTF-8');
						}
					}
					else $filepath = $_POST['DATA_FILE'];
					$arFile = CFile::MakeFileArray($filepath);
					$fid = CFile::SaveFile($arFile, 'kda.importexcel');
				}
				
				if(!$fid)
				{
					$strError.= GetMessage("KDA_IE_FILE_UPLOAD_ERROR")."<br>";
				}
				else
				{
					$SETTINGS_DEFAULT["DATA_FILE"] = $fid;
					if($_POST['OLD_DATA_FILE'])
					{
						CFile::Delete($_POST['OLD_DATA_FILE']);
					}
				}
			}
		}
		
		if(!$SETTINGS_DEFAULT["DATA_FILE"] && $_POST['OLD_DATA_FILE'])
		{
			$SETTINGS_DEFAULT["DATA_FILE"] = $_POST['OLD_DATA_FILE'];
		}
		
		if($SETTINGS_DEFAULT["DATA_FILE"])
		{
			$arFile = CFile::GetFileArray($SETTINGS_DEFAULT["DATA_FILE"]);
			$SETTINGS_DEFAULT['URL_DATA_FILE'] = $arFile['SRC'];
		}
		
		if(strlen($PROFILE_ID)==0)
		{
			$strError.= GetMessage("KDA_IE_PROFILE_NOT_CHOOSE")."<br>";
		}

		if (strlen($strError) <= 0)
		{
			if (strlen($DATA_FILE_NAME) <= 0)
			{
				if (strlen($SETTINGS_DEFAULT['URL_DATA_FILE']) > 0)
				{
					$SETTINGS_DEFAULT['URL_DATA_FILE'] = trim(str_replace("\\", "/", trim($SETTINGS_DEFAULT['URL_DATA_FILE'])) , "/");
					$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$SETTINGS_DEFAULT['URL_DATA_FILE']);
					if (
						(strlen($FILE_NAME) > 1)
						&& ($FILE_NAME === "/".$SETTINGS_DEFAULT['URL_DATA_FILE'])
						&& $io->FileExists($_SERVER["DOCUMENT_ROOT"].$FILE_NAME)
						&& ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W")
					)
					{
						$DATA_FILE_NAME = $FILE_NAME;
					}
				}
			}

			if (strlen($DATA_FILE_NAME) <= 0)
				$strError.= GetMessage("KDA_IE_NO_DATA_FILE")."<br>";
			else
				$SETTINGS_DEFAULT['URL_DATA_FILE'] = $DATA_FILE_NAME;
			
			if(ToLower(GetFileExtension($DATA_FILE_NAME))=='xls' && ini_get('mbstring.func_overload')==2)
			{
				$strError.= GetMessage("KDA_IE_FUNC_OVERLOAD_XLS")."<br>";
			}
			
			if(!in_array(ToLower(GetFileExtension($DATA_FILE_NAME)), array('csv', 'xls', 'xlsx', 'xlsm')))
			{
				$strError.= GetMessage("KDA_IE_FILE_NOT_SUPPORT")."<br>";
			}

			if (!CIBlockRights::UserHasRightTo($SETTINGS_DEFAULT['IBLOCK_ID'], $SETTINGS_DEFAULT['IBLOCK_ID'], "element_edit_any_wf_status"))
				$strError.= GetMessage("KDA_IE_NO_IBLOCK")."<br>";
			
			if((!$DATA_FILE_NAME = CKDAImportExcel::GetFileName($DATA_FILE_NAME)))
			{
				$strError.= GetMessage("KDA_IE_FILE_NOT_FOUND")."<br>";
			}
			
			if(empty($SETTINGS_DEFAULT['ELEMENT_UID']))
			{
				$strError.= GetMessage("KDA_IE_NO_ELEMENT_UID")."<br>";
			}
		}
		
		if (strlen($strError) <= 0)
		{
			/*Пишем профиль*/
			$oProfile = new CKDAImportProfile();
			if($PROFILE_ID === 'new')
			{
				$PID = $oProfile->Add($NEW_PROFILE_NAME);
				if($PID===false)
				{
					if($ex = $APPLICATION->GetException())
					{
						$strError .= $ex->GetString().'<br>';
					}
				}
				else
				{
					$PROFILE_ID = $PID;
				}
			}
			/*/Пишем профиль*/
		}

		if (strlen($strError) > 0)
			$STEP = 1;
		//*****************************************************************//

	}
	
	if($ACTION == 'SHOW_FULL_LIST')
	{
		require_once(dirname(__FILE__).'/../lib/PHPExcel/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReaderForFile($_SERVER['DOCUMENT_ROOT'].$DATA_FILE_NAME);
		$objReader->setReadDataOnly(true);
		$chunkFilter = new chunkReadFilter2();
		$objReader->setReadFilter($chunkFilter);
		$chunkFilter->setRows(1, 3000);
		$efile = $objReader->load($_SERVER['DOCUMENT_ROOT'].$DATA_FILE_NAME);
		
		$APPLICATION->RestartBuffer();
		foreach($efile->getWorksheetIterator() as $k=>$worksheet)
		{
			if($k==$LIST_NUMBER)
			{
				$columns_count = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
				$rows_count = $worksheet->getHighestRow();
				$cntLines = $emptyLines = 0;
				for ($row = 0; ($row < $rows_count && $cntLines < $SHOW_FIRST_LINES+$emptyLines); $row++) 
				{
					$arLine = array();
					for ($column = 0; $column < $columns_count; $column++) 
					{
						$val = CKDAImportExcel::GetCalculatedValue($worksheet->getCellByColumnAndRow($column, $row+1));
						$arLine[] = $val;
					}
					$arLineTmp = array_diff($arLine, array(""));
					if(empty($arLineTmp))
					{
						$emptyLines++;
					}
					$cntLines++;
				}
				
				for ($row = $cntLines; $row < $rows_count; $row++) 
				{
					$arLine = array();
					for ($column = 0; $column < $columns_count; $column++) 
					{
						$val = CKDAImportExcel::GetCalculatedValue($worksheet->getCellByColumnAndRow($column, $row+1));
						$arLine[] = $val;
					}
					$arLineTmp = array_diff($arLine, array(""));
					ShowTblLine($arLine, $k, $row);
				}
			}
		}	
		die();
	}
	
	if($ACTION == 'SHOW_REVIEW_LIST')
	{
		$fl = new CKDAFieldList($SETTINGS_DEFAULT);
		$arIblocks = $fl->GetIblocks();
		try{
			$arWorksheets = CKDAImportExcel::GetPreviewData($DATA_FILE_NAME, $SHOW_FIRST_LINES);
		}catch(Exception $ex){
			echo GetMessage("KDA_IE_ERROR").$ex->getMessage();
		}
		if(!$arWorksheets) $arWorksheets = array();
		foreach($arWorksheets as $k=>$worksheet)
		{
			$columns = (count($worksheet['lines']) > 0 ? count($worksheet['lines'][0]) : 1) + 1;
			$bEmptyList = empty($worksheet['lines']);
			$iblockId = ($SETTINGS['IBLOCK_ID'][$k] ? $SETTINGS['IBLOCK_ID'][$k] : $SETTINGS_DEFAULT['IBLOCK_ID']);
		?>
			<table class="kda-ie-tbl <?if($bEmptyList){echo 'empty';}?>" data-list-index="<?echo $k;?>" data-iblock-id=<?echo $iblockId;?>>
				<tr class="heading">
					<td class="left"><?echo GetMessage("KDA_IE_LIST_TITLE"); ?> "<?echo $worksheet['title'];?>" <?if($bEmptyList){echo GetMessage("KDA_IE_EMPTY_LIST");}?></td>
					<td class="right">
						<?if(count($worksheet['lines']) > 0){?>
							<input type="hidden" name="SETTINGS[LIST_LINES][<?echo $k;?>]" value="<?echo $worksheet['lines_count'];?>">
							<input type="hidden" name="SETTINGS[LIST_ACTIVE][<?echo $k;?>]" value="N">
							<input type="checkbox" name="SETTINGS[LIST_ACTIVE][<?echo $k;?>]" id="list_active_<?echo $k;?>" value="Y" <?=(!isset($SETTINGS['LIST_ACTIVE'][$k]) || $SETTINGS['LIST_ACTIVE'][$k]=='Y' ? 'checked' : '')?>> <label for="list_active_<?echo $k;?>"><small><?echo GetMessage("KDA_IE_DOWNLOAD_LIST"); ?></small></label>
							<a href="javascript:void(0)" class="showlist" onclick="EList.ToggleSettings(this)" title="<?echo GetMessage("KDA_IE_LIST_SETTINGS"); ?>"></a>
						<?}?>
					</td>
				</tr>
				<tr class="settings">
					<td colspan="2">
						<table class="additional">
							<tr>
								<td><?echo GetMessage("KDA_IE_INFOBLOCK"); ?> </td>
								<td>
									<select name="SETTINGS[IBLOCK_ID][<?echo $k;?>]" onchange="EList.ChooseIblock(this);">
										<!--<option value=""><?echo GetMessage("KDA_IE_CHOOSE_IBLOCK"); ?></option>-->
										<?
										foreach($arIblocks as $type)
										{
											?><optgroup label="<?echo $type['NAME']?>"><?
											foreach($type['IBLOCKS'] as $iblock)
											{
												?><option value="<?echo $iblock["ID"];?>" <?if($iblock["ID"]==$iblockId){echo 'selected';}?>><?echo htmlspecialcharsbx($iblock["NAME"]); ?></option><?
											}
											?></optgroup><?
										}
										?>
									</select>
								</td>
								<td width="50px">&nbsp;</td>
								<td><?echo GetMessage("KDA_IE_SECTION"); ?> </td>
								<td><?$fl->ShowSelectSections($iblockId, 'SETTINGS[SECTION_ID]['.$k.']', $SETTINGS['SECTION_ID'][$k]);?></td>
							</tr>
						</table>
						<div align="right">
							<a href="javascript:void(0)" onclick="EList.ApplyToAllLists(this)"><?echo GetMessage("KDA_IE_APPLY_TO_ALL_LISTS"); ?></a>
						</div>
						<div class="set_scroll">
							<div></div>
						</div>
						<div class="set">						
						<table class="list">
						<?
						if(count($worksheet['lines']) > 0)
						{
							?>
								<tr>
									<td>
										<input type="hidden" name="SETTINGS[CHECK_ALL][<?echo $k;?>]" value="0"> 
										<input type="checkbox" name="SETTINGS[CHECK_ALL][<?echo $k;?>]" id="check_all_<?echo $k;?>" value="1" <?if(!isset($SETTINGS['CHECK_ALL'][$k]) || $SETTINGS['CHECK_ALL'][$k]){echo 'checked';}?>> 
										<label for="check_all_<?echo $k;?>"><?echo GetMessage("KDA_IE_CHECK_ALL"); ?></label>
									</td>
									<?
									$num_rows = count($worksheet['lines'][0]);
									for($i = 0; $i < $num_rows; $i++)
									{
										?>
										<td>
											<?$fl->ShowSelectFields($iblockId, 'SETTINGS[FIELDS_LIST]['.$k.']['.$i.']', $SETTINGS['FIELDS_LIST'][$k][$i])?>
											<a href="javascript:void(0)" class="field_settings <?=(empty($EXTRASETTINGS[$k][$i]) ? 'inactive' : '')?>" id="field_settings_<?=$k?>_<?=$i?>" title="<?echo GetMessage("KDA_IE_SETTINGS_FIELD"); ?>" onclick="EList.ShowFieldSettings(this);"></a>
										</td>
										<?
									}
									?>
								</tr>
							<?
							
						}			
						
						foreach($worksheet['lines'] as $line=>$arLine)
						{
							$checked = ((!isset($SETTINGS['IMPORT_LINE'][$k][$line]) && (!isset($SETTINGS['CHECK_ALL'][$k]) || $SETTINGS['CHECK_ALL'][$k])) || $SETTINGS['IMPORT_LINE'][$k][$line]);
							ShowTblLine($arLine, $k, $line, $checked);
						}
						?>
						</table>
						</div>
						<?if($worksheet['show_more']){?>
							<input type="button" value="<?echo GetMessage("KDA_IE_SHOW_LIST"); ?>" onclick="EList.ShowFull(this);">
						<?}?>
						<br><br>
					</td>
				</tr>
			</table>
		<?
		}
		die();
	}
	
	/*Обновление профиля*/
	if(strlen($PROFILE_ID) > 0 && $PROFILE_ID!=='new')
	{
		$oProfile->Update($PROFILE_ID, $SETTINGS_DEFAULT, $SETTINGS);
	}
	/*/Обновление профиля*/
	
	if($ACTION == 'DO_IMPORT')
	{
		$oProfile = new CKDAImportProfile();
		$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);
		$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
		$stepparams = $_POST['stepparams'];
		$ie = new CKDAImportExcel($DATA_FILE_NAME, $params, $EXTRASETTINGS, $stepparams, $PROFILE_ID);
		$arResult = $ie->Import();
		echo CUtil::PhpToJSObject($arResult);
		die();
	}
	
	if ($STEP > 2)
	{
		/*$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
		$ie = new CKDAImportExcel($DATA_FILE_NAME, $params);
		$ie->Import();
		die();*/
	}
	//*****************************************************************//

}

/////////////////////////////////////////////////////////////////////
$APPLICATION->SetTitle(GetMessage("KDA_IE_PAGE_TITLE").$STEP);
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

if (!kda_importexcel_demo_expired()) {
	kda_importexcel_show_demo();
}

$aMenu = array(
	array(
		"TEXT"=>GetMessage("KDA_IE_SHOW_CRONTAB"),
		"TITLE"=>GetMessage("KDA_IE_SHOW_CRONTAB"),
		"ONCLICK" => "EProfile.ShowCron();",
		"ICON" => "btn_green",
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

CAdminMessage::ShowMessage($strError);
?>

<form method="POST" action="<?echo $sDocPath ?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload">

<?$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("KDA_IE_TAB1") ,
		"ICON" => "iblock",
		"TITLE" => GetMessage("KDA_IE_TAB1_ALT"),
	) ,
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("KDA_IE_TAB2") ,
		"ICON" => "iblock",
		"TITLE" => GetMessage("KDA_IE_TAB2_ALT"),
	) ,
	array(
		"DIV" => "edit3",
		"TAB" => GetMessage("KDA_IE_TAB3") ,
		"ICON" => "iblock",
		"TITLE" => GetMessage("KDA_IE_TAB3_ALT"),
	) ,
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();
?>

<?$tabControl->BeginNextTab();
if ($STEP == 1)
{
	$fl = new CKDAFieldList($SETTINGS_DEFAULT);
	$oProfile = new CKDAImportProfile();
?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("KDA_IE_PROFILE_HEADER"); ?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("KDA_IE_PROFILE"); ?>:</td>
		<td>
			<?$oProfile->ShowProfileList('PROFILE_ID');?>
			
			<?if(strlen($PROFILE_ID) > 0 && $PROFILE_ID!='new'){?>
				<span class="kda-ie-edit-btns">
					<a href="javascript:void(0)" class="adm-table-btn-edit" onclick="EProfile.ShowRename();" title="<?echo GetMessage("KDA_IE_RENAME_PROFILE");?>" id="action_edit_button"></a>
					<a href="javascript:void(0);" class="adm-table-btn-delete" onclick="EProfile.Delete();" title="<?echo GetMessage("KDA_IE_DELETE_PROFILE");?>" id="action_delete_button"></a>
				</span>
			<?}?>
		</td>
	</tr>
	
	<tr id="new_profile_name">
		<td><?echo GetMessage("KDA_IE_NEW_PROFILE_NAME"); ?>:</td>
		<td>
			<input type="text" name="NEW_PROFILE_NAME" value="<?echo htmlspecialcharsbx($NEW_PROFILE_NAME)?>">
		</td>
	</tr>

	<?
	if(strlen($PROFILE_ID) > 0)
	{
	?>
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_IE_DEFAULT_SETTINGS"); ?></td>
		</tr>
		
		<tr>
			<td width="40%"><?echo GetMessage("KDA_IE_URL_DATA_FILE"); ?></td>
			<td width="60%" class="kda-ie-file-choose">
				<input type="hidden" name="OLD_DATA_FILE" value="<?echo htmlspecialcharsbx($SETTINGS_DEFAULT['DATA_FILE']); ?>">
				<?
				$arFile = CFile::GetFileArray($SETTINGS_DEFAULT["DATA_FILE"]);
				if($arFile['SRC'])
				{
					if(!file_exists($_SERVER['DOCUMENT_ROOT'].$arFile['SRC']))
					{
						if(defined("BX_UTF")) $arFile['SRC'] = $APPLICATION->ConvertCharsetArray($arFile['SRC'], LANG_CHARSET, 'CP1251');
						else $arFile['SRC'] = $APPLICATION->ConvertCharsetArray($arFile['SRC'], LANG_CHARSET, 'UTF-8');
						if(!file_exists($_SERVER['DOCUMENT_ROOT'].$arFile['SRC']))
						{
							unset($SETTINGS_DEFAULT["DATA_FILE"]);
						}
					}
				}
				else
				{
					unset($SETTINGS_DEFAULT["DATA_FILE"]);
				}
				echo CFileInput::Show("DATA_FILE", $SETTINGS_DEFAULT["DATA_FILE"], array(
					"IMAGE" => "N",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "N"
				), array(
					'upload' => true,
					'medialib' => false,
					'file_dialog' => true,
					'cloud' => true,
					'del' => false,
					'description' => false,
				));
				?>
			</td>
		</tr>

		<tr>
			<td><?echo GetMessage("KDA_IE_INFOBLOCK"); ?></td>
			<td>
				<?echo GetIBlockDropDownList($SETTINGS_DEFAULT['IBLOCK_ID'], 'SETTINGS_DEFAULT[IBLOCK_TYPE_ID]', 'SETTINGS_DEFAULT[IBLOCK_ID]', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"'); ?>
			</td>
		</tr>
		
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_IE_SETTINGS_PROCESSING"); ?></td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_UID"); ?>: <span id="hint_ELEMENT_UID"></span><script>BX.hint_replace(BX('hint_ELEMENT_UID'), '<?echo GetMessage("KDA_IE_ELEMENT_UID_HINT"); ?>');</script></td>
			<td>
				<?$fl->ShowSelectUidFields($SETTINGS_DEFAULT['IBLOCK_ID'], 'SETTINGS_DEFAULT[ELEMENT_UID][]', $SETTINGS_DEFAULT['ELEMENT_UID']);?>
			</td>
		</tr>

		<?
		$OFFERS_IBLOCK_ID = CKDAImportExcel::GetOfferIblock($SETTINGS_DEFAULT['IBLOCK_ID']);
		?>	
		<tr <?if(!$OFFERS_IBLOCK_ID){echo 'style="display: none;"';}?> id="element_uid_sku">
			<td><?echo GetMessage("KDA_IE_ELEMENT_UID_SKU"); ?>: <span id="hint_ELEMENT_UID_SKU"></span><script>BX.hint_replace(BX('hint_ELEMENT_UID_SKU'), '<?echo GetMessage("KDA_IE_ELEMENT_UID_SKU_HINT"); ?>');</script></td>
			<td>
			<?
			if($OFFERS_IBLOCK_ID)
			{
				$fl->ShowSelectUidFields($OFFERS_IBLOCK_ID, 'SETTINGS_DEFAULT[ELEMENT_UID_SKU][]', $SETTINGS_DEFAULT['ELEMENT_UID_SKU'], 'OFFER_');
			}
			else
			{
				echo '<select name="SETTINGS_DEFAULT[ELEMENT_UID_SKU][]" multiple></select>';
			}
			?>
			</td>
		</tr>

		<tr>
			<td><?echo GetMessage("KDA_IE_ONLY_UPDATE_MODE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ONLY_UPDATE_MODE]" value="Y" <?if($SETTINGS_DEFAULT['ONLY_UPDATE_MODE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>	
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_NEW_DEACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ELEMENT_NEW_DEACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['ELEMENT_NEW_DEACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_NO_QUANTITY_DEACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ELEMENT_NO_QUANTITY_DEACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['ELEMENT_NO_QUANTITY_DEACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_NO_PRICE_DEACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ELEMENT_NO_PRICE_DEACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['ELEMENT_NO_PRICE_DEACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_MISSING_DEACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ELEMENT_MISSING_DEACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['ELEMENT_MISSING_DEACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_LOADING_ACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ELEMENT_LOADING_ACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['ELEMENT_LOADING_ACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_SECTION_EMPTY_DEACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[SECTION_EMPTY_DEACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['SECTION_EMPTY_DEACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_SECTION_NOTEMPTY_ACTIVATE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[SECTION_NOTEMPTY_ACTIVATE]" value="Y" <?if($SETTINGS_DEFAULT['SECTION_NOTEMPTY_ACTIVATE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_NOT_UPDATE_WO_CHANGES"); ?>: <span id="hint_ELEMENT_NOT_UPDATE_WO_CHANGES"></span><script>BX.hint_replace(BX('hint_ELEMENT_NOT_UPDATE_WO_CHANGES'), '<?echo GetMessage("KDA_IE_ELEMENT_NOT_UPDATE_WO_CHANGES_HINT"); ?>');</script></td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[ELEMENT_NOT_UPDATE_WO_CHANGES]" value="Y" <?if($SETTINGS_DEFAULT['ELEMENT_NOT_UPDATE_WO_CHANGES']=='Y'){echo 'checked';}?>>
			</td>
		</tr>

		<tr>
			<td><?echo GetMessage("KDA_IE_ELEMENT_MULTIPLE_SEPARATOR"); ?>:</td>
			<td>
				<input type="text" name="SETTINGS_DEFAULT[ELEMENT_MULTIPLE_SEPARATOR]" size="3" value="<?echo ($SETTINGS_DEFAULT['ELEMENT_MULTIPLE_SEPARATOR'] ? htmlspecialcharsbx($SETTINGS_DEFAULT['ELEMENT_MULTIPLE_SEPARATOR']) : ';'); ?>">
			</td>
		</tr>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_MAX_SECTION_LEVEL"); ?>:  <span id="hint_MAX_SECTION_LEVEL"></span><script>BX.hint_replace(BX('hint_MAX_SECTION_LEVEL'), '<?echo GetMessage("KDA_IE_MAX_SECTION_LEVEL_HINT"); ?>');</script></td>
			<td>
				<input type="text" name="SETTINGS_DEFAULT[MAX_SECTION_LEVEL]" size="3" value="<?echo (strlen($SETTINGS_DEFAULT['MAX_SECTION_LEVEL']) > 0 ? htmlspecialcharsbx($SETTINGS_DEFAULT['MAX_SECTION_LEVEL']) : '5'); ?>" maxlength="3">
			</td>
		</tr>
		
		
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("KDA_IE_SETTINGS_CATALOG"); ?></td>
		</tr>

		<?if($bCurrency){?>
		<tr>
			<td><?echo GetMessage("KDA_IE_DEFAULT_CURRENCY"); ?>:</td>
			<td>
				<select name="SETTINGS_DEFAULT[DEFAULT_CURRENCY]">
				<?
				$lcur = CCurrency::GetList(($by="sort"), ($order1="asc"), LANGUAGE_ID);
				while($arr = $lcur->Fetch())
				{
					?><option value="<?echo $arr['CURRENCY']?>" <?if($arr['CURRENCY']==$SETTINGS_DEFAULT['DEFAULT_CURRENCY']){echo 'selected';}?>>[<?echo $arr['CURRENCY']?>] <?echo $arr['FULL_NAME']?></option><?
				}
				?>
				</select>
			</td>
		</tr>
		<?}?>
		
		<tr>
			<td><?echo GetMessage("KDA_IE_QUANTITY_TRACE"); ?>:</td>
			<td>
				<input type="checkbox" name="SETTINGS_DEFAULT[QUANTITY_TRACE]" value="Y" <?if($SETTINGS_DEFAULT['QUANTITY_TRACE']=='Y'){echo 'checked';}?>>
			</td>
		</tr>
	<?
	}
}
$tabControl->EndTab();
?>

<?$tabControl->BeginNextTab();
if ($STEP == 2)
{
?>
	
	<tr>
		<td colspan="2" id="preview_file">
			<div class="kda-ie-file-preloader">
				<?echo GetMessage("KDA_IE_PRELOADING"); ?>
			</div>
		</td>
	</tr>
	
	<?
}
$tabControl->EndTab();
?>


<?$tabControl->BeginNextTab();
if ($STEP == 3)
{
?>
	<tr>
		<td id="resblock" class="kda-ie-result">
		 <table width="100%"><tr><td width="50%">
			<div id="progressbar"><span class="pline"></span><span class="presult load"><b>0%</b><span data-prefix="<?echo GetMessage("KDA_IE_READ_LINES"); ?>"><?echo GetMessage("KDA_IE_IMPORT_INIT"); ?></span></span></div>
			
			<div id="block_error" style="display: none;">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "ERROR",
					"MESSAGE" => GetMessage("KDA_IE_IMPORT_ERROR"),
					"DETAILS" => '<div id="res_error"></div>',
					"HTML" => true,
				))?>
			</div>
		 </td><td>
			<div class="detail_status">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "PROGRESS",
					"MESSAGE" => '<!--<div id="res_continue">'.GetMessage("KDA_IE_AUTO_REFRESH_CONTINUE").'</div><div id="res_finish" style="display: none;">'.GetMessage("KDA_IE_SUCCESS").'</div>-->',
					"DETAILS" =>

					GetMessage("KDA_IE_SU_ALL").' <b id="total_line">'.$line_num.'</b><br>'
					.GetMessage("KDA_IE_SU_CORR").' <b id="correct_line">'.$correct_lines.'</b><br>'
					.GetMessage("KDA_IE_SU_ER").' <b id="error_line">'.$error_lines.'</b><br>'
					.GetMessage("KDA_IE_SU_HIDED").' <b id="killed_line">'.$killed_lines.'</b>'
					.'<div id="redirect_message">'.GetMessage("KDA_IE_REDIRECT_MESSAGE").'</div>',
					"HTML" => true,
				))?>
			</div>
		 </td></tr></table>
		</td>
	</tr>
<?
}
$tabControl->EndTab();
?>

<?$tabControl->Buttons();
?>


<?echo bitrix_sessid_post(); ?>
<?
if($STEP > 1)
{
	if(strlen($PROFILE_ID) > 0)
	{
		?><input type="hidden" name="PROFILE_ID" value="<?echo htmlspecialcharsbx($PROFILE_ID) ?>"><?
	}
	else
	{
		foreach($SETTINGS_DEFAULT as $k=>$v)
		{
			?><input type="hidden" name="SETTINGS_DEFAULT[<?echo $k?>]" value="<?echo htmlspecialcharsbx($v) ?>"><?
		}
	}
}
?>


<?
if($STEP == 2){ ?>
<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("KDA_IE_BACK"); ?>">
<?
}

if($STEP < 3)
{
?>
	<input type="hidden" name="STEP" value="<?echo $STEP + 1; ?>">
	<input type="submit" value="<?echo ($STEP == 2) ? GetMessage("KDA_IE_NEXT_STEP_F") : GetMessage("KDA_IE_NEXT_STEP"); ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">
<? 
}
else
{
?>
	<input type="hidden" name="STEP" value="1">
	<input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage("KDA_IE_2_1_STEP"); ?>" class="adm-btn-save">
<?
}
?>

<?$tabControl->End();
?>

</form>

<script language="JavaScript">

<?if ($STEP < 2): ?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?elseif ($STEP == 2): ?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
<?elseif ($STEP > 2): ?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");

EImport.Init(<?=CUtil::PhpToJSObject($_POST);?>);
<?endif; ?>
//-->
</script>

<?
require ($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
?>
