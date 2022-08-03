CREATE TABLE `setting` (
                           `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                           `org_id` int(11) unsigned NOT NULL,
                           `account_id` int(11) unsigned NOT NULL,
                           `name` varchar(30) NOT NULL DEFAULT '',
                           `value` longtext NOT NULL,
                           `created_at` char(19) NOT NULL DEFAULT '',
                           `updated_at` char(19) NOT NULL DEFAULT '',
                           PRIMARY KEY (`id`),
                           KEY `org_id` (`org_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='配置';