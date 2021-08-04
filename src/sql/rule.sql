CREATE TABLE `rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '规则名称',
  `type` enum('page','api') NOT NULL DEFAULT 'api' COMMENT '1-页面|2-操作',
  `method` enum('GET','POST','PUT','DELETE') DEFAULT NULL COMMENT '接口请求方式',
  `permission_id` varchar(30) DEFAULT NULL,
  `operation` varchar(30) DEFAULT NULL COMMENT '操作标识',
  `service_router` varchar(30) DEFAULT NULL COMMENT '接口路由',
  `client_router` varchar(30) DEFAULT NULL COMMENT '客户端路由',
  `client_route_name` varchar(30) DEFAULT NULL COMMENT '客户端路由名称',
  `client_route_alias` varchar(30) DEFAULT NULL COMMENT '客户端路由别名',
  `icon` varchar(50) DEFAULT NULL,
  `sort` tinyint(255) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='权限规则';