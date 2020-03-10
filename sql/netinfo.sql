/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table domains
# ------------------------------------------------------------

CREATE TABLE `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `alt_names` varchar(255) DEFAULT NULL,
  `serial` int(11) DEFAULT 1,
  `header` text DEFAULT NULL,
  `options` text DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table macwatch
# ------------------------------------------------------------

CREATE TABLE `macwatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `switch` varchar(255) CHARACTER SET latin1 NOT NULL,
  `port` varchar(30) DEFAULT NULL,
  `mac` varchar(12) CHARACTER SET latin1 NOT NULL,
  `vendor` varchar(50) CHARACTER SET latin1 NOT NULL,
  `vlans` varchar(128) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `switch` (`switch`,`port`,`mac`),
  KEY `mac` (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ignored_ports
# ------------------------------------------------------------

CREATE TABLE `ignored_ports` (
  `ignored_ports_id` int(11) NOT NULL AUTO_INCREMENT,
  `switch_hostname` varchar(64) NOT NULL DEFAULT '',
  `portname` varchar(128) NOT NULL DEFAULT '',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ignored_ports_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table switches
# ------------------------------------------------------------

CREATE TABLE `switches` (
  `switch_id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) DEFAULT 1,
  `type` enum('building','server','auxiliary','other') DEFAULT 'other',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`switch_id`),
  KEY `hostname` (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table networks
# ------------------------------------------------------------

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table namespace
# ------------------------------------------------------------

CREATE TABLE `namespace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aname` varchar(64) DEFAULT 'spare',
  `ipnumber` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `hardware` varchar(12) CHARACTER SET latin1 DEFAULT NULL,
  `name` varchar(40) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `room` varchar(5) CHARACTER SET latin1 DEFAULT NULL,
  `os` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `description` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `alias` text CHARACTER SET latin1 DEFAULT NULL,
  `modifiedby` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `property_tag` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `serial_number` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `network_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ipnumber` (`ipnumber`),
  KEY `hardware` (`hardware`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table operating_systems
# ------------------------------------------------------------

CREATE TABLE `operating_systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `os` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table locations
# ------------------------------------------------------------

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `switch_id` int(11) DEFAULT NULL,
  `port` varchar(30) DEFAULT NULL,
  `jack_number` varchar(8) DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `building` varchar(4) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `switch_id` (`switch_id`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table portconfig
# ------------------------------------------------------------

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



# Dump of table ports
# ------------------------------------------------------------

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



# Dump of table portstatus
# ------------------------------------------------------------

CREATE TABLE `portstatus` (
  `switchstack` varchar(32) NOT NULL DEFAULT '',
  `descriptor` varchar(64) NOT NULL DEFAULT '0',
  `adminStatus` tinyint(1) DEFAULT NULL,
  `operStatus` tinyint(1) DEFAULT NULL,
  `lastUpdateTime` datetime NOT NULL,
  PRIMARY KEY (`switchstack`,`descriptor`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table vlans
# ------------------------------------------------------------

CREATE TABLE `vlans` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE ALGORITHM=MERGE DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `macwatch_latest`
AS SELECT
   `m1`.`switch` AS `switch`,
   `m1`.`port` AS `port`,
   `m1`.`mac` AS `mac`,
   `m1`.`vendor` AS `vendor`,
   `m1`.`vlans` AS `vlans`,
   `a`.`jack_number` AS `jack_number`,
   `a`.`room` AS `room`,
   `a`.`building` AS `building`,
   `m1`.`date` AS `date`
FROM (`netinfo`.`macwatch` `m1` left join (select `netinfo`.`locations`.`port` AS `port`,`netinfo`.`locations`.`jack_number` AS `jack_number`,`netinfo`.`locations`.`room` AS `room`,`netinfo`.`locations`.`building` AS `building`,`netinfo`.`switches`.`hostname` AS `hostname` from (`netinfo`.`locations` left join `netinfo`.`switches` on(`netinfo`.`switches`.`switch_id` = `netinfo`.`locations`.`switch_id`))) `a` on(`a`.`port` = `m1`.`port` and `a`.`hostname` = `m1`.`switch`)) where `m1`.`date` = (select max(`netinfo`.`macwatch`.`date`) from `netinfo`.`macwatch` where `netinfo`.`macwatch`.`mac` = `m1`.`mac`);

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
