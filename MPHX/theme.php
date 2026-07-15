<?php
/**
 * MNBT 用户端主题系统
 * 主题目录: templates/{theme}/user/
 * 缺页自动回退到 templates/default/user/
 */

if (!defined('IN_CRONLITE')) {
	exit('Access Denied');
}

define('MNBT_THEME_ROOT', ROOT . 'templates/');
define('MNBT_THEME_DEFAULT', 'default');

/**
 * 当前用户端主题名（仅允许字母数字下划线横线）
 */
function mnbt_theme_name()
{
	global $conf;
	$name = '';
	if (is_array($conf) && !empty($conf['usertheme'])) {
		$name = (string)$conf['usertheme'];
	}
	if ($name === '' && is_file(MNBT_THEME_ROOT . 'active_user_theme')) {
		$name = trim((string)@file_get_contents(MNBT_THEME_ROOT . 'active_user_theme'));
	}
	$name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
	if ($name === '' || !is_dir(MNBT_THEME_ROOT . $name . '/user')) {
		return MNBT_THEME_DEFAULT;
	}
	return $name;
}

/**
 * 解析主题视图文件路径（带 default 回退）
 * @param string $view 如 login / head / set，或 user/login
 * @return string|null
 */
function mnbt_theme_resolve($view)
{
	$view = str_replace('\\', '/', (string)$view);
	$view = ltrim($view, '/');
	if (strpos($view, 'user/') === 0) {
		$view = substr($view, 5);
	}
	$view = preg_replace('/\.php$/i', '', $view);
	if ($view === '' || strpos($view, '..') !== false) {
		return null;
	}

	$theme = mnbt_theme_name();
	$candidates = [
		MNBT_THEME_ROOT . $theme . '/user/' . $view . '.php',
	];
	if ($theme !== MNBT_THEME_DEFAULT) {
		$candidates[] = MNBT_THEME_ROOT . MNBT_THEME_DEFAULT . '/user/' . $view . '.php';
	}
	foreach ($candidates as $path) {
		if (is_file($path)) {
			return $path;
		}
	}
	return null;
}

/**
 * 主题静态资源 URL（相对 user/ 页面）
 */
function mnbt_theme_url($path = '')
{
	$path = ltrim(str_replace('\\', '/', (string)$path), '/');
	$base = '../templates/' . mnbt_theme_name() . '/user/';
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
 * 引入主题局部模板（如 head）
 * 主脚本顶层变量会进入 $GLOBALS，模板内可直接使用 $title/$conf/$yhc 等
 */
function mnbt_theme_include($view, array $vars = [])
{
	$path = mnbt_theme_resolve($view);
	if ($path === null) {
		trigger_error('Theme partial not found: ' . $view, E_USER_WARNING);
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
 * 渲染用户端页面模板
 * @param string $view 视图名
 * @param array $vars 额外变量（覆盖同名全局）
 * @param bool $exit 渲染后是否 exit
 */
function mnbt_render($view, array $vars = [], $exit = true)
{
	$path = mnbt_theme_resolve($view);
	if ($path === null) {
		http_response_code(500);
		echo 'Theme view not found: ' . htmlspecialchars((string)$view);
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

/**
 * 用户端登录校验，未登录则跳转
 */
function mnbt_user_require_login()
{
	global $islogins;
	if (!isset($islogins) || (int)$islogins !== 1) {
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
}

/**
 * 已登录则跳转控制面板（用于 login 页）
 */
function mnbt_user_guest_only()
{
	global $islogins;
	if (isset($islogins) && (int)$islogins === 1) {
		exit("<script language='javascript'>window.location.href='./index.php';</script>");
	}
}

/**
 * 列出可用用户主题
 * @return array [name => meta]
 */
function mnbt_theme_list()
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
		if (!is_dir($base . '/user')) {
			continue;
		}
		$meta = [
			'name' => $dir,
			'title' => $dir,
			'version' => '',
			'description' => '',
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
