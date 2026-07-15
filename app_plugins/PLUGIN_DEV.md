# MNBT 插件开发手册

本文面向需要**开发 PHP 业务插件**的开发者。  
先读 [README.md](./README.md) 了解启用方式与目录约定。

> **注意**：本文的插件与宝塔侧 `plugins/mnbt_connector`（Python 节点代理）**无关**。  
> PHP 业务插件只放在站点根目录 `app_plugins/` 下。

---

## 1. 设计原则

1. **不改核心 URL**：业务接口仍走 `user/ajax.php` / `admin/ajax.php`；页面走 `plugin.php?p=slug&page=...`。
2. **插件只注册、不劫持核心 `gn`**：自定义 AJAX 的 `gn` 必须用前缀，建议 `p_{slug}_{action}`。
3. **配置进 option 表**：用 `MN_plugin_option`，不要往 `MN_config` 加列。
4. **文件只在插件目录内**：页面路径禁止跳出 `app_plugins/{slug}/`。
5. **与主题分离**：主题管外观（`templates/`）；插件管业务能力。
6. **升级友好**：自定义代码放在 `app_plugins/`，避免改 `MPHX/`、`user/`、`admin/` 核心文件。

---

## 2. 新建插件（最短路径）

### 步骤 1：创建目录

```text
app_plugins/
└── my_plugin/              # 目录名 = 插件 ID（字母数字下划线横线，≤63）
    ├── plugin.json         # 必填
    ├── bootstrap.php       # 必填
    ├── install.sql         # 可选：建插件自有表
    ├── uninstall.sql       # 可选：卸载时清理
    ├── admin/              # 可选：后台页面
    │   └── index.php
    ├── user/               # 可选：用户端页面
    │   └── index.php
    └── assets/             # 可选：静态资源
```

### 步骤 2：编写 `plugin.json`

```json
{
  "id": "my_plugin",
  "name": "我的插件",
  "version": "1.0.0",
  "author": "YourName",
  "description": "插件一句话说明",
  "requires_mnbt": "1.81",
  "type": ["business"]
}
```

| 字段 | 必填 | 说明 |
|------|------|------|
| `id` | 建议 | 与目录名一致；引擎以**目录名**为准 |
| `name` | 是 | 后台「插件管理」显示名 |
| `version` | 是 | 版本号 |
| `author` | 否 | 作者 |
| `description` | 否 | 简介 |
| `requires_mnbt` | 否 | 文档用最低版本 |
| `type` | 否 | 文档分类，如 `business` / `lifecycle` / `integration` |

### 步骤 3：编写 `bootstrap.php`

```php
<?php
if (!defined('IN_CRONLITE')) {
	exit;
}

mnbt_plugin_register('my_plugin', ['name' => '我的插件']);

// 后台菜单
mnbt_register_menu('admin', [
	'title' => '我的插件',
	'page' => 'index',
	'icon' => 'mdi-puzzle',
	'order' => 20,
	'multitabs' => true,
]);

// 后台页面（相对插件根目录）
mnbt_register_page('admin', 'index', 'admin/index.php', '我的插件');

// 可选：出现在「插件管理」页顶部快捷入口
mnbt_register_settings_tab([
	'title' => '我的插件设置',
	'page' => 'index',
	'order' => 20,
]);

// AJAX：POST admin/ajax.php  gn=p_my_plugin_ping
mnbt_register_ajax('admin', 'p_my_plugin_ping', function () {
	mnbt_plugin_require_admin();
	json_exit_success('pong', ['time' => date('Y-m-d H:i:s')]);
});

// 生命周期钩子
mnbt_add_action('host.created', function ($host, $ctx = []) {
	// $host 为主机行数组；$ctx 含 source 等
});
```

### 步骤 4：后台页面示例 `admin/index.php`

```php
<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4>我的插件</h4></div>
    <div class="card-body">
      <button type="button" class="btn btn-primary" id="btn-ping">Ping</button>
    </div>
  </div>
</div>
<script>
$('#btn-ping').on('click', function () {
  $.post('ajax.php', {gn: 'p_my_plugin_ping'}, function (res) {
    try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
    alert(res.msg || res.code || JSON.stringify(res));
  });
});
</script>
```

### 步骤 5：启用

1. 将目录放到 `app_plugins/my_plugin/`
2. 后台 → 系统管理 → **插件管理** → **安装** → **启用**
3. **整页刷新**后台（侧栏菜单才会出现）

### 步骤 6：自测清单

