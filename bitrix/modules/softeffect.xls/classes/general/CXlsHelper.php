<?
IncludeModuleLangFile(__FILE__);

class CXlsHelper {
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
					
					OnIBlockChanged(iblockSelect, "'.htmlspecialchars($strSectionName).'");
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

		$html .= '<select name="'.htmlspecialchars($strTypeName).'" id="'.htmlspecialchars($strTypeName).'" OnChange="'.htmlspecialchars('OnTypeChanged(this, \''.CUtil::JSEscape($strIBlockName).'\')').'">'."\n";
		foreach($arTypes as $key => $value)
		{
			if($IBLOCK_TYPE === false)
				$IBLOCK_TYPE = $key;
			$html .= '<option value="'.htmlspecialchars($key).'"'.($IBLOCK_TYPE===$key? ' selected': '').'>'.htmlspecialchars($value).'</option>'."\n";
		}
		$html .= "</select>\n";

		$html .= "&nbsp;\n";

		$html .= '<select name="'.htmlspecialchars($strIBlockName).'" id="'.htmlspecialchars($strIBlockName).'" OnChange="'.htmlspecialchars('OnIBlockChanged(this, \''.CUtil::JSEscape($strSectionName).'\')').'">'."\n";
		foreach($arIBlocks[$IBLOCK_TYPE] as $key => $value)
		{
			$html .= '<option value="'.htmlspecialchars($key).'"'.($IBLOCK_ID==$key? ' selected': '').'>'.htmlspecialchars($value).'</option>'."\n";
		}
		$html .= "</select>\n";
		
		$html .= "&nbsp;\n";
		
		$html .= '<select name="'.htmlspecialchars($strSectionName).'" id="'.htmlspecialchars($strSectionName).'" style="min-width:200px;">'."\n";
		foreach($arSections[$IBLOCK_ID] as $key => $value)
		{
			$html .= '<option value="'.htmlspecialchars($key).'"'.($IBLOCK_SECTION_ID==$key? ' selected': '').'>'.htmlspecialchars($value).'</option>'."\n";
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
			GetMessage('LETTER_A_M') => 'a', GetMessage('LETTER_A') => 'A',
			GetMessage('LETTER_B_M') => 'b', GetMessage('LETTER_B') => 'B',
			GetMessage('LETTER_V_M') => 'v', GetMessage('LETTER_V') => 'V',
			GetMessage('LETTER_G_M') => 'g', GetMessage('LETTER_G') => 'G',
			GetMessage('LETTER_D_M') => 'd', GetMessage('LETTER_D') => 'D',
			GetMessage('LETTER_E_M') => 'e', GetMessage('LETTER_E') => 'E',
			GetMessage('LETTER_EE_M') => 'e', GetMessage('LETTER_EE') => 'E',
			GetMessage('LETTER_J_M') => 'zh', GetMessage('LETTER_J') => 'ZH',
			GetMessage('LETTER_Z_M') => 'z', GetMessage('LETTER_Z') => 'Z',
			GetMessage('LETTER_I_M') => 'i', GetMessage('LETTER_I') => 'I',
			GetMessage('LETTER_II_M') => 'i', GetMessage('LETTER_II') => 'I',
			GetMessage('LETTER_K_M') => 'k', GetMessage('LETTER_K') => 'K',
			GetMessage('LETTER_L_M') => 'l', GetMessage('LETTER_L') => 'L',
			GetMessage('LETTER_M_M') => 'm', GetMessage('LETTER_M') => 'M',
			GetMessage('LETTER_N_M') => 'n', GetMessage('LETTER_N') => 'N',
			GetMessage('LETTER_O_M') => 'o', GetMessage('LETTER_O') => 'O',
			GetMessage('LETTER_P_M') => 'p', GetMessage('LETTER_P') => 'P',
			GetMessage('LETTER_R_M') => 'r', GetMessage('LETTER_R') => 'R',
			GetMessage('LETTER_S_M') => 's', GetMessage('LETTER_S') => 'S',
			GetMessage('LETTER_T_M') => 't', GetMessage('LETTER_T') => 'T',
			GetMessage('LETTER_U_M') => 'u', GetMessage('LETTER_U') => 'U',
			GetMessage('LETTER_F_M') => 'f', GetMessage('LETTER_F') => 'F',
			GetMessage('LETTER_H_M') => 'h', GetMessage('LETTER_H') => 'H',
			GetMessage('LETTER_C_M') => 'c', GetMessage('LETTER_C') => 'C',
			GetMessage('LETTER_CH_M') => 'ch', GetMessage('LETTER_CH') => 'CH',
			GetMessage('LETTER_SH_M') => 'sh', GetMessage('LETTER_SH') => 'SH',
			GetMessage('LETTER_SHH_M') => 'sh', GetMessage('LETTER_SHH') => 'SH',
			GetMessage('LETTER_UU_M') => 'u', GetMessage('LETTER_UU') => 'U',
			GetMessage('LETTER_EEE_M') => 'e', GetMessage('LETTER_EEE') => 'E',
			GetMessage('LETTER_YU_M') => 'yu', GetMessage('LETTER_YU') => 'YU',
			GetMessage('LETTER_YA_M') => 'ya', GetMessage('LETTER_YA') => 'YA',
			GetMessage('LETTER_MYAG_M') => '', GetMessage('LETTER_MYAG') => '',
			GetMessage('LETTER_TVERD_M') => '', GetMessage('LETTER_TVERD') => '',
			"'" => '', '"' => '',
		);
		$number = array(GetMessage('LETTER_NUM') => 'NUM', '#' => 'NUM');
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
}