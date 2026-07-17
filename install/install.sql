DROP TABLE IF EXISTS `MN_config`;
CREATE TABLE `MN_config` (
  `id` int(1) NOT NULL AUTO_INCREMENT,   -- 数据库表ID
  `user` varchar(250) NOT NULL,  -- 账号
  `pwd` varchar(250) NOT NULL,  -- 密码
  `gg` mediumtext NOT NULL,    -- 网站公告
  `name` text NOT NULL,     -- 控制面板名称
  `yzm` text NOT NULL,   -- 后台验证码
  `yzme` text NOT NULL,   -- 控制面板验证码
  `wzqk` text NOT NULL,  -- 网站是否开启
  `auther` text NOT NULL,  -- 控制面板logo修改时间
  `kzmbqk` text NOT NULL,   -- 控制面板是否开启
  `apiqk` varchar(20) NOT NULL,  -- API接口是否开启
  `api` varchar(50) NOT NULL,     -- API统一1级密钥
  `qqh` varchar(50) NOT NULL,       -- 站长QQ号
  `date` varchar(50) NOT NULL,     -- 网站建成日期
  `hxw` varchar(250) NOT NULL,     -- FTP操作面板
  `hxe` varchar(250) NOT NULL,     -- 易支付地址
  `hxr` varchar(250) NOT NULL,     -- 易支付ID
  `hxt` varchar(250) NOT NULL,     -- 易支付key
  `hxy` varchar(50) NOT NULL,     -- 后续
  `hxu` text NOT NULL,     -- 开通网站后的默认PHP版本
  `hxi` text NOT NULL,     -- Linux建站的目录
  `hxo` text NOT NULL,     -- Windows建站的目录
  `hxp` text NOT NULL,     -- 控制面板显示版权
  `hxa` text NOT NULL,     -- 后续....
  `hxs` text NOT NULL,     -- 后续....
  `hxd` text NOT NULL,     -- 后续....
  `pay_methods` text NOT NULL,  -- V1.81 P3: 已启用的付款方式配置（JSON）
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `MN_config`(`id`, `user`, `pwd`, `gg`, `name`, `yzm`,`yzme`,`wzqk`,`auther`,`kzmbqk`, `apiqk`,`api`,`qqh`,`date`,`hxw`, `hxe`, `hxr`, `hxt`, `hxy`, `hxu`,`hxi`,`hxo`,`hxp`,`hxa`, `hxs`,`hxd`, `pay_methods`) VALUES
('1', 'admin', '123456', '', '', 'true', 'false', '', '', 'true', '', '', '', '', 'mnftp', '', '', '', '', '56', '/www/wwwroot', 'D:/wwwroot', "<a href='./'>Copyright ©梦奈云 2023</a>", '', '', '', '');



DROP TABLE IF EXISTS `MN_log`;
CREATE TABLE `MN_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,		-- 数据库表ID，日志功能的停用不代表以后不会开启
  `czuser` varchar(250) NOT NULL,			-- 操作用户
  `date` varchar(250) NOT NULL,				-- 操作时间
  `lx` varchar(250) NOT NULL,				-- 操作类型
  `lr` varchar(50) NOT NULL,				-- 操作内容
  `ip` text NOT NULL,					    -- 客户端IP
  `qk` text NOT NULL,					   -- 操作情况
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_bt`;
CREATE TABLE `MN_bt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,		-- 数据库表ID
  `btip` varchar(250) NOT NULL,					-- 宝塔IP
  `btdk` varchar(250) NOT NULL,					-- 宝塔的端口
  `btmy` varchar(250) NOT NULL,					-- 宝塔的密钥
  `date` varchar(50) NOT NULL,					-- 添加时间
  `ktmy` text NOT NULL,							-- 调用时的密钥
  `qmk` text NOT NULL,							-- 二级验证
  `btdh` varchar(250) NOT NULL,					-- 宝塔开通代号
  `btos` INT(10) NOT NULL DEFAULT '1',			-- 宝塔的操作系统(1为Linux,2为Windows)
  `als` varchar(200) NOT NULL,					-- 自定义域名解析地址
  `ftpdz` varchar(50) NOT NULL,					-- 自定义FTP地址
  `ptl` varchar(50) NOT NULL,					-- 是否开启安全访问(true和false)
  `qk` varchar(50) NOT NULL,					-- 目前宝塔情况
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_zj`;
CREATE TABLE `MN_zj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,				-- 数据库表ID
  `ssbt` varchar(250) NOT NULL,						-- 所属宝塔
  `user` varchar(250) NOT NULL,						-- 控制面板账号
  `pass` varchar(50) NOT NULL,						-- 控制面板密码
  `sqluser` text NOT NULL,								-- 数据库账号
  `sqlpass` text NOT NULL,								-- 数据密码
  `sqldz` varchar(50) NOT NULL,						-- 网站名
  `data` varchar(50) NOT NULL,						-- 开通时间
  `datae` varchar(50) NOT NULL,						-- 到期时间
  `qk` varchar(50) NOT NULL,						-- 目前状态
  `btid` varchar(50) NOT NULL,						-- 宝塔内网站id
  `ftpid` varchar(50) NOT NULL,						-- FTP的id
  `ymbds` varchar(50) NOT NULL,						-- 域名最大绑定数
  `hxa` varchar(50) NOT NULL,						-- 网页空间(max最大，dq当前)
  `hxb` varchar(50) NOT NULL,						-- 数据库空间(max最大，dq当前)
  `hxc` varchar(50) NOT NULL,						-- 产品类型（1为CDN2为主机）
  `hxd` varchar(50) NOT NULL,						-- SQLID
  `llmax` text NOT NULL,						-- 最大流量(max最大，dq当前)
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_bs`;			-- 一键部署的可用网站列表
CREATE TABLE `MN_bs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,				-- 数据库表ID
  `name` varchar(250) NOT NULL,						-- 程序名称
  `jc` varchar(250) NOT NULL,						-- 程序介绍
  `src` text NOT NULL,								-- 程序图标位置
  `date` text NOT NULL,								-- 添加时间
  `cxwz` text NOT NULL,								-- 程序位置
  `sxpz` varchar(500) NOT NULL,						-- 所需最低配置(存入数组 网页空间和SQL空间)
  `tj` text NOT NULL,						        -- 使用本程序的主机和总人数
  `jg` varchar(50) NOT NULL,						-- 程序价格
  `inp` text NOT NULL,								-- 用户部署时填写的表单(存储为json)
  `pz` text NOT NULL,								-- 搭建程序时的程序配置(存储为json)
  `alet` text NOT NULL,								-- 部署完成后的弹窗提示
  `qk` varchar(50) NOT NULL,						-- 状态(上架和下架)
  `hxa` varchar(50) NOT NULL,						-- 后续...
  `hxb` varchar(50) NOT NULL,						-- 后续...
  `hxc` varchar(50) NOT NULL,						-- 后续...
  `hxd` varchar(50) NOT NULL,						-- 后续...
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `MN_bs` (`id`, `name`, `jc`, `src`, `date`, `cxwz`, `sxpz`, `tj`, `jg`, `inp`, `pz`, `qk`, `hxa`, `hxb`, `hxc`, `hxd`) VALUES
(1, '彩虹外链网盘', '彩虹外链网盘也称彩虹个人网盘，可以用来保存各种文件<br/>部署完成后默认账号为admin默认密码为123456<br/>默认后台为：域名/admin', '["../filecx/b7b04562/tp/0.png","../filecx/b7b04562/tp/1.png","../filecx/b7b04562/tp/2.png","../filecx/b7b04562/tp/3.png"]', '2022-01-29 21:27:06', '../filecx/b7b04562/cxym.zip', '["10","5"]', '[]', '0.01', '[]', '{"1":{"cz":"xjwj","name":"install.lock","ml":"/install/"},"2":{"cz":"setwj","name":"config.php","ml":"/","nr":"../filecx/b7b04562/setwj/2.setwj"},"3":{"cz":"drsql","name":"install.sql","ml":"/install"},"4":{"cz":"drsql","name":"update.sql","ml":"/install"}}', 'true', '', '', '', '');

DROP TABLE IF EXISTS `MN_ym`;
CREATE TABLE `MN_ym` (
  `id` int(11) NOT NULL AUTO_INCREMENT,		-- 数据库表ID
  `url` varchar(128) NOT NULL,					-- 域名
  `btdh` varchar(250) NOT NULL,					-- 对应的宝塔
  `jg` varchar(250) NOT NULL,					-- 解析价格
  `date` varchar(50) NOT NULL,					-- 添加时间
  `js` varchar(50) NOT NULL,					-- 域名介绍
  `json` text NOT NULL,							-- 绑定了该域名的主机(json)
  `qk` varchar(50) NOT NULL,					-- 目前域名情况
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_dd`;               -- 支付订单
CREATE TABLE `MN_dd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,		-- 数据库表ID
  `cs` varchar(1000) NOT NULL,				-- 传入参数(json)
  `date` varchar(250) NOT NULL,				-- 操作时间
  `zffs` varchar(250) NOT NULL,				-- 支付方式
  `je` varchar(250) NOT NULL,				-- 支付金额
  `ddh` varchar(250) NOT NULL,				-- 订单号
  `lx` varchar(250) NOT NULL,				-- 功能类型
  `qk` varchar(50) NOT NULL,				-- 支付情况
  `ip` text NOT NULL,					    -- 发起者的IP
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_monitor_task`;       -- 用户端URL监控任务
CREATE TABLE `MN_monitor_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `task_type` varchar(30) NOT NULL DEFAULT 'url',
  `url` varchar(1000) NOT NULL,
  `resource_type` varchar(30) NOT NULL DEFAULT '',
  `resource_threshold` int(11) NOT NULL DEFAULT 80,
  `method` varchar(10) NOT NULL DEFAULT 'GET',
  `interval_seconds` int(11) NOT NULL DEFAULT 60,
  `timeout_seconds` int(11) NOT NULL DEFAULT 10,
  `status_rule` varchar(30) NOT NULL DEFAULT 'eq',
  `status_value` varchar(100) NOT NULL DEFAULT '200',
  `content_rule` varchar(30) NOT NULL DEFAULT 'none',
  `content_value` text,
  `fail_threshold` int(11) NOT NULL DEFAULT 1,
  `notify_email` varchar(10) NOT NULL DEFAULT 'true',
  `enabled` varchar(10) NOT NULL DEFAULT 'true',
  `last_run` varchar(50) DEFAULT NULL,
  `next_run` varchar(50) DEFAULT NULL,
  `last_status` varchar(20) DEFAULT NULL,
  `last_code` int(11) DEFAULT NULL,
  `last_error` text,
  `fail_count` int(11) NOT NULL DEFAULT 0,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user`),
  KEY `idx_next_run` (`enabled`,`next_run`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_monitor_log`;        -- 用户端URL监控检测日志
CREATE TABLE `MN_monitor_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user` varchar(250) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `http_code` int(11) DEFAULT NULL,
  `response_time` int(11) DEFAULT NULL,
  `check_status` varchar(20) NOT NULL,
  `error_message` text,
  `response_excerpt` text,
  `notified` varchar(10) NOT NULL DEFAULT 'false',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_notice_log`;         -- 用户端通知日志
CREATE TABLE `MN_notice_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(250) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'info',
  `is_read` varchar(10) NOT NULL DEFAULT 'false',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user`,`is_read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_node`;              -- MNBT节点插件
CREATE TABLE `MN_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bt_id` int(11) NOT NULL DEFAULT 0,
  `node_id` varchar(64) NOT NULL,
  `node_name` varchar(100) NOT NULL DEFAULT '',
  `node_secret` varchar(128) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'offline',
  `enabled` varchar(10) NOT NULL DEFAULT 'true',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `version` varchar(30) NOT NULL DEFAULT '',
  `capabilities` text,
  `last_heartbeat` varchar(50) DEFAULT NULL,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_node_id` (`node_id`),
  KEY `idx_bt_id` (`bt_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_node_task`;         -- MNBT节点任务
CREATE TABLE `MN_node_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(64) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `action` varchar(50) NOT NULL,
  `payload` mediumtext,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `result` mediumtext,
  `error` text,
  `created_at` varchar(50) NOT NULL,
  `pulled_at` varchar(50) DEFAULT NULL,
  `finished_at` varchar(50) DEFAULT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_task_id` (`task_id`),
  KEY `idx_node_status` (`node_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_node_nonce`;        -- MNBT节点防重放
CREATE TABLE `MN_node_nonce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` varchar(64) NOT NULL,
  `nonce` varchar(80) NOT NULL,
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_node_nonce` (`node_id`,`nonce`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_forbidden_scan`;    -- 违禁词扫描任务
CREATE TABLE `MN_forbidden_scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(64) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `site` varchar(250) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `scanned_files` int(11) NOT NULL DEFAULT 0,
  `scanned_rows` int(11) NOT NULL DEFAULT 0,
  `matches_count` int(11) NOT NULL DEFAULT 0,
  `summary` mediumtext,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_node` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_forbidden_match`;   -- 违禁词命中记录
CREATE TABLE `MN_forbidden_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` varchar(64) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `site` varchar(250) NOT NULL DEFAULT '',
  `match_type` varchar(30) NOT NULL DEFAULT 'file',
  `target` varchar(1000) NOT NULL DEFAULT '',
  `line_no` int(11) NOT NULL DEFAULT 0,
  `keyword` varchar(250) NOT NULL DEFAULT '',
  `excerpt` text,
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task` (`task_id`),
  KEY `idx_node` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE MN_bt SET ftpdz= 'false';

ALTER TABLE `MN_config` ADD `mailhost` VARCHAR(50) NULL DEFAULT NULL COMMENT '邮箱服务器地址';
ALTER TABLE `MN_config` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL COMMENT '邮箱账号';
ALTER TABLE `MN_config` ADD `mailpassword` VARCHAR(50) NULL DEFAULT NULL COMMENT '邮箱密码';
ALTER TABLE `MN_config` ADD `mailport` VARCHAR(20) NOT NULL DEFAULT '465' COMMENT '邮箱端口';
ALTER TABLE `MN_config` ADD `ymjkkg` VARCHAR(20) NOT NULL DEFAULT 'false' COMMENT '域名监控开关';
ALTER TABLE `MN_config` ADD `mtyxfskg` VARCHAR(20) NOT NULL DEFAULT 'false' COMMENT '每天邮箱发送开关';
ALTER TABLE `MN_config` ADD `ymjktsyz` VARCHAR(20) NOT NULL DEFAULT '7' COMMENT '域名监控天数阈值';
ALTER TABLE `MN_config` ADD `wjjkkg` VARCHAR(20) NOT NULL DEFAULT 'false' COMMENT '文件监控开关';
ALTER TABLE `MN_config` ADD `mtwjfskg` VARCHAR(50) NOT NULL DEFAULT 'false' COMMENT '每天文件发送邮箱开关';
ALTER TABLE `MN_config` ADD `wjjktsyz` VARCHAR(20) NOT NULL DEFAULT '7' COMMENT '文件监控天数阈值';
ALTER TABLE `MN_zj` ADD `backup` VARCHAR(50) NOT NULL DEFAULT '{\"max\":\"3\",\"dq\":0}' COMMENT '备份SQL个数';
ALTER TABLE `MN_zj` ADD `mailuser` VARCHAR(50) NULL DEFAULT NULL COMMENT '主机使用的用户邮箱';
ALTER TABLE `MN_config` ADD `optionzc` VARCHAR(20) NOT NULL DEFAULT 'stop' COMMENT '选择暂停主机还是删除主机';
ALTER TABLE `MN_config` ADD `zjyxbd` VARCHAR(20) NOT NULL DEFAULT 'true' COMMENT '主机邮箱绑定';

-- 违禁词扫描配置字段
ALTER TABLE `MN_config` ADD `wjsckg` VARCHAR(20) NOT NULL DEFAULT 'false' COMMENT '违禁词扫描开关';
ALTER TABLE `MN_config` ADD `wjsccnr` TEXT NULL DEFAULT NULL COMMENT '违禁词内容(每行一个)';
ALTER TABLE `MN_config` ADD `wjsckgqbfx` VARCHAR(10) NOT NULL DEFAULT 'true' COMMENT '是否只扫描变更文件';
ALTER TABLE `MN_config` ADD `wjscml` VARCHAR(500) NOT NULL DEFAULT '/www/wwwroot' COMMENT '扫描目录';
ALTER TABLE `MN_config` ADD `wjstqml` TEXT NULL DEFAULT NULL COMMENT '跳过目录(逗号分隔)';
ALTER TABLE `MN_config` ADD `wjstqhz` TEXT NULL DEFAULT NULL COMMENT '跳过后缀(逗号分隔)';
ALTER TABLE `MN_config` ADD `wjscdzmax` INT(11) NOT NULL DEFAULT 5242880 COMMENT '单文件最大大小(字节),默认5MB';
ALTER TABLE `MN_config` ADD `wjscdhmax` INT(11) NOT NULL DEFAULT 1000 COMMENT '单次扫描最大命中数';
ALTER TABLE `MN_config` ADD `wjscqzcs` VARCHAR(50) NOT NULL DEFAULT '0 3 * * *' COMMENT '定时全量复扫 cron 表达式(默认每天凌晨3点)';
ALTER TABLE `MN_config` ADD `wjscqzcskg` VARCHAR(20) NOT NULL DEFAULT 'true' COMMENT '定时全量复扫开关';

-- V1.81 插件系统
DROP TABLE IF EXISTS `MN_plugin`;
CREATE TABLE `MN_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) NOT NULL,
  `name` varchar(120) NOT NULL DEFAULT '',
  `version` varchar(32) NOT NULL DEFAULT '',
  `enabled` varchar(10) NOT NULL DEFAULT 'false',
  `config_json` mediumtext,
  `installed_at` varchar(50) NOT NULL DEFAULT '',
  `updated_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MN_plugin_option`;
CREATE TABLE `MN_plugin_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_slug` varchar(64) NOT NULL,
  `k` varchar(120) NOT NULL,
  `v` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plugin_k` (`plugin_slug`,`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- V1.82 权限管理系统
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

-- V1.83 独立用户体系
DROP TABLE IF EXISTS `MN_user_group`;
CREATE TABLE `MN_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '用户组名称',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '折扣率',
  `rules` text COMMENT '权限规则JSON',
  `status` varchar(10) NOT NULL DEFAULT 'true',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `MN_user_group` (`id`, `name`, `discount`, `rules`, `status`, `created_at`) VALUES
(1, '普通用户', '0.00', '[]', 'true', '2026-07-16 22:50:00'),
(2, 'VIP用户', '10.00', '[]', 'true', '2026-07-16 22:50:00'),
(3, '代理', '20.00', '[]', 'true', '2026-07-16 22:50:00');

DROP TABLE IF EXISTS `MN_user`;
CREATE TABLE `MN_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(MD5+盐)',
  `salt` varchar(32) NOT NULL COMMENT '密码盐',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `group_id` int(11) NOT NULL DEFAULT '1' COMMENT '用户组ID',
  `status` varchar(10) NOT NULL DEFAULT 'true' COMMENT '状态',
  `reg_date` varchar(50) NOT NULL COMMENT '注册时间',
  `login_date` varchar(50) DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `reg_ip` varchar(50) DEFAULT NULL COMMENT '注册IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `MN_user` (`id`, `username`, `password`, `salt`, `email`, `money`, `score`, `group_id`, `status`, `reg_date`, `reg_ip`) VALUES
(1, 'admin', MD5('123456'), '', 'admin@example.com', '0.00', '0', '1', 'true', '2026-07-16 22:50:00', '');

DROP TABLE IF EXISTS `MN_money_log`;
CREATE TABLE `MN_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `money` decimal(10,2) NOT NULL COMMENT '变动金额',
  `before` decimal(10,2) NOT NULL COMMENT '变动前余额',
  `after` decimal(10,2) NOT NULL COMMENT '变动后余额',
  `memo` varchar(255) DEFAULT NULL COMMENT '备注',
  `date` varchar(50) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
