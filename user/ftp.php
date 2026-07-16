<?php
/*
 * 这是远程宝塔文件管理系统
 * 由小泉独立完成
 * 未经允许禁止修改
 * 小泉QQ3108007898
 * 全套系统由小泉以及梦奈完成
 * 版权©归梦奈所有
 */
@header('Content-Type: text/html; charset=UTF-8');
include("../MPHX/common.php");
mnbt_user_require_login();
requirePermission("file_manager");
set_time_limit(0);
ignore_user_abort();
ini_set('memory_limit', '-1');
$siot = $_GET['wj'] ?? '';
mnbt_render('ftp');
