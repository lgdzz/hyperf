CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('master','branch') DEFAULT 'branch' COMMENT '账号类型',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `realname` varchar(15) DEFAULT NULL COMMENT '真实姓名',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` char(5) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-启用|2-禁用',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `last_ip` varchar(15) DEFAULT NULL COMMENT '最后登录IP',
  `last_time` char(19) DEFAULT NULL COMMENT '最后登录时间',
  `from_channel` enum('组织') NOT NULL DEFAULT '组织' COMMENT '来源渠道',
  `from_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
  `extends` text NOT NULL,
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  `deleted_at` char(19) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `phone` (`phone`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户';

=@=@=@=@=@=

INSERT INTO `user` (`id`, `type`, `phone`, `username`, `password`, `salt`, `status`, `is_system`, `remark`, `last_ip`, `last_time`, `extends`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(1, 'master', '', 'root', 'bdbb649454039ddd2c56083cd0f32771', 'TO1br', 1, 0, '', NULL, NULL, '[]', '2021-09-22 12:00:00', '2021-09-22 12:00:00', NULL);