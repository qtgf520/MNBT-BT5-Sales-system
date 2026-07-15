# 用户端主题说明

## 目录结构

```
templates/
  active_user_theme          # 当前主题名（无数据库字段时生效）
  default/                   # 默认主题
    theme.json
    user/
      head.php               # 公共 head
      login.php
      index.php
      sy.php
      ...
  your_theme/                # 自定义主题
    theme.json
    user/
      login.php              # 可只覆盖部分页面，其余回退 default
```

## 切换主题

1. 修改 `templates/active_user_theme` 内容为目录名，或
2. 数据库 `MN_config.usertheme` 字段（若已添加）设为主题名

优先级：`MN_config.usertheme` > `active_user_theme` > `default`

## 开发约定

- 路由仍是 `user/*.php`，控制器负责鉴权与取数，视图在主题目录
- 页面内 AJAX 路径保持 `./ajax.php` 等不变
- 静态公共资源仍用 `../imsetes/...` 或 `mnbt_asset_url()`
- 主题自有资源用 `mnbt_theme_url('assets/xxx.css')`
- 缺页自动回退到 `default` 同名视图
