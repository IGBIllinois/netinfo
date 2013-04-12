
CREATE TABLE IF NOT EXISTS `data` (
  `aname` varchar(20) DEFAULT NULL,
  `ipnumber` varchar(15) NOT NULL DEFAULT '',
  `hardware` varchar(12) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `room` varchar(5) DEFAULT NULL,
  `os` varchar(20) DEFAULT NULL,
  `description` varchar(30) DEFAULT NULL,
  `backpass` varchar(10) DEFAULT NULL,
  `alias` text,
  `modifiedby` varchar(8) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `property_tag` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ipnumber`)
)


CREATE TABLE IF NOT EXISTS `macwatch` (
  `switch` varchar(255) NOT NULL,
  `port` varchar(10) NOT NULL,
  `mac` varchar(12) NOT NULL,
  `vendor` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `switch` (`switch`,`port`,`mac`)
)


CREATE TABLE IF NOT EXISTS `namespace` (
  `aname` varchar(20) DEFAULT NULL,
  `ipnumber` varchar(15) NOT NULL DEFAULT '',
  `hardware` varchar(12) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `room` varchar(5) DEFAULT NULL,
  `os` varchar(20) DEFAULT NULL,
  `description` varchar(30) DEFAULT NULL,
  `backpass` varchar(10) DEFAULT NULL,
  `alias` text,
  `modifiedby` varchar(8) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `property_tag` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ipnumber`)
)


CREATE TABLE IF NOT EXISTS `operating_systems` (
  `os` varchar(20) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
)



