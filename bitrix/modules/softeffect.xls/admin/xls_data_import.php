<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$group_user_right = COption::GetOptionString("softeffect.xls", "softeffect_xls_group_users", "");
if ($group_user_right!="") {
	$arGroups = explode(",", $group_user_right);
}

$arUserGroups = $USER->GetUserGroupArray();

$result = array_intersect($arGroups, $arUserGroups);

if (count($result)<=0 && !$USER->IsAdmin()) {
	die('Access denied');
}

global $USER;

// opredelim tekushhuu kodirovku
$arSite = CSite::GetList($by="sort", $order="desc", array('LANGUAGE_ID' => SITE_ID))->getNext();
if (count($arSite)){
	$charset = $arSite['CHARSET'];
}
else {
	$charset = 'windows-1251';
}

if ($_REQUEST['skipAllSteps']!='') {
	$USER->SetParam('SKIP_ALL_STEPS', "Y");
}

IncludeModuleLangFile(__FILE__);
$APPLICATION->SetTitle(GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_PAGE_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// all handwrite scripts
CJSCore::Init(array("jquery"));
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/softeffect.xls/scripts.js');
$GLOBALS['APPLICATION']->AddHeadString('<link rel="stylesheet" href="/bitrix/css/softeffect.xls/styles.css" />');

set_time_limit(0);

$autoLoadClasses = array(
	'CSofteffectXlsHelper' => 'classes/general/CSofteffectXlsHelper.php',
);

CModule::AddAutoloadClasses(
	'softeffect.xls',
	$autoLoadClasses
);
$DEMO_MODE = CModule::IncludeModuleEx('softeffect.xls');
$arModule = CSofteffectXlsHelper::getDiffDays();
if ($arModule['DIFF_DAYS']<=-10 && $DEMO_MODE==1) { ?>
	<div class="diffdays">
		<img src="/bitrix/images/softeffect.xls/podarok.png" alt="" width="85" align="left" /><?=str_replace(array('#DIFF_DAYS#', '#MODULE_NAME#'), array(abs($arModule['DIFF_DAYS']), $arModule['MODULE_NAME']), GetMessage('SOFTEFFECT_XLS_DIFFDAYS'));?>
	</div>
	<? //die();
} elseif ($DEMO_MODE>=3) {
	?><div class="demo_mode"><?=GetMessage('SOFTEFFECT_XLS_DEMO_MODE_'.$DEMO_MODE);?></div><?
	//die();
}

### Obrabotka zaprosov ot Profilya ###
// Opredelyaem tekushhy Shag
//$_REQUEST['backButton2']='Y';
if ($_REQUEST['backButton2']) {
	$XLS_STEP = 1;

	// clear params in session
	$USER->SetParam('XLS_PROFILE_SELECT', "");
	$USER->SetParam('XLS_PROFILE_NAME', "");
	$USER->SetParam('SKIP_ALL_STEPS', "");

	// 1st step params
	$USER->SetParam('XLS_DATA_FILE', "");
	$USER->SetParam('XLS_DATA_FILE_ID', "");
	$USER->SetParam('XLS_IBLOCK_TYPE_ID', "");
	$USER->SetParam('XLS_IBLOCK_ID', "");
	$USER->SetParam('XLS_IBLOCK_SECTION_ID', "");
	$USER->SetParam('XLS_PROFILE_SELECT', "");

	// 2nd step params
	$USER->SetParam('XLS_SELECT_IBLOCK_PROPERTY', "");
	$USER->SetParam('XLS_SELECT_TYPE_PROPERTY', "");
	$USER->SetParam('XLS_SELECT_UPLOAD_PROPERTY', "");
	$USER->SetParam('XLS_PROPERTY_MAIN', "");

	// 3d step params
	$USER->SetParam('XLS_LIST', "");
	$USER->SetParam('XLS_FIRST_ROW_HEADER', "");
	$USER->SetParam('XLS_FIRST_COLUMN_HEADER', "");
	$USER->SetParam('XLS_FIRST_ROW', "");
	$USER->SetParam('XLS_FIRST_COLUMN', "");
	$USER->SetParam('XLS_DEPTH_LEVEL_MAX', "");
	$USER->SetParam('XLS_DEACTIVATE_ELEMENTS', "");
	$USER->SetParam('XLS_BITRIX_UNACTIVE', "");
	$USER->SetParam('XLS_BITRIX_COUNT_NULL', "");
	$USER->SetParam('XLS_BITRIX_PRICE_NULL', "");
	$USER->SetParam('XLS_IBLOCK_NAME_AUTO', "");
	$USER->SetParam('XLS_NEW_ELEMENT_SKIP', "");

	// 4th step params
	$USER->SetParam('XLS_DATA_ROW_SELECT', "");

	unset($_REQUEST['XLS_PROFILE_SELECT']);
	unset($_REQUEST['XLS_PROFILE_NAME']);
	unset($_REQUEST['XLS_DATA_FILE']);
	unset($_REQUEST['XLS_DATA_FILE_ID']);
	unset($_REQUEST['XLS_IBLOCK_TYPE_ID']);
	unset($_REQUEST['XLS_IBLOCK_ID']);
	unset($_REQUEST['XLS_IBLOCK_SECTION_ID']);
	unset($_REQUEST['XLS_PROFILE_SELECT']);
	unset($_REQUEST['XLS_SELECT_IBLOCK_PROPERTY']);
	unset($_REQUEST['XLS_SELECT_TYPE_PROPERTY']);
	unset($_REQUEST['XLS_SELECT_UPLOAD_PROPERTY']);
	unset($_REQUEST['XLS_PROPERTY_MAIN']);
	unset($_REQUEST['XLS_LIST']);
	unset($_REQUEST['XLS_FIRST_ROW_HEADER']);
	unset($_REQUEST['XLS_FIRST_COLUMN_HEADER']);
	unset($_REQUEST['XLS_FIRST_ROW']);
	unset($_REQUEST['XLS_FIRST_COLUMN']);
	unset($_REQUEST['XLS_DEPTH_LEVEL_MAX']);
	unset($_REQUEST['XLS_DEACTIVATE_ELEMENTS']);
	unset($_REQUEST['XLS_BITRIX_UNACTIVE']);
	unset($_REQUEST['XLS_BITRIX_COUNT_NULL']);
	unset($_REQUEST['XLS_BITRIX_PRICE_NULL']);
	unset($_REQUEST['XLS_BITRIX_COUNT_NULL']);
	unset($_REQUEST['XLS_IBLOCK_NAME_AUTO']);
	unset($_REQUEST['XLS_NEW_ELEMENT_SKIP']);
	unset($_REQUEST['XLS_PRICE_PARAM_CURRENCY']);
	unset($_REQUEST['XLS_QUANTITY_TRACE']);
	unset($_REQUEST['XLS_DATA_ROW_SELECT']);

	unset($_SESSION['PROFILE_ARRAY']);
	unset($_REQUEST['PROFILE_ARRAY']);
} else {
	if (isset($_REQUEST['XLS_STEP'])) {
		$XLS_STEP = (int)$_REQUEST['XLS_STEP'];

		if (isset($_REQUEST['backButton'])) {
			$XLS_STEP -= 2;
		}

		$USER->SetParam('XLS_STEP', $XLS_STEP);
	} else {
		// $XLS_STEP = $USER->GetParam('XLS_STEP');
		if (!$XLS_STEP) {
			$XLS_STEP = 1;
		}
	}
}

if ($XLS_STEP == 2) {
	if ((strpos($_REQUEST['XLS_DATA_FILE'], 'http://')!==FALSE || strpos($_REQUEST['XLS_DATA_FILE'], 'https://')!==FALSE) && strlen(trim($_REQUEST['XLS_DATA_FILE']))>0) { // esli gruzyat fayl s vneshnego servisa
		$_FILES['XLS_DATA_FILE'] = CFile::MakeFileArray($_REQUEST['XLS_DATA_FILE']);
	}

	// file load
	if ($_FILES['XLS_DATA_FILE']['size']>0) { // esli idet zagruzka faila
		$dbFile = CFile::GetList(array(), array('FILE_NAME'=>$_FILES['XLS_DATA_FILE']['name']));
		$arFile = $dbFile->GetNext();
		if ($arFile && $arFile['FILE_SIZE']!=$_FILES['XLS_DATA_FILE']['size'] || !file_exists($_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arFile['ID']))) { // esli fail est' i on ne sovpadaet po razmeru ili fail sterli rukami
			CFile::Delete($arFile['ID']);
			unset($arFile);
		}

		if (!$arFile) {
			$_FILES['XLS_DATA_FILE']['MODULE_ID'] = 'softeffect.xls';
			$XLS_DATA_FILE_ID = CFile::SaveFile($_FILES['XLS_DATA_FILE'], 'softeffect.xls');
			$_REQUEST['XLS_DATA_FILE'] = CFile::GetPath($XLS_DATA_FILE_ID);
			$_REQUEST['XLS_DATA_FILE_ID'] = $XLS_DATA_FILE_ID;
		} else {
			$_REQUEST['XLS_DATA_FILE'] = CFile::GetPath($arFile['ID']);
			$_REQUEST['XLS_DATA_FILE_ID'] = $arFile['ID'];
		}
	} elseif ($_REQUEST['XLS_DATA_FILE']) { // vibran fail iz strukturi ili mediabiblioteki
		$_REQUEST['XLS_DATA_FILE_ID'] = '-1';
	} else { // esli idet rabota s uge zagrujennim failom
		$arTmpProfile = CSofteffectXlsProfile::get((int)$_REQUEST['XLS_PROFILE_SELECT']);
		$_REQUEST['XLS_DATA_FILE'] = $arTmpProfile['XLS_DATA_FILE'];
		$_REQUEST['XLS_DATA_FILE_ID'] = $arTmpProfile['XLS_DATA_FILE_ID'];
		unset($arTmpProfile);
	}
}

