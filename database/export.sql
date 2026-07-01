-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: saferoad_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(200) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_action_time` (`user_id`,`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'login','User logged in successfully','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 15:48:24'),(2,1,'logout','User logged out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 15:54:54'),(3,NULL,'login_failed','Failed login attempt for: driver','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 15:55:19'),(4,2,'login','User logged in successfully','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 15:55:41'),(5,2,'logout','User logged out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 16:02:08'),(6,1,'login','User logged in successfully','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 16:07:19'),(7,1,'logout','User logged out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 16:12:14'),(8,2,'login','User logged in successfully','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 16:12:19'),(9,2,'login','User logged in successfully','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 16:59:44'),(10,2,'logout','User logged out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 17:16:40'),(11,1,'login','User logged in successfully','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 17:16:56'),(12,1,'update_profile','Updated profile information','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 17:19:39'),(13,1,'logout','User logged out','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-07-01 17:22:16');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_type` enum('accident','road_closure','construction','flood','hazard','weather','traffic_jam','school_zone','hospital_zone','dangerous_curve','checkpoint') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `radius_meters` int(11) DEFAULT 500,
  `road_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `road_id` (`road_id`),
  KEY `reported_by` (`reported_by`),
  KEY `idx_active_alerts` (`is_active`,`alert_type`),
  CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`road_id`) REFERENCES `roads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alerts`
--

