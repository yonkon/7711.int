<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/new/props_format.php");
?>
<script src="/bitrix/js/jquery.mask/jquery.mask.js" type="text/javascript"></script>
<div class="section">
<h4><?=GetMessage("SOA_TEMPL_PROP_INFO")?></h4>
	<?
	$bHideProps = true;

	if (is_array($arResult["ORDER_PROP"]["USER_PROFILES"]) && !empty($arResult["ORDER_PROP"]["USER_PROFILES"])) {
    if (false) {
      if ($arParams["ALLOW_NEW_PROFILE"] == "Y"):
        ?>
        <div class="bx_block r1x3">
          <?= GetMessage("SOA_TEMPL_PROP_CHOOSE") ?>
        </div>
        <div class="bx_block r3x1">
          <select name="PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
            <option value="0"><?= GetMessage("SOA_TEMPL_PROP_NEW_PROFILE") ?></option>
            <?
            foreach ($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles) {
              ?>
              <option
                value="<?= $arUserProfiles["ID"] ?>"<? if ($arUserProfiles["CHECKED"] == "Y") echo " selected"; ?>><?= $arUserProfiles["NAME"] ?></option>
              <?
            }
            ?>
          </select>

          <div style="clear: both;"></div>
        </div>
      <? else: ?>
        <div class="bx_block r1x3">
          <?= GetMessage("SOA_TEMPL_EXISTING_PROFILE") ?>
        </div>
        <div class="bx_block r3x1">
          <?
          if (count($arResult["ORDER_PROP"]["USER_PROFILES"]) == 1) {
            foreach ($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles) {
              echo "<strong>" . $arUserProfiles["NAME"] . "</strong>";
              ?>
              <input type="hidden" name="PROFILE_ID" id="ID_PROFILE_ID" value="<?= $arUserProfiles["ID"] ?>"/>
              <?
            }
          } else {
            ?>
            <select name="PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
              <?
              foreach ($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles) {
                ?>
                <option
                  value="<?= $arUserProfiles["ID"] ?>"<? if ($arUserProfiles["CHECKED"] == "Y") echo " selected"; ?>><?= $arUserProfiles["NAME"] ?></option>
                <?
              }
              ?>
            </select>
            <?
          }
          ?>
          <div style="clear: both;"></div>
        </div>
        <?
      endif;
    }
  }
	else {
    $bHideProps = false;
  }
	?>
</div>

<br/>
<div class="bx_section">
	<h4>
		<?=GetMessage("SOA_TEMPL_BUYER_INFO")?>
		<?
		if (array_key_exists('ERROR', $arResult) && is_array($arResult['ERROR']) && !empty($arResult['ERROR']))
		{
			$bHideProps = false;
		}

		if ($bHideProps && $_POST["showProps"] != "Y"):
		?>
			<a href="#" class="slide" onclick="fGetBuyerProps(this); return false;">
				<?=GetMessage('SOA_TEMPL_BUYER_SHOW');?>
			</a>
		<?
		elseif (($bHideProps && $_POST["showProps"] == "Y")):
		?>
			<a href="#" class="slide" onclick="fGetBuyerProps(this); return false;">
				<?=GetMessage('SOA_TEMPL_BUYER_HIDE');?>
			</a>
		<?
		endif;
		?>
		<input type="hidden" name="showProps" id="showProps" value="<?=($_POST["showProps"] == 'Y' ? 'Y' : 'N')?>" />
	</h4>
	<div id="sale_order_props" <?=($bHideProps && $_POST["showProps"] != "Y")?"style='display:none;'":''?>>
		<?
		?>
    <div id="sale_order_props_tabs">
      <span tab="tab1" class="tab tab1 "><span class="tleft first"><span class="tright"><span class="tcenter">У меня есть учетная запись</span></span></span></span>
      <span tab="tab2" class="tab tab2 "><span class="tleft first"><span class="tright"><span class="tcenter">У меня есть учетная запись</span></span></span></span>
    </div>
    <div class="active" id="old_user_form">
      <div data-property-id-row="login">
        <div class="order_label">
          Телефон																	<span class="bx_sof_req">*</span>
        </div>
        <div class="order_input">
          <input type="text" maxlength="250" size="0" value="" name="ORDER_LOGIN" id="ORDER_LOGIN">
        </div>
        <div style="clear: both;"></div><br>
      </div>
      <div data-property-id-row="pass">
        <label class="order_label">Пароль<span class="bx_sof_req">*</span></label>
