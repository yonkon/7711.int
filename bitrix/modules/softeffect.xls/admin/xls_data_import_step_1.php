<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<tr>
	<td colspan="2">
		<div class="block-title block-title-first">
			<div class="block-title-inner"><?=GetMessage("SOFTEFFECT_XLS_PROFILE_HEADER")?></div>
		</div>
	</td>
</tr>
<tr>
	<td style="width: 270px; vertical-align:top;"><?=GetMessage("SOFTEFFECT_XLS_PROFILE_SELECT")?></td>
	<td>
		<div class="profile_select">
			<select name="XLS_PROFILE_SELECT" style="min-width:200px;" onchange="xlsProfileSelectChange(this)">
				<option disabled="true" selected="selected"><?=GetMessage("SOFTEFFECT_XLS_PROFILE_PROFILE_MESSAGE")?></option>
				<?
				$XlsDbProfiles = CSofteffectXlsProfile::getAll();
				while ($profile = $XlsDbProfiles->getNext()) {
					if (strlen($profile['name'])>0) { ?>
						<option value="<?=$profile['id']?>" <?if ($XLS_PROFILE_SELECT == $profile['id']) echo 'selected'?>><?=$profile['name']?></option>
					<?
					}
				}
				?>
				<option value="new"><?=GetMessage("SOFTEFFECT_XLS_PROFILE_NEW");?></option>
			</select><br>
			<input type="image" name="XLS_CHANGE_PROFILE" id="xls_change_profile" src="/bitrix/images/softeffect.xls/load.png" title="<?=GetMessage("SOFTEFFECT_XLS_PROFILE_UPLOAD_TITLE")?>" <?if (!$XLS_PROFILE_SELECT || $XLS_PROFILE_SELECT == 'new') echo 'disabled'?> />
			<?/*<input type="image" name="XLS_PROFILE_RENAME" id="xls_profile_rename" src="/bitrix/images/softeffect.xls/rename.png" title="<?=GetMessage("SOFTEFFECT_XLS_PROFILE_RENAME_TITLE")?>" style="margin-top: 7px; width: 20px; margin-right: 10px;" <?if (!$XLS_PROFILE_SELECT || $XLS_PROFILE_SELECT == 'new') echo 'disabled'?> />*/?>
			<a href="#" onclick="xlsRenameProfile();" id="xls_profile_rename_link" style="display: none;"><img width="20" src="/bitrix/images/softeffect.xls/rename.png" alt="<?=GetMessage("SOFTEFFECT_XLS_PROFILE_RENAME_TITLE")?>" title="<?=GetMessage("SOFTEFFECT_XLS_PROFILE_RENAME_TITLE")?>"></a>
			<input type="image" name="XLS_DELETE_PROFILE" id="xls_delete_profile" src="/bitrix/images/softeffect.xls/delete.png" title="<?=GetMessage("SOFTEFFECT_XLS_PROFILE_DELETE_TITLE")?>" <?if (!$XLS_PROFILE_SELECT || $XLS_PROFILE_SELECT == 'new') echo 'disabled'?> onclick="return xlsConfirmDeleteProfile('<?=GetMessage("SOFTEFFECT_XLS_A_YOU_SURE")?>')" />
		</div>
		<div>
			<?=GetMessage("SOFTEFFECT_XLS_PROFILE_COMMENT");?>
		</div>
		<br clear="both" />
	</td>
</tr>
<tr>
	<td><?=GetMessage("SOFTEFFECT_XLS_PROFILE_NAME")?></td>
	<td>
		<input type="text" name="XLS_PROFILE_NAME" id="xls_profile_name" style="min-width:200px;"<?if ($XLS_PROFILE_SELECT != 'new') { ?> disabled="disabled"<? } ?> onkeyup="checkNameProfile(this.value);" value="" />
		<input type="image" name="XLS_PROFILE_RENAME" value="Y" id="xls_profile_rename" src="/bitrix/images/softeffect.xls/ok.png" title="<?=GetMessage("SOFTEFFECT_XLS_PROFILE_RENAME_TITLE")?>" style="width: 20px; position: relative; top: 5px; display: none;" />
	</td>
</tr>
<tr>
	<td colspan="2">
		<div class="block-title">
			<div class="block-title-inner"><?=GetMessage("SOFTEFFECT_XLS_FILE_HEADER")?></div>
		</div>
	</td>
</tr>
<tr>
	<td><?=GetMessage("SOFTEFFECT_XLS_IBLOCK_ADM_IMP_DATA_FILE")?></td>
	<td>
		<?
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].$XLS_DATA_FILE)) {
			?><span style="color: red; font-size: 12px;"><?=str_replace('#FILE_NAME#', $XLS_DATA_FILE, GetMessage("SOFTEFFECT_XLS_IBLOCK_ADM_IMP_DATA_FILE_ERROR"))?></span><br /><br /><?
			unset($XLS_DATA_FILE);
		}
		?>
		<?=CFileInput::Show("XLS_DATA_FILE", ($XLS_DATA_FILE ? $XLS_DATA_FILE: 0), array(),
			array(
				'upload' => true,
				'medialib' => true,
				'file_dialog' => true,
				'cloud' => true,
				'del' => false,
				'description' => false,
			)
		);
		?>
		<?/*<input type="text" name="XLS_DATA_FILE" value="<?=htmlspecialcharsbx($XLS_DATA_FILE)?>" size="30" style="width: 215px;" />
		<input type="button" value="<?=GetMessage("SOFTEFFECT_XLS_IBLOCK_ADM_IMP_OPEN")?>" OnClick="BtnClick()" />
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick",
				"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "XLS_DATA_FILE"),
				"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'xls,xlsx',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>*/?>
		<?=GetMessage("SOFTEFFECT_XLS_ONLY_XLSX")?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("SOFTEFFECT_XLS_IBLOCK_ADM_IMP_INFOBLOCK")?></td>
	<td>
		<?=CSofteffectXlsHelper::GetIBlockDropDownList($XLS_IBLOCK_ID, $XLS_IBLOCK_SECTION_ID, 'XLS_IBLOCK_TYPE_ID', 'XLS_IBLOCK_ID', 'XLS_IBLOCK_SECTION_ID');?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<div class="block-title">
			<div class="block-title-inner"><?=GetMessage("SOFTEFFECT_XLS_STEP1_NOTIF_TITLE")?></div>
		</div>
	</td>
</tr>
<tr>
	<td colspan="2"><?=GetMessage("SOFTEFFECT_XLS_STEP1_NOTIF")?></td>
</tr>
<?