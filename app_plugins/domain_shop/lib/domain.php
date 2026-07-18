<?php
/**
 * domain_shop 插件 - 域名商品 CRUD 函数
 * 迁移自原 admin/api/ym.php，操作 plg_domain_product 表
 */
if (!defined('IN_CRONLITE')) exit;

/**
 * 添加域名商品
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function domain_product_add($url, $btdh, $jg, $js, $qk = 'true')
{
	global $DB, $date, $user;
	$url = trim($url);
	$btdh = trim($btdh);
	$jg = (string)$jg;
	$js = trim($js);
	$qk = $qk ? 'true' : 'false';

	if ($url === '' || $btdh === '' || $btdh === '-00-' || $js === '') {
		return ['ok' => false, 'msg' => '表单不能为空或未选择宝塔'];
	}
	if (!preg_match('/^[0-9a-zA-Z\.\-]{1,128}$/', $url)) {
		return ['ok' => false, 'msg' => '域名格式不合法'];
	}

	$exists = $DB->get_row_prepare("SELECT id FROM plg_domain_product WHERE url=? limit 1", [$url]);
	if ($exists) {
		return ['ok' => false, 'msg' => '该域名已存在'];
	}

	$ok = $DB->query_prepare(
		"INSERT INTO `plg_domain_product` (`url`, `btdh`, `jg`, `date`, `js`, `json`, `qk`) VALUES (?,?,?,?,?,?,?)",
		[$url, $btdh, $jg, $date, $js, '[]', $qk]
	);
	if (!$ok) return ['ok' => false, 'msg' => '添加失败：' . $DB->error()];

	if (function_exists('logjl')) {
		logjl($user, '添加域名商品', '添加了 ' . $url, '添加成功', $DB);
	}
	return ['ok' => true, 'msg' => '添加成功'];
}

/**
 * 修改域名商品（仅允许改介绍/价格/上架状态）
 */
function domain_product_update($id, $js, $jg, $qk)
{
	global $DB, $user;
	$id = (int)$id;
	$qk = $qk ? 'true' : 'false';
	$ok = $DB->query_prepare(
		"update `plg_domain_product` set `js`=?, `jg`=?, `qk`=? where `id`=?",
		[$js, $jg, $qk, $id]
	);
	if (!$ok) return ['ok' => false, 'msg' => '修改失败：' . $DB->error()];

	if (function_exists('logjl')) {
		logjl($user, '修改域名商品', '修改了 ID=' . $id, '修改成功', $DB);
	}
	return ['ok' => true, 'msg' => '修改成功'];
}

/**
 * 删除单个域名商品
 */
function domain_product_delete($id)
{
	global $DB, $user;
	$id = (int)$id;
	$ok = $DB->query_prepare("DELETE FROM plg_domain_product WHERE id=? limit 1", [$id]);
	if (!$ok) return ['ok' => false, 'msg' => '删除失败：' . $DB->error()];

	if (function_exists('logjl')) {
		logjl($user, '删除域名商品', '删除了 ID=' . $id, '删除成功', $DB);
	}
	return ['ok' => true, 'msg' => '删除成功'];
}

/**
 * 批量删除域名商品
 * @return array ['ok'=>int, 'fail'=>int]
 */
function domain_product_delete_batch(array $ids)
{
	global $DB, $user;
	$ok = 0; $fail = 0;
	foreach ($ids as $id) {
		$id = (int)$id;
		if ($DB->query_prepare("DELETE FROM plg_domain_product WHERE id=? limit 1", [$id])) {
			$ok++;
		} else {
			$fail++;
		}
	}
	if (function_exists('logjl')) {
		logjl($user, '批量删除域名商品', '删除了 ' . $ok . ' 条', '删除成功', $DB);
	}
	return ['ok' => $ok, 'fail' => $fail];
}

/**
 * 列表分页查询
 * @return array ['total'=>int, 'rows'=>array]
 */
function domain_product_list($page, $pagesize, $sort = 'id', $order = 'ASC')
{
	global $DB;
	$sort = preg_replace('/[^a-zA-Z0-9_]/', '', $sort) ?: 'id';
	$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
	$page = max(1, (int)$page);
	$pagesize = max(1, (int)$pagesize);
	$offset = ($page - 1) * $pagesize;

	$total = (int)$DB->count_prepare("SELECT count(*) from plg_domain_product WHERE 1");
	$rows = $DB->get_all_prepare("SELECT * FROM plg_domain_product order by $sort $order limit $offset,$pagesize") ?: [];
	return ['total' => $total, 'rows' => $rows];
}

/**
 * 根据 URL 取域名商品（用于支付结算时查商品）
 */
function domain_product_get_by_url($url)
{
	global $DB;
	return $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE url=? limit 1", [$url]);
}

/**
 * 根据宝塔节点取上架域名（用户端下拉用）
 */
function domain_product_list_by_node($btdh)
{
	global $DB;
	return $DB->get_all_prepare("SELECT * FROM plg_domain_product WHERE btdh=? and qk='true' order by id desc limit 9999", [$btdh]) ?: [];
}

/**
 * 记录用户购买（写入 json 字段）
 */
function domain_product_add_buyer($productId, $user)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE id=? limit 1", [$productId]);
	if (!$row) return false;
	$buyers = json_decode($row['json'], true);
	if (!is_array($buyers)) $buyers = [];
	if (!in_array($user, $buyers)) $buyers[] = $user;
	return $DB->query_prepare("update `plg_domain_product` set `json`=? where `id`=?", [json_encode($buyers, 256), $productId]);
}

/**
 * 根据 URL 更新购买者列表（ymgm 结算用）
 */
function domain_product_add_buyer_by_url($url, $user)
{
	global $DB;
	$row = $DB->get_row_prepare("SELECT * FROM plg_domain_product WHERE url=? limit 1", [$url]);
	if (!$row) return false;
	$buyers = json_decode($row['json'], true);
	if (!is_array($buyers)) $buyers = [];
	if (!in_array($user, $buyers)) $buyers[] = $user;
	return $DB->query_prepare("update `plg_domain_product` set `json`=? where `url`=?", [json_encode($buyers, 256), $url]);
}
