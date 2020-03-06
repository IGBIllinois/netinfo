ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domains ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE macwatch ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE namespace ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE networks ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE operating_systems ENGINE = InnoDB, CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE ignored_ports (
        ignored_ports_id INT NOT NULL AUTO_INCREMENT,
        switch_hostname varchar(64) NOT NULL DEFAULT '',
        portname VARCHAR(128) NOT NULL DEFAULT '',
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (ignored_ports_id)
);

CREATE TABLE switches (
        switch_id INT NOT NULL AUTO_INCREMENT,
        hostname VARCHAR(64) NOT NULL DEFAULT '',
        enabled BOOLEAN DEFAULT 1,
        PRIMARY KEY (switch_id)
);

CREATE TABLE vlans (
        vlan_id INT NOT NULL AUTO_INCREMENT,
        vlan INT UNSIGNED NOT NULL,
        description TEXT,
        enabled BOOLEAN DEFAULT 1,
        PRIMARY KEY (vlan_id)
);

ALTER TABLE operating_systems 
	MODIFY column id INT NOT NULL AUTO_INCREMENT FIRST,
	ADD COLUMN date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE namespace
	DROP PRIMARY KEY,
	ADD COLUMN id INT NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY(id);
CREATE INDEX hardware ON namespace(hardware,ipnumber);


ALTER TABLE macwatch
	ADD COLUMN id INT NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (id),
	ADD KEY `mac` (`mac`), 
	ADD COLUMN `vlans` varchar(128) NULL DEFAULT NULL AFTER `vendor`, 
	MODIFY `port` VARCHAR(30);

CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `macwatch_latest`
AS SELECT m1.switch AS switch,
        m1.port AS port,
        m1.mac AS mac,
        m1.vendor AS vendor,
        m1.vlans AS vlans,
        m1.date AS date FROM macwatch m1
        WHERE m1.date = (SELECT MAX(macwatch.date) FROM macwatch WHERE macwatch.mac = m1.mac);

