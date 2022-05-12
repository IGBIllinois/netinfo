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
  `switch_id` INT REFERENCES switches(switch_id),
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
  UNIQUE KEY `switch_port` (`switch_id`,`port`)
)\p;

CREATE TABLE `macwatch` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `switch_id` INT REFERENCES switches(switch_id),
  `switch` VARCHAR(255) NOT NULL,
  `port` VARCHAR(30) DEFAULT NULL,
  `mac` VARCHAR(12) NOT NULL,
  `vendor` VARCHAR(50) NOT NULL,
  `vlans` VARCHAR(128) DEFAULT NULL,
  `date` TIMESTAMP(3) NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `switch` UNIQUE (`switch_id`,`port`,`mac`),
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

CREATE TABLE `switches` (
  `switch_id` INT NOT NULL AUTO_INCREMENT,
  `hostname` VARCHAR(255) NOT NULL DEFAULT '',
  `enabled` BOOLEAN DEFAULT 1,
  `date` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`switch_id`),
  KEY `hostname` (`hostname`)
)\p;

CREATE TABLE `vlans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `vlan` INT NOT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `macwatch_latest`
AS SELECT m1.switch_id AS switch_id,
        switches.hostname AS switch,
        m1.port AS port,
        m1.mac AS mac,
        m1.vendor AS vendor,
        m1.vlans AS vlans,
        a.jack_number AS jack_number,
        a.room AS room,
        a.building AS building,
        m1.date AS date FROM macwatch m1
		LEFT JOIN switches ON switches.switch_id=m1.switch_id
        LEFT JOIN (SELECT locations.port,locations.jack_number,locations.room,locations.building,switches.hostname,locations.switch_id FROM locations
        LEFT JOIN switches ON switches.switch_id=locations.switch_id) AS a
        ON (a.port=m1.port AND a.switch_id=m1.switch_id)
        WHERE m1.date = (SELECT MAX(macwatch.date) FROM macwatch WHERE macwatch.mac = m1.mac)\p;

