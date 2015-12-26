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
$rsItemsDETURL = $DB->Query('SELECT e.ID, e.NAME, s.NAME as SECTIONNAME , CONCAT_WS(\'/\', \'/catalog\', s.CODE, e.CODE, \'index.php\') AS DETAIL_URL FROM `b_iblock_element` e JOIN b_iblock_section s ON s.ID = e.IBLOCK_SECTION_ID ');
while($arItemDETURL = $rsItemsDETURL->Fetch()) {
	$arItemsDETURL[$arItemDETURL['ID']] = $arItemDETURL['DETAIL_URL'];
  $arItemsSECTION[$arItemDETURL['ID']] = $arItemDETURL['SECTIONNAME'];
}
foreach($arResult["CATEGORIES"]['all'] as $i => $item) {
  $item['URL'] =
  $arResult['CATEGORIES'][$arItemsSECTION[ $item["ITEM_ID"] ] ]  = $item;
}
//TODO shikon: make grouping by categories & steal markup from bri
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
</div>
