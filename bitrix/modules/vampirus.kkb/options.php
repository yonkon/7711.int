<?
include_once(dirname(__FILE__)."/include.php");

$module_id = CVampiRUSKKBPayment::getModuleId();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = Array(
	Array("merchant_id", GetMessage("VAMPIRUS.KKB_OPTIONS_MERCHANT_ID")." ", Array("text", ""), GetMessage("VAMPIRUS.KKB_OPTIONS_MERCHANT_ID_DESC")),
	Array("certificate_id", GetMessage("VAMPIRUS.KKB_OPTIONS_CERTIFICATE_ID")." ", Array("text", ""), GetMessage("VAMPIRUS.KKB_OPTIONS_CERTIFICATE_ID_DESC")),
	Array("private_key_pass", GetMessage("VAMPIRUS.KKB_OPTIONS_PRIVATE_KEY_PASS")." ", Array("text", ""), GetMessage("VAMPIRUS.KKB_OPTIONS_PRIVATE_KEY_PASS_DESC")),
	Array("merchant_name", GetMessage("VAMPIRUS.KKB_OPTIONS_MERCHANT_NAME")." ", Array("text", ""), GetMessage("VAMPIRUS.KKB_OPTIONS_MERCHANT_NAME_DESC")),
	Array("private_key_fn", GetMessage("VAMPIRUS.KKB_OPTIONS_PRIVATE_KEY_FN")." ", Array("text", ""), GetMessage("VAMPIRUS.KKB_OPTIONS_PRIVATE_KEY_FN_DESC")),
	Array("public_key_fn", GetMessage("VAMPIRUS.KKB_OPTIONS_PUBLIC_KEY_FN")." ", Array("text", ""), GetMessage("VAMPIRUS.KKB_OPTIONS_PUBLIC_KEY_FN_DESC")),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => $module_id."_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply)>0 && check_bitrix_sessid())
{
	foreach($arAllOptions as $arOption) {
		$name=$arOption[0];
		$val=$_POST[$name];
		COption::SetOptionString($module_id, $name, $val, $arOption[1]);
	}

	if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}


$tabControl->Begin();
?><form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>"><?
$tabControl->BeginNextTab();
?>
<?	foreach($arAllOptions as $arOption):
		$val = COption::GetOptionString($module_id, $arOption[0]);
		$type = $arOption[2];
	?>
		<tr>
			<td valign="top" width="50%"><?
							echo $arOption[1], "\n<br /><small>", $arOption[3], "</small>";?></td>
			<td valign="top" width="50%">
					<?if($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialchars($val)?>" name="<?echo htmlspecialchars($arOption[0])?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialchars($arOption[0])?>"><?echo htmlspecialchars($val)?></textarea>
					<?endif;?>
			</td>
		</tr>
	<?endforeach?>
<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
