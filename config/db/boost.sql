CREATE DATABASE  IF NOT EXISTS gosearch DEFAULT CHARSET=utf8;
use gosearch;
CREATE TABLE IF NOT EXISTS `:TABLE:` (
  `docid` varchar(20) NOT NULL DEFAULT '',
  `query` varchar(128) NOT NULL DEFAULT  '',
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:ready, 1:updated, 2:badword',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`docid`, `query`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