LOCK TABLES `alerts` WRITE;
/*!40000 ALTER TABLE `alerts` DISABLE KEYS */;
INSERT INTO `alerts` VALUES (1,'accident','Accident on KN 5 Highway','Collision near Kacyiru. Use KG 7 Ave alternative.',-1.9460000,30.0660000,'high',500,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(2,'construction','Construction KG 11 Remera','Road repair KG 11. Use KG 9 Nyarutarama.',-1.9620000,30.0830000,'medium',300,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(3,'school_zone','├ëcole Internationale Zone','School zone KG 7 - speed 25 km/h',-1.9420000,30.0600000,'medium',200,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(4,'hospital_zone','King Faisal Hospital Zone','KN 5 - emergency vehicles',-1.9550000,30.0750000,'medium',300,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(5,'traffic_jam','Heavy Traffic Umuganda Blvd','Peak hour Kacyiru. Use KN 3.',-1.9445000,30.0680000,'high',800,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(6,'dangerous_curve','Sharp Curve KN 3','Near Kabuga. Reduce to 30 km/h.',-1.9740000,30.0360000,'high',150,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(7,'accident','Accident Remera Junction','KG 11 & KN 5. Road partially blocked.',-1.9600000,30.0800000,'critical',400,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(8,'school_zone','Green Hills Academy Zone','KN 5 - speed 25 km/h',-1.9520000,30.0700000,'medium',200,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(9,'construction','Road Works KN 2 Gatuna Rd','Bridge maintenance. Single lane.',-1.9340000,30.0560000,'medium',400,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(10,'hospital_zone','CHUK Hospital Zone','Emergency vehicles',-1.9580000,30.0680000,'medium',250,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(11,'flood','Flood Warning Nyarutarama','Water on KG 9. Use KN 5.',-1.9520000,30.0760000,'high',300,NULL,1,NULL,NULL,'2026-07-01 15:46:21'),(12,'dangerous_curve','Hairpin Bend KN 1','Reduce to 20 km/h.',-1.9340000,30.0460000,'medium',100,NULL,1,NULL,NULL,'2026-07-01 15:46:21');
/*!40000 ALTER TABLE `alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checkpoints`
--

DROP TABLE IF EXISTS `checkpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checkpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checkpoint_name` varchar(200) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `type` enum('traffic_police','speed_camera','red_light_camera','toll_booth','weigh_station','border') DEFAULT 'traffic_police',
  `status` enum('active','inactive') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `added_by` (`added_by`),
  CONSTRAINT `checkpoints_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checkpoints`
--

LOCK TABLES `checkpoints` WRITE;
/*!40000 ALTER TABLE `checkpoints` DISABLE KEYS */;
INSERT INTO `checkpoints` VALUES (1,'KN 5 - Kacyiru Police Checkpoint',-1.9480000,30.0650000,'traffic_police','active','Official RNP traffic checkpoint on KN 5 Kacyiru',NULL,'2026-07-01 15:46:21'),(2,'Umuganda Blvd Speed Camera',-1.9445000,30.0680000,'speed_camera','active','Automated speed enforcement Umuganda Blvd',NULL,'2026-07-01 15:46:21'),(3,'Airport Road Toll Plaza',-1.9720000,30.0990000,'toll_booth','active','Kigali Airport toll point',NULL,'2026-07-01 15:46:21'),(4,'KN 3 - Gitarama Road Camera',-1.9640000,30.0460000,'speed_camera','active','Speed camera KN 3',NULL,'2026-07-01 15:46:21'),(5,'KG 11 - Remera Traffic Light Camera',-1.9630000,30.0830000,'red_light_camera','active','Red light enforcement Remera',NULL,'2026-07-01 15:46:21'),(6,'KN 2 - Gatuna Checkpoint',-1.9340000,30.0560000,'traffic_police','active','RNP checkpoint KN 2',NULL,'2026-07-01 15:46:21'),(7,'Boulevard de la R├®volution Camera',-1.9510000,30.0630000,'speed_camera','active','Speed camera Revolution Blvd',NULL,'2026-07-01 15:46:21'),(8,'KG 7 - Kacyiru Junction',-1.9440000,30.0630000,'traffic_police','active','Traffic police Kacyiru',NULL,'2026-07-01 15:46:21'),(9,'Nyarutarama Speed Check',-1.9530000,30.0730000,'speed_camera','active','Speed monitoring Nyarutarama',NULL,'2026-07-01 15:46:21'),(10,'KN 4 - City Center Camera',-1.9470000,30.0590000,'red_light_camera','active','Red light camera KN 4',NULL,'2026-07-01 15:46:21');
/*!40000 ALTER TABLE `checkpoints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinations`
--

DROP TABLE IF EXISTS `destinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `address` text DEFAULT NULL,
  `is_favorite` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `destinations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinations`
--

LOCK TABLES `destinations` WRITE;
/*!40000 ALTER TABLE `destinations` DISABLE KEYS */;
/*!40000 ALTER TABLE `destinations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver_locations`
--

DROP TABLE IF EXISTS `driver_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `driver_locations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `speed` decimal(6,2) DEFAULT 0.00,
  `heading` decimal(5,2) DEFAULT 0.00,
  `accuracy` decimal(5,2) DEFAULT 0.00,
  `is_moving` tinyint(1) DEFAULT 0,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_location` (`user_id`,`recorded_at`),
  CONSTRAINT `driver_locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver_locations`
--

LOCK TABLES `driver_locations` WRITE;
/*!40000 ALTER TABLE `driver_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `driver_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driving_history`
--

DROP TABLE IF EXISTS `driving_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `driving_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `start_lat` decimal(10,7) DEFAULT NULL,
  `start_lng` decimal(10,7) DEFAULT NULL,
  `end_lat` decimal(10,7) DEFAULT NULL,
  `end_lng` decimal(10,7) DEFAULT NULL,
  `start_address` text DEFAULT NULL,
  `end_address` text DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `avg_speed` decimal(6,2) DEFAULT NULL,
  `max_speed` decimal(6,2) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `warnings_count` int(11) DEFAULT 0,
  `is_completed` tinyint(1) DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `driving_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driving_history`
--

LOCK TABLES `driving_history` WRITE;
/*!40000 ALTER TABLE `driving_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `driving_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'Welcome Back','You have successfully logged in.','success',0,'2026-07-01 15:48:24'),(2,2,'Welcome Back','You have successfully logged in.','success',0,'2026-07-01 15:55:41'),(3,1,'Welcome Back','You have successfully logged in.','success',0,'2026-07-01 16:07:19'),(4,2,'Welcome Back','You have successfully logged in.','success',0,'2026-07-01 16:12:19'),(5,2,'Welcome Back','You have successfully logged in.','success',0,'2026-07-01 16:59:44'),(6,1,'Welcome Back','You have successfully logged in.','success',0,'2026-07-01 17:16:56');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `report_type` enum('accident','traffic','speed_violation','road_condition','other') DEFAULT 'other',
  `description` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reported_by` (`reported_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roads`
--

DROP TABLE IF EXISTS `roads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `road_name` varchar(200) NOT NULL,
  `road_type` varchar(50) DEFAULT 'Highway',
  `start_lat` decimal(10,7) DEFAULT NULL,
  `start_lng` decimal(10,7) DEFAULT NULL,
  `end_lat` decimal(10,7) DEFAULT NULL,
  `end_lng` decimal(10,7) DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `lanes` int(11) DEFAULT 2,
  `surface_type` varchar(50) DEFAULT 'Asphalt',
  `status` enum('open','closed','under_construction') DEFAULT 'open',
  `added_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `added_by` (`added_by`),
  CONSTRAINT `roads_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roads`
--

LOCK TABLES `roads` WRITE;
/*!40000 ALTER TABLE `roads` DISABLE KEYS */;
INSERT INTO `roads` VALUES (1,'KN 5 Rd - Kigali Highway','Highway',-1.9441000,30.0619000,-2.0041000,30.1119000,8.50,4,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(2,'Boulevard de l\'Umuganda','Highway',-1.9390000,30.0580000,-1.9541000,30.0819000,3.20,4,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(3,'KG 7 Ave - Kacyiru','City Road',-1.9390000,30.0580000,-1.9490000,30.0680000,2.10,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(4,'KK 15 Rd - Airport Road','Highway',-1.9680000,30.0740000,-1.9780000,30.1240000,5.50,4,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(5,'KN 3 Rd - Gitarama Road','Highway',-1.9441000,30.0619000,-1.9841000,30.0319000,6.00,4,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(6,'Avenue de la Paix','City Road',-1.9500000,30.0600000,-1.9600000,30.0700000,1.50,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(7,'KG 11 Ave - Remera','City Road',-1.9580000,30.0780000,-1.9680000,30.0880000,1.80,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(8,'KN 1 Rd - Ruhengeri Road','Highway',-1.9441000,30.0619000,-1.9241000,30.0419000,3.00,4,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(9,'Boulevard de la R├®volution','City Road',-1.9460000,30.0580000,-1.9560000,30.0680000,1.60,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(10,'KG 9 Ave - Nyarutarama','City Road',-1.9580000,30.0680000,-1.9480000,30.0780000,1.40,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(11,'KN 2 Rd - Gatuna Road','Highway',-1.9441000,30.0619000,-1.9141000,30.0519000,5.00,4,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(12,'KG 220 St - Kimihurura','City Road',-1.9510000,30.0640000,-1.9410000,30.0740000,1.20,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(13,'KN 4 Rd','City Road',-1.9420000,30.0640000,-1.9520000,30.0540000,1.30,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(14,'KG 14 Ave','City Road',-1.9560000,30.0720000,-1.9460000,30.0820000,1.50,2,'Asphalt','open',NULL,'2026-07-01 15:46:21'),(15,'KG 5 Ave','City Road',-1.9480000,30.0620000,-1.9380000,30.0720000,1.30,2,'Asphalt','open',NULL,'2026-07-01 15:46:21');
/*!40000 ALTER TABLE `roads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Administrator with full access','2026-07-01 14:55:53'),(2,'driver','Registered driver user','2026-07-01 14:55:53');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','SafeRoad AI','general',NULL,'2026-07-01 14:55:54'),(2,'admin_email','admin@saferoad.ai','general',NULL,'2026-07-01 14:55:54'),(3,'speed_warning_threshold','10','safety',NULL,'2026-07-01 14:55:54'),(4,'voice_warning_enabled','1','safety',NULL,'2026-07-01 14:55:54'),(5,'location_update_interval','5','tracking',NULL,'2026-07-01 14:55:54'),(6,'google_maps_api_key','YOUR_API_KEY_HERE','api',NULL,'2026-07-01 17:36:04'),(7,'alert_radius_default','500','alerts',NULL,'2026-07-01 14:55:54'),(8,'session_timeout','3600','security',NULL,'2026-07-01 14:55:54'),(9,'dark_mode','0','appearance',NULL,'2026-07-01 14:55:54'),(10,'notifications_enabled','1','notifications',NULL,'2026-07-01 14:55:54');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `speed_limits`
--

DROP TABLE IF EXISTS `speed_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `speed_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `road_id` int(11) DEFAULT NULL,
  `max_speed` int(11) NOT NULL,
  `min_speed` int(11) DEFAULT 0,
  `vehicle_type` varchar(50) DEFAULT 'all',
  `effective_from` time DEFAULT '00:00:00',
  `effective_to` time DEFAULT '23:59:59',
  `is_active` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `road_id` (`road_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `speed_limits_ibfk_1` FOREIGN KEY (`road_id`) REFERENCES `roads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `speed_limits_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `speed_limits`
--

LOCK TABLES `speed_limits` WRITE;
/*!40000 ALTER TABLE `speed_limits` DISABLE KEYS */;
INSERT INTO `speed_limits` VALUES (1,1,60,20,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(2,2,50,20,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(3,3,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(4,4,60,20,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(5,5,60,20,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(6,6,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(7,7,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(8,8,50,20,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(9,9,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(10,10,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(11,11,60,20,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(12,12,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(13,13,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(14,14,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21'),(15,15,30,10,'all','00:00:00','23:59:59',1,NULL,'2026-07-01 15:46:21');
/*!40000 ALTER TABLE `speed_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','admin@saferoad.ai','$2y$10$EfhwduTNGrXCdcgbzV/oDO0HYKR3.j1vS4prxUZ5jrXPdUTheZw1y','kevin Ndayisenga','kevin Ndayisenga','kigali\r\nkigali,rwanda','default.png',1,'2026-07-01 17:16:56','2026-07-01 14:55:53','2026-07-01 17:19:39'),(2,2,'driver1','driver1@saferoad.ai','$2y$10$YLtPGopi80kkNy9RerI0yO/pBs2avSTJf1MJbz7EuYxdpHnw1cSH6','John Driver','9876543211',NULL,'default.png',1,'2026-07-01 16:59:43','2026-07-01 14:55:53','2026-07-01 16:59:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warning_logs`
--

DROP TABLE IF EXISTS `warning_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warning_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `warning_type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `speed` decimal(6,2) DEFAULT NULL,
  `speed_limit` int(11) DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_warnings` (`user_id`,`created_at`),
  CONSTRAINT `warning_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warning_logs`
--

LOCK TABLES `warning_logs` WRITE;
/*!40000 ALTER TABLE `warning_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `warning_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-01 20:36:13
