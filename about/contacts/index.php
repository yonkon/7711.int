<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Задайте вопрос");
?><p>
	 Отдел продаж: +7 (7172) 78 77 11, +7 (702) &nbsp;000 77&nbsp;&nbsp;11, +7 (776) 000 77 11
</p>
<p>
	 Сервис центр:&nbsp;+7 (7172) 24 81 04
</p>
<p class="bx_page">
</p>
<p>
	 Мы находимся по адресу: г. Астана, ул. Кенесары 40, офис 702
</p>
 <script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=RJl-JsizvVg9BcyaBEYZzz3SPs4xE_A3&width=100%&height=500"></script><br>
 <br>
<p>
</p>
<h2>Задать вопрос</h2>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.feedback",
	"eshop_adapt",
	Array(
		"USE_CAPTCHA" => "Y",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"EMAIL_TO" => "union@unn.kz",
		"REQUIRED_FIELDS" => array(),
		"EVENT_MESSAGE_ID" => array()
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>