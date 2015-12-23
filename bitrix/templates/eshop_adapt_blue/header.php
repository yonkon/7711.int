<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".SITE_TEMPLATE_ID."/header.php");
$wizTemplateId = COption::GetOptionString("main", "wizard_template_id", "eshop_adapt_horizontal", SITE_ID);
CUtil::InitJSCore();
CJSCore::Init(array("fx"));
$curPage = $APPLICATION->GetCurPage(true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
<link href='http://fonts.googleapis.com/css?family=Roboto&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_DIR?>favicon.ico" />
	<?//$APPLICATION->ShowHead();
	echo '<meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'"'.(true ? ' /':'').'>'."\n";
	$APPLICATION->ShowMeta("robots", false, true);
	$APPLICATION->ShowMeta("keywords", false, true);
	$APPLICATION->ShowMeta("description", false, true);
	$APPLICATION->ShowCSS(true, true);
	?>
	<link rel="stylesheet" type="text/css" href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/colors.css")?>" />
	<?
	$APPLICATION->ShowHeadStrings();
	$APPLICATION->ShowHeadScripts();
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/script.js");
	?>
	<title><?$APPLICATION->ShowTitle()?></title>

	<link rel="stylesheet" type="text/css" href="/highslide/highslide.css">
	<script type="text/javascript" src="/highslide/highslide.js"></script>
<script type="text/javascript">
	hs.graphicsDir = '/highslide/graphics/';
	hs.wrapperClassName = 'wide-border';
</script>

	<link rel="stylesheet" href="/images/print.css" type="text/css" media="print" charset="utf-8">

</head>
<body>
<div id="panel"><?$APPLICATION->ShowPanel();?></div>
<?$APPLICATION->IncludeComponent("bitrix:eshop.banner", "", array());?>
<div class="wrap" id="bx_eshop_wrap">
	<div class="header_wrap">
		<div class="header_wrap_container">
			<div class="header_top_section">
				<div class="header_top_section_container_one">
					<div class="bx_cart_login_top">
						<table>
							<tr>
								<td>
								<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.line", "", array(
										"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
										"PATH_TO_PERSONAL" => SITE_DIR."personal/",
										"SHOW_PERSONAL_LINK" => "N",
										"SHOW_NUM_PRODUCTS" => "Y",
										"SHOW_TOTAL_PRICE" => "Y",
										"SHOW_PRODUCTS" => "N",
										"POSITION_FIXED" =>"N"
									),
									false,
									array()
								);?>
								</td>
								<td>
								<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "eshop_adapt", array(
										"REGISTER_URL" => SITE_DIR."login/",
										"PROFILE_URL" => SITE_DIR."personal/",
										"SHOW_ERRORS" => "N"
									),
									false,
									array()
								);?>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="header_top_section_container_two">
<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "sect",
		"AREA_FILE_SUFFIX" => "up_menu",
		"EDIT_TEMPLATE" => "",
		"AREA_FILE_RECURSIVE" => "Y"
	)
);?>
				</div>
        <?$APPLICATION->IncludeComponent(
          "bitrix:search.title",
          "visual2",
          array(
            "NUM_CATEGORIES" => "1",
            "TOP_COUNT" => "5",
            "CHECK_DATES" => "N",
            "SHOW_OTHERS" => "N",
            "PAGE" => SITE_DIR."catalog/",
            "CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
            "CATEGORY_0" => array(
              0 => "no",
            ),
            "CATEGORY_0_iblock_catalog" => array(
              0 => "all",
            ),
            "CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
            "SHOW_INPUT" => "Y",
            "INPUT_ID" => "title-search-input",
            "CONTAINER_ID" => "search",
            "PRICE_CODE" => array(
              0 => "BASE",
            ),
            "SHOW_PREVIEW" => "Y",
            "PREVIEW_WIDTH" => "75",
            "PREVIEW_HEIGHT" => "75",
            "CONVERT_CURRENCY" => "Y",
            "COMPONENT_TEMPLATE" => "visual1",
            "ORDER" => "date",
            "USE_LANGUAGE_GUESS" => "Y",
            "PRICE_VAT_INCLUDE" => "Y",
            "PREVIEW_TRUNCATE_LEN" => "",
            "CURRENCY_ID" => "KZT"
          ),
          false
        );?>
				<div class="clb"></div>
		</div>  <!-- //header_top_section -->
			<div class="header_inner" itemscope itemtype = "http://schema.org/LocalBusiness">
				<?if ($curPage == SITE_DIR."index.php"):?><h1 class="site_title"><?endif?>
					<a <?if ($curPage != SITE_DIR."index.php"):?>class="site_title"<?endif?> href="<?=SITE_DIR?>" itemprop = "name"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_name.php"), false);?></a>
				<?if ($curPage == SITE_DIR."index.php"):?></h1><?endif?>

				<div class="slogan">IT-инфраструктура любой сложности для предприятий любого размера</div>
				<?if (false): ?><div class="header_inner_container_two">
