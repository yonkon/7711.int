<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init(array('jquery'));
?>
<tr>
	<td colspan="2">
		<div class="table-div">
			<script type="text/javascript">
				function xls_onSelectIBlockProperty(oSelect) {

					var XlsArIBlockProps = <?=CUtil::PhpToJSObject($XlsArIBlockProps)?>;

					var oSelectVal = $(oSelect).val();
					var oSelectProp = $(oSelect).parents('tr').find('select').eq(1);
					var oIdentProp = $(oSelect).parents('tr').find('input').eq(1);

					if (oSelectVal == '__xls_new_prop') {
						$(oSelectProp).find('option').removeAttr('selected');
					} else {
						$(oSelectProp).find('option[value='+XlsArIBlockProps[oSelectVal]['type']+']').attr('selected', 'selected');
					}

					if (XlsArIBlockProps[oSelectVal]['prop_identify']) {
						$(oIdentProp).attr('checked', 'checked');
					}

					if (XlsArIBlockProps[oSelectVal]['prop_identify_dis'] || oSelectVal=='__xls_new_prop') {
						$(oIdentProp).attr('disabled', 'disabled');
						$(oIdentProp).removeAttr('checked');
					} else {
						$(oIdentProp).removeAttr('disabled');
					}

					if (oSelectVal=='__xls_new_prop') {
						$(oSelectProp).find('option').each(function(index, el) {
							if ($(this).val()=='element' || $(this).val()=='elementlist' ||	$(this).val()=='section' || $(this).val()=='sectionlist') {
								$(this).css('display', 'none');
							}
						});
						$(oSelectProp).find('option').removeAttr('selected').eq(0).attr('selected', 'selected');

						$(oSelect).parent().find('.mark').css('display', 'inline');
					} else {
						$(oSelectProp).find('option').removeAttr('style');
						$(oSelect).parent().find('.mark').css('display', 'none');
					}

					xls_onSelectIBlockTypeProperty(oSelectProp);
				}

				function xls_onSelectIBlockTypeProperty(oSelect) {
					var oIdentProp = $(oSelect).parents('tr').find('input').eq(1);
					if (oSelect.value=='text' || oSelect.value=='number') { // may be selected for identify props
						oIdentProp.disabled=false;
					} else {
						oIdentProp.disabled=true;
						oIdentProp.checked=false;
					}
				}

				$('.adm-detail-content-item-block').each(function(index, el) {
					if ($(this).css('display') == 'block') {
						$(this).addClass('notminititle');
					}
				});
			</script>
			<table class="xls_table_property" cellspacing="0" cellpadding="0" width="100%">
				<thead>
					<tr class="head">
						<td align="center" style="border-left: 1px solid #BDC6E0;"><?=GetMessage("SOFTEFFECT_XLS_TABLE_NAME_COLUMN")?></td>
						<td align="center"><?=GetMessage("SOFTEFFECT_XLS_TABLE_NAME_IBLOCK_PROPERTY")?></td>
						<td align="center"><?=GetMessage("SOFTEFFECT_XLS_TABLE_TYPE_IBLOCK_PROPERTY")?></td>
						<td align="center"><input checked="checked" type="checkbox" id="property_check_all" />&nbsp;<?=GetMessage("SOFTEFFECT_XLS_TABLE_IS_UPLOAD")?></td>
						<td align="center"><?=GetMessage("SOFTEFFECT_XLS_PROPERTY_MAIN")?></td>
					</tr>
				</thead>
				<tbody>
					<?
					// Formiruem stroki tablicy po kol-vu stolbcov Excel
					$column = 0;

					foreach ($XlsArColumns as $column=>$name) {
						$type_disable=FALSE;
						$ident_disable=FALSE;
						$generatedCode = ToUpper("xls_".CSofteffectXlsHelper::translit($name));
						?>
						<tr>
							<td>
								<?=$name?>
							</td>
							<td align="left" nowrap="nowrap">
								<select name="XLS_SELECT_IBLOCK_PROPERTY[<?=$column?>]" onchange="xls_onSelectIBlockProperty(this)">
									<?
									$new=TRUE;
									$optgroup=0;
									$copyStr='';
									$selected=0;
									foreach ($XlsArIBlockProps as $code=>$value) {
										if ($code == $XLS_SELECT_IBLOCK_PROPERTY[$column]) {
											$new=FALSE;
										}

										$title = $value['title'];
										if ($groupKey=='iblock_group_4') {
											$title .= ' ['.$code.']';
										}

										if ($value['type']=='disabled') {
											if ($optgroup>1) echo '</optgroup>';
											echo '<optgroup label="'.$value['title'].'">';
											$groupKey = $code;
											$optgroup++;
											continue;
										}

										if ($code == $XLS_SELECT_IBLOCK_PROPERTY[$column] && $code!='__xls_new_prop') {
											$type_disable=TRUE;
										}

										if ($code == $XLS_SELECT_IBLOCK_PROPERTY[$column] && $value['prop_identify_dis']) { // if selected and may be disable
											$ident_disable = TRUE;
										}

										if ($generatedCode==$code) {
											$name = $title;
											$copyStr=$generatedCode;
										}

										if ($code == $XLS_SELECT_IBLOCK_PROPERTY[$column]) {
											$selected++;
										}
										?>
										<option value="<?=$code?>"<?if ($code == $XLS_SELECT_IBLOCK_PROPERTY[$column] || ($new && $code=='__xls_new_prop')) { echo ' selected="selected"'; }?>><?=$title?></option>
									<? } ?>
									</optgroup>
								</select><?
								if ($copyStr!='' && $selected<=0) { // esli est' sovpadayuschee po kodu svo-vo i ni odin element vybran ne byl
									?><a href="javascript:void(0);" onclick="setProperty(this, '<?=$copyStr?>');" class="set-prop" title="<?=str_replace('#PROP#', $name, GetMessage("SOFTEFFECT_XLS_STEP3_SETPROPLINK"))?>">&#9756;</a><?
								} ?><span class="mark" style="display: none;">&#10071;</span>
							</td>
							<td align="left">
								<select name="XLS_SELECT_TYPE_PROPERTY[<?=$column?>]"<? /*if ($type_disable) { ?> disabled="disabled"<? }*/ ?> onchange="xls_onSelectIBlockTypeProperty(this)">
									<? foreach ($XlsArProps as $type=>$name) { ?>
										<option value="<?=$type?>" <?if ($type == $XLS_SELECT_TYPE_PROPERTY[$column]) { $typeCurrent = $type; echo 'selected="selected"'; }?>><?=$name?></option>
									<? } ?>
								</select>
							</td>
							<td align="center">
								<input type="checkbox" class="XLS_SELECT_UPLOAD_PROPERTY" name="XLS_SELECT_UPLOAD_PROPERTY[<?=$column?>]" value="1" <?if ($XLS_SELECT_UPLOAD_PROPERTY[$column]) echo 'checked="checked"'?>>
							</td>
							<td align="center">
								<input type="radio" name="XLS_PROPERTY_MAIN" value="<?=$column?>"<?if ($XLS_PROPERTY_MAIN==$column) echo ' checked="checked"'?><? if ($ident_disable) echo ' disabled="disabled"'; ?>>
							</td>
						</tr>
						<?
						$column++;
					} ?>
				</tbody>
			</table>
			<script type="text/javascript">
				function setProperty(obj, propCode) {
					var selectObj = $(obj).parent('td').find('select');
					selectObj.find('option').removeAttr('selected');
					selectObj.find('option[value='+propCode+']').attr('selected', 'selected');
				}
				$(function () {
					$('.xls_table_property select').trigger('change');
				});
			</script>
		</div>
	</td>
</tr>
<script type="text/javascript">
	$(function () {
		$('input[name=submit_btn]').click(function(event) { // отслеживаем кнопку Далее чтобы узнать, есть ли хоть одно поле Наименование
			nameCnt=0;
			$('table.xls_table_property tr').each(function(index, el) {
				if (index>0) {
					if ($(this).find('td').eq(1).find('select').val()=='iblock_name' && $(this).find('td').eq(3).find('input').attr('checked')=='checked') {
						nameCnt++;
					}
				}
			});
			if (nameCnt<=0) {
				if (confirm('<?=GetMessage("SOFTEFFECT_XLS_ERROR_NAME_JS")?>')) {

				} else {
					return false;
				}
			}
		});
	});
</script>
<? if ($USER->GetParam('SKIP_ALL_STEPS')=="Y") { ?>
	</table>
	<img src="/bitrix/templates/.default/ajax/images/wait.gif" class="skip-wait" />&nbsp;<?=GetMessage("SOFTEFFECT_XLS_STEP3_AUTOREDIRECT")?>
	<script type="text/javascript">
		document.getElementById('edit3_edit_table').style.display = 'none';
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
	<table>
<? } ?>