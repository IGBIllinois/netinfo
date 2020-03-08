CREATE TABLE domains (
	id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(255) DEFAULT NULL,
	alt_names VARCHAR(255) DEFAULT NULL,
	serial INT DEFAULT DEFAULT 1,
	header TEXT DEFAULT NULL,
	options TEXT DEFAULT NULL,
	enabled BOOLEAN DEFAULT 1,
	last_updated TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	PRIMARY KEY (id)
);

CREATE TABLE macwatch (
	id INT NOT NULL AUTO_INCREMENT,
	switch VARCHAR(255) NOT NULL,
	port VARCHAR(30) NOT NULL,
	mac VARCHAR(12) NOT NULL,
	vendor VARCHAR(50) NOT NULL,
	vlans VARCHAR(128) DEFAULT NULL,
	date TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	PRIMARY KEY (id),
	KEY (switch,port,mac),
	KEY mac (mac)
);


CREATE TABLE ignored_ports (
	ignored_ports_id INT NOT NULL AUTO_INCREMENT,
        switch_hostname varchar(64) NOT NULL DEFAULT '',
        portname VARCHAR(128) NOT NULL DEFAULT '',
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (ignored_ports_id)
);

CREATE TABLE switches (
	switch_id INT NOT NULL AUTO_INCREMENT,
	hostname VARCHAR(255) NOT NULL DEFAULT '',
	enabled BOOLEAN DEFAULT 1,
        PRIMARY KEY (switch_id)
);

CREATE TABLE networks (
        id INT NOT NULL AUTO_INCREMENT,
        domain_id INT DEFAULT NULL,
        name VARCHAR(255) DEFAULT NULL,
        network VARCHAR(255) DEFAULT NULL,
        netmask VARCHAR(255) DEFAULT NULL,
        vlan INT DEFAULT NULL,
        options TEXT DEFAULT NULL,
        enabled BOOLEAN DEFAULT 1,
        last_updated TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (id)
);

CREATE TABLE namespace (
	id INT NOT NULL AUTO_INCREMENT,
	aname VARCHAR(64) DEFAULT 'spare',
	ipnumber VARCHAR(15) NOT NULL DEFAULT '',
	hardware VARCHAR(12) DEFAULT NULL,
	name VARCHAR(40) DEFAULT NULL,
	email VARCHAR(30) DEFAULT NULL,
	room VARCHAR(5) DEFAULT NULL,
	os VARCHAR(20) DEFAULT NULL,
	description VARCHAR(30) DEFAULT NULL,
	alias TEXT DEFAULT NULL,
	modifiedby VARCHAR(8) DEFAULT NULL,
	modified TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	property_tag VARCHAR(10) DEFAULT NULL,
	serial_number VARCHAR(50) DEFAULT NULL,
	network_id INT REFERENCES networks(id),
	PRIMARY KEY (id),
	KEY `hardware` (hardware,ipnumber)
);

CREATE TABLE operating_systems (
	id INT NOT NULL AUTO_INCREMENT,
	os VARCHAR(20) DEFAULT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE locations (
	id INT NOT NULL AUTO_INCREMENT,
	switch_id INT REFERENCES switch(switch_id),
	port VARCHAR(30),
	jack_number VARCHAR(8),
	room VARCHAR(20),
	building VARCHAR(4),
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id)
);

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

