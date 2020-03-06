CREATE TABLE `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `alt_names` varchar(255) DEFAULT NULL,
  `serial` int(11) DEFAULT NULL,
  `header` text DEFAULT NULL,
  `options` text DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) 

CREATE TABLE `macwatch` (
  `switch` varchar(255) NOT NULL,
  `port` varchar(30) NOT NULL,
  `mac` varchar(12) NOT NULL,
  `vendor` varchar(50) NOT NULL,
  `vlans` varchar(128) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`switch`,`port`,`mac`),
  KEY `mac` (`mac`)
)


CREATE TABLE `macwatch_ignored_ports` (
        `switch_hostname` varchar(64) NOT NULL DEFAULT '',
        `portname` varchar(128) NOT NULL DEFAULT '',
        PRIMARY KEY (`switch_hostname`,`portname`),
        CONSTRAINT `macwatch_ignored_ports_ibfk_1` FOREIGN KEY (`switch_hostname`) REFERENCES `macwatch_switches` (`hostname`) ON DELETE CASCADE ON UPDATE CASCADE
)

CREATE TABLE `switches` (
        `switch_id` INT NOT NULL AUTOINCREMENT,
        `hostname` varchar(64) NOT NULL DEFAULT '',
        PRIMARY KEY (`switch_id`)
)

CREATE TABLE `macwatch_vlans` (
        `vlan_id` INT NOT NULL AUTOINCREMENT,
        `vlan` int(11) unsigned NOT NULL,
        `description` text,
        PRIMARY KEY (`vlan_id`)
)

CREATE TABLE `namespace` (
  `aname` varchar(20) DEFAULT NULL,
  `ipnumber` varchar(15) NOT NULL DEFAULT '',
  `hardware` varchar(12) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `room` varchar(5) DEFAULT NULL,
  `os` varchar(20) DEFAULT NULL,
  `description` varchar(30) DEFAULT NULL,
  `alias` text DEFAULT NULL,
  `modifiedby` varchar(8) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `property_tag` varchar(10) DEFAULT NULL,
  `serial_number` varchar(50) DEFAULT NULL,
  `network_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ipnumber`),
  KEY `hardware` (`hardware`)
)

CREATE TABLE `networks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `network` varchar(255) DEFAULT NULL,
  `netmask` varchar(255) DEFAULT NULL,
  `vlan` int(11) DEFAULT NULL,
  `options` text DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
)

CREATE TABLE `operating_systems` (
  `os` varchar(20) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
)

CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `macwatch_latest` 
AS SELECT `m1`.`switch` AS `switch`,
	`m1`.`port` AS `port`,
	`m1`.`mac` AS `mac`,
	`m1`.`vendor` AS `vendor`,
	`m1`.`vlans` AS `vlans`,
	`m1`.`date` AS `date` FROM `macwatch` `m1` 
	WHERE `m1`.`date` = (SELECT MAX(`netinfo`.`macwatch`.`date`) FROM `macwatch` WHERE `netinfo`.`macwatch`.`mac` = `m1`.`mac`);