<div class="order_input"><input type="password" maxlength="250" size="0" value="" name="ORDER_PASSWORD" id="ORDER_PASSWORD"></div>
        <div style="clear: both;"></div><br>
      </div>
    </div>
    <div id="new_user_form">
  <div class="groupheader">Контактное лицо</div>
      <? /*****FIO*****/ ?>
      <div data-property-id-row="1">
        <label class="order_label" for="ORDER_PROP_1">Ф.И.О.</label>
<div class="order_input"><input type="text" maxlength="250" size="40" value="" name="ORDER_PROP_1"  id="ORDER_PROP_1"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'1','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <? //jur fio?>
      <div data-property-id-row="12" style="display: none;">
        <label class="order_label" for="ORDER_PROP_12">Контактное лицо</label>
<div class="order_input"><input type="text" maxlength="250" size="0" value="" name="ORDER_PROP_12"  id="ORDER_PROP_12"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'12','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>
      <? /*****EMAIL*****/ ?>
      <div data-property-id-row="2">
        <label class="order_label" for="ORDER_PROP_2">E-Mail</label>
<div class="order_input"><input type="text" maxlength="250" size="40" value="" name="ORDER_PROP_2"  id="ORDER_PROP_2"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'2','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <? //jur email?>
      <div data-property-id-row="13">
        <label class="order_label" for="ORDER_PROP_13">E-Mail																	<span class="bx_sof_req">*</span></label>
<div class="order_input"><input type="text" maxlength="250" size="40" value="" name="ORDER_PROP_13"  id="ORDER_PROP_13"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'13','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <? /*****Телефон *****/ ?>
      <div data-property-id-row="3">
        <label class="order_label" for="ORDER_PROP_3">Телефон																	<span class="bx_sof_req">*</span></label>
<div class="order_input"><input class="phone" type="text" maxlength="250" size="0" value="" name="ORDER_PROP_3"  id="ORDER_PROP_3"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        $(document).ready(function(){ $('input.phone').mask('(999) 999-9999'); });
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'3','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>
      <? //JUR PHONE ?>
      <div data-property-id-row="14" class="display: none;">
        <label class="order_label phone" for="ORDER_PROP_14">Телефон															</label>
<div class="order_input"><input type="text" class="phone" maxlength="250" size="0" value="" name="ORDER_PROP_14"  id="ORDER_PROP_14"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'14','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <? /*****Представитель юридического лица*****/ ?>
      <div data-property-id-row="3">
        <input type="checkbox" size="0" value="2" name="PERSON_TYPE"  id="PERSON_TYPE">
        <label class="order_label" for="PERSON_TYPE">Представитель юридического лица</label>

        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'3','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>
      <? /*****РЕКВИЗИТЫ ОРГАНИЗАЦИИ*****/ ?>
      <? /*****Наименование организации*****/ ?>
      <div data-property-id-row="8">
        <label class="order_label" for="ORDER_PROP_8">Название компании</label>
<div class="order_input"><input type="text" maxlength="250" size="40" value="" name="ORDER_PROP_8"  id="ORDER_PROP_8"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'8','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <? /*****Юридический адрес*****/ ?>
      <div data-property-id-row="9">
        <br>
        <label class="order_label" for="ORDER_PROP_9">Юридический адрес															</label>
<div class="order_input"><textarea rows="4" cols="40" name="ORDER_PROP_9"  id="ORDER_PROP_9"></textarea></div>
        <div style="clear: both;"></div>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'9','attributes':{'type':'TEXTAREA','valueSource':'form'}});
      </script>

      <? /*****Почтовый адрес*****Подставить юр.адрес/ ?>
      <? /*****ИНН *****/ ?>
      <div data-property-id-row="10">
        <label class="order_label" for="ORDER_PROP_10">ИНН															</label>
