<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); 

global $MCART_IS_SKU;
$MCART_IS_SKU = false;
global $DB;
$db_type=strtolower($DB->type);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/classes/".$db_type."/profile.php"); 
if(
  (CModule::IncludeModule('catalog'))&&
  (CModule::IncludeModule('sale'))
  )
	$MCART_IS_SKU = true;
?>

<?
$inputFileName = $_SESSION["MCART_XLS_ARRAY"]['INPUT_FILENAME'];

$bEnd = false;
CModule::IncludeModule('mcart.xls');
 $langCls = new CMcartXlsStrRef();

 
 
$firstColumn = $_SESSION["MCART_XLS_ARRAY"]["DIAPAZONE_A"];
$highestColumn = $_SESSION["MCART_XLS_ARRAY"]["DIAPAZONE_Z"];	
$SECTION = $_SESSION["MCART_XLS_ARRAY"]["SECTION"];	
$SECTION_FOR_NEW = $_SESSION["MCART_XLS_ARRAY"]["SECTION_FOR_NEW"];	
/*$arrToInt = $_SESSION["MCART_XLS_ARRAY"]['ACTION_MODIFY']['TO_INT'];
$arrToLink = $_SESSION["MCART_XLS_ARRAY"]['ACTION_MODIFY']['TO_LINK'];
$arrTakeTwo = $_SESSION["MCART_XLS_ARRAY"]['ACTION_MODIFY']['TAKE_TWO'];
$arrTakeThree = $_SESSION["MCART_XLS_ARRAY"]['ACTION_MODIFY']['TAKE_THREE'];
*/
$arrSubactionParams = $_SESSION["MCART_XLS_ARRAY"]['ACTION_MODIFY']["SUBACTION_PARAMS"];
$arrDelSubstr = $_SESSION["MCART_XLS_ARRAY"]['ACTION_MODIFY']["DEL_SUBSTR"];
$MAKE_TRANSLIT_CODE = $_SESSION["MCART_XLS_ARRAY"]["MAKE_TRANSLIT_CODE"];
$strError = $_SESSION["MCART_XLS_ARRAY"]['strError'];
$PROP_COLUMNS = $_SESSION["MCART_XLS_ARRAY"]['COLUMNS'];
$IDENTIFY =  $_SESSION["MCART_XLS_ARRAY"]['IDENTIFY'];
//print "<pre>"; print_r($IDENTIFY); print "</pre>";
$COLUMNS = array();

$CATALOG_PRICE_BASE_ID = $_SESSION["MCART_XLS_ARRAY"]['CATALOG_PRICE_BASE_ID'];
$arrDatetime = array();
	
$handle = fopen ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/err_log.txt", "a");	
	
