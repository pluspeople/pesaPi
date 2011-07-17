-- phpMyAdmin SQL Dump
-- version 3.4.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 17, 2011 at 05:43 AM
-- Server version: 5.5.8
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `2011_simulator`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_danish_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `type`, `transfer_direction`, `reciept`, `time`, `phonenumber`, `name`, `account`, `status`, `amount`, `post_balance`, `note`) VALUES
(1, 1, 0, 'badf21332', '2011-07-17 02:43:36', '1234567', 'yodelay', '1234', 1, 10000, 10000, ''),
(3, 1, 0, 'badf21332x', '2011-07-17 02:44:22', '1234567', 'yodelay', '1234', 1, 10000, 10000, ''),
(5, 1, 0, 'BCXY8373', '2011-07-17 05:36:00', '39848585', 'Mike', '6284', 1, 300000, 300000, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
