<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$title = 'MN宝塔主机系统设置';
include("./class.php");
mnbt_user_require_login();
$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
$btipe = ($cert['ptl'] == 'true' ? 'https' : 'http') . '://' . $cert['btip'] . ':' . $cert['btdk'];
$btkeye = $cert['btmy'];
$hxd = $yhc['hxd'];
$user = $yhc['user'];
$api = new bt_api($btipe, $btkeye);
$r_data = $api->Databasebackuplist($hxd);
$pattern = '/共(\d+)条/';
$matches = [];
preg_match($pattern, $r_data['page'] ?? '', $matches);
$count = $matches[1] ?? 0;
$bf_data = $r_data['data'] ?? [];
if (!is_array($bf_data)) {
	$bf_data = [];
}
mnbt_render('sqlgl');
