-- MySQL dump 10.13  Distrib 8.0.31, for Win64 (x86_64)
--
-- Host: localhost    Database: 90day
-- ------------------------------------------------------
-- Server version	8.0.31

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
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `districts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
INSERT INTO `districts` VALUES (1,'เมืองพัทลุง'),(2,'กงหรา'),(3,'ควนขนุน'),(4,'ตะโหมด'),(5,'เขาชัยสน'),(6,'ปากพะยูน'),(7,'ศรีบรรพต'),(8,'ป่าบอน'),(9,'บางแก้ว'),(10,'ป่าพะยอม'),(11,'ศรีนครินทร์');
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_locations`
--

DROP TABLE IF EXISTS `risk_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `district_id` int DEFAULT NULL,
  `subdistrict_id` int DEFAULT NULL,
  `risk_type_id` int DEFAULT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('active','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'สถานะ: active=มีความเสี่ยง, resolved=แก้ไขแล้ว',
  `details` text COLLATE utf8mb4_unicode_ci,
  `image_before` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_after` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `district_id` (`district_id`),
  KEY `risk_type_id` (`risk_type_id`),
  KEY `fk_risk_subdistrict` (`subdistrict_id`),
  CONSTRAINT `fk_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_risk_subdistrict` FOREIGN KEY (`subdistrict_id`) REFERENCES `subdistricts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_risk_type` FOREIGN KEY (`risk_type_id`) REFERENCES `risk_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_locations`
--

