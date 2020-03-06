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

CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `macwatch_latest`
AS SELECT `m1`.`switch` AS `switch`,
	`m1`.`port` AS `port`,
	`m1`.`mac` AS `mac`,
	`m1`.`vendor` AS `vendor`,
	`m1`.`vlans` AS `vlans`,
	`m1`.`date` AS `date` FROM `macwatch` `m1` 
	WHERE `m1`.`date` = (SELECT MAX(`netinfo`.`macwatch`.`date`) FROM `macwatch` WHERE `netinfo`.`macwatch`.`mac` = `m1`.`mac`);

ALTER TABLE `macwatch` DROP KEY `switch`, ADD PRIMARY KEY `PRIMARY` (`switch`, `port`, `mac`), ADD KEY `mac` (`mac`), ADD COLUMN `vlans` varchar(128) NULL DEFAULT NULL AFTER `vendor`, MODIFY `port` VARCHAR(30);


