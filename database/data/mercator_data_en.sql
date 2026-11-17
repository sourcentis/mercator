/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.11-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: mercator
-- ------------------------------------------------------
-- Server version	10.11.11-MariaDB-0+deb12u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
INSERT INTO `activities` (`id`, `name`, `description`, `recovery_time_objective`, `maximum_tolerable_downtime`, `recovery_point_objective`, `maximum_tolerable_data_loss`, `drp`, `drp_link`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Activity 1','<p>Description of activity 1</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-10 13:20:42','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(2,'Activity 2','<p>Description of the testing activity</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-10 15:44:26','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(3,'Activity 3','<p>Description of activity 3</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-13 04:57:08','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(4,'Activity 4','<p>Description of activity 4</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-13 04:57:24','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(5,'Helpdesk','<p>User support</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:49:05','2020-08-13 05:49:05',NULL),
(6,'Development','<p>Application development</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:49:47','2020-08-13 05:49:47',NULL),
(7,'IT monitoring','<p>Check the proper functioning of IT equipment</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:52:47','2020-08-13 05:52:47',NULL),
(8,'Application monitoring','<p>Check the correct functioning of computer applications</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:53:19','2020-08-13 05:53:19',NULL),
(9,'Admission','<p>Admission of patients to the hospital</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-09-07 07:54:20','2024-10-14 08:01:04',NULL),
(10,'Complaint management','<p>Complaints management process</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2023-04-12 07:39:25','2024-10-14 08:00:35',NULL);
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `activity_document`
--

LOCK TABLES `activity_document` WRITE;
/*!40000 ALTER TABLE `activity_document` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `activity_impact`
--

LOCK TABLES `activity_impact` WRITE;
/*!40000 ALTER TABLE `activity_impact` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_impact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `activity_application`
--

LOCK TABLES `activity_application` WRITE;
/*!40000 ALTER TABLE `activity_application` DISABLE KEYS */;
INSERT INTO `activity_application` (`application_id`, `activity_id`) VALUES (1,9);
/*!40000 ALTER TABLE `activity_application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `activity_operation`
--

LOCK TABLES `activity_operation` WRITE;
/*!40000 ALTER TABLE `activity_operation` DISABLE KEYS */;
INSERT INTO `activity_operation` (`activity_id`, `operation_id`) VALUES (2,3),
(1,1),
(1,2),
(4,3),
(3,1),
(5,6),
(5,7),
(5,8);
/*!40000 ALTER TABLE `activity_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `activity_process`
--

LOCK TABLES `activity_process` WRITE;
/*!40000 ALTER TABLE `activity_process` DISABLE KEYS */;
INSERT INTO `activity_process` (`process_id`, `activity_id`) VALUES (1,1),
(1,2),
(2,3),
(2,4),
(9,5),
(9,6),
(9,7),
(9,8),
(5,9),
(19,9),
(18,9);
/*!40000 ALTER TABLE `activity_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `actor_operation`
--

LOCK TABLES `actor_operation` WRITE;
/*!40000 ALTER TABLE `actor_operation` DISABLE KEYS */;
INSERT INTO `actor_operation` (`operation_id`, `actor_id`) VALUES (2,1),
(1,1),
(1,4),
(2,5),
(3,6),
(6,7),
(7,7),
(8,7);
/*!40000 ALTER TABLE `actor_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `actors`
--

LOCK TABLES `actors` WRITE;
/*!40000 ALTER TABLE `actors` DISABLE KEYS */;
INSERT INTO `actors` (`id`, `name`, `nature`, `type`, `contact`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Jeans','Person','internal',NULL,'2020-06-14 11:02:22','2020-06-22 06:12:20','2020-06-22 06:12:20'),
(7,'Helpdesk Agent','Band','Internal','80800 - helpdesk.informatique@hospital.lu','2020-08-13 06:35:31','2021-01-28 14:08:24',NULL),
(8,'Caregiver','Band','Internal','None','2025-06-10 17:29:28','2025-06-10 17:29:28',NULL),
(9,'Doctor','band','Internal','None','2025-06-10 17:29:47','2025-06-10 17:29:47',NULL),
(10,'Supplier','entity','external','None','2025-06-10 17:30:11','2025-06-10 17:30:11',NULL),
(11,'Administrative agent','person','internal','None','2025-06-10 17:30:41','2025-06-10 17:30:41',NULL),
(12,'Recruiter','person','internal','None','2025-06-10 17:31:12','2025-06-10 17:31:12',NULL);
/*!40000 ALTER TABLE `actors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_user_application`
--

LOCK TABLES `admin_user_application` WRITE;
/*!40000 ALTER TABLE `admin_user_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user_application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` (`id`, `user_id`, `firstname`, `lastname`, `type`, `attributes`, `icon_id`, `description`, `domain_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'M01','Marcel','Dupont','System','',NULL,'<p>System Administrator</p>',1,'2025-06-12 11:29:56','2025-06-12 11:30:37',NULL),
(2,'P02','Paul','Martin','System','',NULL,'<p>System administrator</p>',1,'2025-06-12 11:30:31','2025-06-12 11:30:31',NULL),
(3,'G03','Gus','Schmidt','Network','Local',NULL,'<p>Network administrator</p>',1,'2025-06-12 11:31:08','2025-07-01 05:23:07',NULL),
(4,'UD34','Ursula','Dender','Network','Private premises',NULL,NULL,1,'2025-07-01 05:34:19','2025-07-01 05:36:33',NULL);
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `annuaires`
--

LOCK TABLES `annuaires` WRITE;
/*!40000 ALTER TABLE `annuaires` DISABLE KEYS */;
INSERT INTO `annuaires` (`id`, `name`, `description`, `solution`, `created_at`, `updated_at`, `deleted_at`, `zone_admin_id`) VALUES (1,'PHONE','<p>Telephone directory</p>','TASCO','2025-06-12 11:25:21','2025-06-12 11:25:21',NULL,1),
(2,'OpenLDAP','<p>LDAP + Kerberos + proprietary extensions</p>','Apache','2025-06-12 11:27:40','2025-06-14 05:52:18',NULL,1);
/*!40000 ALTER TABLE `annuaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_blocks`
--

LOCK TABLES `application_blocks` WRITE;
/*!40000 ALTER TABLE `application_blocks` DISABLE KEYS */;
INSERT INTO `application_blocks` (`id`, `name`, `description`, `responsible`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Administration','<p>Administrative applications</p>',NULL,'2025-06-10 09:49:49','2025-06-10 09:49:49',NULL),
(2,'Laboratory','<p>Laboratory applications</p>',NULL,'2025-06-10 09:50:11','2025-06-10 09:50:11',NULL),
(3,'Medical','<p>Medical applications</p>',NULL,'2025-06-10 09:50:25','2025-06-10 09:50:25',NULL),
(4,'Accounting','<p>Accounting software</p>',NULL,'2025-06-10 10:02:16','2025-06-10 10:02:16',NULL),
(5,'Human Resources','<p>Human resources management software</p>',NULL,'2025-06-10 10:02:46','2025-06-10 10:02:46',NULL),
(6,'Computer science','<p>IT department software</p>',NULL,'2025-06-10 10:03:05','2025-06-10 10:03:05',NULL);
/*!40000 ALTER TABLE `application_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_module_application_service`
--

LOCK TABLES `application_module_application_service` WRITE;
/*!40000 ALTER TABLE `application_module_application_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_module_application_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_modules`
--

LOCK TABLES `application_modules` WRITE;
/*!40000 ALTER TABLE `application_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_services`
--

LOCK TABLES `application_services` WRITE;
/*!40000 ALTER TABLE `application_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bay_wifi_terminal`
--

LOCK TABLES `bay_wifi_terminal` WRITE;
/*!40000 ALTER TABLE `bay_wifi_terminal` DISABLE KEYS */;
/*!40000 ALTER TABLE `bay_wifi_terminal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bays`
--

LOCK TABLES `bays` WRITE;
/*!40000 ALTER TABLE `bays` DISABLE KEYS */;
INSERT INTO `bays` (`id`, `name`, `description`, `building_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'R01','<p>Main rack</p>',12,'2025-06-10 08:46:55','2025-06-10 08:46:55',NULL),
(2,'R02','<p>Rack Database / BackupList</p>',12,'2025-06-11 10:24:04','2025-06-11 10:24:04',NULL),
(3,'R03','<p>Mainframe</p>',12,'2025-06-12 17:57:32','2025-06-12 17:57:32',NULL);
/*!40000 ALTER TABLE `bays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` (`id`, `name`, `type`, `description`, `attributes`, `site_id`, `building_id`, `icon_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'101','Lobby','<p>Patient reception</p>','Audience',1,16,NULL,'2025-06-10 08:33:42','2025-10-08 13:44:41',NULL),
(2,'102','Room','<p>Waiting room</p>','Audience',1,16,NULL,'2025-06-10 08:34:36','2025-10-08 13:44:41',NULL),
(3,'103','Room','<p>Consultation Room 1</p>','Public Care',1,16,NULL,'2025-06-10 08:35:13','2025-10-08 13:44:41',NULL),
(4,'104','Room','<p>Consultation Room 2</p>','Public Care',1,16,NULL,'2025-06-10 08:35:34','2025-10-08 13:44:41',NULL),
(5,'105','Lobby','<p>Emergencies</p>','Public Care',1,16,NULL,'2025-06-10 08:38:19','2025-10-08 13:44:41',NULL),
(6,'201','Lab','<p>Laboratory</p>','Restricted',1,17,NULL,'2025-06-10 08:38:51','2025-10-08 13:42:53',NULL),
(7,'202','Lab','<p>Pharmacy</p>','Restricted',1,17,NULL,'2025-06-10 08:39:35','2025-10-08 13:43:57',NULL),
(8,'205','Room','<p>Medical Imaging</p>','Audience',1,17,NULL,'2025-06-10 08:42:11','2025-10-08 13:43:36',NULL),
(9,'303','Room','<p>Operating theater</p>','Restricted Care',1,18,NULL,'2025-06-10 08:43:01','2025-10-08 13:43:21',NULL),
(10,'304','Room','<p>Intensive care</p>','Restricted Care',1,18,NULL,'2025-06-10 08:44:58','2025-10-08 13:44:04',NULL),
(11,'401','Desk','<p>Administration</p>','Restricted',1,19,NULL,'2025-06-10 08:45:20','2025-10-08 13:44:15',NULL),
(12,'403','IT','<p>Technical room</p>','Restricted',1,19,NULL,'2025-06-10 08:45:44','2025-10-08 13:43:29',NULL),
(13,'404','Desk','<p>Logistics</p>','Restricted',1,19,NULL,'2025-06-10 08:46:05','2025-10-08 13:44:28',NULL),
(14,'402','Desk','<p>Direction</p>','Restricted',1,19,NULL,'2025-06-11 10:23:11','2025-10-08 13:44:21',NULL),
(15,'302','IT','<p>Technical room</p>','Restricted',1,18,NULL,'2025-06-14 05:57:11','2025-10-08 13:43:07',NULL),
(16,'AND1','Floor','<p>First floor</p>','Audience',1,NULL,NULL,'2025-10-08 13:39:08','2025-10-08 13:44:41',NULL),
(17,'ET2','Floor','<p>Floor number two</p>','Audience',1,NULL,NULL,'2025-10-08 13:40:44','2025-10-08 13:40:44',NULL),
(18,'ET3','Floor','<p>Floor 3</p>','Restricted',1,NULL,NULL,'2025-10-08 13:41:07','2025-10-08 13:41:07',NULL),
(19,'ET4','Floor','<p>Fourth floor - Management + IT</p>','Restricted',1,NULL,NULL,'2025-10-08 13:41:54','2025-10-08 13:41:54',NULL);
/*!40000 ALTER TABLE `buildings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `certificate_logical_server`
--

LOCK TABLES `certificate_logical_server` WRITE;
/*!40000 ALTER TABLE `certificate_logical_server` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificate_logical_server` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cluster_logical_server`
--

LOCK TABLES `cluster_logical_server` WRITE;
/*!40000 ALTER TABLE `cluster_logical_server` DISABLE KEYS */;
INSERT INTO `cluster_logical_server` (`cluster_id`, `logical_server_id`) VALUES (1,1),
(1,2),
(1,3),
(1,6),
(1,7),
(2,4),
(2,5),
(3,1);
/*!40000 ALTER TABLE `cluster_logical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cluster_physical_server`
--

LOCK TABLES `cluster_physical_server` WRITE;
/*!40000 ALTER TABLE `cluster_physical_server` DISABLE KEYS */;
INSERT INTO `cluster_physical_server` (`cluster_id`, `physical_server_id`) VALUES (1,2),
(1,5),
(2,3);
/*!40000 ALTER TABLE `cluster_physical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cluster_router`
--

LOCK TABLES `cluster_router` WRITE;
/*!40000 ALTER TABLE `cluster_router` DISABLE KEYS */;
INSERT INTO `cluster_router` (`cluster_id`, `router_id`) VALUES (1,1);
/*!40000 ALTER TABLE `cluster_router` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `clusters`
--

LOCK TABLES `clusters` WRITE;
/*!40000 ALTER TABLE `clusters` DISABLE KEYS */;
INSERT INTO `clusters` (`id`, `name`, `type`, `attributes`, `icon_id`, `description`, `address_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'CLUSTER01','XZWare','C1 C2 C3',NULL,'<p>Main cluster.</p>','10.10.8.2','2025-06-12 11:51:05','2025-10-19 09:23:17',NULL),
(2,'CLUSTER02','APP','C1 C4 C5',NULL,'<p>desc</p>','1.2.3.4','2025-10-19 09:15:31','2025-10-19 09:23:30',NULL),
(3,'CLUSTER03','DB','C1 C4',NULL,'<p>Desc</p>','1.2.3.4','2025-10-19 09:25:13','2025-10-19 09:25:13',NULL);
/*!40000 ALTER TABLE `clusters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `container_database`
--

LOCK TABLES `container_database` WRITE;
/*!40000 ALTER TABLE `container_database` DISABLE KEYS */;
/*!40000 ALTER TABLE `container_database` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `container_logical_server`
--

LOCK TABLES `container_logical_server` WRITE;
/*!40000 ALTER TABLE `container_logical_server` DISABLE KEYS */;
/*!40000 ALTER TABLE `container_logical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `containers`
--

LOCK TABLES `containers` WRITE;
/*!40000 ALTER TABLE `containers` DISABLE KEYS */;
/*!40000 ALTER TABLE `containers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `data_processing`
--

LOCK TABLES `data_processing` WRITE;
/*!40000 ALTER TABLE `data_processing` DISABLE KEYS */;
INSERT INTO `data_processing` (`id`, `name`, `legal_basis`, `description`, `responsible`, `purpose`, `categories`, `recipients`, `transfert`, `retention`, `controls`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'User account management','Legal obligation / Legitimate interest.','<p>Creation, modification and deletion of IT accounts for access to internal digital services.</p>','<p>Information Systems Manager.</p>','<p>Information system access rights management.</p>','<p>Internal staff (employees, trainees).<br>Technical provider</p>','<p>Internal IT team<br>DPO<br>IAM provider</p>','<p>No transfers outside the EU.</p>','<p>1 year after end of contract or departure of user.</p>',NULL,'2025-06-14 10:55:47','2025-06-16 04:36:28',NULL),
(2,'Traceability, detection and management of cybersecurity incidents','Legal obligation / Legitimate interest.','<p>Recording and analysis of incidents or anomalies affecting IS security (e.g. attempted intrusion, account compromise).</p>','<p>Information Systems Security Manager (CISO).</p>','<p>Traceability, detection and management of cybersecurity incidents.</p>','<ul><li>CISO, CIO (category: <strong>Internal service</strong>)</li><li>Cybersecurity service providers (category: <strong>Technical service provider</strong>)</li><li>Competent authorities in the event of notification (category: <strong>Public authority</strong>)</li></ul>','<p>CISO, CIO, cybersecurity providers.</p>','<p>None, except judicial requisition with international cooperation.</p>','<p>Three years after the incident was closed.</p>',NULL,'2025-06-14 10:56:18','2025-06-14 11:01:37',NULL),
(3,'Information system analysis (mapping)','Legitimate interest.','<p>Gathering and structuring information on assets, their flows and those responsible for them, as part of a risk management approach.Information systems manager.</p>','<p>Information Systems Manager.</p>','<p>Referencing and analysis of IS components for security, audit and compliance purposes.</p>','<p>In-house<br>Authorized third-party service provider</p>','<p>IT team<br>CISO, internal auditors<br>External auditors<br>&nbsp;</p>','<p>None.</p>','<p>Data retained as long as the asset is present in the IS.</p>',NULL,'2025-06-14 11:12:15','2025-06-16 04:34:29',NULL),
(4,'Computerized patient record (CPR) management','Public interest mission','<p>Record, update and consult patient health data relating to the care provided by the facility (diagnoses, prescriptions, reports, imaging, etc.).</p>','<p>Hospital Director / Medical Director.</p>','<p>Provide medical and administrative care for patients.</p>','<ul><li>&nbsp;Authorized internal service</li><li>&nbsp;Internal service</li><li>Authorized technical service provider</li><li>Authorized public or private organizations</li><li>Public authority</li></ul>','<ul><li>Health professionals (doctors, nurses, medical secretaries) – <strong>Category</strong>:</li><li>Internal administrative services (billing, admissions) – <strong>Category</strong>:</li><li>Approved health data host (HDS) – <strong>Category</strong> :&nbsp;</li><li>Social security organizations, mutual societies – <strong>Category</strong>:&nbsp;</li><li>Health authorities (ARS, CNAM, etc.) – <strong>Category</strong>:&nbsp;</li></ul>','<p>All data is hosted by an HDS certified service provider located in Luxembourg or the EU.</p>','<p>20 years after the last treatment (Public Health Code, art. R1112-7)</p>',NULL,'2025-06-14 11:15:41','2025-06-16 04:37:57',NULL);
/*!40000 ALTER TABLE `data_processing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `data_processing_document`
--

LOCK TABLES `data_processing_document` WRITE;
/*!40000 ALTER TABLE `data_processing_document` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_processing_document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `data_processing_information`
--

LOCK TABLES `data_processing_information` WRITE;
/*!40000 ALTER TABLE `data_processing_information` DISABLE KEYS */;
INSERT INTO `data_processing_information` (`data_processing_id`, `information_id`) VALUES (4,19),
(4,8),
(4,15),
(4,4),
(1,10),
(1,13),
(3,18),
(2,18);
/*!40000 ALTER TABLE `data_processing_information` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `data_processing_m_application`
--

LOCK TABLES `application_data_processing` WRITE;
/*!40000 ALTER TABLE `application_data_processing` DISABLE KEYS */;
INSERT INTO `application_data_processing` (`data_processing_id`, `application_id`) VALUES (4,1),
(1,13),
(3,6),
(3,3),
(3,2),
(3,1),
(3,8),
(3,4),
(3,7),
(3,12),
(3,15),
(3,14),
(3,5),
(3,10),
(3,9),
(3,11),
(3,13),
(2,6),
(2,3),
(2,2),
(2,1),
(2,8),
(2,4),
(2,7),
(2,12),
(2,15),
(2,14),
(2,5),
(2,10),
(2,9),
(2,11),
(2,13);
/*!40000 ALTER TABLE `application_data_processing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `data_processing_process`
--

LOCK TABLES `data_processing_process` WRITE;
/*!40000 ALTER TABLE `data_processing_process` DISABLE KEYS */;
INSERT INTO `data_processing_process` (`data_processing_id`, `process_id`) VALUES (4,19),
(4,18),
(4,5),
(1,9),
(3,36),
(3,19),
(3,6),
(3,32),
(3,33),
(3,38),
(3,11),
(3,39),
(3,18),
(3,13),
(3,31),
(3,24),
(3,9),
(3,34),
(3,26),
(3,21),
(3,27),
(3,12),
(3,37),
(3,7),
(3,14),
(3,29),
(3,28),
(3,10),
(3,30),
(3,5),
(2,36),
(2,19),
(2,6),
(2,32),
(2,33),
(2,38),
(2,11),
(2,39),
(2,18),
(2,13),
(2,31),
(2,24),
(2,9),
(2,34),
(2,26),
(2,21),
(2,27),
(2,12),
(2,37),
(2,7),
(2,14),
(2,29),
(2,28),
(2,10),
(2,30),
(2,5);
/*!40000 ALTER TABLE `data_processing_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `database_entity`
--

LOCK TABLES `database_entity` WRITE;
/*!40000 ALTER TABLE `database_entity` DISABLE KEYS */;
INSERT INTO `database_entity` (`database_id`, `entity_id`) VALUES (2,3),
(3,3),
(4,4);
/*!40000 ALTER TABLE `database_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `database_information`
--

LOCK TABLES `database_information` WRITE;
/*!40000 ALTER TABLE `database_information` DISABLE KEYS */;
INSERT INTO `database_information` (`database_id`, `information_id`) VALUES (1,8),
(1,14),
(3,7),
(3,15),
(3,6),
(4,14),
(4,4);
/*!40000 ALTER TABLE `database_information` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `database_logical_server`
--

LOCK TABLES `database_logical_server` WRITE;
/*!40000 ALTER TABLE `database_logical_server` DISABLE KEYS */;
INSERT INTO `database_logical_server` (`database_id`, `logical_server_id`) VALUES (2,1),
(1,3),
(3,3),
(1,5);
/*!40000 ALTER TABLE `database_logical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_database`
--

LOCK TABLES `application_database` WRITE;
/*!40000 ALTER TABLE `application_database` DISABLE KEYS */;
INSERT INTO `application_database` (`application_id`, `database_id`) VALUES (1,1),
(3,2),
(2,3),
(12,3),
(1,4),
(5,4);
/*!40000 ALTER TABLE `application_database` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `databases`
--

LOCK TABLES `databases` WRITE;
/*!40000 ALTER TABLE `databases` DISABLE KEYS */;
INSERT INTO `databases` (`id`, `name`, `type`, `description`, `responsible`, `external`, `entity_resp_id`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'MEDIC','DB3','<p>Medical database</p>','Paul','Inner',15,4,4,4,4,NULL,'2025-06-10 10:06:13','2025-06-12 13:14:11',NULL),
(2,'LIBRARY','MySQL','<p>Medical publications database</p>','Paul','Inner',9,1,1,1,1,NULL,'2025-06-12 12:48:43','2025-06-12 13:02:56',NULL),
(3,'ACCOUNTING','SOPs','<p>Accounting database</p>','Paul','Inner',3,4,4,4,4,NULL,'2025-06-12 12:50:49','2025-06-14 05:55:03',NULL),
(4,'DNA','MySQL','<p>DNA database</p>','Paul',NULL,2,4,4,4,4,NULL,'2025-06-12 12:52:36','2025-06-12 12:53:05',NULL);
/*!40000 ALTER TABLE `databases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `dhcp_servers`
--

LOCK TABLES `dhcp_servers` WRITE;
/*!40000 ALTER TABLE `dhcp_servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `dhcp_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `dnsservers`
--

LOCK TABLES `dnsservers` WRITE;
/*!40000 ALTER TABLE `dnsservers` DISABLE KEYS */;
/*!40000 ALTER TABLE `dnsservers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `document_external_connected_entity`
--

LOCK TABLES `document_external_connected_entity` WRITE;
/*!40000 ALTER TABLE `document_external_connected_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_external_connected_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `document_logical_server`
--

LOCK TABLES `document_logical_server` WRITE;
/*!40000 ALTER TABLE `document_logical_server` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_logical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `document_relation`
--

LOCK TABLES `document_relation` WRITE;
/*!40000 ALTER TABLE `document_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` (`id`, `filename`, `mimetype`, `size`, `hash`, `deleted_at`, `created_at`, `updated_at`) VALUES (1,'module.png','image/png',21332,'56a60db4a6b89f3d7cbe8545d64fe1340564a1c0856564d781fe2429b87092c7',NULL,'2025-10-19 09:23:17','2025-10-19 09:23:17'),
(2,'server.png','image/png',17981,'a2581758e69692c3df3fef17e95c6c7737c2f091d0a21b31ad0c251df529957e',NULL,'2025-10-19 09:23:30','2025-10-19 09:23:30'),
(3,'macroprocess.png','image/png',22229,'be9bb36504f2ff6f89bd275b21fbb091209facf46afb72d5a2b7bb7b8bdf6382',NULL,'2025-10-19 09:25:13','2025-10-19 09:25:13');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `domain_forest_ad`
--

LOCK TABLES `domain_forest_ad` WRITE;
/*!40000 ALTER TABLE `domain_forest_ad` DISABLE KEYS */;
INSERT INTO `domain_forest_ad` (`forest_ad_id`, `domain_id`) VALUES (1,1);
/*!40000 ALTER TABLE `domain_forest_ad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
INSERT INTO `domains` (`id`, `name`, `description`, `domain_ctrl_cnt`, `user_count`, `machine_count`, `relation_inter_domaine`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OPENHOST','<p>Open Hospital Active Directory Domain</p>',1,120,30,'N / A','2025-06-12 11:24:48','2025-06-12 11:24:48',NULL);
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `entities`
--

LOCK TABLES `entities` WRITE;
/*!40000 ALTER TABLE `entities` DISABLE KEYS */;
INSERT INTO `entities` (`id`, `name`, `icon_id`, `security_level`, `contact_point`, `description`, `type`, `attributes`, `reference`, `parent_entity_id`, `external_ref_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Big Health Tech',NULL,'<p>ISO 27001 - HDS</p>','<p>Technical Support<br><a href="mailto:support@bighealthtech.com">support@bighealthtech.com</a><br>---<br>John Borg&nbsp;<br>Sales Manager<br><a href="mailto:john@gibhealthtech.com">john@gibhealthtech.com</a><br>+33 45 67 89 01<br>&nbsp;</p>','<p>Company publishing the o</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-10 16:53:12','2025-06-10 16:54:20',NULL),
(2,'OPENHOSP-IT',NULL,'<p>ISO 27001</p>','<p>Email: helpdesks@openhop.net<br>Tel: 88 800</p>','<p>Open Hospital IT Department</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 12:16:17','2025-06-12 12:25:34',NULL),
(3,'OPENHOSP',NULL,'<p>CERT-Med+</p>','<p>Mail: <a href="mailto:contact@openhosp.net">contact@openhosp.net</a><br>Tel: +33 44</p>','<p>The Open Hospital</p>','Internal',NULL,NULL,NULL,NULL,'2025-06-12 12:16:56','2025-06-14 05:55:03',NULL),
(4,'OPENHOSP-LAB',NULL,'<p>None</p>','<p>Mail: <a href="mailto:labo@opennosp.net">labo@opennosp.net</a><br>Tel: 23 45</p><p>&nbsp;</p>','<p>Open Hospital Laboratory</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 12:17:53','2025-06-12 12:18:43',NULL),
(5,'OPENHOSP-DIR',NULL,'<p>None</p>','<p>Mail: <a href="mailto:direction@openhosp.net">direction@openhosp.net</a><br>Tel: 57 32</p>','<p>Directorate of the Open Hospital</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 12:19:31','2025-06-12 12:20:24',NULL),
(6,'OPENHOSP-COM',NULL,'<p>None</p>','<p>mail: <a href="mailto:comminucation@openhosp.net">comminucation@openhosp.net</a><br>Tel: 859 43</p>','<p>Communication unit of the Open Hospital</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 12:21:18','2025-06-12 12:21:18',NULL),
(7,'OPENHOSP-URG',NULL,'<p>None</p>','<p>Mail: <a href="mailto:urgences@openhosp.net">urgences@openhosp.net</a><br>tel: 11 11</p>','<p>Open Hospital Emergency Department</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 12:22:13','2025-06-12 12:22:13',NULL),
(8,'OPENHOSP-RX',NULL,'<p>None</p>','<p>Mail: <a href="mailto:radiologie@openhosp.net">radiologie@openhosp.net</a><br>Tel: 57 43</p>','<p>Radiology department</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 12:24:23','2025-06-12 12:24:23',NULL),
(9,'Medi+',NULL,'<p>None</p>','<p>Mail: <a href="mailto:Support@mediplus.com">support@mediplus.com</a><br>Tel: 12 43 43</p>','<p>Medical application editor</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 12:47:34','2025-06-12 13:02:56',NULL),
(10,'General Sys',NULL,'<p>ISO 27001 - SYS/DSS 32</p>','<p>Mail: <a href="mailto:contact@general-sys.com">contact@general-sys.com</a><br>Tel: 32 54 65</p>','<p>Software publishing company</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 12:56:14','2025-06-12 12:56:14',NULL),
(11,'LTR',NULL,'<p>None</p>','<p>Paul Right<br>Tel: 32 54 32<br>Email: paul@ltr.com</p>','<p>Little Things Right - Consulting</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 12:57:43','2025-06-12 12:57:43',NULL),
(12,'NONESoft',NULL,'<p>None</p>','<p>Mail: <a href="mailto:info@nonesoft.com">info@nonesoft.com</a><br>Tel: 32 432 432</p>','<p>No more Software LTD</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 13:01:26','2025-06-12 13:01:26',NULL),
(13,'HAL',NULL,'<p>CSP+, ISO 27001, FDM, RRLF, FOSDEM</p>','<p>Mail: <a href="mailto:contact@hal.com">contact@hal.com</a><br>Tel: 32 43 54</p>','<p>Big IT provider</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 13:02:39','2025-06-12 13:02:39',NULL),
(14,'BigBrainLab',NULL,'<p>ISO 27001</p>','<p>Mail: <a href="mailto:info@bigbrain.com">info@bigbrain.com</a><br>Tel: 99 43 74</p>','<p>The Big Brain Laboratory</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 13:04:14','2025-06-12 13:04:14',NULL),
(15,'Tech24',NULL,'<p>ISO 27001 - HDS</p>','<p>Mail: <a href="mailto:tech@tech24.com">tech@tech24.com</a><br>Phone: 21 45 32</p>','<p>The Tech 24 application provider</p>','Supplier','extern',NULL,NULL,NULL,'2025-06-12 13:14:11','2025-06-12 13:14:11',NULL),
(16,'OHF',NULL,'<p>ISO 27001</p>','<p>Mail: <a href="mailto:contact@ohf.net">contact@ohf.net</a><br>Tel: 32 54 23</p>','<p>Open Hospital Federation</p>','Supplier',NULL,NULL,NULL,NULL,'2025-06-12 13:24:04','2025-06-12 13:24:04',NULL),
(17,'OPENHOSP-HR',NULL,'<p>None</p>','<p>Mail: <a href="mailto:rh@openhosp.net">rh@openhosp.net</a><br>Tel: 87 43 54</p>','<p>Human Resources Department</p>','Internal',NULL,NULL,3,NULL,'2025-06-12 17:04:31','2025-06-12 17:04:31',NULL);
/*!40000 ALTER TABLE `entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `entity_document`
--

LOCK TABLES `entity_document` WRITE;
/*!40000 ALTER TABLE `entity_document` DISABLE KEYS */;
/*!40000 ALTER TABLE `entity_document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_entity`
--

LOCK TABLES `application_entity` WRITE;
/*!40000 ALTER TABLE `application_entity` DISABLE KEYS */;
INSERT INTO `application_entity` (`application_id`, `entity_id`) VALUES (3,3),
(4,3),
(5,4),
(6,3),
(7,2),
(8,2),
(9,3),
(10,3),
(12,17),
(11,17),
(13,2),
(14,3),
(15,3),
(2,3),
(1,3);
/*!40000 ALTER TABLE `application_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `entity_process`
--

LOCK TABLES `entity_process` WRITE;
/*!40000 ALTER TABLE `entity_process` DISABLE KEYS */;
INSERT INTO `entity_process` (`process_id`, `entity_id`) VALUES (18,1),
(24,1),
(9,2),
(21,4),
(6,5),
(11,5),
(14,5),
(6,6),
(5,7),
(18,8),
(5,8),
(24,9),
(34,10),
(9,11),
(9,12),
(9,13),
(21,14),
(19,15),
(18,15),
(5,15),
(7,16),
(28,17),
(36,3),
(19,3),
(6,3),
(32,3),
(33,3),
(38,3),
(11,3),
(39,3),
(18,3),
(13,3),
(31,3),
(24,3),
(9,3),
(34,3),
(26,3),
(21,3),
(27,3),
(12,3),
(37,3),
(7,3),
(14,3),
(29,3),
(28,3),
(10,3),
(30,3),
(5,3);
/*!40000 ALTER TABLE `entity_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `external_connected_entities`
--

LOCK TABLES `external_connected_entities` WRITE;
/*!40000 ALTER TABLE `external_connected_entities` DISABLE KEYS */;
/*!40000 ALTER TABLE `external_connected_entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `external_connected_entity_subnetwork`
--

LOCK TABLES `external_connected_entity_subnetwork` WRITE;
/*!40000 ALTER TABLE `external_connected_entity_subnetwork` DISABLE KEYS */;
/*!40000 ALTER TABLE `external_connected_entity_subnetwork` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_flows`
--

LOCK TABLES `application_flows` WRITE;
/*!40000 ALTER TABLE `application_flows` DISABLE KEYS */;
INSERT INTO `application_flows` (`id`, `name`, `attributes`, `description`, `application_source_id`, `service_source_id`, `module_source_id`, `database_source_id`, `application_dest_id`, `service_dest_id`, `module_dest_id`, `database_dest_id`, `crypted`, `bidirectional`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Medical Billing',NULL,'<p>Sending billing to patients</p>',1,NULL,NULL,NULL,2,NULL,NULL,NULL,0,0,'API','2025-06-12 17:29:15','2025-06-12 17:29:15',NULL),
(2,'Availability',NULL,'<p>Availability of caregivers</p>',4,NULL,NULL,NULL,1,NULL,NULL,NULL,0,0,'API','2025-06-12 17:29:45','2025-06-12 17:29:45',NULL),
(3,'Guards',NULL,'<p>Payment of guards</p>',4,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:30:10','2025-06-12 17:30:17',NULL),
(4,'Medical Services',NULL,'<p>Payment of services</p>',1,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:30:39','2025-06-12 17:30:39',NULL),
(5,'Recruitment',NULL,'<p>Recruitment management</p>',12,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:31:16','2025-06-12 17:31:16',NULL),
(6,'Recruitment',NULL,'<p>Management of new employees</p>',12,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:34:20','2025-06-12 17:34:20',NULL),
(7,'Synchronization',NULL,'<p>Add and delete users</p>',11,NULL,NULL,NULL,13,NULL,NULL,NULL,0,0,'API','2025-06-12 17:45:07','2025-06-12 17:45:07',NULL),
(8,'Pictures',NULL,'<p>Add data to the medical record</p>',9,NULL,NULL,NULL,1,NULL,NULL,NULL,0,0,'API','2025-06-12 17:46:21','2025-06-12 17:46:21',NULL),
(9,'Prescriptions',NULL,'<p>Management of medical prescriptions</p>',10,NULL,NULL,NULL,1,NULL,NULL,NULL,0,0,'API','2025-06-12 17:47:42','2025-06-12 17:47:42',NULL);
/*!40000 ALTER TABLE `application_flows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `forest_ads`
--

LOCK TABLES `forest_ads` WRITE;
/*!40000 ALTER TABLE `forest_ads` DISABLE KEYS */;
INSERT INTO `forest_ads` (`id`, `name`, `description`, `zone_admin_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Open source LDAP','<p>Open Hospital Active Directory Forest</p>',1,'2025-06-12 11:23:11','2025-06-12 11:28:33',NULL);
/*!40000 ALTER TABLE `forest_ads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gateways`
--

LOCK TABLES `gateways` WRITE;
/*!40000 ALTER TABLE `gateways` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateways` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `information`
--

LOCK TABLES `information` WRITE;
/*!40000 ALTER TABLE `information` DISABLE KEYS */;
INSERT INTO `information` (`id`, `name`, `description`, `owner`, `administrator`, `storage`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `sensitivity`, `constraints`, `retention_period`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Information 1','<p>Description of information 1</p>','Establishment','Administrator Name','Storage type',3,3,3,3,NULL,'Personal data','<p>Description of regulatory and normative constraints</p>',NULL,'2020-06-13 00:06:43','2020-06-22 06:12:26','2020-06-22 06:12:26'),
(2,'information 2','<p>Description of the information</p>',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,NULL,'2020-06-13 00:09:13','2020-06-22 06:12:26','2020-06-22 06:12:26'),
(3,'information 3','<p>Description of information 3</p>','Owner',NULL,NULL,3,3,3,3,NULL,NULL,NULL,NULL,'2020-06-13 00:10:07','2020-06-22 06:12:26','2020-06-22 06:12:26'),
(4,'Patient Name','<p>Patient’s first and last name</p>','Establishment','Open-Hosp','secure',3,3,3,3,NULL,'Personal data','<p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0107/index.html\">Identification of natural persons (2013 law)&nbsp;</a></p><p>Law of June 19, 2013 relating to the identification of natural persons, to the national register of natural persons, à la carte of identity, to the municipal registers of natural persons and amending 1) article 104 of the Civil Code; 2</p><p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0208/index.html\">Identification of natural persons - terms of application (grand-ducal regulation 2013)&nbsp;</a></p><p>Grand-ducal regulation of November 28, 2013 setting out the terms application of the law of June 19, 2013 relating to the identification of natural persons. Terms of application of the law of June 19, 2013 relating to the identification of physical persons</p>',NULL,'2020-07-02 05:58:39','2021-05-19 05:42:48',NULL),
(5,'Social Security Number','<p>13-digit national identification number.</p>','Establishment','Open-Hosp','secure',3,3,3,3,NULL,'Personal data','<p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0107/a107.pdf#page=2\">Law of June 19, 2013</a> relating to the identification of natural persons, the national register of natural persons, the identity card, municipal registers of persons physical</p><p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0208/a208.pdf#page=2\">Grand-ducal regulation of November 28, 2013</a> setting the&nbsp; terms of application of the law of June 19, 2013 relating to the identification of natural persons</p>',NULL,'2020-07-02 06:02:03','2021-05-19 05:45:06',NULL),
(6,'Account number','<p>Bank details - IBAN code</p>','Establishment','Open-Hosp','secure',3,3,3,4,NULL,'Personal data','<p>General Regulation on the Protection of Personal Data (GDPR)</p>',NULL,'2020-07-07 10:48:21','2021-05-25 08:38:11',NULL),
(7,'Address','<p>Physical address of a person - main place of residence</p>','Establishment','Open-Hosp','local',3,3,3,3,NULL,'Personal data','<p>General Regulation on the Protection of Personal Data (GDPR)</p>',NULL,'2020-07-07 10:49:11','2021-05-19 05:42:01',NULL),
(8,'Diagnosis','<p>Identification of the nature of a situation, an illness, a difficulty, etc.</p><p>Reasoning leading to the identification of an illness.</p>','Health professional','Open-Hosp','Secure',3,3,3,4,NULL,'medical data','<p>General Regulation on the Protection of Personal Data (GDPR)</p>',NULL,'2020-07-07 11:42:36','2021-05-25 08:37:42',NULL),
(9,'Prescription/prescription','<p>Act by which the doctor, after a diagnosis, describes the treatment that the patient must follow.</p>','Health professional','Open-Hosp','secure',3,3,3,3,NULL,'medical data','<p>General Regulation on the Protection of Personal Data (GDPR)</p>',NULL,'2020-07-07 11:42:56','2021-05-19 05:45:34',NULL),
(10,'IP address','<p>Identification number that is permanently or temporarily assigned to each device connected to a computer network that uses the Internet Protocol.</p>','Establishment','Open-Hosp','local',2,3,2,2,NULL,'technical data','<p>General Regulation on the Protection of Personal Data (GDPR)</p>',NULL,'2020-07-08 06:19:37','2021-05-25 08:37:26',NULL),
(11,'Email address','<p>Character string used to aOpenHospin e-mail into a computer mailbox.</p>','Establishment','Open-Hosp','local',3,3,3,3,NULL,'Personal data','<p>General regulation on the protection of personal data</p>',NULL,'2020-07-08 06:20:12','2021-05-19 05:43:22',NULL),
(12,'Internal telephone number','<p>Sequence of numbers that uniquely identifies a terminal within a telephone network.</p>','Establishment','Open-Hosp','local',3,3,3,3,NULL,'general data','<p>None</p>',NULL,'2020-07-08 06:21:13','2021-05-19 05:45:23',NULL),
(13,'Name of healthcare professional','<p>Name and surname of a healthcare professional</p>','Establishment','Open-Hosp','audience',2,2,2,2,NULL,'Personal data','<p>None</p>',NULL,'2020-07-08 06:21:44','2021-05-25 08:38:02',NULL),
(14,'Medical data','<p>General medical data from a patient file</p>','Establishment','Open-Hosp','Database',3,3,3,3,NULL,'Medical data',NULL,NULL,'2020-09-04 12:45:08','2021-05-19 05:44:30',NULL),
(15,'Patient administrative data','<p>Administrative data of the patient and these stays</p>','Establishment','Open-Hosp','Database',3,3,3,3,NULL,'Personal data',NULL,NULL,'2020-09-04 14:59:33','2021-05-19 05:43:43',NULL),
(16,'Patient billing data','<p>Patient billing data and these stays</p>','Establishment','Open-Hosp','Database',3,3,3,3,NULL,'Personal data',NULL,NULL,'2020-09-04 15:00:14','2021-05-19 05:44:20',NULL),
(17,'Accounting data','<p>Accounting data</p>','Establishment','Open-Hosp','Database',3,3,3,3,NULL,'Personal data',NULL,NULL,'2020-10-22 09:52:29','2021-05-19 05:44:10',NULL),
(18,'Technical data','<p>Technical data on the internal functioning of the information system</p>','Establishment','Open-Hosp','secure',3,3,3,3,NULL,'technical data',NULL,NULL,'2021-10-26 12:17:08','2021-10-26 12:17:08',NULL),
(19,'Date of birth','<p>Date of birth of a natural person</p>','Establishment','Open-Hosp','local',3,3,3,3,NULL,'Personal data',NULL,NULL,'2021-10-28 03:19:52','2021-10-28 03:20:16',NULL),
(20,'Test data','<p>Data used for testing</p>','Establishment','Open-Hosp','Database',1,2,2,2,NULL,'Test data','<p>Cannot contain production data.</p>',NULL,'2023-04-27 07:57:24','2023-04-27 09:30:47',NULL);
/*!40000 ALTER TABLE `information` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `information_process`
--

LOCK TABLES `information_process` WRITE;
/*!40000 ALTER TABLE `information_process` DISABLE KEYS */;
INSERT INTO `information_process` (`information_id`, `process_id`) VALUES (3,1),
(3,2),
(2,1),
(1,1),
(10,9),
(11,9),
(8,5),
(12,6),
(12,9),
(4,19),
(4,5),
(5,5),
(7,5),
(4,11),
(5,11),
(6,11),
(7,11),
(11,11),
(13,11),
(9,5),
(4,21),
(9,21),
(13,21),
(9,19),
(13,19),
(4,18),
(9,18),
(13,18),
(13,12),
(7,6),
(11,6),
(5,28),
(6,28),
(7,28),
(11,28),
(10,10),
(11,10),
(13,10),
(12,10),
(7,29),
(11,29),
(4,29),
(13,29),
(12,29),
(14,19),
(14,24),
(16,11),
(15,11),
(9,32),
(4,32),
(7,36),
(6,36),
(12,36),
(17,11),
(7,7),
(11,7),
(15,7),
(17,7),
(16,7),
(14,7),
(12,7),
(13,26),
(12,26),
(4,27),
(13,27),
(12,27),
(9,27),
(12,30),
(12,38),
(12,37),
(17,14),
(17,28),
(14,32),
(14,21),
(14,5),
(11,34),
(13,34),
(12,34),
(7,14),
(11,14),
(15,14),
(16,14),
(4,14),
(13,14),
(5,14),
(12,14),
(15,39),
(16,39),
(4,39),
(9,39),
(11,31),
(13,31),
(12,31),
(11,33),
(17,33),
(13,33),
(12,33),
(11,13),
(4,13),
(12,13),
(18,9),
(19,19),
(19,11),
(19,18),
(19,21),
(19,14),
(19,28),
(19,5),
(20,9);
/*!40000 ALTER TABLE `information_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `lan_man`
--

LOCK TABLES `lan_man` WRITE;
/*!40000 ALTER TABLE `lan_man` DISABLE KEYS */;
/*!40000 ALTER TABLE `lan_man` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `lan_wan`
--

LOCK TABLES `lan_wan` WRITE;
/*!40000 ALTER TABLE `lan_wan` DISABLE KEYS */;
/*!40000 ALTER TABLE `lan_wan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `lans`
--

LOCK TABLES `lans` WRITE;
/*!40000 ALTER TABLE `lans` DISABLE KEYS */;
/*!40000 ALTER TABLE `lans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `logical_flows`
--

LOCK TABLES `logical_flows` WRITE;
/*!40000 ALTER TABLE `logical_flows` DISABLE KEYS */;
/*!40000 ALTER TABLE `logical_flows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_logical_server`
--

LOCK TABLES `application_logical_server` WRITE;
/*!40000 ALTER TABLE `application_logical_server` DISABLE KEYS */;
INSERT INTO `application_logical_server` (`application_id`, `logical_server_id`) VALUES (2,2),
(3,1),
(4,2),
(5,2),
(7,3),
(8,3),
(9,2),
(10,3),
(12,3),
(11,3),
(13,3),
(3,7),
(1,1);
/*!40000 ALTER TABLE `application_logical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `logical_server_physical_server`
--

LOCK TABLES `logical_server_physical_server` WRITE;
/*!40000 ALTER TABLE `logical_server_physical_server` DISABLE KEYS */;
INSERT INTO `logical_server_physical_server` (`logical_server_id`, `physical_server_id`) VALUES (4,3),
(5,3),
(6,3);
/*!40000 ALTER TABLE `logical_server_physical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `logical_servers`
--

LOCK TABLES `logical_servers` WRITE;
/*!40000 ALTER TABLE `logical_servers` DISABLE KEYS */;
INSERT INTO `logical_servers` (`id`, `name`, `icon_id`, `type`, `active`, `description`, `net_services`, `configuration`, `operating_system`, `address_ip`, `cpu`, `memory`, `environment`, `disk`, `disk_used`, `install_date`, `update_date`, `attributes`, `patching_frequency`, `next_update`, `domain_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'SRV01',NULL,'APP',1,'<p>Server01</p>','SSH',NULL,'Linux','10.10.25.9','12','64','Prod',512,154,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-11 10:42:11','2025-06-18 08:33:37',NULL),
(2,'SRV02',NULL,'APP',1,'<p>Application server</p>','SSH, HTTP, HTTPS',NULL,'Linux','10.10.25.24','4','10','Prod',120,80,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 11:52:23','2025-06-18 08:33:37',NULL),
(3,'SRV03',NULL,'DEV',1,'<p>Development server</p>','SSH, HTTP, HTTPS',NULL,'Linux','10.10.25.23','4','8','Dev',120,40,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 11:53:52','2025-06-18 08:33:37',NULL),
(4,'DB01',NULL,'DB',1,'<p>Database server 01</p>',NULL,NULL,'Linux','10.10.25.4',NULL,NULL,'Prod',NULL,NULL,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 13:07:22','2025-06-14 09:13:04',NULL),
(5,'DB02',NULL,'DB',1,'<p>Databse server 02</p>',NULL,NULL,'Linux','10.10.25.7','2','32',NULL,512,120,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 13:08:16','2025-06-14 09:13:13',NULL),
(6,'DB-TST',NULL,'DB',1,'<p>Test Database Server</p>','SSH, DB',NULL,'Linux','10.10.25.3','2','10','TEST',1024,130,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 13:09:20','2025-06-18 08:33:37',NULL),
(7,'SRV-DEV',NULL,'DEV',1,'<p>Development server</p>','SSH',NULL,'Linux','10.10.25.8','2','16','Dev',250,50,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 17:52:19','2025-06-18 08:33:37',NULL);
/*!40000 ALTER TABLE `logical_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_events`
--

LOCK TABLES `application_events` WRITE;
/*!40000 ALTER TABLE `application_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_peripheral`
--

LOCK TABLES `application_peripheral` WRITE;
/*!40000 ALTER TABLE `application_peripheral` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_peripheral` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_physical_server`
--

LOCK TABLES `application_physical_server` WRITE;
/*!40000 ALTER TABLE `application_physical_server` DISABLE KEYS */;
INSERT INTO `application_physical_server` (`application_id`, `physical_server_id`) VALUES (2,1);
/*!40000 ALTER TABLE `application_physical_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_process`
--

LOCK TABLES `application_process` WRITE;
/*!40000 ALTER TABLE `application_process` DISABLE KEYS */;
INSERT INTO `application_process` (`application_id`, `process_id`) VALUES (1,11),
(1,18),
(2,11),
(3,24),
(4,28),
(5,18),
(5,5),
(6,6),
(7,9),
(8,9),
(9,19),
(9,18),
(9,5),
(10,19),
(10,18),
(10,5),
(12,28),
(11,28),
(13,28),
(14,36),
(14,6),
(14,32),
(14,31),
(15,36),
(15,32),
(15,33),
(15,39);
/*!40000 ALTER TABLE `application_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_workstation`
--

LOCK TABLES `application_workstation` WRITE;
/*!40000 ALTER TABLE `application_workstation` DISABLE KEYS */;
INSERT INTO `application_workstation` (`application_id`, `workstation_id`) VALUES (1,2),
(1,3),
(1,4),
(1,5),
(5,5),
(10,5),
(3,6),
(1,6),
(10,7),
(9,8),
(1,9),
(1,10),
(3,11),
(2,11),
(3,12),
(1,12),
(12,13),
(1,1),
(15,1),
(14,1),
(15,2),
(14,2),
(14,3),
(14,5),
(14,8),
(15,10),
(14,10),
(15,7),
(14,7);
/*!40000 ALTER TABLE `application_workstation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` (`id`, `name`, `description`, `vendor`, `product`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `responsible`, `functional_referent`, `type`, `icon_id`, `technology`, `external`, `users`, `editor`, `entity_resp_id`, `application_block_id`, `documentation`, `version`, `rto`, `rpo`, `install_date`, `update_date`, `attributes`, `patching_frequency`, `next_update`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Medical File','<p>Medical records management software</p>',NULL,NULL,4,4,4,4,NULL,'Rock','Jeans','Fat Customer',NULL,'Web','Internal','> 100','Tech24',15,3,'//documentation/medical_file',NULL,60,240,'2025-01-01',NULL,'',NULL,NULL,'2025-06-10 10:05:14','2025-06-18 08:28:48',NULL),
(2,'Accounting+','<p>Accounting software</p>',NULL,NULL,3,3,3,3,NULL,'Sue','Rock','Software',NULL,'Web','Internal','10',NULL,13,4,'//Share/Documentation/Accounting',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 11:56:56','2025-06-12 13:05:59',NULL),
(3,'Library+','<p>Medical publications management application</p>',NULL,NULL,1,1,1,1,NULL,'Mark','Mark','Internal',NULL,'Web',NULL,'10',NULL,9,3,NULL,NULL,4320,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 12:46:36','2025-06-12 13:02:56',NULL),
(4,'Guard','<p>Management of hospital guards</p>',NULL,NULL,2,2,2,2,NULL,'David','Julian','Internal',NULL,'Web','Internal','> 100',NULL,2,5,'//Share/Documentation/Guard',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:16:32','2025-06-12 13:16:32',NULL),
(5,'MediLab','<p>Laboratory analysis management</p>',NULL,NULL,0,0,0,0,NULL,'Sophie',NULL,'Internal',NULL,'Web','Internal','10',NULL,2,2,'//Share/Documentation/MediLab',NULL,0,0,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:19:03','2025-06-12 13:19:03',NULL),
(6,'Apache','<p>Apache Web Server</p>',NULL,NULL,2,2,2,2,NULL,'Henry',NULL,'Software',NULL,'Software','external','> 100','Apache Foundation',2,6,'/share/doc/website',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:44:03','2025-06-12 13:44:03',NULL),
(7,'JDev','<p>Java Development Application</p>',NULL,NULL,1,1,1,1,NULL,'Nicholas','Nicholas','Fat Customer',NULL,'Software','Internal','5','JDev',2,6,'//Share/Documentation/JDEV',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:50:59','2025-06-12 13:50:59',NULL),
(8,'GITLab','<p>IT source management</p>',NULL,NULL,1,1,1,1,NULL,'Nicholas','Nicholas','Software',NULL,'Software','Internal','10','GITLab',2,6,'//Share/Documentation/GITLab',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:52:22','2025-06-18 08:29:34',NULL),
(9,'RXMaker','<p>Medical imaging application</p>',NULL,NULL,3,3,3,3,NULL,'Carole','Sylvie','Internal',NULL,'Software','Internal','10','BIG Elec',2,3,'//documentation/RX',NULL,120,120,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:54:46','2025-06-12 13:54:46',NULL),
(10,'PharamaMag','<p>Pharmacy management</p>',NULL,NULL,3,3,3,3,NULL,'Anne','Anne','Software',NULL,'Fat Customer','Internal','30','PharaMaker',2,3,NULL,NULL,120,120,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:57:51','2025-06-12 13:57:51',NULL),
(11,'SalaryPay','<p>Payroll management application</p>',NULL,NULL,3,3,2,3,NULL,'Speedwell','Speedwell','Internal dev',NULL,'Web','Internal','10','OPENHOSP',17,5,'//documentation/SalaryPay',NULL,2880,240,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 15:28:22','2025-06-12 17:08:10',NULL),
(12,'Jobs','<p>Recruitment management application</p>',NULL,NULL,3,3,3,3,NULL,'Speedwell','Speedwell','Software',NULL,'Web','Internal','10','OPENHOSP',2,5,NULL,NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 17:03:00','2025-06-12 17:06:35',NULL),
(13,'SyncAD','<p>Active directory synchronization</p>',NULL,NULL,3,3,3,3,NULL,'Mark','Julian','Internal dev',NULL,'Job','Internal','5','OPENHOSP',2,6,'//documentation/jobs',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 17:33:13','2025-06-12 17:33:13',NULL),
(14,'LibreOffice','<p>Text</p>',NULL,NULL,1,1,1,1,NULL,'Carole','Mark',NULL,NULL,NULL,NULL,'> 100','Apache Foundation',2,1,NULL,NULL,1440,1440,NULL,NULL,'',NULL,NULL,'2025-06-14 05:50:17','2025-06-14 05:50:17',NULL),
(15,'LibreCalc','<p>Spreadsheet</p>',NULL,NULL,1,1,1,1,NULL,'Carole','Mark',NULL,NULL,NULL,NULL,'> 100','Apache Foundation',2,1,NULL,NULL,1440,1440,NULL,NULL,'',NULL,NULL,'2025-06-14 05:51:20','2025-06-14 05:51:20',NULL);
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `macro_processuses`
--

LOCK TABLES `macro_processuses` WRITE;
/*!40000 ALTER TABLE `macro_processuses` DISABLE KEYS */;
INSERT INTO `macro_processuses` (`id`, `name`, `description`, `io_elements`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `owner`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Macro Process 1','<p>Description of the testing macro process<br>Test only</p>','<ul><li>data 1 </li><li>data 2 </li><li>data 3 </li></ul>',2,NULL,NULL,NULL,NULL,'test owner','2020-06-10 07:02:16','2020-06-22 06:07:55','2020-06-22 06:07:55'),
(2,'Maro-process 2','<p>Description of the macro-process</p>',NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-06-13 01:03:42','2020-06-22 06:07:55','2020-06-22 06:07:55'),
(3,'Care',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-08-21 08:32:46','2020-08-21 08:44:59','2020-08-21 08:44:59'),
(4,'Human Resources','<p>Human resources</p>',NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-08-21 08:34:19','2020-08-21 08:41:36','2020-08-21 08:41:36'),
(5,'Running the hospital','<p>Care and management support processes: set of processes that contribute to the smooth running of other processes, by providing them with the necessary resources, both material and intangible.</p>','<p>Incoming:<br>- resource needs<br>Outgoing:<br>- allocation of resources<br>- reporting on quality of care</p>',3,3,3,3,NULL,'Administrative and financial director','2020-08-21 08:38:01','2025-06-14 19:00:27',NULL),
(6,'Run the hospital','<p>Processes which transcribe the strategy, the objectives and make it possible to manage the quality approach while ensuring its continuous improvement.</p>','<p>Incoming: </p><ul><li>information on the functioning of processes </li></ul><p>Outgoing:</p><ul><li>reports</li><li>dashboards</li></ul>',3,3,3,2,NULL,'Administrative and financial director','2020-08-21 08:43:31','2025-06-14 19:00:10',NULL),
(7,'Treat the patient','<p>Patient care process in hospitalization, surgery, outpatient and emergency departments</p>','<p>Incoming:</p><ul><li>Patient Name</li><li>Social Security Number</li><li>Patient Address</li></ul><p>Outgoing:</p><ul><li>Diagnosis</li><li>Prescription</li></ul>',3,3,3,3,NULL,'Medical Director','2020-08-21 08:44:47','2025-06-14 19:00:15',NULL),
(8,'Treat the patient','<p>All clinical support processes: hospital hygiene, laboratory, drug circuit and sterilization</p>','<p>Input:<br>- prescription<br>- medical needs<br>Output:<br>- care provided<br>- medication<br>- Care support</p>',3,3,3,3,NULL,'Medical Director','2020-09-07 07:22:46','2025-06-14 19:00:21',NULL),
(9,'Hospital management',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-09-07 07:28:46','2020-09-07 08:05:55','2020-09-07 08:05:55');
/*!40000 ALTER TABLE `macro_processuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `man_wan`
--

LOCK TABLES `man_wan` WRITE;
/*!40000 ALTER TABLE `man_wan` DISABLE KEYS */;
/*!40000 ALTER TABLE `man_wan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mans`
--

LOCK TABLES `mans` WRITE;
/*!40000 ALTER TABLE `mans` DISABLE KEYS */;
/*!40000 ALTER TABLE `mans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `network_switch_physical_switch`
--

LOCK TABLES `network_switch_physical_switch` WRITE;
/*!40000 ALTER TABLE `network_switch_physical_switch` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_switch_physical_switch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `network_switches`
--

LOCK TABLES `network_switches` WRITE;
/*!40000 ALTER TABLE `network_switches` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_switches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `networks`
--

LOCK TABLES `networks` WRITE;
/*!40000 ALTER TABLE `networks` DISABLE KEYS */;
INSERT INTO `networks` (`id`, `name`, `description`, `protocol_type`, `responsible`, `responsible_sec`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OPENHOSP-INT','<p>Internal hospital network</p>','TCP/IP','Paul','Jeans',3,3,3,3,NULL,'2025-06-12 11:41:43','2025-06-12 11:42:02',NULL);
/*!40000 ALTER TABLE `networks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `operation_task`
--

LOCK TABLES `operation_task` WRITE;
/*!40000 ALTER TABLE `operation_task` DISABLE KEYS */;
INSERT INTO `operation_task` (`operation_id`, `task_id`) VALUES (1,1),
(1,2),
(2,1),
(3,3);
/*!40000 ALTER TABLE `operation_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `operations`
--

LOCK TABLES `operations` WRITE;
/*!40000 ALTER TABLE `operations` DISABLE KEYS */;
INSERT INTO `operations` (`id`, `name`, `description`, `process_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Operation 1','<p>Description of the operation</p>',NULL,'2020-06-13 00:02:42','2020-06-22 06:12:11','2020-06-22 06:12:11'),
(2,'Operation 2','<p>Description of the operation</p>',NULL,'2020-06-13 00:02:58','2020-06-22 06:12:11','2020-06-22 06:12:11'),
(3,'Operation 3','<p>Description of the operation</p>',NULL,'2020-06-13 00:03:11','2020-06-22 06:12:11','2020-06-22 06:12:11'),
(4,'Test operation','<p>Test description</p>',NULL,'2020-07-16 06:53:24','2020-07-24 09:42:13','2020-07-24 09:42:13'),
(5,'Helpdesk','<p>IT support for users</p>',NULL,'2020-08-13 05:44:38','2020-08-13 05:48:19','2020-08-13 05:48:19'),
(6,'Asset inventory','<p>Maintaining IT inventory</p>',NULL,'2020-08-13 06:35:04','2020-08-13 06:37:29',NULL),
(7,'Inventory review','<p>Review of IT asset inventory</p>',NULL,'2020-08-13 06:36:28','2020-08-13 06:36:28',NULL),
(8,'Encoding incidents and requests','<p>Capture incident resolution requests and service requests</p><p>Identification (documentation)</p><p>Categorization (routing to the corresponding frontline group)</p><p>Prioritization (management of the urgency of the incident or request)</p>',NULL,'2020-09-16 12:19:26','2020-09-16 12:19:26',NULL);
/*!40000 ALTER TABLE `operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `peripherals`
--

LOCK TABLES `peripherals` WRITE;
/*!40000 ALTER TABLE `peripherals` DISABLE KEYS */;
/*!40000 ALTER TABLE `peripherals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `phones`
--

LOCK TABLES `phones` WRITE;
/*!40000 ALTER TABLE `phones` DISABLE KEYS */;
/*!40000 ALTER TABLE `phones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_links`
--

LOCK TABLES `physical_links` WRITE;
/*!40000 ALTER TABLE `physical_links` DISABLE KEYS */;
INSERT INTO `physical_links` (`id`, `src_port`, `dest_port`, `peripheral_src_id`, `phone_src_id`, `physical_router_src_id`, `physical_security_device_src_id`, `physical_server_src_id`, `physical_switch_src_id`, `storage_device_src_id`, `wifi_terminal_src_id`, `workstation_src_id`, `logical_server_src_id`, `network_switch_src_id`, `router_src_id`, `peripheral_dest_id`, `phone_dest_id`, `physical_router_dest_id`, `physical_security_device_dest_id`, `physical_server_dest_id`, `physical_switch_dest_id`, `storage_device_dest_id`, `wifi_terminal_dest_id`, `workstation_dest_id`, `logical_server_dest_id`, `network_switch_dest_id`, `router_dest_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,'2025-06-14 05:59:26','2025-06-14 05:59:26',NULL),
(2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,'2025-06-14 05:59:38','2025-06-14 05:59:38',NULL),
(3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,'2025-06-14 05:59:55','2025-06-14 05:59:55',NULL),
(4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,'2025-06-14 06:00:06','2025-06-14 06:00:06',NULL),
(5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,'2025-06-14 06:00:18','2025-06-14 06:00:18',NULL),
(6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,'2025-06-14 06:00:31','2025-06-14 06:00:31',NULL),
(7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5,NULL,NULL,NULL,'2025-06-14 06:02:12','2025-06-14 06:02:12',NULL),
(8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,6,NULL,NULL,NULL,'2025-06-14 06:02:28','2025-06-14 06:02:54',NULL),
(9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,'2025-06-14 06:03:18','2025-06-14 06:03:18',NULL),
(10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,7,NULL,NULL,NULL,'2025-06-14 06:04:06','2025-06-14 06:04:06',NULL),
(11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,8,NULL,NULL,NULL,'2025-06-14 06:04:29','2025-06-14 06:05:08',NULL),
(12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,9,NULL,NULL,NULL,'2025-06-14 06:05:31','2025-06-14 06:05:31',NULL),
(13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,10,NULL,NULL,NULL,'2025-06-14 06:05:51','2025-06-14 06:05:51',NULL),
(14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,11,NULL,NULL,NULL,'2025-06-14 06:06:19','2025-06-14 06:06:19',NULL),
(15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,12,NULL,NULL,NULL,'2025-06-14 06:06:37','2025-06-14 06:06:37',NULL),
(16,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:07:09','2025-06-14 06:07:09',NULL),
(17,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:07:18','2025-06-14 06:07:18',NULL),
(18,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:07:33','2025-06-14 06:07:33',NULL),
(19,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:07:43','2025-06-14 06:07:43',NULL),
(20,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:07:58','2025-06-14 06:07:58',NULL),
(21,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:08:11','2025-06-14 06:08:11',NULL),
(22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,13,NULL,NULL,NULL,'2025-06-14 06:08:34','2025-06-14 06:08:34',NULL),
(23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,'2025-06-14 06:08:45','2025-06-14 06:08:45',NULL),
(24,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:09:15','2025-06-14 06:09:15',NULL),
(25,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:09:24','2025-06-14 06:09:24',NULL),
(26,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:09:34','2025-06-14 06:09:34',NULL),
(27,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 06:09:43','2025-06-14 06:09:43',NULL);
/*!40000 ALTER TABLE `physical_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_router_router`
--

LOCK TABLES `physical_router_router` WRITE;
/*!40000 ALTER TABLE `physical_router_router` DISABLE KEYS */;
INSERT INTO `physical_router_router` (`router_id`, `physical_router_id`) VALUES (1,1);
/*!40000 ALTER TABLE `physical_router_router` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_router_vlan`
--

LOCK TABLES `physical_router_vlan` WRITE;
/*!40000 ALTER TABLE `physical_router_vlan` DISABLE KEYS */;
/*!40000 ALTER TABLE `physical_router_vlan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_routers`
--

LOCK TABLES `physical_routers` WRITE;
/*!40000 ALTER TABLE `physical_routers` DISABLE KEYS */;
INSERT INTO `physical_routers` (`id`, `name`, `description`, `vendor`, `product`, `version`, `type`, `site_id`, `building_id`, `bay_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'R01','<p>Main open router</p>',NULL,NULL,NULL,'Norel',1,12,1,'2025-06-14 05:47:32','2025-06-14 05:47:32',NULL);
/*!40000 ALTER TABLE `physical_routers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_security_device_security_device`
--

LOCK TABLES `physical_security_device_security_device` WRITE;
/*!40000 ALTER TABLE `physical_security_device_security_device` DISABLE KEYS */;
/*!40000 ALTER TABLE `physical_security_device_security_device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_security_devices`
--

LOCK TABLES `physical_security_devices` WRITE;
/*!40000 ALTER TABLE `physical_security_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `physical_security_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_servers`
--

LOCK TABLES `physical_servers` WRITE;
/*!40000 ALTER TABLE `physical_servers` DISABLE KEYS */;
INSERT INTO `physical_servers` (`id`, `name`, `type`, `icon_id`, `description`, `vendor`, `product`, `version`, `responsible`, `configuration`, `site_id`, `building_id`, `bay_id`, `physical_switch_id`, `address_ip`, `cpu`, `memory`, `disk`, `disk_used`, `operating_system`, `install_date`, `update_date`, `patching_group`, `patching_frequency`, `next_update`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Mainframe01',NULL,NULL,'<p>Super Server 01</p>',NULL,NULL,NULL,'John','<p>MAC: 123.456.789.065</p>',1,12,3,NULL,'10.10.1.1','12','4','1024','532','AS/300',NULL,NULL,NULL,NULL,NULL,'2025-06-11 10:25:20','2025-06-12 17:57:42',NULL),
(2,'BigCluster01','HAL',NULL,'<p>Big Cluster Master</p>',NULL,NULL,NULL,'Nestor',NULL,1,12,1,NULL,'10.30.4.5','48','512','1024','304','OS34','2025-01-01 00:00:00',NULL,NULL,NULL,NULL,'2025-06-11 12:57:47','2025-06-18 08:33:37',NULL),
(3,'DataStore','DB',NULL,'<p>The database server</p>',NULL,NULL,NULL,'Paul',NULL,1,12,2,NULL,'10.50.3.1','4','64','3045','2025','DB23',NULL,NULL,NULL,NULL,NULL,'2025-06-11 12:58:58','2025-06-11 12:58:58',NULL),
(4,'BackupList','Storage',NULL,'<p>The backup server</p>',NULL,NULL,NULL,'John',NULL,1,12,2,NULL,'10.10.34.3','4','64','5673','2132','OS34','2025-01-01 00:00:00',NULL,NULL,NULL,NULL,'2025-06-11 13:00:19','2025-06-14 18:27:34',NULL),
(5,'BigCluster02','HAL',NULL,'<p>Big Cluster Slave</p>',NULL,NULL,NULL,NULL,NULL,1,12,1,NULL,NULL,'48','512','1024','394','OS34','2025-01-01 00:00:00',NULL,NULL,NULL,NULL,'2025-06-12 17:54:40','2025-06-18 08:33:37',NULL);
/*!40000 ALTER TABLE `physical_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_switches`
--

LOCK TABLES `physical_switches` WRITE;
/*!40000 ALTER TABLE `physical_switches` DISABLE KEYS */;
INSERT INTO `physical_switches` (`id`, `name`, `type`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `bay_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'SW01','Nortel','<p>1st floor switch</p>',NULL,NULL,NULL,1,1,NULL,'2025-06-14 05:56:03','2025-06-14 05:58:42',NULL),
(2,'SW02','Nortel','<p>Switch 2nd floor</p>',NULL,NULL,NULL,1,6,NULL,'2025-06-14 05:56:19','2025-06-14 05:58:30',NULL),
(3,'SW03','Nortel','<p>Switch 3rd floor</p>',NULL,NULL,NULL,1,15,NULL,'2025-06-14 05:57:18','2025-06-14 05:57:27',NULL),
(4,'SW04','Nortel','<p>Switch 4th floor</p>',NULL,NULL,NULL,1,11,NULL,'2025-06-14 05:58:16','2025-06-14 05:58:16',NULL);
/*!40000 ALTER TABLE `physical_switches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `processes`
--

LOCK TABLES `processes` WRITE;
/*!40000 ALTER TABLE `processes` DISABLE KEYS */;
INSERT INTO `processes` (`id`, `name`, `icon_id`, `description`, `owner`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `in_out`, `macroprocess_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Process 1',NULL,'<p>Description of process 1</p>','OpenHosp - Billing',3,3,3,3,NULL,'<ul><li>apples </li><li>pears </li><li>cherries </li></ul>',NULL,'2020-06-17 14:36:24','2020-06-22 06:12:00','2020-06-22 06:12:00'),
(2,'Process 2',NULL,'<p>Description of process 2</p>','OpenHosp - Admission',3,3,3,3,NULL,NULL,NULL,'2020-06-17 14:36:58','2020-06-22 06:12:00','2020-06-22 06:12:00'),
(3,'Welcome to visitors',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-06-22 13:49:28','2020-06-22 13:49:46','2020-06-22 13:49:46'),
(4,'Human resources',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-06-22 13:50:04','2020-08-21 08:34:48','2020-08-21 08:34:48'),
(5,'EMERGENCIES',NULL,'<p>Reception and care of patients referred to the emergency room / who present to the emergency room</p>','Medical Director',3,3,3,3,NULL,'<p>Entry:<br>- patients</p><p>Exit:<br>- care provided</p>',7,'2020-06-22 13:50:19','2025-06-14 19:00:15',NULL),
(6,'Communication Unit',NULL,'<p>Management of internal and external communication</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- need for communication<br>- context of the organization<br>Output:<br>- communications<br>- reporting</p>',5,'2020-06-22 14:43:24','2025-06-14 19:00:27',NULL),
(7,'Organization and performance',NULL,'<p>Quality/Patient Relations Unit</p><p>Also contains controlling</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Incoming:<br>- &nbsp;Data on the operation of the hospital</p><p>Outgoing:<br>- Statistics<br>- Risk analyses<br>- Reports</p>',6,'2020-06-22 14:50:06','2025-06-14 19:00:10',NULL),
(8,'Care',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-06-22 14:50:23','2020-08-21 08:46:12','2020-08-21 08:46:12'),
(9,'Computer science',NULL,'<p>OpenHosp IT department</p>','CIO of OpenHosp',3,3,3,3,NULL,'<p>Input:&nbsp;<br>- needs for use of information technologies<br>Output:&nbsp;<br>- needs covered</p>',5,'2020-06-24 06:20:23','2025-06-14 19:00:27',NULL),
(10,'Security',NULL,'<p>Physical and environmental security of the hospital</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- physical and environmental security needs<br>Output:<br>- applied physical and environmental security</p>',5,'2020-07-31 11:51:06','2025-06-14 19:00:27',NULL),
(11,'Finances and Patient Reception',NULL,'<p>Is responsible for:</p><ul><li>General accounting</li><li>Accounts payable</li><li>Invoicing</li><li>Front office</li><li>Back office &amp; Telephony </li></ul>','Administrative and Financial Department',3,3,3,3,NULL,'<p>IN:<br>- budgets<br>- invoice<br>- validated offers<br>Out:<br>- accounting reporting</p>',5,'2020-07-31 11:53:40','2025-06-14 19:00:27',NULL),
(12,'Medical-technical',NULL,'<p>Management of medical and care equipment</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- medical equipment needs<br>Output:<br>- managed equipment</p>',5,'2020-07-31 12:05:45','2025-06-14 19:00:27',NULL),
(13,'Hospitality',NULL,'<p>Is responsible for:</p><ul><li>Catering</li><li>Cleaning</li></ul>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- hotel service needs<br>Exit:<br>- hotel needs covered</p>',5,'2020-07-31 12:06:49','2025-06-14 19:00:27',NULL),
(14,'Hospital management',NULL,'<p><strong>The “<strong>steering</strong>” process contributes to the determination of the corporate strategy and the deployment of objectives in the organization.<br>Under the responsibility of management, they make it possible to guide and ensure the coherence of the <strong>implementation and support processes</strong>.</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>IN:<br>- hospital data (Datawarehouse)<br>OUT:<br>- Management reports</p>',6,'2020-08-03 07:32:25','2025-06-14 19:00:10',NULL),
(15,'Recruitment',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:35:10','2020-08-21 08:41:20','2020-08-21 08:41:20'),
(16,'Admission',NULL,'<p>Admission and appointment making</p>',NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:47:46','2020-09-07 07:53:43','2020-09-07 07:53:43'),
(17,'Bed management',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:48:30','2020-09-07 08:02:12','2020-09-07 08:02:12'),
(18,'Hospitalization',NULL,'<p>Treatment of hospitalized patients</p>','Medical Director',3,3,3,3,NULL,'<p>Incoming:<br>- patients</p><p>Outgoing<br>- care provided</p>',7,'2020-08-21 08:51:29','2025-06-14 19:00:15',NULL),
(19,'Ambulatory',NULL,'<p>Patient care services for examinations, therapies, consultations, out-of-hospital care</p>','Medical Director',3,3,3,3,NULL,'<p>Incoming:<br>- patients<br>- pathologies<br>Outgoing:<br>Care provided</p>',7,'2020-08-21 08:51:40','2025-06-14 19:00:15',NULL),
(20,'IT security',NULL,'<p>Computer security</p>','CIO of OpenHosp',3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:54:25','2020-08-24 06:15:57','2020-08-24 06:15:57'),
(21,'Laboratory',NULL,'<p>Management of medical prescriptions, analysis and communication of results</p>','Medical Director',3,3,3,3,NULL,'<p>Incoming:<br>- prescriptions<br>Output:<br>- analysis results</p>',8,'2020-08-21 08:58:05','2025-06-14 19:00:21',NULL),
(22,'E-learning training',NULL,NULL,NULL,3,3,3,3,NULL,NULL,5,'2020-08-21 09:01:30','2021-02-08 06:20:07','2021-02-08 06:20:07'),
(23,'Pharmacy',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 09:15:08','2020-09-07 08:08:45','2020-09-07 08:08:45'),
(24,'Medical information',NULL,'<p>Medical Information Department</p>','Medical Director',3,3,3,3,NULL,'<p>Input:&nbsp;<br>- medical coding<br>- medical procedures provided by OpenHosp<br>Output:&nbsp;<br>- codified procedures</p>',5,'2020-08-21 09:16:07','2025-06-14 19:00:27',NULL),
(25,'Legal Unit',NULL,NULL,'General Management',3,3,3,3,NULL,NULL,NULL,'2020-08-21 09:17:32','2020-09-07 08:01:46','2020-09-07 08:01:46'),
(26,'Infrastructure and logistics',NULL,'<p>Is responsible for:</p><ul><li>Building</li><li>Preventative maintenance</li><li>Construction</li><li>Safety &amp; Environment </li></ul>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- infrastructure and logistics needs<br>Output:<br>- needs covered</p>',5,'2020-08-21 10:22:58','2025-06-14 19:00:27',NULL),
(27,'Logistics and catering',NULL,'<p>Is responsible for:</p><ul><li>Transport Logistics and Patients</li><li>Purchasing and Store</li></ul>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Entry:<br>- transport and catering needs<br>Exit:<br>- covered needs</p>',5,'2020-08-21 10:23:50','2025-06-14 19:00:27',NULL),
(28,'Human Resources',NULL,'<p>OpenHosp human resources management</p>','Human Resources Department',3,3,3,3,NULL,'<p>IN:<br>- recruitment request<br>- ...<br>OUT:&nbsp;<br>- Time management<br>- current staff<br>- salary slips</p>',5,'2020-08-21 10:30:24','2025-06-14 19:00:27',NULL),
(29,'Data protection',NULL,'<p>Ensure proper compliance with the General Data Protection Regulation (GDPR) at OpenHosp.</p>','DPO',3,3,3,3,NULL,'<p>In:<br>- OpenHosp context</p><p>Out:<br>- compliance recommendations</p><p>&nbsp;</p>',5,'2020-08-24 06:16:37','2025-06-14 19:00:27',NULL),
(30,'Sterilization',NULL,'<p>Sterilization service</p>','Medical Director',3,3,3,3,NULL,'<p>Input:<br>- sterilization requests<br>Output:<br>- sterilized equipment</p>',8,'2020-09-07 07:23:04','2025-06-14 19:00:21',NULL),
(31,'Hospital hygiene',NULL,'<p>Hospital hygiene</p>','Medical Director',2,2,2,3,NULL,'<p>Entry:<br>- hygiene needs<br>Exit:<br>- hygiene rules applied</p>',8,'2020-09-07 07:23:37','2025-06-14 19:00:21',NULL),
(32,'Medication circuit',NULL,'<p>Medication management</p>','Medical Director',3,3,3,3,NULL,'<p>Incoming:<br>- ordinances<br>Output:<br>- medications<br>- reporting</p>',8,'2020-09-07 07:23:48','2025-06-14 19:00:21',NULL),
(33,'Construction',NULL,'<p>OpenHosp construction department</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>IN:<br>- Plan<br>- Change<br>Out:<br>- Completion of work</p>',5,'2020-09-07 08:04:54','2025-06-14 19:00:27',NULL),
(34,'Infrastructure',NULL,'<p>Management of the technical infrastructure of OpenHosp</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- technical needs<br>- norms and standards to be respected<br>Output:<br>- functional technical installations</p>',5,'2020-09-07 08:05:20','2025-06-14 19:00:27',NULL),
(35,'Finance',NULL,NULL,NULL,3,3,3,3,NULL,NULL,5,'2020-09-07 08:05:32','2020-09-07 08:11:17','2020-09-07 08:11:17'),
(36,'Purchases',NULL,'<p>Purchasing department</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>In:<br>- purchase request</p><p>Out:<br>- purchase invoices</p>',5,'2020-09-07 08:05:45','2025-06-14 19:00:27',NULL),
(37,'Cleaning',NULL,'<p>OpenHosp cleaning service</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>Input:<br>- cleaning needs<br>Output:<br>- treated areas</p>',5,'2020-09-07 08:10:26','2025-06-14 19:00:27',NULL),
(38,'Waste',NULL,'<p>Hospital waste management</p>','Medical Director',3,3,3,3,NULL,'<p>Input:<br>- waste<br>Output:<br>- recycled/treated waste</p>',5,'2020-09-07 08:30:29','2025-06-14 19:00:27',NULL),
(39,'Information management',NULL,'<p>Medical information management</p>','Administrative and Financial Department',3,3,3,3,NULL,'<p>IN:<br>- international standards<br>- medical procedures performed<br>Out:&nbsp;<br>- coding of medical procedures</p>',6,'2020-09-07 08:53:46','2025-06-14 19:00:10',NULL);
/*!40000 ALTER TABLE `processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `relation_values`
--

LOCK TABLES `relation_values` WRITE;
/*!40000 ALTER TABLE `relation_values` DISABLE KEYS */;
INSERT INTO `relation_values` (`relation_id`, `date_price`, `price`, `created_at`, `updated_at`) VALUES (1,'2025-01-01',65000.00,'2025-06-14 18:30:45','2025-06-14 18:30:45'),
(2,'2025-01-01',125000.00,'2025-06-14 18:32:03','2025-06-14 18:32:03'),
(3,'2025-01-01',12000.00,'2025-06-14 18:33:26','2025-06-14 18:33:26'),
(4,'2025-01-01',75000.00,'2025-06-14 18:35:36','2025-06-14 18:35:36'),
(5,'2025-01-01',20000.00,'2025-06-14 18:36:59','2025-06-14 18:36:59'),
(6,'2025-12-01',65000.00,'2025-06-14 18:38:20','2025-06-14 18:38:20'),
(7,'2025-01-01',125000.00,'2025-06-14 18:39:56','2025-06-14 18:39:56'),
(8,'2025-04-01',25000.00,'2025-06-14 18:44:20','2025-06-14 18:44:20');
/*!40000 ALTER TABLE `relation_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `relations`
--

LOCK TABLES `relations` WRITE;
/*!40000 ALTER TABLE `relations` DISABLE KEYS */;
INSERT INTO `relations` (`id`, `importance`, `name`, `type`, `description`, `source_id`, `destination_id`, `attributes`, `reference`, `responsible`, `order_number`, `active`, `start_date`, `end_date`, `comments`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,2,'Medi+ Licenses','Supplier','<p>Medi+ license provision</p>',9,2,'Valid','1234567','Paul','1234567',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:30:24','2025-06-14 18:30:45',NULL),
(2,3,'RX support','Supplier','<p>Medical imaging support - 24x7</p>',10,17,'Valid','1235948453','Henry','432043284382',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:32:03','2025-06-14 18:32:03',NULL),
(3,2,'LTR advisory mission','Advice','<p>IT Consulting</p>',11,2,'Valid','43943284320','Rock','32443929432',1,'2025-01-01','2025-04-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:33:26','2025-06-14 18:33:26',NULL),
(4,3,'PharmaMan Support','Supplier','<p>Pharmaman Software Support - 24x7</p>',1,4,'Valid','43943294329','Sophie','943294329432',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:35:36','2025-06-14 18:35:36',NULL),
(5,3,'Accounting Software','Supplier','<p>Accounting software license</p>',12,3,'Valid','42343243232','Henry','443224432',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:36:59','2025-06-14 18:36:59',NULL),
(6,3,'Support Tech24','Supplier','<p>24x7 Medical Record software support</p>',15,3,'Valid','43294329432','Rock','424329439',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:38:20','2025-06-14 18:38:20',NULL),
(7,3,'Mainframe Maintenance','Maintenance','<p>24x7 Mainrame maintenance</p>',13,2,'Valid','439432943','Paul','1044384833',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:39:56','2025-06-14 18:39:56',NULL),
(8,3,'Aboratory analysis','Partnership','<p>Laboratory analysis partnership</p>',14,4,'Valid','5943548354','Julie','60545965945',1,'2025-04-01','2026-04-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:44:20','2025-06-14 18:44:20',NULL),
(9,3,'Filitation','Member','<p>Member of the Open Hospital Federation</p>',3,16,'','5439555453','Nathalie','06544569456',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:45:24','2025-06-14 18:45:36',NULL);
/*!40000 ALTER TABLE `relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `routers`
--

LOCK TABLES `routers` WRITE;
/*!40000 ALTER TABLE `routers` DISABLE KEYS */;
INSERT INTO `routers` (`id`, `name`, `type`, `description`, `rules`, `ip_addresses`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'LR01',NULL,'<p>Main player of the Open Hospital</p>',NULL,'10.10.5.25','2025-06-12 11:45:47','2025-10-19 09:42:17',NULL);
/*!40000 ALTER TABLE `routers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_security_control`
--

LOCK TABLES `application_security_control` WRITE;
/*!40000 ALTER TABLE `application_security_control` DISABLE KEYS */;
INSERT INTO `application_security_control` (`security_control_id`, `application_id`) VALUES (7,6),
(5,6),
(6,6),
(2,6),
(3,1),
(10,1),
(1,1),
(7,1),
(5,1),
(9,1),
(6,1),
(8,1),
(2,1),
(4,1),
(6,3),
(8,3);
/*!40000 ALTER TABLE `application_security_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `security_control_process`
--

LOCK TABLES `security_control_process` WRITE;
/*!40000 ALTER TABLE `security_control_process` DISABLE KEYS */;
INSERT INTO `security_control_process` (`security_control_id`, `process_id`) VALUES (3,36),
(10,36),
(1,36),
(7,36),
(9,36),
(6,36),
(8,36),
(2,36),
(4,36);
/*!40000 ALTER TABLE `security_control_process` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `security_controls`
--

LOCK TABLES `security_controls` WRITE;
/*!40000 ALTER TABLE `security_controls` DISABLE KEYS */;
INSERT INTO `security_controls` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Role-Based Access Control (RBAC)','<p>Only authorized professionals can access data according to their function (e.g.: a doctor sees his own patients, but not those of others).</p><p>Implemented via Active Directory, SSO, badge, etc.</p>','2025-06-14 11:22:50','2025-06-14 11:26:02',NULL),
(2,'Access traceability and logging','<p>All access to medical records and critical systems are recorded.</p><p>Logs are kept, analyzed and regularly audited to detect abuse or incidents.</p>','2025-06-14 11:23:12','2025-06-14 11:26:29',NULL),
(3,'Strong authentication','<p>Use of complex passwords, two-factor authentication (2FA) for sensitive access (e.g. remote DPI, administrator access).</p>','2025-06-14 11:23:27','2025-06-14 11:23:27',NULL),
(4,'Automatic session locking','<p>Workstations automatically lock after a few minutes of inactivity, to protect visible screens in treatment areas.</p>','2025-06-14 11:23:45','2025-06-14 11:23:45',NULL),
(5,'Isolation of test environments','<p>Development or test environments do not use real patient data, or they are anonymized/pseudonymized.</p>','2025-06-14 11:24:00','2025-06-14 11:25:45',NULL),
(6,'Regular and tested backups','<p>Encrypted, daily, remote backups, with regular restoration tests.</p><p>Objective: resilience to disasters (fire, ransomware, etc.).</p>','2025-06-14 11:24:15','2025-06-14 11:24:15',NULL),
(7,'Management of authorizations','<p>Authorizations assigned according to the job description, revised upon each departure or change of function.</p><p>Monitoring and traceability of access requests.</p>','2025-06-14 11:24:34','2025-06-14 11:26:15',NULL),
(8,'Staff awareness and training','<p>Annual training in IT security and GDPR for all hospital staff.</p><p>Display of clear instructions on confidentiality in the premises.</p>','2025-06-14 11:24:53','2025-06-14 11:26:22',NULL),
(9,'Continuity and business recovery plan (PCA/PRA)','<p>Written and tested procedures to continue care in the event of a computer failure (e.g.: paper forms, emergency telephone, read-only DPI access, etc.).</p>','2025-06-14 11:25:08','2025-06-14 11:25:08',NULL),
(10,'Encryption of posts and exchanges','<p>Encryption of hard drives of mobile workstations (laptops, tablets).</p><p>Use of TLS encryption for inter-application and secure health messaging (MSSanté) exchanges.</p>','2025-06-14 11:25:26','2025-06-14 11:25:56',NULL);
/*!40000 ALTER TABLE `security_controls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `security_devices`
--

LOCK TABLES `security_devices` WRITE;
/*!40000 ALTER TABLE `security_devices` DISABLE KEYS */;
INSERT INTO `security_devices` (`id`, `name`, `description`, `vendor`, `product`, `version`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'IDS-Rock','<p>Network intrusion detection equipment</p>',NULL,NULL,NULL,'2025-06-12 11:46:25','2025-06-12 11:46:25',NULL);
/*!40000 ALTER TABLE `security_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
INSERT INTO `sites` (`id`, `name`, `icon_id`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'The Open Hospital',NULL,'<p>Address:<br>1, rue de la Santé<br>1024 Bonsite</p>','2025-06-10 08:31:32','2025-06-11 10:23:30',NULL);
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `storage_devices`
--

LOCK TABLES `storage_devices` WRITE;
/*!40000 ALTER TABLE `storage_devices` DISABLE KEYS */;
INSERT INTO `storage_devices` (`id`, `name`, `type`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `bay_id`, `address_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'STORE01','HAL','<p>Big Disk storage</p>',NULL,NULL,NULL,1,12,2,'10.10.25.25','2025-06-14 05:46:40','2025-06-14 09:18:32',NULL);
/*!40000 ALTER TABLE `storage_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `subnetworks`
--

LOCK TABLES `subnetworks` WRITE;
/*!40000 ALTER TABLE `subnetworks` DISABLE KEYS */;
INSERT INTO `subnetworks` (`id`, `name`, `description`, `address`, `ip_allocation_type`, `responsible_exp`, `dmz`, `wifi`, `connected_subnets_id`, `gateway_id`, `zone`, `vlan_id`, `network_id`, `default_gateway`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OPENHOSP-DMZ','<p>Demilitarized zone of the hospital</p>','10.10.25.0/24','Static','Paul','Yes','No',NULL,NULL,'ZONE2',4,1,'10.10.25.1','2025-06-12 11:43:52','2025-06-14 09:25:24',NULL),
(2,'OPENHOSP-LAB','<p>Laboratory network area</p>','10.10.8.0/24','Static','Paul','No','No',NULL,NULL,'No',1,1,'10.10.8.1','2025-06-12 11:44:27','2025-06-14 09:24:53',NULL),
(3,'OPENHOSP-LAN','<p>PC network</p>','10.10.2.0/24','Static','Paul','Yes','No',NULL,NULL,'ZONE1',3,1,'10.10.2.1','2025-06-14 08:57:49','2025-06-14 09:25:10',NULL),
(4,'OPENHOSP-ADIN','<p>Sub network administration</p>','10.10.5.0/24','Dynamic','Paul','Yes','No',NULL,NULL,'DMZ',2,1,'10.10.5.1','2025-06-14 09:05:42','2025-06-14 09:23:39',NULL);
/*!40000 ALTER TABLE `subnetworks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Task 2','Description of task 2','2020-06-13 00:04:07','2020-06-22 06:12:15','2020-06-22 06:12:15'),
(2,'Task 1','Description of task 1','2020-06-13 00:04:21','2020-06-22 06:12:15','2020-06-22 06:12:15'),
(3,'Task 3','Description of task 3','2020-06-13 00:04:41','2020-06-22 06:12:15','2020-06-22 06:12:15');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `vlans`
--

LOCK TABLES `vlans` WRITE;
/*!40000 ALTER TABLE `vlans` DISABLE KEYS */;
INSERT INTO `vlans` (`id`, `name`, `description`, `vlan_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'VLAN-LAB','<p>Laboratory VLAN</p>',1,'2025-06-12 11:54:55','2025-06-12 11:54:55',NULL),
(2,'VLAN-ADMIN','<p>VLAN Administration</p>',10,'2025-06-14 09:19:18','2025-06-14 09:19:18',NULL),
(3,'VLAN-PC','<p>PC Vlan</p>',15,'2025-06-14 09:19:53','2025-06-14 09:19:53',NULL),
(4,'VLAN-SRV','<p>Server VLANs</p>',17,'2025-06-14 09:26:07','2025-06-14 09:26:07',NULL);
/*!40000 ALTER TABLE `vlans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `wans`
--

LOCK TABLES `wans` WRITE;
/*!40000 ALTER TABLE `wans` DISABLE KEYS */;
/*!40000 ALTER TABLE `wans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `wifi_terminals`
--

LOCK TABLES `wifi_terminals` WRITE;
/*!40000 ALTER TABLE `wifi_terminals` DISABLE KEYS */;
INSERT INTO `wifi_terminals` (`id`, `name`, `type`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `address_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'WIFI-DIR','NOTEL','<p>WiFi Direction Terminal</p>',NULL,NULL,NULL,1,13,'10.10.5.11','2025-06-12 12:31:17','2025-06-14 09:15:17',NULL),
(2,'WIFI-LABO','NOTEL','<p>Laboratory WiFi terminal</p>',NULL,NULL,NULL,1,6,'10.10.5.14','2025-06-12 12:32:22','2025-06-14 09:15:39',NULL),
(3,'WIFI-GUESTS','NOTEL','<p>Patient WiFi edge</p>',NULL,NULL,NULL,1,1,'10.10.5.13','2025-06-12 12:33:16','2025-06-14 09:15:27',NULL);
/*!40000 ALTER TABLE `wifi_terminals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `workstations`
--

LOCK TABLES `workstations` WRITE;
/*!40000 ALTER TABLE `workstations` DISABLE KEYS */;
INSERT INTO `workstations` (`id`, `entity_id`, `name`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `physical_switch_id`, `type`, `icon_id`, `operating_system`, `address_ip`, `cpu`, `memory`, `disk`, `user_id`, `other_user`, `status`, `manufacturer`, `model`, `serial_number`, `last_inventory_date`, `warranty_end_date`, `domain_id`, `warranty`, `warranty_start_date`, `warranty_period`, `agent_version`, `update_source`, `network_id`, `network_port_type`, `mac_address`, `purchase_date`, `fin_value`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,NULL,'PC034','<p>PC home</p>',NULL,NULL,NULL,1,1,NULL,'Black',NULL,'Windows','10.10.2.35','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:35:44','2025-06-14 08:59:15',NULL),
(2,NULL,'PC035','<p>PC Consulting</p>',NULL,NULL,NULL,1,3,NULL,'Black',NULL,'Windows','10.10.2.36','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:36:26','2025-06-14 08:59:42',NULL),
(3,NULL,'PC037','<p>PC Consulting</p>',NULL,NULL,NULL,1,4,NULL,'Black',NULL,'Windows','10.10.2.37','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:37:02','2025-06-14 09:00:03',NULL),
(4,NULL,'PC038','<p>PC Emergencies</p>',NULL,NULL,NULL,1,5,NULL,'Black',NULL,'Windows','10.10.2.38','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:37:37','2025-06-14 09:00:16',NULL),
(5,NULL,'PC040','<p>PC Lab</p>',NULL,NULL,NULL,1,6,NULL,'Black',NULL,'Windows','10.10.8.5','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:38:30','2025-06-14 09:00:33',NULL),
(6,NULL,'PC041','<p>PC Lab</p>',NULL,NULL,NULL,1,6,NULL,'Black',NULL,'Windows','10.10.8.6','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:39:24','2025-06-14 09:00:57',NULL),
(7,NULL,'PC043','<p>PC Pharmacy</p>',NULL,NULL,NULL,1,7,NULL,'Black',NULL,'Windows','10.10.8.7','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:40:04','2025-06-14 09:04:09',NULL),
(8,NULL,'PC044','<p>PC RX</p>',NULL,NULL,NULL,1,8,NULL,'Black',NULL,'Windows','10.10.2.40','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:40:43','2025-06-14 09:01:37',NULL),
(9,NULL,'PC045','<p>PC Mediacal</p>',NULL,NULL,NULL,1,9,NULL,'Black',NULL,'Windows','10.10.2.43','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:41:48','2025-06-14 09:02:06',NULL),
(10,NULL,'PC046','<p>PC Medical</p>',NULL,NULL,NULL,1,10,NULL,'Black',NULL,'Windows','10.10.2.41','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:42:30','2025-06-14 09:01:55',NULL),
(11,NULL,'PC047','<p>PC Admin</p>',NULL,NULL,NULL,1,11,NULL,'Black',NULL,'Windows','10.10.2.43','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:43:16','2025-06-14 09:02:17',NULL),
(12,NULL,'PC050','<p>PC Dir</p>',NULL,NULL,NULL,1,14,NULL,'Banana',NULL,'Windows','10.10.2.45','M4','8',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:44:20','2025-06-14 09:11:48',NULL),
(13,NULL,'PC049','<p>PC Technical</p>',NULL,NULL,NULL,1,13,NULL,'Black',NULL,'Windows','10.10.5.12','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:45:51','2025-06-14 09:11:19',NULL);
/*!40000 ALTER TABLE `workstations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `zone_admins`
--

LOCK TABLES `zone_admins` WRITE;
/*!40000 ALTER TABLE `zone_admins` DISABLE KEYS */;
INSERT INTO `zone_admins` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OpenHospital','<p>Main administration area of ​​the Open Hospital</p>','2025-06-11 10:21:37','2025-06-12 11:22:07',NULL);
/*!40000 ALTER TABLE `zone_admins` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-19 11:54:54
