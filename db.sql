-- MySQL dump 10.13  Distrib 8.0.18, for Win64 (x86_64)
--
-- Host: localhost    Database: rocko-routesetting
-- ------------------------------------------------------
-- Server version	5.7.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Articles_Articles`
--

DROP TABLE IF EXISTS `Articles_Articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Articles_Articles` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `User_Id` int(11) DEFAULT NULL,
  `User_New` tinyint(1) NOT NULL,
  `Publish` datetime NOT NULL,
  `Published` tinyint(1) NOT NULL,
  `AuthorName` varchar(128) NOT NULL,
  `Title` varchar(256) NOT NULL,
  `Intro` varchar(1024) NOT NULL,
  `Content_Raw` mediumtext NOT NULL,
  `Content_Html` mediumtext NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Articles-Articles_Users-Users_idx` (`User_Id`),
  FULLTEXT KEY `Articles-Articles_Match` (`Title`,`AuthorName`,`Content_Html`,`Intro`),
  CONSTRAINT `Articles-Articles_Users-Users` FOREIGN KEY (`User_Id`) REFERENCES `Users_Users` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1107 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Articles_Articles`
--

LOCK TABLES `Articles_Articles` WRITE;
/*!40000 ALTER TABLE `Articles_Articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `Articles_Articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Cache_Files`
--

DROP TABLE IF EXISTS `Cache_Files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Cache_Files` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `User_Id` int(11) DEFAULT NULL,
  `Hash` varchar(128) NOT NULL,
  `Expires` datetime NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Cache_Files-Users_Users_idx` (`User_Id`),
  CONSTRAINT `Cache_Files-Users_Users` FOREIGN KEY (`User_Id`) REFERENCES `Users_Users` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Cache_Files`
--

LOCK TABLES `Cache_Files` WRITE;
/*!40000 ALTER TABLE `Cache_Files` DISABLE KEYS */;
/*!40000 ALTER TABLE `Cache_Files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Config_Settings`
--

DROP TABLE IF EXISTS `Config_Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Config_Settings` (
  `Name` varchar(32) NOT NULL,
  `Value` mediumtext NOT NULL,
  PRIMARY KEY (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Config_Settings`
--

LOCK TABLES `Config_Settings` WRITE;
/*!40000 ALTER TABLE `Config_Settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `Config_Settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Log_Logs`
--

DROP TABLE IF EXISTS `Log_Logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Log_Logs` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `User_Id` int(11) DEFAULT NULL,
  `DateTime` datetime DEFAULT NULL,
  `Message` varchar(256) DEFAULT NULL,
  `Data` mediumtext,
  PRIMARY KEY (`Id`),
  KEY `Log_Logs-Users_Users_idx` (`User_Id`),
  CONSTRAINT `Log_Logs-Users_Users` FOREIGN KEY (`User_Id`) REFERENCES `Users_Users` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Log_Logs`
--

LOCK TABLES `Log_Logs` WRITE;
/*!40000 ALTER TABLE `Log_Logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `Log_Logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Session_Sessions`
--

DROP TABLE IF EXISTS `Session_Sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Session_Sessions` (
  `Id` varchar(32) NOT NULL,
  `Access` int(10) unsigned DEFAULT NULL,
  `Data` text,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Session_Sessions`
--

LOCK TABLES `Session_Sessions` WRITE;
/*!40000 ALTER TABLE `Session_Sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `Session_Sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Sys_Users`
--

DROP TABLE IF EXISTS `Sys_Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Sys_Users` (
  `Id` int(11) NOT NULL,
  `Name` varchar(64) NOT NULL,
  `Email` varchar(128) NOT NULL,
  PRIMARY KEY (`Id`),
  CONSTRAINT `Sys_Users-Users_Users` FOREIGN KEY (`Id`) REFERENCES `Users_Users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Sys_Users`
--

LOCK TABLES `Sys_Users` WRITE;
/*!40000 ALTER TABLE `Sys_Users` DISABLE KEYS */;
/*!40000 ALTER TABLE `Sys_Users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Tasks_Tasks`
--

DROP TABLE IF EXISTS `Tasks_Tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tasks_Tasks` (
  `Hash` varchar(128) NOT NULL,
  `User_Id` int(11) DEFAULT NULL,
  `DateTime` datetime NOT NULL,
  `Finished` tinyint(1) NOT NULL,
  `Info` mediumtext NOT NULL,
  `Data` mediumtext NOT NULL,
  PRIMARY KEY (`Hash`),
  KEY `Tasks_Tasks_DateTime_idx` (`DateTime`),
  KEY `Tasks_Tasks_idx` (`Hash`),
  KEY `Users_Users-Tasks-Tasks_idx` (`User_Id`),
  CONSTRAINT `Users_Users-Tasks-Tasks` FOREIGN KEY (`User_Id`) REFERENCES `Users_Users` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tasks_Tasks`
--

LOCK TABLES `Tasks_Tasks` WRITE;
/*!40000 ALTER TABLE `Tasks_Tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `Tasks_Tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Users_Users`
--

DROP TABLE IF EXISTS `Users_Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users_Users` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(16) NOT NULL,
  `LoginHash` varchar(256) NOT NULL,
  `EmailHash` varchar(256) NOT NULL,
  `PasswordHash` varchar(256) NOT NULL,
  `Groups` varchar(128) NOT NULL DEFAULT '',
  `Active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`),
  KEY `login` (`LoginHash`(255),`PasswordHash`(255),`Active`)
) ENGINE=InnoDB AUTO_INCREMENT=1006 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users_Users`
--

LOCK TABLES `Users_Users` WRITE;
/*!40000 ALTER TABLE `Users_Users` DISABLE KEYS */;
/*!40000 ALTER TABLE `Users_Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-09-05 17:58:31
