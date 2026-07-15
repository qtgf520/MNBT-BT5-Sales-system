<?php
/**
 * 兼容旧引用：include './head.php'
 * 实际输出当前用户主题下的 head 视图
 */
if (!function_exists('mnbt_theme_include')) {
	include_once dirname(__DIR__) . '/MPHX/common.php';
}
mnbt_theme_include('head');
