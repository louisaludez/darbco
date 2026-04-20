-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: darbco_system
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `inventory_data`
--

DROP TABLE IF EXISTS `inventory_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_data` (
  `item_id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'General',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `quantity_on_hand` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reorder_level` decimal(10,2) NOT NULL DEFAULT '10.00',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  KEY `fk_inventory_user` (`created_by`),
  KEY `idx_inventory_category` (`category`),
  KEY `idx_inventory_reorder` (`quantity_on_hand`,`reorder_level`),
  CONSTRAINT `fk_inventory_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_data`
--

LOCK TABLES `inventory_data` WRITE;
/*!40000 ALTER TABLE `inventory_data` DISABLE KEYS */;
INSERT INTO `inventory_data` VALUES (1,'Banana Bags (Blue)','Packaging','pcs',5000.00,500.00,2.50,NULL,1,'2026-04-15 00:35:49','2026-04-15 00:35:49'),(2,'Fertilizer (Urea)','Fertilizer','kg',500.00,50.00,45.00,NULL,1,'2026-04-15 00:35:49','2026-04-15 00:35:49'),(3,'Fertilizer (Complete)','Fertilizer','kg',300.00,30.00,52.00,NULL,1,'2026-04-15 00:35:49','2026-04-15 00:35:49'),(4,'Twine / Rope','Supplies','roll',80.00,10.00,35.00,NULL,1,'2026-04-15 00:35:49','2026-04-15 00:35:49'),(5,'Cardboard Boxes','Packaging','pcs',1999.99,200.00,8.00,NULL,1,'2026-04-15 00:35:49','2026-04-15 00:58:24'),(6,'Pesticide (Manzate)','Chemical','kg',150.00,20.00,95.00,NULL,1,'2026-04-15 00:35:49','2026-04-15 00:35:49');
/*!40000 ALTER TABLE `inventory_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_data`
--

DROP TABLE IF EXISTS `payroll_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_data` (
  `payroll_id` int unsigned NOT NULL AUTO_INCREMENT,
  `production_id` int unsigned NOT NULL,
  `worker_id` int unsigned NOT NULL,
  `harvest_date` date NOT NULL,
  `boxes_produced` int unsigned NOT NULL DEFAULT '0',
  `rate_per_box` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gross_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` enum('pending_review','reviewed','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_review',
  `computed_by` int unsigned NOT NULL,
  `reviewed_by` int unsigned DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payroll_id`),
  KEY `fk_payroll_production` (`production_id`),
  KEY `fk_payroll_computed_by` (`computed_by`),
  KEY `fk_payroll_reviewed_by` (`reviewed_by`),
  KEY `fk_payroll_approved_by` (`approved_by`),
  KEY `idx_payroll_status` (`status`),
  KEY `idx_payroll_worker` (`worker_id`),
  KEY `idx_payroll_period` (`period_start`,`period_end`),
  KEY `idx_payroll_date` (`harvest_date`),
  CONSTRAINT `fk_payroll_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_computed_by` FOREIGN KEY (`computed_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_production` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_data`
--

LOCK TABLES `payroll_data` WRITE;
/*!40000 ALTER TABLE `payroll_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_data`
--

DROP TABLE IF EXISTS `production_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_data` (
  `production_id` int unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` int unsigned NOT NULL,
  `harvest_date` date NOT NULL,
  `boxes_produced` int unsigned NOT NULL DEFAULT '0',
  `field_location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`production_id`),
  KEY `fk_production_recorder` (`recorded_by`),
  KEY `idx_production_date` (`harvest_date`),
  KEY `idx_production_worker` (`worker_id`),
  KEY `idx_production_boxes` (`boxes_produced`),
  CONSTRAINT `fk_production_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_production_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_data`
--

LOCK TABLES `production_data` WRITE;
/*!40000 ALTER TABLE `production_data` DISABLE KEYS */;
INSERT INTO `production_data` VALUES (1,1,'2026-04-15',12,'Block A, Farm 2','df',2,'2026-04-15 00:58:24','2026-04-15 00:58:24');
/*!40000 ALTER TABLE `production_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_materials`
--

DROP TABLE IF EXISTS `production_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_materials` (
  `pm_id` int unsigned NOT NULL AUTO_INCREMENT,
  `production_id` int unsigned NOT NULL,
  `item_id` int unsigned NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  PRIMARY KEY (`pm_id`),
  KEY `idx_pm_production` (`production_id`),
  KEY `idx_pm_item` (`item_id`),
  CONSTRAINT `fk_pm_inventory` FOREIGN KEY (`item_id`) REFERENCES `inventory_data` (`item_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pm_production` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_materials`
--

LOCK TABLES `production_materials` WRITE;
/*!40000 ALTER TABLE `production_materials` DISABLE KEYS */;
INSERT INTO `production_materials` VALUES (1,1,5,0.01);
/*!40000 ALTER TABLE `production_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_logs`
--

DROP TABLE IF EXISTS `transaction_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_logs` (
  `log_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `action_type` enum('production_insert','production_update','production_delete','inventory_insert','inventory_update','inventory_deduction','payroll_compute','payroll_review','payroll_approve','user_login','user_logout','user_create','user_update') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_table` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_action` (`action_type`),
  KEY `idx_log_reference` (`reference_table`,`reference_id`),
  KEY `idx_log_created` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_logs`
--

LOCK TABLES `transaction_logs` WRITE;
/*!40000 ALTER TABLE `transaction_logs` DISABLE KEYS */;
INSERT INTO `transaction_logs` VALUES (1,1,'user_create','users',2,'User \'clerk\' (id:2) created.','::1','2026-04-15 00:51:41'),(2,1,'user_create','users',3,'User \'personnel\' (id:3) created.','::1','2026-04-15 00:52:12'),(3,1,'user_create','users',4,'User \'officer\' (id:4) created.','::1','2026-04-15 00:53:06'),(4,1,'user_create','users',5,'User \'bookkeeper\' (id:5) created.','::1','2026-04-15 00:53:45'),(5,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-04-15 00:54:10'),(6,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-04-15 00:54:27'),(7,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 00:56:30'),(8,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 00:56:54'),(9,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 00:57:25'),(10,1,'user_create','workers',1,'Registered new worker: SALUDEZ LOUI','::1','2026-04-15 00:57:34'),(11,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 00:58:00'),(12,2,'production_insert','production_data',1,'Production record #1 created for worker \'\'.','::1','2026-04-15 00:58:24'),(13,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 00:58:44'),(14,1,'user_update','workers',1,'Updated worker profile (ID: 1)','::1','2026-04-15 00:58:54'),(15,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 00:59:01'),(16,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 02:10:49'),(17,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 02:15:52'),(18,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-04-15 02:16:47'),(19,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 02:17:45'),(20,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-04-15 02:18:19'),(21,5,'user_login',NULL,NULL,'User \'bookkeeper\' logged in.','::1','2026-04-15 02:19:37');
/*!40000 ALTER TABLE `transaction_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','production_clerk','payroll_personnel','finance_officer','bookkeeper') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'production_clerk',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','admin','admin@darbco.local','$2y$12$Wj0v0frbEJNw.bOckLF3l.5P9XNMdJwmTuXEnmwK/XHZpmC/lJ2xy','admin',1,'2026-04-15 00:35:49','2026-04-15 00:56:10'),(2,'Production Clerk','clerk','clerk@gmail.com','$2y$12$Z1saBYUa45nUemT6nl5rT.j7VcZfZxdxYydhDOflFRicrdj5hnUd2','production_clerk',1,'2026-04-15 00:51:41','2026-04-15 00:51:41'),(3,'Payroll Personnel','personnel','personnel@gmail.com','$2y$12$L6VmNsO0CMYjfpftHn9Sdu6/5souEYGox6P1JfFrpJggixCoq8WVq','payroll_personnel',1,'2026-04-15 00:52:12','2026-04-15 00:52:12'),(4,'Finance Officer','officer','officer@gmail.com','$2y$12$JN05MXs33jHZITOgzAE9IODKhm8sbpPu7MbK6itATYbi3yozOwTnu','finance_officer',1,'2026-04-15 00:53:06','2026-04-15 00:53:06'),(5,'Bookkeeper','bookkeeper','bookkeeper@gmail.com','$2y$12$y8AcIPvmZ5PO1NEQ19jI2O/rDNH9ojCMKl.D/Mybgx6DtDxnXzYFi','bookkeeper',1,'2026-04-15 00:53:45','2026-04-15 00:53:45');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workers`
--

DROP TABLE IF EXISTS `workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workers` (
  `worker_id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_id`),
  KEY `idx_workers_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workers`
--

LOCK TABLES `workers` WRITE;
/*!40000 ALTER TABLE `workers` DISABLE KEYS */;
INSERT INTO `workers` VALUES (1,'SALUDEZ','LOUI','09700909910',0,'2026-04-15 00:57:34','2026-04-15 00:58:54');
/*!40000 ALTER TABLE `workers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-15 11:02:32
