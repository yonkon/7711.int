<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
global $USER;
CModule::IncludeModule('iblock');


if (!$_SESSION['PROFILE_ARRAY']) die('no profile!||The End');

// sozdaem peremennie
foreach ($_SESSION['PROFILE_ARRAY'] as $key => $value) {
	if (strpos($key, 'XLS_')===0) {
		$$key = $value;
	}
}

// s kakogo elementa nachinaem
if ($_SESSION['startRow']) {
	$startRow = $_SESSION['startRow'];
} else {
	$startRow = 0;
}

// kakim elementom zakanchivaem
$elemTo = $startRow+$XLS_STEP5_CHUNKSIZE;
if (!$_SESSION['arElemsChange'][$elemTo]) {
	$elemTo = count($_SESSION['arElemsChange']);
}

// perebiraem elementy
for ($i=$startRow; $i <= $elemTo; $i++) {
	CIBlockElement::UpdateSearch($_SESSION['arElemsChange'][$i], true);
}

// esli sled. elementa net - zakanchivaem
if (isset($_SESSION['arElemsChange'][$elemTo+1])) {
	echo $elemTo;
	$_SESSION['startRow'] = $startRow+$XLS_STEP5_CHUNKSIZE;
} else {
	// очищаем кеш SEO свойств
	$ipropValues = new \Bitrix\Iblock\InheritedProperty\IblockValues($_SESSION['PROFILE_ARRAY']['XLS_IBLOCK_ID']);
	$ipropValues->clearValues();

	unset($_SESSION['startRow']);
	echo $elemTo.'||The End';
}
?>