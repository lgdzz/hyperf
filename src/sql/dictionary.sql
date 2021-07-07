CREATE TABLE `dictionary` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '标题',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '描述',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '值',
  `path` varchar(255) NOT NULL DEFAULT '',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `type_id` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='数据字典';