// profile delete
if ($_REQUEST['XLS_DELETE_PROFILE_x'] && $_REQUEST['XLS_DELETE_PROFILE_y'] && $_REQUEST['XLS_PROFILE_SELECT']) {
	CSofteffectXlsProfile::delete($_REQUEST['XLS_PROFILE_SELECT']);

	// to 2nd step
	$XLS_STEP = 1;

	unset($XLS_PROFILE_SELECT);
	unset($XLS_DELETE_PROFILE);
}

// profile load
if ($_REQUEST['XLS_CHANGE_PROFILE_x'] && $_REQUEST['XLS_CHANGE_PROFILE_y'] && (int)$_REQUEST['XLS_PROFILE_SELECT']) {
	$_SESSION['PROFILE_ARRAY'] = CSofteffectXlsProfile::get((int)$_REQUEST['XLS_PROFILE_SELECT']);

	// vse znachenia parametrov vinesem v massiv $_REQUEST
	foreach ($_SESSION['PROFILE_ARRAY'] as $key => $value) {
		$_REQUEST[$key] = $value;
	}

	$XLS_STEP = 1;
}

// profile rename
if ($_REQUEST['XLS_PROFILE_RENAME_x'] && $_REQUEST['XLS_PROFILE_RENAME_y'] && $_REQUEST['XLS_PROFILE_SELECT'] && trim($_REQUEST['XLS_PROFILE_NAME'])!='') {
	CSofteffectXlsProfile::save($_REQUEST['XLS_PROFILE_SELECT'], array('name'=>trim($_REQUEST['XLS_PROFILE_NAME'])));
	CSofteffectXlsHelper::saveParamStep('XLS_PROFILE_NAME');

	// to 2nd step
	$XLS_STEP = 1;
}

