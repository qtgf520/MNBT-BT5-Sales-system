<?php
/**
 * BootstrapUI 主题初始化文件
 * 引擎会在首次解析本主题视图时自动加载（通过 mnbt_theme_ensure_loaded）。
 */
if (!defined('IN_CRONLITE')) {
	exit;
}

/**
 * 渲染插件菜单树为 BootstrapUI 主题的侧边栏结构。
 *
 * @param array $items 插件菜单树（已按 order 排序）
 * @return string
 */
mnbt_register_theme_menu_renderer('user', function ($items) {
	if (empty($items)) {
		return '';
	}
	$html = '';
	$groupIdx = 0;
	foreach ($items as $it) {
		$title = htmlspecialchars($it['title'] ?? '', ENT_QUOTES, 'UTF-8');
		$icon  = htmlspecialchars($it['icon'] ?? 'mdi-puzzle', ENT_QUOTES, 'UTF-8');
		if (!empty($it['children'])) {
			$groupId = 'bsui-plugin-sub-' . $groupIdx;
			$groupIdx++;
			$childrenHtml = '';
			foreach ($it['children'] as $child) {
				$childTitle = htmlspecialchars($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
				$childUrl   = htmlspecialchars($child['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
				$childIcon  = htmlspecialchars($child['icon'] ?? 'mdi-circle-small', ENT_QUOTES, 'UTF-8');
				$mt = !empty($child['multitabs']) || strpos($childUrl, 'plugin.php') !== false ? ' multitabs' : '';
				$childrenHtml .= '<li><a class="bsui-subnav-link' . $mt . '" href="' . $childUrl . '"><i class="mdi ' . $childIcon . '"></i> ' . $childTitle . '</a></li>';
			}
			$html .= '<li class="bsui-nav-item">'
				. '<a href="#' . $groupId . '" class="bsui-nav-link" data-toggle="collapse" role="button" aria-expanded="false">'
				. '<i class="mdi ' . $icon . '"></i> ' . $title . ' <i class="mdi mdi-chevron-right arrow"></i></a>'
				. '<ul class="collapse bsui-subnav" id="' . $groupId . '">' . $childrenHtml . '</ul></li>';
		} else {
			$url = htmlspecialchars($it['url'] ?? 'javascript:void(0)', ENT_QUOTES, 'UTF-8');
			$mt = !empty($it['multitabs']) || strpos($url, 'plugin.php') !== false ? ' multitabs' : '';
			$html .= '<li class="bsui-nav-item">'
				. '<a href="' . $url . '" class="bsui-nav-link' . $mt . '"><i class="mdi ' . $icon . '"></i> ' . $title . '</a></li>';
		}
	}
	return $html;
});
