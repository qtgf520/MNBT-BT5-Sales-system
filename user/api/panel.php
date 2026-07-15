<?php
/**
 * 清凉云 / SPA 面板数据接口（JSON 列表与初始化）
 * 仅在 $egn 匹配时执行
 */
$panel_actions = [
	'monitor_list', 'monitor_log_list', 'notice_list',
	'backup_list', 'deploy_list', 'set_init', 'pass_list',
];
if (!in_array($egn, $panel_actions, true)) {
	return;
}

include_once("../MPHX/monitor.function.php");
if (function_exists('monitor_ensure_tables')) {
	monitor_ensure_tables($DB);
}

if ($egn === 'monitor_list') {
	$tasks = $DB->get_all_prepare(
		"SELECT * FROM MN_monitor_task WHERE user=? ORDER BY id DESC",
		[$yhc['user']]
	) ?: [];
	exit(json_encode([
		'qk' => 1,
		'code' => '获取成功',
		'msg' => [
			'tasks' => $tasks,
			'task_count' => count($tasks),
			'max' => 5,
		],
	], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'monitor_log_list') {
	$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
	$page = max(1, intval($_POST['page'] ?? 1));
	$page_size = intval($_POST['page_size'] ?? 20);
	if (!in_array($page_size, [10, 15, 20, 25, 50, 100], true)) {
		$page_size = 20;
	}
	$where = " WHERE user=?";
	$params = [$yhc['user']];
	if ($id > 0) {
		$where .= " AND task_id=?";
		$params[] = $id;
	}
	$total = (int)$DB->count_prepare("SELECT count(*) FROM MN_monitor_log {$where}", $params);
	$total_pages = max(1, (int)ceil($total / $page_size));
	if ($page > $total_pages) $page = $total_pages;
	$offset = ($page - 1) * $page_size;
	$logs = $DB->get_all_prepare(
		"SELECT * FROM MN_monitor_log {$where} ORDER BY id DESC LIMIT {$offset},{$page_size}",
		$params
	) ?: [];
	exit(json_encode([
		'qk' => 1,
		'code' => '获取成功',
		'msg' => [
			'logs' => $logs,
			'total' => $total,
			'page' => $page,
			'page_size' => $page_size,
			'total_pages' => $total_pages,
			'task_id' => $id,
		],
	], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'notice_list') {
	$type = trim($_POST['type'] ?? '');
	$level = trim($_POST['level'] ?? '');
	$read = trim($_POST['read'] ?? '');
	$keyword = trim($_POST['keyword'] ?? '');
	$page = max(1, intval($_POST['page'] ?? 1));
	$page_size = intval($_POST['page_size'] ?? 15);
	if (!in_array($page_size, [10, 15, 25, 50, 100], true)) {
		$page_size = 15;
	}
	$where = " WHERE user=?";
	$params = [$yhc['user']];
	if ($type !== '') {
		$where .= " AND type=?";
		$params[] = $type;
	}
	if ($level !== '') {
		$where .= " AND level=?";
		$params[] = $level;
	}
	if ($read === 'true' || $read === 'false') {
		$where .= " AND is_read=?";
		$params[] = $read;
	}
	if ($keyword !== '') {
		$where .= " AND (title LIKE ? OR content LIKE ?)";
		$like = '%' . $keyword . '%';
		$params[] = $like;
		$params[] = $like;
	}
	$total = (int)$DB->count_prepare("SELECT count(*) FROM MN_notice_log {$where}", $params);
	$total_pages = max(1, (int)ceil($total / $page_size));
	if ($page > $total_pages) $page = $total_pages;
	$offset = ($page - 1) * $page_size;
	$logs = $DB->get_all_prepare(
		"SELECT * FROM MN_notice_log {$where} ORDER BY id DESC LIMIT {$offset},{$page_size}",
		$params
	) ?: [];
	exit(json_encode([
		'qk' => 1,
		'code' => '获取成功',
		'msg' => [
			'logs' => $logs,
			'total' => $total,
			'page' => $page,
			'page_size' => $page_size,
			'total_pages' => $total_pages,
		],
	], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'backup_list') {
	include("../class.php");
	$api = new bt_api($btipe, $btkeye);
	$hxd = $yhc['hxd'] ?? '';
	$r_data = $api->Databasebackuplist($hxd) ?: [];
	$pattern = '/共(\d+)条/';
	$matches = [];
	preg_match($pattern, $r_data['page'] ?? '', $matches);
	$count = $matches[1] ?? 0;
	$bf_data = $r_data['data'] ?? [];
	if (!is_array($bf_data)) $bf_data = [];
	exit(json_encode([
		'qk' => 1,
		'code' => '获取成功',
		'msg' => [
			'list' => $bf_data,
			'count' => $count,
			'db_id' => $hxd,
			'user' => $yhc['user'] ?? '',
		],
	], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'deploy_list') {
	$rows = $DB->get_all_prepare("SELECT * FROM MN_bs WHERE qk='1' OR qk=1 ORDER BY id DESC") ?: [];
	// 兼容无 qk 字段
	if (!$rows) {
		$rows = $DB->get_all_prepare("SELECT * FROM MN_bs ORDER BY id DESC") ?: [];
	}
	$list = [];
	foreach ($rows as $r) {
		$src = $r['src'] ?? '[]';
		$sxpz = $r['sxpz'] ?? '{}';
		$tj = $r['tj'] ?? '[]';
		if (is_string($src)) {
			$decoded = json_decode($src, true);
			$src = is_array($decoded) ? $decoded : [];
		}
		if (is_string($sxpz)) {
			$decoded = json_decode($sxpz, true);
			$sxpz = is_array($decoded) ? $decoded : [];
		}
		if (is_string($tj)) {
			$decoded = json_decode($tj, true);
			$tj = is_array($decoded) ? $decoded : [];
		}
		$list[] = [
			'id' => $r['id'] ?? 0,
			'name' => $r['name'] ?? '',
			'jc' => $r['jc'] ?? '',
			'jg' => $r['jg'] ?? 0,
			'src' => $src,
			'sxpz' => $sxpz,
			'tj' => $tj,
			'qk' => $r['qk'] ?? '1',
		];
	}
	exit(json_encode([
		'qk' => 1,
		'code' => '获取成功',
		'msg' => [
			'list' => $list,
			'web' => json_decode($yhc['hxa'] ?? '', true) ?: [],
			'sql' => json_decode($yhc['hxb'] ?? '', true) ?: [],
		],
	], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'pass_list') {
	include("../class.php");
	$api = new bt_api($btipe, $btkeye);
	$r = $api->GetLogs($zjid) ?: [];
	$list = is_array($r) ? $r : [];
	exit(json_encode(['qk' => 1, 'code' => '获取成功', 'msg' => ['list' => $list]], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'set_init') {
	$section = trim($_POST['section'] ?? $_POST['gn_section'] ?? 'php');
	include("../class.php");
	$api = new bt_api($btipe, $btkeye);
	$apist = new bt_api_set($btipe, $btkeye);
	$msg = ['section' => $section];

	if ($section === 'php') {
		$list = $apist->btapi_listphp();
		if (is_array($list)) {
			unset($list[0], $list[1]);
			$list = array_values($list);
		} else {
			$list = [];
		}
		$cur = $apist->btapi_phpnowz($yhc['sqldz'] ?? '');
		$msg['php'] = $cur['phpversion'] ?? '';
		$msg['list'] = $list;
	} elseif ($section === 'mrwd') {
		$r = $api->GetLogsea($zjid, '') ?: [];
		// 默认文档可能在不同字段
		$msg['index'] = $r['msg'] ?? $r['index'] ?? '';
		if (is_array($msg['index'])) {
			$msg['index'] = implode(',', $msg['index']);
		}
		// 备用：从站点配置取
		if ($msg['index'] === '' || $msg['index'] === null) {
			$site = $apist->sitemsg($yhc['sqldz'] ?? '') ?: [];
			$sm = $site['msg'] ?? [];
			$msg['index'] = $sm['index'] ?? 'index.php,index.html,index.htm,default.php,default.htm,default.html';
		}
	} elseif ($section === 'yxml') {
		$r = $api->yxmlrhq($zjid, $yhc['sqldz'] ?? '') ?: [];
		$dirs = $r['dir'] ?? $r['dirs'] ?? $r['msg'] ?? [];
		if (!is_array($dirs)) $dirs = [];
		$msg['dirs'] = $dirs;
		$msg['current'] = $r['runPath'] ?? $r['path'] ?? $r['runpath'] ?? '';
	} elseif ($section === 'wjt') {
		// 伪静态模板列表 + 当前规则
		$templates = $api->GetLogswr($yhc['sqldz'] ?? '') ?: [];
		if (!is_array($templates)) $templates = [];
		$msg['templates'] = $templates;
		$msg['current'] = '';
	} elseif ($section === 'gzip') {
		$r = $api->get_gzip_status($yhc['sqldz'] ?? '') ?: [];
		$msg['gzip'] = $r;
	} elseif ($section === 'cache') {
		$r = $api->get_static_cache($yhc['sqldz'] ?? '') ?: [];
		$list = $r['data'] ?? $r['msg'] ?? $r;
		if (!is_array($list)) $list = [];
		// 统一为数组列表
		if (isset($list['status']) || isset($list['msg'])) {
			$list = $list['data'] ?? [];
		}
		$msg['list'] = is_array($list) ? array_values($list) : [];
	} elseif ($section === 'xgpass') {
		$msg['ftp_hint'] = '修改后控制面板登录密码同步为 FTP 密码';
	} elseif ($section === 'mysqlcz') {
		$r = $api->GetDatabaseAccess($yhc['sqluser'] ?? '') ?: [];
		$msg['access'] = $r['msg'] ?? $r['dataAccess'] ?? $r['access'] ?? '127.0.0.1';
		if (is_array($msg['access'])) {
			$msg['access'] = $msg['access']['dataAccess'] ?? '127.0.0.1';
		}
	} elseif ($section === 'url' || $section === 'CDN_url') {
		$msg['btip'] = $cert['btip'] ?? '';
		$msg['als'] = $cert['als'] ?? 'false';
		$msg['is_cdn'] = (string)($yhc['hxc'] ?? '') === '1';
	} else {
		$msg['ok'] = true;
	}

	exit(json_encode(['qk' => 1, 'code' => '获取成功', 'msg' => $msg], JSON_UNESCAPED_UNICODE));
}
