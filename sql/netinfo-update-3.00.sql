ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domains ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE macwatch ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE namespace ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE networks ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE operating_systems ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE ignored_ports (
        ignored_ports_id INT NOT NULL AUTO_INCREMENT,
        switch_hostname varchar(64) NOT NULL DEFAULT '',
        port VARCHAR(30) NOT NULL DEFAULT '',
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (ignored_ports_id)
);

CREATE TABLE switches (
        switch_id INT NOT NULL AUTO_INCREMENT,
        hostname VARCHAR(255) NOT NULL DEFAULT '',
        enabled BOOLEAN DEFAULT 1,
        type ENUM('building','server','auxiliary','other') DEFAULT 'other',
        date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (switch_id),
    KEY `hostname` (`hostname`)
);

CREATE TABLE locations (
        id INT NOT NULL AUTO_INCREMENT,
        switch_id INT REFERENCES switches(switch_id),
        port VARCHAR(30),
        jack_number VARCHAR(8),
        room VARCHAR(20),
        building VARCHAR(4),
        date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY `switch_id` (`switch_id`,`port`)
);

ALTER TABLE operating_systems 
	MODIFY column id INT NOT NULL AUTO_INCREMENT FIRST,
	ADD COLUMN date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE namespace
	DROP PRIMARY KEY,
	ADD COLUMN id INT NOT NULL AUTO_INCREMENT FIRST,
	MODIFY aname VARCHAR(64) DEFAULT 'spare',
	ADD PRIMARY KEY(id);

UPDATE namespace SET modified=modified,property_tag=UPPER(property_tag),serial_number=UPPER(serial_number);

ALTER TABLE domains
	MODIFY serial INT DEFAULT 1;


ALTER TABLE macwatch
	ADD COLUMN id INT NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (id),
	ADD COLUMN `vlans` varchar(128) NULL DEFAULT NULL AFTER `vendor`, 
	MODIFY `port` VARCHAR(30);

CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `macwatch_latest`
AS SELECT m1.switch AS switch,
        m1.port AS port,
        m1.mac AS mac,
        m1.vendor AS vendor,
        m1.vlans AS vlans,
	a.jack_number AS jack_number,
	a.room AS room,
	a.building AS building,
        m1.date AS date FROM macwatch m1
	LEFT JOIN (SELECT locations.port,locations.jack_number,locations.room,locations.building,switches.hostname FROM locations 
	LEFT JOIN switches ON switches.switch_id=locations.switch_id) AS a
	ON (a.port=m1.port AND a.hostname=m1.switch)
        WHERE m1.date = (SELECT MAX(macwatch.date) FROM macwatch WHERE macwatch.mac = m1.mac);

CREATE TABLE `portconfig` (
    `switchstack` varchar(32) NOT NULL DEFAULT '',
    `descriptor` varchar(64) NOT NULL DEFAULT '',
    `mode` varchar(16) DEFAULT 'access',
    `vlan` int(11) DEFAULT 1,
    `printerfirewall` tinyint(1) NOT NULL DEFAULT 0,
    `allowedvlan` varchar(64) DEFAULT NULL,
    `lastUpdateTime` datetime NOT NULL,
    PRIMARY KEY (`switchstack`,`descriptor`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `ports` (
    `switchstack` varchar(32) NOT NULL DEFAULT '',
    `descriptor` varchar(64) NOT NULL DEFAULT '',
    `snmpindex` int(11) NOT NULL,
    `desc1` int(11) DEFAULT NULL,
    `desc2` int(11) DEFAULT NULL,
    `desc3` int(11) DEFAULT NULL,
    `name` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`switchstack`,`descriptor`),
    KEY `descriptor` (`descriptor`),
    KEY `desc1` (`desc1`,`desc2`,`desc3`),
    KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `portstatus` (
    `switchstack` varchar(32) NOT NULL DEFAULT '',
    `descriptor` varchar(64) NOT NULL DEFAULT '0',
    `adminStatus` tinyint(1) DEFAULT NULL,
    `operStatus` tinyint(1) DEFAULT NULL,
    `lastUpdateTime` datetime NOT NULL,
    PRIMARY KEY (`switchstack`,`descriptor`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `vlans` (
    `id` int(11) unsigned NOT NULL,
    `name` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;