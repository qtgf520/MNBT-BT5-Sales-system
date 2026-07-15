<?php
include("../MPHX/common.php");
require_once ROOT . 'MPHX/node.function.php';
$title = 'MNBT节点管理';
mnbt_admin_require_login();
mnbt_admin_render('node');