### Sohranenie i vosstanovlenie parametrov vseh shagov ###
if ((!$_REQUEST['XLS_DEACTIVATE_ELEMENTS'] || strlen($_REQUEST['XLS_DEACTIVATE_ELEMENTS'])<=0) && $XLS_STEP>2) {
	$_REQUEST['XLS_DEACTIVATE_ELEMENTS']='off';
}

// profile params
$XLS_PROFILE_SELECT = CSofteffectXlsHelper::saveParamStep('XLS_PROFILE_SELECT');
$XLS_PROFILE_NAME = CSofteffectXlsHelper::saveParamStep('XLS_PROFILE_NAME');

// step's params
$dbFields = CSofteffectXlsProfile::getFields();
while ($arField = $dbFields->GetNext()) {
	if (strpos($arField['Field'], "XLS_")===0) {
		$arrFields[]=$arField['Field'];
		$$arField['Field'] = CSofteffectXlsHelper::saveParamStep($arField['Field']);
	}
}

if (!$XLS_STEP4_MODE) $XLS_STEP4_MODE=2; // ustanovka po umolchaniu rejim dlya shaga 4

### Otmechennye stroki dlya sohraneniya v infobloke
### V nastroykah profilya eti dannye ne hranim
$XLS_ROW_TABLE_SELECT = CSofteffectXlsHelper::saveParamStep('XLS_DATA_ROW_SELECT');

