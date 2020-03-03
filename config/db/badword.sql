CREATE DATABASE  IF NOT EXISTS gosearch DEFAULT CHARSET=utf8;
use gosearch;
CREATE TABLE IF NOT EXISTS `:TABLE:` (
  `string` varchar(128) NOT NULL DEFAULT '',
  `type` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0:keyword, 1:regex',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

