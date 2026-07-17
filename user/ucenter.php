<?php
include("../MPHX/common.php");
$title = '用户中心';
// 验证独立用户Token
$token=$_COOKIE['mn_user_token']??'';
$mn_user=false;
if($token){
    $mn_user=$UserAuth->validateToken($token);
}
mnbt_render('user_center');
?>