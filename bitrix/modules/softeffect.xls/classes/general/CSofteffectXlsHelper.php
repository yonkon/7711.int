<?
IncludeModuleLangFile(__FILE__);

class CSofteffectXlsHelper {
	function GetIBlockDropDownList($IBLOCK_ID, $IBLOCK_SECTION_ID, $strTypeName, $strIBlockName, $strSectionName, $arFilter = false)
	{
		$html = '';

		static $arTypes = false;
		static $arIBlocks = false;
		static $arSections = false;

		if(!$arTypes)
		{
			$arTypes = array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK_TYPE"));
			$arIBlocks = array(''=>array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK")));
			$arSections = array();

			if(!is_array($arFilter))
				$arFilter = array();

			$arFilter["MIN_PERMISSION"] = "W";
			$rsIBlocks = CIBlock::GetList(array("IBLOCK_TYPE" => "ASC", "NAME" => "ASC"), $arFilter);
			while($arIBlock = $rsIBlocks->Fetch())
			{
				if(!array_key_exists($arIBlock["IBLOCK_TYPE_ID"], $arTypes))
				{
					$arType = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG);
					$arTypes[$arType["~ID"]] = $arType["~NAME"]." [".$arType["~ID"]."]";
					$arIBlocks[$arType["~ID"]] = array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK"));
				}
				$arSections[$arIBlock['ID']][]=GetMessage("IBLOCK_CHOOSE_SECTION");
				$arIBlocks[$arIBlock["IBLOCK_TYPE_ID"]][$arIBlock["ID"]] = $arIBlock["NAME"]." [".$arIBlock["ID"]."]";
			}



			$arFilter["MIN_PERMISSION"] = "W";
			$rsSections = CIBlockSection::GetTreeList($arFilter);
			while($arSection = $rsSections->Fetch()) {

				$value = '';
				for ($i = 0; $i < $arSection['DEPTH_LEVEL'] - 1; $i++) {
					$value .= '.';
				}
				$value = $value.$arSection['NAME'];
				$arSections[$arSection['IBLOCK_ID']][$arSection['ID']] = $value;
			}

			$html .= '
			<script language="JavaScript">
				function OnTypeChanged(typeSelect, iblockSelectID) {
					var arIBlocks = '.CUtil::PhpToJSObject($arIBlocks).';
					var iblockSelect = document.getElementById(iblockSelectID);
					if(iblockSelect)
					{
						for(var i=iblockSelect.length-1; i >= 0; i--)
							iblockSelect.remove(i);
						var n = 0;
						for(var j in arIBlocks[typeSelect.value])
						{
							var newoption = new Option(arIBlocks[typeSelect.value][j], j, false, false);
							iblockSelect.options[n]=newoption;
							n++;
						}
					}

					OnIBlockChanged(iblockSelect, "'.htmlspecialcharsbx($strSectionName).'");
				}

				function OnIBlockChanged(iblockSelect, sectionSelectID)  {
					var arSections = '.CUtil::PhpToJSObject($arSections).';
					var sectionSelect = document.getElementById(sectionSelectID);
					if(sectionSelect) {
						for(var i=sectionSelect.length-1; i >= 0; i--)
							sectionSelect.remove(i);
						var n = 0;

						if (iblockSelect.value != "") {
							for(var j in arSections[iblockSelect.value])
							{
								var newoption = new Option(arSections[iblockSelect.value][j], j, false, false);
								sectionSelect.options[n]=newoption;
								n++;
							}
						}
					}
				}
			</script>
			';
		}

		$IBLOCK_TYPE = false;
		if($IBLOCK_ID > 0)
		{
			foreach($arIBlocks as $iblock_type_id => $iblocks)
			{
				if(array_key_exists($IBLOCK_ID, $iblocks))
				{
					$IBLOCK_TYPE = $iblock_type_id;
					break;
				}
			}
		}
		if (!$arSections[$IBLOCK_ID]) {
			$arSections = array(''=>array(''=>GetMessage("IBLOCK_CHOOSE_SECTION")));
		}

		$html .= '<select name="'.htmlspecialcharsbx($strTypeName).'" id="'.htmlspecialcharsbx($strTypeName).'" OnChange="'.htmlspecialcharsbx('OnTypeChanged(this, \''.CUtil::JSEscape($strIBlockName).'\')').'">'."\n";
		foreach($arTypes as $key => $value)
		{
			if($IBLOCK_TYPE === false)
				$IBLOCK_TYPE = $key;
			$html .= '<option value="'.htmlspecialcharsbx($key).'"'.($IBLOCK_TYPE===$key? ' selected': '').'>'.htmlspecialcharsbx($value).'</option>'."\n";
		}
		$html .= "</select>\n";

		$html .= "&nbsp;\n";

		$html .= '<select name="'.htmlspecialcharsbx($strIBlockName).'" id="'.htmlspecialcharsbx($strIBlockName).'" OnChange="'.htmlspecialcharsbx('OnIBlockChanged(this, \''.CUtil::JSEscape($strSectionName).'\')').'">'."\n";
		foreach($arIBlocks[$IBLOCK_TYPE] as $key => $value)
		{
			$html .= '<option value="'.htmlspecialcharsbx($key).'"'.($IBLOCK_ID==$key? ' selected': '').'>'.htmlspecialcharsbx($value).'</option>'."\n";
		}
		$html .= "</select>\n";

		$html .= "&nbsp;\n";

		$html .= '<select name="'.htmlspecialcharsbx($strSectionName).'" id="'.htmlspecialcharsbx($strSectionName).'" style="min-width:200px;">'."\n";
		foreach($arSections[$IBLOCK_ID] as $key => $value)
		{
			$html .= '<option value="'.htmlspecialcharsbx($key).'"'.($IBLOCK_SECTION_ID==$key? ' selected': '').'>'.htmlspecialcharsbx($value).'</option>'."\n";
		}
		$html .= "</select>\n";

		return $html;
	}

