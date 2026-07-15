<?php
@header('Content-Type: text/html; charset=UTF-8');
include("../MPHX/common.php");
mnbt_admin_require_login();
include("../cf_up.php");
mnbt_admin_render('index');