include $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/mcart.xls/classes/general/PHPExcel/IOFactory.php';
	 
	 try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			
			if ($inputFileType=='CSV')
			{
				
				if (ini_get('mbstring.func_overload') & 2) 
				{
				
					die(GetMessage("MCART_WRONG_FILE_FORMAT")."</br><a href = '/bitrix/admin/mcart_xls_import.php'>".GetMessage("STEP_BACK")."</a>");
				
				}
			}
			
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			$worksheet_names = $objReader->listWorksheetNames($inputFileName);
//print_r($worksheet_names);
			
		} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		$sheet = $objPHPExcel->getSheet($_SESSION['ARR_XLS_DATA']["SHEET_ID"]); 
		
		$trueHighestRow = $_SESSION["MCART_XLS_ARRAY"]["HIGHEST_ROW"];
		$firstRow = $_SESSION['START_ROW'];
		
		$ROWS_COUNT = COption::GetOptionInt("mcart.xls", "ROWS_COUNT", 50);
		
		$highestRow =$firstRow+$ROWS_COUNT;
		if ($highestRow>=$trueHighestRow)
			{
			$highestRow=$trueHighestRow;
			$bEnd = true;
			}
		
		
		
		$mergedCellsRange = $sheet->getMergeCells();
		
		for ($row = $firstRow; $row <= $highestRow; $row++)
		{ 
			$rowData = $sheet->rangeToArray($firstColumn . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE,
											true
											);
											foreach ($rowData as $row_id=>$row_val)
											{
											
											$global_row_id = $row_id;
											
												$col_id_int = 0;
												foreach($row_val as $col_id=>$txt)
												{
												
												$isInRange = false;
												
												$cell = $sheet->getCell($col_id.$row_id);
												//echo "address = ".$col_id.$row_id."</br>";
												$merge_index = 0;
													if ($col_id==$IDENTIFY['xls'])
														{
														$txt = $cell->getValue();
													
													switch ($IDENTIFY['modify_type']) {
															case 'XLS_MODIFY_TYPE_TO_LINK':
																$tmpArrLink =  $sheet->getHyperlink($col_id.$global_row_id);
																$tmpLink = each($tmpArrLink);
																$value = $tmpLink['value'];
															break;
															case 'XLS_MODIFY_TYPE_TO_INT':
																$value = IntVal($langCls->ConvertArrayCharset($txt, BP_EI_DIRECTION_IMPORT));
																break;
															
															default:
																$value = $langCls->ConvertArrayCharset($txt, BP_EI_DIRECTION_IMPORT);
																}	
														
														$COLUMNS[$row_id]["IDENTIFY"] = $value;
														$codeColumn = $IDENTIFY['bx'];
														if (substr($codeColumn, 0, 9)=='PROPERTY_')
															{
															$codeColumn = substr($codeColumn, 9);
															$COLUMNS[$row_id]["PROP"][$codeColumn] = $value;	
															}
														else
															$COLUMNS[$row_id][$codeColumn] = $value;	
														//$COLUMNS[$row_id]["IDENTIFY_CODE"] = 
														}
													foreach ($PROP_COLUMNS[$col_id] as $codeColumn=>$arrPropColumn)
													{
													
														foreach($mergedCellsRange as $currMergedRange) 
														{
														
														if($cell->isInRange($currMergedRange)) {
																$currMergedCellsArray = PHPExcel_Cell::splitRange($currMergedRange);
																
																$merge2CELL = $sheet->getCell($currMergedCellsArray[0][0]);
																
																if ($col_id==$IDENTIFY['xls'])
																{
																$global_row_id = $merge2CELL->GetRow();
																$merge_index = $row_id - $global_row_id;
																$ArrMergeRows[$row_id] = array('mi'=>$merge_index, 'gl_r'=>$global_row_id);
																}
																
																$cell = $merge2CELL;
															}
															
														}
													$txt = $cell->getValue();
													
													switch ($arrPropColumn['action']) {
															case 'XLS_MODIFY_TYPE_TO_LINK':
																$tmpArrLink =  $sheet->getHyperlink($col_id.$global_row_id);
																$tmpLink = each($tmpArrLink);
																$value = $tmpLink['value'];
															break;
															case 'XLS_MODIFY_TYPE_TO_INT':
																$value = IntVal($langCls->ConvertArrayCharset($txt, BP_EI_DIRECTION_IMPORT));
																break;
															/*case 'XLS_TAKE_VALUE_TWO':
																if ()
																$value = IntVal($langCls->ConvertArrayCharset($txt, BP_EI_DIRECTION_IMPORT));
																break;
															*/	
															default:
																$value = $langCls->ConvertArrayCharset($txt, BP_EI_DIRECTION_IMPORT);
																}	
/*															elseif(in_array($col_id, $arrDatetime))	
																$txt = $cell->getFormattedValue();
*/													
														if (substr($codeColumn, 0, 9)=='PROPERTY_')
															{
															$codeColumn = substr($codeColumn, 9);
															$COLUMNS[$row_id]["PROP"][$codeColumn] = $value;	
															}
														else
															$COLUMNS[$row_id][$codeColumn] = $value;	
													
													}
												}
											
											
											}
										
										
											
											
		}
		//print "<pre>"; print_r($COLUMNS); print "</pre>"; echo "The End</br>"; die();	
		//==============================================================================================================================================================================
		//==============================================================================================================================================================================
		if (CModule::IncludeModule('iblock'))
{
	$ielcount = $_SESSION["MCART_XLS_ARRAY"]['ADD_COUNT'];
	$ierrcount = $_SESSION["MCART_XLS_ARRAY"]['ERR_COUNT'];
	$ielUpdcount = $_SESSION["MCART_XLS_ARRAY"]['UPDATE_COUNT'];
	
	
	//=====================================================	
	$XLS_IBLOCK_ID = $_SESSION["MCART_XLS_ARRAY"]['IBLOCK_ID'];
	
	$arrSelectedFields = array("NAME", "IBLOCK_SECTION_ID", "IBLOCK_ID", "ID", "CODE", "DETAIL_TEXT", "PREVIEW_TEXT", $IDENTIFY['bx']);
	$arrSelectedFields = array_merge($arrSelectedFields, $_SESSION["MCART_XLS_ARRAY"]["SELECTED_FIELDS"]);
	
	//print "<pre>"; print_r($arrSelectedFields); print "</pre>"; echo "iblock id = ".$XLS_IBLOCK_ID;
	
		$IBLOCK_ELEMENTS = array();
		$arrSelectedFields[] = "CATALOG_GROUP_".$CATALOG_PRICE_BASE_ID;
		
		if (substr($IDENTIFY['bx'], 0, 9)=='PROPERTY_')
			$IDENTIFY_CODE = $IDENTIFY['bx']."_VALUE";
		else
			$IDENTIFY_CODE = $IDENTIFY['bx'];
		$list = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$XLS_IBLOCK_ID), false, false, $arrSelectedFields);
			while ($search_el = $list->GetNext())
			if (!empty($search_el[$IDENTIFY_CODE]))
				{
				$IBLOCK_ELEMENTS[] = array("IDENTIFY"=>$search_el[$IDENTIFY_CODE], "ELEMENT"=>$search_el);
				}
		//======================================================================
		//print "<pre>"; print_r($IBLOCK_ELEMENTS); print "</pre>"; 
		
		//print "<pre>"; print_r($COLUMNS); print "</pre>"; echo "The End</br>"; die();
	
	foreach ($COLUMNS as $COLUMN1)
	{
	
	
	if (empty($COLUMN1["IDENTIFY"]))
		continue;
		
		$el = new CIBlockElement;

		$PROP = array();
		$detail_text = "";
		$preview_text = "";
		$amount = 0;
		$base_price = 0;
		$purchasing_price = 0;
		$arFilter = array();
		
		$SEARCH_EL_ID=false;
		$PRODUCT_ID = "";
		$section_old = false;
		$search_el = false;
		
		foreach ($IBLOCK_ELEMENTS as $test_element)
			{
			if ($test_element["IDENTIFY"]==$COLUMN1["IDENTIFY"])
				{
				$search_el = $test_element["ELEMENT"];
				break;
				}
			}
		
		if ($search_el)
		{	
		
			$SEARCH_EL_ID = $search_el["ID"];
			
			//echo " нашли элемент".$search_el["ID"]."</br>";
			
			$section_old = $search_el["IBLOCK_SECTION_ID"];
			
			$bNeedUpdate = false;
			
			
			
			if (trim($search_el["~NAME"])!==$COLUMN1["NAME"])
				{
				$bNeedUpdate = true;
				
				}
			elseif ((trim($search_el["DETAIL_TEXT"])!==$COLUMN1["DETAIL_TEXT"])&&(!empty($COLUMN1["DETAIL_TEXT"])))	
				{
				$bNeedUpdate = true;
				//echo "неравно детально текст<br>";
				}
			elseif ((trim($search_el["PREVIEW_TEXT"])!==$COLUMN1["PREVIEW_TEXT"])&&(!empty($COLUMN1["PREVIEW_TEXT"])))
				{
				$bNeedUpdate = true;
				//echo "неравно текст превью<br>";
				}
			else
				{
				foreach ($COLUMN1["PROP"] as $key_prop=>$prop_val)
					if (trim($search_el["PROPERTY_".$key_prop."_VALUE"])!=$prop_val)
						{
						$bNeedUpdate = true;	
						//echo "неравно ".$key_prop." текст<br>";
						break;
						}
				}
		}
		else
			{
			$section_old = false;
			$search_el = null;
			}
		
		if ($SECTION==0)
			$tmpSection = false;
		elseif ($SECTION == -1)	
			$tmpSection= $section_old;
		else
			$tmpSection= $SECTION;
		
		$arLoadProductArray = $COLUMN1;
		
		
		
		
		unset ($arLoadProductArray["IDENTIFY"]);
		unset($arLoadProductArray["PROP"]);
		
		$base_price = $COLUMN1["FLD_CATALOG_BASE_PRICE"];
		$purchasing_price =$COLUMN1["FLD_PURCHASING_PRICE"];
		$amount = $COLUMN1['FLD_AMOUNT'];
		
		if (($base_price>0)||($purchasing_price>0)||($amount>0))
			$bNeedUpdate = true;	
		
		$arLoadProductArray["MODIFIED_BY"]= $USER->GetID(); 
		$arLoadProductArray["IBLOCK_ID"]= $XLS_IBLOCK_ID;
		$arLoadProductArray["ACTIVE"]= "Y";
		$arLoadProductArray["CATALOG_PRICE_".$CATALOG_PRICE_BASE_ID] =$base_price; 
		
		if (intval($MAKE_TRANSLIT_CODE)==1)
		{

			$params = Array(
							"max_len" => "75", // обрезает символьный код до 75 символов
							"change_case" => "L", // буквы преобразуются к нижнему регистру
							"replace_space" => "-", // меняем пробелы на нижнее подчеркивание
							"replace_other" => "-", // меняем левые символы на нижнее подчеркивание
							"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
							"use_google" => "false", // отключаем использование google
						 );
				
				$CODE = CUtil::Translit($arLoadProductArray["NAME"], "ru", $params);
			if (!empty($CODE))	
				$arLoadProductArray["CODE"] = $CODE;
			
			if ((!empty($search_el))&&($search_el["CODE"]!=$CODE))
				$bNeedUpdate = true;
			
		}
		//print "<pre>"; print_r($COLUMN1); print "</pre>"; 
		
		
		if (!empty($SEARCH_EL_ID))
			{
			if ($bNeedUpdate)
				{
					$arLoadProductArray["IBLOCK_SECTION_ID"] = $tmpSection;
					//echo "обновляем элемент".$SEARCH_EL_ID;
					//	print "<pre>"; print_r($arLoadProductArray); print "</pre>"; //echo "The End</br>"; die();
					$res = $el->Update($SEARCH_EL_ID, $arLoadProductArray, false, false, true);
					
					CIBlockElement::SetPropertyValuesEx($SEARCH_EL_ID, false, $COLUMN1["PROP"]);	
					
					  $ielUpdcount++;
				}
			$PRODUCT_ID = $SEARCH_EL_ID;	
			
			}
			
			
		else
		{
		$arLoadProductArray["IBLOCK_SECTION_ID"] = $SECTION_FOR_NEW;
		$arLoadProductArray["PROPERTY_VALUES"] = $COLUMN1["PROP"];
		if( $PRODUCT_ID = $el->Add($arLoadProductArray, false, false, true))
			{
			//echo GetMessage("XLS_ELEMENT_ADDED").$PRODUCT_ID."</br>";
			  $ielcount++;
			}
			
		else
		  {
		  //$handle = fopen ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/err_log.txt", "a");
		  fwrite($handle, print_r($arLoadProductArray,1)."\n");
		  fwrite($handle, $el->LAST_ERROR."\n");
		  $strError = $strError ."</br> ".$el->LAST_ERROR."</br>";
		  $ierrcount++;
		  }	
			
		}	
		if (!empty($PRODUCT_ID))
		
		  {	$SKU_ID ="";
			 
			
			
		  
			if ((!empty($SKU_IBLOCK_ID))&&(empty($SEARCH_EL_ID)))
			{	
			
				$PROP[$CML2_LINK_CODE] = $PRODUCT_ID;
				$arLoadSKUArray = Array(
					  "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
					  "IBLOCK_SECTION_ID" => $SECTION,          // элемент лежит в корне раздела
					  "IBLOCK_ID"      => $SKU_IBLOCK_ID,
					  "PROPERTY_VALUES"=> $PROP,
					  "NAME"           => $one_row[$NAME_ID],
					  "ACTIVE"         => "Y",            // активен
					  "PREVIEW_TEXT"   => $preview_text,
					  "DETAIL_TEXT"    => $detail_text,
					  "CATALOG_PRICE_".$CATALOG_PRICE_BASE_ID =>$base_price, 
					  "DETAIL_PICTURE" => $detail_picture,
					  "PREVIEW_PICTURE" => $preview_picture
					  
					  );
				if($SKU_ID = $el->Add($arLoadSKUArray, false, false, true))
					{
					
					
					}
					

			}
			else
				{
				
				if (intval($amount)>0)
					
						CCatalogProduct::Update($PRODUCT_ID, array("QUANTITY"=>$amount));
				}
			 
			if (!empty($SKU_ID))
				$PRICE_PROD_ID = $SKU_ID;
			else	
				$PRICE_PROD_ID = $PRODUCT_ID;
			

			if ($MCART_IS_SKU)
			{	
			 if ($base_price>0)// добавление базовой цены
			  {
			  $base_price = str_replace(",", ".", $base_price);
			  CCatalogProduct::Add(array("ID"=>$PRICE_PROD_ID));
				
				$arCatalogFields = Array(
					"PRODUCT_ID" => $PRICE_PROD_ID,
					"CATALOG_GROUP_ID" => $CATALOG_PRICE_BASE_ID,
					"PRICE" => $base_price,
					"CURRENCY" => "RUB",
					"QUANTITY_FROM" => false,
					"QUANTITY_TO" => false
					
				);

					//$obPrice = new CPrice();
					
					$res = CPrice::GetList(
							array(),
							array(
									"PRODUCT_ID" => $PRICE_PROD_ID,
									"CATALOG_GROUP_ID" => $CATALOG_PRICE_BASE_ID
								)
						);

					if ($arr = $res->Fetch())
					{
						CPrice::Update($arr["ID"], $arCatalogFields);
					}
					else
					{
						CPrice::Add($arCatalogFields);
					}
					
					
					/*
					if (!$obPrice->Add($arCatalogFields,false))
					{
					 $e = $APPLICATION->GetException();
					 $str = $e->GetString();
					 echo $str;
					}
					*/
		  
			  }
			  
			  if($purchasing_price>0)// добавление закупочной цены
			  {
			  $purchasing_price = str_replace(",", ".", $purchasing_price);
				$arPurchFields = array("PURCHASING_PRICE" => $purchasing_price);// зарезервированное количество
				CCatalogProduct::Update($PRICE_PROD_ID, $arPurchFields);
			  }
			  
			 if (intval($amount)>0)
						CCatalogProduct::Update($PRICE_PROD_ID, array("QUANTITY"=>$amount));
			}
			  
		  }
		else
		  {
		  //$handle = fopen ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.xls/err_log.txt", "a");
		  fwrite($handle, $el->LAST_ERROR."\n");
		  $strError = $strError ."</br> ".$el->LAST_ERROR."</br>";
		  $ierrcount++;
		  }
			
			
			
	}
	
}
		//==============================================================================================================================================================================
		//==============================================================================================================================================================================
		
		$_SESSION['START_ROW'] = $highestRow+1;
		
		$_SESSION["MCART_XLS_ARRAY"]['ADD_COUNT'] = $ielcount;
	$_SESSION["MCART_XLS_ARRAY"]['ERR_COUNT'] = $ierrcount;
	$_SESSION["MCART_XLS_ARRAY"]['UPDATE_COUNT'] = $ielUpdcount;
	$_SESSION["MCART_XLS_ARRAY"]['strError'] = $strError;
		
		if ($bEnd)
			echo "The End</br>Added: ".$ielcount."</br>Updated: ".$ielUpdcount."</br>Errors: ".$ierrcount.$strError;
		else
			echo "string number ".$firstRow." to ".$highestRow.$strError;
?>