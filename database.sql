-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 14, 2011 at 03:50 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `2011_mpesapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `mpesapi_payment`
--

CREATE TABLE IF NOT EXISTS `mpesapi_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `transfer_direction` int(11) NOT NULL,
  `reciept` varchar(10) COLLATE latin1_danish_ci NOT NULL,
  `time` datetime NOT NULL,
  `phonenumber` varchar(45) COLLATE latin1_danish_ci NOT NULL,
  `name` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `account` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `status` int(11) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `post_balance` bigint(20) NOT NULL,
  `note` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reciept_UNIQUE` (`reciept`,`type`),
  KEY `type_index` (`type`),
  KEY `name_index` (`name`),
  KEY `phone_index` (`phonenumber`),
  KEY `time_index` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_danish_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mpesapi_payment`
--


-- --------------------------------------------------------

--
-- Table structure for table `mpesapi_setting`
--

CREATE TABLE IF NOT EXISTS `mpesapi_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `name` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `value_string` varchar(255) COLLATE latin1_danish_ci DEFAULT NULL,
  `value_date` datetime DEFAULT NULL,
  `value_int` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name_index` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_danish_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mpesapi_setting`
--

INSERT INTO `mpesapi_setting` (`id`, `type`, `name`, `value_string`, `value_date`, `value_int`) VALUES
(1, 2, 'LastSync', NULL, '2011-01-01 00:00:01', NULL);
