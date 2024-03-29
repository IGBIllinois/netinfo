ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domains ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE macwatch ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE namespace ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE networks ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE operating_systems ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE ignored_ports (
        ignored_ports_id INT NOT NULL AUTO_INCREMENT,
	`switch_id` INT REFERENCES switches(switch_id),
        port VARCHAR(30) NOT NULL DEFAULT '',
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (ignored_ports_id)
);

CREATE TABLE switches (
        switch_id INT NOT NULL AUTO_INCREMENT,
        hostname VARCHAR(255) NOT NULL DEFAULT '',
        enabled BOOLEAN DEFAULT 1,
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
        UNIQUE KEY `switch_port` (`switch_id`,`port`)
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
	ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT FIRST,
	ADD COLUMN `switch_id` INT REFERENCES switches(switch_id) AFTER `id`,
	ADD PRIMARY KEY (id),
	ADD COLUMN `vlans` varchar(128) NULL DEFAULT NULL AFTER `vendor`, 
	MODIFY `port` VARCHAR(30),
	MODIFY `date` TIMESTAMP(3) NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	DROP INDEX `switch`,
	ADD CONSTRAINT `switch` UNIQUE (`switch_id`,`port`,`mac`);


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
        WHERE m1.date = (SELECT MAX(macwatch.date) FROM macwatch WHERE macwatch.mac = m1.mac);

CREATE TABLE `vlans` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL DEFAULT '',
    `vlan` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `location_latest`
AS SELECT a.id, 
	a.switch_id, 
	a.port, 
	a.jack_number, 
	a.room, 
	a.building,
	b.mac,
	DATE_FORMAT(b.date,'%Y-%m-%d %l:%i:%s %p') AS last_seen,
	switches.hostname as switch 
	FROM locations a
	LEFT JOIN switches ON switches.switch_id=a.switch_id
	LEFT JOIN (SELECT m1.switch_id,m1.port,m1.mac,m1.date FROM macwatch m1 WHERE m1.date= (SELECT MAX(macwatch.date) FROM macwatch WHERE macwatch.port=m1.port AND macwatch.switch_id=m1.switch_id )) AS b
	ON (b.port=a.port AND b.switch_id=a.switch_id)	
	ORDER BY room ASC;

