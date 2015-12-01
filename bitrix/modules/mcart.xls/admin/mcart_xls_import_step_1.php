<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); 
IncludeModuleLangFile( __FILE__);
CJSCore::Init("jquery");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//print "<pre>"; print_r($_SESSION['ARR_REAL_PROFILE']); print "</pre>";
?>

<?
$ARR_REAL_PROFILE = $_SESSION['ARR_REAL_PROFILE'];

$errMess = "";
$XLS_IBLOCK_ID=$_REQUEST["xls_iblock_id"];

$inputFileName =  $_REQUEST['xls_file_name'];


$SKU_IBLOCK_ID ="";
$titleRow = $_REQUEST['title_xls_row'];

$add_sku = $_REQUEST['add_sku'];
if ($add_sku=="Y")
	{$SKU_IBLOCK_ID = $_REQUEST['iblock_sku_id'];
	$CML2_LINK_CODE = $_REQUEST['cml2_link_code'];
	}


if ($XLS_IBLOCK_ID==0)
	$errMess = GetMessage("XLS_NULL_IBLOCK_SET");
	
if (empty($inputFileName))	
	$errMess =$errMess."</br>".GetMessage("XLS_NULL_FILE");

	
$highestColumn = strtoupper($_REQUEST["column_b"]);
$firstColumn = strtoupper($_REQUEST["column_a"]);
$firstRow = $_REQUEST['first_row'];
if (empty($firstRow))
	$firstRow = 1;

$_SESSION['START_ROW'] = $firstRow;
	
if (empty($titleRow))
	$titleRow = $firstRow;	

if (ord($highestColumn)<ord($firstColumn))
{
$tempColumn = $firstColumn;
$firstColumn = $highestColumn;
$highestColumn = $tempColumn;
}

if ((ord($highestColumn)>90)||(ord($firstColumn)<65))
	$errMess.=GetMessage("XLS_WRONG_DIAPAZONE");	

