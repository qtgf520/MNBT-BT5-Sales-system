<?php
include("../MPHX/common.php");
include("../MPHX/BL.php");
include("../MPHX/SQ.php");
include("../cf_up.php");
$title = 'MN宝塔主机系统更新';
mnbt_admin_require_login();
mnbt_admin_render('update');