	// sohranyaet i vosstanavlivaet znachenie parametra shaga iz sessii
	function saveParamStep($name) {
		global $USER;

		if (isset($_REQUEST[$name])) {
			$USER->SetParam($name, $_REQUEST[$name]);
			$_SESSION['PROFILE_ARRAY'][$name] = $_REQUEST[$name];
		}

		return $USER->GetParam($name);
	}

	/**
	 * Vozvraschaet tekst, preobrazovannye s russkogo yazyka v latinicu
	 */
	static public function translit($str) {
		$str = trim($str);
		if (substr($str, -1, 1)=='.') {
			$str = substr($str, 0, strlen($str)-1);
		}
		$str = stripslashes($str);
		$translation = array(
			GetMessage('SOFTEFFECT_XLS_LETTER_A_M') => 'a', GetMessage('SOFTEFFECT_XLS_LETTER_A') => 'A',
			GetMessage('SOFTEFFECT_XLS_LETTER_B_M') => 'b', GetMessage('SOFTEFFECT_XLS_LETTER_B') => 'B',
			GetMessage('SOFTEFFECT_XLS_LETTER_V_M') => 'v', GetMessage('SOFTEFFECT_XLS_LETTER_V') => 'V',
			GetMessage('SOFTEFFECT_XLS_LETTER_G_M') => 'g', GetMessage('SOFTEFFECT_XLS_LETTER_G') => 'G',
			GetMessage('SOFTEFFECT_XLS_LETTER_D_M') => 'd', GetMessage('SOFTEFFECT_XLS_LETTER_D') => 'D',
			GetMessage('SOFTEFFECT_XLS_LETTER_E_M') => 'e', GetMessage('SOFTEFFECT_XLS_LETTER_E') => 'E',
			GetMessage('SOFTEFFECT_XLS_LETTER_EE_M') => 'e', GetMessage('SOFTEFFECT_XLS_LETTER_EE') => 'E',
			GetMessage('SOFTEFFECT_XLS_LETTER_J_M') => 'zh', GetMessage('SOFTEFFECT_XLS_LETTER_J') => 'ZH',
			GetMessage('SOFTEFFECT_XLS_LETTER_Z_M') => 'z', GetMessage('SOFTEFFECT_XLS_LETTER_Z') => 'Z',
			GetMessage('SOFTEFFECT_XLS_LETTER_I_M') => 'i', GetMessage('SOFTEFFECT_XLS_LETTER_I') => 'I',
			GetMessage('SOFTEFFECT_XLS_LETTER_II_M') => 'i', GetMessage('SOFTEFFECT_XLS_LETTER_II') => 'I',
			GetMessage('SOFTEFFECT_XLS_LETTER_K_M') => 'k', GetMessage('SOFTEFFECT_XLS_LETTER_K') => 'K',
			GetMessage('SOFTEFFECT_XLS_LETTER_L_M') => 'l', GetMessage('SOFTEFFECT_XLS_LETTER_L') => 'L',
			GetMessage('SOFTEFFECT_XLS_LETTER_M_M') => 'm', GetMessage('SOFTEFFECT_XLS_LETTER_M') => 'M',
			GetMessage('SOFTEFFECT_XLS_LETTER_N_M') => 'n', GetMessage('SOFTEFFECT_XLS_LETTER_N') => 'N',
			GetMessage('SOFTEFFECT_XLS_LETTER_O_M') => 'o', GetMessage('SOFTEFFECT_XLS_LETTER_O') => 'O',
			GetMessage('SOFTEFFECT_XLS_LETTER_P_M') => 'p', GetMessage('SOFTEFFECT_XLS_LETTER_P') => 'P',
			GetMessage('SOFTEFFECT_XLS_LETTER_R_M') => 'r', GetMessage('SOFTEFFECT_XLS_LETTER_R') => 'R',
			GetMessage('SOFTEFFECT_XLS_LETTER_S_M') => 's', GetMessage('SOFTEFFECT_XLS_LETTER_S') => 'S',
			GetMessage('SOFTEFFECT_XLS_LETTER_T_M') => 't', GetMessage('SOFTEFFECT_XLS_LETTER_T') => 'T',
			GetMessage('SOFTEFFECT_XLS_LETTER_U_M') => 'u', GetMessage('SOFTEFFECT_XLS_LETTER_U') => 'U',
			GetMessage('SOFTEFFECT_XLS_LETTER_F_M') => 'f', GetMessage('SOFTEFFECT_XLS_LETTER_F') => 'F',
			GetMessage('SOFTEFFECT_XLS_LETTER_H_M') => 'h', GetMessage('SOFTEFFECT_XLS_LETTER_H') => 'H',
			GetMessage('SOFTEFFECT_XLS_LETTER_C_M') => 'c', GetMessage('SOFTEFFECT_XLS_LETTER_C') => 'C',
			GetMessage('SOFTEFFECT_XLS_LETTER_CH_M') => 'ch', GetMessage('SOFTEFFECT_XLS_LETTER_CH') => 'CH',
			GetMessage('SOFTEFFECT_XLS_LETTER_SH_M') => 'sh', GetMessage('SOFTEFFECT_XLS_LETTER_SH') => 'SH',
			GetMessage('SOFTEFFECT_XLS_LETTER_SHH_M') => 'sh', GetMessage('SOFTEFFECT_XLS_LETTER_SHH') => 'SH',
			GetMessage('SOFTEFFECT_XLS_LETTER_UU_M') => 'u', GetMessage('SOFTEFFECT_XLS_LETTER_UU') => 'U',
			GetMessage('SOFTEFFECT_XLS_LETTER_EEE_M') => 'e', GetMessage('SOFTEFFECT_XLS_LETTER_EEE') => 'E',
			GetMessage('SOFTEFFECT_XLS_LETTER_YU_M') => 'yu', GetMessage('SOFTEFFECT_XLS_LETTER_YU') => 'YU',
			GetMessage('SOFTEFFECT_XLS_LETTER_YA_M') => 'ya', GetMessage('SOFTEFFECT_XLS_LETTER_YA') => 'YA',
			GetMessage('SOFTEFFECT_XLS_LETTER_MYAG_M') => '', GetMessage('SOFTEFFECT_XLS_LETTER_MYAG') => '',
			GetMessage('SOFTEFFECT_XLS_LETTER_TVERD_M') => '', GetMessage('SOFTEFFECT_XLS_LETTER_TVERD') => '',
			"'" => '', '"' => '',
		);
		$number = array(GetMessage('SOFTEFFECT_XLS_LETTER_NUM') => 'NUM', '#' => 'NUM');
		$znaki = array('.', ',', ';', ':', '*', '+', '-', '>', '<', '&', '@', '!', '$', '%', '^', '(', ')', ' ', '/', '_');

		$new = str_replace(array_keys($translation), array_values($translation), $str);
		$new = str_replace(array_keys($number), array_values($number), $new);
		$new = str_replace($znaki, '_', $new);
		$new = str_replace('__', '_', $new);
		if (substr($new, -1)=='_') {
			$new = substr($new, 0, strlen($new)-1);
		}

		return $new;
	}

