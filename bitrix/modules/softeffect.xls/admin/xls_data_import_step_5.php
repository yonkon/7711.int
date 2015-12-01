<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (count($_REQUEST['XLS_DATA_ID_BITRIX'])>0 && $XLS_STEP4_MODE==1) {
	$_SESSION['PROFILE_ARRAY']['XLS_DATA_ID_BITRIX'] = $_REQUEST['XLS_DATA_ID_BITRIX'];
}

$USER->SetParam('SKIP_ALL_STEPS', ""); // zakryvaem avtoredirekty po shagam
$_SESSION['PROFILE_ARRAY']['XLS_SELECT_TYPE_PROPERTY'] = $XLS_SELECT_TYPE_PROPERTY;

if ($_SESSION['PROFILE_ARRAY']['XLS_IBLOCK_SECTION_ID']) {
	$dbSec = CIBlockSection::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$_SESSION['PROFILE_ARRAY']['XLS_IBLOCK_ID'], 'ID'=>$_SESSION['PROFILE_ARRAY']['XLS_IBLOCK_SECTION_ID']));
	$arSec = $dbSec->GetNext();

	$secID = $_SESSION['PROFILE_ARRAY']['XLS_IBLOCK_SECTION_ID'];
	$_SESSION['PROFILE_ARRAY']['XLS_IBLOCK_SECTION_ID_DL'] = $arSec['DEPTH_LEVEL'];
}

unset($_SESSION['arElemsChange']);
unset($_SESSION['arError']);
unset($_SESSION['SEC_CODES']);
unset($_SESSION['startRow']);

$XLS_STEP5_TIMEOUT = $XLS_STEP5_TIMEOUT*1000;
?>
<div class="step5-progress-bar" id="step5-progress-bar">
	<span><img src="/bitrix/templates/.default/ajax/images/wait.gif" class="skip-wait" />&nbsp;&nbsp;<?=GetMessage("SOFTEFFECT_XLS_STEP5_READFILE");?></span><br />
	<?=GetMessage("SOFTEFFECT_XLS_STEP5_INCOMPLITE");?>&nbsp;<b>0</b>
</div>
<br clear="both" />
<br />
<div class="step5-progress-bar" id="step5-1-progress-bar" style="display: none;">
	<span><img src="/bitrix/templates/.default/ajax/images/wait.gif" class="skip-wait" />&nbsp;&nbsp;<?=GetMessage("SOFTEFFECT_XLS_STEP5_UDTSEARCH");?></span><br />
	<?=GetMessage("SOFTEFFECT_XLS_STEP5_INCOMPLITE_1");?>&nbsp;<b>0</b>
</div>
<br clear="both" />
<div id="step5-error">
	<span><?=GetMessage("SOFTEFFECT_XLS_STEP5_ERROR");?></span><br />
	<b></b>
</div>
<br clear="both" />

<script type="text/javascript">
	function strpos( haystack, needle, offset){
		var i = haystack.indexOf( needle, offset );
		return i >= 0 ? i : false;
	}

	function repeat_import() {
		$.ajax({
			url: "/bitrix/include/softeffect.xls/xls_data_import_step_5_ajax.php",
			timeout: 50000,
			success: function(data, textStatus){
				//$("#progress-bar").append("I");

				if (strpos(data, "The End")) {
					var cntString = data.toString().split ('||');
					$("#step5-progress-bar span").html("<?=GetMessage("SOFTEFFECT_XLS_STEP5_THEEND");?>");
					$("#step5-progress-bar b").html(""+cntString[0]+"");

					$('#step5-1-progress-bar').css('display', 'block');
					setTimeout(repeat_import_1, 1000);

					if (cntString[1]!='') {
						$("#step5-error b").html(cntString[1]);
						$("#step5-error").before('<br />');
						$("#step5-error").css('display', 'block');
					}
				} else {
					$("#step5-progress-bar b").html(data);
					setTimeout(repeat_import, '<?=$XLS_STEP5_TIMEOUT?>');
				}
			},
			complete: function(xhr, textStatus) {
				if (textStatus != "success") {
					//$("#progress-bar").append("I");
					//repeat_import();
				}
			},
			async: false
		});
	}

	function repeat_import_1() {
		$.ajax({
			url: "/bitrix/include/softeffect.xls/xls_data_import_step_5_1_ajax.php",
			timeout: 50000,
			success: function(data, textStatus){
				//$("#progress-bar").append("I");

				if (strpos(data, "The End")) {
					var cntString = data.toString().split ('||');
					$("#step5-1-progress-bar span").html("<?=GetMessage("SOFTEFFECT_XLS_STEP5_THEEND_1");?>");
					$("#step5-1-progress-bar b").html(""+cntString[0]+"");
				} else {
					$("#step5-1-progress-bar b").html(data);
					setTimeout(repeat_import_1, '<?=$XLS_STEP5_TIMEOUT?>');
				}
			},
			complete: function(xhr, textStatus) {
				if (textStatus != "success") {
					//$("#progress-bar").append("I");
					//repeat_import();
				}
			},
			async: false
		});
	}

	$(function (){
		setTimeout(repeat_import, 1000);
	});
</script>