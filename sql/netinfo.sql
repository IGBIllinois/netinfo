CREATE TABLE `domains` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) DEFAULT NULL,
  `alt_names` VARCHAR(255) DEFAULT NULL,
  `serial` INT DEFAULT 1,
  `header` TEXT DEFAULT NULL,
  `options` TEXT DEFAULT NULL,
  `enabled` BOOLEAN DEFAULT 1,
  `last_updated` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `ignored_ports` (
  `ignored_ports_id` INT NOT NULL AUTO_INCREMENT,
  `switch_hostname` VARCHAR(64) NOT NULL DEFAULT '',
  `port` VARCHAR(30) NOT NULL DEFAULT '',
  `date` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ignored_ports_id`)
)\p;

CREATE TABLE `locations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `switch_id` INT REFERENCES switches(switch_id),
  `port` VARCHAR(30) DEFAULT NULL,
  `jack_number` VARCHAR(8) DEFAULT NULL,
  `room` VARCHAR(20) DEFAULT NULL,
  `building` VARCHAR(4) DEFAULT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `switch_id` (`switch_id`,`port`)
)\p;

CREATE TABLE `macwatch` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `switch` VARCHAR(255) NOT NULL,
  `port` VARCHAR(30) DEFAULT NULL,
  `mac` VARCHAR(12) NOT NULL,
  `vendor` VARCHAR(50) NOT NULL,
  `vlans` VARCHAR(128) DEFAULT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `switch` (`switch`,`port`,`mac`),
  KEY `mac` (`mac`)
)\p;

CREATE TABLE `namespace` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `aname` VARCHAR(64) DEFAULT 'spare',
  `ipnumber` VARCHAR(15) NOT NULL DEFAULT '',
  `hardware` VARCHAR(12) DEFAULT NULL,
  `name` VARCHAR(40) DEFAULT NULL,
  `email` VARCHAR(30) DEFAULT NULL,
  `room` VARCHAR(5) DEFAULT NULL,
  `os` VARCHAR(20) DEFAULT NULL,
  `description` VARCHAR(30) DEFAULT NULL,
  `alias` TEXT DEFAULT NULL,
  `modifiedby` VARCHAR(8) DEFAULT NULL,
  `modified` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `property_tag` VARCHAR(10) DEFAULT NULL,
  `serial_number` VARCHAR(50) DEFAULT NULL,
  `network_id` INT REFERENCES networks(id),
  PRIMARY KEY (`id`),
  KEY `ipnumber` (`ipnumber`),
  KEY `hardware` (`hardware`)
)\p;

CREATE TABLE `networks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `domain_id` INT REFERENCES domains(id),
  `name` VARCHAR(255) DEFAULT NULL,
  `network` VARCHAR(255) DEFAULT NULL,
  `netmask` VARCHAR(255) DEFAULT NULL,
  `vlan` INT DEFAULT NULL,
  `options` TEXT DEFAULT NULL,
  `enabled` BOOLEAN DEFAULT 1,
  `last_updated` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `operating_systems` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `os` VARCHAR(20) DEFAULT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `portconfig` (
  `switchstack` VARCHAR(32) NOT NULL DEFAULT '',
  `descriptor` VARCHAR(64) NOT NULL DEFAULT '',
  `mode` VARCHAR(16) DEFAULT 'access',
  `vlan` INT DEFAULT 1,
  `printerfirewall` BOOLEAN NOT NULL DEFAULT 0,
  `allowedvlan` VARCHAR(64) DEFAULT NULL,
  `lastUpdateTime` DATETIME NOT NULL,
  PRIMARY KEY (`switchstack`,`descriptor`)
)\p;


CREATE TABLE `ports` (
  `switchstack` varchar(32) NOT NULL DEFAULT '',
  `descriptor` varchar(64) NOT NULL DEFAULT '',
  `snmpindex` INT NOT NULL,
  `desc1` INT DEFAULT NULL,
  `desc2` INT DEFAULT NULL,
  `desc3` INT DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`switchstack`,`descriptor`),
  KEY `descriptor` (`descriptor`),
  KEY `desc1` (`desc1`,`desc2`,`desc3`),
  KEY `name` (`name`)
)\p;

CREATE TABLE `portstatus` (
  `switchstack` VARCHAR(32) NOT NULL DEFAULT '',
  `descriptor` VARCHAR(64) NOT NULL DEFAULT '0',
  `adminStatus` BOOLEAN DEFAULT NULL,
  `operStatus` BOOLEAN DEFAULT NULL,
  `lastUpdateTime` DATETIME NOT NULL,
  PRIMARY KEY (`switchstack`,`descriptor`)
);


CREATE TABLE `switches` (
  `switch_id` INT NOT NULL AUTO_INCREMENT,
  `hostname` VARCHAR(255) NOT NULL DEFAULT '',
  `enabled` BOOLEAN DEFAULT 1,
  `type` ENUM('building','server','auxiliary','other') DEFAULT 'other',
  `date` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`switch_id`),
  KEY `hostname` (`hostname`)
)\p;

CREATE TABLE `vlans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)\p;

CREATE VIEW `macwatch_latest` AS select `m1`.`switch` AS `switch`,`m1`.`port` AS `port`,`m1`.`mac` AS `mac`,`m1`.`vendor` AS `vendor`,`m1`.`vlans` AS `vlans`,`a`.`jack_number` AS `jack_number`,`a`.`room` AS `room`,`a`.`building` AS `building`,`m1`.`date` AS `date` from (`netinfo`.`macwatch` `m1` left join (select `netinfo`.`locations`.`port` AS `port`,`netinfo`.`locations`.`jack_number` AS `jack_number`,`netinfo`.`locations`.`room` AS `room`,`netinfo`.`locations`.`building` AS `building`,`netinfo`.`switches`.`hostname` AS `hostname` from (`netinfo`.`locations` left join `netinfo`.`switches` on(`netinfo`.`switches`.`switch_id` = `netinfo`.`locations`.`switch_id`))) `a` on(`a`.`port` = `m1`.`port` and `a`.`hostname` = `m1`.`switch`)) where `m1`.`date` = (select max(`netinfo`.`macwatch`.`date`) from `netinfo`.`macwatch` where `netinfo`.`macwatch`.`mac` = `m1`.`mac`)\p;

