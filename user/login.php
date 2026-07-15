<?php
include("../MPHX/common.php");
$title = 'MN宝塔主机控制面板登录';
if ($conf['kzmbqk'] == 'false') {
	sysmsg('控制面板已经被关闭详细请联系站长QQ' . $conf['qqh']);
}
mnbt_user_guest_only();
mnbt_render('login');
