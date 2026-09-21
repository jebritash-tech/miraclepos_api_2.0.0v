-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: pharmacy_db
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `severity` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_created_at_action_index` (`created_at`,`action`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_model_type_index` (`model_type`),
  KEY `audit_logs_model_id_index` (`model_id`),
  KEY `audit_logs_branch_id_index` (`branch_id`),
  KEY `audit_logs_shift_id_index` (`shift_id`),
  KEY `audit_logs_severity_index` (`severity`)
) ENGINE=InnoDB AUTO_INCREMENT=572 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (435,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',21,'فتح وردية برصيد ابتدائي 10000',NULL,'{\"opening_cash\": 10000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,21,'info','2026-09-13 17:36:48','2026-09-13 17:36:48'),(436,2,'Cashier','cashier','created','App\\Models\\Sale',36,'إنشاء Sale',NULL,'{\"id\": 36, \"user_id\": 2, \"shift_id\": 21, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 19:36:51\", \"updated_at\": \"2026-09-13 19:36:51\", \"total_amount\": 21300, \"profit_amount\": 6100, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:51','2026-09-13 17:36:51'),(437,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',1,'تعديل MedicineBatch','{\"remaining_quantity\": \"10003.00\"}','{\"remaining_quantity\": 9903}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:51','2026-09-13 17:36:51'),(438,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',4,'تعديل MedicineBatch','{\"remaining_quantity\": \"2000.00\"}','{\"remaining_quantity\": 1980}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:51','2026-09-13 17:36:51'),(439,2,'Cashier','cashier','created','App\\Models\\Sale',37,'إنشاء Sale',NULL,'{\"id\": 37, \"user_id\": 2, \"shift_id\": 21, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 19:36:52\", \"updated_at\": \"2026-09-13 19:36:52\", \"total_amount\": 133300, \"profit_amount\": 47300, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:52','2026-09-13 17:36:52'),(440,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2485.00\"}','{\"remaining_quantity\": 2461}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:52','2026-09-13 17:36:52'),(441,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',9,'تعديل MedicineBatch','{\"remaining_quantity\": \"2800.00\"}','{\"remaining_quantity\": 2772}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:52','2026-09-13 17:36:52'),(442,2,'Cashier','cashier','created','App\\Models\\Sale',38,'إنشاء Sale',NULL,'{\"id\": 38, \"user_id\": 2, \"shift_id\": 21, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 19:36:54\", \"updated_at\": \"2026-09-13 19:36:54\", \"total_amount\": 87600, \"profit_amount\": 17600, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:54','2026-09-13 17:36:54'),(443,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',8,'تعديل MedicineBatch','{\"remaining_quantity\": \"100.00\"}','{\"remaining_quantity\": 98}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:36:54','2026-09-13 17:36:54'),(444,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',22,'فتح وردية برصيد ابتدائي 5000',NULL,'{\"opening_cash\": 5000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,22,'info','2026-09-13 17:36:58','2026-09-13 17:36:58'),(445,2,'Cashier','cashier','created','App\\Models\\Sale',39,'إنشاء Sale',NULL,'{\"id\": 39, \"user_id\": 2, \"shift_id\": 22, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 19:37:01\", \"updated_at\": \"2026-09-13 19:37:01\", \"total_amount\": 99200, \"profit_amount\": 35200, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:37:01','2026-09-13 17:37:01'),(446,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',7,'تعديل MedicineBatch','{\"remaining_quantity\": \"1400.00\"}','{\"remaining_quantity\": 1386}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:37:01','2026-09-13 17:37:01'),(447,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',10,'تعديل MedicineBatch','{\"remaining_quantity\": \"102.00\"}','{\"remaining_quantity\": 101}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:37:01','2026-09-13 17:37:01'),(448,2,'Cashier','cashier','created','App\\Models\\Sale',40,'إنشاء Sale',NULL,'{\"id\": 40, \"user_id\": 2, \"shift_id\": 22, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 19:37:02\", \"updated_at\": \"2026-09-13 19:37:02\", \"total_amount\": 65100, \"profit_amount\": 23100, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:37:02','2026-09-13 17:37:02'),(449,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2461.00\"}','{\"remaining_quantity\": 2425}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:37:02','2026-09-13 17:37:02'),(450,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',23,'فتح وردية برصيد ابتدائي 8000',NULL,'{\"opening_cash\": 8000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,23,'info','2026-09-13 17:47:31','2026-09-13 17:47:31'),(451,2,'Cashier','cashier','created','App\\Models\\Sale',41,'إنشاء Sale',NULL,'{\"id\": 41, \"user_id\": 2, \"shift_id\": 23, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 19:51:06\", \"updated_at\": \"2026-09-13 19:51:06\", \"total_amount\": 45000, \"profit_amount\": 16000, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:51:06','2026-09-13 17:51:06'),(452,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',9,'تعديل MedicineBatch','{\"remaining_quantity\": \"2772.00\"}','{\"remaining_quantity\": 2758}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 17:51:06','2026-09-13 17:51:06'),(453,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',23,'إغلاق وردية #23 — الرصيد الفعلي: 53000، الفرق: مطابق',NULL,'{\"difference\": 0, \"sales_count\": 1, \"closing_cash\": 53000, \"expected_cash\": 53000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,23,'info','2026-09-13 18:06:27','2026-09-13 18:06:27'),(454,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',24,'فتح وردية برصيد ابتدائي 0',NULL,'{\"opening_cash\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,24,'info','2026-09-13 18:09:37','2026-09-13 18:09:37'),(455,2,'Cashier','cashier','created','App\\Models\\Refund',122,'إنشاء Refund',NULL,'{\"id\": 122, \"amount\": 0, \"reason\": \"إرجاع Nexium 40mg (AstraZeneca) (strip)\", \"sale_id\": 41, \"created_at\": \"2026-09-13 20:10:42\", \"updated_at\": \"2026-09-13 20:10:42\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:10:42','2026-09-13 18:10:42'),(456,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',9,'تعديل MedicineBatch','{\"quantity\": \"2800.00\"}','{\"quantity\": 2802}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:10:42','2026-09-13 18:10:42'),(457,2,'Cashier','cashier','updated','App\\Models\\Refund',122,'تعديل Refund','{\"amount\": 0}','{\"amount\": 45000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-13 18:10:42','2026-09-13 18:10:42'),(458,2,'Cashier','cashier','created','App\\Models\\Refund',123,'إنشاء Refund',NULL,'{\"id\": 123, \"amount\": 0, \"reason\": \"إرجاع Panadol Extra (UK) (strip)\", \"sale_id\": 40, \"created_at\": \"2026-09-13 20:15:22\", \"updated_at\": \"2026-09-13 20:15:22\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:15:22','2026-09-13 18:15:22'),(459,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"quantity\": \"2485.00\"}','{\"quantity\": 2486}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:15:22','2026-09-13 18:15:22'),(460,2,'Cashier','cashier','updated','App\\Models\\Refund',123,'تعديل Refund','{\"amount\": 0}','{\"amount\": 21700}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-13 18:15:22','2026-09-13 18:15:22'),(461,2,'Cashier','cashier','created','App\\Models\\Refund',124,'إنشاء Refund',NULL,'{\"id\": 124, \"amount\": 0, \"reason\": \"إرجاع Panadol Extra (UK) (strip)\", \"sale_id\": 40, \"created_at\": \"2026-09-13 20:15:23\", \"updated_at\": \"2026-09-13 20:15:23\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:15:23','2026-09-13 18:15:23'),(462,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"quantity\": \"2486.00\"}','{\"quantity\": 2487}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:15:23','2026-09-13 18:15:23'),(463,2,'Cashier','cashier','updated','App\\Models\\Refund',124,'تعديل Refund','{\"amount\": 0}','{\"amount\": 21700}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-13 18:15:23','2026-09-13 18:15:23'),(464,2,'Cashier','cashier','created','App\\Models\\Sale',42,'إنشاء Sale',NULL,'{\"id\": 42, \"user_id\": 2, \"shift_id\": 24, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-13 20:23:53\", \"updated_at\": \"2026-09-13 20:23:53\", \"total_amount\": 43400, \"profit_amount\": 15400, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:23:53','2026-09-13 18:23:53'),(465,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2425.00\"}','{\"remaining_quantity\": 2401}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:23:53','2026-09-13 18:23:53'),(466,2,'Cashier','cashier','created','App\\Models\\Refund',125,'إنشاء Refund',NULL,'{\"id\": 125, \"amount\": 0, \"reason\": \"إرجاع Panadol Extra (UK) (Box)\", \"sale_id\": 42, \"created_at\": \"2026-09-13 20:23:55\", \"updated_at\": \"2026-09-13 20:23:55\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:23:55','2026-09-13 18:23:55'),(467,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"quantity\": \"2487.00\"}','{\"quantity\": 2488}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:23:55','2026-09-13 18:23:55'),(468,2,'Cashier','cashier','updated','App\\Models\\Refund',125,'تعديل Refund','{\"amount\": 0}','{\"amount\": 43400}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-13 18:23:55','2026-09-13 18:23:55'),(469,2,'Cashier','cashier','created','App\\Models\\Refund',126,'إنشاء Refund',NULL,'{\"id\": 126, \"amount\": 0, \"reason\": \"إرجاع باراسيتامول 500mg (box)\", \"sale_id\": 36, \"created_at\": \"2026-09-13 20:26:08\", \"updated_at\": \"2026-09-13 20:26:08\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:26:08','2026-09-13 18:26:08'),(470,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',1,'تعديل MedicineBatch','{\"quantity\": \"10003.00\"}','{\"quantity\": 10004}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-13 18:26:08','2026-09-13 18:26:08'),(471,2,'Cashier','cashier','updated','App\\Models\\Refund',126,'تعديل Refund','{\"amount\": 0}','{\"amount\": 16800}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-13 18:26:08','2026-09-13 18:26:08'),(473,1,'Admin','admin','price_unlocked','App\\Models\\MedicinePrice',1,'فتح قفل سعر باراسيتامول 500mg — أعيد الحساب تلقائياً',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-14 05:09:22','2026-09-14 05:09:22'),(474,1,'Admin','admin','price_locked','App\\Models\\MedicinePrice',1,'قفل سعر باراسيتامول 500mg عند 300 — السبب: test',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-14 05:13:09','2026-09-14 05:13:09'),(475,1,'Admin','admin','price_unlocked','App\\Models\\MedicinePrice',1,'فتح قفل سعر باراسيتامول 500mg — أعيد الحساب تلقائياً',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-14 05:17:28','2026-09-14 05:17:28'),(476,1,'Admin','admin','price_unlocked','App\\Models\\MedicinePrice',1,'فتح قفل سعر باراسيتامول 500mg — أعيد الحساب تلقائياً',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-14 05:17:42','2026-09-14 05:17:42'),(477,1,'Admin','admin','created','App\\Models\\Salary',1,'إنشاء Salary',NULL,'{\"id\": 1, \"year\": 2026, \"month\": 9, \"status\": \"pending\", \"user_id\": 2, \"allowances\": 0, \"created_at\": \"2026-09-14 08:33:05\", \"deductions\": 0, \"net_salary\": \"250000.00\", \"updated_at\": \"2026-09-14 08:33:05\", \"basic_salary\": \"250000.00\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-14 06:33:05','2026-09-14 06:33:05'),(478,1,'Admin','admin','created','App\\Models\\Expense',1,'إنشاء Expense',NULL,'{\"id\": 1, \"notes\": \"راتب شهر 9/2026\", \"title\": \"راتب شهر \", \"amount\": \"250000.00\", \"user_id\": 1, \"shift_id\": 24, \"created_at\": \"2026-09-14 08:33:37\", \"updated_at\": \"2026-09-14 08:33:37\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-14 06:33:37','2026-09-14 06:33:37'),(479,1,'Admin','admin','updated','App\\Models\\Salary',1,'تعديل Salary','{\"status\": \"pending\", \"paid_at\": null, \"bank_name\": null, \"bank_reference\": null, \"payment_method\": null}','{\"status\": \"paid\", \"paid_at\": \"2026-09-14 08:33:37\", \"bank_name\": \"بنك النيل\", \"bank_reference\": \"6060\", \"payment_method\": \"bank\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-14 06:33:37','2026-09-14 06:33:37'),(480,1,'Admin','admin','price_locked','App\\Models\\MedicinePrice',1,'قفل سعر باراسيتامول 500mg عند 500 — السبب: قرار تجاري',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-15 09:03:01','2026-09-15 09:03:01'),(481,1,'Admin','admin','price_unlocked','App\\Models\\MedicinePrice',1,'فتح قفل سعر باراسيتامول 500mg — أعيد الحساب تلقائياً',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-15 09:21:18','2026-09-15 09:21:18'),(482,1,'Admin','admin','price_unlocked','App\\Models\\MedicinePrice',1,'فتح قفل سعر باراسيتامول 500mg — أعيد الحساب تلقائياً',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-15 09:21:28','2026-09-15 09:21:28'),(483,1,'Admin','admin','bulk_pricing_recalculate',NULL,NULL,'إعادة حساب شاملة: 10 دفعة معالَجة، 0 متجاوزة، 24 سعر محدّث — السبب: test',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-15 09:22:42','2026-09-15 09:22:42'),(484,2,'Cashier','cashier','created','App\\Models\\Sale',43,'إنشاء Sale',NULL,'{\"id\": 43, \"user_id\": 2, \"shift_id\": 24, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-15 21:32:19\", \"updated_at\": \"2026-09-15 21:32:19\", \"total_amount\": 21700, \"profit_amount\": 7700, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:32:19','2026-09-15 19:32:19'),(485,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2352.00\"}','{\"remaining_quantity\": 2340}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:32:20','2026-09-15 19:32:20'),(486,2,'Cashier','cashier','created','App\\Models\\Refund',128,'إنشاء Refund',NULL,'{\"id\": 128, \"amount\": 0, \"reason\": \"إرجاع Panadol Extra (UK) (strip)\", \"sale_id\": 43, \"created_at\": \"2026-09-15 21:32:37\", \"updated_at\": \"2026-09-15 21:32:37\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:32:37','2026-09-15 19:32:37'),(487,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2340.00\"}','{\"remaining_quantity\": 2352}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:32:37','2026-09-15 19:32:37'),(488,2,'Cashier','cashier','updated','App\\Models\\Refund',128,'تعديل Refund','{\"amount\": 0}','{\"amount\": 21700}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-15 19:32:37','2026-09-15 19:32:37'),(489,2,'Cashier','cashier','created','App\\Models\\Sale',44,'إنشاء Sale',NULL,'{\"id\": 44, \"user_id\": 2, \"shift_id\": 24, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-15 21:50:43\", \"updated_at\": \"2026-09-15 21:50:43\", \"total_amount\": 21700, \"profit_amount\": 7700, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:50:43','2026-09-15 19:50:43'),(490,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2352.00\"}','{\"remaining_quantity\": 2340}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:50:43','2026-09-15 19:50:43'),(491,2,'Cashier','cashier','created','App\\Models\\Refund',129,'إنشاء Refund',NULL,'{\"id\": 129, \"amount\": 0, \"reason\": \"إرجاع Panadol Extra (UK) (strip)\", \"sale_id\": 44, \"created_at\": \"2026-09-15 21:51:01\", \"updated_at\": \"2026-09-15 21:51:01\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:51:01','2026-09-15 19:51:01'),(492,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2340.00\"}','{\"remaining_quantity\": 2352}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 19:51:01','2026-09-15 19:51:01'),(493,2,'Cashier','cashier','updated','App\\Models\\Refund',129,'تعديل Refund','{\"amount\": 0}','{\"amount\": 21700}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-15 19:51:01','2026-09-15 19:51:01'),(494,1,'Admin','admin','updated','App\\Models\\User',1,'تعديل User','{\"name\": \"Admin\"}','{\"name\": \"د.صلاح عبد المنعم\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-15 20:15:57','2026-09-15 20:15:57'),(495,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',24,'إغلاق وردية #24 — الرصيد الفعلي: 0، الفرق: مطابق',NULL,'{\"difference\": 0, \"sales_count\": 3, \"closing_cash\": 0, \"expected_cash\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,24,'info','2026-09-16 06:54:12','2026-09-16 06:54:12'),(496,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',25,'فتح وردية برصيد ابتدائي 0',NULL,'{\"opening_cash\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,25,'info','2026-09-16 06:56:24','2026-09-16 06:56:24'),(497,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',27,'فتح وردية برصيد ابتدائي 0',NULL,'{\"opening_cash\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,27,'info','2026-09-16 06:56:25','2026-09-16 06:56:25'),(498,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',26,'فتح وردية برصيد ابتدائي 0',NULL,'{\"opening_cash\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,26,'info','2026-09-16 06:56:25','2026-09-16 06:56:25'),(499,2,'Cashier','cashier','created','App\\Models\\Sale',45,'إنشاء Sale',NULL,'{\"id\": 45, \"user_id\": 2, \"shift_id\": 25, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 08:56:27\", \"updated_at\": \"2026-09-16 08:56:27\", \"total_amount\": 45500, \"profit_amount\": 14500, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:27','2026-09-16 06:56:27'),(500,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2352.00\"}','{\"remaining_quantity\": 2340}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:27','2026-09-16 06:56:27'),(501,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',1,'تعديل MedicineBatch','{\"remaining_quantity\": \"10000.00\"}','{\"remaining_quantity\": 9900}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:27','2026-09-16 06:56:27'),(502,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',2,'تعديل MedicineBatch','{\"remaining_quantity\": \"2000.00\"}','{\"remaining_quantity\": 1980}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:27','2026-09-16 06:56:27'),(503,2,'Cashier','cashier','created','App\\Models\\Sale',46,'إنشاء Sale',NULL,'{\"id\": 46, \"user_id\": 2, \"shift_id\": 25, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 08:56:28\", \"updated_at\": \"2026-09-16 08:56:28\", \"total_amount\": 10300, \"profit_amount\": 2600, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:28','2026-09-16 06:56:28'),(504,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',4,'تعديل MedicineBatch','{\"remaining_quantity\": \"1980.00\"}','{\"remaining_quantity\": 1960}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:28','2026-09-16 06:56:28'),(505,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',3,'تعديل MedicineBatch','{\"remaining_quantity\": \"3000.00\"}','{\"remaining_quantity\": 2970}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:56:28','2026-09-16 06:56:28'),(506,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:58:01','2026-09-16 06:58:01'),(507,2,'Cashier','cashier','created','App\\Models\\Withdrawal',3,'إنشاء Withdrawal',NULL,'{\"id\": 3, \"amount\": 1000, \"reason\": \"فطور عمال\", \"user_id\": 2, \"shift_id\": 25, \"created_at\": \"2026-09-16 08:58:03\", \"updated_at\": \"2026-09-16 08:58:03\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:58:03','2026-09-16 06:58:03'),(508,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:58:45','2026-09-16 06:58:45'),(509,2,'Cashier','cashier','created','App\\Models\\Expense',2,'إنشاء Expense',NULL,'{\"id\": 2, \"notes\": \"كهرباء\", \"title\": \"كهرباء\", \"amount\": 20000, \"user_id\": 2, \"shift_id\": 25, \"created_at\": \"2026-09-16 08:58:47\", \"updated_at\": \"2026-09-16 08:58:47\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:58:47','2026-09-16 06:58:47'),(510,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 06:59:14','2026-09-16 06:59:14'),(511,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',25,'إغلاق وردية #25 — الرصيد الفعلي: 36800، الفرق: زيادة +2,000.00',NULL,'{\"difference\": 2000, \"sales_count\": 2, \"closing_cash\": 36800, \"expected_cash\": 34800}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,25,'info','2026-09-16 06:59:45','2026-09-16 06:59:45'),(512,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:00:34','2026-09-16 07:00:34'),(513,2,'Cashier','cashier','created','App\\Models\\Sale',47,'إنشاء Sale',NULL,'{\"id\": 47, \"user_id\": 2, \"shift_id\": 26, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 09:04:09\", \"updated_at\": \"2026-09-16 09:04:09\", \"total_amount\": 87200, \"profit_amount\": 24200, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:09','2026-09-16 07:04:09'),(514,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2340.00\"}','{\"remaining_quantity\": 2316}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:09','2026-09-16 07:04:09'),(515,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',8,'تعديل MedicineBatch','{\"remaining_quantity\": \"98.00\"}','{\"remaining_quantity\": 97}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:09','2026-09-16 07:04:09'),(516,2,'Cashier','cashier','created','App\\Models\\Sale',48,'إنشاء Sale',NULL,'{\"id\": 48, \"user_id\": 2, \"shift_id\": 26, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 09:04:11\", \"updated_at\": \"2026-09-16 09:04:11\", \"total_amount\": 36100, \"profit_amount\": 10000, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:11','2026-09-16 07:04:11'),(517,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',1,'تعديل MedicineBatch','{\"remaining_quantity\": \"9900.00\"}','{\"remaining_quantity\": 9800}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:11','2026-09-16 07:04:11'),(518,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',3,'تعديل MedicineBatch','{\"remaining_quantity\": \"2970.00\"}','{\"remaining_quantity\": 2940}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:11','2026-09-16 07:04:11'),(519,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',4,'تعديل MedicineBatch','{\"remaining_quantity\": \"1960.00\"}','{\"remaining_quantity\": 1900}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:11','2026-09-16 07:04:11'),(520,2,'Cashier','cashier','created','App\\Models\\Refund',130,'إنشاء Refund',NULL,'{\"id\": 130, \"amount\": 0, \"reason\": \"إرجاع فيتامين سي 500mg (Tube)\", \"sale_id\": 48, \"created_at\": \"2026-09-16 09:04:12\", \"updated_at\": \"2026-09-16 09:04:12\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:12','2026-09-16 07:04:12'),(521,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',4,'تعديل MedicineBatch','{\"remaining_quantity\": \"1900.00\"}','{\"remaining_quantity\": 1940}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:12','2026-09-16 07:04:12'),(522,2,'Cashier','cashier','updated','App\\Models\\Refund',130,'تعديل Refund','{\"amount\": 0}','{\"amount\": 9000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-16 07:04:12','2026-09-16 07:04:12'),(523,2,'Cashier','cashier','created','App\\Models\\Refund',131,'إنشاء Refund',NULL,'{\"id\": 131, \"amount\": 0, \"reason\": \"إرجاع ميتفورمين 850mg (Strip)\", \"sale_id\": 48, \"created_at\": \"2026-09-16 09:04:13\", \"updated_at\": \"2026-09-16 09:04:13\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:13','2026-09-16 07:04:13'),(524,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',3,'تعديل MedicineBatch','{\"remaining_quantity\": \"2940.00\"}','{\"remaining_quantity\": 2955}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:13','2026-09-16 07:04:13'),(525,2,'Cashier','cashier','updated','App\\Models\\Refund',131,'تعديل Refund','{\"amount\": 0}','{\"amount\": 2900}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-16 07:04:13','2026-09-16 07:04:13'),(526,2,'Cashier','cashier','created','App\\Models\\Expense',3,'إنشاء Expense',NULL,'{\"id\": 3, \"notes\": \"صيانة عامة\", \"title\": \"صيانة\", \"amount\": 30000, \"user_id\": 2, \"shift_id\": 26, \"created_at\": \"2026-09-16 09:04:14\", \"updated_at\": \"2026-09-16 09:04:14\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:04:14','2026-09-16 07:04:14'),(527,1,'د.صلاح عبد المنعم','admin','updated','App\\Models\\MedicineBatch',10,'تعديل MedicineBatch','{\"quantity\": \"100.00\", \"remaining_quantity\": \"99.00\"}','{\"quantity\": 100, \"remaining_quantity\": 100}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:55:22','2026-09-16 07:55:22'),(528,1,'د.صلاح عبد المنعم','admin','updated','App\\Models\\MedicineBatch',10,'تعديل MedicineBatch','{\"quantity\": \"100.00\", \"remaining_quantity\": \"100.00\"}','{\"quantity\": 99, \"remaining_quantity\": 99}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:58:11','2026-09-16 07:58:11'),(529,1,'د.صلاح عبد المنعم','admin','updated','App\\Models\\MedicineBatch',10,'تعديل MedicineBatch','{\"quantity\": \"99.00\", \"remaining_quantity\": \"99.00\"}','{\"quantity\": 0, \"remaining_quantity\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 07:58:38','2026-09-16 07:58:38'),(530,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',26,'إغلاق وردية #26 — الرصيد الفعلي: 81400، الفرق: زيادة +11,900.00',NULL,'{\"difference\": 11900, \"sales_count\": 2, \"closing_cash\": 81400, \"expected_cash\": 69500}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,26,'info','2026-09-16 15:07:33','2026-09-16 15:07:33'),(531,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 15:10:05','2026-09-16 15:10:05'),(532,2,'Cashier','cashier','created','App\\Models\\Withdrawal',4,'إنشاء Withdrawal',NULL,'{\"id\": 4, \"amount\": 5000, \"reason\": \"اختبار\", \"user_id\": 2, \"shift_id\": 27, \"created_at\": \"2026-09-16 17:10:06\", \"updated_at\": \"2026-09-16 17:10:06\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 15:10:06','2026-09-16 15:10:06'),(533,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN على حساب مقفل — Cashier',NULL,'{\"reason\": \"locked\", \"attempts_left\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:33:22','2026-09-16 15:33:22'),(534,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN فاشلة — Cashier (متبقي 4 محاولات)',NULL,'{\"reason\": \"wrong\", \"attempts_left\": 4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:34:46','2026-09-16 15:34:46'),(535,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN فاشلة — Cashier (متبقي 3 محاولات)',NULL,'{\"reason\": \"wrong\", \"attempts_left\": 3}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:35:43','2026-09-16 15:35:43'),(536,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN فاشلة — Cashier (متبقي 2 محاولات)',NULL,'{\"reason\": \"wrong\", \"attempts_left\": 2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:35:48','2026-09-16 15:35:48'),(537,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN فاشلة — Cashier (متبقي 1 محاولات)',NULL,'{\"reason\": \"wrong\", \"attempts_left\": 1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:35:52','2026-09-16 15:35:52'),(538,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN على حساب مقفل — Cashier',NULL,'{\"reason\": \"locked\", \"attempts_left\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:35:57','2026-09-16 15:35:57'),(539,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN على حساب مقفل — Cashier',NULL,'{\"reason\": \"locked\", \"attempts_left\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:36:42','2026-09-16 15:36:42'),(540,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح — Cashier',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 15:40:37','2026-09-16 15:40:37'),(541,2,'Cashier','cashier','created','App\\Models\\Expense',4,'إنشاء Expense',NULL,'{\"id\": 4, \"notes\": null, \"title\": \"اختبار\", \"amount\": 1000, \"user_id\": 2, \"shift_id\": 27, \"created_at\": \"2026-09-16 17:40:39\", \"updated_at\": \"2026-09-16 17:40:39\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 15:40:39','2026-09-16 15:40:39'),(542,2,'Cashier','cashier','pin_failed','App\\Models\\User',2,'محاولة PIN فاشلة — Cashier (متبقي 4 محاولات)',NULL,'{\"reason\": \"wrong\", \"attempts_left\": 4}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'warning','2026-09-16 15:41:30','2026-09-16 15:41:30'),(543,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح — Cashier',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 15:41:41','2026-09-16 15:41:41'),(544,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',27,'إغلاق وردية #27 — الرصيد الفعلي: 0، الفرق: زيادة +6,000.00',NULL,'{\"difference\": 6000, \"sales_count\": 0, \"closing_cash\": 0, \"expected_cash\": -6000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,27,'info','2026-09-16 15:46:33','2026-09-16 15:46:33'),(545,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',28,'فتح وردية برصيد ابتدائي 10000',NULL,'{\"opening_cash\": 10000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,28,'info','2026-09-16 15:50:28','2026-09-16 15:50:28'),(546,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',28,'إغلاق وردية #28 — الرصيد الفعلي: 10000، الفرق: مطابق',NULL,'{\"difference\": 0, \"sales_count\": 0, \"closing_cash\": 10000, \"expected_cash\": 10000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,28,'info','2026-09-16 16:07:05','2026-09-16 16:07:05'),(547,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح — Cashier',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:08:57','2026-09-16 16:08:57'),(548,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح — Cashier',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:09:29','2026-09-16 16:09:29'),(549,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح — Cashier',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:10:07','2026-09-16 16:10:07'),(550,2,'Cashier','cashier','pin_verified','App\\Models\\User',2,'تحقق PIN ناجح — Cashier',NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:15:17','2026-09-16 16:15:17'),(551,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',29,'فتح وردية برصيد ابتدائي 10000',NULL,'{\"opening_cash\": 10000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,29,'info','2026-09-16 16:20:48','2026-09-16 16:20:48'),(552,2,'Cashier','cashier','created','App\\Models\\Sale',49,'إنشاء Sale',NULL,'{\"id\": 49, \"user_id\": 2, \"shift_id\": 29, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 18:20:51\", \"updated_at\": \"2026-09-16 18:20:51\", \"total_amount\": 16800, \"profit_amount\": 4800, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:20:51','2026-09-16 16:20:51'),(553,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',1,'تعديل MedicineBatch','{\"remaining_quantity\": \"9800.00\"}','{\"remaining_quantity\": 9700}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:20:51','2026-09-16 16:20:51'),(554,2,'Cashier','cashier','created','App\\Models\\Sale',50,'إنشاء Sale',NULL,'{\"id\": 50, \"user_id\": 2, \"shift_id\": 29, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 18:20:52\", \"updated_at\": \"2026-09-16 18:20:52\", \"total_amount\": 43400, \"profit_amount\": 15400, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:20:52','2026-09-16 16:20:52'),(555,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',6,'تعديل MedicineBatch','{\"remaining_quantity\": \"2316.00\"}','{\"remaining_quantity\": 2292}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:20:52','2026-09-16 16:20:52'),(556,2,'Cashier','cashier','created','App\\Models\\Expense',5,'إنشاء Expense',NULL,'{\"id\": 5, \"notes\": \"الاختبار الشامل\", \"title\": \"كهرباء\", \"amount\": 2000, \"user_id\": 2, \"shift_id\": 29, \"created_at\": \"2026-09-16 18:20:53\", \"updated_at\": \"2026-09-16 18:20:53\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:20:53','2026-09-16 16:20:53'),(557,2,'Cashier','cashier','created','App\\Models\\Withdrawal',5,'إنشاء Withdrawal',NULL,'{\"id\": 5, \"amount\": 500, \"reason\": \"فطور موظفين\", \"user_id\": 2, \"shift_id\": 29, \"created_at\": \"2026-09-16 18:20:54\", \"updated_at\": \"2026-09-16 18:20:54\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:20:55','2026-09-16 16:20:55'),(558,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',29,'إغلاق وردية #29 — الرصيد الفعلي: 69200، الفرق: زيادة +1,500.00',NULL,'{\"difference\": 1500, \"sales_count\": 2, \"closing_cash\": 69200, \"expected_cash\": 67700}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,29,'info','2026-09-16 16:20:59','2026-09-16 16:20:59'),(559,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',30,'فتح وردية برصيد ابتدائي 5000',NULL,'{\"opening_cash\": 5000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,30,'info','2026-09-16 16:21:01','2026-09-16 16:21:01'),(560,2,'Cashier','cashier','created','App\\Models\\Sale',51,'إنشاء Sale',NULL,'{\"id\": 51, \"user_id\": 2, \"shift_id\": 30, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 18:21:03\", \"updated_at\": \"2026-09-16 18:21:03\", \"total_amount\": 89900, \"profit_amount\": 31900, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:03','2026-09-16 16:21:03'),(561,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',9,'تعديل MedicineBatch','{\"remaining_quantity\": \"2772.00\"}','{\"remaining_quantity\": 2744}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:03','2026-09-16 16:21:03'),(562,2,'Cashier','cashier','created','App\\Models\\Sale',52,'إنشاء Sale',NULL,'{\"id\": 52, \"user_id\": 2, \"shift_id\": 30, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 18:21:04\", \"updated_at\": \"2026-09-16 18:21:04\", \"total_amount\": 9000, \"profit_amount\": 2600, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:04','2026-09-16 16:21:04'),(563,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',4,'تعديل MedicineBatch','{\"remaining_quantity\": \"1940.00\"}','{\"remaining_quantity\": 1900}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:04','2026-09-16 16:21:04'),(564,2,'Cashier','cashier','created','App\\Models\\Refund',132,'إنشاء Refund',NULL,'{\"id\": 132, \"amount\": 0, \"reason\": \"إرجاع باراسيتامول 500mg (Box)\", \"sale_id\": 49, \"created_at\": \"2026-09-16 18:21:06\", \"updated_at\": \"2026-09-16 18:21:06\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:06','2026-09-16 16:21:06'),(565,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',1,'تعديل MedicineBatch','{\"remaining_quantity\": \"9700.00\"}','{\"remaining_quantity\": 9800}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:06','2026-09-16 16:21:06'),(566,2,'Cashier','cashier','updated','App\\Models\\Refund',132,'تعديل Refund','{\"amount\": 0}','{\"amount\": 16800}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'critical','2026-09-16 16:21:06','2026-09-16 16:21:06'),(567,2,'Cashier','cashier','created','App\\Models\\Expense',6,'إنشاء Expense',NULL,'{\"id\": 6, \"notes\": \"الاختبار الشامل\", \"title\": \"ماء\", \"amount\": 300, \"user_id\": 2, \"shift_id\": 30, \"created_at\": \"2026-09-16 18:21:07\", \"updated_at\": \"2026-09-16 18:21:07\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:21:07','2026-09-16 16:21:07'),(568,2,'Cashier','cashier','shift_closed','App\\Models\\Shift',30,'إغلاق وردية #30 — الرصيد الفعلي: 86800، الفرق: عجز -16,800.00',NULL,'{\"difference\": -16800, \"sales_count\": 2, \"closing_cash\": 86800, \"expected_cash\": 103600}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,30,'info','2026-09-16 16:21:09','2026-09-16 16:21:09'),(569,2,'Cashier','cashier','shift_opened','App\\Models\\Shift',31,'فتح وردية برصيد ابتدائي 8000',NULL,'{\"opening_cash\": 8000}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,31,'info','2026-09-16 16:21:48','2026-09-16 16:21:48'),(570,2,'Cashier','cashier','created','App\\Models\\Sale',53,'إنشاء Sale',NULL,'{\"id\": 53, \"user_id\": 2, \"shift_id\": 31, \"bank_name\": null, \"branch_id\": 1, \"bank_notes\": null, \"created_at\": \"2026-09-16 18:22:11\", \"updated_at\": \"2026-09-16 18:22:11\", \"total_amount\": 65100, \"profit_amount\": 23100, \"bank_reference\": null, \"payment_method\": \"cash\", \"bank_transfer_date\": null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:22:11','2026-09-16 16:22:11'),(571,2,'Cashier','cashier','updated','App\\Models\\MedicineBatch',7,'تعديل MedicineBatch','{\"remaining_quantity\": \"1386.00\"}','{\"remaining_quantity\": 1372}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',1,NULL,'info','2026-09-16 16:22:11','2026-09-16 16:22:11');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'الفرع الرئيسي','الخرطوم','2026-09-12 12:03:41','2026-09-12 12:03:41',NULL);
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('miraclepos-cache-settings.all','a:27:{s:13:\"pharmacy.name\";a:4:{s:5:\"value\";s:41:\"صيدلية مركز صحي التلال\";s:4:\"type\";s:6:\"string\";s:5:\"group\";s:7:\"general\";s:5:\"label\";s:23:\"اسم الصيدلية\";}s:14:\"pharmacy.phone\";a:4:{s:5:\"value\";s:11:\"09123456789\";s:4:\"type\";s:6:\"string\";s:5:\"group\";s:7:\"general\";s:5:\"label\";s:19:\"رقم الهاتف\";}s:16:\"pharmacy.address\";a:4:{s:5:\"value\";s:49:\"الخرطوم - شرق النيل - التلال\";s:4:\"type\";s:6:\"string\";s:5:\"group\";s:7:\"general\";s:5:\"label\";s:14:\"العنوان\";}s:19:\"pharmacy.tax_number\";a:4:{s:5:\"value\";s:8:\"12345678\";s:4:\"type\";s:6:\"string\";s:5:\"group\";s:7:\"general\";s:5:\"label\";s:25:\"الرقم الضريبي\";}s:17:\"pharmacy.currency\";a:4:{s:5:\"value\";s:5:\"ج.س\";s:4:\"type\";s:6:\"string\";s:5:\"group\";s:7:\"general\";s:5:\"label\";s:12:\"العملة\";}s:17:\"pharmacy.logo_url\";a:4:{s:5:\"value\";s:0:\"\";s:4:\"type\";s:6:\"string\";s:5:\"group\";s:7:\"general\";s:5:\"label\";s:25:\"شعار الصيدلية\";}s:11:\"pin.enabled\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:23:\"تفعيل نظام PIN\";}s:10:\"pin.length\";a:4:{s:5:\"value\";s:1:\"4\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:21:\"عدد أرقام PIN\";}s:17:\"pin.on_shift_open\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:24:\"عند فتح وردية\";}s:18:\"pin.on_shift_close\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:28:\"عند إغلاق وردية\";}s:15:\"pin.on_withdraw\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:30:\"عند السحب النقدي\";}s:14:\"pin.on_expense\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:28:\"عند إضافة مصروف\";}s:19:\"pin.on_debt_payment\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:22:\"عند سداد دين\";}s:16:\"pin.on_void_sale\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:30:\"عند إلغاء فاتورة\";}s:19:\"pin.on_price_change\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:24:\"عند تغيير سعر\";}s:17:\"pin.on_every_sale\";a:4:{s:5:\"value\";s:1:\"0\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:29:\"عند كل عملية بيع\";}s:16:\"pin.max_attempts\";a:4:{s:5:\"value\";s:1:\"5\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:40:\"الحد الأقصى للمحاولات\";}s:19:\"pin.lockout_minutes\";a:4:{s:5:\"value\";s:1:\"5\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:3:\"pin\";s:5:\"label\";s:48:\"مدة القفل بعد الفشل (دقائق)\";}s:13:\"print.enabled\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:5:\"print\";s:5:\"label\";s:25:\"تفعيل الطباعة\";}s:21:\"print.auto_after_sale\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:5:\"print\";s:5:\"label\";s:43:\"طباعة تلقائية بعد البيع\";}s:11:\"print.width\";a:4:{s:5:\"value\";s:2:\"80\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:5:\"print\";s:5:\"label\";s:22:\"عرض الورق (mm)\";}s:19:\"print.allow_reprint\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:5:\"print\";s:5:\"label\";s:40:\"السماح بإعادة الطباعة\";}s:22:\"print.auto_close_shift\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:5:\"print\";s:5:\"label\";s:47:\"طباعة تقرير إغلاق الوردية\";}s:32:\"security.session_timeout_minutes\";a:4:{s:5:\"value\";s:3:\"720\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:8:\"security\";s:5:\"label\";s:47:\"مهلة انتهاء الجلسة (دقائق)\";}s:37:\"security.require_password_change_days\";a:4:{s:5:\"value\";s:1:\"0\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:8:\"security\";s:5:\"label\";s:59:\"إلزام تغيير كلمة المرور كل (أيام)\";}s:19:\"backup.auto_enabled\";a:4:{s:5:\"value\";s:1:\"1\";s:4:\"type\";s:7:\"boolean\";s:5:\"group\";s:6:\"backup\";s:5:\"label\";s:34:\"نسخ احتياطي تلقائي\";}s:16:\"backup.keep_days\";a:4:{s:5:\"value\";s:2:\"30\";s:4:\"type\";s:7:\"integer\";s:5:\"group\";s:6:\"backup\";s:5:\"label\";s:34:\"مدة الاحتفاظ (أيام)\";}}',1789590473);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_movements`
--

DROP TABLE IF EXISTS `cash_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('withdraw','debt_payment','expense','opening','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_movements_shift_id_foreign` (`shift_id`),
  KEY `cash_movements_user_id_foreign` (`user_id`),
  CONSTRAINT `cash_movements_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_movements`
--

LOCK TABLES `cash_movements` WRITE;
/*!40000 ALTER TABLE `cash_movements` DISABLE KEYS */;
INSERT INTO `cash_movements` VALUES (1,25,2,'debt_payment',2000.00,'فطور','2026-09-16 06:59:16','2026-09-16 06:59:16'),(2,27,2,'debt_payment',500.00,'اختبار','2026-09-16 15:41:42','2026-09-16 15:41:42'),(3,29,2,'debt_payment',1500.00,'سداد دفعة اولي','2026-09-16 16:20:56','2026-09-16 16:20:56');
/*!40000 ALTER TABLE `cash_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_name_index` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'مسكنات وخافضات حرارة','2026-09-12 12:03:41','2026-09-12 12:03:41',NULL),(2,'مضادات حيوية','2026-09-12 12:03:41','2026-09-12 12:03:41',NULL),(3,'فيتامينات ومكملات غذائية','2026-09-12 12:03:41','2026-09-12 12:03:41',NULL),(4,'أدوية الأطفال والرضع','2026-09-12 12:03:41','2026-09-12 12:03:41',NULL),(5,'أدوية ضغط الدم والقلب','2026-09-12 12:03:41','2026-09-12 12:03:41',NULL),(6,'أدوية السكري','2026-09-12 12:03:42','2026-09-12 12:03:42',NULL),(7,'عناية بالبشرة والتجميل','2026-09-12 12:03:42','2026-09-12 12:03:42',NULL),(8,'مضادات الحساسية والتهاب الجيوب','2026-09-12 12:03:42','2026-09-12 12:03:42',NULL),(9,'أدوية الجهاز الهضمي ومضادات الحموضة','2026-09-12 12:03:42','2026-09-12 12:03:42',NULL),(10,'قطرات ومراهم العين والأذن','2026-09-12 12:03:42','2026-09-12 12:03:42',NULL),(11,'مستلزمات طبية وأجهزة قياس','2026-09-12 12:03:43','2026-09-12 12:03:43',NULL),(12,'مضادات الالتهاب ومسكنات العظام','2026-09-12 12:03:43','2026-09-12 12:03:43',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debt_payments`
--

DROP TABLE IF EXISTS `debt_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `debt_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `debt_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `debt_payments_debt_id_foreign` (`debt_id`),
  KEY `debt_payments_user_id_foreign` (`user_id`),
  CONSTRAINT `debt_payments_debt_id_foreign` FOREIGN KEY (`debt_id`) REFERENCES `debts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `debt_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debt_payments`
--

LOCK TABLES `debt_payments` WRITE;
/*!40000 ALTER TABLE `debt_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `debt_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debts`
--

DROP TABLE IF EXISTS `debts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `debts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `sale_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `remaining_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','partial','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `debts_user_id_foreign` (`user_id`),
  KEY `debts_branch_id_foreign` (`branch_id`),
  KEY `debts_sale_id_foreign` (`sale_id`),
  CONSTRAINT `debts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `debts_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `debts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debts`
--

LOCK TABLES `debts` WRITE;
/*!40000 ALTER TABLE `debts` DISABLE KEYS */;
/*!40000 ALTER TABLE `debts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_debts`
--

DROP TABLE IF EXISTS `employee_debts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_debts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `shift_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','partial','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_debts_user_id_foreign` (`user_id`),
  KEY `employee_debts_shift_id_foreign` (`shift_id`),
  CONSTRAINT `employee_debts_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_debts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_debts`
--

LOCK TABLES `employee_debts` WRITE;
/*!40000 ALTER TABLE `employee_debts` DISABLE KEYS */;
INSERT INTO `employee_debts` VALUES (3,2,25,1000.00,0.00,'فطور عمال','pending','2026-09-16 06:58:03','2026-09-16 06:58:03'),(4,2,27,5000.00,0.00,'اختبار','pending','2026-09-16 15:10:06','2026-09-16 15:10:06'),(5,2,29,500.00,0.00,'فطور موظفين','pending','2026-09-16 16:20:55','2026-09-16 16:20:55');
/*!40000 ALTER TABLE `employee_debts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_shift_id_foreign` (`shift_id`),
  KEY `expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `expenses_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,24,1,250000.00,'راتب شهر ','راتب شهر 9/2026','2026-09-14 06:33:37','2026-09-14 06:33:37'),(2,25,2,20000.00,'كهرباء','كهرباء','2026-09-16 06:58:47','2026-09-16 06:58:47'),(3,26,2,30000.00,'صيانة','صيانة عامة','2026-09-16 07:04:14','2026-09-16 07:04:14'),(4,27,2,1000.00,'اختبار',NULL,'2026-09-16 15:40:39','2026-09-16 15:40:39'),(5,29,2,2000.00,'كهرباء','الاختبار الشامل','2026-09-16 16:20:53','2026-09-16 16:20:53'),(6,30,2,300.00,'ماء','الاختبار الشامل','2026-09-16 16:21:07','2026-09-16 16:21:07');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `purchased_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `factor` decimal(10,2) NOT NULL DEFAULT '1.00',
  `reserved_quantity` int NOT NULL DEFAULT '0',
  `available_quantity` int NOT NULL DEFAULT '0',
  `minimum_quantity` int NOT NULL DEFAULT '1000',
  `maximum_quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_medicine_id_branch_id_unique` (`medicine_id`,`branch_id`),
  KEY `inventories_branch_id_foreign` (`branch_id`),
  CONSTRAINT `inventories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventories_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
INSERT INTO `inventories` VALUES (1,1,1,8777,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:28:55','2026-09-16 16:20:51'),(2,2,1,1938,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:30:18','2026-09-16 06:56:27'),(3,3,1,2940,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:31:24','2026-09-16 07:04:11'),(4,4,1,1779,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:32:56','2026-09-16 16:21:04'),(5,5,1,97,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:35:20','2026-09-13 15:00:33'),(6,6,1,1216,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:36:15','2026-09-16 16:20:52'),(7,7,1,1323,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:37:40','2026-09-16 16:22:11'),(8,8,1,81,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:38:39','2026-09-16 07:04:09'),(9,9,1,2667,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:39:48','2026-09-16 16:21:03'),(10,10,1,0,0.00,1.00,0,0,1000,NULL,'2026-09-12 12:41:00','2026-09-16 07:58:38');
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_logs`
--

DROP TABLE IF EXISTS `inventory_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medicine_batch_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_changed` int NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_logs_medicine_batch_id_foreign` (`medicine_batch_id`),
  CONSTRAINT `inventory_logs_medicine_batch_id_foreign` FOREIGN KEY (`medicine_batch_id`) REFERENCES `medicine_batches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_logs`
--

LOCK TABLES `inventory_logs` WRITE;
/*!40000 ALTER TABLE `inventory_logs` DISABLE KEYS */;
INSERT INTO `inventory_logs` VALUES (62,9,'REFUND',2,'إرجاع صنف من فاتورة #41','2026-09-13 18:10:42','2026-09-13 18:10:42'),(63,6,'REFUND',1,'إرجاع صنف من فاتورة #40','2026-09-13 18:15:22','2026-09-13 18:15:22'),(64,6,'REFUND',1,'إرجاع صنف من فاتورة #40','2026-09-13 18:15:23','2026-09-13 18:15:23'),(65,6,'REFUND',1,'إرجاع صنف من فاتورة #42','2026-09-13 18:23:55','2026-09-13 18:23:55'),(66,1,'REFUND',1,'إرجاع صنف من فاتورة #36','2026-09-13 18:26:08','2026-09-13 18:26:08'),(67,6,'REFUND',12,'إرجاع صنف من فاتورة #43','2026-09-15 19:32:37','2026-09-15 19:32:37'),(68,6,'REFUND',12,'إرجاع صنف من فاتورة #44','2026-09-15 19:51:01','2026-09-15 19:51:01'),(69,4,'REFUND',40,'إرجاع صنف من فاتورة #48','2026-09-16 07:04:12','2026-09-16 07:04:12'),(70,3,'REFUND',15,'إرجاع صنف من فاتورة #48','2026-09-16 07:04:13','2026-09-16 07:04:13'),(71,1,'REFUND',100,'إرجاع صنف من فاتورة #49','2026-09-16 16:21:06','2026-09-16 16:21:06');
/*!40000 ALTER TABLE `inventory_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `type` enum('purchase','sale','purchase_return','sale_return','adjustment','transfer_in','transfer_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `balance_after` int NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_batch_id_foreign` (`batch_id`),
  KEY `inventory_movements_branch_id_foreign` (`branch_id`),
  KEY `inventory_movements_medicine_id_branch_id_index` (`medicine_id`,`branch_id`),
  CONSTRAINT `inventory_movements_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_movements`
--

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
INSERT INTO `inventory_movements` VALUES (1,1,1,1,'purchase',10000,10000,'App\\Models\\Purchase',1,NULL,'2026-09-12 12:28:55','2026-09-12 12:28:55'),(2,2,2,1,'purchase',2000,2000,'App\\Models\\Purchase',2,NULL,'2026-09-12 12:30:18','2026-09-12 12:30:18'),(3,3,3,1,'purchase',3000,3000,'App\\Models\\Purchase',3,NULL,'2026-09-12 12:31:24','2026-09-12 12:31:24'),(4,4,4,1,'purchase',2000,2000,'App\\Models\\Purchase',4,NULL,'2026-09-12 12:32:56','2026-09-12 12:32:56'),(5,5,5,1,'purchase',100,100,'App\\Models\\Purchase',5,NULL,'2026-09-12 12:35:20','2026-09-12 12:35:20'),(6,6,6,1,'purchase',2400,2400,'App\\Models\\Purchase',6,NULL,'2026-09-12 12:36:15','2026-09-12 12:36:15'),(7,7,7,1,'purchase',1400,1400,'App\\Models\\Purchase',7,NULL,'2026-09-12 12:37:40','2026-09-12 12:37:40'),(8,8,8,1,'purchase',100,100,'App\\Models\\Purchase',8,NULL,'2026-09-12 12:38:39','2026-09-12 12:38:39'),(9,9,9,1,'purchase',2800,2800,'App\\Models\\Purchase',9,NULL,'2026-09-12 12:39:48','2026-09-12 12:39:48'),(10,10,10,1,'purchase',100,100,'App\\Models\\Purchase',10,NULL,'2026-09-12 12:41:00','2026-09-12 12:41:00');
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicine_batches`
--

DROP TABLE IF EXISTS `medicine_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicine_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` bigint unsigned NOT NULL,
  `purchase_item_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `purchase_unit_id` bigint unsigned NOT NULL,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `buy_price` decimal(12,2) NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `remaining_quantity` decimal(12,2) NOT NULL,
  `pricing_rule_id` bigint unsigned DEFAULT NULL,
  `custom_markup_percent` decimal(8,2) DEFAULT NULL COMMENT 'نسبة ربح مخصصة لهذه الدفعة فقط',
  `pricing_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medicine_batches_medicine_id_foreign` (`medicine_id`),
  KEY `medicine_batches_purchase_item_id_foreign` (`purchase_item_id`),
  KEY `medicine_batches_purchase_unit_id_foreign` (`purchase_unit_id`),
  KEY `medicine_batches_pricing_rule_id_foreign` (`pricing_rule_id`),
  CONSTRAINT `medicine_batches_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_batches_pricing_rule_id_foreign` FOREIGN KEY (`pricing_rule_id`) REFERENCES `price_engine_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medicine_batches_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_batches_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `medicine_units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicine_batches`
--

LOCK TABLES `medicine_batches` WRITE;
/*!40000 ALTER TABLE `medicine_batches` DISABLE KEYS */;
INSERT INTO `medicine_batches` VALUES (1,1,1,1,3,'1111','2026-12-26',12000.00,10000.00,9800.00,NULL,NULL,NULL,'2026-09-12 12:28:55','2026-09-16 16:21:06'),(2,2,2,1,5,'2222','2026-12-31',5000.00,2000.00,1980.00,NULL,NULL,NULL,'2026-09-12 12:30:18','2026-09-16 06:56:27'),(3,3,3,1,9,'3333','2027-03-25',4500.00,3000.00,2955.00,NULL,NULL,NULL,'2026-09-12 12:31:24','2026-09-16 07:04:13'),(4,4,4,1,11,'4444',NULL,3200.00,2000.00,1900.00,NULL,NULL,NULL,'2026-09-12 12:32:56','2026-09-16 16:21:04'),(5,5,5,1,12,'5555','2027-01-12',2800.00,100.00,100.00,NULL,NULL,NULL,'2026-09-12 12:35:20','2026-09-13 15:00:33'),(6,6,6,1,15,'6666','2027-01-12',28000.00,2400.00,2292.00,NULL,NULL,NULL,'2026-09-12 12:36:15','2026-09-16 16:20:52'),(7,7,7,1,18,'7777','2026-09-30',42000.00,1400.00,1372.00,NULL,NULL,NULL,'2026-09-12 12:37:40','2026-09-16 16:22:11'),(8,8,8,1,19,'8888','2027-01-12',35000.00,100.00,97.00,NULL,NULL,NULL,'2026-09-12 12:38:39','2026-09-16 07:04:09'),(9,9,9,1,22,'9999','2026-09-12',58000.00,2800.00,2744.00,NULL,NULL,NULL,'2026-09-12 12:39:48','2026-09-16 16:21:03'),(10,10,10,1,24,'1010','2026-09-12',22000.00,0.00,0.00,NULL,NULL,NULL,'2026-09-12 12:41:00','2026-09-16 07:58:38');
/*!40000 ALTER TABLE `medicine_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicine_prices`
--

DROP TABLE IF EXISTS `medicine_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicine_prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint unsigned NOT NULL,
  `medicine_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `buy_price` decimal(14,2) NOT NULL,
  `sell_price` decimal(14,2) NOT NULL,
  `profit_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `profit_percent` decimal(8,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price_mode` enum('auto','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'auto',
  `locked_by` bigint unsigned DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `lock_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicine_prices_batch_id_unit_id_unique` (`batch_id`,`unit_id`),
  KEY `medicine_prices_medicine_id_foreign` (`medicine_id`),
  KEY `medicine_prices_unit_id_foreign` (`unit_id`),
  KEY `medicine_prices_locked_by_foreign` (`locked_by`),
  CONSTRAINT `medicine_prices_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_prices_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medicine_prices_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_prices_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicine_prices`
--

LOCK TABLES `medicine_prices` WRITE;
/*!40000 ALTER TABLE `medicine_prices` DISABLE KEYS */;
INSERT INTO `medicine_prices` VALUES (1,1,1,7,120.00,200.00,80.00,66.67,1,'2026-09-12 12:28:55','2026-09-15 09:21:18','auto',NULL,NULL,NULL),(2,1,1,2,1200.00,1700.00,500.00,41.67,1,'2026-09-12 12:28:55','2026-09-12 15:55:54','auto',NULL,NULL,NULL),(3,1,1,3,12000.00,16800.00,4800.00,40.00,1,'2026-09-12 12:28:55','2026-09-12 15:55:54','auto',NULL,NULL,NULL),(4,2,2,7,250.00,400.00,150.00,60.00,1,'2026-09-12 12:30:18','2026-09-12 15:55:54','auto',NULL,NULL,NULL),(5,2,2,3,5000.00,7000.00,2000.00,40.00,1,'2026-09-12 12:30:18','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(6,2,2,2,2500.00,3500.00,1000.00,40.00,1,'2026-09-12 12:30:18','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(7,3,3,7,150.00,200.00,50.00,33.33,1,'2026-09-12 12:31:24','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(8,3,3,2,2250.00,2900.00,650.00,28.89,1,'2026-09-12 12:31:24','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(9,3,3,3,4500.00,5700.00,1200.00,26.67,1,'2026-09-12 12:31:24','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(10,4,4,7,160.00,300.00,140.00,87.50,1,'2026-09-12 12:32:56','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(11,4,4,6,3200.00,4500.00,1300.00,40.63,1,'2026-09-12 12:32:56','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(12,5,5,6,2800.00,4000.00,1200.00,42.86,1,'2026-09-12 12:35:20','2026-09-12 15:55:55','auto',NULL,NULL,NULL),(13,6,6,7,1166.67,1900.00,733.33,62.86,1,'2026-09-12 12:36:15','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(14,6,6,2,14000.00,21700.00,7700.00,55.00,1,'2026-09-12 12:36:15','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(15,6,6,3,28000.00,43400.00,15400.00,55.00,1,'2026-09-12 12:36:15','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(16,7,7,7,3000.00,4700.00,1700.00,56.67,1,'2026-09-12 12:37:40','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(17,7,7,2,21000.00,32600.00,11600.00,55.24,1,'2026-09-12 12:37:40','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(18,7,7,3,42000.00,65100.00,23100.00,55.00,1,'2026-09-12 12:37:40','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(19,8,8,12,35000.00,43800.00,8800.00,25.14,1,'2026-09-12 12:38:39','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(20,9,9,7,2071.43,3300.00,1228.57,59.31,1,'2026-09-12 12:39:48','2026-09-12 15:55:56','auto',NULL,NULL,NULL),(21,9,9,2,14500.00,22500.00,8000.00,55.17,1,'2026-09-12 12:39:48','2026-09-12 15:55:57','auto',NULL,NULL,NULL),(22,9,9,3,58000.00,89900.00,31900.00,55.00,1,'2026-09-12 12:39:48','2026-09-12 15:55:57','auto',NULL,NULL,NULL),(23,10,10,8,22000.00,34100.00,12100.00,55.00,1,'2026-09-12 12:41:00','2026-09-12 15:55:57','auto',NULL,NULL,NULL),(24,10,10,4,22000.00,34100.00,12100.00,55.00,1,'2026-09-12 12:41:00','2026-09-12 15:55:57','auto',NULL,NULL,NULL);
/*!40000 ALTER TABLE `medicine_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicine_units`
--

DROP TABLE IF EXISTS `medicine_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicine_units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `factor` int NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_base` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '1',
  `allow_sale` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicine_units_medicine_id_unit_id_unique` (`medicine_id`,`unit_id`),
  KEY `medicine_units_unit_id_foreign` (`unit_id`),
  CONSTRAINT `medicine_units_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_units_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicine_units`
--

LOCK TABLES `medicine_units` WRITE;
/*!40000 ALTER TABLE `medicine_units` DISABLE KEYS */;
INSERT INTO `medicine_units` VALUES (1,1,7,1,NULL,1,1,1,'2026-09-12 12:13:20','2026-09-12 14:09:27'),(2,1,2,10,NULL,0,2,1,'2026-09-12 12:13:20','2026-09-12 14:09:27'),(3,1,3,100,NULL,0,3,1,'2026-09-12 12:13:20','2026-09-12 14:09:27'),(4,2,7,1,NULL,1,1,1,'2026-09-12 12:15:04','2026-09-12 12:15:04'),(5,2,3,20,NULL,0,2,1,'2026-09-12 12:15:04','2026-09-12 12:15:04'),(6,2,2,10,NULL,0,3,1,'2026-09-12 12:15:04','2026-09-12 12:15:04'),(7,3,7,1,NULL,1,1,1,'2026-09-12 12:16:37','2026-09-12 12:16:37'),(8,3,2,15,NULL,0,2,1,'2026-09-12 12:16:37','2026-09-12 12:16:37'),(9,3,3,30,NULL,0,3,1,'2026-09-12 12:16:37','2026-09-12 12:16:37'),(10,4,7,1,NULL,1,1,1,'2026-09-12 12:18:12','2026-09-12 12:18:12'),(11,4,6,20,NULL,0,2,1,'2026-09-12 12:18:12','2026-09-12 12:18:12'),(12,5,6,1,NULL,1,1,1,'2026-09-12 12:19:13','2026-09-12 12:19:13'),(13,6,7,1,NULL,1,1,1,'2026-09-12 12:20:21','2026-09-12 14:09:49'),(14,6,2,12,NULL,0,2,1,'2026-09-12 12:20:21','2026-09-12 14:09:49'),(15,6,3,24,NULL,0,3,1,'2026-09-12 12:20:21','2026-09-12 14:09:49'),(16,7,7,1,NULL,1,1,1,'2026-09-12 12:21:30','2026-09-12 14:10:17'),(17,7,2,7,NULL,0,2,1,'2026-09-12 12:21:30','2026-09-12 14:10:17'),(18,7,3,14,NULL,0,3,1,'2026-09-12 12:21:30','2026-09-12 14:10:17'),(19,8,12,1,NULL,1,1,1,'2026-09-12 12:22:50','2026-09-12 14:10:41'),(20,9,7,1,NULL,1,1,1,'2026-09-12 12:24:05','2026-09-12 14:11:39'),(21,9,2,7,NULL,0,2,1,'2026-09-12 12:24:05','2026-09-12 14:11:39'),(22,9,3,28,NULL,0,3,1,'2026-09-12 12:24:05','2026-09-12 14:11:39'),(23,10,8,1,NULL,1,1,1,'2026-09-12 12:25:19','2026-09-12 14:11:06'),(24,10,4,1,NULL,0,2,1,'2026-09-12 12:25:19','2026-09-12 14:11:06');
/*!40000 ALTER TABLE `medicine_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicines`
--

DROP TABLE IF EXISTS `medicines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pricing_rule_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scientific_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pricing_method` enum('local','imported') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `strips_per_box` int NOT NULL DEFAULT '1',
  `pieces_per_strip` int NOT NULL DEFAULT '1',
  `allow_box_sale` tinyint(1) NOT NULL DEFAULT '1',
  `allow_strip_sale` tinyint(1) NOT NULL DEFAULT '1',
  `allow_piece_sale` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medicines_category_id_foreign` (`category_id`),
  KEY `medicines_name_index` (`name`),
  KEY `medicines_pricing_rule_id_foreign` (`pricing_rule_id`),
  CONSTRAINT `medicines_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medicines_pricing_rule_id_foreign` FOREIGN KEY (`pricing_rule_id`) REFERENCES `price_engine_rules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicines`
--

LOCK TABLES `medicines` WRITE;
/*!40000 ALTER TABLE `medicines` DISABLE KEYS */;
INSERT INTO `medicines` VALUES (1,1,1,'باراسيتامول 500mg',NULL,NULL,'local',1,1,1,1,1,'2026-09-12 12:13:20','2026-09-12 14:09:27',NULL),(2,1,2,'أموكسيسيلين 500mg',NULL,NULL,'local',1,1,1,1,1,'2026-09-12 12:15:04','2026-09-12 12:44:45',NULL),(3,3,6,'ميتفورمين 850mg',NULL,NULL,'local',1,1,1,1,1,'2026-09-12 12:16:37','2026-09-12 12:44:59',NULL),(4,1,3,'فيتامين سي 500mg',NULL,NULL,'local',1,1,1,1,1,'2026-09-12 12:18:12','2026-09-12 12:45:10',NULL),(5,1,7,'كريم مضاد فطري موضعي',NULL,NULL,'local',1,1,1,1,1,'2026-09-12 12:19:13','2026-09-12 12:45:19',NULL),(6,2,1,'Panadol Extra (UK)',NULL,NULL,'imported',1,1,1,1,1,'2026-09-12 12:20:21','2026-09-12 14:09:49',NULL),(7,2,2,'Augmentin 1g (GSK)',NULL,NULL,'imported',1,1,1,1,1,'2026-09-12 12:21:30','2026-09-12 14:10:17',NULL),(8,3,5,'Ventolin Inhaler 100mcg (GSK)',NULL,NULL,'imported',1,1,1,1,1,'2026-09-12 12:22:50','2026-09-12 14:10:41',NULL),(9,2,9,'Nexium 40mg (AstraZeneca)',NULL,NULL,'imported',1,1,1,1,1,'2026-09-12 12:24:05','2026-09-12 14:11:39',NULL),(10,2,10,'Refresh Tears Eye Drops (Allergan)',NULL,NULL,'imported',1,1,1,1,1,'2026-09-12 12:25:19','2026-09-12 14:11:06',NULL);
/*!40000 ALTER TABLE `medicines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_branches_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'0001_02_01_182417_create_categories_table',1),(5,'0001_05_28_182354_create_suppliers_table',1),(6,'0002_01_01_000000_create_users_table',1),(7,'2026_05_18_034906_create_units_table',1),(8,'2026_05_20_070220_create_price_engine_rules_table',1),(9,'2026_05_26_182638_create_medicines_table',1),(10,'2026_05_26_182712_create_purchases_table',1),(11,'2026_05_26__195412_create_shifts_table',1),(12,'2026_05_27_035242_create_medicine_units_table',1),(13,'2026_05_27_182731_create_purchase_items_table',1),(14,'2026_05_28_182450_create_medicine_batches_table',1),(15,'2026_05_28_182625_create_sales_table',1),(16,'2026_05_28_182652_create_sale_items_table',1),(17,'2026_05_28_182806_create_inventory_logs_table',1),(18,'2026_05_29_054924_create_personal_access_tokens_table',1),(19,'2026_05_29_123353_create_refunds_table',1),(20,'2026_07_13_102618_create_debts_table',1),(21,'2026_07_14_100333_create_withdrawals_table',1),(22,'2026_07_14_101408_create_employee_debts_table',1),(23,'2026_07_14_101725_create_debt_payments_table',1),(24,'2026_07_14_102438_create_expenses_table',1),(25,'2026_07_14_194241_create_cash_movements_table',1),(26,'2026_07_15_111731_create_shift_activities_table',1),(27,'2026_07_17_114601_create_pricing_settings_table',1),(28,'2026_07_21_071237_create_inventories_table',1),(29,'2026_07_21_072331_create_inventory_movements_table',1),(30,'2026_07_25_075943_create_medicine_prices_table',1),(31,'2026_08_03_151003_create_salaries_table',1),(32,'2026_08_12_042354_add_pricing_rule_settings_to_medicines_and_price_engine_rules',1),(33,'2026_09_03_122026_create_refund_items_table',1),(34,'2026_09_11_080807_create_settings_table',1),(35,'2026_09_11_112255_create_audit_logs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'pos-token','182c63e116964726e71f30a8c7cb857b4a652b8b0917d0d6ada85c525c5be202','[\"*\"]','2026-09-12 12:05:24',NULL,'2026-09-12 12:05:07','2026-09-12 12:05:24'),(2,'App\\Models\\User',1,'pos-token','1f8c2b8f4d5e67f794aed900f1b5f0e109a1a7f895a012c64b97114d991521f8','[\"*\"]','2026-09-12 13:22:36',NULL,'2026-09-12 12:05:38','2026-09-12 13:22:36'),(3,'App\\Models\\User',2,'pos-token','b46fa881970ff0785e55e4682c501ea4527a01586333bbc4e62984d83d22290d','[\"*\"]','2026-09-12 13:50:35',NULL,'2026-09-12 13:23:17','2026-09-12 13:50:35'),(4,'App\\Models\\User',1,'pos-token','f623677656d3675c4cc6b3beec62daed6467b3b847ad14aefb99912d5b68c180','[\"*\"]','2026-09-12 13:54:21',NULL,'2026-09-12 13:50:47','2026-09-12 13:54:21'),(5,'App\\Models\\User',2,'pos-token','ebec32f01cba14f96cb9d9f706918f51eeeb192ccdcf337240e54a5c4cd8bdd8','[\"*\"]','2026-09-12 14:06:24',NULL,'2026-09-12 13:55:16','2026-09-12 14:06:24'),(6,'App\\Models\\User',1,'pos-token','f841b3b4dbf94dc4926cff89799e47d4f9a1b0719a303d85723ea19a0ffcee04','[\"*\"]','2026-09-12 14:30:16',NULL,'2026-09-12 14:06:33','2026-09-12 14:30:16'),(7,'App\\Models\\User',1,'pos-token','929dff7009e1ec19cff11df723355898e3d0acb7911760018a9ff0ca87414ea3','[\"*\"]','2026-09-12 14:37:29',NULL,'2026-09-12 14:32:30','2026-09-12 14:37:29'),(8,'App\\Models\\User',2,'pos-token','b09272ac3eabed7c9c00e61b170e94a4b357714cd36d41f1d9f3319afa160b81','[\"*\"]','2026-09-12 14:39:30',NULL,'2026-09-12 14:37:49','2026-09-12 14:39:30'),(9,'App\\Models\\User',1,'pos-token','76bb9ab3abe2f5f25011f8fb8650e59c5f1b3755edace3cc4394e8bd6cb21ab6','[\"*\"]','2026-09-12 14:41:16',NULL,'2026-09-12 14:39:44','2026-09-12 14:41:16'),(10,'App\\Models\\User',1,'pos-token','8ba5e3da004f4d8c598b5defa3eb0b1ec7b7816fd9ca6ffcf1353d5a060ae35a','[\"*\"]','2026-09-12 14:51:58',NULL,'2026-09-12 14:41:28','2026-09-12 14:51:58'),(11,'App\\Models\\User',2,'pos-token','90f3bc8ef1ec61cfc0197c5cb896050c50f9569da8af82307f15aa715933f669','[\"*\"]','2026-09-12 15:49:49',NULL,'2026-09-12 15:06:17','2026-09-12 15:49:49'),(12,'App\\Models\\User',1,'pos-token','e346b218abf24122fe3df8f754ea5b0bd7a6739c49be4d6f8d450763acb0236a','[\"*\"]','2026-09-12 15:55:59',NULL,'2026-09-12 15:50:07','2026-09-12 15:55:59'),(13,'App\\Models\\User',2,'pos-token','880e985e474076f7fbb03a4f63770d6345a08565009228c6d84cdd336915d62f','[\"*\"]','2026-09-12 16:55:16',NULL,'2026-09-12 15:58:13','2026-09-12 16:55:16'),(14,'App\\Models\\User',2,'pos-token','d2ed6a63bc8a51efa8b3d443256447274423de133cc930ece937d473341ab558','[\"*\"]','2026-09-12 17:19:20',NULL,'2026-09-12 16:55:56','2026-09-12 17:19:20'),(15,'App\\Models\\User',2,'pos-token','8c856c19121f92cc1f3e14d2c34cd8e2b367a68d12abc8e398d87e0380f235af','[\"*\"]','2026-09-12 17:29:04',NULL,'2026-09-12 17:20:17','2026-09-12 17:29:04'),(16,'App\\Models\\User',2,'pos-token','b9bc5b7d236098c4debe195dc34d8c25fd8c99103ae4adb790e3ebd553b3570b','[\"*\"]','2026-09-12 17:38:45',NULL,'2026-09-12 17:29:55','2026-09-12 17:38:45'),(17,'App\\Models\\User',2,'pos-token','404a9e1fa98131762a2a2cc1d7ec132282aec6a92f6837df89e47a381afb5c4a','[\"*\"]','2026-09-12 18:10:22',NULL,'2026-09-12 17:38:48','2026-09-12 18:10:22'),(18,'App\\Models\\User',2,'pos-token','38c6ae92798bdd94fe266d66339f1ccaa59c9a9a84628340a58f4eb64d381439','[\"*\"]','2026-09-12 19:27:21',NULL,'2026-09-12 18:11:02','2026-09-12 19:27:21'),(19,'App\\Models\\User',2,'pos-token','2306217051e5e631ff5717e92f1edab25c876aaec1b4f00935d744db1262bebf','[\"*\"]','2026-09-12 19:38:51',NULL,'2026-09-12 19:38:08','2026-09-12 19:38:51'),(20,'App\\Models\\User',2,'pos-token','8ef59a6b9bd774ea6526903dd2142e9d85e7dace00f56b66001cc530335fd4ef','[\"*\"]','2026-09-12 19:42:34',NULL,'2026-09-12 19:42:12','2026-09-12 19:42:34'),(21,'App\\Models\\User',2,'pos-token','5f8776e26d4c4153ef9f613de37211f50fe1c21f9ffa47917f49ecd87f527986','[\"*\"]','2026-09-12 20:06:38',NULL,'2026-09-12 19:48:30','2026-09-12 20:06:38'),(22,'App\\Models\\User',2,'pos-token','9c77b027e830d69086e6bfc378783ecfa08d7c9b79eec116cbb1676b494358ce','[\"*\"]','2026-09-12 20:19:03',NULL,'2026-09-12 20:08:30','2026-09-12 20:19:03'),(23,'App\\Models\\User',2,'pos-token','fc543401260845c14129eacedfa2b5da95d56433b9fc9c192f10399cfaf35c1b','[\"*\"]','2026-09-13 09:35:03',NULL,'2026-09-13 09:33:33','2026-09-13 09:35:03'),(24,'App\\Models\\User',2,'pos-token','2ae49e6c4c137c26e8a15ebc0120bda8398408b60799de557d3f3a0dd95de097','[\"*\"]','2026-09-13 09:43:58',NULL,'2026-09-13 09:36:24','2026-09-13 09:43:58'),(25,'App\\Models\\User',2,'pos-token','3373da8d1e3f63bb2e2fe1dc4a46b9ce437ed4f129b388b93f135683df970eb8','[\"*\"]','2026-09-13 13:12:34',NULL,'2026-09-13 09:45:47','2026-09-13 13:12:34'),(26,'App\\Models\\User',2,'pos-token','4a82fff4180799f584474bcdc304f3eba934d8f63ea2be781b57d2e3dc55cf17','[\"*\"]',NULL,NULL,'2026-09-13 13:50:11','2026-09-13 13:50:11'),(27,'App\\Models\\User',2,'pos-token','69be815d81af13142827dc3b503f306d087cb8ba633b66f5f2e7d1f5d8df6a39','[\"*\"]','2026-09-13 14:34:15',NULL,'2026-09-13 13:54:52','2026-09-13 14:34:15'),(28,'App\\Models\\User',2,'pos-token','e3805909eda151ce17af26973babaf6982f14f80dfa1d28597d60826cec9f381','[\"*\"]','2026-09-13 14:36:01',NULL,'2026-09-13 14:35:20','2026-09-13 14:36:01'),(29,'App\\Models\\User',1,'pos-token','c65a114d2a05a36eb0df0e1ecfd83a5657ee95d9a3a9a003f1769d3efaac1f87','[\"*\"]','2026-09-13 14:45:39',NULL,'2026-09-13 14:36:39','2026-09-13 14:45:39'),(30,'App\\Models\\User',2,'pos-token','e58a4e900011cdbdaff31e6207fa609886ef63f83bb8ade0db225ce4f85bcdfe','[\"*\"]','2026-09-13 14:49:12',NULL,'2026-09-13 14:47:33','2026-09-13 14:49:12'),(31,'App\\Models\\User',2,'pos-token','bbf2354611f6b58bdc95f5cba84b16bde8de168562523708e22254a062f74778','[\"*\"]','2026-09-13 14:57:36',NULL,'2026-09-13 14:50:44','2026-09-13 14:57:36'),(32,'App\\Models\\User',2,'pos-token','224e95d88df5fc4f9861498fbc6c0c12910287a84db643a72fd03dcf191aa4c3','[\"*\"]','2026-09-13 15:03:28',NULL,'2026-09-13 15:00:22','2026-09-13 15:03:28'),(33,'App\\Models\\User',1,'pos-token','35029744f0f70dadb6983f8eb52a56c7ae4048bb529ff7eb2d98bbea52cf3d1c','[\"*\"]','2026-09-13 15:05:58',NULL,'2026-09-13 15:04:52','2026-09-13 15:05:58'),(34,'App\\Models\\User',2,'pos-token','b3b7529db7a6cf70dae8833a6fcfbbd7b9487e00d56ce433b29a2932120c312f','[\"*\"]','2026-09-13 17:25:18',NULL,'2026-09-13 17:10:49','2026-09-13 17:25:18'),(35,'App\\Models\\User',2,'pos-token','f26d804ed612aba9fb45f2152316130c6cb8bfd35450421660affd395ee6b275','[\"*\"]','2026-09-13 18:06:27',NULL,'2026-09-13 17:36:40','2026-09-13 18:06:27'),(36,'App\\Models\\User',2,'pos-token','3477c46bf616bb869ec1e64afbd42c6f4125e5a81490feb27e5807c9cac833ed','[\"*\"]','2026-09-14 05:03:05',NULL,'2026-09-13 18:09:22','2026-09-14 05:03:05'),(37,'App\\Models\\User',1,'pos-token','26e2b7572f305dd0434a0dc73b962fbb3c1705cb5231eaa29d4cb3250d4d103c','[\"*\"]','2026-09-14 05:13:13',NULL,'2026-09-14 05:06:18','2026-09-14 05:13:13'),(38,'App\\Models\\User',2,'pos-token','8d277e2d7a612197081f3479ea195cfbba6439657ca87677cf684a2ed3d7f491','[\"*\"]','2026-09-14 05:13:47',NULL,'2026-09-14 05:13:30','2026-09-14 05:13:47'),(39,'App\\Models\\User',1,'pos-token','786ad2dbcfd470aa715c513e4af534f5bbebabea46c29e5b01bbc6a3ef34f6b2','[\"*\"]','2026-09-14 06:49:46',NULL,'2026-09-14 05:14:10','2026-09-14 06:49:46'),(40,'App\\Models\\User',2,'pos-token','9792fc04b7a2d723edbc9e717a67daaad85a3b34399d950146f17171a2a4f7e6','[\"*\"]','2026-09-14 10:08:39',NULL,'2026-09-14 06:52:04','2026-09-14 10:08:39'),(41,'App\\Models\\User',1,'pos-token','92fbcad0b1724b1a66024d0abf6f2f1b7914223fee566dfa2cfa64cfe7e5b480','[\"*\"]','2026-09-15 09:03:03',NULL,'2026-09-14 10:09:11','2026-09-15 09:03:03'),(42,'App\\Models\\User',2,'pos-token','355af0ba4057b4e4c7a07ebf713f6362bcb547b7f2e780e8d69e1a47b62da16e','[\"*\"]','2026-09-15 09:03:40',NULL,'2026-09-15 09:03:22','2026-09-15 09:03:40'),(43,'App\\Models\\User',1,'pos-token','5c544e04e2b5090ec2e3d422557643910e1ad66e0ce0f431b3de390a3854df30','[\"*\"]','2026-09-15 12:21:14',NULL,'2026-09-15 09:04:04','2026-09-15 12:21:14'),(44,'App\\Models\\User',2,'pos-token','24f6ccf6de26e5fc9d2c07bfef9723d2bc0d09b8345d809ba38900574528f28a','[\"*\"]','2026-09-15 20:10:22',NULL,'2026-09-15 12:24:45','2026-09-15 20:10:22'),(45,'App\\Models\\User',1,'pos-token','94b4af052901dd84e946e52ea3fb8cdd7116ead08428a2a3637de1e828219e66','[\"*\"]','2026-09-15 20:18:00',NULL,'2026-09-15 20:10:32','2026-09-15 20:18:00'),(46,'App\\Models\\User',1,'pos-token','cf234bbcda5f300fc36781db6f70e28ea847e5f4d1cfc500d06663ed389c71a2','[\"*\"]','2026-09-16 03:45:45',NULL,'2026-09-15 20:18:17','2026-09-16 03:45:45'),(47,'App\\Models\\User',1,'pos-token','6fcfd4c717f316bbed221424a94ae83f657bcd6fe9733a00bca1f3f6ae435a91','[\"*\"]','2026-09-16 06:20:50',NULL,'2026-09-16 03:51:19','2026-09-16 06:20:50'),(48,'App\\Models\\User',2,'pos-token','080e314c7a1b37afb13540b44e394765adfe77b197647a740c0a528e241cd518','[\"*\"]','2026-09-16 06:43:21',NULL,'2026-09-16 06:42:10','2026-09-16 06:43:21'),(49,'App\\Models\\User',1,'pos-token','9de1ff6b2f45efec08041a3a8f83c094f853acd8cdf8f13f5d9a506ddd21ba0b','[\"*\"]','2026-09-16 06:44:15',NULL,'2026-09-16 06:43:32','2026-09-16 06:44:15'),(50,'App\\Models\\User',2,'pos-token','fc84cdefc0b1ac7312aa9e66525ba05c28b85d551d0eeb314c4a44bcabd39de2','[\"*\"]','2026-09-16 06:51:11',NULL,'2026-09-16 06:44:25','2026-09-16 06:51:11'),(51,'App\\Models\\User',1,'pos-token','55e6e806380892bd9346db891952928b6f1e86c37d7304959a9a21e058608035','[\"*\"]','2026-09-16 06:52:22',NULL,'2026-09-16 06:51:22','2026-09-16 06:52:22'),(52,'App\\Models\\User',2,'pos-token','51a0f7abb7c0e22b525e03c58d1ba11e5ebdafa1898c11a3e872a4a43f82e9b4','[\"*\"]','2026-09-16 07:21:23',NULL,'2026-09-16 06:52:44','2026-09-16 07:21:23'),(53,'App\\Models\\User',1,'pos-token','1442431cc7341d0a8f9d1c061ff9b14edc05480331952f9caa5cde3317093284','[\"*\"]','2026-09-16 07:23:21',NULL,'2026-09-16 07:21:32','2026-09-16 07:23:21'),(54,'App\\Models\\User',1,'pos-token','2a6c987d5c157da87e3ebb20f5edb8740f1de3d71b29174a76984b266ca1dc9b','[\"*\"]','2026-09-16 07:58:39',NULL,'2026-09-16 07:32:54','2026-09-16 07:58:39'),(55,'App\\Models\\User',2,'pos-token','b643db6a051c82a8920a40ce48e60511d89c6e0fb89fb7257d6ae611a9806c66','[\"*\"]','2026-09-16 15:07:33',NULL,'2026-09-16 07:58:51','2026-09-16 15:07:33'),(56,'App\\Models\\User',2,'pos-token','34ff24422bcc76f48db2fa2f9fce0815d5e89b2d41eda3b8366ef0afe8279fba','[\"*\"]','2026-09-16 15:46:33',NULL,'2026-09-16 15:07:42','2026-09-16 15:46:33'),(57,'App\\Models\\User',2,'pos-token','66cd3d0f2180c99bd61354f45a19dd36bab56c4044ea2437c39f7f053dfc8bd8','[\"*\"]','2026-09-16 15:48:54',NULL,'2026-09-16 15:47:19','2026-09-16 15:48:54'),(58,'App\\Models\\User',2,'pos-token','f2f2887881397c28576ca64a7a0c1870b55eaca6a5ca5f42c68336f7c5684a8e','[\"*\"]','2026-09-16 15:51:01',NULL,'2026-09-16 15:50:23','2026-09-16 15:51:01'),(59,'App\\Models\\User',2,'pos-token','7d3abcc7c61926cf5ec38143e44d09aa0fedbb79bf520d92586c7eb02c628b2c','[\"*\"]','2026-09-16 17:30:35',NULL,'2026-09-16 16:06:58','2026-09-16 17:30:35'),(60,'App\\Models\\User',2,'pos-token','69f482ce0af9de5aa39c410ee226b3306af3d4d9a2ed78f5f7b9ad9e1976395d','[\"*\"]','2026-09-16 18:23:28',NULL,'2026-09-16 18:22:43','2026-09-16 18:23:28');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_engine_rules`
--

DROP TABLE IF EXISTS `price_engine_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_engine_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percentage','fixed','multiply') COLLATE utf8mb4_unicode_ci NOT NULL,
  `apply_on` enum('buy_price','sell_price','profit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(12,2) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_engine_rules`
--

LOCK TABLES `price_engine_rules` WRITE;
/*!40000 ALTER TABLE `price_engine_rules` DISABLE KEYS */;
INSERT INTO `price_engine_rules` VALUES (1,'ربح 40% - محلي (افتراضية)','percentage','sell_price',40.00,1,1,1,'{\"rounding\": {\"mode\": \"up\", \"unit\": 100}}','2026-09-12 12:07:39','2026-09-12 13:06:15'),(2,'ربح 55% - مستورد','percentage','sell_price',55.00,2,1,0,'{\"rounding\": {\"mode\": \"up\", \"unit\": 100}}','2026-09-12 12:08:13','2026-09-12 13:51:43'),(3,'ربح 25% - أدوية مزمنة','percentage','sell_price',25.00,3,1,0,'{\"rounding\": {\"mode\": \"up\", \"unit\": 100}}','2026-09-12 12:08:45','2026-09-12 14:34:29');
/*!40000 ALTER TABLE `price_engine_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pricing_settings`
--

DROP TABLE IF EXISTS `pricing_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pricing_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exchange_rate` decimal(15,2) NOT NULL,
  `profit_percent` decimal(8,2) NOT NULL,
  `extra_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `round_to` decimal(15,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pricing_settings`
--

LOCK TABLES `pricing_settings` WRITE;
/*!40000 ALTER TABLE `pricing_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `pricing_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `medicine_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `factor` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `base_quantity` decimal(12,2) NOT NULL,
  `buy_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_items_medicine_id_foreign` (`medicine_id`),
  KEY `purchase_items_unit_id_foreign` (`unit_id`),
  CONSTRAINT `purchase_items_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
INSERT INTO `purchase_items` VALUES (1,1,1,3,100.00,100.0000,10000.00,12000.00,1200000.00,'2026-09-12 12:28:55','2026-09-12 12:28:55'),(2,2,2,3,100.00,20.0000,2000.00,5000.00,500000.00,'2026-09-12 12:30:18','2026-09-12 12:30:18'),(3,3,3,3,100.00,30.0000,3000.00,4500.00,450000.00,'2026-09-12 12:31:24','2026-09-12 12:31:24'),(4,4,4,6,100.00,20.0000,2000.00,3200.00,320000.00,'2026-09-12 12:32:56','2026-09-12 12:32:56'),(5,5,5,6,100.00,1.0000,100.00,2800.00,280000.00,'2026-09-12 12:35:20','2026-09-12 12:35:20'),(6,6,6,3,100.00,24.0000,2400.00,28000.00,2800000.00,'2026-09-12 12:36:15','2026-09-12 12:36:15'),(7,7,7,3,100.00,14.0000,1400.00,42000.00,4200000.00,'2026-09-12 12:37:40','2026-09-12 12:37:40'),(8,8,8,12,100.00,1.0000,100.00,35000.00,3500000.00,'2026-09-12 12:38:39','2026-09-12 12:38:39'),(9,9,9,3,100.00,28.0000,2800.00,58000.00,5800000.00,'2026-09-12 12:39:48','2026-09-12 12:39:48'),(10,10,10,4,100.00,1.0000,100.00,22000.00,2200000.00,'2026-09-12 12:41:00','2026-09-12 12:41:00');
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `exchange_rate` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchases_supplier_id_foreign` (`supplier_id`),
  KEY `purchases_user_id_foreign` (`user_id`),
  CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,1,1200000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:28:55','2026-09-12 12:28:55'),(2,1,500000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:30:18','2026-09-12 12:30:18'),(3,1,450000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:31:24','2026-09-12 12:31:24'),(4,1,320000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:32:56','2026-09-12 12:32:56'),(5,1,280000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:35:20','2026-09-12 12:35:20'),(6,1,2800000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:36:15','2026-09-12 12:36:15'),(7,1,4200000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:37:40','2026-09-12 12:37:40'),(8,1,3500000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:38:39','2026-09-12 12:38:39'),(9,1,5800000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:39:48','2026-09-12 12:39:48'),(10,1,2200000.00,NULL,'2026-09-12',1.0000,0.00,NULL,1,1,'2026-09-12 12:41:00','2026-09-12 12:41:00');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refund_items`
--

DROP TABLE IF EXISTS `refund_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refund_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `refund_id` bigint unsigned NOT NULL,
  `sale_item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refund_items_refund_id_foreign` (`refund_id`),
  KEY `refund_items_sale_item_id_foreign` (`sale_item_id`),
  CONSTRAINT `refund_items_refund_id_foreign` FOREIGN KEY (`refund_id`) REFERENCES `refunds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `refund_items_sale_item_id_foreign` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refund_items`
--

LOCK TABLES `refund_items` WRITE;
/*!40000 ALTER TABLE `refund_items` DISABLE KEYS */;
INSERT INTO `refund_items` VALUES (62,122,88,2,22500.00,'2026-09-13 18:10:42','2026-09-13 18:10:42'),(63,123,87,1,21700.00,'2026-09-13 18:15:22','2026-09-13 18:15:22'),(65,125,89,1,43400.00,'2026-09-13 18:23:55','2026-09-13 18:23:55'),(66,126,80,1,16800.00,'2026-09-13 18:26:08','2026-09-13 18:26:08'),(67,128,90,1,21700.00,'2026-09-15 19:32:37','2026-09-15 19:32:37'),(68,129,91,1,21700.00,'2026-09-15 19:51:01','2026-09-15 19:51:01'),(69,130,101,2,4500.00,'2026-09-16 07:04:12','2026-09-16 07:04:12'),(70,131,100,1,2900.00,'2026-09-16 07:04:13','2026-09-16 07:04:13'),(71,132,102,1,16800.00,'2026-09-16 16:21:06','2026-09-16 16:21:06');
/*!40000 ALTER TABLE `refund_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refunds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refunds_sale_id_foreign` (`sale_id`),
  CONSTRAINT `refunds_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
INSERT INTO `refunds` VALUES (122,41,45000.00,'إرجاع Nexium 40mg (AstraZeneca) (strip)','2026-09-13 18:10:42','2026-09-13 18:10:42'),(123,40,21700.00,'إرجاع Panadol Extra (UK) (strip)','2026-09-13 18:15:22','2026-09-13 18:15:22'),(125,42,43400.00,'إرجاع Panadol Extra (UK) (Box)','2026-09-13 18:23:55','2026-09-13 18:23:55'),(126,36,16800.00,'إرجاع باراسيتامول 500mg (box)','2026-09-13 18:26:08','2026-09-13 18:26:08'),(128,43,21700.00,'إرجاع Panadol Extra (UK) (strip)','2026-09-15 19:32:37','2026-09-15 19:32:37'),(129,44,21700.00,'إرجاع Panadol Extra (UK) (strip)','2026-09-15 19:51:01','2026-09-15 19:51:01'),(130,48,9000.00,'إرجاع فيتامين سي 500mg (Tube)','2026-09-16 07:04:12','2026-09-16 07:04:12'),(131,48,2900.00,'إرجاع ميتفورمين 850mg (Strip)','2026-09-16 07:04:13','2026-09-16 07:04:13'),(132,49,16800.00,'إرجاع باراسيتامول 500mg (Box)','2026-09-16 16:21:06','2026-09-16 16:21:06');
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salaries`
--

DROP TABLE IF EXISTS `salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `allowances` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(12,2) NOT NULL,
  `status` enum('pending','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` date DEFAULT NULL,
  `payment_method` enum('cash','bank') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salaries_user_id_month_year_unique` (`user_id`,`month`,`year`),
  CONSTRAINT `salaries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salaries`
--

LOCK TABLES `salaries` WRITE;
/*!40000 ALTER TABLE `salaries` DISABLE KEYS */;
INSERT INTO `salaries` VALUES (1,2,9,2026,250000.00,0.00,0.00,250000.00,'paid','2026-09-14','bank','بنك النيل','6060',NULL,'2026-09-14 06:33:05','2026-09-14 06:33:37');
/*!40000 ALTER TABLE `salaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `medicine_batch_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit` enum('box','strip','piece') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_pieces` int NOT NULL DEFAULT '0',
  `medicine_unit_id` bigint unsigned DEFAULT '0',
  `quantity_base` int NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL,
  `profit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_medicine_batch_id_foreign` (`medicine_batch_id`),
  CONSTRAINT `sale_items_medicine_batch_id_foreign` FOREIGN KEY (`medicine_batch_id`) REFERENCES `medicine_batches` (`id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (80,36,1,1,'box',0,3,100,16800.00,4800.00,'2026-09-13 17:36:51','2026-09-13 17:36:51'),(81,36,4,1,'piece',0,6,20,4500.00,1300.00,'2026-09-13 17:36:51','2026-09-13 17:36:51'),(82,37,6,1,'box',0,3,24,43400.00,15400.00,'2026-09-13 17:36:52','2026-09-13 17:36:52'),(83,37,9,1,'box',0,3,28,89900.00,31900.00,'2026-09-13 17:36:52','2026-09-13 17:36:52'),(84,38,8,2,'piece',0,12,2,43800.00,17600.00,'2026-09-13 17:36:54','2026-09-13 17:36:54'),(85,39,7,1,'box',0,3,14,65100.00,23100.00,'2026-09-13 17:37:01','2026-09-13 17:37:01'),(86,39,10,1,'piece',0,4,1,34100.00,12100.00,'2026-09-13 17:37:01','2026-09-13 17:37:01'),(87,40,6,3,'strip',0,2,36,21700.00,23100.00,'2026-09-13 17:37:02','2026-09-13 17:37:02'),(88,41,9,2,'strip',0,2,14,22500.00,16000.00,'2026-09-13 17:51:06','2026-09-13 17:51:06'),(89,42,6,1,'box',0,3,24,43400.00,15400.00,'2026-09-13 18:23:53','2026-09-13 18:23:53'),(90,43,6,1,'strip',0,2,12,21700.00,7700.00,'2026-09-15 19:32:20','2026-09-15 19:32:20'),(91,44,6,1,'strip',0,2,12,21700.00,7700.00,'2026-09-15 19:50:43','2026-09-15 19:50:43'),(92,45,6,1,'strip',0,2,12,21700.00,7700.00,'2026-09-16 06:56:27','2026-09-16 06:56:27'),(93,45,1,1,'box',0,3,100,16800.00,4800.00,'2026-09-16 06:56:27','2026-09-16 06:56:27'),(94,45,2,1,'box',0,3,20,7000.00,2000.00,'2026-09-16 06:56:27','2026-09-16 06:56:27'),(95,46,4,1,'piece',0,6,20,4500.00,1300.00,'2026-09-16 06:56:28','2026-09-16 06:56:28'),(96,46,3,2,'strip',0,2,30,2900.00,1300.00,'2026-09-16 06:56:28','2026-09-16 06:56:28'),(97,47,6,1,'box',0,3,24,43400.00,15400.00,'2026-09-16 07:04:09','2026-09-16 07:04:09'),(98,47,8,1,'piece',0,12,1,43800.00,8800.00,'2026-09-16 07:04:09','2026-09-16 07:04:09'),(99,48,1,1,'box',0,3,100,16800.00,4800.00,'2026-09-16 07:04:11','2026-09-16 07:04:11'),(100,48,3,2,'strip',0,2,30,2900.00,1300.00,'2026-09-16 07:04:11','2026-09-16 07:04:11'),(101,48,4,3,'piece',0,6,60,4500.00,3900.00,'2026-09-16 07:04:11','2026-09-16 07:04:11'),(102,49,1,1,'box',0,3,100,16800.00,4800.00,'2026-09-16 16:20:51','2026-09-16 16:20:51'),(103,50,6,1,'box',0,3,24,43400.00,15400.00,'2026-09-16 16:20:52','2026-09-16 16:20:52'),(104,51,9,1,'box',0,3,28,89900.00,31900.00,'2026-09-16 16:21:03','2026-09-16 16:21:03'),(105,52,4,2,'piece',0,6,40,4500.00,2600.00,'2026-09-16 16:21:04','2026-09-16 16:21:04'),(106,53,7,1,'box',0,3,14,65100.00,23100.00,'2026-09-16 16:22:11','2026-09-16 16:22:11');
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `profit_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_transfer_date` date DEFAULT NULL,
  `bank_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_branch_id_foreign` (`branch_id`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_shift_id_foreign` (`shift_id`),
  CONSTRAINT `sales_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `sales_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (36,1,2,21,21300.00,6100.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 17:36:51','2026-09-13 17:36:51'),(37,1,2,21,133300.00,47300.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 17:36:52','2026-09-13 17:36:52'),(38,1,2,21,87600.00,17600.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 17:36:54','2026-09-13 17:36:54'),(39,1,2,22,99200.00,35200.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 17:37:01','2026-09-13 17:37:01'),(40,1,2,22,65100.00,23100.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 17:37:02','2026-09-13 17:37:02'),(41,1,2,23,45000.00,16000.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 17:51:06','2026-09-13 17:51:06'),(42,1,2,24,43400.00,15400.00,'cash',NULL,NULL,NULL,NULL,'2026-09-13 18:23:53','2026-09-13 18:23:53'),(43,1,2,24,21700.00,7700.00,'cash',NULL,NULL,NULL,NULL,'2026-09-15 19:32:19','2026-09-15 19:32:19'),(44,1,2,24,21700.00,7700.00,'cash',NULL,NULL,NULL,NULL,'2026-09-15 19:50:43','2026-09-15 19:50:43'),(45,1,2,25,45500.00,14500.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 06:56:27','2026-09-16 06:56:27'),(46,1,2,25,10300.00,2600.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 06:56:28','2026-09-16 06:56:28'),(47,1,2,26,87200.00,24200.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 07:04:09','2026-09-16 07:04:09'),(48,1,2,26,36100.00,10000.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 07:04:11','2026-09-16 07:04:11'),(49,1,2,29,16800.00,4800.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 16:20:51','2026-09-16 16:20:51'),(50,1,2,29,43400.00,15400.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 16:20:52','2026-09-16 16:20:52'),(51,1,2,30,89900.00,31900.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 16:21:03','2026-09-16 16:21:03'),(52,1,2,30,9000.00,2600.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 16:21:04','2026-09-16 16:21:04'),(53,1,2,31,65100.00,23100.00,'cash',NULL,NULL,NULL,NULL,'2026-09-16 16:22:11','2026-09-16 16:22:11');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'pharmacy.name','صيدلية مركز صحي التلال','string','general','اسم الصيدلية',NULL,'2026-09-12 12:03:44','2026-09-16 06:52:20'),(2,'pharmacy.phone','09123456789','string','general','رقم الهاتف',NULL,'2026-09-12 12:03:44','2026-09-12 14:46:46'),(3,'pharmacy.address','الخرطوم - شرق النيل - التلال','string','general','العنوان',NULL,'2026-09-12 12:03:44','2026-09-12 14:47:06'),(4,'pharmacy.tax_number','12345678','string','general','الرقم الضريبي',NULL,'2026-09-12 12:03:44','2026-09-12 14:47:14'),(5,'pharmacy.currency','ج.س','string','general','العملة',NULL,'2026-09-12 12:03:44','2026-09-12 12:03:44'),(6,'pharmacy.logo_url','','string','general','شعار الصيدلية',NULL,'2026-09-12 12:03:44','2026-09-12 12:03:44'),(7,'pin.enabled','1','boolean','pin','تفعيل نظام PIN',NULL,'2026-09-12 12:03:44','2026-09-12 12:03:44'),(8,'pin.length','4','integer','pin','عدد أرقام PIN',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(9,'pin.on_shift_open','1','boolean','pin','عند فتح وردية',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(10,'pin.on_shift_close','1','boolean','pin','عند إغلاق وردية',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(11,'pin.on_withdraw','1','boolean','pin','عند السحب النقدي',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(12,'pin.on_expense','1','boolean','pin','عند إضافة مصروف',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(13,'pin.on_debt_payment','1','boolean','pin','عند سداد دين',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(14,'pin.on_void_sale','1','boolean','pin','عند إلغاء فاتورة',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(15,'pin.on_price_change','1','boolean','pin','عند تغيير سعر',NULL,'2026-09-12 12:03:45','2026-09-12 14:47:39'),(16,'pin.on_every_sale','0','boolean','pin','عند كل عملية بيع',NULL,'2026-09-12 12:03:45','2026-09-12 12:03:45'),(17,'pin.max_attempts','5','integer','pin','الحد الأقصى للمحاولات',NULL,'2026-09-12 12:03:46','2026-09-12 12:03:46'),(18,'pin.lockout_minutes','5','integer','pin','مدة القفل بعد الفشل (دقائق)',NULL,'2026-09-12 12:03:46','2026-09-12 12:03:46'),(19,'print.enabled','1','boolean','print','تفعيل الطباعة',NULL,'2026-09-12 12:03:48','2026-09-12 14:45:37'),(20,'print.auto_after_sale','1','boolean','print','طباعة تلقائية بعد البيع',NULL,'2026-09-12 12:03:48','2026-09-12 14:45:44'),(21,'print.width','80','integer','print','عرض الورق (mm)',NULL,'2026-09-12 12:03:48','2026-09-14 06:44:53'),(22,'print.allow_reprint','1','boolean','print','السماح بإعادة الطباعة',NULL,'2026-09-12 12:03:48','2026-09-16 06:44:14'),(23,'print.auto_close_shift','1','boolean','print','طباعة تقرير إغلاق الوردية',NULL,'2026-09-12 12:03:48','2026-09-12 12:03:48'),(24,'security.session_timeout_minutes','720','integer','security','مهلة انتهاء الجلسة (دقائق)',NULL,'2026-09-12 12:03:48','2026-09-12 12:03:48'),(25,'security.require_password_change_days','0','integer','security','إلزام تغيير كلمة المرور كل (أيام)',NULL,'2026-09-12 12:03:49','2026-09-12 12:03:49'),(26,'backup.auto_enabled','1','boolean','backup','نسخ احتياطي تلقائي',NULL,'2026-09-12 12:03:49','2026-09-12 12:03:49'),(27,'backup.keep_days','30','integer','backup','مدة الاحتفاظ (أيام)',NULL,'2026-09-12 12:03:49','2026-09-12 12:03:49');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_activities`
--

DROP TABLE IF EXISTS `shift_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shift_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_activities_shift_id_foreign` (`shift_id`),
  KEY `shift_activities_user_id_foreign` (`user_id`),
  CONSTRAINT `shift_activities_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_activities`
--

LOCK TABLES `shift_activities` WRITE;
/*!40000 ALTER TABLE `shift_activities` DISABLE KEYS */;
INSERT INTO `shift_activities` VALUES (23,21,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-13 17:36:48','2026-09-13 17:36:48'),(24,22,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-13 17:36:59','2026-09-13 17:36:59'),(25,23,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-13 17:47:31','2026-09-13 17:47:31'),(26,24,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-13 18:09:37','2026-09-13 18:09:37'),(27,27,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 06:56:25','2026-09-16 06:56:25'),(28,25,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 06:56:25','2026-09-16 06:56:25'),(29,26,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 06:56:25','2026-09-16 06:56:25'),(30,25,2,'withdraw',1000.00,NULL,NULL,'سحب موظف',NULL,'[]','2026-09-16 06:58:03','2026-09-16 06:58:03'),(31,25,2,'expense',20000.00,NULL,NULL,'مصروف',NULL,'[]','2026-09-16 06:58:47','2026-09-16 06:58:47'),(32,25,2,'debt_payment',2000.00,NULL,NULL,'سداد دين','فطور','[]','2026-09-16 06:59:16','2026-09-16 06:59:16'),(33,26,2,'expense',30000.00,NULL,NULL,'مصروف',NULL,'[]','2026-09-16 07:04:15','2026-09-16 07:04:15'),(34,27,2,'withdraw',5000.00,NULL,NULL,'سحب موظف',NULL,'[]','2026-09-16 15:10:06','2026-09-16 15:10:06'),(35,27,2,'expense',1000.00,NULL,NULL,'مصروف',NULL,'[]','2026-09-16 15:40:39','2026-09-16 15:40:39'),(36,27,2,'debt_payment',500.00,NULL,NULL,'سداد دين','اختبار','[]','2026-09-16 15:41:42','2026-09-16 15:41:42'),(37,28,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 15:50:28','2026-09-16 15:50:28'),(38,29,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 16:20:48','2026-09-16 16:20:48'),(39,29,2,'expense',2000.00,NULL,NULL,'مصروف',NULL,'[]','2026-09-16 16:20:53','2026-09-16 16:20:53'),(40,29,2,'withdraw',500.00,NULL,NULL,'سحب موظف',NULL,'[]','2026-09-16 16:20:55','2026-09-16 16:20:55'),(41,29,2,'debt_payment',1500.00,NULL,NULL,'سداد دين','سداد دفعة اولي','[]','2026-09-16 16:20:56','2026-09-16 16:20:56'),(42,30,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 16:21:01','2026-09-16 16:21:01'),(43,30,2,'expense',300.00,NULL,NULL,'مصروف',NULL,'[]','2026-09-16 16:21:07','2026-09-16 16:21:07'),(44,31,2,'open',0.00,NULL,NULL,'فتح الوردية',NULL,'[]','2026-09-16 16:21:49','2026-09-16 16:21:49');
/*!40000 ALTER TABLE `shift_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `opening_cash` decimal(15,2) NOT NULL,
  `expected_cash` decimal(15,2) NOT NULL DEFAULT '0.00',
  `closing_cash` decimal(15,2) DEFAULT NULL,
  `cash_sales` decimal(15,2) NOT NULL DEFAULT '0.00',
  `card_sales` decimal(15,2) NOT NULL DEFAULT '0.00',
  `refund_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `expenses_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `debts_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `withdraw_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sales_count` int NOT NULL DEFAULT '0',
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_user_id_foreign` (`user_id`),
  KEY `shifts_branch_id_foreign` (`branch_id`),
  CONSTRAINT `shifts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
INSERT INTO `shifts` VALUES (21,2,1,10000.00,235400.00,252200.00,225400.00,0.00,16800.00,0.00,0.00,0.00,3,'closed','2026-09-13 17:36:48','2026-09-13 17:36:56','2026-09-13 17:36:48','2026-09-13 18:26:08'),(22,2,1,5000.00,147600.00,169300.00,142600.00,0.00,21700.00,0.00,0.00,0.00,2,'closed','2026-09-13 17:36:58','2026-09-13 17:37:04','2026-09-13 17:36:58','2026-09-13 18:15:23'),(23,2,1,8000.00,8000.00,53000.00,0.00,0.00,45000.00,0.00,0.00,0.00,1,'closed','2026-09-13 17:47:31','2026-09-13 18:06:27','2026-09-13 17:47:31','2026-09-13 18:10:42'),(24,2,1,0.00,0.00,0.00,0.00,0.00,86800.00,0.00,0.00,0.00,3,'closed','2026-09-13 18:09:37','2026-09-16 06:54:12','2026-09-13 18:09:37','2026-09-16 06:54:12'),(25,2,1,0.00,34800.00,36800.00,55800.00,0.00,0.00,20000.00,2000.00,1000.00,2,'closed','2026-09-16 06:56:24','2026-09-16 06:59:44','2026-09-16 06:56:24','2026-09-16 06:59:44'),(26,2,1,0.00,69500.00,81400.00,111400.00,0.00,11900.00,30000.00,0.00,0.00,2,'closed','2026-09-16 06:56:24','2026-09-16 15:07:33','2026-09-16 06:56:24','2026-09-16 15:07:33'),(27,2,1,0.00,-6000.00,0.00,0.00,0.00,0.00,1000.00,500.00,5000.00,0,'closed','2026-09-16 06:56:24','2026-09-16 15:46:33','2026-09-16 06:56:24','2026-09-16 15:46:33'),(28,2,1,10000.00,10000.00,10000.00,0.00,0.00,0.00,0.00,0.00,0.00,0,'closed','2026-09-16 15:50:28','2026-09-16 16:07:04','2026-09-16 15:50:28','2026-09-16 16:07:04'),(29,2,1,10000.00,50900.00,69200.00,43400.00,0.00,16800.00,2000.00,1500.00,500.00,2,'closed','2026-09-16 16:20:48','2026-09-16 16:20:59','2026-09-16 16:20:48','2026-09-16 16:21:06'),(30,2,1,5000.00,103600.00,86800.00,98900.00,0.00,0.00,300.00,0.00,0.00,2,'closed','2026-09-16 16:21:01','2026-09-16 16:21:09','2026-09-16 16:21:01','2026-09-16 16:21:09'),(31,2,1,8000.00,73100.00,NULL,65100.00,0.00,0.00,0.00,0.00,0.00,1,'open','2026-09-16 16:21:48',NULL,'2026-09-16 16:21:48','2026-09-16 16:22:11');
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'الشفاء',NULL,'0111111111',NULL,NULL,'2026-09-12 12:26:30','2026-09-16 07:53:27',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'Piece','pc',1,NULL,NULL),(2,'Strip','str',1,NULL,NULL),(3,'Box','box',1,NULL,NULL),(4,'Bottle','bot',1,NULL,NULL),(5,'Vial','vial',1,NULL,NULL),(6,'Tube','tube',1,NULL,NULL),(7,'Tablets','tab',1,NULL,NULL),(8,'Drop','drop',1,NULL,NULL),(9,'Drip','drip',1,NULL,NULL),(10,'Spray','spr',1,NULL,NULL),(11,'Ampoule','amp',1,NULL,NULL),(12,'Inhaler','inh',1,NULL,NULL),(13,'Suppository','supp',1,NULL,NULL),(14,'Sachet','sach',1,NULL,NULL),(15,'Pack','pack',1,NULL,NULL);
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary` decimal(15,2) NOT NULL DEFAULT '250000.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pin_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pin_set_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_branch_id_foreign` (`branch_id`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'د.صلاح عبد المنعم','admin@pharmacy.test',NULL,'$2y$12$/CTn/VEag/SePsMfnTcY9eMWOdP2qhF1bZHpSYNKLoYeemJdnLzRm','admin',250000.00,1,NULL,NULL,NULL,'2026-09-12 12:03:43','2026-09-15 20:15:57',NULL),(2,1,'Cashier','cashier@pharmacy.test',NULL,'$2y$12$Abs.1.jdLUACd8iGyDOEoen6ykNSgm73uVhKLNMc.CeGJvdd3cmCe','cashier',250000.00,1,'$2y$12$P0esutkRdlJjINQD6E6tVujte79t5ppcG92GBFGxM5MWyrnrR8xdm','2026-09-12 14:48:01',NULL,'2026-09-12 12:03:44','2026-09-12 14:48:01',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawals`
--

DROP TABLE IF EXISTS `withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawals_shift_id_foreign` (`shift_id`),
  KEY `withdrawals_user_id_foreign` (`user_id`),
  CONSTRAINT `withdrawals_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawals`
--

LOCK TABLES `withdrawals` WRITE;
/*!40000 ALTER TABLE `withdrawals` DISABLE KEYS */;
INSERT INTO `withdrawals` VALUES (3,25,2,1000.00,'فطور عمال','2026-09-16 06:58:03','2026-09-16 06:58:03'),(4,27,2,5000.00,'اختبار','2026-09-16 15:10:06','2026-09-16 15:10:06'),(5,29,2,500.00,'فطور موظفين','2026-09-16 16:20:54','2026-09-16 16:20:54');
/*!40000 ALTER TABLE `withdrawals` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-16 22:28:20
