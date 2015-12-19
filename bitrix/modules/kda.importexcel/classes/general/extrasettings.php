<?php
IncludeModuleLangFile(__FILE__);

class CKDAImportExtrasettings {
	function __construct()
	{

	}
	
	public static function GetMarginTemplates(&$pfile)
	{
		$pdir = dirname(__FILE__).'/../../profiles/';
		CheckDirPath($pdir);
		$pfile = $pdir.'margins.txt';
		$arTemplates = unserialize(file_get_contents($pfile));
		if(!is_array($arTemplates)) $arTemplates = array();
		return $arTemplates;
	}
	
	public static function SaveMarginTemplate($arPost)
	{
		$pfile = '';
		$arTemplates = self::GetMarginTemplates($pfile);
		$PEXTRASETTINGS = array();
		self::HandleParams($PEXTRASETTINGS, $arPost['EXTRASETTINGS']);
		$arMargins = self::GetMargins($PEXTRASETTINGS);
		if(strlen($arPost['template_id']) > 0 && is_numeric($arPost['template_id']))
		{
			$arTemplates[$arPost['template_id']]['MARGINS'] = $arMargins;
		}
		elseif(strlen($arPost['template_name']) > 0)
		{
			$arTemplates[] = array(
				'TITLE' => $arPost['template_name'],
				'MARGINS' => $arMargins
			);
		}
		file_put_contents($pfile, serialize($arTemplates));
		return $arTemplates;
	}
	
	public static function DeleteMarginTemplate($tid)
	{
		$pfile = '';
		$arTemplates = self::GetMarginTemplates($pfile);
		unset($arTemplates[$tid]);
		$arTemplates = array_values($arTemplates);
		file_put_contents($pfile, serialize($arTemplates));
		return $arTemplates;
	}
	
	public static function GetMargins($PEXTRASETTINGS)
	{
		foreach($PEXTRASETTINGS as $k1=>$v1)
		{
			foreach($v1 as $k2=>$v2)
			{
				return $v2['MARGINS'];
			}
		}
	}
	
	public static function HandleParams(&$PEXTRASETTINGS, $arParams)
	{
		foreach($arParams as $k1=>$v1)
		{
			foreach($v1 as $k2=>$v2)
			{
				foreach($v2 as $k3=>$v3)
				{
					if($k3=='MARGINS')
					{
						$PEXTRASETTINGS[$k1][$k2][$k3] = array();
						foreach($v3['PERCENT'] as $k4=>$v4)
						{
							$v4 = str_replace(',', '.', $v4);
							$v3['PRICE_FROM'][$k4] = str_replace(',', '.', $v3['PRICE_FROM'][$k4]);
							$v3['PRICE_TO'][$k4] = str_replace(',', '.', $v3['PRICE_TO'][$k4]);
							if(floatval($v4) > 0)
							{
								$margin = array(
									'TYPE' => $v3['TYPE'][$k4],
									'PERCENT' => floatval($v4),
									'PRICE_FROM' => (strlen(trim($v3['PRICE_FROM'][$k4])) > 0 ? floatval($v3['PRICE_FROM'][$k4]) : false),
									'PRICE_TO' => (strlen(trim($v3['PRICE_TO'][$k4])) > 0 ? floatval($v3['PRICE_TO'][$k4]) : false)
								);
								$PEXTRASETTINGS[$k1][$k2][$k3][] = $margin;
							}
						}
						continue;
					}
					
					if(is_array($v3))
					{
						$v3 = array_map('trim', $v3);
						$v3 = array_diff($v3, array(''));
					}

					if(!empty($v3))
					{
						$PEXTRASETTINGS[$k1][$k2][$k3] = $v3;
					}
					else
					{
						unset($PEXTRASETTINGS[$k1][$k2][$k3]);
					}
				}
			}
		}
	}
}