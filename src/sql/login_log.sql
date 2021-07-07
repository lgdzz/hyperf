CREATE TABLE `login_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `ip_isp` varchar(50) NOT NULL DEFAULT '',
  `login_ip` varchar(15) NOT NULL DEFAULT '',
  `login_time` char(19) NOT NULL DEFAULT '',
  `channel` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='登录日志';