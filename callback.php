<?php

if (empty($_REQUEST['product']['id']) || empty($_REQUEST['product']['URL'])) {
  echo "404";
  die();
}
if (empty($_REQUEST['client']['fio']) || empty($_REQUEST['client']['phone'])) {
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
$from = strip_tags($_REQUEST['client']['fio']);
$to = $adminEmail;
$subject = 'Union. Просьба перезвонить';
//strip_tags($_POST['req-email'])
$headers = "From: " . $adminEmail . "\r\n";
$headers .= "Reply-To: ". $adminEmail . "\r\n";
//$headers .= "CC: susan@example.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$now = date('d-m-Y H:i:s');

$id = empty($_REQUEST['product']['id']) ? 'Не указано' : htmlentities($_REQUEST['product']['id'], null, 'UTF-8');
$URL = empty($_REQUEST['product']['URL']) ? 'Не указано' :  htmlentities($_REQUEST['product']['URL']);
$name = empty($_REQUEST['product']['name']) ? 'Не указано' :  htmlentities($_REQUEST['product']['name']);

$fio = empty($_REQUEST['client']['fio']) ? 'Не указано' :  htmlentities($_REQUEST['client']['fio']);
$comments = empty($_REQUEST['client']['comments']) ? 'Не указано' :  htmlentities($_REQUEST['client']['comments']);
$phone = empty($_REQUEST['client']['phone']) ? 'Не указано' :  htmlentities($_REQUEST['client']['phone']);

$body =
  "<h1>Поступила просьба перезвонить</h1>
<dl>
  <dt>От:</dt>
    <dd>{$now}</dd>
  <dt>Клиент:</dt>
    <dd>{$fio}</dd>
  <dt>По вопросу:</dt>
    <dd>{$comments}</dd>
  <dt>Просматриваемый товар:</dt>
    <dd>{$name}</dd>
  <dt>Ссылка на товар:</dt>
    <dd><a href='{$URL}'>{$URL}</a></dd>
  <dt>Идентификатор товара</dt>
    <dd>{$id}</dd>
  <dt>Контактный телефон:</dt>
    <dd>{$phone}</dd>
</dl>
";
if($_REQUEST['client']['urgent']) {
  $urgent_date = date('d-m-Y H:i:s', time()+3600);
  $body .= '<p>Перезвонить до: <span style="color: #f66;">'.$urgent_date.'</span></p>';
} else {
  $urgent_date = "Не указано";
}

$createTableSql = "CREATE TABLE IF NOT EXISTS `b_api_callback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `BE_ID` int(11) NOT NULL,
  `BE_NAME` text NOT NULL,
  `BE_URL` text NOT NULL,
  `client_fio` text NOT NULL,
  `client_phone` text NOT NULL,
  `comments` text NOT NULL,
  `date` text NOT NULL,
  `urgent` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 AUTO_INCREMENT=1 ;";
$DB->Query($createTableSql);

$id = mysql_real_escape_string($id);
$name = mysql_real_escape_string($name);
$URL = mysql_real_escape_string($URL);

$fio = mysql_real_escape_string($fio);
$phone = mysql_real_escape_string($phone);
$comments = mysql_real_escape_string($comments);
$urgent_date = mysql_real_escape_string($urgent_date);

$insertSql = "INSERT INTO `b_api_callback`(
`id`, `BE_ID`, `BE_NAME`, `BE_URL`,
`client_fio`, `client_phone`, `comments`, `date`, `urgent`)
VALUES (
NULL, '{$id}', '{$name}', '{$URL}',
'{$fio}',  '{$phone}', '{$comments}', '{$now}', '{$urgent_date}'
)";
$DB->Query($insertSql);
ob_end_clean();


if (mail($to, $subject, $body, $headers)) {
  print("200");
} else {
  print("500");
}
//ob_flush();
die();

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");