<div class="order_input"><input type="text" maxlength="250" size="0" value="" name="ORDER_PROP_10"  id="ORDER_PROP_10"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'10','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>
      <? /*****КПП *****/ ?>
      <div data-property-id-row="11">
        <label class="order_label" for="ORDER_PROP_11">КПП															</label>
<div class="order_input"><input type="text" maxlength="250" size="0" value="" name="ORDER_PROP_11"  id="ORDER_PROP_11"></div>
        <div style="clear: both;"></div><br>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'11','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <? /*****БАНКОВСКИЕ РЕКВИЗИТЫ
       *****/ ?>
      <? /*****Наименование банка *****/ ?>
      <? /*****БИК *****/ ?>
      <? /*****Расчетный счет *****/ ?>
      <? /*****Корреспондентский счет *****/ ?>
      <? /*****АДРЕС ДОСТАВКИ
      Адрес доставки*****Подставить почт.адрес*/ ?>
      <div data-property-id-row="7">
        <br>
        <label class="order_label" for="ORDER_PROP_7">Адрес доставки															</label>
<div class="order_input"><textarea rows="3" cols="30" name="ORDER_PROP_7"  id="ORDER_PROP_7"></textarea></div>
        <div style="clear: both;"></div>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'7','attributes':{'type':'TEXTAREA','valueSource':'form'}});
      </script>
      <? //jur dost addr ?>
      <div data-property-id-row="19">
        <br>
        <label class="order_label" for="ORDER_PROP_19">Адрес доставки															</label>
<div class="order_input"><textarea rows="4" cols="30" name="ORDER_PROP_19"  id="ORDER_PROP_19"></textarea></div>
        <div style="clear: both;"></div>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'19','attributes':{'type':'TEXTAREA','valueSource':'form'}});
      </script>



<? if (false) { ?>

      <div data-property-id-row="15">
        <div class="bx_block r1x3 pt8">
          Факс															</div>
        <div class="bx_block r3x1">
          <input type="text" maxlength="250" size="0" value="" name="ORDER_PROP_15" id="ORDER_PROP_15">
        </div>
        <div style="clear: both;"></div><br>
      </div>

      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'15','attributes':{'type':'TEXT','valueSource':'default'}});
      </script>

      <div data-property-id-row="16">
        <div class="bx_block r1x3 pt8">
          Индекс																	<span class="bx_sof_req">*</span>
        </div>
        <div class="bx_block r3x1">
          <input type="text" maxlength="250" size="8" value="101000" name="ORDER_PROP_16" id="ORDER_PROP_16">
        </div>
        <div style="clear: both;"></div><br>
      </div>

      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'16','attributes':{'type':'TEXT','valueSource':'default','isZip':true}});
      </script>

      <div data-property-id-row="18">
        <div class="bx_block r1x3 pt8">
          Местоположение																	<span class="bx_sof_req">*</span>
        </div>
        <div class="bx_block r3x1">
          <div class="location-block-wrapper">
            <div id="sls-88878" class="bx-sls ">
              <div class="dropdown-block bx-ui-sls-input-block">
                <span class="dropdown-icon"></span>
                <input type="text" autocomplete="off" name="ORDER_PROP_18" value="" class="dropdown-field" placeholder="Enter name ..." style="display: none;"><div class="bx-ui-sls-container" style="margin: 0px; padding: 0px; border: none; position: relative;"><input type="text" disabled="disabled" autocomplete="off" class="bx-ui-sls-route" style="padding: 6px 0px 0px 8px; margin: 0px;"><input type="text" autocomplete="off" value="" class="bx-ui-sls-fake" placeholder="Enter name ..."></div>
                <div class="dropdown-fade2white"></div>
                <div class="bx-ui-sls-loader"></div>
                <div class="bx-ui-sls-clear" title="Clear selection" style="display: none;"></div>
                <div class="bx-ui-sls-pane" style="overflow-y: auto; overflow-x: hidden;"><div class="bx-ui-sls-variants"></div></div>
              </div>
              <div class="bx-ui-sls-error-message">
              </div>
            </div>
            <script>
              if (!window.BX && top.BX)
                window.BX = top.BX;
              if(typeof window.BX.locationsDeferred == 'undefined') window.BX.locationsDeferred = {};
              window.BX.locationsDeferred['18'] = function(){
                if(typeof window.BX.locationSelectors == 'undefined') window.BX.locationSelectors = {};
                window.BX.locationSelectors['18'] =
                  new BX.Sale.component.location.selector.search({'scope':'sls-88878','source':'/bitrix/components/bitrix/sale.location.selector.search/get.php','query':{'FILTER':{'EXCLUDE_ID':0,'SITE_ID':'s1'},'BEHAVIOUR':{'SEARCH_BY_PRIMARY':'0','LANGUAGE_ID':'en'}},'selectedItem':false,'knownItems':[],'provideLinkBy':'id','messages':{'nothingFound':'Unfortunately search produced no results','error':'There was an error'},'callback':'submitFormProxy','useSpawn':false,'initializeByGlobalEvent':'','globalEventScope':'','pathNames':[],'types':{'1':{'CODE':'COUNTRY'},'2':{'CODE':'REGION'},'3':{'CODE':'CITY'}}});
              };
            </script>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>
      <script>
        (window.top.BX || BX).saleOrderAjax.addPropertyDesc({'id':'18','attributes':{'type':'LOCATION','valueSource':'form','altLocationPropId':'17'}});
      </script>
 <?}?>

    </div>
	</div>