	static public function getDiffDays($str) {
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

		$dir = 'softeffect.xls';
		$info = CModule::CreateModuleObject($dir);
		$arModules["MODULE_ID"] = $info->MODULE_ID;
		$arModules["MODULE_NAME"] = $info->MODULE_NAME;
		$arModules["MODULE_VERSION"] = $info->MODULE_VERSION;
		$arModules["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
		$arModules["IsInstalled"] = $info->IsInstalled();
		if(defined(str_replace(".", "_", $info->MODULE_ID)."_DEMO"))
		{
			$arModules["DEMO"] = "Y";
			$arModules["DEMO_DATE"] = date('d.m.Y', strtotime($arModules["MODULE_VERSION_DATE"].' +21 days'));
		}

		$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
		$arRequestedModules = CUpdateClientPartner::GetRequestedModules("");
		$arUpdateList = CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly, $arRequestedModules, Array("fullmoduleinfo" => "Y"));

		foreach ($arUpdateList['MODULE'] as $key => $moduleInfo) {
			if ($moduleInfo['@']['ID']==$dir) {
				$arModules["MODULE_LIC_DATE_TO"] = $moduleInfo['@']['DATE_TO'];
				break;
			}
		}

		if ($arModules['DEMO']=='Y') {
			$endDate = $arModules["DEMO_DATE"];
		} else {
			$endDate = $arModules["MODULE_LIC_DATE_TO"];
		}

		$date_from = explode('-', date('Y-m-d'));
		$date_till = explode('.', $endDate);

		$time_from = mktime(0, 0, 0, $date_from[1], $date_from[2], $date_from[0]);
		$time_till = mktime(0, 0, 0, $date_till[1], $date_till[0], $date_till[2]);

		$arModules['DIFF_DAYS'] = ($time_till - $time_from)/60/60/24;

		return $arModules;
	}

	static public function convertRussianDomens($linkFile) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/softeffect.xls/classes/general/idna_convert.class.php';

		$hostOrig = parse_url($linkFile, PHP_URL_HOST);
		$host = iconv("windows-1251", "UTF-8", $hostOrig);
		$idn = new idna_convert();
		$hostChange = $idn->encode($host);
		return str_replace($hostOrig, $hostChange, $linkFile);

	}
}