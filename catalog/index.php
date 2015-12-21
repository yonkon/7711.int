<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

function GET_SALE_FILTER(){
  global $DB;
  $arDiscountElementID = array();

  $dbProductDiscounts = CCatalogDiscount::GetList(
    array("SORT" => "ASC"),
    array(
      "ACTIVE" => "Y",
      "!>ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"),
        "YYYY-MM-DD HH:MI:SS",
        CSite::GetDateFormat("FULL")),
      "!<ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"),
        "YYYY-MM-DD HH:MI:SS",
        CSite::GetDateFormat("FULL")),
    ),
    false,
    false,
    null
  );

  while ($arProductDiscounts = $dbProductDiscounts->Fetch())
  {
//    if($res = $CCatalogDiscount->GetDiscountProductsList(array(), array(">=DISCOUNT_ID" => $arProductDiscounts['PRODUCT_ID']), false, false, array())){
    if($res = CCatalogDiscount::GetDiscountProductsList(array(), array(">=DISCOUNT_ID" => $arProductDiscounts['ID']), false, false, array())){
      while($ob = $res->GetNext()){
        if(!in_array($ob["PRODUCT_ID"],$arDiscountElementID))
          $arDiscountElementID[] = $ob["PRODUCT_ID"];
      }
    }
  }

  return $arDiscountElementID;
//  (
//    ((isset($arProduct['PARENT_ID']) ?
//      ((isset($arProduct['ID']) && ($arProduct['ID'] == 4233)) || $arProduct['PARENT_ID'] == 4233) :
//      (isset($arProduct['ID']) && ($arProduct['ID'] == 4233))
//    )) ||
//    ((isset($arProduct['PARENT_ID']) ?
//      ((isset($arProduct['ID']) && ($arProduct['ID'] == 4232)) || $arProduct['PARENT_ID'] == 4232) :
//      (isset($arProduct['ID']) && ($arProduct['ID'] == 4232))
//    ))
//    || ((isset($arProduct['PARENT_ID']) ? ((isset($arProduct['ID']) && ($arProduct['ID'] == 3812)) || $arProduct['PARENT_ID'] == 3812) : (isset($arProduct['ID']) && ($arProduct['ID'] == 3812)))) || (isset($arProduct['SECTION_ID']) && (in_array(94, $arProduct['SECTION_ID']))))
}

$APPLICATION->SetTitle("");
$filterView = (COption::GetOptionString("main", "wizard_template_id", "eshop_adapt_horizontal", SITE_ID) == "eshop_adapt_vertical" ? "HORIZONTAL" : "VERTICAL");
?>

