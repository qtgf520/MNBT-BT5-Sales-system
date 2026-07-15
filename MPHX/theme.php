<?php
/**
 * MNBT 主题系统（用户端 + 管理端）
 * 目录: templates/{theme}/user/ 与 templates/{theme}/admin/
 * 缺页自动回退到 templates/default/{scope}/
 */

if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

define('MNBT_THEME_ROOT', ROOT . 'templates/');
define('MNBT_THEME_DEFAULT', 'default');

/**
 * 规范化主题名
 */
function mnbt_theme_sanitize($name)
{
	return preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$name);
}

/**
 * 当前主题名
 * @param string $scope user|admin
 */
function mnbt_theme_name($scope = 'user')
{
	global $conf;
	$scope = ($scope === 'admin') ? 'admin' : 'user';
	$confKey = $scope === 'admin' ? 'admintheme' : 'usertheme';
	$fileKey = $scope === 'admin' ? 'active_admin_theme' : 'active_user_theme';

	$name = '';
	if (is_array($conf) && !empty($conf[$confKey])) {
		$name = (string)$conf[$confKey];
	}
	if ($name === '' && is_file(MNBT_THEME_ROOT . $fileKey)) {
		$name = trim((string)@file_get_contents(MNBT_THEME_ROOT . $fileKey));
	}
	$name = mnbt_theme_sanitize($name);
	if ($name === '' || !is_dir(MNBT_THEME_ROOT . $name . '/' . $scope)) {
		return MNBT_THEME_DEFAULT;
	}
	return $name;
}

/**
 * 解析视图路径（带 default 回退）
 * @param string $view 如 login / head，或 user/login / admin/login
 * @param string $scope user|admin
 */
function mnbt_theme_resolve($view, $scope = 'user')
{
	$view = str_replace('\\', '/', (string)$view);
	$view = ltrim($view, '/');
	$scope = ($scope === 'admin') ? 'admin' : 'user';

	if (strpos($view, 'user/') === 0) {
		$scope = 'user';
		$view = substr($view, 5);
	} elseif (strpos($view, 'admin/') === 0) {
		$scope = 'admin';
		$view = substr($view, 6);
	}

	$view = preg_replace('/\.php$/i', '', $view);
	if ($view === '' || strpos($view, '..') !== false) {
		return null;
	}

	$theme = mnbt_theme_name($scope);
	$candidates = [
		MNBT_THEME_ROOT . $theme . '/' . $scope . '/' . $view . '.php',
	];
	if ($theme !== MNBT_THEME_DEFAULT) {
		$candidates[] = MNBT_THEME_ROOT . MNBT_THEME_DEFAULT . '/' . $scope . '/' . $view . '.php';
	}
	foreach ($candidates as $path) {
		if (is_file($path)) {
			return $path;
		}
	}
	return null;
}

/**
 * 主题静态资源 URL（相对 user/ 或 admin/ 页面）
 */
function mnbt_theme_url($path = '', $scope = 'user')
{
	$scope = ($scope === 'admin') ? 'admin' : 'user';
	$path = ltrim(str_replace('\\', '/', (string)$path), '/');
	$base = '../templates/' . mnbt_theme_name($scope) . '/' . $scope . '/';
	return $path === '' ? $base : $base . $path;
}

/**
 * 公共静态资源（imsetes）URL
 */
function mnbt_asset_url($path = '')
{
	$path = ltrim(str_replace('\\', '/', (string)$path), '/');
	return $path === '' ? '../imsetes/' : '../imsetes/' . $path;
}

/**
 * 引入主题局部模板
 */
function mnbt_theme_include($view, array $vars = [], $scope = 'user')
{
	$path = mnbt_theme_resolve($view, $scope);
	if ($path === null) {
		trigger_error('Theme partial not found: ' . $scope . '/' . $view, E_USER_WARNING);
		return false;
	}
	extract($GLOBALS, EXTR_SKIP);
	if ($vars) {
		extract($vars, EXTR_OVERWRITE);
	}
	include $path;
	return true;
}

/**
 * 渲染页面模板
 */
