-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2011 at 10:01 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `2011_pesapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `pesapi_account`
--

CREATE TABLE IF NOT EXISTS `pesapi_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type_index` (`type`),
  KEY `definedby` (`identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pesapi_account`
--

INSERT INTO `pesapi_account` (`id`, `type`, `name`, `identifier`) VALUES
(1, 2, 'Private Mpesa account', 'privatedefault');

-- --------------------------------------------------------

--
-- Table structure for table `pesapi_payment`
--

CREATE TABLE IF NOT EXISTS `pesapi_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `super_type` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `receipt` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `time` datetime NOT NULL,
  `phonenumber` varchar(45) COLLATE latin1_danish_ci NOT NULL,
  `name` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `account` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `status` int(11) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `post_balance` bigint(20) NOT NULL,
  `note` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `transaction_cost` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type_index` (`type`),
  KEY `name_index` (`name`),
  KEY `phone_index` (`phonenumber`),
  KEY `time_index` (`time`),
  KEY `fk_mpesapi_payment_account` (`account_id`),
  KEY `super_index` (`super_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_danish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pesapi_setting`
--

CREATE TABLE IF NOT EXISTS `pesapi_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `name` varchar(255) COLLATE latin1_danish_ci NOT NULL,
  `value_string` varchar(255) COLLATE latin1_danish_ci DEFAULT NULL,
  `value_date` datetime DEFAULT NULL,
  `value_int` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name_index` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_danish_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pesapi_setting`
--

INSERT INTO `pesapi_setting` (`id`, `type`, `name`, `value_string`, `value_date`, `value_int`) VALUES
(1, 2, 'LastSync', NULL, '2011-09-28 21:27:36', NULL),
(2, 1, 'MpesaPassword', '**initial_password**', NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesapi_payment`
--
ALTER TABLE `pesapi_payment`
  ADD CONSTRAINT `fk_mpesapi_payment_account` FOREIGN KEY (`account_id`) REFERENCES `pesapi_account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