</div>

<script type="text/javascript">
	function fGetBuyerProps(el)
	{
		var show = '<?=GetMessageJS('SOA_TEMPL_BUYER_SHOW')?>';
		var hide = '<?=GetMessageJS('SOA_TEMPL_BUYER_HIDE')?>';
		var status = BX('sale_order_props').style.display;
		var startVal = 0;
		var startHeight = 0;
		var endVal = 0;
		var endHeight = 0;
		var pFormCont = BX('sale_order_props');
		pFormCont.style.display = "block";
		pFormCont.style.overflow = "hidden";
		pFormCont.style.height = 0;
		var display = "";

		if (status == 'none')
		{
			el.text = '<?=GetMessageJS('SOA_TEMPL_BUYER_HIDE');?>';

			startVal = 0;
			startHeight = 0;
			endVal = 100;
			endHeight = pFormCont.scrollHeight;
			display = 'block';
			BX('showProps').value = "Y";
			el.innerHTML = hide;
		}
		else
		{
			el.text = '<?=GetMessageJS('SOA_TEMPL_BUYER_SHOW');?>';

			startVal = 100;
			startHeight = pFormCont.scrollHeight;
			endVal = 0;
			endHeight = 0;
			display = 'none';
			BX('showProps').value = "N";
			pFormCont.style.height = startHeight+'px';
			el.innerHTML = show;
		}

		(new BX.easing({
			duration : 700,
			start : { opacity : startVal, height : startHeight},
			finish : { opacity: endVal, height : endHeight},
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step : function(state){
				pFormCont.style.height = state.height + "px";
				pFormCont.style.opacity = state.opacity / 100;
			},
			complete : function(){
					BX('sale_order_props').style.display = display;
					BX('sale_order_props').style.height = '';

					pFormCont.style.overflow = "visible";
			}
		})).animate();
	}
</script>

<?if(!CSaleLocation::isLocationProEnabled()):?>
	<div style="display:none;">

		<?$APPLICATION->IncludeComponent(
			"bitrix:sale.ajax.locations",
			$arParams["TEMPLATE_LOCATION"],
			array(
				"AJAX_CALL" => "N",
				"COUNTRY_INPUT_NAME" => "COUNTRY_tmp",
				"REGION_INPUT_NAME" => "REGION_tmp",
				"CITY_INPUT_NAME" => "tmp",
				"CITY_OUT_LOCATION" => "Y",
				"LOCATION_VALUE" => "",
				"ONCITYCHANGE" => "submitForm()",
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);?>

	</div>
<?endif?>