if (empty($errMess))
{

CModule::IncludeModule('mcart.xls');
	 $langCls = new CMcartXlsStrRef();

?>
<form action="/bitrix/admin/mcart_xls_import_step_2.php"  method="POST">
<input type=hidden name="xls_iblock_id" value="<?=intval($XLS_IBLOCK_ID)?>">
<input type=hidden name="xls_input_filename" value="<?=$inputFileName?>">


<?

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
	
	$XLS_SHEET_INDEX = $_REQUEST['xls_shett_index'];
	$_SESSION['ARR_XLS_DATA']["SHEET_ID"] = $XLS_SHEET_INDEX;
	$arRows = array();
	$sheet = $objPHPExcel->getSheet($XLS_SHEET_INDEX); 
	
	
	$LAST_ROW_TYPE = $_REQUEST['rows_end_label'];
	
	switch ($LAST_ROW_TYPE) {
						case 'auto':
							$highestRow = $sheet->getHighestRow(); 
							break;
						case 'lastrownumber':
							$num = $_REQUEST['last_row_num'];
							$highestRow = intval($num); 
							break;	
						default:
							$highestRow = $sheet->getHighestRow(); 
						
						}
	
?>
<input type=hidden name="xls_highest_row" value="<?=$highestRow?>">
<?
	foreach(range(ord($firstColumn), ord($highestColumn)) as $v)
	{
	$alfavit[] = chr($v);
	}

        $arIBlocksSection=Array();
        $arIBlocksSection["-1"] = GetMessage("MCART_SECTION_NO_CHANGE");

        $tmpFfields = CIBlock::getFields($XLS_IBLOCK_ID);
        if ($tmpFfields["IBLOCK_SECTION"]["IS_REQUIRED"] != "Y")
            $arIBlocksSection["0"] = GetMessage("MCART_FIRST_SECTION_XLS");


        $db_list = CIBlockSection::GetList(Array("NAME"=>"ASC"), array("IBLOCK_ID"=>$XLS_IBLOCK_ID));
        while($arRes = $db_list->GetNext())
            $arIBlocksSection[$arRes["ID"]] = $arRes["NAME"];

?>
<h4><?=GetMessage("XLS_SELECT_IBLOCK_SECTION")?></h4>
			<select name = "xls_iblock_section_id" >
				<?foreach ($arIBlocksSection as $key=>$value):?>
				<option value="<?=$key?>"   <?if (isset($ARR_REAL_PROFILE['section_id'])&&($ARR_REAL_PROFILE['section_id']==$key)) echo " selected"?>><?=$value?></option>
				<?endforeach?>
			</select>
				
</br>
<? unset($arIBlocksSection["-1"]);?>

<h4><?=GetMessage("XLS_SELECT_IBLOCK_SECTION_NEW")?></h4>
			<select name = "xls_iblock_section_id_new" >
				<?foreach ($arIBlocksSection as $key=>$value):?>
				<option value="<?=$key?>"   <?if (isset($ARR_REAL_PROFILE['section_new'])&&($ARR_REAL_PROFILE['section_new']==$key)) echo " selected"?>><?=$value?></option>
				<?endforeach?>
			</select>
				
</br>

<h3><?=GetMessage("XLS_DATA_EXAMPLE")?></h3>
</br>
<table border="1">

<tr>
	<? foreach ($alfavit as $bukva):?>
	<td><h3><?=$bukva?></h3></td>
	<?endforeach;?>
</tr>
<?



?>

<?
$ARR_TITLE_ROW = array();

$rowTitleData = $sheet->rangeToArray($firstColumn . $titleRow . ':' . $highestColumn . $titleRow,
											NULL,
											TRUE,
											FALSE);
foreach ($rowTitleData as $row_str)											
	foreach ($row_str as $row_cell)	
			$ARR_TITLE_ROW[] = $row_cell;
											
	for ($row = $firstRow; $row <= ($firstRow+5); $row++)
		{ 
			$rowData = $sheet->rangeToArray($firstColumn . $row . ':' . $highestColumn . $row,
											NULL,
											TRUE,
											FALSE);
											
											?>
											
											
											<?foreach ($rowData as $row_id=>$row_val):?>
											
											<tr>
												<?foreach($row_val as $col_id=>$txt):?>
												<td><?= $langCls->ConvertArrayCharset($txt, BP_EI_DIRECTION_IMPORT)?></td>
												<?endforeach?>
											</tr>
											
											<?endforeach;?>
											
											<?
											
											
		}
?>
</table >
<?
global $MCART_IS_SKU;

	// определение базовой цены	
	if ((CModule::IncludeModule('catalog'))&&(CModule::IncludeModule('sale')))
	{
	$MCART_IS_SKU = true;
	$db_res = GetCatalogGroups(($b="SORT"), ($o="ASC"));
	while ($res = $db_res->Fetch())
	{
		if ($res["BASE"]=="Y")
		{
		$BASE_PRICE_ID = $res["ID"];
		$BASE_PRICE_NAME = $res["NAME_LANG"];
		//print "<pre>"; print_r($res); print "</pre>";
		break;
		}
	  
	}	
	}	
		
	//print "<pre>"; print_r($arRows); print "</pre>";
	$SrcPropID["DETAIL_TEXT"] = GetMessage("XLS_FLD_DETAIL_TEXT");
	$SrcPropID["PREVIEW_TEXT"] = GetMessage("XLS_FLD_PREVIEW_TEXT");
	
	$SrcPropID["DETAIL_PICTURE"] = GetMessage("XLS_FLD_DETAIL_PICTURE");
	$SrcPropID["PREVIEW_PICTURE"] = GetMessage("XLS_FLD_PREVIEW_PICTURE");
	$SrcPropID["FLD_AMOUNT"] = GetMessage("XLS_FLD_AMOUNT");
	
	if ($MCART_IS_SKU)
	{
		$SrcPropID["FLD_CATALOG_BASE_PRICE"] = $BASE_PRICE_NAME;
		$SrcPropID["FLD_PURCHASING_PRICE"] = GetMessage("XLS_FLD_PURCHASING_PRICE");
	}
	
	$res = CIBlock::GetProperties($XLS_IBLOCK_ID, Array("SORT"=>"ASC"), Array());
	while($res_arr = $res->GetNext())
		$SrcPropID["PROPERTY_".$res_arr["CODE"]] =$res_arr["NAME"];
		
	//$SrcPropID["0"] =GetMessage("XLS_NOT_RECORD");
	$SrcPropID["NAME"] = GetMessage("XLS_FLD_NAME");

?>
</br>
</br>
<h1><?=GetMessage("XLS_SET_COLUMN_FILED");?></h1>
</br>
<table border="1px" id='mcartxls_addrow_table1'>
<thead>
<td>
<?=GetMessage("XLS_TITLE_1")?>
</td>
<td>
<?=GetMessage("XLS_TITLE_2")?>
</td>
<!--
<td>
<?//=GetMessage("XLS_TITLE_3")?>
</td>
<td>
<?//=GetMessage("XLS_TITLE_4")?>
</td>
-->
<td>
<?=GetMessage("XLS_MODIFY_TYPE")?>
</td>
<td>
<?=GetMessage("XLS_SUBACTION")?>
</td>
<td>
<?=GetMessage("XLS_SUBACTION_PARAMS")?>
</td>

</thead>
<tr>
<td colspan="4">
<h4><?=GetMessage("PROPERTY_IDENTIFY")?></h4>
</td>
</tr>
<?$bcheck = false;?>
<? $key = 0; ?>	
	<tr id='tr_etalon'>
		<td>
			<select name = "XLS_IDENTIFY[xls]">
			<?foreach($alfavit as $key1=>$val):?>
			<option value="<?=$val?>" ><?=$langCls->ConvertArrayCharset($ARR_TITLE_ROW[$key1], BP_EI_DIRECTION_IMPORT)." (".$val.") "?></option>
		
			<?endforeach;?>	
		
		</td>
		<td>
			<select name = "XLS_IDENTIFY[bx]">
			<?
			$arrJSPropName = array();
			$arrJSPropCode = array();
			$arrJSPropName[] = GetMessage("MCART_SELECT_CODE");
			$arrJSPropCode[] = "0";
			?>
			<?foreach ($SrcPropID as $kode=>$name):?>
			<?
			$arrJSPropName[] = $name;
			
			$arrJSPropCode[] = $kode;
			?>
			<option value="<?=$kode?>" <? if (in_array($kode, $ARR_REAL_PROFILE["FIELDS"][$key])) echo " selected"?>><?=$name?></option>
			<?endforeach?>
			</select>
		</td>
		
		<td>
		
			<select name = "XLS_IDENTIFY[modify_type]" >
			<option value="XLS_MODIFY_TYPE_NONE"  <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_MODIFY_TYPE_NONE')) echo " selected"?>><?=GetMessage("XLS_MODIFY_TYPE_NONE")?></option>
			<option value="XLS_MODIFY_TYPE_TO_INT" <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_MODIFY_TYPE_TO_INT')) echo " selected"?>><?=GetMessage("XLS_MODIFY_TYPE_TO_INT")?></option>
			<option value="XLS_MODIFY_TYPE_TO_LINK" <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_MODIFY_TYPE_TO_LINK')) echo " selected"?>><?=GetMessage("XLS_MODIFY_TYPE_TO_LINK")?></option>
			<option value="XLS_TAKE_VALUE_TWO" <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_TAKE_VALUE_TWO')) echo " selected"?>><?=GetMessage("XLS_TAKE_VALUE_TWO")?></option>
			<option value="XLS_TAKE_VALUE_THREE" <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_TAKE_VALUE_THREE')) echo " selected"?>><?=GetMessage("XLS_TAKE_VALUE_THREE")?></option>
			
			</select>
		</td>
		<?
		$arrModifyTypeCode = array
		(
		"XLS_MODIFY_TYPE_NONE", 
		"XLS_MODIFY_TYPE_TO_INT", 
		"XLS_MODIFY_TYPE_TO_LINK",
		"XLS_TAKE_VALUE_TWO",
		"XLS_TAKE_VALUE_THREE"
		);
		$arrModifyTypeName = array
		(
		GetMessage("XLS_MODIFY_TYPE_NONE"), 
		GetMessage("XLS_MODIFY_TYPE_TO_INT"), 
		GetMessage("XLS_MODIFY_TYPE_TO_LINK"),
		GetMessage("XLS_TAKE_VALUE_TWO"),
		GetMessage("XLS_TAKE_VALUE_THREE")
		);
		
		?>
		<td>
		<?$arrModifySubactionCode = array
		(
		"XLS_MODIFY_TYPE_NONE", 
		"XLS_DEL_SUBSTR"
		);
		$arrModifySubactionName = array
		(
		GetMessage("XLS_MODIFY_TYPE_NONE"), 
		GetMessage("XLS_DEL_SUBSTR")
		);
		
		?>
			<select name = "XLS_IDENTIFY[modify_subaction]" >
				<option value="XLS_MODIFY_TYPE_NONE"  <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_MODIFY_TYPE_NONE')) echo " selected"?>><?=GetMessage("XLS_MODIFY_TYPE_NONE")?></option>
				<option value="XLS_DEL_SUBSTR" <?if (isset($ARR_REAL_PROFILE["ACTIONS"])&&($ARR_REAL_PROFILE["ACTIONS"][$key]=='XLS_DEL_SUBSTR')) echo " selected"?>><?=GetMessage("XLS_DEL_SUBSTR")?></option>
			</select>
		</td>
		<td>
			<input type="text" name="XLS_IDENTIFY[modify_subaction_params]" >
		</td>
		
	</tr>
<tr>
<td colspan="4">
<h4><?=GetMessage("OTHER_PROPS")?></h4>
</td>
</tr>	
</table>
<a href="#" onclick="AddCondition('', ''); return false;"><?=GetMessage("ADD_CONDITION");?></a>
</br>
</br>
<h4><input type="checkbox" class="make_translit_code"  name="make_translit_code" value="Y" <?if ($ARR_REAL_PROFILE['need_translit']) echo "checked='checked'"?>">
<?=GetMessage("MAKE_TRANSLIT_CODE")?></h4>
</br>
</br>
<h4><input type="checkbox" class="save_profile"  name="save_profile" value="Y">
<?=GetMessage("XLS_SAVE_PROFILE")?></h4>
</br>
<input type="text" name="profile_name" >
</br>
</br>
</br>
<a href = "/bitrix/admin/mcart_xls_import.php"><?=GetMessage("STEP_BACK")?></a>
<input type="submit" name="next_step" value="<?=GetMessage("BEGIN_IMPORT")?>">
<input type='hidden' name='catalog_base_price_id' value=<?=$BASE_PRICE_ID?>>
<input type='hidden' name='sku_iblock_id' value=<?=$SKU_IBLOCK_ID?>>
<input type='hidden' name='cml2_link_code' value=<?=$CML2_LINK_CODE?>>
<input type='hidden' name='firstColumn' value=<?=$firstColumn?>>
<input type='hidden' name='firstRow' value=<?=$firstRow?>>
<input type='hidden' name='titleRow' value=<?=$titleRow?>>
<input type='hidden' name='highestColumn' value=<?=$highestColumn?>>

</form>

<?}
else
{
?>
<?=$errMess?>

<a href = "/bitrix/admin/mcart_xls_import.php"><?=GetMessage("STEP_BACK")?></a>
<?
}
?>
<script>
var b_log_counter = 0;
function AddCondition(field, val)
{

	var addrowTable = document.getElementById('mcartxls_addrow_table1');

	b_log_counter++;
	var newRow = addrowTable.insertRow(-1);

	newRow.id = "delete_row_log_" + b_log_counter;

	var newCell = newRow.insertCell(-1);
	var newSelect = document.createElement("select");
       
	newSelect.setAttribute('b_log_counter', b_log_counter);
	newSelect.name = "XLS_GLOBALS[xls][" + b_log_counter + "]";
	var i = -1;
	var i1 = -1;

	newCell.appendChild(newSelect);
	var array1 = [<?php   foreach ($alfavit as $litera)// переводит массивы из php в javascript
echo  '"'.$litera.'",';   ?>];

var array2 = [
	<?php   foreach ($ARR_TITLE_ROW as $litera) echo  '"'.$litera.'",';   ?>
	];

	for (var i = 0; i < array1.length; i++) {
    var option = document.createElement("option");
    option.value = array1[i];
    option.text = array2[i]+'['+array1[i]+']';
    newSelect.appendChild(option);
}
	
	var newCell = newRow.insertCell(-1);
	var newSelect = document.createElement("select");
       
	
	newSelect.name = "XLS_GLOBALS[bx][" + b_log_counter + "]";
	var i = -1;
	var i1 = -1;

	newCell.appendChild(newSelect);
	var array1 = [<?php   foreach ($arrJSPropCode as $litera)
		echo  '"'.$litera.'",';   ?>];

		var array2 = [
			<?php   foreach ($arrJSPropName as $litera) echo  '"'.$litera.'",';   ?>
			];

	for (var i = 0; i < array1.length; i++) {
    var option = document.createElement("option");
    option.value = array1[i];
    option.text = array2[i];
	
    newSelect.appendChild(option);
	}
	
	var newCell = newRow.insertCell(-1);
	var newSelect = document.createElement("select");
       
	
	newSelect.name = "XLS_GLOBALS[modify_type][" + b_log_counter + "]";
	var i = -1;
	var i1 = -1;

	newCell.appendChild(newSelect);
	var array1 = [<?php   foreach ($arrModifyTypeCode as $litera)// переводит массивы из php в javascript
		echo  '"'.$litera.'",';   ?>];

		var array2 = [
			<?php   foreach ($arrModifyTypeName as $litera) echo  '"'.$litera.'",';   ?>
			];

	for (var i = 0; i < array1.length; i++) {
    var option = document.createElement("option");
    option.value = array1[i];
    option.text = array2[i];
    newSelect.appendChild(option);
	
	}
	
	var newCell = newRow.insertCell(-1);
	var newSelect = document.createElement("select");
	newSelect.name = "XLS_GLOBALS[modify_subaction][" + b_log_counter + "]";
	var i = -1;
	var i1 = -1;

	newCell.appendChild(newSelect);
	var array1 = [<?php   foreach ($arrModifySubactionCode as $litera)// переводит массивы из php в javascript
		echo  '"'.$litera.'",';   ?>];

		var array2 = [
			<?php   foreach ($arrModifySubactionName as $litera) echo  '"'.$litera.'",';   ?>
			];

	for (var i = 0; i < array1.length; i++) {
    var option = document.createElement("option");
    option.value = array1[i];
    option.text = array2[i];
    newSelect.appendChild(option);
	
	}
	var newCell = newRow.insertCell(-1);
	//newCell.id = "id_row_value_" + b_log_counter;
     //   newCell.align="right";
     	newCell.innerHTML = '<input type="text" name="XLS_GLOBALS[modify_subaction_params][' + b_log_counter +']">';
		
	var newCell = newRow.insertCell(-1);
	//newCell.id = "id_row_value_" + b_log_counter;
     //   newCell.align="right";
     	newCell.innerHTML = '<a href="#" onclick="MCARTXLSDeleteRow(' + b_log_counter + '); return false;"><?=GetMessage("MCART_DELETE_ROW")?></a>';	
        
        
}
function MCARTXLSDeleteRow(ind)
{
	var addrowTable = document.getElementById('mcartxls_addrow_table1');

	var cnt = addrowTable.rows.length;
	for (i = 0; i < cnt; i++)
	{
		if (addrowTable.rows[i].id != 'delete_row_log_' + ind)
			continue;

		addrowTable.deleteRow(i);

		break;
	}
       // if (addrowTable.rows.length <= 0)
        //    addrowTable.style.display = 'none';
}
</script>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>