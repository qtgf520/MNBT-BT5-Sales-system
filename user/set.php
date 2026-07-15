<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$title = 'MN宝塔主机系统设置';
mnbt_user_require_login();
mnbt_render('set');
