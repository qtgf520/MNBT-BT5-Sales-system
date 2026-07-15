<?php
@header('Content-Type: text/html; charset=UTF-8');
include("../MPHX/common.php");
mnbt_user_require_login();
mnbt_render('index');
