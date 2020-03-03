DROP TABLE IF EXISTS `QueryLog`;
CREATE TABLE `QueryLog` (
      `id` int(11) NOT NULL DEFAULT '0',
      `query` varchar(128) DEFAULT NULL,
      `count` int(11) unsigned NOT NULL DEFAULT '0',
      `matches` int(11) unsigned NOT NULL DEFAULT '0',
      `createTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `updateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      UNIQUE KEY `query_UNIQUE` (`query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
