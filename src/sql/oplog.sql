CREATE TABLE `oplog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `method` varchar(10) NOT NULL DEFAULT '' COMMENT '请求方式',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '日志说明',
  `operator` varchar(100) NOT NULL DEFAULT '' COMMENT '操作人',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路由地址',
  `body` text NOT NULL COMMENT '携带参数',
  `created_at` char(19) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志';