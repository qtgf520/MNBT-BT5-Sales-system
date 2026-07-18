<?php
/**
 * jQueryUI 主题初始化文件
 * 引擎会在首次解析本主题视图时自动加载（通过 mnbt_theme_ensure_loaded）。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

/**
 * 渲染插件菜单树为 jQueryUI 主题的侧边栏结构。
 *
 * 该主题使用 jQueryUI accordion，每个分组都是 <h3> + <div><ul class="jqui-subnav">...</ul></div>。
 * 叶子项统一归入「插件管理」分组。
 *
 * @param array $items 插件菜单树（已按 order 排序）
 * @return string
 */
mnbt_register_theme_menu_renderer('user', function ($items) {
	if (empty($items)) {
		return '';
	}
	$groups = [];
	$leafs = [];
	foreach ($items as $it) {
		if (!empty($it['children'])) {
			$groups[] = $it;
		} else {
			$leafs[] = $it;
		}
	}
	$html = '';
	foreach ($groups as $it) {
		$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
		$icon  = htmlspecialchars($it['icon'] ?? 'mdi-puzzle', ENT_QUOTES, 'UTF-8');
		$childrenHtml = '';
		foreach ($it['children'] as $child) {
			$childTitle = htmlspecialchars($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
			$childUrl   = htmlspecialchars($child['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
			$childIcon  = htmlspecialchars($child['icon'] ?? 'mdi-circle-small', ENT_QUOTES, 'UTF-8');
			$mt = !empty($child['multitabs']) || strpos($childUrl, 'plugin.php') !== false ? ' multitabs' : '';
			$childrenHtml .= '<li><a class="' . trim($mt) . '" href="' . $childUrl . '"><i class="mdi ' . $childIcon . '"></i> ' . $childTitle . '</a></li>';
		}
		$html .= '<h3><i class="mdi ' . $icon . '"></i> ' . $title . '</h3>'
			. '<div><ul class="jqui-subnav">' . $childrenHtml . '</ul></div>';
	}
	if (!empty($leafs)) {
		$leafsHtml = '';
		foreach ($leafs as $it) {
			$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
			$icon  = htmlspecialchars($it['icon'] ?? 'mdi-puzzle', ENT_QUOTES, 'UTF-8');
			$url   = htmlspecialchars($it['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
			$mt = !empty($it['multitabs']) || strpos($url, 'plugin.php') !== false ? ' multitabs' : '';
			$leafsHtml .= '<li><a class="' . trim($mt) . '" href="' . $url . '"><i class="mdi ' . $icon . '"></i> ' . $title . '</a></li>';
		}
		$html .= '<h3><i class="mdi mdi-puzzle"></i> 插件管理</h3>'
			. '<div><ul class="jqui-subnav">' . $leafsHtml . '</ul></div>';
	}
	return $html;
});
