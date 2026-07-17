<?php
/*
 *本文件为控制面板功能性操作文件
 *©梦奈
 *version: 20260717_03
 */
// ★ 强制清除OPcache（管理员专用）
if($_GET['clear_op']=='1'){
    if(function_exists('opcache_reset')){opcache_reset();echo "opcache cleared\n";}
    if(function_exists('apc_clear_cache')){apc_clear_cache();echo "apc cleared\n";}
    if(function_exists('clearstatcache')){clearstatcache(true);echo "statcache cleared\n";}
    exit;
}

include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$egn=$_POST['gn'] ?? '';

// 独立用户注册 - 在登录检查之前
if($egn=='user_register') {
    $uname=daddslashes($_POST['username']??'');
    $pwd=$_POST['password']??'';$email=daddslashes($_POST['email']??'');
    if(strlen($uname)<3)exit('{"code":"用户名太短"}');
    if(strlen($pwd)<6)exit('{"code":"密码太短"}');
    $exist=$DB->get_row_prepare("SELECT id FROM MN_user WHERE username=?",[$uname]);
    if($exist)exit('{"code":"用户名已存在"}');
    $salt=substr(md5(uniqid(mt_rand(),true)),0,8);
    $pwd_enc=md5(md5($pwd).$salt);
    $ip=$_SERVER['REMOTE_ADDR']??'0.0.0.0';
    $ret=$DB->query_prepare("INSERT INTO MN_user (username,password,salt,email,group_id,status,reg_date,reg_ip) VALUES(?,?,?,?,1,'true',?,?)",[$uname,$pwd_enc,$salt,$email,$date,$ip]);
    if($ret)exit('{"code":"注册成功"}');else exit('{"code":"注册失败"}');
}
// 独立用户登录 - 在登录检查之前
if($egn=='user_login') {
    $uname=daddslashes($_POST['user']??'');$pwd=$_POST['pass']??'';
    $user=$DB->get_row_prepare("SELECT * FROM MN_user WHERE username=? LIMIT 1",[$uname]);
    if(!$user)exit('{"code":"用户不存在"}');
    if($user['status']!='true')exit('{"code":"账号禁用"}');
    $pwd_enc=md5(md5($pwd).$user['salt']);
    if($pwd_enc!=$user['password'])exit('{"code":"密码错误"}');
    $DB->query_prepare("UPDATE MN_user SET login_date=?,login_ip=? WHERE id=?",[$date,$_SERVER['REMOTE_ADDR']??'',$user['id']]);
    $token=base64_encode($user['id']."\t".$user['username']."\t".md5($user['username'].$pwd_enc.'MNBT'));
    setcookie("mn_user_token",$token,time()+604800,'/');
    exit('{"code":"登陆成功"}');
}
// 独立用户信息 - 在登录检查之前
if($egn=='user_info') {
    $tk=$_COOKIE['mn_user_token']??'';if(!$tk)exit('{"code":-1,"msg":"未登录"}');
    $dec=base64_decode($tk);if(!$dec)exit('{"code":-1,"msg":"token无效"}');
    list($uid,$uname,$sess)=explode("\t",$dec);
    $user=$DB->get_row_prepare("SELECT * FROM MN_user WHERE id=? AND username=? LIMIT 1",[$uid,$uname]);
    if(!$user)exit('{"code":-1,"msg":"用户不存在"}');
    if(md5($user['username'].$user['password'].'MNBT')!=$sess)exit('{"code":-1,"msg":"登录过期"}');
    $g=$DB->get_row_prepare("SELECT name FROM MN_user_group WHERE id=?",[$user['group_id']]);
    $user['group_name']=$g['name']??'未知';unset($user['password'],$user['salt']);
    exit(json_encode(['code'=>0,'data'=>$user]));
}
// 独立用户退出
if($egn=='user_logout'){setcookie("mn_user_token","",time()-604800,'/');exit('{"code":"已退出"}');}
// 资金流水
if($egn=='user_money_logs') {
    $tk=$_COOKIE['mn_user_token']??'';if(!$tk)exit('{"code":-1,"msg":"未登录"}');
    $dec=base64_decode($tk);if(!$dec)exit('{"code":-1,"msg":"token无效"}');
    list($uid,$uname,$sess)=explode("\t",$dec);
    $u=$DB->get_row_prepare("SELECT id FROM MN_user WHERE id=? AND username=? LIMIT 1",[$uid,$uname]);
    if(!$u)exit('{"code":-1,"msg":"用户不存在"}');
    $logs=$DB->get_all_prepare("SELECT * FROM MN_money_log WHERE user_id=? ORDER BY id DESC LIMIT 20",[$uid]);
    exit(json_encode(['code'=>0,'data'=>$logs?:[]]));
}

if($islogins==1 || $egn=='login') {
	$cert=$DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	if(!$cert)exit('{"code":"宝塔服务器配置错误，请联系管理员"}');
	$btipe=($cert['ptl']=='true'?'https':'http').'://'.$cert['btip'].':'.$cert['btdk'];
	$btkeye=$cert['btmy'];
	if($cert['btos']=='1') {
		$os_xt=$conf['hxi'].'/';
		$l_ler_a='/etc/hosts';
	} else {
		$os_xt=$conf['hxo'].'/';
		$l_ler_a='C:\Windows\System32\drivers\etc\hosts';
	}
}
// === 权限拦截：限制AJAX操作 ===
if($islogins==1){
    $ajaxPermMap = array(
        'ftpsc'=>'file_manager', 'ftpscxz'=>'file_manager', 'xjwj'=>'file_manager',
        'phpxg'=>'php_version',
        'sqldr'=>'database_backup',
        'scmmfw'=>'password_access', 'tjmmfw'=>'password_access',
        'xgmrwd'=>'default_document',
        'setwjt'=>'pseudo_static',
        'xgpass'=>'change_password',
        'setyxml'=>'running_directory',
        'yjbs'=>'one_click_deploy',
        'xjwjj'=>'file_manager', 'scwj'=>'file_manager',
    );
    if(isset($ajaxPermMap[$egn]) && !$Permission->hasPermission($ajaxPermMap[$egn])){
        exit(json_encode(array('code'=>-1,'msg'=>'权限不足'),JSON_UNESCAPED_UNICODE));
    }
}
if (($islogins==1 || $egn=='login') && function_exists('mnbt_plugin_dispatch_ajax') && mnbt_plugin_dispatch_ajax('user', $egn)) {
	return;
}
include("api/login.php");
if($yhc['hxc']=='1') {
	include("api/cdn.php");
	exit('{"code":"CDN产品无法进行此操作"}');
}
include("api/domain.php");
include("api/file.php");
include("api/cache.php");
include("api/site.php");
include("api/ssl.php");
include("api/monitor.php");
include("api/deploy.php");
include("api/database.php");
include("api/site_stats.php");
include("api/other.php");
include("api/panel.php");
exit('{"code":"请求错误！"}');
