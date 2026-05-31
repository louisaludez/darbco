-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: darbco_system
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `daily_boxes`
--

DROP TABLE IF EXISTS `daily_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_boxes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `packing_date` date NOT NULL,
  `first_box_out` time DEFAULT NULL,
  `last_box_out` time DEFAULT NULL,
  `recorded_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `daily_boxes_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_boxes`
--

LOCK TABLES `daily_boxes` WRITE;
/*!40000 ALTER TABLE `daily_boxes` DISABLE KEYS */;
INSERT INTO `daily_boxes` VALUES (2,'2026-05-30','08:44:00','20:44:00',2,'2026-05-30 00:44:48');
/*!40000 ALTER TABLE `daily_boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_boxes_items`
--

DROP TABLE IF EXISTS `daily_boxes_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_boxes_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `daily_box_id` int NOT NULL,
  `class` enum('A','B') NOT NULL,
  `row_label` varchar(100) NOT NULL,
  `group_num` tinyint DEFAULT NULL,
  `tally` decimal(10,2) DEFAULT '0.00',
  `adj` decimal(10,2) DEFAULT '0.00',
  `should_be` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `daily_box_id` (`daily_box_id`),
  CONSTRAINT `daily_boxes_items_ibfk_1` FOREIGN KEY (`daily_box_id`) REFERENCES `daily_boxes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_boxes_items`
--

LOCK TABLES `daily_boxes_items` WRITE;
/*!40000 ALTER TABLE `daily_boxes_items` DISABLE KEYS */;
INSERT INTO `daily_boxes_items` VALUES (2,2,'A','7/8/9 Hands',1,1.00,0.00,0.00),(3,2,'B','4/5/6 Hands',1,1.00,0.00,0.00);
/*!40000 ALTER TABLE `daily_boxes_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_prod_beneficiary`
--

DROP TABLE IF EXISTS `daily_prod_beneficiary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_prod_beneficiary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `packing_date` date NOT NULL,
  `recorded_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `daily_prod_beneficiary_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_prod_beneficiary`
--

LOCK TABLES `daily_prod_beneficiary` WRITE;
/*!40000 ALTER TABLE `daily_prod_beneficiary` DISABLE KEYS */;
INSERT INTO `daily_prod_beneficiary` VALUES (1,'2026-05-30',2,'2026-05-30 01:13:25');
/*!40000 ALTER TABLE `daily_prod_beneficiary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_prod_beneficiary_items`
--

DROP TABLE IF EXISTS `daily_prod_beneficiary_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_prod_beneficiary_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `sub_code` varchar(50) DEFAULT NULL,
  `arb_name` varchar(150) DEFAULT NULL,
  `stems_cut` varchar(20) DEFAULT NULL,
  `class_a_hands` varchar(20) DEFAULT NULL,
  `class_a_sh` varchar(20) DEFAULT NULL,
  `class_a_blank1` varchar(20) DEFAULT NULL,
  `class_a_fp` varchar(20) DEFAULT NULL,
  `class_a_blank2` varchar(20) DEFAULT NULL,
  `class_a_blank3` varchar(20) DEFAULT NULL,
  `class_a_cl_b` varchar(20) DEFAULT NULL,
  `class_b_h` varchar(20) DEFAULT NULL,
  `class_b_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `daily_prod_beneficiary_items_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `daily_prod_beneficiary` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_prod_beneficiary_items`
--

LOCK TABLES `daily_prod_beneficiary_items` WRITE;
/*!40000 ALTER TABLE `daily_prod_beneficiary_items` DISABLE KEYS */;
INSERT INTO `daily_prod_beneficiary_items` VALUES (1,1,'1','1','1','1','','1','1','','','1','1','1'),(2,1,'1','1','1','1','','','','','','','',''),(3,1,'1','1','','','','','','','','','',''),(4,1,'1','1','','','','','','','','','','');
/*!40000 ALTER TABLE `daily_prod_beneficiary_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_production_reports`
--

DROP TABLE IF EXISTS `daily_production_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_production_reports` (
  `dpr_id` int unsigned NOT NULL AUTO_INCREMENT,
  `report_date` date NOT NULL,
  `week_no` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crew_size` int unsigned DEFAULT '0',
  `first_fruit_in` time DEFAULT NULL,
  `last_box_out` time DEFAULT NULL,
  `first_box_out` time DEFAULT NULL,
  `volume_stems_cut` int unsigned DEFAULT '0',
  `bs_ratio` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `per_pack_plan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dpr_id`),
  KEY `fk_dpr_recorded_by` (`recorded_by`),
  KEY `idx_dpr_date` (`report_date`),
  CONSTRAINT `fk_dpr_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_production_reports`
--

LOCK TABLES `daily_production_reports` WRITE;
/*!40000 ALTER TABLE `daily_production_reports` DISABLE KEYS */;
INSERT INTO `daily_production_reports` VALUES (2,'2026-05-29','1','asdasd',1,'01:14:00','03:14:00','13:15:00',1,'1','2',2,'2026-05-29 05:14:47','2026-05-29 05:14:47');
/*!40000 ALTER TABLE `daily_production_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dpr_boxes`
--

DROP TABLE IF EXISTS `dpr_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dpr_boxes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dpr_id` int unsigned NOT NULL,
  `box_class` enum('A','B') COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'GRP 1, GRP 3, HMLND',
  `box_spec` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '4 Hands, 5 Hands, etc.',
  `tally_count` int unsigned DEFAULT '0',
  `adjusted_count` int unsigned DEFAULT '0',
  `should_be_count` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_dprbox_dpr` (`dpr_id`),
  CONSTRAINT `fk_dprbox_dpr` FOREIGN KEY (`dpr_id`) REFERENCES `daily_production_reports` (`dpr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dpr_boxes`
--

LOCK TABLES `dpr_boxes` WRITE;
/*!40000 ALTER TABLE `dpr_boxes` DISABLE KEYS */;
INSERT INTO `dpr_boxes` VALUES (5,2,'A','GRP 1','4 Hands',0,0,0),(6,2,'A','GRP 3','4 Hands',0,0,0),(7,2,'A','HMLND','4 Hands',0,0,0),(8,2,'A','TOTAL','4 Hands',0,0,0),(9,2,'A','GRP 1','5 Hands',0,0,0),(10,2,'A','GRP 3','5 Hands',0,0,0),(11,2,'A','HMLND','5 Hands',0,0,0),(12,2,'A','TOTAL','5 Hands',0,0,0),(13,2,'A','GRP 1','6 Hands',0,0,0),(14,2,'A','GRP 1','7 Hands',0,0,0),(15,2,'A','GRP 1','100L',0,0,0),(16,2,'A','BRAND','4 Hands',0,0,0),(17,2,'A','BRAND','5 Hands',0,0,0),(18,2,'A','BRAND','6 Hands',0,0,0),(19,2,'A','BRAND','7 Hands',0,0,0),(20,2,'A','BRAND','8 Hands',0,0,0),(21,2,'A','BRAND','9 Hands',0,0,0);
/*!40000 ALTER TABLE `dpr_boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `harvest_parameters`
--

DROP TABLE IF EXISTS `harvest_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_parameters` (
  `hp_id` int unsigned NOT NULL AUTO_INCREMENT,
  `harvest_date` date NOT NULL,
  `cutting_group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crew_size` int unsigned DEFAULT '0',
  `manhours` decimal(10,2) DEFAULT '0.00',
  `stem_cut` int unsigned DEFAULT '0',
  `farm_rejects_total` int unsigned DEFAULT '0',
  `ave_fingerlength` decimal(10,2) DEFAULT '0.00',
  `ave_handclass` decimal(10,2) DEFAULT '0.00',
  `ave_stem_weight` decimal(10,2) DEFAULT '0.00',
  `percent_area_covered` decimal(10,2) DEFAULT '0.00',
  `ave_calibration` decimal(10,2) DEFAULT '0.00',
  `recorded_by` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`hp_id`),
  KEY `fk_hp_recorded_by` (`recorded_by`),
  KEY `idx_hp_date` (`harvest_date`),
  CONSTRAINT `fk_hp_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `harvest_parameters`
--

LOCK TABLES `harvest_parameters` WRITE;
/*!40000 ALTER TABLE `harvest_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `harvest_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hp_calibrations`
--

DROP TABLE IF EXISTS `hp_calibrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hp_calibrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `hp_id` int unsigned NOT NULL,
  `data_type` enum('CALIBRATION','COLOR_CODE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `week_11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `week_12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `week_13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `week_14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hpcal_hp` (`hp_id`),
  CONSTRAINT `fk_hpcal_hp` FOREIGN KEY (`hp_id`) REFERENCES `harvest_parameters` (`hp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hp_calibrations`
--

LOCK TABLES `hp_calibrations` WRITE;
/*!40000 ALTER TABLE `hp_calibrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `hp_calibrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hp_defects`
--

DROP TABLE IF EXISTS `hp_defects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hp_defects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `hp_id` int unsigned NOT NULL,
  `defect_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_8_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_9_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_10_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_11_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hpdef_hp` (`hp_id`),
  CONSTRAINT `fk_hpdef_hp` FOREIGN KEY (`hp_id`) REFERENCES `harvest_parameters` (`hp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hp_defects`
--

LOCK TABLES `hp_defects` WRITE;
/*!40000 ALTER TABLE `hp_defects` DISABLE KEYS */;
/*!40000 ALTER TABLE `hp_defects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hp_farm_rejects`
--

DROP TABLE IF EXISTS `hp_farm_rejects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hp_farm_rejects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `hp_id` int unsigned NOT NULL,
  `reject_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_11` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_12` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_13` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_14` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hprej_hp` (`hp_id`),
  CONSTRAINT `fk_hprej_hp` FOREIGN KEY (`hp_id`) REFERENCES `harvest_parameters` (`hp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hp_farm_rejects`
--

LOCK TABLES `hp_farm_rejects` WRITE;
/*!40000 ALTER TABLE `hp_farm_rejects` DISABLE KEYS */;
/*!40000 ALTER TABLE `hp_farm_rejects` ENABLE KEYS */;
UNLOCK TABLES;

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
  CONSTRAINT `fk_inventory_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
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
-- Table structure for table `packing_sessions`
--

DROP TABLE IF EXISTS `packing_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packing_sessions` (
  `session_id` int unsigned NOT NULL AUTO_INCREMENT,
  `packing_date` date NOT NULL,
  `crew_size` smallint unsigned NOT NULL DEFAULT '0',
  `first_fruit_in` time DEFAULT NULL,
  `last_fruit_in` time DEFAULT NULL,
  `first_box_out` time DEFAULT NULL,
  `last_box_out` time DEFAULT NULL,
  `stems_cut_total` int unsigned NOT NULL DEFAULT '0',
  `bs_ratio` decimal(5,2) DEFAULT NULL COMMENT 'B/S ratio',
  `per_pack_plan` int unsigned NOT NULL DEFAULT '0' COMMENT 'Per pack plan count',
  `recorded_by` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `fk_session_recorder` (`recorded_by`),
  KEY `idx_session_date` (`packing_date`),
  CONSTRAINT `fk_session_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packing_sessions`
--

LOCK TABLES `packing_sessions` WRITE;
/*!40000 ALTER TABLE `packing_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `packing_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_box_details`
--

