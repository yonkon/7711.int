<div id="step5-progress-bar">
Wait, please
</div>
<?

?>
<script type="text/javascript">
	function strpos( haystack, needle, offset){
		var i = haystack.indexOf( needle, offset );
		return i >= 0 ? i : false;
	}

	function repeat_import() {
		$.ajax({
			url: "/bitrix/include/mcart.xls/mcart_xls_import_step_2_js.php",
			timeout: 50000,
			success: function(data, textStatus){
				$("#step5-progress-bar").html(data);
				if (strpos(data, "The End")) {
					var cntString = data.toString().split ('||');
				}
				else {
					
					setTimeout(repeat_import, 1000);
				}
			},
			complete: function(xhr, textStatus) {
			
			
			/*
				if (textStatus != "success") {
				
				}
				*/
			},
			async: false
		});
	}
	
	$(function (){
		setTimeout(repeat_import, 1000);
	});
</script>

