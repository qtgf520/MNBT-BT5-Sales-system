<?php
/**
 * domain_shop 插件 - 用户端域名绑定 AJAX
 * 迁移自 user/api/domain.php 的 urllist/erurl/tjurl/seturl/scurl
 *
 * 注意：urllist/erurl 保留原 gn 名（与 cdn.php 不冲突，cdn.php 未实现）。
 * tjurl/scurl/seturl 因与 CDN 产品的 cdn.php 同名冲突，改名为：
 *   p_domain_tjurl / p_domain_scurl / p_domain_seturl
 * CDN 产品（hxc==1）仍使用核心 cdn.php 的同名处理器。
 */
if (!defined('IN_CRONLITE')) exit;

/* urllist - 获取用户主机已绑定域名列表 + 子目录列表 */
mnbt_register_ajax('user', 'urllist', function () {
	mnbt_plugin_require_user();
	global $yhc, $ssbt, $zjid, $cert, $btipe, $btkeye, $os_xt;
	$type = isset($_POST['type']) && $_POST['type'] !== '' ? $_POST['type'] : 3;
	include("../class.php");
	$apie = new bt_api_set($btipe, $btkeye);
	$api  = new bt_api($btipe, $btkeye);
	$list  = $apie->btapi_ym($zjid) ?: [];
	$listz = $api->urlzmlls($zjid) ?: [];
	$arr = [];
	if ($type == 2 || $type == 3) {
		foreach ($list as $val) {
			if (($val['name'] ?? '') == $yhc['sqldz']) continue;
			$arr['url'][] = ["name" => $val['name'] ?? '', 'port' => $val['port'] ?? '', 'addtime' => $val['addtime'] ?? '', 'path' => '/'];
		}
	}
	if ($type == 1 || $type == 3) {
		foreach (($listz['binding'] ?? []) as $val) {
			$arr['url'][] = ["name" => $val['domain'] ?? '', 'port' => $val['port'] ?? '', 'addtime' => $val['addtime'] ?? '', 'path' => $val['path'] ?? ''];
		}
	}
	$dirs = $listz['dirs'] ?? [];
	array_unshift($dirs, '/');
	$arr['dir'] = $dirs;
	exit(json_encode($arr, 256));
});

/* erurl - 获取本节点可购二级域名列表（改查 plg_domain_product） */
mnbt_register_ajax('user', 'erurl', function () {
	mnbt_plugin_require_user();
	global $ssbt;
	$bym_list = domain_product_list_by_node($ssbt);
	$arr = [];
	foreach ($bym_list as $res) {
		$arr[] = ["url" => $res['url'], "jg" => $res['jg'], "jj" => $res['js']];
	}
	exit(json_encode($arr, 256));
});

/* p_domain_tjurl - 添加域名（含本站二级域名校验，改查 plg_domain_product）
 * 注意：原 gn 名 tjurl 与 CDN 产品的 cdn.php 冲突，故改名为 p_domain_tjurl。
 * CDN 产品（hxc==1）仍使用核心 cdn.php 的 tjurl/scurl/seturl 处理器。 */
