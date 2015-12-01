<?
///////////////////////////////////////////////
//   Copyright (c) 2012-2014 PHP Solutions   //
//   Поддержка: support@phpsolutions.ru      //
///////////////////////////////////////////////

IncludeModuleLangFile(__FILE__);
Class phpsolutions_backtotop extends CModule{
	
	const MODULE_ID = 'phpsolutions.backtotop';
	var $MODULE_ID = 'phpsolutions.backtotop'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

	function __construct(){
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php")) ;
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)){
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
		
		$this->MODULE_NAME = GetMessage("PHPSOLUTIONS_BACKTOTOP_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("PHPSOLUTIONS_BACKTOTOP_MODULE_DESC");
		$this->PARTNER_NAME = GetMessage("PHPSOLUTIONS_BACKTOTOP_PARTNER_NAME");
        $this->PARTNER_URI = "http://phpsolutions.ru/";
	}

	function InstallDB(){		
		RegisterModuleDependences( "main", "OnBeforeEndBufferContent", self::MODULE_ID, "CPHPSolutionsBacktotop", "AddScriptBacktotop" ) ;
	}

	function UnInstallDB(){		
        UnRegisterModuleDependences( "main", "OnBeforeEndBufferContent", self::MODULE_ID, "CPHPSolutionsBacktotop", "AddScriptBacktotop" ) ;		
	}

	function InstallFiles(){
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/phpsolutions.backtotop/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/phpsolutions.backtotop/", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/phpsolutions.backtotop/install/css", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/phpsolutions.backtotop/", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/phpsolutions.backtotop/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/phpsolutions.backtotop/", true, true);
	}

	function UnInstallFiles(){
		DeleteDirFilesEx("/bitrix/js/phpsolutions.backtotop");
        DeleteDirFilesEx("/bitrix/images/phpsolutions.backtotop");		
	}

	function DoInstall(){
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall(){
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>