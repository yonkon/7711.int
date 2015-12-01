<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
IncludeModuleLangFile(__FILE__);

$psTitle = GetMessage("VAMPIRUS.KKB_TITLE");
$psDescription = GetMessage("VAMPIRUS.KKB_DESCRIPTION");

$arPSCorrespondence = array(
		"TEST_MODE" => array(
				"NAME" => GetMessage("VAMPIRUS.KKB_TEST_MODE"),
				"DESCR" => GetMessage("VAMPIRUS.KKB_TEST_MODE_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"BACKLINK" => array(
				"NAME" => GetMessage("VAMPIRUS.KKB_BACKLINK"),
				"DESCR" => GetMessage("VAMPIRUS.KKB_BACKLINK_DESC"),
				"VALUE" => "http://".$_SERVER['SERVER_NAME'],
				"TYPE" => ""
			),
		"FBACKLINK" => array(
				"NAME" => GetMessage("VAMPIRUS.KKB_FBACKLINK"),
				"DESCR" => GetMessage("VAMPIRUS.KKB_FBACKLINK_DESC"),
				"VALUE" => "http://".$_SERVER['SERVER_NAME'],
				"TYPE" => ""
			),
		"CLIENT_EMAIL" => array(
				"NAME" => GetMessage("VAMPIRUS.KKB_CLIENT_EMAIL"),
				"DESCR" => "",
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("VAMPIRUS.KKB_ORDER_ID"),
				"DESCR" => "",
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("VAMPIRUS.KKB_SHOULD_PAY"),
				"DESCR" => "",
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
	);
?>