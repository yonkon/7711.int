<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
CJSCore::Init(array('jquery'));

### DEYSTVIE 1. Poluchaem ob"ekt Lista i massiv stolbcov ###
$Sheet = $XlsExcel->getSheet((int)$XLS_LIST);

$column = $XLS_FIRST_COLUMN_HEADER - 1;
while ($column < 200) { // ogranichenie na zaciklivanie
	$Cell = $Sheet->getCellByColumnAndRow($column, $XLS_FIRST_ROW_HEADER);
	$value = $Cell->getValue();

	if (!$value || !$XLS_SELECT_UPLOAD_PROPERTY[$column]) {
		$column++;
		continue;
	}

	$XlsArColumns[$column] = $XlsExcel->iconv($value);
	$column++;
}

$arIBlockPropsCode=array();
$dbIblockProps = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$XLS_IBLOCK_ID));
while ($arIblockProp = $dbIblockProps->GetNext()) {
	$arIBlockPropsCode[]=$arIblockProp['CODE'];
}

### DEYSTVIE 2. Sozdaem novye svoystva v infobloke ###
$XlsArRealColumns = array();
if (count($XLS_SELECT_IBLOCK_PROPERTY)) {
	foreach ($XLS_SELECT_IBLOCK_PROPERTY as $column=>$name) {
		// esli nado, sozdaem novoe svoystvo
		if ($name == '__xls_new_prop') {
			if ($XlsArColumns[$column]) {
				$text = $XlsArColumns[$column];
				if ($charset!='windows-1251') {
					$text = $XlsExcel->iconv($XlsArColumns[$column]);
				}

				$generatedCode = ToUpper("xls_".CSofteffectXlsHelper::translit($text));

				// proveryaem, est' li uje takoe svo-vo
				if (in_array($generatedCode, $arIBlockPropsCode)) {
					$generatedCode .= "_".rand(0, intval(date('Y')));
				}

				$PROPERTY_TYPE = 'S';
				$USER_TYPE = FALSE;
				if ($XLS_SELECT_TYPE_PROPERTY[$column] == 'list' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'multilist') {
					$PROPERTY_TYPE = 'L';
				} elseif ($XLS_SELECT_TYPE_PROPERTY[$column] == 'number') {
					$PROPERTY_TYPE = 'N';
				} elseif ($XLS_SELECT_TYPE_PROPERTY[$column] == 'html' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'html_path' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'html_pathlist' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'texth' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'texth_path' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'texth_pathlist') {
					$PROPERTY_TYPE = 'S';
					$USER_TYPE = 'HTML';
				} elseif ($XLS_SELECT_TYPE_PROPERTY[$column] == 'file' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'fileslist') {
					$PROPERTY_TYPE = 'F';
				}

				$arFields = Array(
					"NAME" => $XlsArColumns[$column],
					"ACTIVE" => "Y",
					"SORT" => "100",
					"CODE" => $generatedCode,
					"PROPERTY_TYPE" => $PROPERTY_TYPE,
					"MULTIPLE" => ($XLS_SELECT_TYPE_PROPERTY[$column] == 'multilist' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'fileslist' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'html_pathlist' || $XLS_SELECT_TYPE_PROPERTY[$column] == 'texth_pathlist') ? 'Y' : 'N',
					"IBLOCK_ID" => $XLS_IBLOCK_ID
				);

				if ($USER_TYPE) {
					$arFields['USER_TYPE'] = $USER_TYPE;
				}

				$IBProperty = new CIBlockProperty;
				$idProp = $IBProperty->Add($arFields);
				if ($idProp) {
					$XlsArRealColumns[$column] = $generatedCode;
				}  else {
					ShowError($XlsArColumns[$column].': '.$IBProperty->LAST_ERROR);
				}
			}
		} else {
			$XlsArRealColumns[$column] = $name;
		}
	}

	// zapishem novie svoistva v profile
	if ($idProfile) {
		CSofteffectXlsProfile::save($idProfile, array('XLS_SELECT_IBLOCK_PROPERTY'=>$XlsArRealColumns));

		// vse znachenia parametrov vinesem v massiv $_REQUEST
		foreach ($_SESSION['PROFILE_ARRAY'] as $key => $value) {
			$_REQUEST[$key] = $value;
		}

		CSofteffectXlsHelper::saveParamStep('XLS_SELECT_IBLOCK_PROPERTY');
	}
	$USER->SetParam('XlsRealColums', $XlsArRealColumns);
}

##### sobiraem dannye 4go shaga ######
$arResultSTEP4=array(
	'THEAD' => array(),
	'DATA' => array()
);

