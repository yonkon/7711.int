<?php
IncludeModuleLangFile(__FILE__);

class CKDAImportProfile {
	function __construct()
	{
		$this->pathProfiles = dirname(__FILE__).'/../../profiles/';
		CheckDirPath($this->pathProfiles);
		$this->fileProfiles = $this->pathProfiles.'profiles.txt';
	}
	
	public function ShowProfileList($fname)
	{
		$arProfiles = $this->GetList();
		?><select name="<?echo $fname;?>" id="<?echo $fname;?>" onchange="EProfile.Choose(this)"><?
			?><option value=""><?echo GetMessage("KDA_IE_NO_PROFILE"); ?></option><?
			?><option value="new" <?if($_REQUEST[$fname]=='new'){echo 'selected';}?>><?echo GetMessage("KDA_IE_NEW_PROFILE"); ?></option><?
			foreach($arProfiles as $k=>$profile)
			{
				?><option value="<?echo $k;?>" <?if(strlen($_REQUEST[$fname])>0 && strval($_REQUEST[$fname])===strval($k)){echo 'selected';}?>><?echo $profile; ?></option><?
			}
		?></select><?
	}
	
	public function GetList()
	{
		if(!file_exists($this->fileProfiles))
		{
			$arProfiles = array();
		}
		else
		{
			$arProfiles = unserialize(file_get_contents($this->fileProfiles));
			if(!is_array($arProfiles))
			{
				$arProfiles = array();
			}
		}
		
		return $arProfiles;
	}
	
	public function GetByID($ID)
	{
		$arProfiles = $this->GetList();
		$fn = $this->pathProfiles.$ID.'.txt';
		if($arProfiles[$ID] && file_exists($fn))
		{
			$arProfile = unserialize(file_get_contents($fn));
		}
		if(!isset($arProfile) || !is_array($arProfile))
		{
			$arProfile = array();
		}
		
		return $arProfile;
	}
	
	public function Add($name)
	{
		global $APPLICATION;
		$APPLICATION->ResetException();
		
		$name = trim($name);
		if(strlen($name)==0)
		{
			$APPLICATION->throwException(GetMessage("KDA_IE_NOT_SET_PROFILE_NAME"));
			return false;
		}
		
		$arProfiles = $this->GetList();
		
		if(in_array($name, $arProfiles))
		{
			$APPLICATION->throwException(GetMessage("KDA_IE_PROFILE_NAME_EXISTS"));
			return false;
		}
		
		$arProfiles[] = $name;
		file_put_contents($this->fileProfiles, serialize($arProfiles));
		
		$ID = array_search($name, $arProfiles);
		
		return $ID;
	}
	
	public function Update($ID, $settigs_default, $settings)
	{
		$arProfile = $this->GetByID($ID);
		if(is_array($settigs_default))
		{
			$arProfile['SETTINGS_DEFAULT'] = $settigs_default;
		}
		if(is_array($settings))
		{
			$arProfile['SETTINGS'] = $settings;
		}
		$fn = $this->pathProfiles.'/'.$ID.'.txt';
		file_put_contents($fn, serialize($arProfile));
	}
	
	public function UpdateExtra($ID, $extrasettings)
	{
		$arProfile = $this->GetByID($ID);
		if(!is_array($extrasettings)) $extrasettings = array();
		$arProfile['EXTRASETTINGS'] = $extrasettings;
		$fn = $this->pathProfiles.'/'.$ID.'.txt';
		file_put_contents($fn, serialize($arProfile));
	}
	
	public function Apply(&$settigs_default, &$settings, $ID)
	{
		$arProfile = $this->GetByID($ID);
		if(!is_array($settigs_default) && is_array($arProfile['SETTINGS_DEFAULT']))
		{
			$settigs_default = $arProfile['SETTINGS_DEFAULT'];
		}
		if(!is_array($settings) && is_array($arProfile['SETTINGS']))
		{
			$settings = $arProfile['SETTINGS'];
		}
	}
	
	public function ApplyExtra(&$extrasettings, $ID)
	{
		$arProfile = $this->GetByID($ID);
		if(!is_array($extrasettings) && is_array($arProfile['EXTRASETTINGS']))
		{
			$extrasettings = $arProfile['EXTRASETTINGS'];
		}
	}
	
	public function Delete($ID)
	{
		$arProfiles = $this->GetList();
		unset($arProfiles[$ID]);
		file_put_contents($this->fileProfiles, serialize($arProfiles));
		
		$fn = $this->pathProfiles.'/'.$ID.'.txt';
		unlink($fn);
	}
	
	public function Rename($ID, $name)
	{
		$arProfiles = $this->GetList();
		$arProfiles[$ID] = $name;
		file_put_contents($this->fileProfiles, serialize($arProfiles));
	}
}