function mnbt_render($view, array $vars = [], $exit = true, $scope = 'user')
{
	$path = mnbt_theme_resolve($view, $scope);
	if ($path === null) {
		http_response_code(500);
		echo 'Theme view not found: ' . htmlspecialchars($scope . '/' . (string)$view);
		if ($exit) {
			exit;
		}
		return false;
	}
	extract($GLOBALS, EXTR_SKIP);
	if ($vars) {
		extract($vars, EXTR_OVERWRITE);
	}
	include $path;
	if ($exit) {
		exit;
	}
	return true;
}

/** 管理端快捷渲染 */
function mnbt_admin_render($view, array $vars = [], $exit = true)
{
	return mnbt_render($view, $vars, $exit, 'admin');
}

/** 管理端快捷 include */
function mnbt_admin_include($view, array $vars = [])
{
	return mnbt_theme_include($view, $vars, 'admin');
}

function mnbt_user_require_login()
{
	global $islogins;
	if (!isset($islogins) || (int)$islogins !== 1) {
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
}

function mnbt_user_guest_only()
{
	global $islogins;
	if (isset($islogins) && (int)$islogins === 1) {
		exit("<script language='javascript'>window.location.href='./index.php';</script>");
	}
}

function mnbt_admin_require_login()
{
	global $islogin;
	if (!isset($islogin) || (int)$islogin !== 1) {
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
}

function mnbt_admin_guest_only()
{
	global $islogin;
	if (isset($islogin) && (int)$islogin === 1) {
		exit("<script language='javascript'>window.location.href='./index.php';</script>");
	}
}

/**
 * 列出主题
 * @param string|null $scope user|admin|null(全部，只要有任一侧目录)
 */
function mnbt_theme_list($scope = null)
{
	$list = [];
	if (!is_dir(MNBT_THEME_ROOT)) {
		return $list;
	}
	$dirs = @scandir(MNBT_THEME_ROOT) ?: [];
	foreach ($dirs as $dir) {
		if ($dir === '.' || $dir === '..') {
			continue;
		}
		$base = MNBT_THEME_ROOT . $dir;
		if (!is_dir($base)) {
			continue;
		}
		$hasUser = is_dir($base . '/user');
		$hasAdmin = is_dir($base . '/admin');
		if ($scope === 'user' && !$hasUser) {
			continue;
		}
		if ($scope === 'admin' && !$hasAdmin) {
			continue;
		}
		if ($scope === null && !$hasUser && !$hasAdmin) {
			continue;
		}
		$meta = [
			'name' => $dir,
			'title' => $dir,
			'version' => '',
			'description' => '',
			'has_user' => $hasUser,
			'has_admin' => $hasAdmin,
		];
		$json = $base . '/theme.json';
		if (is_file($json)) {
			$data = json_decode((string)@file_get_contents($json), true);
			if (is_array($data)) {
				$meta['title'] = $data['title'] ?? $meta['title'];
				$meta['version'] = $data['version'] ?? '';
				$meta['description'] = $data['description'] ?? '';
			}
		}
		$list[$dir] = $meta;
	}
	return $list;
}

/**
 * 设置当前主题（写文件；若表字段存在则同步数据库）
 */
function mnbt_theme_set_active($scope, $name)
{
	global $DB, $conf, $siteid;
	$scope = ($scope === 'admin') ? 'admin' : 'user';
	$name = mnbt_theme_sanitize($name);
	if ($name === '' || !is_dir(MNBT_THEME_ROOT . $name . '/' . $scope)) {
		return [false, '主题不存在或不支持该端：' . $name];
	}

	$file = MNBT_THEME_ROOT . ($scope === 'admin' ? 'active_admin_theme' : 'active_user_theme');
	if (@file_put_contents($file, $name) === false) {
		return [false, '无法写入主题配置文件，请检查 templates 目录写权限'];
	}

	$confKey = $scope === 'admin' ? 'admintheme' : 'usertheme';
	if (is_array($conf)) {
		$conf[$confKey] = $name;
	}

	// 可选：同步到 MN_config（字段不存在时忽略）
	if (isset($DB) && is_object($DB)) {
		$col = $confKey;
		$sid = isset($siteid) ? $siteid : 1;
		@$DB->query_prepare("UPDATE `MN_config` SET `{$col}` = ? WHERE `id` = ?", [$name, $sid]);
	}

	return [true, '设置成功'];
}
