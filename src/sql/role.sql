CREATE TABLE `role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `org_id` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `master` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-启用|2-禁用',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-系统默认',
  `remark` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `rules` varchar(2000) NOT NULL DEFAULT '',
  `half_rules` varchar(2000) NOT NULL DEFAULT '',
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='角色';

=@=@=@=@=@=

INSERT INTO `role` (`id`, `pid`, `org_id`, `path`, `name`, `master`, `status`, `is_system`, `remark`, `rules`, `created_at`, `updated_at`)
VALUES
	(1, 0, 1, '0,1', '系统管理员', 1, 1, 1, NULL, '[]', '[]', '2021-07-06 12:00:00', '2021-07-06 12:00:00');
