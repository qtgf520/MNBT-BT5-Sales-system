-- MNBT 权限管理系统 v3.1 增量SQL
-- 执行方式：mysql -u root -p 数据库名 < update/update_v181_permission.sql

DROP TABLE IF EXISTS `mn_permissions`;
CREATE TABLE `mn_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mn_permissions` (`name`, `code`, `description`, `module`, `sort_order`) VALUES
('域名绑定', 'domain_binding', '绑定/解绑域名', '网站配置', 1),
('修改密码', 'change_password', '修改主机FTP/SQL密码', '网站配置', 2),
('PHP版本切换', 'php_version', '切换PHP版本', '网站配置', 3),
('密码访问设置', 'password_access', '设置目录密码访问', '网站配置', 4),
('默认文档设置', 'default_document', '修改默认首页文档', '网站配置', 5),
('运行目录设置', 'running_directory', '修改网站运行目录', '网站配置', 6),
('伪静态设置', 'pseudo_static', '配置URL伪静态规则', '网站配置', 7),
('SSL证书管理', 'ssl_management', '管理SSL/HTTPS证书', '网站配置', 8),
('防盗链设置', 'hotlink_protection', '配置防盗链规则', '网站配置', 9),
('在线文件管理', 'file_manager', '在线文件浏览器/编辑器', '数据管理', 10),
('数据库管理面板', 'database_panel', 'phpMyAdmin数据库管理', '数据管理', 11),
('数据库备份管理', 'database_backup', '备份/恢复数据库', '数据管理', 12),
('数据库权限修改', 'database_permission', '修改数据库用户权限', '数据管理', 13),
('一键部署', 'one_click_deploy', '一键部署网站程序', '网站管理', 14),
('FTP服务', 'ftp_service', '控制FTP信息显示/FTP账号密码可见', '服务控制', 15),
('数据库服务', 'database_service', '控制数据库信息显示/数据库账号密码可见', '服务控制', 16);

DROP TABLE IF EXISTS `mn_user_permissions`;
CREATE TABLE `mn_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_code` varchar(50) NOT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permission` (`user_id`,`permission_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 逻辑: __perm_init__标记=已初始化 | 无标记=初次默认全开 | 有标记无记录=全关