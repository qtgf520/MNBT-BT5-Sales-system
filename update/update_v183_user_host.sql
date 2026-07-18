-- V1.83 用户-主机关联表
DROP TABLE IF EXISTS `MN_user_host`;
CREATE TABLE `MN_user_host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'MN_user.id',
  `host_id` int(11) NOT NULL COMMENT 'MN_zj.id',
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_host` (`user_id`,`host_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;