// sohranim v profil' dannye v fonom rejime
if ($XLS_STEP > 1 && $XLS_STEP<=4) {
	if ($XLS_PROFILE_SELECT) {
		$profileData=array();
		foreach ($arrFields as $key => $value) {
			$profileData[$value] = $$value;
		}
		if ($XLS_PROFILE_SELECT == 'new') {
			$idProfile = CSofteffectXlsProfile::init($XLS_PROFILE_NAME);
		} else {
			$idProfile = (int)$XLS_PROFILE_SELECT;
		}

		CSofteffectXlsProfile::save($idProfile, $profileData);
	}
}

### Proveryaem nalichie neobodimyh parametrov dlya shaga 2 ###
if ($XLS_STEP == 4) {
	/*if ((array_search('iblock_name', $XLS_SELECT_IBLOCK_PROPERTY) === false || !$XLS_SELECT_UPLOAD_PROPERTY[array_search('iblock_name', $XLS_SELECT_IBLOCK_PROPERTY)]) && !$XLS_IBLOCK_NAME_AUTO) {
			$strError .= GetMessage('SOFTEFFECT_XLS_ERROR_NAME');
		$XLS_STEP = 3;
	}*/
}

// Nalichie XLS_DATA_FILE � XLS_IBLOCK_ID
if ($XLS_STEP >= 2) {
	if (!$XLS_DATA_FILE) {
		$strError .= GetMessage('SOFTEFFECT_XLS_ERROR_FILE');
		$XLS_STEP = 1;
	} else {
		// sozdaem ob"ekt, dlya raboty s xls faylom
		try {
			if ($XLS_STEP>=4) {
				$XlsExcel = new CSofteffectXlsExcel($XLS_DATA_FILE, $XLS_FIRST_ROW_HEADER, FALSE);
			} else {
				$XlsExcel = new CSofteffectXlsExcel($XLS_DATA_FILE, $XLS_FIRST_ROW_HEADER, 1);
			}
		}
		catch (Exception $e) {
			$strError .= $e->getMessage();
			$XLS_STEP = 1;
		}
	}

	if (!$XLS_IBLOCK_ID) {
		$strError .= GetMessage('SOFTEFFECT_XLS_ERROR_IBLOCK');
		$XLS_STEP = 1;
	} else {
		if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")) {
			if (CCatalog::GetByID($XLS_IBLOCK_ID)) {
				//$strError .= GetMessage('SOFTEFFECT_XLS_ERROR_IB_CATALOG');
				//$XLS_STEP = 1;
				$XLS_IBLOCK_IS_CATALOG = true;
			}
			else {
				$XLS_IBLOCK_IS_CATALOG = false;
			}
		} else {
			//$strError .= GetMessage('SOFTEFFECT_XLS_ERROR_MODULES');
			//$XLS_STEP = 1;
		}
	}
}

