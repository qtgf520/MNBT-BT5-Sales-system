<?php
// ★ 独立用户注册/登录 - 直接在此处理，绕过opcache
$action=$_POST['action']??'';
if($action=='user_register'||$action=='user_login'){
    $cfg=[];require dirname(__FILE__).'/../config.php';
    $m=@new mysqli($dbconfig['host'],$dbconfig['user'],$dbconfig['pwd'],$dbconfig['dbname'],$dbconfig['port']);
    if($m->connect_error)die('{"code":"系统错误"}');
    $m->query("set names utf8");
    if($action=='user_register'){
        $u=trim($_POST['username']??'');$p=$_POST['password']??'';$e=trim($_POST['email']??'');
        if(strlen($u)<3)die('{"code":"用户名太短"}');if(strlen($p)<6)die('{"code":"密码太短"}');
        $s=$m->prepare("SELECT id FROM MN_user WHERE username=?");$s->bind_param('s',$u);$s->execute();
        if($s->get_result()->num_rows>0)die('{"code":"用户名已存在"}');$s->close();
        $salt=substr(md5(uniqid(mt_rand(),true)),0,8);$pe=md5(md5($p).$salt);$d=date('Y-m-d H:i:s');$ip=$_SERVER['REMOTE_ADDR']??'0.0.0.0';
        $s=$m->prepare("INSERT INTO MN_user (username,password,salt,email,group_id,status,reg_date,reg_ip) VALUES(?,?,?,?,1,'true',?,?)");
        $s->bind_param('ssssss',$u,$pe,$salt,$e,$d,$ip);
        if($s->execute())die('{"code":"注册成功"}');else die('{"code":"注册失败"}');
    }
    if($action=='user_login'){
        $u=trim($_POST['username']??'');$p=$_POST['password']??'';
        $s=$m->prepare("SELECT * FROM MN_user WHERE username=? LIMIT 1");$s->bind_param('s',$u);$s->execute();$user=$s->get_result()->fetch_assoc();
        if(!$user)die('{"code":"用户不存在"}');if($user['status']!='true')die('{"code":"账号禁用"}');
        $pe=md5(md5($p).$user['salt']);if($pe!=$user['password'])die('{"code":"密码错误"}');
        $d=date('Y-m-d H:i:s');$ip=$_SERVER['REMOTE_ADDR']??'0.0.0.0';
        $s=$m->prepare("UPDATE MN_user SET login_date=?,login_ip=? WHERE id=?");$s->bind_param('ssi',$d,$ip,$user['id']);$s->execute();
        $token=base64_encode($user['id']."\t".$user['username']."\t".md5($user['username'].$user['password'].'MNBT'));
        setcookie("mn_user_token",$token,time()+604800,'/');
        die('{"code":"登陆成功"}');
    }
}

// ★ 直接输出完整HTML，绕过模板引擎的opcache
?><!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>MNBT用户登录/注册</title>
<link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.bootcdn.net/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css" rel="stylesheet">
<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,PingFang SC,Microsoft YaHei,sans-serif;padding:20px}
.login-box{background:#fff;border-radius:16px;padding:40px 32px;width:100%;max-width:400px;box-shadow:0 10px 40px rgba(0,0,0,.15)}
.login-box h3{text-align:center;margin-bottom:8px;color:#1e293b;font-weight:600}
.login-box .sub{text-align:center;color:#94a3b8;font-size:14px;margin-bottom:28px}
.form-group{margin-bottom:16px}
.form-control{height:44px;border-radius:10px;border:1px solid #e2e8f0;padding:0 14px;font-size:14px;background:#f8fafc}
.form-control:focus{border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.2);background:#fff;outline:none}
.btn-login{height:44px;border-radius:10px;border:none;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:15px;font-weight:600;width:100%;cursor:pointer;transition:opacity .2s}
.btn-login:hover{opacity:.9}
.btn-login:active{transform:scale(.98)}
.toggle-link{text-align:center;margin-top:16px;font-size:13px}
.toggle-link a{color:#667eea;cursor:pointer;text-decoration:none}
.toggle-link a:hover{text-decoration:underline}
.alert-msg{padding:10px 14px;border-radius:8px;margin-bottom:16px;display:none;font-size:13px}
.alert-success{background:#d1fae5;color:#065f46;display:block}
.alert-error{background:#fee2e2;color:#991b1b;display:block}
.hidden{display:none}
</style>
</head>
<body>
<div class="login-box">
  <h3>MNBT 用户中心</h3>
  <p class="sub" id="formTitle">用户登录</p>
  <div id="alertMsg" class="alert-msg"></div>
  <form onsubmit="return false" id="loginForm">
    <div class="form-group">
      <input type="text" class="form-control" id="username" placeholder="用户名" autocomplete="username">
    </div>
    <div id="emailGroup" class="form-group hidden">
      <input type="email" class="form-control" id="regEmail" placeholder="邮箱(选填)" autocomplete="email">
    </div>
    <div id="pwd2Group" class="form-group hidden">
      <input type="password" class="form-control" id="regPwd2" placeholder="确认密码">
    </div>
    <div class="form-group">
      <input type="password" class="form-control" id="password" placeholder="密码" autocomplete="current-password">
    </div>
    <button class="btn-login" id="submitBtn" onclick="doSubmit()">登 录</button>
  </form>
  <div class="toggle-link">
    <a id="toggleMode" onclick="toggleMode()">没有账号？去注册</a>
  </div>
</div>
<script>
var isReg=false;
function toggleMode(){
  isReg=!isReg;
  if(isReg){
    $('#formTitle').text('用户注册');
    $('#submitBtn').text('注 册');
    $('#emailGroup').removeClass('hidden');
    $('#pwd2Group').removeClass('hidden');
    $('#toggleMode').text('已有账号？去登录');
  }else{
    $('#formTitle').text('用户登录');
    $('#submitBtn').text('登 录');
    $('#emailGroup').addClass('hidden');
    $('#pwd2Group').addClass('hidden');
    $('#toggleMode').text('没有账号？去注册');
  }
}
function showMsg(type,text){
  var el=$('#alertMsg');
  el.removeClass('alert-success alert-error').addClass(type=='ok'?'alert-success':'alert-error').text(text).show();
  setTimeout(function(){el.hide();},3000);
}
function doSubmit(){
  var user=$('#username').val().trim();
  var pwd=$('#password').val();
  if(user==''||pwd==''){showMsg('err','请填写完整');return;}
  if(isReg){
    var p2=$('#regPwd2').val();
    if(pwd.length<6){showMsg('err','密码至少6位');return;}
    if(pwd!=p2){showMsg('err','两次密码不一致');return;}
    $.post('./login.php',{action:'user_register',username:user,password:pwd,email:$('#regEmail').val()},function(d){
      var r=typeof d=='string'?JSON.parse(d):d;
      if(r.code=='注册成功'){showMsg('ok','注册成功！请登录');toggleMode();}
      else{showMsg('err',r.code);}
    });
  }else{
    $.post('./login.php',{action:'user_login',username:user,password:pwd},function(d){
      var r=typeof d=='string'?JSON.parse(d):d;
      if(r.code=='登陆成功'){showMsg('ok','登录成功，正在跳转...');setTimeout(function(){window.location.href='./index.php';},800);}
      else{showMsg('err',r.code);}
    });
  }
}
document.addEventListener('keydown',function(e){if(e.key=='Enter')doSubmit();});
</script>
</body>
</html>