LOCK TABLES `risk_locations` WRITE;
/*!40000 ALTER TABLE `risk_locations` DISABLE KEYS */;
INSERT INTO `risk_locations` VALUES (9,1,1,4,'ศาลากลางจังหวัดพัทลุง',7.61708304,100.07280785,'active','ทดสอบ การแจ้งจุดเสี่ยง','eed93753f75621921c36c1389042bb9e.png',NULL,7,'2026-06-22 06:17:24','2026-06-22 06:17:24'),(10,3,13,2,'ศาลาหมู่บ้าน',7.76255848,99.99954708,'active','วัยรุ่นชอบมามั่วสุมกัน','3b85eac85ffbf4fcf9338a6f263b73c8.png',NULL,8,'2026-06-22 06:28:00','2026-06-22 06:28:00');
/*!40000 ALTER TABLE `risk_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_types`
--

DROP TABLE IF EXISTS `risk_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marker_color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_types`
--

LOCK TABLES `risk_types` WRITE;
/*!40000 ALTER TABLE `risk_types` DISABLE KEYS */;
INSERT INTO `risk_types` VALUES (1,'จุดค้ายา','#DC2626'),(2,'จุดมั่วสุม','#F59E0B'),(3,'จุดแข่งรถ','#3B82F6'),(4,'จุดยิงกันบ่อย','#7F1D1D'),(5,'จุดลักทรัพย์','#8B5CF6');
/*!40000 ALTER TABLE `risk_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','ดูและจัดการข้อมูลได้ทั้งหมดในจังหวัด'),(2,'Governor','ผู้ว่าราชการจังหวัด ดูข้อมูลได้ทั้งหมดในจังหวัด'),(3,'District Chief','นายอำเภอ สรุปยอดได้เฉพาะอำเภอตนเอง'),(4,'Officer','เจ้าหน้าที่ผู้ลงข้อมูล ดูข้อมูลได้เฉพาะของตนเอง');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subdistricts`
--

DROP TABLE IF EXISTS `subdistricts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subdistricts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `district_id` int NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_subdistrict_district` (`district_id`),
  CONSTRAINT `fk_subdistrict_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subdistricts`
--

LOCK TABLES `subdistricts` WRITE;
/*!40000 ALTER TABLE `subdistricts` DISABLE KEYS */;
INSERT INTO `subdistricts` VALUES (1,1,'คูหาสวรรค์',7.63206700,100.07793300),(2,1,'เขาเจียก',7.63756700,100.09563300),(3,1,'ท่ามิหรำ',7.61836700,100.09993300),(4,1,'โคกชะงาย',7.61056700,100.06923300),(5,1,'ลำปำ',7.63336700,100.08333300),(6,1,'ตำนาน',7.64136700,100.09393300),(7,1,'ควนมะพร้าว',7.63106700,100.07963300),(8,2,'กงหรา',7.45293300,99.95080000),(9,2,'ชะรัด',7.42553300,99.92990000),(10,2,'คลองทรายขาว',7.44623300,99.96510000),(11,2,'สมหวัง',7.43023300,99.93270000),(12,2,'คลองเฉลิม',7.46213300,99.97120000),(13,3,'ควนขนุน',7.76133300,100.00906700),(14,3,'มะกอกเหนือ',7.76103300,100.04586700),(15,3,'ทะเลน้อย',7.71203300,99.99946700),(16,3,'ดอนทราย',7.72363300,99.99266700),(17,3,'พะนางตุง',7.74463300,100.01916700),(18,3,'แพรกหา',7.74343300,100.02746700),(19,4,'ตะโหมด',7.33583300,100.07443300),(20,4,'แม่ขรี',7.35143300,100.08293300),(21,4,'คลองใหญ่',7.34433300,100.06373300),(22,5,'เขาชัยสน',7.46670000,100.13203300),(23,5,'โคกม่วง',7.46050000,100.11663300),(24,5,'หานโพธิ์',7.43650000,100.13193300),(25,5,'จองถนน',7.45080000,100.14713300),(26,6,'ปากพะยูน',7.37060000,100.30746700),(27,6,'ดอนประดู่',7.33270000,100.34126700),(28,6,'เกาะหมาก',7.37480000,100.31456700),(29,6,'เกาะนางคำ',7.36940000,100.29456700),(30,7,'เขาย่า',7.63700000,99.90433300),(31,7,'ตะแพน',7.65220000,99.88253300),(32,7,'เตาปูน',7.65340000,99.90823300),(33,8,'ป่าบอน',7.24156700,100.16926700),(34,8,'โคกทราย',7.26896700,100.16326700),(35,8,'หนองธง',7.25916700,100.14856700),(36,8,'ทุ่งนารี',7.25496700,100.16296700),(37,9,'ท่ามะเดื่อ',7.46073300,100.20223300),(38,9,'นาปะขอ',7.43573300,100.16353300),(39,9,'โคกสัก',7.42053300,100.20333300),(40,10,'ป่าพะยอม',7.88000000,99.93533300),(41,10,'ลานข่อย',7.85010000,99.95143300),(42,10,'เกาะเต่า',7.85210000,99.93053300),(43,10,'บ้านพร้าว',7.86970000,99.93893300),(44,11,'ชุมพล',7.52250000,99.92090000),(45,11,'บ้านนา',7.53140000,99.94540000),(46,11,'อ่างทอง',7.52990000,99.96490000),(47,11,'ลำสินธุ์',7.54390000,99.97200000);
/*!40000 ALTER TABLE `subdistricts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_houses`
--

DROP TABLE IF EXISTS `target_houses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_houses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `district_id` int DEFAULT NULL,
  `subdistrict_id` int DEFAULT NULL,
  `target_type_id` int DEFAULT NULL,
  `house_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('active','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'สถานะ: active=รอตรวจสอบ, resolved=ดำเนินการแล้ว',
  `details` text COLLATE utf8mb4_unicode_ci,
  `image_before` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_after` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_target_district` (`district_id`),
  KEY `fk_target_subdistrict` (`subdistrict_id`),
  KEY `fk_target_type` (`target_type_id`),
  CONSTRAINT `fk_target_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_target_subdistrict` FOREIGN KEY (`subdistrict_id`) REFERENCES `subdistricts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_target_type` FOREIGN KEY (`target_type_id`) REFERENCES `target_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_houses`
--

LOCK TABLES `target_houses` WRITE;
/*!40000 ALTER TABLE `target_houses` DISABLE KEYS */;
INSERT INTO `target_houses` VALUES (2,1,2,4,'บ้านนายสิทธิ์',7.63132428,100.05993385,'active','ชอบเอาปืนมาขู่ชาวบ้าน','94f2ce83dde325b10a1fa728cf69ddf7.png',NULL,7,'2026-06-22 06:19:14','2026-06-22 06:19:14');
/*!40000 ALTER TABLE `target_houses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_types`
--

DROP TABLE IF EXISTS `target_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marker_color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_types`
--

LOCK TABLES `target_types` WRITE;
/*!40000 ALTER TABLE `target_types` DISABLE KEYS */;
INSERT INTO `target_types` VALUES (1,'บ้านค้ายาเสพติด','#DC2626'),(2,'บ้านมั่วสุม','#F59E0B'),(3,'บ้านผู้มีอิทธิพล','#8B5CF6'),(4,'บ้านครอบครองอาวุธปืน','#374151');
/*!40000 ALTER TABLE `target_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'หน่วยงาน',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ตำแหน่ง',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `role_id` int NOT NULL,
  `district_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_user_role` (`role_id`),
  KEY `fk_user_district` (`district_id`),
  CONSTRAINT `fk_user_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'admin','$2y$10$7lhYlebJRHJO76P5EgGXB.Uxv8Jmvl5LiChPmHynlIzxKBB/BEJty','แอดมินระบบ',NULL,NULL,NULL,1,NULL,'2026-06-22 04:13:39'),(5,'governor','$2y$10$p9XjETuCKZ/BN0wWVJOBS.noLgpfOCcHVii8B0s26ZbTDt6qieY1K','ผู้ว่าราชการจังหวัด',NULL,NULL,NULL,2,NULL,'2026-06-22 04:13:39'),(6,'chief_mueang','$2y$10$sy0e1fjRlJuUqC2XrCZ9seTaIH4PltYfCSrDtUocIyv/UGtD2Riz2','นายอำเภอเมืองพัทลุง',NULL,NULL,NULL,3,1,'2026-06-22 04:13:39'),(7,'officer_mueang','$2y$10$oWK.hFnk8PfI9kE/PFmhief4tq1sTSDPcYI49ZmFwkrO5HZ1pggQm','จนท.เมืองพัทลุง','ที่ทำการปกครองอำเภอเมืองพัทลุง','ปลัดอำเภอ','0815333125',4,1,'2026-06-22 04:13:39'),(8,'test1','$2y$10$NsTfmUp/800M.7GU54klj.v4U7bETsZxnptmZU6uUZq/R5aOGmUBS','นายสมมุติ  สุดหล่อ','ที่ทำการปกครองอำเภอปากพะยูน','ปลัดอำเภอ','0815399135',4,3,'2026-06-22 06:25:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-22 14:02:50
