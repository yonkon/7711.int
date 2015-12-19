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
$adminEmail = COption::GetOptionString('main', 'email_from', 'yonkon.ru@gmail.com');
$from =   empty($_REQUEST['client']['email']) ? $adminEmail :   $_REQUEST['client']['email'];
$to = $adminEmail;
//$to = 'yonkon.ru@gmail.com';
$subject = 'Union. Запрос персональной цены';
$headers = "From: " . $adminEmail . "\r\n";
$headers .= "Reply-To: ". $from . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$now = date('d-m-Y H:i:s');

$id = empty($_REQUEST['product']['id']) ? 'Не указано' : htmlentities($_REQUEST['product']['id']);
$name = empty($_REQUEST['product']['name']) ? 'Не указано' :  htmlentities($_REQUEST['product']['name']);
$URL = empty($_REQUEST['product']['URL']) ? 'Не указано' :  htmlentities($_SERVER["HTTP_HOST"]. $_REQUEST['product']['URL']);

$fio = empty($_REQUEST['client']['fio']) ? 'Не указано' :  htmlentities($_REQUEST['client']['fio']);
$phone = empty($_REQUEST['client']['phone']) ? 'Не указано' :  htmlentities($_REQUEST['client']['phone']);
$email = empty($_REQUEST['client']['email']) ? 'Не указано' :  htmlentities($_REQUEST['client']['email']);
$quantity = empty($_REQUEST['client']['quantity']) ? 'Не указано' :  htmlentities($_REQUEST['client']['quantity']);
$price = empty($_REQUEST['client']['price']) ? 'Не указано' :  htmlentities($_REQUEST['client']['price']);
$comments = empty($_REQUEST['client']['comments']) ? 'Не указано' :  htmlentities($_REQUEST['client']['comments']);

$body = "
<h1>Поступил запрос персональной цены</h1>
<dl>
  <b><dt>От:</dt></b><br>
    <dd>{$now}</dd><br>
  <b><dt>Клиент:</dt></b><br>
    <dd>{$fio}</dd><br>
  <br>
  <b><dt>Просматриваемый товар:</dt></b><br>
    <dd>{$name}</dd><br>
  <b><dt>Ссылка на товар:</dt></b><br>
    <dd>
      <a href='{$URL}'>{$URL}</a>
    </dd><br>
  <b><dt>Идентификатор товара</dt></b><br>
    <dd>{$id}</dd><br>
   <br>
   <b><dt>Обсуждаемое количество</dt></b><br>
    <dd>{$quantity}</dd><br>
   <b><dt>Предлагаемая клиентом цена:</dt></b><br>
    <dd>{$price}</dd><br>
  <br>
  <b><dt>Контактный телефон:</dt></b><br>
    <dd>{$phone}</dd><br>
  <b><dt>Контактный email:</dt></b><br>
    <dd>{$email}</dd>
  <br>  <br>
  <b><dt>Комментарии клиента:</dt></b><br>
    <dd>{$comments}</dd>
</dl>
";

global $DB;
try {
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
} catch (Exception $err) {
  ;
}
ob_end_clean();


if (mail($to, $subject, $body, $headers)) {
  print("200");
} else {
  print("500");
}
//ob_flush();
die();

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
