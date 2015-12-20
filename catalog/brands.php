<?php
/**
 * @var $arSections array DB result from b_iblock_section + items_count + picture relative to "/upload/"
 * @var $APPLICATION CMain
 * @var $brand string
 */

$brand = $_REQUEST['brand'];
$brandEscape = mysql_escape_string($_REQUEST['brand']);
$brandsSectionSql =
  "SELECT
	  s.*,
	  COUNT(s.ID) as items_count,
	  CONCAT_WS('/', f.SUBDIR, f.FILE_NAME) AS picture
FROM `b_iblock_element` e
join b_iblock_section s on s.ID = e.IBLOCK_SECTION_ID
join b_iblock_element_property p ON p.IBLOCK_ELEMENT_ID = e.ID AND p.IBLOCK_PROPERTY_ID=9
LEFT JOIN b_file f ON f.ID = s.PICTURE
WHERE p.VALUE LIKE '%{$brandEscape}%' GROUP BY s.ID";

$rsSections = $DB->Query($brandsSectionSql);
$arSections = array();
while($arSection = $rsSections->Fetch()){
  $arSections[] = $arSection;
}

$APPLICATION->SetTitle("Товары бренда {$brand}");
?>
<?php
foreach($arSections as $section) {
    ?>
  <div class="brand_section">
    <div class="brand_section_picture" style="background-image: url('/upload/<? echo $section['picture']; ?>');">
    </div>
    <div class="brand_section_title">
      <? $altBrandKey = abs(crc32($brand)); ?>
      <a href="/catalog/<? echo $section['CODE']; ?>/index.php?arrFilter_9_<? echo $altBrandKey; ?>=Y&set_filter=Show">
        <? echo $section['NAME']. ' ' . $brand; ?>
        <span class="brand_section_count">
          (<? echo $section['items_count']; ?>)
        </span>
      </a>
    </div>
  </div>
    <?
}
