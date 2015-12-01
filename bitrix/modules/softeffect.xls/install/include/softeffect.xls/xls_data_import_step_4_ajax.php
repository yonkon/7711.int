<?
if ($_REQUEST['CHARSET'] && !$CHARSET) {
	$CHARSET = $_REQUEST['CHARSET'];
}

header('Content-type: text/html; charset='.$CHARSET);
$arResultSTEP4 = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/include/softeffect.xls/file.txt'));

if ($_REQUEST['START_ROW'] && !$START_ROW) {
	$START_ROW = intval($_REQUEST['START_ROW']);
}

if ($_REQUEST['END_ROW'] && !$END_ROW) {
	$END_ROW = intval($_REQUEST['END_ROW']);
}

if ($_REQUEST['XLS_STEP4_MODE'] && !$XLS_STEP4_MODE) {
	$XLS_STEP4_MODE = intval($_REQUEST['XLS_STEP4_MODE']);
}

foreach ($arResultSTEP4['DATA'] as $rowIndex => $row) {
	if ($XLS_STEP4_MODE>1) {
		if ($rowIndex<$START_ROW) continue;
		if ($rowIndex>=$END_ROW) break;
	}
	?>
	<tr>
		<? if ($XLS_STEP4_MODE=='1') { ?>
			<td style="text-align:center">
				<input type="checkbox" name="XLS_DATA_ROW_SELECT[<?=$rowIndex?>]" value="1" checked="checked" title="<?=GetMessage("XLS_DATA_TABLE_LOAD_IN_BITRIX")?>">
			</td>
		<? } ?>
		<td<? if ($XLS_STEP4_MODE=='2') { ?> align="center"<? } ?>>
			<? if ($XLS_STEP4_MODE=='1') { ?>
				<input type="text" style="width:50px" id="XLS_DATA_ID_BITRIX[<?=$rowIndex?>]" name="XLS_DATA_ID_BITRIX[<?=$rowIndex?>]" value="<?=$arResultSTEP4['XLS_DATA_ID_BITRIX'][$rowIndex]?>">
				<a title="<?=GetMessage('XLS_SELECT_ELEMENT')?>" style="font-size:25px;text-decoration:none;" href="javascript:void(0)" onClick="jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=<?=LANG?>&IBLOCK_ID=<?=$XLS_IBLOCK_ID?>&n=XLS_DATA_ID_BITRIX[<?=$rowIndex?>]', 800, 500);">&#9756;</a>
			<? } else {
				if ($arResultSTEP4['XLS_DATA_ID_BITRIX'][$rowIndex]) echo $arResultSTEP4['XLS_DATA_ID_BITRIX'][$rowIndex];
				else echo "<b>new</b>";
			} ?>
		</td>
		<? foreach ($arResultSTEP4['THEAD'] as $column=>$name) { // Znachenie v fayle ?>
			<td>
				<? if (is_numeric($row[$column])) {
					if (is_float($row[$column])) {
						echo round($row[$column], 2);
					} else {
						echo $row[$column];
					}
				} else {
					 echo $row[$column];
				} ?>
				&nbsp;
			</td>
		<? } ?>
	</tr>
<? } ?>