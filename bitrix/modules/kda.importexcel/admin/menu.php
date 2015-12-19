<?
if (!CModule::IncludeModule("iblock"))
	return false;

IncludeModuleLangFile(__FILE__);

$aMenu = array();

global $USER;
$bUserIsAdmin = $USER->IsAdmin();

$bHasWRight = false;
$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array("MIN_PERMISSION" => "U"));
if($arIBlock = $rsIBlocks->Fetch())
{
	$bHasWRight = true;
}


if($bUserIsAdmin || $bHasWRight)
{
	if($bUserIsAdmin || $bHasWRight)
	{
		$aMenu[] = array(
			"parent_menu" => "global_menu_content",
			"section" => "kda_importexcel",
			"sort" => 400,
			"text" => GetMessage("KDA_MENU_IMPORT_TITLE"),
			"title" => GetMessage("KDA_MENU_IMPORT_TITLE"),
			"icon" => "kda_importexcel_menu_import_icon",
			/*"page_icon" => "iblock_page_icon_settings",*/
			"items_id" => "menu_kda_importexcel",
			"module_id" => "kda.importexcel",
			"url" => "kda_import_excel.php?lang=".LANGUAGE_ID,
		);
	}
}

return $aMenu;
?>