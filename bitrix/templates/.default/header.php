<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
<html>
<head>
  <? if(false) { ?>
  <style>
    @media (max-width:570px) {
      .worakarea_wrap_container {
        margin-left: auto;
      }
      .title.notive img {
        max-height: 2em;
        width: auto;
      }
      .bx-touch .header_inner_include_aria {
        padding-top: 0;
      }

      .header_inner_bottom_line_container.left-menu {
        display: none;
      }
      .slogan {
        max-width: 90px;
        font-size: 8pt;
        margin-left: 0;
        padding-left: 7pt;
      }
      .header_inner_include_aria strong  {
        padding-top: 11pt !important
      }
      .header_inner_include_aria strong a {
        font-size: 9pt;
      }
    }
    @media(max-width:700px) {
      .w50_inline-block_container > div, .w50_inline-block {
        width: auto;
      }
      .slogan {
        max-width: 200px;
      }
    }
  </style>
  <? } ?>
<?$APPLICATION->ShowHead();?>
<title><?$APPLICATION->ShowTitle()?></title>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF">

<?$APPLICATION->ShowPanel()?>

<?if($USER->IsAdmin()):?>

<div style="border:red solid 1px">
	<form action="/bitrix/admin/site_edit.php" method="GET">
		<input type="hidden" name="LID" value="<?=SITE_ID?>" />
		<p><font style="color:red"><?echo GetMessage("DEF_TEMPLATE_NF")?> </font></p>
		<input type="submit" name="set_template" value="<?echo GetMessage("DEF_TEMPLATE_NF_SET")?>" />
	</form>
</div>

<?endif?>
