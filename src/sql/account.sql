CREATE TABLE `account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `org_id` int(11) unsigned NOT NULL COMMENT '组织ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `role_id` int(11) unsigned NOT NULL COMMENT '角色ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-启用|2-禁用',
  `extends` text COMMENT '附加属性，对象',
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  `deleted_at` char(19) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `org_id` (`org_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

=@=@=@=@=@=

INSERT INTO `account` (`id`, `org_id`, `user_id`, `role_id`, `status`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1, 1, 1, 1, 1, '2021-08-01 12:00:00', '2021-08-01 12:00:00', NULL);
