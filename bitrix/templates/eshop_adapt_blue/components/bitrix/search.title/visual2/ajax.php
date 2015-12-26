<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["CATEGORIES"]))
	return;
?>
<div class="bx_searche">
<?
$foundItemsIds = array();
foreach($arResult["CATEGORIES"]['all']['ITEMS'] as $foundItem){
	$foundItemsIds[] = $foundItem['ID'];
}
$arItemsDETURL = array();
$arItemsSECTION = array();
$arSectionITEMS = array();
$arProcessed = array();
$rsItemsDETURL = $DB->Query('SELECT e.ID, e.NAME, s.NAME as SECTIONNAME , CONCAT_WS(\'/\', \'/catalog\', s.CODE, \'index.php\') AS SECTION_URL, CONCAT_WS(\'/\', \'/catalog\', s.CODE, e.CODE, \'index.php\') AS DETAIL_URL FROM `b_iblock_element` e JOIN b_iblock_section s ON s.ID = e.IBLOCK_SECTION_ID ');
while($arItemDETURL = $rsItemsDETURL->Fetch()) {
	$arItemsDETURL[$arItemDETURL['ID']] = $arItemDETURL['DETAIL_URL'];
  $arItemsSECTION[$arItemDETURL['ID']] = $arItemDETURL['SECTIONNAME'];
  $arItemsSECTION_URL[$arItemDETURL['ID']] = $arItemDETURL['SECTION_URL'];
}
foreach($arResult["CATEGORIES"] as $categories) {
  foreach($categories['ITEMS'] as $i => $item) {
    if(empty($item['ITEM_ID']))
      continue;
    $item['URL'] = $arItemsDETURL[$item['ITEM_ID']];
    $item['SECTION_URL'] = $arItemsSECTION_URL[$item['ITEM_ID']];
    $arProcessed[$arItemsSECTION[ $item["ITEM_ID"] ] ][$item["ITEM_ID"]]  = $item;
  }
}

//TODO shikon: make grouping by categories & steal markup from bri

if(false)
  foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
	<?foreach($arCategory["ITEMS"] as $i => $arItem):?>
		<?//echo $arCategory["TITLE"]?>
		<?if($category_id === "all"):?>
			<div class="bx_item_block" style="min-height:0">
				<div class="bx_img_element"></div>
				<div class="bx_item_element"><hr></div>
			</div>
			<div class="bx_item_block all_result">
				<div class="bx_img_element"></div>
				<div class="bx_item_element">
					<span class="all_result_title">
						<a href="<?echo
							empty($arItem["URL"])?
								$arItemsDETURL[$arItem["ITEM_ID"]]:
								$arItem["URL"];
						?>"><?echo $arItem["NAME"]?></a>
					</span>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?elseif(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):
			$arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];?>
			<div class="bx_item_block">
				<?if (is_array($arElement["PICTURE"])):?>
				<div class="bx_img_element">
					<div class="bx_image" style="background-image: url('<?echo $arElement["PICTURE"]["src"]?>')"></div>
				</div>
				<?endif;?>
				<div class="bx_item_element">
					<a href="<?echo empty($arItem["URL"])?$arItemsDETURL[$arItem['ITEM_ID']]:$arItem["URL"]; ?>"><?echo $arItem["NAME"]?></a>
					<?
					foreach($arElement["PRICES"] as $code=>$arPrice)
					{
						if ($arPrice["MIN_PRICE"] != "Y")
							continue;

						if($arPrice["CAN_ACCESS"])
						{
							if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
								<div class="bx_price">
									<?=$arPrice["PRINT_DISCOUNT_VALUE"]?>
									<span class="old"><?=$arPrice["PRINT_VALUE"]?></span>
								</div>
							<?else:?>
								<div class="bx_price"><?=$arPrice["PRINT_VALUE"]?></div>
							<?endif;
						}
						if ($arPrice["MIN_PRICE"] == "Y")
							break;
					}
					?>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?else:?>
			<div class="bx_item_block others_result">
				<div class="bx_img_element"></div>
				<div class="bx_item_element">
					<a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?endif;?>
	<?endforeach;?>
<?endforeach;?>

<!--<div class="title-search-result" style="position: absolute; top: 190px; left: -141px; width: 437px; display: none;">	-->
  <table class="title-search-result">
    <tbody>
    <tr>
      <th class="title-search-separator" style="width: 188px;">&nbsp;</th>
      <td class="title-search-separator">&nbsp;</td>
    </tr>
  <?
  $i = 1;
  foreach($arProcessed as $category_name => $items) {
    $cat_start = true;
    $j = 0;
  ?>
    <tr id="row_<? echo $i++; ?>">
      <th class="title-search-th"><? echo $category_name; ?></th>

    <?
    foreach( $items as $id => $item) {
      if($j>5)
        break;
      if (!$cat_start) { ?>
        <tr id="row_<? echo $i++; ?>">
        <th>&nbsp;</th>
      <?
      } else {
        $cat_start = false;
      }
?>

        <? if($j<5) { ?>
      <td class="title-search-more">
        <a href="<? echo $item['URL']; ?>"><? echo $item['NAME']; ?></a>
      </td>

    <? } else { ?>
      <td class="title-search-all">
          <a href="<? echo $item['SECTION_URL']; ?>?arrFilter_name=<?echo urlencode($_REQUEST['q'])?>">Все в этой категории</a>
      </td>
        <? } ?>
    </tr>
      <?
      $j++;
    }
    ?>
    <tr>
      <th class="title-search-separator">&nbsp;</th>
      <td class="title-search-separator">&nbsp;</td>
    </tr>
    <?
  }
  ?>
  <tr id="row_<? echo $i++; ?>">
									<th>&nbsp;</th>

									<td class="title-search-all-last"><a href="/search/index.php?q=<? echo urlencode($_REQUEST['q']);?>">Все результаты</a></td>
							</tr>
							<tr>
			<th class="title-search-separator">&nbsp;</th>
			<td class="title-search-separator">&nbsp;</td>
		</tr>
    </tbody>
  </table>
  <div class="title-search-fader" style="left: 419px; width: 18px; top: 0px; height: 744px; display: block;"></div>
<!--</div>-->
</div>
