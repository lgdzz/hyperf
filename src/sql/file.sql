CREATE TABLE `file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `c_id` int(11) unsigned NOT NULL DEFAULT '0',
  `channel` varchar(15) NOT NULL COMMENT '渠道',
  `from_id` varchar(30) NOT NULL DEFAULT '' COMMENT '来源ID',
  `type` tinyint(1) unsigned NOT NULL COMMENT '1-图片|2-视频|3-音频|4-文件',
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '资源名称',
  `filepath` varchar(255) NOT NULL DEFAULT '' COMMENT '资源路径',
  `filesize` int(11) unsigned NOT NULL COMMENT '文件大小',
  `mimetype` varchar(50) NOT NULL DEFAULT '',
  `extension` varchar(10) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `extra` varchar(500) NOT NULL DEFAULT '' COMMENT '附件属性',
  `created_at` char(19) NOT NULL DEFAULT '' COMMENT '上传时间',
  `updated_at` char(19) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `channel` (`channel`),
  KEY `from_id` (`from_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COMMENT='文件';