### Proveryaem nalichie neobodimyh parametrov dlya shaga 3 ###
if ($XLS_STEP == 3) {
	// Schityvaem zagolovki stolbcov
	$Sheet = $XlsExcel->getSheet((int)$XLS_LIST);
	$XlsArColumns = array();
	$column = $XLS_FIRST_COLUMN_HEADER - 1;

	// ogranichenie na zaciklivanie
	while ($column < 200) {
		$Cell = $Sheet->getCellByColumnAndRow($column, $XLS_FIRST_ROW_HEADER);
		$value = $Cell->getValue();

		if (!$value) {
			break;
		}

		$XlsArColumns[$column] = $XlsExcel->iconv($value);
		$column++;
	}

	// Poluchaem vse svoystva infobloka + naimenovanie elementab + cena v torgovom katalogeb + kol-vo v torgovom kataloge
	$XlsArIBlockProps = array();
	$XlsArIBlockProps['iblock_group_1'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_GROUP_1'),
		'type' => 'disabled'
	);

	$XlsArIBlockProps['iblock_name'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_NAME'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => TRUE,
		'prop_identify_dis' => FALSE
	);


	$XlsArIBlockProps['iblock_code'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_CODE'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_sort'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_SORT'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_tags'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_TAGS'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FASLE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_anons'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_PREVIEW_TEXT'),
		'type' => ($_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'][array_search('iblock_anons', $_SESSION['PROFILE_ARRAY']['XLS_SELECT_IBLOCK_PROPERTY'])]!='') ? $_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'][array_search('iblock_anons', $_SESSION['PROFILE_ARRAY']['XLS_SELECT_IBLOCK_PROPERTY'])] : 'html',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_detaill'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_DETAIL_TEXT'),
		'type' => ($_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'][array_search('iblock_detaill', $_SESSION['PROFILE_ARRAY']['XLS_SELECT_IBLOCK_PROPERTY'])]!='') ? $_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'][array_search('iblock_detaill', $_SESSION['PROFILE_ARRAY']['XLS_SELECT_IBLOCK_PROPERTY'])] : 'html',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_anons_pic'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_PREVIEW_PICTURE'),
		'type' => 'file',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_detaill_pic'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_DETAIL_PICTURE'),
		'type' => 'file',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	// vlojennost' sekciy
	if ($XLS_DEPTH_LEVEL_MAX>=1) {
		$XlsArIBlockProps['iblock_group_2'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PROPS_GROUP_2'),
			'type' => 'disabled'
		);
		for ($i=1; $i<=$XLS_DEPTH_LEVEL_MAX; $i++) {
			$XlsArIBlockProps['iblock_section_depth_'.$i] = array(
				'title' => str_replace('#NUM#', $i, GetMessage('SOFTEFFECT_XLS_DEPTH_LEVEL_PROP')),
				'type' => 'text',
				'type_disable' => TRUE,
				'prop_identify' => FALSE,
				'prop_identify_dis' => TRUE
			);
		}
	}

	$XlsArIBlockProps['iblock_group_6'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_GROUP_6'),
		'type' => 'disabled'
	);

	$XlsArIBlockProps['iblock_seo_meta_title'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_META_TITLE'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_meta_keywords'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_META_KEYWORDS'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_meta_description'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_META_DESCRIPTION'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_meta_pagetitle'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_META_PAGETITLE'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_preview_alt'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_PREVIEW_ALT'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_preview_title'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_PREVIEW_TITLE'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_preview_name'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_PREVIEW_NAME'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_detail_alt'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_DETAIL_ALT'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_detail_title'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_DETAIL_TITLE'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	$XlsArIBlockProps['iblock_seo_detail_name'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_SEO_DETAIL_NAME'),
		'type' => 'text',
		'type_disable' => TRUE,
		'prop_identify' => FALSE,
		'prop_identify_dis' => TRUE
	);

	if ($XLS_IBLOCK_IS_CATALOG) {
		$XlsArIBlockProps['iblock_group_3'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PROPS_GROUP_3'),
			'type' => 'disabled'
		);

		$XlsArIBlockProps['iblock_PURCHASING_PRICE'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PRICE_BUY'),
			'type' => 'number',
			'type_disable' => TRUE,
			'prop_identify' => FALSE,
			'prop_identify_dis' => TRUE
		);

		$XlsArIBlockProps['iblock_PURCHASING_CURRENCY'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PRICE_BUY_CURRENCY'),
			'type' => 'text',
			'type_disable' => TRUE,
			'prop_identify' => FALSE,
			'prop_identify_dis' => TRUE
		);

		$dbCatalogGroups = CCatalogGroup::GetList();
		while ($arCatalogGroup = $dbCatalogGroups->GetNext()) {
			$XlsArIBlockProps['iblock_price_'.$arCatalogGroup['ID']] = array(
				'title' => $arCatalogGroup['NAME_LANG'],
				'type' => 'number',
				'type_disable' => TRUE,
				'prop_identify' => FALSE,
				'prop_identify_dis' => TRUE
			);
			$XlsArIBlockProps['iblock_currencies_price_'.$arCatalogGroup['ID']] = array(
				'title' => str_replace('#PRICE#', $arCatalogGroup['NAME_LANG'], GetMessage('SOFTEFFECT_XLS_CURRENCY_FOR')),
				'type' => 'text',
				'type_disable' => TRUE,
				'prop_identify' => FALSE,
				'prop_identify_dis' => TRUE
			);
		}

		$XlsArIBlockProps['iblock_count'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PROPS_QUANT'),
			'type' => 'number',
			'type_disable' => TRUE,
			'prop_identify' => FALSE,
			'prop_identify_dis' => TRUE
		);

		$XlsArIBlockProps['iblock_weight'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PROPS_WEIGHT'),
			'type' => 'number',
			'type_disable' => TRUE,
			'prop_identify' => FALSE,
			'prop_identify_dis' => TRUE
		);

		$XlsArIBlockProps['iblock_nds'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PROPS_NDS'),
			'type' => 'text',
			'type_disable' => TRUE,
			'prop_identify' => FALSE,
			'prop_identify_dis' => TRUE
		);


		$NewStoreOb = CCatalogStore::GetList(array("TITLE" => "ASC"),array(),false,false,array("ID","TITLE"));
		while($NewStore = $NewStoreOb->GetNext()) {
			if(strlen($NewStore["ID"]) > 0) {
				$XlsArIBlockProps['iblock_store_'.$NewStore["ID"]] = array(
					'title' => GetMessage('SOFTEFFECT_XLS_PROPS_STORE').": ".$NewStore["TITLE"],
					'type' => 'number',
					'type_disable' => TRUE,
					'prop_identify' => FALSE,
					'prop_identify_dis' => TRUE
				);
			}
		};

	}

	$dbResult = CIBlock::GetProperties($XLS_IBLOCK_ID, array("SORT" => "ASC", "NAME" => "ASC"), array());
	if ($dbResult->SelectedRowsCount()>0) {
		$XlsArIBlockProps['iblock_group_4'] = array(
			'title' => GetMessage('SOFTEFFECT_XLS_PROPS_GROUP_4'),
			'type' => 'disabled'
		);
	}
	while ($arResult = $dbResult->GetNext()) {
		if ($arResult['PROPERTY_TYPE']=='N') {
			$type = 'number';
			if ($arResult['MULTIPLE']=='Y') {
				$prop_identify_dis = TRUE;
			} else {
				$prop_identify_dis = FALSE;
			}
		} elseif ($arResult['PROPERTY_TYPE']=='S') {
			if ($arResult['USER_TYPE']=='HTML') {
				$type = 'html';
				$prop_identify_dis = TRUE;
			} else {
				$type = 'text';
				$prop_identify_dis = FALSE;
			}

			if ($arResult['MULTIPLE']=='Y') {
				$prop_identify_dis = TRUE;
				$type = 'html_pathlist';
			}
		} elseif ($arResult['PROPERTY_TYPE']=='L') {
			if ($arResult['MULTIPLE']=='Y') {
				$type = 'multilist';
			} else {
				$type = 'list';
			}
			$prop_identify_dis = TRUE;
		} elseif ($arResult['PROPERTY_TYPE']=='F') {
			if ($arResult['MULTIPLE']=='Y') {
				$type = 'fileslist';
			} else {
				$type = 'file';
			}
			$prop_identify_dis = TRUE;
		} elseif ($arResult['PROPERTY_TYPE']=='E') {
			if ($arResult['MULTIPLE']=='Y') {
				$type = 'elementlist';
			} else {
				$type = 'element';
			}
			$prop_identify_dis = TRUE;
		} elseif ($arResult['PROPERTY_TYPE']=='G') {
			if ($arResult['MULTIPLE']=='Y') {
				$type = 'sectionnlist';
			} else {
				$type = 'section';
			}
			$prop_identify_dis = TRUE;
		}

		if ($_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'][array_search($arResult['CODE'], $_SESSION['PROFILE_ARRAY']['XLS_SELECT_IBLOCK_PROPERTY'])]) {
			$type = $_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'][array_search($arResult['CODE'], $_SESSION['PROFILE_ARRAY']['XLS_SELECT_IBLOCK_PROPERTY'])];
		}

		$XlsArIBlockProps[$arResult['CODE']] = array(
			'title' => $arResult['NAME'],
			'type' => $type,
			'type_disable' => TRUE,
			'prop_identify' => FALSE,
			'prop_identify_dis' => $prop_identify_dis
		);
	}

	$XlsArIBlockProps['iblock_group_5'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_GROUP_5'),
		'type' => 'disabled'
	);

	$XlsArIBlockProps['__xls_new_prop'] = array(
		'title' => GetMessage('SOFTEFFECT_XLS_PROPS_NEW'),
		'type' => 'new'
	);

	// Sformiruem massiv vozmojnyh svoystv
	$XlsArProps = array(
		'text' => GetMessage('SOFTEFFECT_XLS_PROPS_TEXT'),
		'textlist' => GetMessage('SOFTEFFECT_XLS_PROPS_TEXTLIST'),
		'number' => GetMessage('SOFTEFFECT_XLS_PROPS_NUMBER'),
		'numberlist' => GetMessage('SOFTEFFECT_XLS_PROPS_NUMBERLIST'),
		'html' => GetMessage('SOFTEFFECT_XLS_PROPS_HTML'),
		'html_path' => GetMessage('SOFTEFFECT_XLS_PROPS_HTML_PATH'),
		'html_pathlist' => GetMessage('SOFTEFFECT_XLS_PROPS_HTML_PATHLIST'),
		'texth' => GetMessage('SOFTEFFECT_XLS_PROPS_TEXTH'),
		'texth_path' => GetMessage('SOFTEFFECT_XLS_PROPS_TEXTH_PATH'),
		'texth_pathlist' => GetMessage('SOFTEFFECT_XLS_PROPS_TEXTH_PATHLIST'),
		'list' => GetMessage('SOFTEFFECT_XLS_PROPS_LIST'),
		'multilist' => GetMessage('SOFTEFFECT_XLS_PROPS_MULTILIST'),
		'file' => GetMessage('SOFTEFFECT_XLS_PROPS_FILE'),
		'fileslist' => GetMessage('SOFTEFFECT_XLS_PROPS_FILELIST'),
		'element' => GetMessage('SOFTEFFECT_XLS_PROPS_ELEMENT'),
		'elementlist' => GetMessage('SOFTEFFECT_XLS_PROPS_ELEMENTLIST'),
		'section' => GetMessage('SOFTEFFECT_XLS_PROPS_SECTION'),
		'sectionlist' => GetMessage('SOFTEFFECT_XLS_PROPS_SECTIONLIST'),
	);

	$arIBlockList = array();
	$dbIBlock = CIBlock::GetList(array('SORT'=>'ASC', 'NAME'=>'ASC'), array(), false);
	while ($arIBlock = $dbIBlock->GetNext()) {
		$arIBlockList[$arIBlock['ID']] = $arIBlock['NAME'];
	}

	unset($Cell);
	unset($Sheet);
	unset($column);
	unset($dbResult);
	unset($arResult);
	unset($value);

	if (!count($XlsArColumns)) {
		$strError .= GetMessage('SOFTEFFECT_XLS_ERROR_TITLE');
		$XLS_STEP = 2;
	}
}
?>
<? CAdminMessage::ShowMessage($strError); ?>
<form method="POST" action="<?=$sDocPath?>?lang=<?=LANG?>" enctype="multipart/form-data" name="dataload" id="dataload" autocomplete="off">
	<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB1'), "ICON" => "iblock", "TITLE" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB1_ALT')),
		array("DIV" => "edit2", "TAB" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB2'), "ICON" => "iblock", "TITLE" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB2_ALT')),
		array("DIV" => "edit3", "TAB" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB3'), "ICON" => "iblock", "TITLE" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB3_ALT')),
		array("DIV" => "edit4", "TAB" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB4'), "ICON" => "iblock", "TITLE" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB4_ALT')),
		array("DIV" => "edit5", "TAB" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB5'), "ICON" => "iblock", "TITLE" => GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB5_ALT')),
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs, false);
	$tabControl->Begin();

	?>
	<? if ($DEMO_MODE==2) { ?>
		<div class="demo_mode"><?=GetMessage('SOFTEFFECT_XLS_DEMO_MODE_'.$DEMO_MODE);?></div>
	<? }

	$tabControl->BeginNextTab();
	if ($XLS_STEP == 1) {
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/admin/xls_data_import_step_1.php');
	}
	$tabControl->EndTab();

	$tabControl->BeginNextTab();
	if ($XLS_STEP == 2) {
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/admin/xls_data_import_step_2.php');
	}
	$tabControl->EndTab();

	$tabControl->BeginNextTab();
	if ($XLS_STEP == 3) {
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/admin/xls_data_import_step_3.php');
	}
	$tabControl->EndTab();

	$tabControl->BeginNextTab();
	if ($XLS_STEP == 4) {
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/admin/xls_data_import_step_4.php');
	}
	$tabControl->EndTab();

	$tabControl->BeginNextTab();
	if ($XLS_STEP == 5) {
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/admin/xls_data_import_step_5.php');
	}
	$tabControl->EndTab();

	$tabControl->Buttons();

	if ($XLS_STEP >=2 ) {
		?><input type="hidden" name="XLS_IBLOCK_SECTION_ID" value="<?=htmlspecialcharsbx($_REQUEST['XLS_IBLOCK_SECTION_ID'])?>"><?
	}

	if ($XLS_STEP>2) {
		?><input type="hidden" name="XLS_DEACTIVATE_ELEMENTS" value="<?=$XLS_DEACTIVATE_ELEMENTS?>"><?
	}

	if ($XLS_STEP < 5) { ?>
		<input type="hidden" name="XLS_STEP" value="<?=$XLS_STEP + 1;?>">
		<?=bitrix_sessid_post()?>

		<? if ($XLS_STEP > 1) { ?>
			<input type="submit" name="backButton2" value="<?=GetMessage('SOFTEFFECT_XLS_UPLOAD_ELSE')?>">
			<input type="submit" name="backButton" value="&laquo; <?=GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_BACK')?>">
		<? } ?>

		<input type="submit" value="<?echo ($XLS_STEP==4)? GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_NEXT_STEP_F') : GetMessage('SOFTEFFECT_XLS_IBLOCK_ADM_IMP_NEXT_STEP') ?> &raquo;" name="submit_btn" />

		<? if ($XLS_STEP==1) { ?>
			<input type="submit" name="skipAllSteps" value="<?=GetMessage('SOFTEFFECT_XLS_SE_XLS_STEP_AUTOREDIRECT_BTN')?> &raquo;&raquo;" title="<?=GetMessage('SOFTEFFECT_XLS_SE_XLS_STEP_AUTOREDIRECT_BTN_TITLE')?>"<? if (intval($_REQUEST['XLS_PROFILE_SELECT'])<=0) { ?> disabled="true"<? } ?> />
		<? }
	} else {
		?><input type="submit" name="backButton2" value="<?=GetMessage('SOFTEFFECT_XLS_UPLOAD_ELSE')?>"><?
	} ?>

	<? $tabControl->End(); ?>