<!--					--><?//$APPLICATION->IncludeComponent(
//	"bitrix:search.title",
//	"visual1",
//	array(
//		"NUM_CATEGORIES" => "1",
//		"TOP_COUNT" => "5",
//		"CHECK_DATES" => "N",
//		"SHOW_OTHERS" => "N",
//		"PAGE" => SITE_DIR."catalog/",
//		"CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
//		"CATEGORY_0" => array(
//			0 => "no",
//		),
//		"CATEGORY_0_iblock_catalog" => array(
//			0 => "all",
//		),
//		"CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
//		"SHOW_INPUT" => "Y",
//		"INPUT_ID" => "title-search-input",
//		"CONTAINER_ID" => "search",
//		"PRICE_CODE" => array(
//			0 => "BASE",
//		),
//		"SHOW_PREVIEW" => "Y",
//		"PREVIEW_WIDTH" => "75",
//		"PREVIEW_HEIGHT" => "75",
//		"CONVERT_CURRENCY" => "Y",
//		"COMPONENT_TEMPLATE" => "visual1",
//		"ORDER" => "date",
//		"USE_LANGUAGE_GUESS" => "Y",
//		"PRICE_VAT_INCLUDE" => "Y",
//		"PREVIEW_TRUNCATE_LEN" => "",
//		"CURRENCY_ID" => "KZT"
//	),
//	false
//);?><!--<span>--><?//$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/schedule.php"), false);?><!--</span>-->
				</div>
        <? endif; ?>

				<div class="header_inner_container_one">
<div class="header_inner_include_aria"><span style="color: #1b5c79;">
							<strong style="display: inline-block;padding-top: 7px;"><a style="text-decoration: none;color:#1b5c79;" href="<?=SITE_DIR?>about/contacts/" itemprop = "telephone"><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/telephone.php"), false);?></a></strong><br />
							</span>
					</div>
				</div>

				<div class="clb"></div>
			</div>  <!-- //header_inner -->
<!-- Меню каталога сверху -->

				<div class="header_inner_bottom_line_container">
					<div class="header_inner_bottom_line">
						<?if ( false && $wizTemplateId == "eshop_adapt_horizontal"):?>
						<?$APPLICATION->IncludeComponent(
	"bitrix:menu", 
	"catalog_horizontal2", 
	array(
		"ROOT_MENU_TYPE" => "left",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_TIME" => "36000000",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_THEME" => "site",
		"CACHE_SELECTED_ITEMS" => "N",
		"MENU_CACHE_GET_VARS" => array(
		),
		"MAX_LEVEL" => "3",
		"CHILD_MENU_TYPE" => "left",
		"USE_EXT" => "Y",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N"
	),
	false
);?>
						<?endif?>
					</div>
				</div><!-- //header_inner_bottom_line_container -->
			<!-- //Меню каталога сверху -->





		</div> <!-- //header_wrap_container -->

	</div> <!-- //header_wrap -->

	<div class="workarea_wrap">


<!-- Меню каталога -->
<div class="header_inner_bottom_line_container left-menu">
					<div class="header_inner_bottom_line">
<div class="catalog_left">
<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "sect",
		"AREA_FILE_SUFFIX" => "katalog_left",
		"EDIT_TEMPLATE" => "",
		"AREA_FILE_RECURSIVE" => "Y"
	)
);?>
</div>
<!-- Баннер слева -->
<div class="ban_left">
<?$APPLICATION->IncludeComponent(
	"bitrix:main.include", 
	".default", 
	array(
		"AREA_FILE_SHOW" => "sect",
		"AREA_FILE_SUFFIX" => "banner_left",
		"EDIT_TEMPLATE" => "",
		"AREA_FILE_RECURSIVE" => "Y"
	),
	false
);?>
</div>
<!-- //Баннер слева -->
</div></div>
<!-- //Меню каталога -->


		<div class="worakarea_wrap_container workarea <?if ($wizTemplateId == "eshop_adapt_vertical"):?>grid1x3<?else:?>grid<?endif?>">

<!-- Слайдер -->
			<?if ($APPLICATION->GetCurPage(true) == SITE_DIR."index.php"):?>
				<?$APPLICATION->IncludeComponent(
					"bitrix:main.include",
					"",
					Array(
						"AREA_FILE_SHOW" => "sect",
						"AREA_FILE_SUFFIX" => "inc",
						"AREA_FILE_RECURSIVE" => "N",
						"EDIT_MODE" => "html",
					),
					false,
					Array('HIDE_ICONS' => 'Y')
				);?>
			<?endif?>
<!-- //Слайдер -->


			<div id="navigation">
				<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "", array(
						"START_FROM" => "0",
						"PATH" => "",
						"SITE_ID" => "-"
					),
					false,
					Array('HIDE_ICONS' => 'Y')
				);?>
			</div>
			<div class="bx_content_section">
				<? if(mb_substr_count($curPage, '/') > 3) {
          echo $APPLICATION->ShowProperty('html_title');
        } elseif ($curPage != SITE_DIR."index.php") {?>
					<h1><?=$APPLICATION->ShowTitle(false);?></h1>
				<? } ?>
