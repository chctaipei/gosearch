CREATE DATABASE  IF NOT EXISTS gosearch DEFAULT CHARSET=utf8;
use gosearch;
CREATE TABLE IF NOT EXISTS `:TABLE:` (
  `id` int(11) NOT NULL DEFAULT '0',
  `query` varchar(128) DEFAULT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `matches` int(11) unsigned NOT NULL DEFAULT '0',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `query_UNIQUE` (`query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