DROP TABLE IF EXISTS `payroll_box_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_box_details` (
  `detail_id` int unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` int unsigned NOT NULL,
  `box_spec` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. 456H, CB HP, CS FD',
  `quantity` int unsigned NOT NULL DEFAULT '0',
  `price_per_box` decimal(10,2) NOT NULL DEFAULT '0.00',
  `forex_rate` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`detail_id`),
  KEY `idx_payboxdetail_payroll` (`payroll_id`),
  CONSTRAINT `fk_payboxdetail_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payroll_data` (`payroll_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_box_details`
--

LOCK TABLES `payroll_box_details` WRITE;
/*!40000 ALTER TABLE `payroll_box_details` DISABLE KEYS */;
INSERT INTO `payroll_box_details` VALUES (1,1,'CB HP',1,13.00,1.0013,13.02),(2,2,'CB HP',2,2.00,1.0000,4.00),(3,3,'CB HP',1,3.00,1.0000,3.00);
/*!40000 ALTER TABLE `payroll_box_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_contributions`
--

DROP TABLE IF EXISTS `payroll_contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_contributions` (
  `contribution_id` int unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` int unsigned NOT NULL,
  `contribution_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. Dale Capital Share, CEFUAPCO CBU',
  `previous_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `current_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `running_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`contribution_id`),
  KEY `idx_paycontrib_payroll` (`payroll_id`),
  CONSTRAINT `fk_paycontrib_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payroll_data` (`payroll_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_contributions`
--

LOCK TABLES `payroll_contributions` WRITE;
/*!40000 ALTER TABLE `payroll_contributions` DISABLE KEYS */;
INSERT INTO `payroll_contributions` VALUES (1,1,'Bio-Organic/Credit',10.00,1.00,11.00);
/*!40000 ALTER TABLE `payroll_contributions` ENABLE KEYS */;
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
  `area` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `week_number` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cycle_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvest_date` date NOT NULL,
  `boxes_produced` int unsigned NOT NULL DEFAULT '0',
  `class_a_big_hands` int unsigned NOT NULL DEFAULT '0',
  `class_a_small_hands` int unsigned NOT NULL DEFAULT '0',
  `class_a_cps` int unsigned NOT NULL DEFAULT '0',
  `class_b` int unsigned NOT NULL DEFAULT '0',
  `rate_per_box` decimal(10,2) NOT NULL DEFAULT '0.00',
  `forex_rate` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `gross_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_material_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_labor_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_personal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cash_advance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `guaranteed_income` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_contributions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `bs_ratio` decimal(6,3) DEFAULT NULL,
  `stems_cut_payroll` int unsigned NOT NULL DEFAULT '0',
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
  CONSTRAINT `fk_payroll_computed_by` FOREIGN KEY (`computed_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_production` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payroll_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_data`
--

LOCK TABLES `payroll_data` WRITE;
/*!40000 ALTER TABLE `payroll_data` DISABLE KEYS */;
INSERT INTO `payroll_data` VALUES (1,3,1,'12124','2','24242','2026-05-13',100,0,0,0,0,12.50,100.0000,13.02,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.909,110,13.02,'2026-05-01','2026-05-31','approved',3,4,4,'2026-05-13 08:19:52','2026-05-13 08:19:58','ad','2026-05-13 08:19:12','2026-05-13 08:19:58'),(2,3,1,'23','23','23','2026-05-29',0,0,0,0,0,12.50,1.0000,4.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,0,4.00,'2026-05-01','2026-05-31','approved',3,4,1,'2026-05-29 04:19:33','2026-05-29 05:16:34','d','2026-05-29 04:10:52','2026-05-29 05:16:34'),(3,3,1,'4','','4','2026-05-29',4,1,1,1,1,12.50,1.0000,3.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,0,3.00,'2026-05-01','2026-05-31','approved',3,4,1,'2026-05-29 05:12:00','2026-05-29 05:12:20','','2026-05-29 05:11:42','2026-05-29 05:12:20');
/*!40000 ALTER TABLE `payroll_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_deductions`
--

DROP TABLE IF EXISTS `payroll_deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_deductions` (
  `deduction_id` int unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` int unsigned NOT NULL,
  `category` enum('material','labor','personal','cash_advance','contribution','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`deduction_id`),
  KEY `idx_paydeduction_payroll` (`payroll_id`),
  KEY `idx_paydeduction_category` (`category`),
  CONSTRAINT `fk_paydeduction_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payroll_data` (`payroll_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_deductions`
--

LOCK TABLES `payroll_deductions` WRITE;
/*!40000 ALTER TABLE `payroll_deductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_box_breakdown`
--

DROP TABLE IF EXISTS `production_box_breakdown`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_box_breakdown` (
  `breakdown_id` int unsigned NOT NULL AUTO_INCREMENT,
  `production_id` int unsigned NOT NULL,
  `box_class` enum('A','B') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Class A or Class B',
  `box_spec` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. 4 Hands, 7 Hands, 4.7k, 7.2k, BCP, Clusters, F.P',
  `tally_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'Actual tally count',
  `adjusted_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'ADJ count after correction',
  `should_be_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'Expected / plan count',
  PRIMARY KEY (`breakdown_id`),
  KEY `idx_breakdown_production` (`production_id`),
  KEY `idx_breakdown_class` (`box_class`),
  KEY `idx_breakdown_spec` (`box_spec`),
  CONSTRAINT `fk_breakdown_production` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_box_breakdown`
--

LOCK TABLES `production_box_breakdown` WRITE;
/*!40000 ALTER TABLE `production_box_breakdown` DISABLE KEYS */;
/*!40000 ALTER TABLE `production_box_breakdown` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_data`
--

DROP TABLE IF EXISTS `production_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_data` (
  `production_id` int unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` int unsigned DEFAULT NULL,
  `beneficiary_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvest_date` date NOT NULL,
  `boxes_produced` int unsigned NOT NULL DEFAULT '0',
  `stems_cut` int unsigned NOT NULL DEFAULT '0' COMMENT 'Total stems cut by this ARB on this day',
  `hands` int unsigned DEFAULT '0',
  `small_hands` int unsigned DEFAULT '0',
  `class_a_fp` int unsigned DEFAULT '0',
  `class_b_h` int unsigned DEFAULT '0',
  `class_b_id` int unsigned DEFAULT '0',
  `class_b_cl_b` int unsigned DEFAULT '0',
  `group_number` tinyint unsigned DEFAULT NULL COMMENT 'Packing group: 1 = Group 1, 3 = Group 3',
  `block_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carrier_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `first_box_out` time DEFAULT NULL,
  `last_box_out` time DEFAULT NULL,
  `week_number` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cycle_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  KEY `idx_prod_week` (`week_number`),
  KEY `idx_prod_cycle` (`cycle_code`),
  CONSTRAINT `fk_production_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_production_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_data`
--

LOCK TABLES `production_data` WRITE;
/*!40000 ALTER TABLE `production_data` DISABLE KEYS */;
INSERT INTO `production_data` VALUES (2,1,NULL,'2026-05-13',13,12,0,0,0,0,0,0,1,'12','1212','16:09:00','16:09:00','23:21:00','s','3213','Block A, Farm 2','32242',2,'2026-05-13 08:12:38','2026-05-13 08:12:38'),(3,1,NULL,'2026-05-13',1,1,0,0,0,0,0,0,3,'ee','d',NULL,NULL,NULL,'','','','',2,'2026-05-13 08:13:00','2026-05-13 08:13:00'),(5,1,NULL,'2026-05-29',0,12,0,0,0,0,0,0,NULL,'1','wr','15:08:00',NULL,NULL,'','','','4',2,'2026-05-29 07:08:29','2026-05-29 07:08:29');
/*!40000 ALTER TABLE `production_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_defects`
--

DROP TABLE IF EXISTS `production_defects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_defects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `production_id` int unsigned NOT NULL,
  `defect_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_8_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_9_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_10_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_11_wks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_proddef_prod` (`production_id`),
  CONSTRAINT `fk_proddef_prod` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_defects`
--

LOCK TABLES `production_defects` WRITE;
/*!40000 ALTER TABLE `production_defects` DISABLE KEYS */;
/*!40000 ALTER TABLE `production_defects` ENABLE KEYS */;
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
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`pm_id`),
  KEY `idx_pm_production` (`production_id`),
  KEY `idx_pm_item` (`item_id`),
  CONSTRAINT `fk_pm_inventory` FOREIGN KEY (`item_id`) REFERENCES `inventory_data` (`item_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pm_production` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_materials`
--

LOCK TABLES `production_materials` WRITE;
/*!40000 ALTER TABLE `production_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `production_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_stem_details`
--

DROP TABLE IF EXISTS `production_stem_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_stem_details` (
  `detail_id` int unsigned NOT NULL AUTO_INCREMENT,
  `production_id` int unsigned NOT NULL,
  `row_number` tinyint unsigned NOT NULL COMMENT 'Row 11, 12, 13, or 14',
  `stem_count` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`detail_id`),
  KEY `idx_stemdetail_prod` (`production_id`),
  CONSTRAINT `fk_stemdetail_production` FOREIGN KEY (`production_id`) REFERENCES `production_data` (`production_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_stem_details`
--

LOCK TABLES `production_stem_details` WRITE;
/*!40000 ALTER TABLE `production_stem_details` DISABLE KEYS */;
INSERT INTO `production_stem_details` VALUES (1,2,11,1),(2,2,12,3),(3,2,13,3),(4,2,14,1),(5,5,11,1),(6,5,12,3),(7,5,13,4),(8,5,14,4);
/*!40000 ALTER TABLE `production_stem_details` ENABLE KEYS */;
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
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_logs`
--

LOCK TABLES `transaction_logs` WRITE;
/*!40000 ALTER TABLE `transaction_logs` DISABLE KEYS */;
INSERT INTO `transaction_logs` VALUES (1,1,'user_create','users',2,'User \'clerk\' (id:2) created.','::1','2026-04-15 00:51:41'),(2,1,'user_create','users',3,'User \'personnel\' (id:3) created.','::1','2026-04-15 00:52:12'),(3,1,'user_create','users',4,'User \'officer\' (id:4) created.','::1','2026-04-15 00:53:06'),(4,1,'user_create','users',5,'User \'bookkeeper\' (id:5) created.','::1','2026-04-15 00:53:45'),(5,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-04-15 00:54:10'),(6,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-04-15 00:54:27'),(7,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 00:56:30'),(8,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 00:56:54'),(9,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 00:57:25'),(10,1,'user_create','workers',1,'Registered new worker: SALUDEZ LOUI','::1','2026-04-15 00:57:34'),(11,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 00:58:00'),(12,2,'production_insert','production_data',1,'Production record #1 created for worker \'\'.','::1','2026-04-15 00:58:24'),(13,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 00:58:44'),(14,1,'user_update','workers',1,'Updated worker profile (ID: 1)','::1','2026-04-15 00:58:54'),(15,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 00:59:01'),(16,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 02:10:49'),(17,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-15 02:15:52'),(18,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-04-15 02:16:47'),(19,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-15 02:17:45'),(20,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-04-15 02:18:19'),(21,5,'user_login',NULL,NULL,'User \'bookkeeper\' logged in.','::1','2026-04-15 02:19:37'),(22,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-19 23:46:02'),(23,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-04-19 23:47:24'),(24,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-04-22 02:43:27'),(25,1,'user_update','workers',1,'Toggled active status of worker ID: 1','::1','2026-04-22 02:53:37'),(26,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-11 07:47:18'),(27,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-11 08:37:40'),(28,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-11 23:48:02'),(29,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-12 00:14:43'),(30,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-12 03:09:42'),(31,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-13 07:29:29'),(32,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-13 08:09:22'),(33,2,'production_insert','production_data',2,'Production record #2 created for worker ID 1.','::1','2026-05-13 08:12:39'),(34,2,'production_delete','production_data',1,'Deleted production record #1. Associated materials restored to inventory.','::1','2026-05-13 08:12:48'),(35,2,'production_insert','production_data',3,'Production record #3 created for worker ID 1.','::1','2026-05-13 08:13:00'),(36,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-13 08:13:45'),(37,1,'user_create','workers',2,'Registered new worker: sdsad dsdas','::1','2026-05-13 08:14:01'),(38,2,'user_create','workers',3,'Registered new worker: test test','::1','2026-05-13 08:16:15'),(39,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-05-13 08:16:32'),(40,3,'payroll_compute','payroll_data',1,'Payroll #1 computed (full proceeds).','::1','2026-05-13 08:19:12'),(41,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-05-13 08:19:46'),(42,4,'payroll_review','payroll_data',1,'Payroll #1 reviewed.','::1','2026-05-13 08:19:52'),(43,4,'payroll_approve','payroll_data',1,'Payroll #1 approved.','::1','2026-05-13 08:19:58'),(44,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-27 03:18:08'),(45,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-27 03:18:42'),(46,1,'user_login',NULL,NULL,'User \'admin\' logged in.','::1','2026-05-29 04:08:29'),(47,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-05-29 04:09:58'),(48,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-05-29 04:10:22'),(49,3,'payroll_compute','payroll_data',2,'Payroll #2 computed (full proceeds).','::1','2026-05-29 04:10:52'),(50,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-05-29 04:19:20'),(51,4,'payroll_review','payroll_data',2,'Payroll #2 reviewed.','::1','2026-05-29 04:19:33'),(52,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-29 04:23:22'),(53,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-05-29 04:52:05'),(54,3,'payroll_compute','payroll_data',3,'Payroll #3 computed (full proceeds).','::1','2026-05-29 05:11:42'),(55,4,'user_login',NULL,NULL,'User \'officer\' logged in.','::1','2026-05-29 05:11:55'),(56,4,'payroll_review','payroll_data',3,'Payroll #3 reviewed.','::1','2026-05-29 05:12:00'),(57,3,'user_login',NULL,NULL,'User \'personnel\' logged in.','::1','2026-05-29 05:12:13'),(58,1,'payroll_approve','payroll_data',3,'Payroll #3 approved by admin.','::1','2026-05-29 05:12:20'),(59,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-29 05:13:03'),(60,1,'payroll_approve','payroll_data',2,'Payroll #2 approved by admin.','::1','2026-05-29 05:16:34'),(61,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-29 06:46:46'),(62,2,'production_insert','production_data',5,'Production record #5 created for worker ID 1.','::1','2026-05-29 07:08:29'),(63,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-29 08:25:20'),(64,2,'user_login',NULL,NULL,'User \'clerk\' logged in.','::1','2026-05-29 23:24:25');
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
  `sub_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ARB sub-code from physical report (e.g. 042, 181)',
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_id`),
  KEY `idx_workers_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workers`
--

LOCK TABLES `workers` WRITE;
/*!40000 ALTER TABLE `workers` DISABLE KEYS */;
INSERT INTO `workers` VALUES (1,NULL,'SALUDEZ','LOUI','09700909910',NULL,1,'2026-04-15 00:57:34','2026-04-22 02:53:37'),(2,'123','sdsad','dsdas','3453534','5etert',1,'2026-05-13 08:14:01','2026-05-13 08:14:01'),(3,'test','test','test','242345','2353245',1,'2026-05-13 08:16:15','2026-05-13 08:16:15');
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

-- Dump completed on 2026-05-30  9:18:01