</form>
<? if ($XLS_STEP == 3) { ?>
	<div align="center" class="adm-info-message-wrap">
		<div class="adm-info-message" style="text-align: left; float: left; margin-bottom: 0;">
			<span class="set-prop">&#9756;</span> <?=GetMessage('SOFTEFFECT_XLS_STEP3_NOTE');?><br />
			<span class="set-prop" style="font-size: 20px;">&#10071;</span> <?=GetMessage('SOFTEFFECT_XLS_STEP3_NOTE_2');?>
		</div>
	</div>
<? } ?>
<div align="center" class="adm-info-message-wrap">
	<div class="adm-info-message" style="text-align: left; color: red; display: block; margin-bottom: 0;">
		<?=GetMessage('SOFTEFFECT_XLS_MODULE_RED');?>
	</div>
</div>
<div align="center" class="adm-info-message-wrap">
	<div class="adm-info-message" style="text-align: left;">
		<?=GetMessage('SOFTEFFECT_XLS_MODULE_LINK');?>
	</div>
</div>
<? if ($DEMO_MODE==3) { ?>
	<script type="text/javascript">
		var mainDiv = document.getElementById('tabControl_layout');
		var inputs = mainDiv.getElementsByTagName('input');
		var selects = mainDiv.getElementsByTagName('select');

		for (var i = 0; i < inputs.length; i++) {
			inputs[i].setAttribute("disabled", "disabled");
		};

		for (var i = 0; i < selects.length; i++) {
			selects[i].setAttribute("disabled", "disabled");
		};
	</script>
<? } ?>

