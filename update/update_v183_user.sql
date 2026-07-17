-- MNBT V1.83 独立用户体系 增量SQL
-- 执行方式：mysql -u root -p 数据库名 < update/update_v183_user.sql

-- 用户组表
DROP TABLE IF EXISTS `MN_user_group`;
CREATE TABLE `MN_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '用户组名称',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '折扣率(0-100, 0=无折扣)',
  `rules` text COMMENT '权限规则JSON',
  `status` varchar(10) NOT NULL DEFAULT 'true',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `MN_user_group` (`id`, `name`, `discount`, `rules`, `status`, `created_at`) VALUES
(1, '普通用户', '0.00', '[]', 'true', '2026-07-16 22:50:00'),
(2, 'VIP用户', '10.00', '[]', 'true', '2026-07-16 22:50:00'),
(3, '代理', '20.00', '[]', 'true', '2026-07-16 22:50:00');

-- 独立用户表
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

-- 资金流水表
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