- [ ] 插件管理中可见、可启用/禁用  
- [ ] 侧栏菜单可打开页面  
- [ ] AJAX 成功返回 JSON  
- [ ] 禁用后接口/菜单失效  
- [ ] 主机开通/删除等钩子（若用了）有预期行为  

---

## 3. 核心 API 详解

引擎文件：`MPHX/plugin.php`（由 `common.php` 在鉴权后启动 `mnbt_plugins_boot()`）。

### 3.1 注册与元信息

| 函数 | 说明 |
|------|------|
| `mnbt_plugin_register($id, $meta)` | 注册元信息（可选，推荐写） |
| `mnbt_plugin_id()` | 当前插件 slug（钩子/AJAX 回调内有效） |
| `mnbt_plugin_path($slug = null)` | 插件绝对路径，末尾带 `/` |
| `mnbt_plugin_url($slug = null, $rel = '')` | 资源 URL，如 `/app_plugins/my_plugin/assets/a.css` |
| `mnbt_plugin_enabled($slug)` | 是否已启用 |

### 3.2 钩子（Action / Filter）

```php
// 监听
mnbt_add_action('host.created', function ($host, $ctx = []) { ... }, 10);
mnbt_add_filter('menu.admin', function ($items) {
	// 可改菜单数组后 return
	return $items;
}, 10);

// 触发（一般由核心调用，插件很少自己 do_action）
mnbt_do_action('my_event', $arg1, $arg2);
$value = mnbt_apply_filters('my_filter', $value, $extra);
```

- 同一钩子可多个回调；`$priority` 数字越小越先执行（默认 `10`）。
- 回调异常会被捕获并写入 PHP 错误日志，不中断主流程。

### 3.3 AJAX

```php
mnbt_register_ajax('admin', 'p_my_plugin_save', function ($egn, $side) {
	mnbt_plugin_require_admin();
	// 读 $_POST，写 option，返回 JSON
	json_exit_success('已保存');
});

mnbt_register_ajax('user', 'p_my_plugin_list', function () {
	mnbt_plugin_require_user();
	// 全局 $yhc 为当前主机用户
	json_exit_success('ok', ['items' => []]);
});
```

| 侧 | 请求 | 鉴权 |
|----|------|------|
| admin | `POST admin/ajax.php`，`gn=...` | 管理员 cookie；回调内再 `mnbt_plugin_require_admin()` |
| user | `POST user/ajax.php`，`gn=...` | 用户已登录；回调内再 `mnbt_plugin_require_user()` |

分发顺序：**插件注册表 → 核心 `api/*.php`**。  
重复 `gn` 后注册失败并写错误日志。

**推荐返回：**

```php
json_exit_success($msg, $extra);  // qk=1
json_exit_error($msg, $extra);    // qk=4
// 或兼容旧式：
json_exit('提示文案');
```

### 3.4 菜单与页面

```php
mnbt_register_menu('admin', [
	'title' => '标题',
	'page' => 'settings',     // 自动生成 url: plugin.php?p=slug&page=settings
	// 或 'url' => 'https://...',
	'icon' => 'mdi-webhook',  // Material Design Icons 类名（不含 mdi 前缀时引擎会加）
	'order' => 20,
	'multitabs' => true,
]);

mnbt_register_page('admin', 'settings', 'admin/settings.php', '页面标题');
mnbt_register_page('user', 'index', 'user/index.php', '用户页');
```

| 入口 | URL |
|------|-----|
| 管理页 | `admin/plugin.php?p={slug}&page={page}` |
| 用户页 | `user/plugin.php?p={slug}&page={page}` |

页面文件内可：

- 管理端：`mnbt_admin_include('head');`
- 用户端：`mnbt_theme_include('head');`（与 default 主题一致时）
- 使用全局 `$DB`、`$conf`、`$yhc`（用户端）、`$islogin` / `$islogins`

### 3.5 配置存储

表：`MN_plugin_option`（`plugin_slug` + `k` + `v`）。

```php
mnbt_plugin_option_set('my_plugin', 'api_key', 'xxx');
$v = mnbt_plugin_option_get('my_plugin', 'api_key', '默认值');
// 数组/对象会自动 JSON 编解码
mnbt_plugin_option_set('my_plugin', 'flags', ['a' => true]);
$all = mnbt_plugin_option_all('my_plugin');
```

### 3.6 仪表盘小部件

