<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
global $USER;

$autoLoadClasses = array();
BitrixRecursiveAutoload($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/classes/general/PHPExcel', $autoLoadClasses);
CModule::IncludeModule('iblock');
CModule::IncludeModule('sale');
CModule::IncludeModule('catalog');

function BitrixRecursiveAutoload($path, &$autoLoadClasses) {
	$dir = opendir($path);
	while($d = readdir($dir)) {
		if ($d == '.' || $d == '..') continue;
		if (is_file($path.'/'.$d)) {
			if (strpos($path.'/'.$d, '.php')!==FALSE) {
				$classPath = explode('softeffect.xls/', $path.'/'.$d);
				$classPath = $classPath[1];

				$className = str_replace('classes/general/', '', $classPath);
				$className = str_replace(array('/', '.php'), array('_', ''), $className);
				$autoLoadClasses[$className]=$classPath;
			}
		} elseif (is_dir($path.'/'.$d)) {
			BitrixRecursiveAutoload($path.'/'.$d, $autoLoadClasses);
		}
	}
}

$autoLoadClasses = array_merge(
	$autoLoadClasses,
	array(
		'CSofteffectXlsProfile' => 'classes/mysql/CSofteffectXlsProfile.php',
		'CSofteffectXlsHelper' => 'classes/general/CSofteffectXlsHelper.php',
		'CSofteffectXlsExcel' => 'classes/general/CSofteffectXlsExcel.php',
		'PHPExcel' => 'classes/general/CSofteffectPHPExcel.php',
		'idna_convert' => 'classes/general/idna_convert.class.php',
	)
);

CModule::AddAutoloadClasses(
	'softeffect.xls',
	$autoLoadClasses
);

$format = 'd.m.Y H:i:s';
$end=FALSE;
$XlsArRealColumns = $USER->GetParam('XlsRealColums'); // iz 4go shaga

if (!$_SESSION['PROFILE_ARRAY']) die('no profile!||The End');

// sozdaem peremennie
foreach ($_SESSION['PROFILE_ARRAY'] as $key => $value) {
	if (strpos($key, 'XLS_')===0) {
		$$key = $value;
	}
}

// поля инфоблока для генерации поля CODE
$arFieldsIBlock = CIBlock::GetFields($XLS_IBLOCK_ID);

//echo '<pre>'; print_r($_SESSION['PROFILE_ARRAY']); echo '</pre>'; die('The End');
//echo '<pre>'; print_r($XLS_DATA_ROW_SELECT); echo '</pre>';
//echo '<pre>'; print_r($XLS_DATA_ID_BITRIX); echo '</pre>';
if ($_SESSION['startRow'])
	$startRow = $_SESSION['startRow'];
else
	$startRow = (int)$XLS_FIRST_ROW;

// 4itaem excel fail
$XlsExcel = new CSofteffectXlsExcel($XLS_DATA_FILE, $startRow, $XLS_STEP5_CHUNKSIZE);
$Sheet = $XlsExcel->getSheet((int)$XLS_LIST);

// idem po strokam
$rowTo = $startRow+$XLS_STEP5_CHUNKSIZE-1;

//echo $startRow.'->'.$rowTo.'<br />';
for ($row = $startRow; $row<=$rowTo; $row++) {
	$realRow = $row-(int)$XLS_FIRST_ROW; // pokazivaet real'niy kluch v massive, ibo idet sdvig na $XLS_FIRST_ROW

	if ($XLS_DATA_ROW_SELECT[$realRow]==1) {
		$arRow = array();
		$priceCurrency=array();
		$priceValue=array();
		$arDataForCatalog = array();
		$secID = FALSE;
		$arData = array(
			'IBLOCK_ID' => $XLS_IBLOCK_ID,
			'ACTIVE' => 'Y',
			'PROPERTY_VALUES' => array()
		);

		// sobiraem stroku
		for ($column = $XLS_FIRST_COLUMN - 1; $column <= max(array_keys($XLS_SELECT_UPLOAD_PROPERTY)); $column++) {
			if ($XLS_SELECT_UPLOAD_PROPERTY[$column]) {
				$cell = $Sheet->getCellByColumnAndRow($column, $row);
				$val = $cell->getValue();

				if(PHPExcel_Shared_Date::isDateTime($cell)) {
				     $val = date($format, PHPExcel_Shared_Date::ExcelToPHP($val));
				}
				//var_dump(iconv('utf-8', 'windows-1251', $val));
				$val = $XlsExcel->iconv($val);

				if (isset($val)) {
					$arRow[$column] = $val;
				}
			}
		}

		if ($XLS_IBLOCK_SECTION_ID) {
			$dbSec = CIBlockSection::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$XLS_IBLOCK_ID, 'ID'=>$XLS_IBLOCK_SECTION_ID), FALSE, array('UF_*'));
			$arSec = $dbSec->GetNext();

			$secID = $XLS_IBLOCK_SECTION_ID;
			$XLS_IBLOCK_SECTION_ID_DL = $arSec['DEPTH_LEVEL'];
		}

		//echo '<pre>'; print_r($arRow); echo '</pre>';
		// multi secs
		$arMultiSecs=array();
		foreach ($XLS_SELECT_IBLOCK_PROPERTY as $column=>$name) {
			if (strpos($name, 'iblock_section_depth')!==FALSE) {
				$arMultiSecs[str_replace('iblock_section_depth_', '', $name)] = $column; // level => column
			}
		}

		ksort($arMultiSecs); // level sort

		foreach ($arMultiSecs as $level => $column) {
			if (strlen(trim($arRow[$column]))<=0) {
				break; // esli vdrug kakaya-to sekciya pustaya - preryvaem i pishem v poslednyuyu sozdannuyu
			}

			$depth = $level;
			if (intval($XLS_IBLOCK_SECTION_ID)>0) $depth += $XLS_IBLOCK_SECTION_ID_DL;

			// htmlspecialcharsback - ispol'zuetsya dlya kavychek
			$arFilterSec = array('IBLOCK_ID'=>$XLS_IBLOCK_ID, 'DEPTH_LEVEL'=>$depth, 'NAME'=>htmlspecialcharsback(trim($arRow[$column])));
			if ($level>1) {
				$arFilterSec['SECTION_ID'] = $secID;
			}

			$dbSec = CIBlockSection::GetList(array('SORT'=>'ASC'), $arFilterSec);
			if ($dbSec->SelectedRowsCount()<=0) {
				$arParams = array(
					"max_len" => $arFieldsIBlock['SECTION_CODE']['DEFAULT_VALUE']['TRANS_LEN'],
					"change_case" => $arFieldsIBlock['SECTION_CODE']['DEFAULT_VALUE']['TRANS_CASE'],
					"replace_space" => $arFieldsIBlock['SECTION_CODE']['DEFAULT_VALUE']['TRANS_SPACE'],
					"replace_other" => $arFieldsIBlock['SECTION_CODE']['DEFAULT_VALUE']['TRANS_OTHER'],
				);
				$code = Cutil::translit($arRow[$column], "ru", $arParams);

				if (in_array($code, $_SESSION['SEC_CODES']) && $arFieldsIBlock['SECTION_CODE']['DEFAULT_VALUE']['UNIQUE']=='Y') {
					$code = $code.'_'.randString(3);
				}

				$_SESSION['SEC_CODES'][]=$code;

				$bs = new CIBlockSection;
				$arFields = Array(
					'ACTIVE' => 'Y',
					'IBLOCK_SECTION_ID' => $secID,
					'IBLOCK_ID' => $XLS_IBLOCK_ID,
					'NAME' => htmlspecialcharsback(trim($arRow[$column])),
					'CODE' => $code
				);
				$secID = $bs->Add($arFields);
			} else {
				$arSec = $dbSec->GetNext();
				$secID = $arSec['ID'];
			}
		}

		if ($secID) { // po umolchaniyu razdel elementa ne trogaem
			$arData['IBLOCK_SECTION_ID'] = $secID; // last of sections
		}
		//echo '<pre>'; print_r($XlsArRealColumns); echo '</pre>'; die();
		foreach($XlsArRealColumns as $key=>$val) {
			if ($XLS_SELECT_TYPE_PROPERTY[$key]=='list') {
				$dbPROPS = CIBlockProperty::GetPropertyEnum($XlsArRealColumns[$key], array(), array('IBLOCK_ID'=>$XLS_IBLOCK_ID));
				$PROPS_PROPERTY_LIST = CIBlockProperty::GetByID($XlsArRealColumns[$key], $XLS_IBLOCK_ID);
				if($arPROPS_PROPERTY_LIST = $PROPS_PROPERTY_LIST->GetNext())
				$PROPS_PROPERTY_ID[$key] = $arPROPS_PROPERTY_LIST["ID"];
				while($arPROPS = $dbPROPS->GetNext()) {
					$arPropsList[$val][] = Array("VALUE" => $arPROPS["VALUE"], "ID" =>$arPROPS["ID"]);
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$key]=='multilist') {
				$PROPS_PROPERTY_MULTILIST = CIBlockProperty::GetByID($XlsArRealColumns[$key], $XLS_IBLOCK_ID);
				if($arPROPS_PROPERTY_MULTILIST = $PROPS_PROPERTY_MULTILIST->GetNext())
				$PROPS_PROPERTY_ID[$key] = $arPROPS_PROPERTY_MULTILIST["ID"];
				$dbPROPS = CIBlockProperty::GetPropertyEnum($XlsArRealColumns[$key], Array());
				while($arPROPS = $dbPROPS->GetNext()) {
					$arPropsVal[$val][$arPROPS['ID']] = $arPROPS['VALUE'];
				}
			}
		}

		// sobiraem massiv s dannimi elementa ibloka
		foreach ($arRow as $column=>$value) {
			$value = trim($value);
			if ($XlsArRealColumns[$column] == 'iblock_name') {
				$arData['NAME'] = htmlspecialcharsback($value);
			} elseif ($XlsArRealColumns[$column] == 'iblock_code') {
				$arData['CODE'] = htmlspecialcharsback($value);
			} elseif ($XlsArRealColumns[$column] == 'iblock_tags') {
				$arData['TAGS'] = trim($value);
			} elseif ($XlsArRealColumns[$column] == 'iblock_sort') {
				$arData['SORT'] = intval($value);
			} elseif ($XlsArRealColumns[$column] == 'iblock_anons') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0 || is_file($_SERVER['DOCUMENT_ROOT'].$value)) {
					$htmlProp = CFile::MakeFileArray($value);
					$res = CFile::CheckFile($htmlProp);
					if (strlen($res)<=0) {
						$html_text = file_get_contents((strpos($value, 'http')===0 || strpos($value, 'https')===0) ? $value : $_SERVER['DOCUMENT_ROOT'].$value);
						$arData['PREVIEW_TEXT'] = $html_text;
					}
				} else {
					$arData['PREVIEW_TEXT'] = $value;
				}

				if ($XLS_SELECT_TYPE_PROPERTY[$column]=='html' || $XLS_SELECT_TYPE_PROPERTY[$column]=='html_path') {
					$arData['PREVIEW_TEXT_TYPE'] = 'html';
				} else {
					$arData['PREVIEW_TEXT_TYPE'] = 'text';
				}
			} elseif ($XlsArRealColumns[$column] == 'iblock_anons_pic') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0) {
					//$picAn = CFile::MakeFileArray(CSofteffectXlsHelper::convertRussianDomens($value));
					$picAn = CFile::MakeFileArray($value);
				} else {
					$picAn = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$value);
				}
				$res = CFile::CheckImageFile($picAn);
				if (strlen($res)>0) {
					ShowError($value.': is not image!');
				} else {
					$arData['PREVIEW_PICTURE'] = $picAn;
				}
			} elseif ($XlsArRealColumns[$column] == 'iblock_detaill') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0 || is_file($_SERVER['DOCUMENT_ROOT'].$value)) {
					$htmlProp = CFile::MakeFileArray($value);
					$res = CFile::CheckFile($htmlProp);
					if (strlen($res)<=0) {
						$html_text = file_get_contents((strpos($value, 'http')===0 || strpos($value, 'https')===0) ? $value : $_SERVER['DOCUMENT_ROOT'].$value);
						$arData['DETAIL_TEXT'] = $html_text;
					}
				} else {
					$arData['DETAIL_TEXT'] = $value;
				}

				if ($XLS_SELECT_TYPE_PROPERTY[$column]=='html' || $XLS_SELECT_TYPE_PROPERTY[$column]=='html_path') {
					$arData['DETAIL_TEXT_TYPE'] = 'html';
				} else {
					$arData['DETAIL_TEXT_TYPE'] = 'text';
				}
			} elseif ($XlsArRealColumns[$column] == 'iblock_detaill_pic') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0) {
					//$picAn = CFile::MakeFileArray(CSofteffectXlsHelper::convertRussianDomens($value));
					$picAn = CFile::MakeFileArray($value);
				} else {
					$picAn = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$value);
				}
				$res = CFile::CheckImageFile($picAn);
				if (strlen($res)>0) {
					ShowError($value.': is not image!');
				} else {
					$arData['DETAIL_PICTURE'] = $picAn;
				}
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_meta_title') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_META_TITLE'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_meta_keywords') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_META_KEYWORDS'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_meta_description') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_META_DESCRIPTION'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_meta_pagetitle') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_PAGE_TITLE'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_preview_alt') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_PREVIEW_PICTURE_FILE_ALT'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_preview_title') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_preview_name') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_PREVIEW_PICTURE_FILE_NAME'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_detail_alt') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_detail_title') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] = $value;
			} elseif ($XlsArRealColumns[$column]=='iblock_seo_detail_name') {
				$arData['IPROPERTY_TEMPLATES']['ELEMENT_DETAIL_PICTURE_FILE_NAME'] = $value;
			} elseif ($XlsArRealColumns[$column] == 'iblock_count') {
				$arDataForCatalog['QUANTITY'] = (int)$value;
			} elseif ($XlsArRealColumns[$column] == 'iblock_weight') {
				$arDataForCatalog['WEIGHT'] = (int)$value;
			} elseif ($XlsArRealColumns[$column] == 'iblock_nds') {
				if ($value=='Y' || $value =='N') {
					$arDataForCatalog['VAT_INCLUDED'] = $value;
				}
			}
			 elseif ($XlsArRealColumns[$column] == 'iblock_PURCHASING_PRICE') {
				$arDataForCatalog['PURCHASING_PRICE'] = round(floatval(str_replace(',', '.', $value)), 2);
			} elseif ($XlsArRealColumns[$column] == 'iblock_PURCHASING_CURRENCY') {
				$arDataForCatalog['PURCHASING_CURRENCY'] = $value;
			} elseif (strpos($XlsArRealColumns[$column], 'iblock_price_')!==FALSE) {
				$priceValue[intval(str_replace('iblock_price_', '', $XlsArRealColumns[$column]))] = $value;
				//$arDataForCatalog['PRICE'] = floatval(str_replace(",", ".", $value));
			} elseif (strpos($XlsArRealColumns[$column], 'iblock_section_depth_')!==FALSE) {

			} elseif (strpos($XlsArRealColumns[$column], 'iblock_currencies_price_')!==FALSE) {
				$priceCurrency[intval(str_replace('iblock_currencies_price_', '', $XlsArRealColumns[$column]))]=$value;
			} elseif(strpos($XlsArRealColumns[$column], 'iblock_store_')!==FALSE) {
				$storeValue[intval(str_replace('iblock_store_', '', $XlsArRealColumns[$column]))] = $value;
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='list') {
				foreach($arPropsList[$XLS_SELECT_IBLOCK_PROPERTY[$column]] as $PropsListVal) {
					if($PropsListVal["VALUE"] == $value) {
  						$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $PropsListVal["ID"];
					}
				}

				if (ToLower($value)=="false") { // only text type, it's correct
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = false;
				} else {
					//add PROPERTY_VALUES for list
					if(empty($arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]])) {
						$PropID = CIBlockPropertyEnum::Add(Array('PROPERTY_ID'=>$PROPS_PROPERTY_ID[$column], 'VALUE'=>$value));
						$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $PropID;
						$arPropsList[$XLS_SELECT_IBLOCK_PROPERTY[$column]][] = Array("VALUE" => $value, "ID" => $PropID);
					}
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='multilist') {
				$arMultilist = explode($XLS_PROPERTY_SEPARATOR, $value);
				$PROPS = array();
				$PropsVal = FALSE;
				foreach($arMultilist as $MultiValue) {
					if(strlen(trim($MultiValue))>0) {
						if (in_array($MultiValue, $arPropsVal[$XlsArRealColumns[$column]])) {
							$PropsVal = array_search($MultiValue, $arPropsVal[$XlsArRealColumns[$column]]);
						}
					}

					if (!$PropsVal) {
						$ibpenum = new CIBlockPropertyEnum;
						$PropID = $ibpenum->Add(Array('PROPERTY_ID'=>$PROPS_PROPERTY_ID[$column], 'VALUE'=>$MultiValue));
						$PROPS[] = Array("VALUE" => $PropID);
						$arPropsVal[$XlsArRealColumns[$column]][$PropID] = $MultiValue;
					} else {
						$PROPS[] = Array("VALUE" => $PropsVal);
					}
					$PropsVal = FALSE;
				}

				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $PROPS;
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='file') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0) {
					//$picProp = CFile::MakeFileArray(CSofteffectXlsHelper::convertRussianDomens($value));
					$picProp = CFile::MakeFileArray($value);
				} else {
					$picProp = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$value);
				}

				$res = CFile::CheckFile($picProp);

				if (strlen($res)>0) {
					ShowError($value.': is not file!');
				} else {
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $picProp;
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='fileslist') {
				$arFilesListCorrect=array();
				$arFilesList = explode($XLS_PROPERTY_SEPARATOR, $value);

				// check files in list
				foreach ($arFilesList as $pathFile) {
					if (strpos($pathFile, 'http')===0 || strpos($pathFile, 'https')===0) {
						//$picProp = CFile::MakeFileArray(CSofteffectXlsHelper::convertRussianDomens($pathFile));
						$picProp = CFile::MakeFileArray($pathFile);
					} else {
						$picProp = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$pathFile);
					}

					$res = CFile::CheckFile($picProp);
					if (strlen($res)<=0) {
						$arFilesListCorrect[] = $picProp;
					}
				}

				// do list of correct files
				foreach ($arFilesListCorrect as $key => $picProp) {
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]]["n".$key] = array(
						"VALUE" => $picProp,
						"DESCRIPTION" => ""
					);
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='element') {
				if (count($_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_ID'][$XlsArRealColumns[$column]])) {
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_ID'][$XlsArRealColumns[$column]][htmlspecialcharsbx($value)];
				} else {
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $value;
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='section') {
				if (count($_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_SECID'][$XlsArRealColumns[$column]])) {
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_SECID'][$XlsArRealColumns[$column]][$value];
				} else {
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $value;
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='elementlist') {
				$arElementList = explode($XLS_PROPERTY_SEPARATOR, $value);
				if (count($_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_ID'][$XlsArRealColumns[$column]])) {
					$arElementListTMP=array();
					foreach ($arElementList as $nameElem) {
						$arElementListTMP[] = $_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_ID'][$XlsArRealColumns[$column]][$nameElem];
					}
					$arElementList = $arElementListTMP;
				}

				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $arElementList;
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='sectionlist') {
				$arSectionList = explode($XLS_PROPERTY_SEPARATOR, $value);
				if (count($_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_SECID'][$XlsArRealColumns[$column]])) {
					$arElementListTMP=array();
					foreach ($arSectionList as $nameSec) {
						$arElementListTMP[] = $_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_SECID'][$XlsArRealColumns[$column]][$nameSec];
					}
					$arSectionList = $arElementListTMP;
				}

				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = $arSectionList;
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='html') {
				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = array("VALUE" => array("TEXT" => $value, "TYPE" => 'html'));
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='html_path') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0) {
					//$htmlProp = CSofteffectXlsHelper::convertRussianDomens($value);
					$htmlProp = $value;
				} else {
					$htmlProp = $_SERVER['DOCUMENT_ROOT'].$value;
				}

				$res = CFile::CheckFile(CFile::MakeFileArray($htmlProp));
				if (strlen($res)<=0) {
					$html_text = file_get_contents($htmlProp);
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = array("VALUE" => array("TEXT" => $html_text, "TYPE" => 'html'));
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='html_pathlist') {
				$arFilesList = explode($XLS_PROPERTY_SEPARATOR, $value);
				foreach ($arFilesList as $key => $file_path) {
					if (strpos($file_path, 'http')===0 || strpos($file_path, 'https')===0) {
						//$htmlProp = CSofteffectXlsHelper::convertRussianDomens($file_path);
						$htmlProp = $file_path;
					} else {
						$htmlProp = $_SERVER['DOCUMENT_ROOT'].$file_path;
					}

					$res = CFile::CheckFile(CFile::MakeFileArray($htmlProp));
					if (strlen($res)<=0) {
						$html_text = file_get_contents($htmlProp);
						$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = array("VALUE" => array("TEXT" => $html_text, "TYPE" => 'html'));
					}

					$html_text = file_get_contents($_SERVER['DOCUMENT_ROOT'].$file_path);
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]]['n'.$key] = array("VALUE" => array("TEXT" => $html_text, "TYPE" => 'html'));
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='texth') {
				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = array("VALUE" => array("TEXT" => $value, "TYPE" => 'text'));
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='texth_path') {
				if (strpos($value, 'http')===0 || strpos($value, 'https')===0) {
					//$textProp = CSofteffectXlsHelper::convertRussianDomens($value);
					$textProp = $value;
				} else {
					$textProp = $_SERVER['DOCUMENT_ROOT'].$value;
				}

				$res = CFile::CheckFile(CFile::MakeFileArray($textProp));
				if (strlen($res)<=0) {
					$texth_text = file_get_contents($textProp);
					$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = array("VALUE" => array("TEXT" => $texth_text, "TYPE" => 'text'));
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='texth_pathlist') {
				$arFilesList = explode($XLS_PROPERTY_SEPARATOR, $value);
				foreach ($arFilesList as $key => $file_path) {
					if (strpos($file_path, 'http')===0 || strpos($file_path, 'https')===0) {
						//$textProp = CSofteffectXlsHelper::convertRussianDomens($file_path);
						$textProp = $file_path;
					} else {
						$textProp = $_SERVER['DOCUMENT_ROOT'].$file_path;
					}

					$res = CFile::CheckFile(CFile::MakeFileArray($textProp));
					if (strlen($res)<=0) {
						$texth_text = file_get_contents($textProp);
						$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]]['n'.$key] = array("VALUE" => array("TEXT" => $texth_text, "TYPE" => 'text'));
					}
				}
			} elseif ($XLS_SELECT_TYPE_PROPERTY[$column]=='textlist' || $XLS_SELECT_TYPE_PROPERTY[$column]=='numberlist') {
				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = htmlspecialcharsback(explode($XLS_PROPERTY_SEPARATOR, $value));
			} else {
				$arData['PROPERTY_VALUES'][$XlsArRealColumns[$column]] = htmlspecialcharsback($value);
			}
		}

		// auto generate CODE for element
		if (!$arData['CODE']) {
			$arParams = array(
				"max_len" => $arFieldsIBlock['CODE']['DEFAULT_VALUE']['TRANS_LEN'],
				"change_case" => $arFieldsIBlock['CODE']['DEFAULT_VALUE']['TRANS_CASE'],
				"replace_space" => $arFieldsIBlock['CODE']['DEFAULT_VALUE']['TRANS_SPACE'],
				"replace_other" => $arFieldsIBlock['CODE']['DEFAULT_VALUE']['TRANS_OTHER'],
			);
			$arData['CODE'] = Cutil::translit($arData['NAME'], "ru", $arParams);
		}

		/*if (is_null($arDataForCatalog['QUANTITY'])) {
			$arDataForCatalog['QUANTITY']=0;
		}*/

		/*if (!$arDataForCatalog['WEIGHT']) {
			$arDataForCatalog['WEIGHT']=0;
		}*/

		if ($XLS_QUANTITY_TRACE=='1') {
			$arDataForCatalog['QUANTITY_TRACE']='Y';
		}

		$IBElement = new CIBlockElement;

		// delaem neaktivnym, esli kol-vo nulevoe
		if ($XLS_BITRIX_COUNT_NULL && !$arDataForCatalog['QUANTITY']) {
			$arData['ACTIVE'] = 'N';
		}

		// delaem neaktivnym, esli cena nulevaya
		if ($XLS_BITRIX_PRICE_NULL && count($priceValue)<=0 && !$arDataForCatalog['PURCHASING_PRICE']) {
			$arData['ACTIVE'] = 'N';
		}

		if (!$arDataForCatalog['PURCHASING_CURRENCY']) {
			$arDataForCatalog['PURCHASING_CURRENCY'] = $XLS_PRICE_PARAM_CURRENCY;
		}

		// Esli est' sootvetstvuyuschiy ID, obnovlyaem
		if ((int)$XLS_DATA_ID_BITRIX[$realRow]) {
			$dbEl = CIBlockElement::GetList(array('SORT'=>'ASC'), array('ID'=>(int)$XLS_DATA_ID_BITRIX[$realRow]), FALSE, FALSE, array('IBLOCK_ID', 'ID', 'CODE'));
			if ($arEl = $dbEl->Fetch()) {
				// ne izmenyaem CODE esli uje zapolnen
				if ($arEl['CODE']!='') unset($arData['CODE']);
				if ($XLS_IBLOCK_NAME_AUTO) {
					unset($arData['NAME']);
				}
			}

			$propsList = $arData['PROPERTY_VALUES'];
			unset($arData['PROPERTY_VALUES']);

			$res = $IBElement->update((int)$XLS_DATA_ID_BITRIX[$realRow], $arData, false, false, true, true);

			CIBlockElement::SetPropertyValuesEx((int)$XLS_DATA_ID_BITRIX[$realRow], false, $propsList);

			if ($res) {
				$_SESSION['arElemsChange'][]=(int)$XLS_DATA_ID_BITRIX[$realRow];
				$XLS_RESULT_COUNT_UPDATE++;

				if (CModule::IncludeModule('catalog') && CModule::IncludeModule('sale')) {
					// Obnovim dannye torgovogo kataloga
					if (count($priceValue)>0) {
						// proveryaem, est' li dannye po cene v torgovom kataloge
						$arGIDNotDel=array();
						$dbCatalogPrice = CPrice::GetList(array(), array('PRODUCT_ID' => (int)$XLS_DATA_ID_BITRIX[$realRow]));
						while ($arCatalogPrice = $dbCatalogPrice->GetNext()) {
							if (isset($priceValue[$arCatalogPrice["CATALOG_GROUP_ID"]])) {
								if (floatval($priceValue[$arCatalogPrice["CATALOG_GROUP_ID"]])>0) {
									CPrice::Update(
										$arCatalogPrice["ID"],
										array(
											'PRODUCT_ID' => (int)$XLS_DATA_ID_BITRIX[$realRow],
											'PRICE' => $priceValue[$arCatalogPrice["CATALOG_GROUP_ID"]],
											'CATALOG_GROUP_ID' => $arCatalogPrice["CATALOG_GROUP_ID"],
											'CURRENCY' => ($priceCurrency[$arCatalogPrice["CATALOG_GROUP_ID"]]) ? $priceCurrency[$arCatalogPrice["CATALOG_GROUP_ID"]] : $XLS_PRICE_PARAM_CURRENCY
										)
									);
									$arGIDNotDel[]=$arCatalogPrice['ID'];
								}

								unset($priceValue[$arCatalogPrice["CATALOG_GROUP_ID"]]);
							} elseif (!in_array($arCatalogPrice["ID"], $arGIDNotDel) && floatval($arCatalogPrice["PRICE"])>0) {
								$arGIDNotDel[]=$arCatalogPrice['ID'];
							}
						}

						if (count($arGIDNotDel)>0) {
							CPrice::DeleteByProduct((int)$XLS_DATA_ID_BITRIX[$realRow], $arGIDNotDel);
						}

						if (count($priceValue)>0) {
							foreach ($XlsArRealColumns as $key => $value) {
								if (strpos($value, 'iblock_price_')!==FALSE) {
									$priceGroupId = str_replace('iblock_price_', '', $value);
									if (isset($priceValue[$priceGroupId]) && floatval($priceValue[$priceGroupId])>0) {
										$dbPrice = CPrice::Add(
											array(
												'PRICE' => $priceValue[$priceGroupId],
												'PRODUCT_ID' => (int)$XLS_DATA_ID_BITRIX[$realRow],
												'CATALOG_GROUP_ID' => $priceGroupId,
												'CURRENCY' => ($priceCurrency[$priceGroupId]) ? $priceCurrency[$priceGroupId] : $XLS_PRICE_PARAM_CURRENCY
											)
										);
									}
								}
							}
						}
					}

					// proveryaem, est' li dannye po kol-vu v torgovom kataloge
					$arCatalogProduct = CCatalogProduct::GetByID((int)$XLS_DATA_ID_BITRIX[$realRow]);
					if ($arCatalogProduct) {
						if (is_null($arDataForCatalog['QUANTITY']) && intval($arCatalogProduct['QUANTITY'])>0) {
							$arDataForCatalog['QUANTITY']=intval($arCatalogProduct['QUANTITY']);
						}

						$resUpdate = CCatalogProduct::Update((int)$XLS_DATA_ID_BITRIX[$realRow], $arDataForCatalog);
					} else {
						$arDataForCatalog['ID'] = (int)$XLS_DATA_ID_BITRIX[$realRow];
						$resAdd = CCatalogProduct::Add($arDataForCatalog);
					}

					//obnovlenie ili dobavlenie skladov
					if(is_array($storeValue)) {
						foreach ($storeValue as $key => $value) {
							$rsProps = CCatalogStoreProduct::GetList(array(),array("PRODUCT_ID"=>(int)$XLS_DATA_ID_BITRIX[$realRow], "STORE_ID"=>$key),false,false,array('ID','IBLOCK_ID'));
							if($arID = $rsProps->GetNext()) {
								CCatalogStoreProductAll::Update($arID["ID"],array("AMOUNT"=> $value));
							} else {
								CCatalogStoreProduct::Add(array("PRODUCT_ID"=>(int)$XLS_DATA_ID_BITRIX[$realRow], "AMOUNT"=>$value,"STORE_ID"=>$key));
							}
						}
					}
				}
			} else {
				$_SESSION['arError'][$arData['NAME']]=$IBElement->LAST_ERROR;
			}
		} elseif ($XLS_NEW_ELEMENT_SKIP!='1') { // $XLS_NEW_ELEMENT_SKIP==1 - ne zagrujat' novye elementy
			// delaem neaktivnym, esli novyy element
			if ($XLS_BITRIX_UNACTIVE) {
				$arData['ACTIVE'] = 'N';
			}

			// Sozdaem novyy element
			$idIBElement = $IBElement->add($arData, false, false, true);
			if ($idIBElement>0) {
				$_SESSION['arElemsChange'][]=$idIBElement;
				$XLS_RESULT_COUNT_INSERT++;
				if (CModule::IncludeModule('catalog') && CModule::IncludeModule('sale')) { // Dobavim dannye torgovogo kataloga
					foreach ($XlsArRealColumns as $key => $value) {
						if (strpos($value, 'iblock_price_')!==FALSE) {
							$priceGroupId = str_replace('iblock_price_', '', $value);

							$obPrice = new CPrice();
							$obPrice->Add(
								array(
									'PRICE' => $priceValue[$priceGroupId],
									'PRODUCT_ID' => $idIBElement,
									'CATALOG_GROUP_ID' => $priceGroupId,
									'CURRENCY' => ($priceCurrency[$priceGroupId]) ? $priceCurrency[$priceGroupId] : $XLS_PRICE_PARAM_CURRENCY
								)
							);
						}
					}
					$arDataForCatalog['ID'] = $idIBElement;
					CCatalogProduct::Add($arDataForCatalog);

					//obnovlenie skladov
					if(is_array($storeValue)) {
						foreach ($storeValue as $key => $value) {
							CCatalogStoreProduct::Add(array("PRODUCT_ID"=>$idIBElement, "AMOUNT"=>$value,"STORE_ID"=>$key));
						}
					}
				}
			} else {
				$_SESSION['arError']['row file: '.$row]=$row.': '.$IBElement->LAST_ERROR;
			}
		}

	} else {
		### DEYSTVIE 4. deaktiviruem lishnie elementy pri neobhodimosti ###
		$_SESSION['arElemsChange'] = array_unique($_SESSION['arElemsChange']);
		if ($XLS_DEACTIVATE_ELEMENTS=='on' && count($_SESSION['arElemsChange'])>0) {
			$_SESSION['arElemsChange'] = array_unique($_SESSION['arElemsChange']);
			$arFilterDeact = array('IBLOCK_ID'=>$XLS_IBLOCK_ID, '!ID'=>$_SESSION['arElemsChange'], 'INCLUDE_SUBSECTIONS'=>'Y');
			if ($XLS_IBLOCK_SECTION_ID) $arFilterDeact['SECTION_ID'] = $XLS_IBLOCK_SECTION_ID;

			$dbEl = CIBlockElement::GetList(array('SORT'=>'ASC'), $arFilterDeact, FALSE, FALSE, array('IBLOCK_ID', 'ID'));
			while ($arEl = $dbEl->GetNext()) {
				$el = new CIBlockElement;
				$PROP = array();

				$arLoadProductArray = Array(
					"ACTIVE" => "N",
				);

				$res = $el->Update($arEl['ID'], $arLoadProductArray);
			}
		}

		$end=TRUE;

		$stringToEcho = ($row-1).'||';
		if (count($_SESSION['arError'])>0) {
			$stringToEcho .= implode('<br />', $_SESSION['arError']);
		}
		$stringToEcho .= '||The End';

		echo $stringToEcho;
		unset($_SESSION['startRow']);
		break;
	}
	//echo '<pre>'; print_r($arRow); echo '</pre>';
}

if (!$end) {
	echo $row-1; // ibo odin lishniy cikl
	$_SESSION['startRow'] = $startRow+$XLS_STEP5_CHUNKSIZE;
}
?>