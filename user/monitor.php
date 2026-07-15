<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$title = 'MNBT监控任务';
mnbt_user_require_login();
include_once("../MPHX/monitor.function.php");
monitor_ensure_tables($DB);
$tasks = $DB->get_all_prepare("SELECT * FROM MN_monitor_task WHERE user=? ORDER BY id DESC", [$yhc['user']]) ?: [];
$task_count = count($tasks);
mnbt_render('monitor');
