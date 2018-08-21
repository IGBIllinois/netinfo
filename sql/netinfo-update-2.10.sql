CREATE TABLE `macwatch_ignored_ports` (
  `switch_hostname` varchar(64) NOT NULL DEFAULT '',
  `portname` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`switch_hostname`,`portname`),
  CONSTRAINT `macwatch_ignored_ports_ibfk_1` FOREIGN KEY (`switch_hostname`) REFERENCES `macwatch_switches` (`hostname`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `macwatch_switches` (
  `hostname` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `macwatch_vlans` (
  `vlan` int(11) unsigned NOT NULL,
  `description` text,
  PRIMARY KEY (`vlan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE VIEW `macwatch_latest`
AS SELECT
   `m1`.`switch` AS `switch`,
   `m1`.`port` AS `port`,
   `m1`.`mac` AS `mac`,
   `m1`.`vendor` AS `vendor`,(select max(`macwatch`.`date`)
FROM `macwatch` where (`macwatch`.`mac` = `m1`.`mac`)) AS `date` from `macwatch` `m1` group by `m1`.`mac`;

ALTER TABLE `macwatch` 
DROP KEY `switch`,
ADD PRIMARY KEY `PRIMARY` (`switch`, `port`, `mac`),
ADD KEY `mac` (`mac`);