<?
global $DB;
$db_type=strtolower($DB->type);
$arClassesList=array(
	"CMcartXlsProfile" => "classes/".$db_type."/profile.php",
	"CMcartXlsStrRef" => "classes/".$db_type."/str.php"
);

	foreach ($arClassesList as $sClassName => $sClassFile){
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/".$sClassFile);

}

?>
