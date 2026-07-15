<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$title = 'MNBT通知日志';
mnbt_user_require_login();
include_once("../MPHX/monitor.function.php");
monitor_ensure_tables($DB);

$type = trim($_GET['type'] ?? '');
$level = trim($_GET['level'] ?? '');
$read = trim($_GET['read'] ?? '');
$keyword = trim($_GET['keyword'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$page_size = intval($_GET['page_size'] ?? 15);
if (!in_array($page_size, [10, 15, 25, 50, 100], true)) {
	$page_size = 15;
}

$where = " WHERE user=?";
$params = [$yhc['user']];
if ($type !== '') {
	$where .= " AND type=?";
	$params[] = $type;
}
if ($level !== '') {
	$where .= " AND level=?";
	$params[] = $level;
}
if ($read === 'true' || $read === 'false') {
	$where .= " AND is_read=?";
	$params[] = $read;
}
if ($keyword !== '') {
	$where .= " AND (title LIKE ? OR content LIKE ?)";
	$like = '%' . $keyword . '%';
	$params[] = $like;
	$params[] = $like;
}

$total = (int)$DB->count_prepare("SELECT count(*) FROM MN_notice_log {$where}", $params);
$total_pages = max(1, (int)ceil($total / $page_size));
if ($page > $total_pages) {
	$page = $total_pages;
}
$offset = ($page - 1) * $page_size;
$logs = $DB->get_all_prepare("SELECT * FROM MN_notice_log {$where} ORDER BY id DESC LIMIT {$offset},{$page_size}", $params) ?: [];
mnbt_render('notice');
