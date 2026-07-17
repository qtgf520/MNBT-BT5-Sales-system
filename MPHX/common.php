<?php
define('IN_CRONLITE', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');

// 生产环境：页面不显示错误，但写入本地错误日志，便于排查问题。
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
$runtimeLogDir = ROOT . 'runtime/logs';
if (!is_dir($runtimeLogDir)) {
	@mkdir($runtimeLogDir, 0755, true);
}
$errorLogPath = getenv('MNBT_ERROR_LOG');
if (!$errorLogPath) {
	$errorLogPath = $runtimeLogDir . '/php-error.log';
}
ini_set('error_log', $errorLogPath);
error_reporting(E_ALL);

define('SYS_KEY', 'MNBT');
define('CC_Defender', 1); //防CC攻击开关(1为session模式)
date_default_timezone_set("PRC");
$date = date("Y-m-d H:i:s");
session_start();
$scriptpath=str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
$sitepath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$siteurl = ($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$sitepath.'/';
if(is_file(SYSTEM_ROOT.'360safe/360webscan.php')){//360网站卫士
require_once(SYSTEM_ROOT.'360safe/360webscan.php');
}
require ROOT.'config.php';
if(!defined('SQLITE') && (!$dbconfig['user']||!$dbconfig['pwd']||!$dbconfig['dbname']))//检测安装
{
header("Location: /install");
exit();
}
//连接数据库
include_once(SYSTEM_ROOT."db.class.php");
$DB=new DB($dbconfig['host'],$dbconfig['user'],$dbconfig['pwd'],$dbconfig['dbname'],$dbconfig['port']);
if(!$DB->get_all_prepare("select * from MN_config where 1"))//检测安装2
{
header("Location: /install");
exit();
}
	
$siteid=1;
$conf=$DB->get_row_prepare("SELECT * FROM MN_config WHERE id=? limit 1", [$siteid]);//获取系统配置
$password_hash='!@#%!s!0';
	include_once(SYSTEM_ROOT."Response.php");
include_once(SYSTEM_ROOT."function.php");
include_once(SYSTEM_ROOT."member.php");
include_once(SYSTEM_ROOT."theme.php");
include_once(SYSTEM_ROOT."plugin.php");
// 权限管理 - 安全初始化
$Permission = null;
$UserAuth = null;
try{
    $tables = $DB->query("SHOW TABLES LIKE 'mn_permissions'");
    if($tables && $tables->num_rows > 0){
        include_once(SYSTEM_ROOT."permission.php");
        $Permission = new Permission($DB, isset($yhid) ? $yhid : null);
    }
}catch(Throwable $e){}
try{
    $tables2 = $DB->query("SHOW TABLES LIKE 'MN_user'");
    if($tables2 && $tables2->num_rows > 0){
        include_once(SYSTEM_ROOT."user_auth.php");
        $UserAuth = new UserAuth($DB);
    }
}catch(Throwable $e){}
// 兼容旧版函数
if(!function_exists('checkP')){
    function checkP($p){return true;}
    function checkPermission($p){return true;}
    function requirePermission($p){}
    function checkModulePermission($m){return false;}
    function getUserPermissions(){return [];}
}
mnbt_plugins_boot();
?>