-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: daena_db
-- ------------------------------------------------------
-- Server version	5.1.73

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alarm`
--

DROP TABLE IF EXISTS `alarm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alarm` (
  `alarm_id` int(255) NOT NULL AUTO_INCREMENT,
  `freezer_id` int(255) NOT NULL,
  `alarm_level` int(255) NOT NULL,
  `alarm_time` bigint(255) NOT NULL,
  PRIMARY KEY (`alarm_id`),
  UNIQUE KEY `unique_alarm_id` (`alarm_id`),
  KEY `Index_1` (`freezer_id`),
  KEY `Index_2` (`alarm_time`),
  CONSTRAINT `lnk_alarm_freezers` FOREIGN KEY (`freezer_id`) REFERENCES `freezers` (`freezer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `contact_id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `alt_email` varchar(255) NOT NULL,
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `unique_contact_id` (`contact_id`),
  KEY `index_contact_id` (`contact_id`),
  KEY `index_email` (`email`),
  KEY `index_name` (`name`),
  KEY `index_alt_email` (`alt_email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data`
--

DROP TABLE IF EXISTS `data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data` (
  `ping_id` bigint(255) NOT NULL AUTO_INCREMENT,
  `temp` varchar(255) NOT NULL,
  `temp_cksum` varchar(255) NOT NULL,
  `freezer_id` int(255) NOT NULL,
  `int_time` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`ping_id`),
  UNIQUE KEY `unique_ping_id` (`ping_id`),
  KEY `temp_index` (`temp`) USING BTREE,
  KEY `temp_cksum_index` (`temp_cksum`) USING BTREE,
  KEY `index_freezer_id1` (`freezer_id`),
  KEY `int_time_index` (`int_time`) USING BTREE,
  CONSTRAINT `lnk_data_freezers` FOREIGN KEY (`freezer_id`) REFERENCES `freezers` (`freezer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2912275 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `freezer_alarm_contacts`
--

DROP TABLE IF EXISTS `freezer_alarm_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freezer_alarm_contacts` (
  `freezer_id` int(255) NOT NULL,
  `contact_id` int(255) NOT NULL,
  `alarm0` int(1) NOT NULL DEFAULT '0',
  `alarm1` int(1) NOT NULL DEFAULT '0',
  `alarm2` int(1) NOT NULL DEFAULT '0',
  `alarm3` int(1) NOT NULL DEFAULT '0',
  `alarm4` int(1) NOT NULL DEFAULT '0',
  `alarm5` int(1) NOT NULL DEFAULT '0',
  `alarm6` int(1) NOT NULL DEFAULT '0',
  `alarm7` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`,`freezer_id`),
  KEY `Index_1` (`contact_id`),
  KEY `lnk_freezer_alarm_contacts_freezers` (`freezer_id`),
  CONSTRAINT `lnk_freezer_alarm_contacts_contacts` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lnk_freezer_alarm_contacts_freezers` FOREIGN KEY (`freezer_id`) REFERENCES `freezers` (`freezer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `freezers`
--

DROP TABLE IF EXISTS `freezers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freezers` (
  `freezer_id` int(255) NOT NULL,
  `freezer_location` varchar(255) NOT NULL,
  `freezer_group_id` varchar(255) NOT NULL,
  `freezer_name` varchar(255) NOT NULL,
  `freezer_color` varchar(6) NOT NULL,
  `freezer_temp_range` varchar(255) NOT NULL,
  `freezer_active` int(1) NOT NULL,
  `freezer_model_number` varchar(255) NOT NULL,
  `freezer_serial_number` varchar(255) NOT NULL,
  `freezer_setpoint1` float NOT NULL,
  `freezer_setpoint2` float NOT NULL,
  `freezer_alarm_id` int(255) NOT NULL,
  `freezer_description` varchar(255) NOT NULL,
  `freezer_send_alarm` tinyint(1) NOT NULL,
  PRIMARY KEY (`freezer_id`),
  UNIQUE KEY `freezer_id` (`freezer_id`),
  KEY `index_freezer_id` (`freezer_id`),
  KEY `index_freezer_location` (`freezer_location`),
  KEY `index_freezer_name` (`freezer_name`),
  KEY `index_freezer_color` (`freezer_color`),
  KEY `index_freezer_temp_range` (`freezer_temp_range`),
  KEY `index_freezer_active` (`freezer_active`),
  KEY `Index_freezer_group_id` (`freezer_group_id`) USING BTREE,
  KEY `index_Serial Number` (`freezer_serial_number`),
  KEY `index_Model Number` (`freezer_model_number`),
  CONSTRAINT `lnk_freezers_groups` FOREIGN KEY (`freezer_group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `group_id` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `group_desc` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `unique_group_id` (`group_id`),
  UNIQUE KEY `index_group_name` (`group_name`) USING BTREE,
  UNIQUE KEY `unique_group_name` (`group_name`),
  KEY `index_group_desc` (`group_desc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `probes`
--

DROP TABLE IF EXISTS `probes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `probes` (
  `freezer_id` int(255) NOT NULL,
  `probe_range` varchar(255) NOT NULL DEFAULT '-80',
  `probe_hostport` varchar(255) NOT NULL,
  `probe_active` int(1) NOT NULL DEFAULT '1',
  `probe_ntms_port` int(1) NOT NULL,
  `probe_type` varchar(255) NOT NULL,
  `probe_id` int(255) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`probe_id`),
  UNIQUE KEY `probe_hostport` (`probe_hostport`),
  KEY `index_probe_id` (`freezer_id`),
  KEY `index_probe_hostport` (`probe_hostport`),
  KEY `index_probe_ntms_port` (`probe_ntms_port`),
  KEY `index_probe_type` (`probe_type`),
  KEY `index_probe_range` (`probe_range`),
  KEY `index_probe_id1` (`probe_id`),
  CONSTRAINT `lnk_probes_freezers` FOREIGN KEY (`freezer_id`) REFERENCES `freezers` (`freezer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name, unique',
  `user_password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s password in salted and hashed format',
  `user_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s email, unique',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vs_database_diagrams`
--

DROP TABLE IF EXISTS `vs_database_diagrams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vs_database_diagrams` (
  `name` char(80) DEFAULT NULL,
  `diadata` text,
  `comment` varchar(1022) DEFAULT NULL,
  `preview` text,
  `lockinfo` char(80) DEFAULT NULL,
  `locktime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `version` char(80) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-09-27  3:36:49