$column = $XLS_FIRST_COLUMN_HEADER - 1;

// ogranichenie na zaciklivanie
while ($column < 200) {
	$Cell = $Sheet->getCellByColumnAndRow($column, $XLS_FIRST_ROW_HEADER);
	$value = $Cell->getValue();
	if (!$value || !$XLS_SELECT_UPLOAD_PROPERTY[$column]) {
		$column++;
		continue;
	}
	$arResultSTEP4['THEAD'][$column] = $XlsExcel->iconv($value);

	$column++;
}

// Vyberem vse nujnye stroki iz Excel v massiv
$row = $XLS_FIRST_ROW;

// hranim naydennye stroki
$XlsArRows = array();
do {
	$arRow = array();

	// schityvaem stroku po stolbcam
	$format = 'd.m.Y H:i:s';
	for ($column = $XLS_FIRST_COLUMN - 1; $column <= max(array_keys($XLS_SELECT_UPLOAD_PROPERTY)); $column++) {
		if ($XLS_SELECT_UPLOAD_PROPERTY[$column]) {
			$cell = $Sheet->getCellByColumnAndRow($column, $row);
			$val = $cell->getValue();
			if (PHPExcel_Shared_Date::isDateTime($cell)) {
			     $val = date($format, PHPExcel_Shared_Date::ExcelToPHP($val));
			}
			$arRow[$column] = $XlsExcel->iconv($val);
		}
	}

	// Proverim ne pustaya li stroka
	$isEmpty = true;
	foreach ($arRow as $value) {
		if (trim($value) != "") {
			$isEmpty = false;
			break;
		}
	}

	if (!$isEmpty) {
		$XlsArRows[] = $arRow;
		$row++;
	}
} while (!$isEmpty);

$dbXlsArRows = new CDBResult();
$dbXlsArRows->InitFromArray($XlsArRows);

// uroven' vlojennosti dlya elementov
if ($XLS_IBLOCK_SECTION_ID>0) {
	$dbSec = CIBlockSection::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$XLS_IBLOCK_ID, 'ID'=>$XLS_IBLOCK_SECTION_ID));
	$arSec = $dbSec->GetNext();

	$XLS_DEPTH_LEVEL_MAX += $arSec['DEPTH_LEVEL'];

	// stroim put' do sekcii esli gruzim ne v koren' iblock
	$prevPathSec = '';
	$dbSec = CIBlockSection::GetNavChain($XLS_IBLOCK_ID, $arSec['ID']);
	while ($arSec = $dbSec->GetNext()) {
		$prevPathSec .= $arSec['NAME'].'||';
	}

	$IBLOCK_SECTION_ID = $XLS_IBLOCK_SECTION_ID;
} else {
	$prevPathSec = FALSE;
	$arSec['DEPTH_LEVEL']=0;
	$IBLOCK_SECTION_ID = FALSE;
}

/* [id-last-section-in-path]=>textpath-to-section
 * exmpl: [202]=>Playstation 4||Action||Uncharted
 * section "Uncharted" have id=202
*/

$prevDepthLevel=1;
$arTreeSections=array();

$dbSec = CIBlockSection::GetList(array("left_margin"=>"asc"), array('IBLOCK_ID'=>$XLS_IBLOCK_ID), FALSE, array());
while ($arSec = $dbSec->GetNext()) {
	if ($arSec['DEPTH_LEVEL']==1) {
		$path = $arSec['NAME'];
	} elseif($arSec['DEPTH_LEVEL']>$prevDepthLevel) {
		$path .= '||'.$arSec['NAME'];
	} elseif ($arSec['DEPTH_LEVEL']<$prevDepthLevel) {
		$arPath = explode('||', $path);
		unset($arPathNew);
		for ($i=0; $i < ($arSec['DEPTH_LEVEL']-1); $i++) {
			$arPathNew[$i]=$arPath[$i];
		}
		$arPathNew[]=$arSec['NAME'];
		$path = implode('||', $arPathNew);
	} else {
		$arPath = explode('||', $path);
		unset($arPath[count($arPath)-1]);
		$arPath[]=$arSec['NAME'];
		$path = implode('||', $arPath);
	}
	$prevDepthLevel = $arSec['DEPTH_LEVEL'];

	$arTreeSections[$arSec['ID']] = $path;
}