mnbt_register_ajax('user', 'p_domain_tjurl', function () {
	mnbt_plugin_require_user();
	global $yhc, $ssbt, $zjid, $cert, $btipe, $btkeye, $os_xt;
	$path = $_POST['dirs'] ?? '';
	if ($path == '') exit('{"code":"子目录不得为空！"}');
	$url = str_replace([' ', "\t"], '', $_POST['url'] ?? '');
	preg_match("/\d+\.\d+\.\d+\.\d+/", $url, $ure);
	$mhend = strripos($url, ':');
	if (is_numeric($mhend)) $iful = mb_substr($url, 0, $mhend);
	else $iful = $url;
	if ($iful == $cert['btip'] || $ure[0] == $cert['btip']) exit('{"code":"禁止添加本站IP！"}');

	include("../class.php");
	$apie = new bt_api_set($btipe, $btkeye);
	$api  = new bt_api($btipe, $btkeye);
	$ymzce = array_merge($apie->GetLogsy($zjid) ?: [], $api->urlzmlls($zjid)['binding'] ?? []);
	$azxcr = count($ymzce);
	if ($azxcr >= $yhc['ymbds'] + 1 && $yhc['ymbds'] != '0' && $yhc['ymbds'] != '') {
		exit('{"code":"添加失败！域名已达到最大绑定数！"}');
	}

	// 改：从 plg_domain_product 查本站售卖域名
	global $DB;
	$bym_list = $DB->get_all_prepare("SELECT * FROM plg_domain_product order by id desc limit 9999") ?: [];
	$ke_url_fym = false;
	$ke_url_ym = null;
	foreach ($bym_list as $res) {
		if (strpos($url, $res['url']) !== false) {
			if ($res['jg'] > 0) {
				exit('{"code":"禁止使用自定义添加本站的售卖二级域名"}');
			} else {
				$ke_url_fym = true;
				$ke_url_ym = $res;
			}
		}
	}

	if (strpos($path, '/') !== false) {
		$r_data = $apie->btapi_addym($zjid, $yhc['sqldz'], $url);
	} else {
		$r_data = $api->addzml($zjid, $url, $path, $os_xt . $yhc['sqldz']) ?: [];
	}
	$are = $r_data['status'] ?? 'false';
	if (function_exists('logjl')) {
		logjl($yhc['user'], '域名添加', '添加了域名' . $url, '添加成功', $GLOBALS['DB']);
	}
	if ($are == 'true') {
		if ($ke_url_fym && $ke_url_ym) {
			// 改：写入 plg_domain_product 的 json 字段
			$bs_tj = json_decode($ke_url_ym['json'], true);
			if (!is_array($bs_tj)) $bs_tj = [];
			if (!in_array($yhc['user'], $bs_tj)) array_push($bs_tj, $yhc['user']);
			$tj_jg = json_encode($bs_tj, 256);
			$DB->query_prepare("update `plg_domain_product` set `json`=? where `id`=?", [$tj_jg, $ke_url_ym['id']]);
		}
		json_exit('添加成功');
	} else {
		json_exit('添加失败' . ($r_data['msg'] ?? '未知错误'));
	}
});

/* p_domain_scurl - 删除域名（纯宝塔 API）
 * 注意：原 gn 名 scurl 与 CDN 产品的 cdn.php 冲突，故改名为 p_domain_scurl。
 * CDN 产品（hxc==1）仍使用核心 cdn.php 的 scurl 处理器。 */
mnbt_register_ajax('user', 'p_domain_scurl', function () {
	mnbt_plugin_require_user();
	global $yhc, $zjid, $cert, $btipe, $btkeye, $os_xt;
	$url = daddslashes($_POST['url']);
	$dk  = daddslashes($_POST['port']);
	$path = daddslashes($_POST['dir']);
	if ($url == $yhc['sqldz']) exit('{"code":"禁止删除主机名称"}');

	include("../class.php");
	$apie = new bt_api_set($btipe, $btkeye);
	$api  = new bt_api($btipe, $btkeye);
	if ($path == '/') {
		$r_data = $apie->btapi_delym($zjid, $yhc['sqldz'], $url, $dk);
	} else {
		$r_data = $api->delzml($zjid, $url, $os_xt . $yhc['sqldz']) ?: [];
	}
	$are = $r_data['status'] ?? 'false';
	if (function_exists('logjl')) {
		logjl($yhc['user'], '域名删除', '删除了域名' . $url, ($are == 'true' ? '删除成功' : '删除失败'), $GLOBALS['DB']);
	}
	if ($are == 'true') json_exit('删除成功');
	else json_exit('删除失败' . ($r_data['msg'] ?? '未知错误'));
});

