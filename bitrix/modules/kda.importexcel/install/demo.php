<?
IncludeModuleLangFile(__FILE__);

function kda_importexcel_demo_expired() {
	$DemoMode = CModule::IncludeModuleEx("kda.importexcel");
	if ($DemoMode==MODULE_DEMO) {
		$now=time();
		if (defined("kda_importexcel_OLDSITEEXPIREDATE")) {
			if ($now>=kda_importexcel_OLDSITEEXPIREDATE) {
				return true;
			}
		} else{ 
			return true;
		}
	} elseif ($DemoMode==MODULE_DEMO_EXPIRED) {
		return true;
	}
	return false;
}

function kda_importexcel_show_demo() {
	$DemoMode = CModule::IncludeModuleEx("kda.importexcel");
	if ($DemoMode==MODULE_DEMO) {
		$now=time();
		if (defined("kda_importexcel_OLDSITEEXPIREDATE")) {
			if ($now<kda_importexcel_OLDSITEEXPIREDATE) {
				print BeginNote();
				$expire_arr = getdate(kda_importexcel_OLDSITEEXPIREDATE);
				$expire_date = gmmktime($expire_arr["hours"],$expire_arr["minutes"],$expire_arr["seconds"],$expire_arr["mon"],$expire_arr["mday"],$expire_arr["year"]);
				$now_arr = getdate($now);
				$now_date = gmmktime($expire_arr["hours"],$expire_arr["minutes"],$expire_arr["seconds"],$now_arr["mon"],$now_arr["mday"],$now_arr["year"]);
				$days = ($expire_date-$now_date)/86400; 
				print GetMessage("KDA_IMPORTEXCEL_DEMO_MESSAGE_DAYS_REMAIN",array("#DAYS#"=>$days));
				print EndNote();
			} else {
				print BeginNote();
				print GetMessage("KDA_IMPORTEXCEL_DEMO_MESSAGE_EXPIRED");
				print EndNote();
			}
		} else{ 
			print BeginNote();
			print GetMessage("KDA_IMPORTEXCEL_DEMO_MESSAGE_EXPIRED");
			print EndNote();
		}
	} elseif ($DemoMode==MODULE_DEMO_EXPIRED) {
		print BeginNote();
		print GetMessage("KDA_IMPORTEXCEL_DEMO_MESSAGE_EXPIRED");
		print EndNote();
	}
}

?>