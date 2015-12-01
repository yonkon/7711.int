<?
error_reporting(E_ERROR | E_PARSE);

define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

if($_GET["admin_section"]=="Y")
	define("ADMIN_SECTION", true);
else
	define("BX_PUBLIC_TOOLS", true);

if(!require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php")) die('prolog_before.php not found!');
IncludeModuleLangFile(__FILE__);
if(CModule::IncludeModule("vampirus.kkb") && CModule::IncludeModule("sale")) {
    while( @ob_end_clean() );
if(isset($_POST["response"])){$response = $_POST["response"];};
$result = CVampiRUSKKBPayment::process_response(stripslashes($response));
//foreach ($result as $key => $value) {echo $key." = ".$value."<br>";};
if (is_array($result)){
    if (in_array("ERROR",$result)){
        $error = '';
        if ($result["ERROR_TYPE"]=="ERROR"){
            $error =  "System error:".$result["ERROR"];
        } elseif ($result["ERROR_TYPE"]=="system"){
            $error =  "Bank system error > Code: '".$result["ERROR_CODE"]."' Text: '".$result["ERROR_CHARDATA"]."' Time: '".$result["ERROR_TIME"]."' Order_ID: '".$result["RESPONSE_ORDER_ID"]."'";
        }elseif ($result["ERROR_TYPE"]=="auth"){
            $error =  "Bank system user autentication error > Code: '".$result["ERROR_CODE"]."' Text: '".$result["ERROR_CHARDATA"]."' Time: '".$result["ERROR_TIME"]."' Order_ID: '".$result["RESPONSE_ORDER_ID"]."'";
        };
        file_put_contents(dirname(__FILE__).'/error.log', @date('Y-m-d H:i:s').":".$error ,FILE_APPEND);
    };
    if (in_array("DOCUMENT",$result)){
        /*
        echo "Result DATA: <BR>";
        foreach ($result as $key => $value) {echo "Postlink Result: ".$key." = ".$value."<br>";};*/
        if($result['CHECKRESULT']=="[SIGN_GOOD]"){
            $arOrder = CSaleOrder::GetByID($result["ORDER_ORDER_ID"]);
            CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
                        $arFields = array(
                    "PS_STATUS" => 'Y',
                    "PS_STATUS_CODE" => $result['PAYMENT_RESPONSE_CODE'],
                    "PS_STATUS_DESCRIPTION" => GetMessage('VAMPIRUS.KKB_STATUS_PAID',array('CARD'=>$result['PAYMENT_CARD'])),
                    "PS_RESPONSE_DATE" => $result['RESULTS_TIMESTAMP'],
                    //"DATE_PAYED" => $result['RESULTS_TIMESTAMP'],
                    "PS_SUM"=>$result['ORDER_AMOUNT']
                    /*'PAY_VOUCHER_NUM'=>$_REQUEST['operation_id'],
                    'PAY_VOUCHER_DATE'=>Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)),strtotime($_REQUEST['datetime'])),*/
                );
            CSaleOrder::PayOrder($arOrder["ID"], "Y", true, true);
            CSaleOrder::Update($arOrder["ID"], $arFields);
            
        } else {
            file_put_contents(dirname(__FILE__).'/error.log', @date('Y-m-d H:i:s').":".$result['CHECKRESULT'] ,FILE_APPEND);
        }

    };
} else {  file_put_contents(dirname(__FILE__).'/error.log', @date('Y-m-d H:i:s').":System error".$result ,FILE_APPEND);};

 

}
echo '0';
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>