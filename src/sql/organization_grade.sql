CREATE TABLE `organization_grade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `len` int(11) unsigned NOT NULL,
  `code` varchar(100) DEFAULT NULL COMMENT '组织类型编码',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '组织类型名称',
  `name_en` varchar(100) NOT NULL DEFAULT '' COMMENT '英文名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `sort` int(11) unsigned NOT NULL DEFAULT '255' COMMENT '排序，升序',
  `admin_role_id` int(11) unsigned NOT NULL,
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  `deleted_at` char(19) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `path` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='组织类型';

=@=@=@=@=@=

INSERT INTO `organization_grade` (`id`, `pid`, `path`, `len`, `name`, `name_en`, `description`, `sort`, `admin_role_id`, `created_at`, `updated_at`)
VALUES
	(1, 0, '0,1', 1, '系统', 'system', '', 0, 1, '2021-08-01 12:00:00', '2021-08-01 12:00:00');
