CREATE TABLE `rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '规则名称',
  `type` enum('page','api') NOT NULL DEFAULT 'api' COMMENT '1-页面|2-操作',
  `method` enum('GET','POST','PUT','DELETE') DEFAULT NULL,
  `permission_id` varchar(30) DEFAULT NULL,
  `operation` varchar(30) DEFAULT NULL,
  `service_router` varchar(30) DEFAULT NULL,
  `client_router` varchar(30) DEFAULT NULL,
  `client_route_name` varchar(30) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort` tinyint(255) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COMMENT='权限规则';