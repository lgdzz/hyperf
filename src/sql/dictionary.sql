CREATE TABLE `dictionary` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '字典索引',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '字典名称',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '值',
  `value_type` enum('string','int') NOT NULL DEFAULT 'string' COMMENT '数据类型',
  `path` varchar(255) NOT NULL DEFAULT '',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COMMENT='数据字典';