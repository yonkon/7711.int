<?php

if (empty($_REQUEST['product']['id']) || empty($_REQUEST['product']['URL'])) {
  echo "404";
  die();
}
if (
  empty($_REQUEST['client']['fio']) ||
  empty($_REQUEST['client']['phone']) ||
  empty($_REQUEST['client']['email'])
) {
  echo "403";
  die();
}
if (!empty($_REQUEST['client']['comments']) ) {
  $_REQUEST['client']['comments'] = trim($_REQUEST['client']['comments']);
}
  if (empty($_REQUEST['client']['comments'])) {
    echo "400";
    die();
  }
ob_start();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$adminEmail = COption::GetOptionString('main', 'email_from', 'default@admin.email');
$from =   empty($_REQUEST['client']['email']) ? strip_tags($_REQUEST['client']['fio']) :   $_REQUEST['client']['email'];
$to = $adminEmail;
$subject = 'Union. Запрос персональной цены';
//strip_tags($_POST['req-email'])
$headers = "From: " . $adminEmail . "\r\n";
$headers .= "Reply-To: ". $adminEmail . "\r\n";
//$headers .= "CC: susan@example.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$now = date('d-m-Y H:i:s');

$id = empty($_REQUEST['product']['id']) ? 'Не указано' : htmlentities($_REQUEST['product']['id']);
$name = empty($_REQUEST['product']['name']) ? 'Не указано' :  htmlentities($_REQUEST['product']['name']);
$URL = empty($_REQUEST['product']['URL']) ? 'Не указано' :  htmlentities($_REQUEST['product']['URL']);

$fio = empty($_REQUEST['client']['fio']) ? 'Не указано' :  htmlentities($_REQUEST['client']['fio']);
$phone = empty($_REQUEST['client']['phone']) ? 'Не указано' :  htmlentities($_REQUEST['client']['phone']);
$email = empty($_REQUEST['client']['email']) ? 'Не указано' :  htmlentities($_REQUEST['client']['email']);
$quantity = empty($_REQUEST['client']['quantity']) ? 'Не указано' :  htmlentities($_REQUEST['client']['quantity']);
$price = empty($_REQUEST['client']['price']) ? 'Не указано' :  htmlentities($_REQUEST['client']['price']);
$comments = empty($_REQUEST['client']['comments']) ? 'Не указано' :  htmlentities($_REQUEST['client']['comments']);

$body =
  "<h1>Поступил запрос персональной цены</h1>
<dl>
  <dt>От:</dt>
    <dd>{$now}</dd>
  <dt>Клиент:</dt>
    <dd>{$fio}</dd>
  <br>
  <dt>Просматриваемый товар:</dt>
    <dd>{$name}</dd>
  <dt>Ссылка на товар:</dt>
    <dd>
      <a href='{$URL}'>{$URL}</a>
    </dd>
  <dt>Идентификатор товара</dt>
    <dd>{$id}</dd>
   <br>
   <dt>Обсуждаемое количество</dt>
    <dd>{$quantity}</dd>
   <dt>Предлагаемая клиентом цена:</dt>
    <dd>{$quantity}</dd>
  <br>
  <dt>Контактный телефон:</dt>
    <dd>{$phone}</dd>
  <dt>Контактный email:</dt>
    <dd>{$email}</dd>
  <br>
  <dt>Комментарии клиента:</dt>
    <dd>{$comments}</dd>


</dl>
";

global $DB;
$createTableSql = "CREATE TABLE IF NOT EXISTS `b_api_special_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `BE_ID` int(11) NOT NULL,
  `BE_NAME` text NOT NULL,
  `BE_URL` text NOT NULL,
  `client_fio` text NOT NULL,
  `client_phone` text NOT NULL,
  `client_email` text NOT NULL,
  `client_quantity` text NOT NULL,
  `client_price` text NOT NULL,
  `comments` text NOT NULL,
  `date` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 AUTO_INCREMENT=1 ;";
$DB->Query($createTableSql);

$id = mysql_real_escape_string($id);
$name = mysql_real_escape_string($name);
$URL = mysql_real_escape_string($URL);

$fio = mysql_real_escape_string($fio);
$phone = mysql_real_escape_string($phone);
$email = mysql_real_escape_string($email);
$quantity = mysql_real_escape_string($quantity);
$price = mysql_real_escape_string($price);
$comments = mysql_real_escape_string($comments);

$insertSpecialPriceSql = "INSERT INTO `b_api_special_price`(
`id`, `BE_ID`, `BE_NAME`, `BE_URL`,
`client_fio`, `client_phone`, `client_email`, `client_quantity`, `client_price`, `comments`, `date`)
VALUES (
NULL, '{$id}', '{$name}', '{$URL}', '{$fio}',  '{$phone}', '{$email}', '{$quantity}', '{$price}', '{$comments}', '{$now}'
)";
$DB->Query($insertSpecialPriceSql);

ob_end_clean();


if (mail($to, $subject, $body, $headers)) {
  print("200");
} else {
  print("500");
}
//ob_flush();
die();

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");