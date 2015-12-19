<?
@set_time_limit(0);
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__).'/../../../..';
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('kda.importexcel');
$PROFILE_ID = $argv[1];

$oProfile = new CKDAImportProfile();
$oProfile->Apply($SETTINGS_DEFAULT, $SETTINGS, $PROFILE_ID);
$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);
$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
$params['MAX_EXECUTION_TIME'] = 0;
$DATA_FILE_NAME = $params['URL_DATA_FILE'];
$ie = new CKDAImportExcel($DATA_FILE_NAME, $params, $EXTRASETTINGS, array());
$arResult = $ie->Import();
echo CUtil::PhpToJSObject($arResult);
?>