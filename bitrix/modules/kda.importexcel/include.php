<?php
include_once(dirname(__FILE__).'/install/demo.php');

CModule::AddAutoloadClasses(
	'kda.importexcel',
	array(
		'CKDAFieldList' => 'classes/general/field_list.php',
		'CKDAImportProfile' => 'classes/general/profile.php',
		'CKDAImportExcel' => 'classes/general/import.php',
		'CKDAImportExtraSettings' => 'classes/general/extrasettings.php'
	)
);

$arJSKdaIBlockConfig = array(
	'kda_importexcel' => array(
		'js' => array('/bitrix/js/kda.importexcel/chosen/chosen.jquery.min.js', '/bitrix/js/kda.importexcel/script.js'),
		'css' => array('/bitrix/js/kda.importexcel/chosen/chosen.min.css', '/bitrix/panel/kda.importexcel/styles.css'),
		'rel' => array('jquery'),
	),
);

foreach ($arJSKdaIBlockConfig as $ext => $arExt) {
	CJSCore::RegisterExt($ext, $arExt);
}
?>