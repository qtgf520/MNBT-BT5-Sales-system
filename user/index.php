<?php
include("../MPHX/common.php");

// ★ 检查独立用户登录(mn_user_token)
$tk=$_COOKIE['mn_user_token']??'';
$user_info=null;
if($tk){
    $dec=base64_decode($tk);
    if($dec){
        list($uid,$uname,$sess)=explode("\t",$dec);
        $u=$DB->get_row_prepare("SELECT * FROM MN_user WHERE id=? AND username=? LIMIT 1",[$uid,$uname]);
        if($u && md5($u['username'].$u['password'].'MNBT')==$sess && $u['status']=='true'){
            $user_info=$u;
            $g=$DB->get_row_prepare("SELECT name FROM MN_user_group WHERE id=?",[$u['group_id']]);
            $user_info['group_name']=$g['name']??'默认';
            $logs=$DB->get_all_prepare("SELECT * FROM MN_money_log WHERE user_id=? ORDER BY id DESC LIMIT 10",[$uid]);
            // 查询已分配的主机
            $hosts=$DB->get_all_prepare("SELECT z.id,z.sqldz,z.ssbt,z.datae,z.qk,z.user,z.pass,z.hxc FROM MN_zj z INNER JOIN MN_user_host uh ON z.id=uh.host_id WHERE uh.user_id=? ORDER BY z.id ASC",[$uid]);
        }
    }
}

if(!$user_info){
    exit("<script>window.location.href='./login.php';</script>");
}
?><!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>用户中心 - MNBT</title>
<link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,PingFang SC,Microsoft YaHei,sans-serif;min-height:100vh}
.header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:40px 20px 30px;text-align:center}
.header h2{margin:0;font-size:22px;font-weight:600}
.header p{margin:8px 0 0;font-size:14px;opacity:.85}
.container{max-width:700px;margin:0 auto;padding:20px 16px 40px}
.card{background:#fff;border-radius:12px;padding:20px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.card-title{font-size:14px;font-weight:600;color:#1e293b;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #eef1f4}
.info-row{display:flex;justify-content:space-between;padding:6px 0;font-size:14px}
.info-row .label{color:#94a3b8}
.info-row .value{color:#1e293b;font-weight:500}
.money-amount{font-size:24px;font-weight:700;color:#667eea;text-align:center;padding:8px 0 4px}
.money-label{text-align:center;font-size:12px;color:#94a3b8;margin-bottom:8px}
.log-table{width:100%;font-size:13px;border-collapse:collapse}
.log-table th{text-align:left;padding:6px 4px;color:#94a3b8;font-weight:500;border-bottom:1px solid #eef1f4}
.log-table td{padding:6px 4px;border-bottom:1px solid #f1f5f9;color:#1e293b}
.log-table .pos{color:#10b981}
.log-table .neg{color:#ef4444}
.btn-logout{display:block;width:100%;padding:12px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#ef4444;font-size:14px;cursor:pointer;text-align:center;margin-top:8px;transition:all .2s}
.btn-logout:hover{background:#fef2f2;border-color:#fecaca}
.empty{text-align:center;padding:20px;color:#94a3b8;font-size:13px}
.host-item{border:1px solid #eef1f4;border-radius:8px;padding:12px;margin-bottom:10px;transition:all .2s}
.host-item:hover{border-color:#667eea;box-shadow:0 2px 8px rgba(102,126,234,.15)}
.host-item .host-name{font-size:15px;font-weight:600;color:#1e293b}
.host-item .host-meta{font-size:12px;color:#94a3b8;margin-top:4px}
.host-item .host-detail{font-size:13px;color:#475569;margin-top:6px}
.host-item .badge-status{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:500}
.badge-ok{background:#d1fae5;color:#065f46}
.badge-off{background:#fee2e2;color:#991b1b}
</style>
</head>
<body>
<div class="header">
  <h2>👋 欢迎回来</h2>
  <p><?=htmlspecialchars($user_info['username'])?> · <?=htmlspecialchars($user_info['group_name'])?></p>
</div>
<div class="container">
  <div class="card">
    <div class="card-title">账户信息</div>
    <div class="info-row"><span class="label">用户名</span><span class="value"><?=htmlspecialchars($user_info['username'])?></span></div>
    <div class="info-row"><span class="label">邮箱</span><span class="value"><?=htmlspecialchars($user_info['email']?:'未设置')?></span></div>
    <div class="info-row"><span class="label">用户组</span><span class="value"><?=htmlspecialchars($user_info['group_name'])?></span></div>
    <div class="info-row"><span class="label">注册时间</span><span class="value"><?=htmlspecialchars($user_info['reg_date'])?></span></div>
    <div class="info-row"><span class="label">最后登录</span><span class="value"><?=htmlspecialchars($user_info['login_date']?:'从未')?></span></div>
  </div>
  
  <div class="card">
    <div class="card-title">我的余额</div>
    <div class="money-amount">¥<?=number_format($user_info['money']??0,2)?></div>
    <div class="money-label">可用余额</div>
  </div>
  
  <div class="card">
    <div class="card-title">我的主机 <span class="text-muted small">(<?=count($hosts??[])?>台)</span></div>
    <?php if(!empty($hosts)):?>
      <?php foreach($hosts as $h):?>
      <div class="host-item">
        <div class="d-flex justify-content-between align-items-start">
          <div class="host-name"><?=htmlspecialchars($h['sqldz'])?></div>
          <span class="badge-status <?=$h['qk']=='false'?'badge-off':'badge-ok'?>"><?=$h['qk']=='false'?'已关闭':'正常'?></span>
        </div>
        <div class="host-meta">所属宝塔：<?=htmlspecialchars($h['ssbt'])?> ｜ 到期：<?=htmlspecialchars($h['datae']?:'永久')?></div>
        <div class="host-detail">账号：<?=htmlspecialchars($h['user'])?> ｜ 密码：<?=htmlspecialchars($h['pass'])?></div>
      </div>
      <?php endforeach;?>
    <?php else:?>
    <div class="empty">暂无分配的主机</div>
    <?php endif;?>
  </div>
  
  <div class="card">
    <div class="card-title">最近资金流水</div>
    <?php if(!empty($logs)):?>
    <table class="log-table">
      <tr><th>时间</th><th>金额</th><th>说明</th></tr>
      <?php foreach($logs as $log):?>
      <tr>
        <td><?=substr($log['date']??'',5,11)?></td>
        <td class="<?=$log['money']>=0?'pos':'neg'?>"><?=$log['money']>=0?'+':''?><?=number_format($log['money'],2)?></td>
        <td><?=htmlspecialchars($log['memo']??'')?></td>
      </tr>
      <?php endforeach;?>
    </table>
    <?php else:?>
    <div class="empty">暂无资金流水记录</div>
    <?php endif;?>
  </div>
  
  <button class="btn-logout" onclick="if(confirm('确定退出？')){$.post('./ajax.php',{gn:'user_logout'},function(){window.location.href='./login.php';});}">退出登录</button>
</div>
</body>
</html>