```php
mnbt_register_widget('admin', [
	'title' => '统计卡片',
	'order' => 10,
	'class' => 'col-sm-6',
	'callback' => function ($side) {
		echo '<p>内容 HTML</p>';
	},
	// 或 'html' => '<p>静态 HTML</p>',
]);
```

渲染位置：

- 管理首页：`templates/default/admin/sy.php`
- 用户仪表盘：`templates/default/user/sy.php`

### 3.7 设置快捷入口

```php
mnbt_register_settings_tab([
	'title' => 'Webhook 通知',
	'page' => 'settings',
	'order' => 10,
]);
```

显示在后台 **插件管理** 页顶部按钮区。

### 3.8 HTTP 出站

```php
$res = mnbt_http_post('https://example.com/hook', [
	'event' => 'test',
], [
	'timeout' => 10,
	'headers' => ['X-Token: abc'],
	// 'insecure' => true,      // 跳过 SSL 校验（不推荐）
	// 'allow_private' => true, // 允许内网（默认禁止）
]);
// $res = ['ok'=>bool, 'code'=>int, 'body'=>string, 'error'=>string]
```

仅允许 `http://` / `https://`；默认拒绝 localhost / 私网 IP。

### 3.9 日志

```php
mnbt_log($user ?: '系统', '插件-我的插件', '做了某事', '成功', $DB);
```

---

## 4. 钩子一览（核心触发点）

| 钩子 | 类型 | 参数 | 触发位置（约） |
|------|------|------|----------------|
| `boot` | action | — | 全部插件 bootstrap 之后 |
| `init.admin` | action | — | 管理员已登录 |
| `init.user` | action | — | 用户已登录 |
| `host.created` | action | `$host`, `$ctx` | 后台添加主机、外部 API 开通 |
| `host.paused` | action | `$host`, `$ctx` | 后台改状态、API 暂停 |
| `host.unpaused` | action | `$host`, `$ctx` | 后台恢复、API 解除暂停 |
| `host.renewed` | action | `$host`, `$ctx` | 后台改到期、API 续费；`$ctx` 含 `old_date`/`new_date` |
| `host.deleted` | action | `$host`, `$ctx` | 后台删除、API 删除 |
| `order.paid` | action | `$order`, `$ctx` | `user/return_url.php` / `notify_url.php` 支付成功 |
| `cron` | action | `$info` | `jk_monitor.php` 末尾 |
| `menu.admin` / `menu.user` | filter | `$items` | 渲染侧栏插件菜单前 |
| `dashboard.admin.widgets` / `dashboard.user.widgets` | filter | `$items` | 渲染小部件前 |
| `settings.admin.tabs` | filter | `$items` | 插件管理页快捷入口 |

`$ctx` 常见字段：`source` = `admin` | `api` | `return_url` | `notify_url`。

**主机 `$host` 敏感字段**（密码等）可能存在于数组中；对外推送时务必自行脱敏（参见 `webhook_notify`）。

---

## 5. 数据库

### 系统表

| 表 | 用途 |
|----|------|
| `MN_plugin` | 已安装插件：slug、name、version、enabled |
| `MN_plugin_option` | 插件键值配置 |

升级已有站点：执行 `update/update_v181_plugin.sql`，或首次访问时引擎 `CREATE TABLE IF NOT EXISTS`。

### 插件自建表

`install.sql` / `uninstall.sql` 在安装/卸载时执行。建议表名前缀：

```sql
-- install.sql
CREATE TABLE IF NOT EXISTS `plg_my_plugin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

访问数据：

```php
global $DB;
$rows = $DB->get_all_prepare("SELECT * FROM plg_my_plugin_log ORDER BY id DESC LIMIT 20") ?: [];
```

优先使用 `query_prepare` / `get_row_prepare` / `get_all_prepare`，避免拼接 SQL。

---

## 6. 生命周期：安装 / 启用 / 卸载

| 操作 | 行为 |
|------|------|
| 安装 | 写 `MN_plugin` 行；执行 `install.sql` |
| 启用 | `enabled=true`；下次请求加载 `bootstrap.php` |
| 禁用 | `enabled=false`；不再加载 |
| 卸载 | 执行 `uninstall.sql`；删除 `MN_plugin` 与该插件 option；**不删磁盘文件** |

管理 AJAX（核心，勿占用）：

- `plugin_list` / `plugin_install` / `plugin_enable` / `plugin_uninstall`

---

## 7. 与主题 / SPA 的关系