/* p_domain_seturl - 修改域名前缀（含本站二级域名校验）
 * 注意：原 gn 名 seturl 与 CDN 产品的 cdn.php 冲突，故改名为 p_domain_seturl。
 * CDN 产品（hxc==1）仍使用核心 cdn.php 的 seturl 处理器。 */
mnbt_register_ajax('user', 'p_domain_seturl', function () {
	mnbt_plugin_require_user();
	global $yhc, $ssbt, $zjid, $cert, $btipe, $btkeye, $os_xt;
	$url_zy  = daddslashes($_POST['zym']);   // 主域
	$url_jqz = daddslashes($_POST['jqz']);   // 旧前缀
	$url_xqz = daddslashes($_POST['xqz']);   // 新前缀
	$url_path = daddslashes($_POST['path']);
	$xurl = $url_xqz . '.' . $url_zy;        // 新域名
	$durl = $url_jqz . '.' . $url_zy;        // 旧域名
	$dk   = daddslashes($_POST['port']);
	if ($durl == $yhc['sqldz']) exit('{"code":"禁止删除主机名称"}');
	if (!preg_match('/^[0-9a-zA-Z]{1,24}$/', $url_xqz) || !preg_match('/^[0-9a-zA-Z]{1,24}$/', $url_jqz)) {
		exit('{"code":"域名前缀不合法！"}');
	}

	include("../class.php");
	$apie = new bt_api_set($btipe, $btkeye);
	$api  = new bt_api($btipe, $btkeye);
	$ymzce = array_merge($apie->GetLogsy($zjid) ?: [], $api->urlzmlls($zjid)['binding'] ?? []);
	$azxcr = 0;
	$jpath = '/';
	foreach ($ymzce as $val) {
		if ($val['domain'] == $durl) $jpath = $val['path'];
		$azxcr++;
	}
	if ($azxcr > $yhc['ymbds'] + 1 && $yhc['ymbds'] != '0' && $yhc['ymbds'] != '') {
		exit('{"code":"添加失败！域名已达到最大绑定数！如想继续添加则请删除多余闲置域名！"}');
	}
	if ($jpath == '/') {
		$r_data = $apie->btapi_delym($zjid, $yhc['sqldz'], $durl, $dk);
	} else {
		$r_data = $api->delzml($zjid, $durl, $os_xt . $yhc['sqldz']) ?: [];
	}
	$are = $r_data['status'] ?? 'false';
	if ($are == 'true') {
		$url = str_replace([' ', "\t"], '', $xurl);
		preg_match("/\d+\.\d+\.\d+\.\d+/", $url, $ure);
		if ($url == $cert['btip'] || $ure[0] == $cert['btip']) exit('{"code":"禁止添加本站IP！"}');
		$ymzce = array_merge($apie->GetLogsy($zjid) ?: [], $api->urlzmlls($zjid)['binding'] ?? []);
		$azxcr = count($ymzce);
		if ($azxcr >= $yhc['ymbds'] + 1 && $yhc['ymbds'] != '0' && $yhc['ymbds'] != '') {
			exit('{"code":"添加失败！域名已达到最大绑定数！"}');
		}
		if (strpos($url_path, '/') !== false) {
			$r_data = $apie->btapi_addym($zjid, $yhc['sqldz'], $url . ':' . $dk);
		} else {
			$r_data = $api->addzml($zjid, $url . ':' . $dk, $url_path, $os_xt . $yhc['sqldz']) ?: [];
		}
		$are = $r_data['status'] ?? 'false';
		if (function_exists('logjl')) {
			logjl($yhc['user'], '域名修改', '将域名' . $durl . '修改为' . $xurl, ($are == 'true' ? '修改成功' : '修改失败'), $GLOBALS['DB']);
		}
		if ($are == 'true') json_exit('添加成功');
		else json_exit('添加失败' . ($r_data['msg'] ?? '未知错误'));
	} else {
		json_exit('删除失败' . ($r_data['msg'] ?? '未知错误'));
	}
});
