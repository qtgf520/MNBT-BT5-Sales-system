<?php
/**
 * domain_shop 插件 - 域名购买下单
 * 迁移自 user/pay.php 的 $pay_lx=='ymgm' 分支
 *
 * 接收 POST：urla（一级域名）/ urlb（前缀）/ urlzml（子目录）/ yzdip（源站IP）/ type（支付方式）
 * 创建 MN_dd 订单后调 mnbt_pay_dispatch_gateway 分发到支付插件
 */
if (!defined('IN_CRONLITE')) exit;

function domain_shop_handle_buy()
{
	global $DB, $yhc, $ssbt, $zjid, $date, $conf, $siteurl;

	mnbt_plugin_require_user();

	$ym_a = daddslashes($_POST['urla'] ?? '');   // 一级域名
	$ym_b = daddslashes($_POST['urlb'] ?? '');   // 域名前缀
	$type = daddslashes($_POST['type'] ?? '');   // 支付方式 type
	$urlzml = $_POST['urlzml'] ?? '/';
	$yzdip = $_POST['yzdip'] ?? '';

	// 查商品（从 plg_domain_product 取代原 MN_ym）
	$bs_cx = domain_product_get_by_url($ym_a);
	if (!$bs_cx) {
		exit("<script>alert('域名商品不存在！');history.go(-1);</script>");
	}
	if ($bs_cx['qk'] != 'true') {
		exit("<script>alert('该域名已下架！');history.go(-1);</script>");
	}
	if (!preg_match('/^[0-9a-zA-Z]{1,24}$/', $ym_b)) {
		exit("<script>alert('前缀不合法！');history.go(-1);</script>");
	}

	$money = $bs_cx['jg'];
	$out_trade_no = date("YmdHis") . mt_rand(100, 999);
	$name = 'MNBT域名购买';
	$ip = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';

	// 取节点信息（与原 pay.php ymgm 分支一致）
	$cert = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? limit 1", [$ssbt]);
	if (!$cert) {
		exit("<script>alert('宝塔节点不存在！');history.go(-1);</script>");
	}
	$btipe = ($cert['ptl'] == 'true' ? 'https' : 'http') . '://' . $cert['btip'] . ':' . $cert['btdk'];
	$btkeye = $cert['btmy'];
	include_once __DIR__ . "/../../user/class.php";
	$apie = new bt_api_set($btipe, $btkeye);
	$ymzce = $apie->GetLogsy($zjid) ?: [];
	$azxcr = count($ymzce);

	if ($cert['btos'] == '1') {
		$l_ler_a = '/etc/hosts';
	} else {
		$l_ler_a = 'C:\Windows\System32\drivers\etc\hosts';
	}
	$urlpath = ($urlzml == '') ? '/' : $urlzml;

	if ($azxcr >= $yhc['ymbds'] + 1 && $yhc['ymbds'] != '0' && $yhc['ymbds'] != '') {
		exit("<script>alert('您域名绑定数已达最大！');history.go(-1);</script>");
	}

	// 构造订单参数（与原 pay.php 一致）
	$cs = json_encode([
		'user'    => $yhc['user'],
		'url_qz'  => $ym_b,
		'url_zd'  => $ym_a,
		'path'    => $urlpath,
		'url_zy'  => $ym_b . '.' . $ym_a,
		'type'    => $yhc['hxc'],
		'yz_ip'   => $yzdip,
		'hostly'  => $l_ler_a,
	], 256);

	$row1 = $DB->get_row_prepare("SELECT * FROM MN_dd WHERE 1 order by id desc limit 1");
	$id = $row1['id'] + 1;
	$DB->query_prepare(
		"INSERT INTO `MN_dd` (`id`, `cs`, `date`, `zffs`, `je`, `ddh`, `lx`, `qk`, `ip`) VALUES (?,?,?,?,?,?,?,?,?)",
		[$id, $cs, $date, $type, $money, $out_trade_no, 'ymgm', 'false', $ip]
	);

	// 分发到支付插件
	$order_context = [
		'out_trade_no' => $out_trade_no,
		'name'         => $name,
		'money'        => $money,
		'type'         => $type,
		'siteurl'      => $siteurl,
		'pay_lx'       => 'ymgm',
	];

	$html = mnbt_pay_dispatch_gateway($type, $order_context);
	if ($html === false) {
		exit("<script>alert('支付方式不可用');history.go(-1);</script>");
	}
	echo $html;
}
