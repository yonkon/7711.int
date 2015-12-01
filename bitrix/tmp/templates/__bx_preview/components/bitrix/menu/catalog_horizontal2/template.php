<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

if (empty($arResult["ALL_ITEMS"]))
	return;

if (file_exists($_SERVER["DOCUMENT_ROOT"].$this->GetFolder().'/themes/'.$arParams["MENU_THEME"].'/colors.css'))
	$APPLICATION->SetAdditionalCSS($this->GetFolder().'/themes/'.$arParams["MENU_THEME"].'/colors.css');

$menuBlockId = "catalog_menu_".$this->randString();
?>
<div class="bx_horizontal_menu_advaced bx_<?=$arParams["MENU_THEME"]?>" id="<?=$menuBlockId?>">
	<ul id="ul_<?=$menuBlockId?>">
	<?foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns):?>     <!-- first level-->
		<?$existPictureDescColomn = ($arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"] || $arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]) ? true : false;?>
		<li onmouseover="BX.CatalogMenu.itemOver(this);" onmouseout="BX.CatalogMenu.itemOut(this)" class="li_cata bx_hma_one_lvl <?if($arResult["ALL_ITEMS"][$itemID]["SELECTED"]):?>current<?endif?><?if (is_array($arColumns) && count($arColumns) > 0):?> dropdown<?endif?>">
			<a href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>" <?if (is_array($arColumns) && count($arColumns) > 0 && $existPictureDescColomn):?>onmouseover="obj_<?=$menuBlockId?>.changeSectionPicure(this);"<?endif?>>
				<img class="img_cata" src="<?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"]?>" alt="<?=$arResult["ALL_ITEMS"][$itemID]["TEXT"]?>"><br /><?=$arResult["ALL_ITEMS"][$itemID]["TEXT"]?>
			</a>
		<?if (is_array($arColumns) && count($arColumns) > 0):?>
			<span style="display: none">
				<?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]?>
			</span>

			<div class="bx_children_container b<?=($existPictureDescColomn) ? count($arColumns)+1 : count($arColumns)?> animate">
				<?foreach($arColumns as $key=>$arRow):?>
				<div class="bx_children_block">
					<div>
					<?foreach($arRow as $itemIdLevel_2=>$arLevel_3):?>  <!-- second level-->
						<div class="parent">
						
							<a href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>" <?if ($existPictureDescColomn):?>ontouchstart="document.location.href = '<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>';" onmouseover="obj_<?=$menuBlockId?>.changeSectionPicure(this);"<?endif?> data-picture="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["PARAMS"]["picture_src"]?>"><img class="img_tov" src="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["PARAMS"]["picture_src"]?>" alt="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?>"><br />
								<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?>
							</a>
							<span style="display: none">
								<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["PARAMS"]["description"]?>
							</span>
							<span class="bx_children_advanced_panel animate">
								<img class="img_tov" src="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["PARAMS"]["picture_src"]?>" alt="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?>">
							</span>
						
						</div>
					<?endforeach;?>
					</div>
				</div>
				<?endforeach;?>
				<?if ($existPictureDescColomn):?>
				
				<?endif?>
				<div style="clear: both;"></div>
			</div>
		<?endif?>
		</li>
	<?endforeach;?>
	</ul>
	<div style="clear: both;"></div>
</div>

<script>
	BX.ready(function () {
		window.obj_<?=$menuBlockId?> = new BX.Main.Menu.CatalogHorizontal('<?=CUtil::JSEscape($menuBlockId)?>');
	});
</script>