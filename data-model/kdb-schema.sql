-- MySQL dump 10.13  Distrib 8.0.28, for Linux (x86_64)
--
-- Host: localhost    Database: kdb
-- ------------------------------------------------------
-- Server version	8.0.28-0ubuntu0.20.04.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `kdb`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `kdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `kdb`;

--
-- Table structure for table `ir_balance`
--

DROP TABLE IF EXISTS `ir_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_balance` (
  `nick` varchar(8) NOT NULL,
  `balance` int DEFAULT NULL COMMENT 'In Paise',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nick`),
  CONSTRAINT `player_valid` FOREIGN KEY (`nick`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_balance_chk_1` CHECK ((`balance` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_booking`
--

DROP TABLE IF EXISTS `ir_booking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_booking` (
  `booking_id` bigint NOT NULL,
  `court_id` varchar(8) NOT NULL,
  `nick` varchar(8) NOT NULL,
  `booking_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `play_date` date NOT NULL,
  `from_slot` tinyint NOT NULL,
  `to_slot` tinyint NOT NULL,
  `offer_id` char(8) DEFAULT NULL,
  `price` int NOT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `offer_valid` (`court_id`,`offer_id`),
  KEY `nick` (`nick`),
  CONSTRAINT `court_valid` FOREIGN KEY (`court_id`) REFERENCES `ir_court` (`court_id`),
  CONSTRAINT `ir_booking_ibfk_1` FOREIGN KEY (`nick`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `offer_valid` FOREIGN KEY (`court_id`, `offer_id`) REFERENCES `ir_court_offers` (`court_id`, `offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_booking_slots`
--

DROP TABLE IF EXISTS `ir_booking_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_booking_slots` (
  `play_date` date NOT NULL,
  `play_slot` tinyint NOT NULL,
  `player_id` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`play_date`,`play_slot`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `ir_booking_slots_ibfk_1` FOREIGN KEY (`play_date`) REFERENCES `ir_bookings_open` (`play_date`),
  CONSTRAINT `ir_booking_slots_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_booking_slots_chk_1` CHECK (((`play_slot` > 0) and (`play_slot` < 97)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_bookings_open`
--

DROP TABLE IF EXISTS `ir_bookings_open`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_bookings_open` (
  `play_date` date NOT NULL,
  `opened_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `opened_by` varchar(8) NOT NULL,
  PRIMARY KEY (`play_date`),
  KEY `opened_by` (`opened_by`),
  CONSTRAINT `ir_bookings_open_ibfk_1` FOREIGN KEY (`opened_by`) REFERENCES `ir_people` (`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_campus`
--

DROP TABLE IF EXISTS `ir_campus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_campus` (
  `campus_id` varchar(8) NOT NULL,
  `campus_name` varchar(128) NOT NULL,
  `address_1` varchar(128) DEFAULT NULL,
  `address_2` varchar(128) DEFAULT NULL,
  `landmark` varchar(128) NOT NULL,
  `city` varchar(40) NOT NULL,
  `pincode` char(6) NOT NULL,
  `state_code` char(2) NOT NULL,
  `country_code` char(2) NOT NULL,
  `added_by` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`campus_id`),
  KEY `added_by` (`added_by`),
  CONSTRAINT `ir_campus_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `ir_people` (`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_court`
--

DROP TABLE IF EXISTS `ir_court`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_court` (
  `court_id` varchar(8) NOT NULL,
  `campus_id` varchar(8) NOT NULL,
  `price_per_slot` int NOT NULL,
  `court_info` varchar(100) NOT NULL,
  `added_by` varchar(8) NOT NULL,
  PRIMARY KEY (`court_id`),
  KEY `campus_id_valid` (`campus_id`),
  KEY `added_by` (`added_by`),
  CONSTRAINT `campus_id_valid` FOREIGN KEY (`campus_id`) REFERENCES `ir_campus` (`campus_id`),
  CONSTRAINT `ir_court_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_court_chk_1` CHECK ((`price_per_slot` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_court_offers`
--

DROP TABLE IF EXISTS `ir_court_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_court_offers` (
  `court_id` varchar(8) NOT NULL,
  `offer_id` varchar(8) NOT NULL,
  `discount` tinyint NOT NULL COMMENT 'In percentage',
  `offer_from` date NOT NULL,
  `offer_to` date NOT NULL,
  `notes` varchar(100) DEFAULT NULL COMMENT 'Reason for discount',
  PRIMARY KEY (`court_id`,`offer_id`),
  CONSTRAINT `court_is_valid` FOREIGN KEY (`court_id`) REFERENCES `ir_court` (`court_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_login`
--

DROP TABLE IF EXISTS `ir_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_login` (
  `nick` varchar(8) NOT NULL,
  `token` char(64) NOT NULL,
  `usertype` enum('CUSTOMER','SERVICE') DEFAULT 'CUSTOMER',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nick`),
  CONSTRAINT `ir_login_ibfk_1` FOREIGN KEY (`nick`) REFERENCES `ir_people` (`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_passbook`
--

DROP TABLE IF EXISTS `ir_passbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_passbook` (
  `trx_id` bigint NOT NULL AUTO_INCREMENT,
  `nick` varchar(8) NOT NULL,
  `trx_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trx_info` varchar(64) NOT NULL,
  `trx_amount` int NOT NULL,
  `recharge_id` bigint DEFAULT NULL,
  PRIMARY KEY (`trx_id`),
  KEY `nick` (`nick`),
  KEY `recharge_id` (`recharge_id`),
  CONSTRAINT `ir_passbook_ibfk_1` FOREIGN KEY (`nick`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_passbook_ibfk_2` FOREIGN KEY (`recharge_id`) REFERENCES `ir_recharge` (`recharge_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_payment_mode`
--

DROP TABLE IF EXISTS `ir_payment_mode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_payment_mode` (
  `mode_id` varchar(8) NOT NULL,
  PRIMARY KEY (`mode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_people`
--

DROP TABLE IF EXISTS `ir_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_people` (
  `nick` varchar(8) NOT NULL,
  `full_name` varchar(30) DEFAULT NULL,
  `gender` enum('M','F','O') DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `mobile_no` varchar(15) DEFAULT NULL,
  `aadhar` char(12) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `offer_id` varchar(8) DEFAULT NULL,
  `registered_by` varchar(8) NOT NULL,
  PRIMARY KEY (`nick`),
  KEY `offer_id` (`offer_id`),
  KEY `registered_by` (`registered_by`),
  CONSTRAINT `ir_people_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `ir_register_offers` (`offer_id`),
  CONSTRAINT `ir_people_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `ir_people` (`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_recharge`
--

DROP TABLE IF EXISTS `ir_recharge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_recharge` (
  `recharge_id` bigint NOT NULL AUTO_INCREMENT,
  `nick` varchar(8) DEFAULT NULL,
  `offer_id` varchar(8) NOT NULL,
  `pay_mode` varchar(8) NOT NULL,
  `pay_notes` varchar(40) NOT NULL,
  `recharge_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `recharge_by` varchar(8) NOT NULL,
  PRIMARY KEY (`recharge_id`),
  KEY `offer_id` (`offer_id`),
  KEY `nick` (`nick`),
  KEY `recharge_by` (`recharge_by`),
  KEY `pay_mode` (`pay_mode`),
  CONSTRAINT `ir_recharge_ibfk_2` FOREIGN KEY (`offer_id`) REFERENCES `ir_recharge_offers` (`offer_id`),
  CONSTRAINT `ir_recharge_ibfk_3` FOREIGN KEY (`nick`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_recharge_ibfk_4` FOREIGN KEY (`recharge_by`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_recharge_ibfk_5` FOREIGN KEY (`pay_mode`) REFERENCES `ir_payment_mode` (`mode_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_recharge_offers`
--

DROP TABLE IF EXISTS `ir_recharge_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_recharge_offers` (
  `offer_id` varchar(8) NOT NULL,
  `offer_from` date NOT NULL,
  `offer_to` date NOT NULL,
  `cashback` smallint DEFAULT '0',
  `notes` varchar(100) DEFAULT NULL COMMENT 'reason for discount',
  `offer_made_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `offer_made_by` varchar(8) NOT NULL,
  `recharge_amount` int NOT NULL,
  PRIMARY KEY (`offer_id`),
  KEY `offer_made_by` (`offer_made_by`),
  CONSTRAINT `ir_recharge_offers_ibfk_1` FOREIGN KEY (`offer_made_by`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `ir_recharge_offers_chk_1` CHECK ((`recharge_amount` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_register_offers`
--

DROP TABLE IF EXISTS `ir_register_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_register_offers` (
  `offer_id` varchar(8) NOT NULL,
  `offer_from` date NOT NULL,
  `offer_to` date NOT NULL,
  `cash_back` smallint NOT NULL,
  `notes` varchar(100) NOT NULL COMMENT 'Give reason for cash back',
  `offer_by` varchar(8) DEFAULT NULL,
  `offer_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`offer_id`),
  KEY `offer_by` (`offer_by`),
  CONSTRAINT `ir_register_offers_ibfk_1` FOREIGN KEY (`offer_by`) REFERENCES `ir_people` (`nick`),
  CONSTRAINT `cashback_limit` CHECK ((`cash_back` < 10000)),
  CONSTRAINT `date_order` CHECK ((`offer_from` <= `offer_to`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir_trace`
--

DROP TABLE IF EXISTS `ir_trace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ir_trace` (
  `trace_id` bigint NOT NULL AUTO_INCREMENT,
  `trace_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trace_log` text NOT NULL,
  PRIMARY KEY (`trace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-03-13 11:59:27
