CREATE TABLE `organization` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `pids` varchar(255) NOT NULL DEFAULT '' COMMENT '上级ID集合',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '组织结构',
  `path_name` varchar(255) NOT NULL DEFAULT '' COMMENT '组织结构名称',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组织名称',
  `name_en` varchar(100) NOT NULL DEFAULT '' COMMENT '英文名称',
  `grade_id` int(11) unsigned NOT NULL COMMENT '组织类型',
  `len` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '组织长度',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-启用|2禁用',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序，升序',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  `deleted_at` char(19) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='组织架构';

INSERT INTO `organization` (`id`, `pid`, `pids`, `path`, `path_name`, `name`, `name_en`, `grade_id`, `len`, `status`, `sort`, `description`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1, 0, '[]', '1', '系统', '系统', 'system', 1, 1, 1, 0, NULL, '2021-08-01 12:00:00', '2021-08-01 12:00:00', NULL);
