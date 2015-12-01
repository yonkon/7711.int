<? define("SHORT_INSTALL_CHECK", true);?><?
define("DBPersistent", false);
$DBType = "mysql";
$DBHost = "localhost";
$DBLogin = "v_9619_shop1";
$DBPassword = "Qweszxc123!";
$DBName = "v_9619_shop1";
$DBDebug = false;
$DBDebugToFile = false;

@set_time_limit(60);

define("DELAY_DB_CONNECT", true);
define("CACHED_b_file", 3600);
define("CACHED_b_file_bucket_size", 10);
define("CACHED_b_lang", 3600);
define("CACHED_b_option", 3600);
define("CACHED_b_lang_domain", 3600);
define("CACHED_b_site_template", 3600);
define("CACHED_b_event", 3600);
define("CACHED_b_agent", 3660);
define("CACHED_menu", 3600);

define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~BX_DIR_PERMISSIONS);

define("SHORT_INSTALL", true);
define("BX_UTF", true);

//Bitrix Env replace()

///Bitrix Env replace()
?>