$rowIndex = 0;
while ($row = $dbXlsArRows->getNext()) {
	$findEl=TRUE;
	$DepthLevelForEls = $arSec['DEPTH_LEVEL'];
	$pathToSection = '';
	if ($XLS_DEPTH_LEVEL_MAX>1) {
		// multi secs
		$arMultiSecs=array();
		foreach ($XLS_SELECT_IBLOCK_PROPERTY as $column=>$name) {
			if (strpos($name, 'iblock_section_depth')!==FALSE) {
				if (strlen(trim($row[$column]))<=0) {
					break; // esli vdrug kakaya-to sekciya pustaya - preryvaem i pishem v poslednyuyu sozdannuyu
				}
				$arMultiSecs[str_replace('iblock_section_depth_', '', $name)] = $column; // level => column
				$DepthLevelForEls++;
			}
		}

		ksort($arMultiSecs); // level sort
		foreach ($arMultiSecs as $depthLevel => $column) {
			if ($depthLevel>1) $pathToSection .= '||';
			$pathToSection .= $row[$column];
		}

		if ($prevPathSec) {
			$pathToSection = $prevPathSec.$pathToSection;
		}

		if (substr($pathToSection, -2, 2)=='||') {
			$pathToSection = substr($pathToSection, 0, strlen($pathToSection)-2);
		}

		// poluchaem ID (esli est') sekcii gde doljen lejat' element
		if (!$IBLOCK_SECTION_ID = array_search($pathToSection, $arTreeSections)) {
			$findEl = FALSE;
		}
	}

	// algoritm poiska sootvetstvuyuschego tovara v bitrikse
	if ($findEl) {
		$arFilterBitrix = array('IBLOCK_ID' => $XLS_IBLOCK_ID);

		if ($IBLOCK_SECTION_ID) $arFilterBitrix['SECTION_ID'] = $IBLOCK_SECTION_ID;

		if ($XLS_SELECT_IBLOCK_PROPERTY[$XLS_PROPERTY_MAIN]!='iblock_name') {
			$arFilterBitrix['PROPERTY_'.$XlsArRealColumns[$XLS_PROPERTY_MAIN]]=trim($row[$XLS_PROPERTY_MAIN]);
		} else {
			$arFilterBitrix['NAME'] = htmlspecialcharsback(trim($row[array_search('iblock_name', $XLS_SELECT_IBLOCK_PROPERTY)]));
		}

		$arIBElement = CIBlockElement::GetList(array(), $arFilterBitrix, FALSE, FALSE, array('IBLOCK_ID', 'ID'))->GetNext();
	} else {
		$arIBElement = FALSE;
	}

	if ($XLS_STEP4_MODE<3) {
		$arResultSTEP4['DATA'][$rowIndex]=$row; // dlya tablici MODE 1, 2
	}

	$arResultSTEP4['XLS_DATA_ROW_SELECT'][$rowIndex]=1; // dlya MODE 2, 3
	$arResultSTEP4['XLS_DATA_ID_BITRIX'][$rowIndex]=($arIBElement['ID']) ? $arIBElement['ID'] : FALSE; // dlya MODE 2, 3

	$rowIndex++;
}

/***** обработка сво-в типа "ѕоиск элемента по названию" *****/
// поиск сво-в нужного типа и сбор Ќазваний элементов
$searchElemsName=array();
$searchSecsName=array();
$searchElemsID=array();
$searchSecsID=array();

