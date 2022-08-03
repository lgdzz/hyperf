CREATE TABLE `organization` (
                                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
                                `pids` varchar(255) NOT NULL DEFAULT '' COMMENT '上级ID集合',
                                `path` varchar(255) NOT NULL DEFAULT '' COMMENT '组织结构',
                                `path_name` varchar(255) NOT NULL DEFAULT '' COMMENT '组织结构名称',
                                `code` varchar(100) NOT NULL DEFAULT '' COMMENT '组织编码',
                                `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组织名称',
                                `name_en` varchar(100) NOT NULL DEFAULT '' COMMENT '英文名称',
                                `full_name` varchar(100) NOT NULL DEFAULT '' COMMENT '完整名称',
                                `grade_id` int(11) unsigned NOT NULL COMMENT '组织类型',
                                `len` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '组织长度',
                                `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-启用|2禁用',
                                `sort` int(11) unsigned NOT NULL DEFAULT '255' COMMENT '排序，升序',
                                `description` varchar(255) DEFAULT NULL COMMENT '描述',
                                `extends` text NOT NULL COMMENT '扩展对象',
                                `contact_name` varchar(15) DEFAULT NULL COMMENT '组织联系人',
                                `contact_tel` varchar(30) DEFAULT NULL COMMENT '组织联系电话',
                                `contact_address` varchar(100) DEFAULT NULL COMMENT '组织联系地址',
                                `province_code` varchar(2) DEFAULT NULL COMMENT '省编码',
                                `city_code` varchar(2) DEFAULT NULL COMMENT '市编码',
                                `county_code` varchar(2) DEFAULT NULL COMMENT '区县编码',
                                `province` varchar(30) DEFAULT NULL COMMENT '省',
                                `city` varchar(30) DEFAULT NULL COMMENT '市',
                                `county` varchar(30) DEFAULT NULL COMMENT '区',
                                `created_at` char(19) NOT NULL DEFAULT '',
                                `updated_at` char(19) NOT NULL DEFAULT '',
                                `deleted_at` char(19) DEFAULT NULL,
                                PRIMARY KEY (`id`),
                                KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='组织架构';

=@=@=@=@=@=

INSERT INTO `organization` (`id`, `pid`, `pids`, `path`, `path_name`, `code`, `name`, `name_en`, `full_name`, `grade_id`, `len`, `status`, `sort`, `description`, `extends`, `contact_name`, `contact_tel`, `contact_address`, `province_code`, `city_code`, `county_code`, `province`, `city`, `county`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1, 0, '[]', '1', '系统', '09', '系统', 'system', '', 1, 1, 1, 0, NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-08-01 12:00:00', '2022-06-27 15:06:54', NULL);