<script language="JavaScript">
	<!--
	<? if ($XLS_STEP < 2): ?>
		tabControl.SelectTab("edit1");
		tabControl.DisableTab("edit2");
		tabControl.DisableTab("edit3");
		tabControl.DisableTab("edit4");
		tabControl.DisableTab("edit5");
	<?elseif ($XLS_STEP == 2):?>
		tabControl.SelectTab("edit2");
		tabControl.DisableTab("edit1");
		tabControl.DisableTab("edit3");
		tabControl.DisableTab("edit4");
		tabControl.DisableTab("edit5");
	<?elseif ($XLS_STEP == 3):?>
		tabControl.SelectTab("edit3");
		tabControl.DisableTab("edit1");
		tabControl.DisableTab("edit2");
		tabControl.DisableTab("edit4");
		tabControl.DisableTab("edit5");
	<?elseif ($XLS_STEP == 4):?>
		tabControl.SelectTab("edit4");
		tabControl.DisableTab("edit1");
		tabControl.DisableTab("edit2");
		tabControl.DisableTab("edit3");
		tabControl.DisableTab("edit5");
	<?elseif ($XLS_STEP > 4):?>
		tabControl.SelectTab("edit5");
		tabControl.DisableTab("edit1");
		tabControl.DisableTab("edit2");
		tabControl.DisableTab("edit3");
		tabControl.DisableTab("edit4");
	<?endif;?>
	//-->
</script>

<? require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php"); ?>