| 前端 | 插件如何扩展 |
|------|----------------|
| **default 管理端** | 菜单 + `plugin.php` 页面 + 小部件（完整支持） |
| **default 用户端** | 侧栏插件菜单 + `user/plugin.php` + 仪表盘小部件 |
| **qingliangyun SPA** | 优先提供 JSON AJAX（`user/ajax.php`）；SPA 原生菜单/路由需另约定（当前未内置协议） |

不要在插件里硬编码 `../imsetes/`；公共资源用 `mnbt_asset_url()`，主题资源用 `mnbt_theme_asset()`。

---

## 8. 完整示例：监听开通并写日志

```php
// app_plugins/open_log/bootstrap.php
<?php
if (!defined('IN_CRONLITE')) exit;

mnbt_plugin_register('open_log', ['name' => '开通日志']);

mnbt_add_action('host.created', function ($host, $ctx = []) {
	$u = is_array($host) ? ($host['user'] ?? '') : '';
	$src = is_array($ctx) ? ($ctx['source'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " open user={$u} source={$src}";
	$log = mnbt_plugin_option_get('open_log', 'lines', []);
	if (!is_array($log)) $log = [];
	array_unshift($log, $line);
	mnbt_plugin_option_set('open_log', 'lines', array_slice($log, 0, 100));
});
```

更完整的可运行示例：

| 目录 | 演示点 |
|------|--------|
| `hello_demo/` | 菜单、配置读写、Ping AJAX、主机/订单事件本地日志 |
| `webhook_notify/` | 设置页、事件开关、HTTP POST、HMAC 签名、投递日志、后台小部件 |

---

## 9. 安全清单

- [ ] 所有写操作校验登录（`mnbt_plugin_require_admin` / `require_user`）
- [ ] 用户输入长度与格式校验；输出 `htmlspecialchars`
- [ ] AJAX `gn` 使用 `p_{slug}_` 前缀，避免与核心冲突
- [ ] 不 `eval`、不远程下载执行 PHP
- [ ] 不写核心目录；不改 `MN_config` 表结构
- [ ] 出站 HTTP 用 `mnbt_http_*`，谨慎开启 `allow_private` / `insecure`
- [ ] 推送外部时脱敏密码、API 密钥
- [ ] 生产环境插件目录权限合理（Web 可执行 PHP，但勿对匿名可写）

---

## 10. 常见问题

### 启用后没有菜单？

整页刷新后台框架（`admin/index.php`）。菜单在框架页渲染，仅刷新 iframe 不够。

### AJAX 返回「系统指令不存在」？

1. 插件是否**已启用**（不是仅安装）  
2. `gn` 是否与 `mnbt_register_ajax` 完全一致  
3. 是否请求了正确侧：`admin/ajax.php` vs `user/ajax.php`

### 页面 404 / 插件页面文件无效？

- `mnbt_register_page` 的文件路径相对于插件根目录  
- 文件必须在 `app_plugins/{slug}/` 内（realpath 校验）

### 钩子不触发？

- 确认插件已启用  
- 确认走了对应代码路径（例如 API 开通才会 `source=api`）  
- 看 `runtime/logs/php-error.log` 是否有插件异常

### 与在线更新冲突？

插件放在 `app_plugins/`，官方更新包应避免覆盖该目录；自定义插件勿改核心文件。

---

## 11. 相关文件索引

| 路径 | 说明 |
|------|------|
| [README.md](./README.md) | 插件目录总览、快速启用 |
| `MPHX/plugin.php` | 引擎实现 |
| `MPHX/common.php` | 启动 `mnbt_plugins_boot()` |
| `admin/plugin.php` | 插件管理 + 插件页面入口 |
| `admin/api/plugin.php` | 安装/启用/卸载 AJAX |
| `user/plugin.php` | 用户端插件页面入口 |
| `user/ajax.php` / `admin/ajax.php` | 插件 AJAX 优先分发 |
| `update/update_v181_plugin.sql` | 已有站点升级表结构 |
| `hello_demo/`、`webhook_notify/` | 官方示例 |

---

## 12. 版本与路线

| 版本 | 能力 |
|------|------|
| V1.81 P0 | 引擎、安装启用、AJAX/菜单/页面、host 钩子、cron、示例 |
| V1.81 P1 | HTTP、widget、settings_tab、order.paid、host.renewed、用户菜单、Webhook 插件 |

后续可能：zip 安装、`gn` 冲突检测 UI、qingliangyun SPA 菜单协议、细粒度能力 ACL。
