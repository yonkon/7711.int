<?
if(!$USER->IsAdmin())
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$module_id = "softeffect.xls";
$PDFIT_RIGHT = $APPLICATION->GetGroupRight($module_id);

$arGROUPS = array();
$groups = array();
$z = CGroup::GetList(($v1=""), ($v2=""), array("ACTIVE"=>"Y", "ADMIN"=>"N", "ANONYMOUS"=>"N"));
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;

	$groups[$zr["ID"]] = $zr["NAME"]." [".$zr["ID"]."]";
}

$arAllOptions = Array(
	array("softeffect_xls_group_users", GetMessage("SOFTEFFECT_XLS_GROUP_USERS"), "", Array("multiselectbox", $groups))
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

function ShowParamsHTMLByArray($arParams, $module_id) {
	foreach($arParams as $Option) 	{
	 	__AdmSettingsDrawRow($module_id, $Option);
	}
}

if ($REQUEST_METHOD=="POST" && $PDFIT_RIGHT=="W" && check_bitrix_sessid() && $_REQUEST["SUpdate"]) {
	if (count($_REQUEST['softeffect_xls_group_users'])<=0) {
		$_REQUEST['softeffect_xls_group_users']=array();
	}

	foreach($arAllOptions as $option) {
		__AdmSettingsSaveOption("softeffect.xls", $option);
	}
}
?>
<div align="center" class="adm-info-message-wrap">
	<div class="adm-info-message" style="text-align: left;">
		<?=GetMessage('SE_MODULE_LINK');?>
	</div>
</div>
<?
$tabControl->Begin();
?>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($module_id)?>&lang=<?=LANGUAGE_ID?>">
	<? $tabControl->BeginNextTab(); ?>
	<? ShowParamsHTMLByArray($arAllOptions, $module_id); ?>
	<? $tabControl->Buttons();?>
	<input type="submit" <?if ($PDFIT_RIGHT<"W") echo "disabled" ?> name="SUpdate" value="<?=GetMessage("MAIN_SAVE")?>">
	<input type="hidden" name="Update" value="Y">
	<input type="reset" name="reset" value="<?=GetMessage("MAIN_RESET")?>">
	<?=bitrix_sessid_post();?>
	<? $tabControl->End(); ?>
</form>