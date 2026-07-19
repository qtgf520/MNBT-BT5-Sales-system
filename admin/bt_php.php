<?php
include("../MPHX/common.php");
require_once ROOT . 'MPHX/bt_php.function.php';
$title = '节点PHP版本管理';
mnbt_admin_require_login();
mnbt_admin_render('bt_php');
