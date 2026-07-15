# 主题说明

## 目录结构

```
templates/
  active_user_theme          # 当前用户端主题名
  active_admin_theme         # 当前管理端主题名
  default/
    theme.json
    user/                    # 用户控制面板视图
      head.php
      login.php
      index.php
      ...
    admin/                   # 管理后台视图
      head.php
      login.php
      index.php
      set.php
      ...
  your_theme/                # 自定义主题（可只覆盖部分页面）
    theme.json
    user/
    admin/
```

## 切换主题

1. **后台界面（推荐）**  
   系统管理 → 前端模板 → 选择用户端/管理端主题 → 保存

2. **文件方式**  
   修改 `active_user_theme` / `active_admin_theme` 内容为目录名

3. **数据库（可选）**  
   若 `MN_config` 存在 `usertheme` / `admintheme` 字段则优先读取

优先级：数据库字段 > active_* 文件 > `default`

## 开发约定

- 路由仍是 `user/*.php` / `admin/*.php`，控制器鉴权取数后 `mnbt_render` / `mnbt_admin_render`
- AJAX 路径保持 `./ajax.php` 不变
- 公共资源：`../imsetes/...` 或 `mnbt_asset_url()`
- 主题资源：`mnbt_theme_url('assets/x.css', 'user'|'admin')`
- 缺页自动回退到 `default` 同名视图
