<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("vampirus.kkb");
$orderID = (strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0) ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
$shouldPay = str_replace(",", ".", CSalePaySystemAction::GetParamValue("SHOULD_PAY"));
$content = CVampiRUSKKBPayment::process_request($orderID,'398',$shouldPay);
$url = CVampiRUSKKBPayment::getPaymentUrl();
$path = 'http://'.$_SERVER['SERVER_NAME'].'/bitrix/tools/kkb_notify.php';
$backlink = (CSalePaySystemAction::GetParamValue("BACKLINK"))?CSalePaySystemAction::GetParamValue("BACKLINK"):'http://'.$_SERVER['SERVER_NAME'];
$fbacklink = (CSalePaySystemAction::GetParamValue("FBACKLINK"))?CSalePaySystemAction::GetParamValue("FBACKLINK"):'http://'.$_SERVER['SERVER_NAME'];
?>
<form name="SendOrder" method="post" action="<?=$url?>">
   <input type="hidden" name="Signed_Order_B64" value="<?php echo $content;?>">
   E-mail: <input type="text" name="email" size=50 maxlength=50  value="<?php echo CSalePaySystemAction::GetParamValue("CLIENT_EMAIL")?>">
   <p>
   <input type="hidden" name="Language" value="rus">
   <input type="hidden" name="BackLink" value="<?php echo $backlink;?>">
   <input type="hidden" name="FailureBackLink" value="<?php echo $fbacklink;?>">
   <input type="hidden" name="PostLink" value="<?php echo $path?>">
   <input type="submit" name="GotoPay"  value="<?php echo GetMessage('VAMPIRUS.KKB_GOTO_PAYMENT')?>" >&nbsp;
</form>