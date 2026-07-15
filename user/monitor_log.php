<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$title = 'MNBT监控日志';
mnbt_user_require_login();
include_once("../MPHX/monitor.function.php");
monitor_ensure_tables($DB);
$id = intval($_GET['id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$page_size = intval($_GET['page_size'] ?? 15);
if (!in_array($page_size, [10, 15, 25, 50, 100], true)) {
	$page_size = 15;
}
$where = " WHERE user=? AND (?=0 OR task_id=?)";
$params = [$yhc['user'], $id, $id];
$total = (int)$DB->count_prepare("SELECT count(*) FROM MN_monitor_log {$where}", $params);
$total_pages = max(1, (int)ceil($total / $page_size));
if ($page > $total_pages) {
	$page = $total_pages;
}
$offset = ($page - 1) * $page_size;
$logs = $DB->get_all_prepare("SELECT * FROM MN_monitor_log {$where} ORDER BY id DESC LIMIT {$offset},{$page_size}", $params) ?: [];
mnbt_render('monitor_log');
