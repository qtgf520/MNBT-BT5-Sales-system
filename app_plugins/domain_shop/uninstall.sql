-- domain_shop 插件卸载脚本
-- 注意：卸载会丢失所有域名商品与 DNS 记录数据
DROP TABLE IF EXISTS `plg_domain_product`;
DROP TABLE IF EXISTS `plg_dns_provider`;
DROP TABLE IF EXISTS `plg_dns_record`;
