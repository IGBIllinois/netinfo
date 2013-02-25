-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 30, 2012 at 11:46 AM
-- Server version: 5.5.28-0ubuntu0.12.04.3
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `netinfo`
--

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE IF NOT EXISTS `data` (
  `aname` varchar(20) DEFAULT NULL,
  `ipnumber` varchar(15) NOT NULL DEFAULT '',
  `hardware` varchar(12) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `room` varchar(5) DEFAULT NULL,
  `os` varchar(20) DEFAULT NULL,
  `description` varchar(30) DEFAULT NULL,
  `backpass` varchar(10) DEFAULT NULL,
  `alias` text,
  `modifiedby` varchar(8) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `property_tag` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ipnumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `macwatch`
--

CREATE TABLE IF NOT EXISTS `macwatch` (
  `switch` varchar(255) NOT NULL,
  `port` varchar(10) NOT NULL,
  `mac` varchar(12) NOT NULL,
  `vendor` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `switch` (`switch`,`port`,`mac`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `namespace`
--

CREATE TABLE IF NOT EXISTS `namespace` (
  `aname` varchar(20) DEFAULT NULL,
  `ipnumber` varchar(15) NOT NULL DEFAULT '',
  `hardware` varchar(12) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `room` varchar(5) DEFAULT NULL,
  `os` varchar(20) DEFAULT NULL,
  `description` varchar(30) DEFAULT NULL,
  `backpass` varchar(10) DEFAULT NULL,
  `alias` text,
  `modifiedby` varchar(8) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `property_tag` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ipnumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `operating_systems`
--

CREATE TABLE IF NOT EXISTS `operating_systems` (
  `os` varchar(20) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Table structure for table `retro_pass`
--

CREATE TABLE IF NOT EXISTS `retro_pass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `retro_pass` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE switches (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(50),
        time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
);

CREATE TABLE locations (
	id INT NOT NULL AUTO_INCREMENT,
	switch_id INT REFERENCES switches(id),
	port VARCHAR(10),
	jack VARCHAR(10),
	room VARCHAR(10),
	building VARCHAR(10),
	PRIMARY KEY(id)
);
