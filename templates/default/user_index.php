<?php mnbt_theme_include('head'); ?>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,PingFang SC,Microsoft YaHei,sans-serif;min-height:100vh}
.header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:40px 20px 30px;text-align:center}
.header h2{margin:0;font-size:22px;font-weight:600}
.header p{margin:8px 0 0;font-size:14px;opacity:.85}
.container{max-width:600px;margin:0 auto;padding:20px 16px 40px}
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
</style>
</head>
<body>
<?php
$tk=$_COOKIE['mn_user_token']??'';
$user_info=null;
if($tk){
    $dec=base64_decode($tk);
    if($dec){
        list($uid,$uname,$sess)=explode("\t",$dec);
        $u=$DB->get_row_prepare("SELECT * FROM MN_user WHERE id=? AND username=? LIMIT 1",[$uid,$uname]);
        if($u && md5($u['username'].$u['password'].'MNBT')==$sess){
            $user_info=$u;
            $g=$DB->get_row_prepare("SELECT name FROM MN_user_group WHERE id=?",[$u['group_id']]);
            $user_info['group_name']=$g['name']??'默认';
            $logs=$DB->get_all_prepare("SELECT * FROM MN_money_log WHERE user_id=? ORDER BY id DESC LIMIT 10",[$uid]);
        }
    }
}
if(!$user_info){echo '<div class="header"><h2>未登录</h2></div><div class="container"><div class="card"><p style="text-align:center;color:#94a3b8">请先<a href="./login.php">登录</a></p></div></div>';exit;}
?>
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
<?php mnbt_theme_include('foot'); ?>