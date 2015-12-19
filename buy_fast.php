<?php

if (empty($_REQUEST['product']['id']) || empty($_REQUEST['product']['URL'])) {
  echo "404";
  die();
}
if (empty($_REQUEST['client']['fio']) || (empty($_REQUEST['client']['phone']) && empty($_REQUEST['client']['phone']))) {
  echo "403";
  die();
}
ob_start();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$adminEmail = COption::GetOptionString('main', 'email_from', 'yonkon.ru@gmail.com');
$from =   empty($_REQUEST['client']['email']) ? $adminEmail :   $_REQUEST['client']['email'];
$to = $adminEmail;
//$to = 'yonkon.ru@gmail.com';
$subject = 'Union. Заказ в 1 клик';

$headers = "From: " . $adminEmail . "\r\n";
$headers .= "Reply-To: ". $from . "\r\n";

$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$now = date('d-m-Y H:i:s');

$id = empty($_REQUEST['product']['id']) ? 'Не указано' : htmlentities($_REQUEST['product']['id'], null, 'UTF-8');
$URL = empty($_REQUEST['product']['URL']) ? 'Не указано' :  htmlentities($_SERVER["HTTP_HOST"].  $_REQUEST['product']['URL']);
$name = empty($_REQUEST['product']['name']) ? 'Не указано' :  htmlentities($_REQUEST['product']['name']);

$fio = empty($_REQUEST['client']['fio']) ? 'Не указано' :  htmlentities($_REQUEST['client']['fio']);
$phone = empty($_REQUEST['client']['phone']) ? 'Не указано' :  htmlentities($_REQUEST['client']['phone']);
$email = empty($_REQUEST['client']['email']) ? 'Не указано' :  htmlentities($_REQUEST['client']['email']);

$body =
  "<h1>Заказ в 1 клик</h1>
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
  <b><dt>Контактный телефон:</dt></b><br>
    <dd>{$phone}</dd><br>
  <b><dt>Контактный email:</dt></b><br>
    <dd>{$email}</dd>
 </dl>
";

try {
  $createTableSql = "CREATE TABLE IF NOT EXISTS `b_api_buy_fast` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `BE_ID` INT(11) NOT NULL,
  `BE_NAME` TEXT NOT NULL,
  `BE_URL` TEXT NOT NULL,
  `client_fio` TEXT NOT NULL,
  `client_phone` TEXT NOT NULL,
  `client_email` TEXT NOT NULL,
  `date` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 AUTO_INCREMENT=1 ;";
  $DB->Query($createTableSql);

  $id = mysql_real_escape_string($id);
  $name = mysql_real_escape_string($name);
  $URL = mysql_real_escape_string($URL);

  $fio = mysql_real_escape_string($fio);
  $phone = mysql_real_escape_string($phone);
  $email = mysql_real_escape_string($email);

  $insertSql = "INSERT INTO `b_api_buy_fast`(
`id`, `BE_ID`, `BE_NAME`, `BE_URL`,
`client_fio`, `client_phone`, `client_email`, `date`)
VALUES (
NULL, '{$id}', '{$name}', '{$URL}',
'{$fio}',  '{$phone}', '{$email}', '{$now}'
)";
  $DB->Query($insertSql);
} catch (Exception $err) {
  ;
}
ob_end_clean();

if (mail($to, $subject, $body, $headers)) {
  print("200");
} else {
  print("500");
}

die();

