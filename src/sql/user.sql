CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('master','branch') DEFAULT 'branch',
  `role_id` int(11) unsigned NOT NULL,
  `phone` char(11) NOT NULL DEFAULT '',
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `salt` char(5) NOT NULL DEFAULT '',
  `job_number` varchar(30) DEFAULT NULL COMMENT '工号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-启用|2-禁用',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `last_ip` varchar(15) DEFAULT NULL,
  `last_time` int(11) DEFAULT NULL,
  `created_at` char(19) NOT NULL DEFAULT '',
  `updated_at` char(19) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户';