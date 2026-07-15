# MNBT 插件系统（V1.81 P0 + P1）

PHP 业务插件目录。与宝塔侧 `plugins/mnbt_connector`（Python 节点代理）**无关**。

## 目录结构

```
app_plugins/{slug}/
  plugin.json      # 必填
  bootstrap.php    # 必填：注册钩子/菜单/AJAX
  install.sql      # 可选
  uninstall.sql    # 可选
  admin/           # 可选页面
  user/
  assets/
```

## 启用方式

1. 将插件文件夹放入 `app_plugins/`
2. 后台 → 系统管理 → **插件管理** → 安装 → 启用
3. 刷新后台后，侧栏会出现「插件」分组（若插件注册了菜单）

表 `MN_plugin` / `MN_plugin_option` 会在首次加载时自动创建；完整安装见 `install/install.sql`。

## 核心 API

| API | 说明 |
|-----|------|
| `mnbt_plugin_register($id, $meta)` | 注册元信息 |
| `mnbt_add_action` / `mnbt_do_action` | 事件 |
| `mnbt_add_filter` / `mnbt_apply_filters` | 过滤 |
| `mnbt_register_ajax('user'\|'admin', $gn, $cb)` | AJAX，`gn` 建议 `p_{slug}_*` |
| `mnbt_register_page` / `mnbt_register_menu` | 页面与菜单 |
| `mnbt_plugin_option_get/set` | 插件配置 |
| `mnbt_plugin_path` / `mnbt_plugin_url` | 路径与资源 URL |
| `mnbt_register_widget` | 仪表盘小部件 |
| `mnbt_register_settings_tab` | 插件管理页快捷设置入口 |
| `mnbt_http_get` / `mnbt_http_post` | 安全出站 HTTP（禁内网默认） |

## 钩子

| 钩子 | 时机 |
|------|------|
| `boot` | 插件加载后 |
| `init.admin` / `init.user` | 已登录 |
| `host.created` / `host.paused` / `host.unpaused` / `host.renewed` / `host.deleted` | 主机生命周期（后台 + 外部 API） |
| `order.paid` | 支付成功（return/notify） |
| `cron` | `jk_monitor.php` 末尾 |
| `menu.admin` / `menu.user` | 菜单 filter |
| `dashboard.admin.widgets` / `dashboard.user.widgets` | 仪表盘小部件 filter |
| `settings.admin.tabs` | 设置页签 filter |

## 页面入口

- 管理：`admin/plugin.php?p={slug}&page={page}`
- 用户：`user/plugin.php?p={slug}&page={page}`
- 管理 AJAX：`POST admin/ajax.php`，`gn=...`
- 用户 AJAX：`POST user/ajax.php`，`gn=...`

## 示例

| 插件 | 说明 |
|------|------|
| `hello_demo/` | 菜单、配置、Ping、主机/订单事件日志 |
| `webhook_notify/` | Webhook 推送（URL + 可选 HMAC 签名 + 事件开关 + 测试） |

### Webhook 验签示例（接收端）

```
签名头: X-MNBT-Signature: sha256=<hex>
校验: hash_hmac('sha256', raw_body, secret) === hex
```

## 安全约定

- 仅管理员可安装/启用/卸载
- 插件文件必须在 `app_plugins/{slug}/` 内，页面路径禁止越界
- 不要覆盖核心文件；配置只用 option 表，勿改 `MN_config` 结构
- 第一期不支持任意上传 PHP；本地拷贝目录即可