foreach ($XLS_SELECT_TYPE_PROPERTY as $column => $typeProp) {
	if ($typeProp=='element') {
		foreach ($arResultSTEP4['DATA'] as $rowIndex => $row) {
			if (!is_numeric($row[$column])) {
				$searchElemsName[$XlsArRealColumns[$column]][]=$row[$column];
			}
		}

		if (count($searchElemsName[$XlsArRealColumns[$column]])>0) {
			$searchElemsName[$XlsArRealColumns[$column]] = array_unique($searchElemsName[$XlsArRealColumns[$column]]);
		}
	} elseif ($typeProp=='elementlist') {
		$searchElemsName[$XlsArRealColumns[$column]]=array();
		foreach ($arResultSTEP4['DATA'] as $rowIndex => $row) {
			$elL=0;
			$arVal = explode($XLS_PROPERTY_SEPARATOR, $row[$column]);
			foreach ($arVal as $val) { // провер€ем все ли значени€ - числа, иначе все значени€ - имена и их надо искать
				if (is_numeric($val)) $elL++;
			}

			if ($elL<count($arVal)) { // если есть имена
				$searchElemsName[$XlsArRealColumns[$column]] = array_merge($searchElemsName[$XlsArRealColumns[$column]], $arVal);
				$searchElemsName[$XlsArRealColumns[$column]] = array_unique($searchElemsName[$XlsArRealColumns[$column]]);
			}
		}

		if (count($searchElemsName[$XlsArRealColumns[$column]])<=0) {
			unset($searchElemsName[$XlsArRealColumns[$column]]);
		}
	} elseif ($typeProp=='section') {
		foreach ($arResultSTEP4['DATA'] as $rowIndex => $row) {
			if (!is_numeric($row[$column])) {
				$searchSecsName[$XlsArRealColumns[$column]][]=$row[$column];
			}
		}

		if (count($searchSecsName[$XlsArRealColumns[$column]])>0) {
			$searchSecsName[$XlsArRealColumns[$column]] = array_unique($searchSecsName[$XlsArRealColumns[$column]]);
		}
	} elseif ($typeProp=='sectionlist') {
		$searchSecsName[$XlsArRealColumns[$column]]=array();
		foreach ($arResultSTEP4['DATA'] as $rowIndex => $row) {
			$elL=0;
			$arVal = explode($XLS_PROPERTY_SEPARATOR, $row[$column]);
			foreach ($arVal as $val) { // провер€ем все ли значени€ - числа, иначе все значени€ - имена и их надо искать
				if (is_numeric($val)) $elL++;
			}

			if ($elL<count($arVal)) { // если есть имена
				$searchSecsName[$XlsArRealColumns[$column]] = array_merge($searchSecsName[$XlsArRealColumns[$column]], $arVal);
				$searchSecsName[$XlsArRealColumns[$column]] = array_unique($searchSecsName[$XlsArRealColumns[$column]]);
			}
		}

		if (count($searchSecsName[$XlsArRealColumns[$column]])<=0) {
			unset($searchSecsName[$XlsArRealColumns[$column]]);
		}
	}
}

// поиск сво-в дл€ вычислени€ инфоблока, в котором искать элементы
foreach ($searchElemsName as $codeProp => $elemNames) {
	$dbProp = CIBlockProperty::GetByID($codeProp, $XLS_IBLOCK_ID);
	if ($arProp = $dbProp->GetNext()) {
		foreach ($elemNames as $key=>$names) {
			$elemNames[$key]=htmlspecialchars_decode($names);
		}
		$dbEl = CIBlockElement::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$arProp['LINK_IBLOCK_ID'], 'NAME'=>$elemNames), FALSE, FALSE, array('IBLOCK_ID', 'ID', 'NAME'));
		while ($arEl = $dbEl->GetNext()) {
			$searchElemsID[$codeProp][$arEl['NAME']] = $arEl['ID'];
		}
	}
}

// поиск сво-в дл€ вычислени€ инфоблока, в котором искать смекции
foreach ($searchSecsName as $codeProp => $secNames) {
	$dbProp = CIBlockProperty::GetByID($codeProp, $XLS_IBLOCK_ID);
	if ($arProp = $dbProp->GetNext()) {
		$dbSec = CIBlockSection::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$arProp['LINK_IBLOCK_ID'], 'NAME'=>$secNames));
		while ($arSec = $dbSec->GetNext()) {
			$searchSecsID[$codeProp][$arSec['NAME']] = $arSec['ID'];
		}
	}
}

// отправл€ем данные на следующий шаг
$arResultSTEP4['XLS_PROP_SEARCHNAME_ID'] = $searchElemsID;
$arResultSTEP4['XLS_PROP_SEARCHNAME_SECID'] = $searchSecsID;

if ($XLS_STEP4_MODE>1) {
	$_SESSION['PROFILE_ARRAY']['XLS_DATA_ROW_SELECT'] = $arResultSTEP4['XLS_DATA_ROW_SELECT'];
	$_SESSION['PROFILE_ARRAY']['XLS_DATA_ID_BITRIX'] = $arResultSTEP4['XLS_DATA_ID_BITRIX'];
	$_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_ID'] = $arResultSTEP4['XLS_PROP_SEARCHNAME_ID'];
	$_SESSION['PROFILE_ARRAY']['XLS_PROP_SEARCHNAME_SECID'] = $arResultSTEP4['XLS_PROP_SEARCHNAME_SECID'];
}
######################################

$COUNT_ALL = count($arResultSTEP4['DATA']);
$readTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_READ_1");;
$countTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_LINE_1");;
$lastDigit=substr($COUNT_ALL, -1, 1);
if ($COUNT_ALL>=11 && $COUNT_ALL<=20 || $lastDigit>=5 && $lastDigit<=9 || $lastDigit==0) {
	$readTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_READ_1");;
	$countTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_LINE_1");;
} elseif ($lastDigit==1) {
	$readTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_READ_2");;
	$countTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_LINE_2");;
} elseif ($lastDigit>=2 && $lastDigit<=4) {
	$readTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_READ_3");;
	$countTitle=GetMessage("SOFTEFFECT_XLS_STEP4_TITLE_LINE_3");;
}