<?
if(empty($_REQUEST['brand'])) {
  $searchFilterName = '';
  if(!empty($_REQUEST['discount'])) {
    $arElements = GET_SALE_FILTER();
    global $searchFilter;
    $searchFilter = array(
      "=ID" => $arElements,
    );
    $searchFilterName = 'searchFilter';
  }
    $APPLICATION->IncludeComponent(
      "bitrix:catalog",
      "template1_copy",
      Array(
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => "2",
        "TEMPLATE_THEME" => "site",
        "HIDE_NOT_AVAILABLE" => "N",
        "BASKET_URL" => "/personal/cart/",
        "ACTION_VARIABLE" => "action",
        "PRODUCT_ID_VARIABLE" => "id",
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "PRODUCT_PROPS_VARIABLE" => "prop",
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/catalog/",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",
        "CACHE_TYPE" => "N",
//		"CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_FILTER" => "N",
        "CACHE_GROUPS" => "Y",
        "SET_TITLE" => "Y",
        "ADD_SECTION_CHAIN" => "Y",
        "ADD_ELEMENT_CHAIN" => "Y",
        "SET_STATUS_404" => "Y",
        "DETAIL_DISPLAY_NAME" => "N",
        "USE_ELEMENT_COUNTER" => "Y",
        "USE_FILTER" => "Y",
        "FILTER_NAME" => $searchFilterName,
        "FILTER_VIEW_MODE" => "VERTICAL",
        "FILTER_FIELD_CODE" => array("", ""),
        "FILTER_PROPERTY_CODE" => array("", ""),
        "FILTER_PRICE_CODE" => array("BASE"),
        "FILTER_OFFERS_FIELD_CODE" => array("PREVIEW_PICTURE", ""),
        "FILTER_OFFERS_PROPERTY_CODE" => array("", ""),
        "USE_REVIEW" => "Y",
        "MESSAGES_PER_PAGE" => "10",
        "USE_CAPTCHA" => "Y",
        "REVIEW_AJAX_POST" => "Y",
        "PATH_TO_SMILE" => "/bitrix/images/forum/smile/",
        "FORUM_ID" => "1",
        "URL_TEMPLATES_READ" => "",
        "SHOW_LINK_TO_FORUM" => "Y",
        "USE_COMPARE" => "N",
        "PRICE_CODE" => array("BASE"),
        "USE_PRICE_COUNT" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "PRICE_VAT_INCLUDE" => "N",
        "PRICE_VAT_SHOW_VALUE" => "N",
        "PRODUCT_PROPERTIES" => array("BRAND_REF"),
        "USE_PRODUCT_QUANTITY" => "Y",
        "CONVERT_CURRENCY" => "Y",
        "CURRENCY_ID" => "KZT",
        "QUANTITY_FLOAT" => "N",
        "OFFERS_CART_PROPERTIES" => array("PROCESSOR"),
        "SHOW_TOP_ELEMENTS" => "N",
        "SECTION_COUNT_ELEMENTS" => "N",
        "SECTION_TOP_DEPTH" => "1",
        "SECTIONS_VIEW_MODE" => "LINE",
        "SECTIONS_SHOW_PARENT_NAME" => "N",
        "PAGE_ELEMENT_COUNT" => intval($_REQUEST['PAGE_ELEMENT_COUNT']) ? $_REQUEST['PAGE_ELEMENT_COUNT'] : "15",
        "LINE_ELEMENT_COUNT" => "3",
        "ELEMENT_SORT_FIELD" => "sort",
        "ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER2" => "desc",
        "LIST_PROPERTY_CODE" => array("NEWPRODUCT", ""),
        "INCLUDE_SUBSECTIONS" => "Y",
        "LIST_META_KEYWORDS" => "UF_KEYWORDS",
        "LIST_META_DESCRIPTION" => "UF_META_DESCRIPTION",
        "LIST_BROWSER_TITLE" => "UF_BROWSER_TITLE",
        "LIST_OFFERS_FIELD_CODE" => array("NAME", ""),
        "LIST_OFFERS_PROPERTY_CODE" => array("ARTNUMBER", "COLOR_REF", "MORE_PHOTO", "PROCESSOR", "SIZES_SHOES", "SIZES_CLOTHES", ""),
        "LIST_OFFERS_LIMIT" => "0",
        "DETAIL_PROPERTY_CODE" => array("DELIVERY_DATE", "NEWPRODUCT", "artnumber", "MANUFACTURER", "MATERIAL", ""),
        "DETAIL_META_KEYWORDS" => "KEYWORDS",
        "DETAIL_META_DESCRIPTION" => "META_DESCRIPTION",
        "DETAIL_BROWSER_TITLE" => "TITLE",
        "DETAIL_OFFERS_FIELD_CODE" => array("NAME", ""),
        "DETAIL_OFFERS_PROPERTY_CODE" => array("ARTNUMBER", "COLOR_REF", "MORE_PHOTO", "PROCESSOR", "SIZES_SHOES", "SIZES_CLOTHES", ""),
        "LINK_IBLOCK_TYPE" => "",
        "LINK_IBLOCK_ID" => "",
        "LINK_PROPERTY_SID" => "",
        "LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
        "USE_ALSO_BUY" => "Y",
        "ALSO_BUY_ELEMENT_COUNT" => "3",
        "ALSO_BUY_MIN_BUYES" => "1",
        "OFFERS_SORT_FIELD" => "sort",
        "OFFERS_SORT_ORDER" => "asc",
        "OFFERS_SORT_FIELD2" => "id",
        "OFFERS_SORT_ORDER2" => "desc",
        "PAGER_TEMPLATE" => "arrows",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_TITLE" => "Товары",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
        "PAGER_SHOW_ALL" => "Y",
        "ADD_PICT_PROP" => "MORE_PHOTO",
        "LABEL_PROP" => "NEWPRODUCT",
        "PRODUCT_DISPLAY_MODE" => "Y",
        "OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
        "OFFER_TREE_PROPS" => array("COLOR_REF"),
        "SHOW_DISCOUNT_PERCENT" => "Y",
        "SHOW_OLD_PRICE" => "Y",
        "MESS_BTN_BUY" => "Купить",
        "MESS_BTN_ADD_TO_BASKET" => "В корзину",
        "MESS_BTN_COMPARE" => "Сравнение",
        "MESS_BTN_DETAIL" => "Подробнее",
        "MESS_NOT_AVAILABLE" => "Нет в наличии",
        "DETAIL_USE_VOTE_RATING" => "Y",
        "DETAIL_VOTE_DISPLAY_AS_RATING" => "rating",
        "DETAIL_USE_COMMENTS" => "Y",
        "DETAIL_BLOG_USE" => "Y",
        "DETAIL_VK_USE" => "N",
        "DETAIL_FB_USE" => "Y",
        "AJAX_OPTION_ADDITIONAL" => "",
        "USE_STORE" => "Y",
        "USE_STORE_PHONE" => "Y",
        "USE_STORE_SCHEDULE" => "Y",
        "USE_MIN_AMOUNT" => "N",
        "STORE_PATH" => "/store/#store_id#",
        "MAIN_TITLE" => "Наличие на складах",
        "MIN_AMOUNT" => "10",
        "DETAIL_BRAND_USE" => "Y",
        "DETAIL_BRAND_PROP_CODE" => array("BRAND_REF", ""),
        "ADD_SECTIONS_CHAIN" => "Y",
        "COMMON_SHOW_CLOSE_POPUP" => "Y",
        "DETAIL_SHOW_MAX_QUANTITY" => "N",
        "DETAIL_BLOG_URL" => "catalog_comments",
        "DETAIL_BLOG_EMAIL_NOTIFY" => "N",
        "DETAIL_FB_APP_ID" => "",
        "USE_SALE_BESTSELLERS" => "Y",
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "PARTIAL_PRODUCT_PROPERTIES" => "Y",
        "USE_COMMON_SETTINGS_BASKET_POPUP" => "N",
        "TOP_ADD_TO_BASKET_ACTION" => "ADD",
        "SECTION_ADD_TO_BASKET_ACTION" => "ADD",
        "DETAIL_ADD_TO_BASKET_ACTION" => array("BUY"),
        "DETAIL_SHOW_BASIS_PRICE" => "Y",
        "DETAIL_CHECK_SECTION_ID_VARIABLE" => "N",
        "DETAIL_DETAIL_PICTURE_MODE" => "IMG",
        "DETAIL_ADD_DETAIL_TO_SLIDER" => "N",
        "DETAIL_DISPLAY_PREVIEW_TEXT_MODE" => "E",
        "STORES" => array(),
        "USER_FIELDS" => array("", ""),
        "FIELDS" => array("", ""),
        "SHOW_EMPTY_STORE" => "Y",
        "SHOW_GENERAL_STORE_INFORMATION" => "N",
        "USE_BIG_DATA" => "Y",
        "BIG_DATA_RCM_TYPE" => "bestsell",
        "COMMON_ADD_TO_BASKET_ACTION" => "ADD",
        "COMPARE_NAME" => "CATALOG_COMPARE_LIST",
        "COMPARE_FIELD_CODE" => array(0 => "", 1 => "",),
        "COMPARE_PROPERTY_CODE" => array(0 => "", 1 => "",),
        "COMPARE_OFFERS_FIELD_CODE" => array(0 => "", 1 => "",),
        "COMPARE_OFFERS_PROPERTY_CODE" => array(0 => "", 1 => "",),
        "COMPARE_ELEMENT_SORT_FIELD" => "sort",
        "COMPARE_ELEMENT_SORT_ORDER" => "asc",
        "DISPLAY_ELEMENT_SELECT_BOX" => "N",
        "COMPARE_POSITION_FIXED" => "Y",
        "COMPARE_POSITION" => "top left",
        "COMPONENT_TEMPLATE" => "template1_copy",
        "DETAIL_SET_CANONICAL_URL" => "N",
        "SHOW_DEACTIVATED" => "N",
        "VARIABLE_ALIASES" => Array("sections" => Array(), "section" => Array(), "element" => Array(), "compare" => Array(),),
        "SEF_URL_TEMPLATES" => Array("sections" => "", "section" => "#SECTION_CODE#/", "element" => "#SECTION_CODE#/#ELEMENT_CODE#/", "compare" => "compare/"),
        "VARIABLE_ALIASES" => Array(
          "sections" => Array(),
          "section" => Array(),
          "element" => Array(),
          "compare" => Array(),
        )
      )
    );


} else {

  include($_SERVER["DOCUMENT_ROOT"] . '/catalog/brands.php');

}
?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
