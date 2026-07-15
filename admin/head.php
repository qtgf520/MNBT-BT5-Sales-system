<?php
/**
 * 兼容旧引用：include './head.php'
 */
if (!function_exists('mnbt_admin_include')) {
	include_once dirname(__DIR__) . '/MPHX/common.php';
}
mnbt_admin_include('head');
