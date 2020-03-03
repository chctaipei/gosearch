CREATE DATABASE  IF NOT EXISTS gosearch DEFAULT CHARSET=utf8;
use gosearch;
CREATE TABLE IF NOT EXISTS `cronjob` (
  `jobid` int(11) NOT NULL AUTO_INCREMENT,
  `project` VARCHAR(32) DEFAULT '' COMMENT 'project name',
  `type` VARCHAR(32) DEFAULT '' COMMENT 'index type',
  `task` VARCHAR(32) NOT NULL COMMENT '工作名稱',
  `data` JSON COMMENT '[source, parameter] ',
  `cronstring` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'cron format: * * * * * *',
  `status` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0:waiting, 1:running, 2:inactive',
  `active` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0:no, 1:yes',
  `nextExecTime` timestamp NULL DEFAULT NULL,
  `lastExecTime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`jobid`),
  KEY `project_type_idx` (`project`,`type`),
  KEY `next_job_idx` (`status`,`nextExecTime`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