$countOnPages = 50;

file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/include/softeffect.xls/file.txt', serialize($arResultSTEP4));
?>
<tr>
	<td colspan="2">
		<div class="block-title block-title-first">
			<div class="block-title-inner"><?=GetMessage("SOFTEFFECT_XLS_IBLOCK_ADM_IMP_TAB4_FROM_FILE");?> <?=$readTitle?> <span style="color: #850000;"><?=number_format($COUNT_ALL, 0, '.', ' ');?> <?=$countTitle?></span></div>
		</div>
	</td>
</tr>
<tr>
	<td colspan="2">
		<input type="hidden" id="pages-view" value="1" />
		<? if ($XLS_STEP4_MODE<3 && $USER->GetParam('SKIP_ALL_STEPS')!="Y") { ?>
			<div class="table-div">
				<input type="hidden" id="pages_viewed" value="1" />
				<table class="xls_table_property" cellspacing="0" cellpadding="0" width="100%">
					<thead>
						<tr class="head">
							<? if ($XLS_STEP4_MODE=='1') { ?>
								<td align="center" style="width: 30px;">
									<input type="checkbox" id="XLS_DATA_TABLE_SELECT_ALL" value="1" <?if ($XLS_DATA_TABLE_SELECT_ALL == 1) echo 'checked'?> onclick="xls_SelectAllRows(this)" title="<?=GetMessage("SOFTEFFECT_XLS_DATA_TABLE_LOAD_IN_BITRIX")?>" checked="checked">
								</td>
							<? } ?>
							<td align="center" style="width: 81px;">
								<?=GetMessage("SOFTEFFECT_XLS_DATA_TABLE_ELEMENT_BITRIX")?>
							</td>
							<? foreach ($arResultSTEP4['THEAD'] as $column=>$name) { ?>
								<td align="center"><? if ($column==$XLS_PROPERTY_MAIN) { ?><strong><?=$name?></strong><? } else { ?><?=$name?><? } ?></td>
							<? } ?>
						</tr>
					</thead>
					<tbody>
						<?$APPLICATION->IncludeFile('/bitrix/include/softeffect.xls/xls_data_import_step_4_ajax.php', array('XLS_STEP4_MODE'=>$XLS_STEP4_MODE, 'START_ROW'=>0, 'END_ROW'=>$countOnPages, 'CHARSET'=>LANG_CHARSET), array('MODE'=>'php', 'SHOW_BORDER'=>FALSE));?>
					</tbody>
				</table>
				<? if ($XLS_STEP4_MODE>1 && $COUNT_ALL>$countOnPages) { ?>
					<br />
					<center>
						<input type="submit" id="show_more_pages" value="<?=GetMessage("SOFTEFFECT_XLS_STEP4_SHOW_MORE");;?> <?=$countOnPages?>" />
					</center>
				<? } ?>
			</div>
			<script type="text/javascript">
				jQuery('#show_more_pages').click(function(event) {
					var countOnPages = parseInt(<?=CUtil::PhpToJSObject($countOnPages)?>);
					var START_ROW = parseInt($('#pages_viewed').val())*50;
					var END_ROW = START_ROW+countOnPages;

					$.ajax({
						method: 'get',
						url: '/bitrix/include/softeffect.xls/xls_data_import_step_4_ajax.php',
						data: {XLS_STEP4_MODE: '<?=$XLS_STEP4_MODE?>', START_ROW: START_ROW, END_ROW: END_ROW, CHARSET: '<?=LANG_CHARSET?>'},
						success: function(data) {
							$('.xls_table_property tbody').append(data);
							$('#pages_viewed').val(parseInt($('#pages_viewed').val())+1);
						}
					});

					return false;
				});
			</script>
			<br />
		<? } else { ?>
			<img src="/bitrix/templates/.default/ajax/images/wait.gif" class="skip-wait" />&nbsp;<?=GetMessage("SOFTEFFECT_XLS_STEP4_AUTOREDIRECT")?>
			<script type="text/javascript">
				var d = document;
				function document_loaded() {
					setTimeout(function () {
						document.getElementsByName('submit_btn')[0].click();
						document.getElementsByName('submit_btn')[0].disabled = true;
						document.getElementsByName('backButton')[0].disabled = true;
						document.getElementsByName('backButton2')[0].disabled = true;
					}, 500);
				}
				d.addEventListener("DOMContentLoaded", document_loaded, false);
			</script>
		<? } ?>