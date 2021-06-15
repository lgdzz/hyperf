CREATE TABLE `role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `master` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_disable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-禁用',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-系统默认',
  `remark` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `rules` varchar(2000) NOT NULL DEFAULT '',
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='角色';