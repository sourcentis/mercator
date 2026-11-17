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
INSERT INTO `activities` (`id`, `name`, `description`, `recovery_time_objective`, `maximum_tolerable_downtime`, `recovery_point_objective`, `maximum_tolerable_data_loss`, `drp`, `drp_link`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Activité 1','<p>Description de l\'activité 1</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-10 13:20:42','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(2,'Activité 2','<p>Description de l\'activité de test</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-10 15:44:26','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(3,'Activité 3','<p>Description de l\'activité 3</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-13 04:57:08','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(4,'Activité 4','<p>Description de l\'acivité 4</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-06-13 04:57:24','2020-06-22 06:12:06','2020-06-22 06:12:06'),
(5,'Helpdesk','<p>Support aux utilisateurs</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:49:05','2020-08-13 05:49:05',NULL),
(6,'Développement','<p>Développement d\'application</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:49:47','2020-08-13 05:49:47',NULL),
(7,'Monitoring informatique','<p>Vérifier le bon fonctionnement des équipements informatique</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:52:47','2020-08-13 05:52:47',NULL),
(8,'Monitoring applicatif','<p>Vérifier le bon fonctionnement des applications informatique</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-08-13 05:53:19','2020-08-13 05:53:19',NULL),
(9,'Admission','<p>Admission des patients dans l\'hôpital</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2020-09-07 07:54:20','2024-10-14 08:01:04',NULL),
(10,'Gestion des plaintes','<p>Processus de gestion de plaintes</p>',NULL,NULL,NULL,NULL,NULL,NULL,'2023-04-12 07:39:25','2024-10-14 08:00:35',NULL);
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
INSERT INTO `actors` (`id`, `name`, `nature`, `type`, `contact`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Jean','Personne','interne',NULL,'2020-06-14 11:02:22','2020-06-22 06:12:20','2020-06-22 06:12:20'),
(7,'Agent Helpdesk','Groupe','Interne','80800 - helpdesk.informatique@hospital.lu','2020-08-13 06:35:31','2021-01-28 14:08:24',NULL),
(8,'Soignant','Groupe','Interne','Néant','2025-06-10 17:29:28','2025-06-10 17:29:28',NULL),
(9,'Médecin','groupe','Interne','Néant','2025-06-10 17:29:47','2025-06-10 17:29:47',NULL),
(10,'Fournisseur','entité','externe','Néant','2025-06-10 17:30:11','2025-06-10 17:30:11',NULL),
(11,'Agent administratis','personne','interne','Néant','2025-06-10 17:30:41','2025-06-10 17:30:41',NULL),
(12,'Recruteur','personne','interne','Néant','2025-06-10 17:31:12','2025-06-10 17:31:12',NULL);
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
INSERT INTO `admin_users` (`id`, `user_id`, `firstname`, `lastname`, `type`, `attributes`, `icon_id`, `description`, `domain_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'M01','Marcel','Dupont','Système','',NULL,'<p>Administrateur Système</p>',1,'2025-06-12 11:29:56','2025-06-12 11:30:37',NULL),
(2,'P02','Paul','Martin','Système','',NULL,'<p>Administrateur système</p>',1,'2025-06-12 11:30:31','2025-06-12 11:30:31',NULL),
(3,'G03','Gus','Schmidt','Réseau','Local',NULL,'<p>Administrateur réseau</p>',1,'2025-06-12 11:31:08','2025-07-01 05:23:07',NULL),
(4,'UD34','Ursula','Dender','Réseau','Local Priv',NULL,NULL,1,'2025-07-01 05:34:19','2025-07-01 05:36:33',NULL);
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `annuaires`
--

LOCK TABLES `annuaires` WRITE;
/*!40000 ALTER TABLE `annuaires` DISABLE KEYS */;
INSERT INTO `annuaires` (`id`, `name`, `description`, `solution`, `created_at`, `updated_at`, `deleted_at`, `zone_admin_id`) VALUES (1,'PHONE','<p>Annuaire téléphonique</p>','TASCO','2025-06-12 11:25:21','2025-06-12 11:25:21',NULL,1),
(2,'OpenLDAP','<p>LDAP + Kerberos + extensions propriétaires</p>','Apache','2025-06-12 11:27:40','2025-06-14 05:52:18',NULL,1);
/*!40000 ALTER TABLE `annuaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `application_blocks`
--

LOCK TABLES `application_blocks` WRITE;
/*!40000 ALTER TABLE `application_blocks` DISABLE KEYS */;
INSERT INTO `application_blocks` (`id`, `name`, `description`, `responsible`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Administration','<p>Applications administratives</p>',NULL,'2025-06-10 09:49:49','2025-06-10 09:49:49',NULL),
(2,'Laboratoire','<p>Applications du laboratoire</p>',NULL,'2025-06-10 09:50:11','2025-06-10 09:50:11',NULL),
(3,'Medical','<p>Applications médicales</p>',NULL,'2025-06-10 09:50:25','2025-06-10 09:50:25',NULL),
(4,'Comptabilité','<p>Logiciels de la comptabilité</p>',NULL,'2025-06-10 10:02:16','2025-06-10 10:02:16',NULL),
(5,'Ressources Humaines','<p>Logiciels de la gestion des ressources humaines</p>',NULL,'2025-06-10 10:02:46','2025-06-10 10:02:46',NULL),
(6,'Informatique','<p>Logiciels du département informatique</p>',NULL,'2025-06-10 10:03:05','2025-06-10 10:03:05',NULL);
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
-- Dumping data for table `application_application_service`
--

LOCK TABLES `application_application_service` WRITE;
/*!40000 ALTER TABLE `application_application_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_application_service` ENABLE KEYS */;
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
INSERT INTO `bays` (`id`, `name`, `description`, `building_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'R01','<p>Rack principal</p>',12,'2025-06-10 08:46:55','2025-06-10 08:46:55',NULL),
(2,'R02','<p>Rack Database / BackupList</p>',12,'2025-06-11 10:24:04','2025-06-11 10:24:04',NULL),
(3,'R03','<p>Mainframe</p>',12,'2025-06-12 17:57:32','2025-06-12 17:57:32',NULL);
/*!40000 ALTER TABLE `bays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` (`id`, `name`, `type`, `description`, `attributes`, `site_id`, `building_id`, `icon_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'101','Hall','<p>Accueil patient</p>','Public',1,16,NULL,'2025-06-10 08:33:42','2025-10-08 13:44:41',NULL),
(2,'102','Salle','<p>Salle d\'attente</p>','Public',1,16,NULL,'2025-06-10 08:34:36','2025-10-08 13:44:41',NULL),
(3,'103','Salle','<p>Salle de Consultation 1</p>','Public Soins',1,16,NULL,'2025-06-10 08:35:13','2025-10-08 13:44:41',NULL),
(4,'104','Salle','<p>Salle de Consultation 2</p>','Public Soins',1,16,NULL,'2025-06-10 08:35:34','2025-10-08 13:44:41',NULL),
(5,'105','Hall','<p>Urgences</p>','Public Soins',1,16,NULL,'2025-06-10 08:38:19','2025-10-08 13:44:41',NULL),
(6,'201','Labo','<p>Laboratoire</p>','Restreint',1,17,NULL,'2025-06-10 08:38:51','2025-10-08 13:42:53',NULL),
(7,'202','Labo','<p>Pharmacie</p>','Restreint',1,17,NULL,'2025-06-10 08:39:35','2025-10-08 13:43:57',NULL),
(8,'205','Salle','<p>Imagerie Médicale</p>','Public',1,17,NULL,'2025-06-10 08:42:11','2025-10-08 13:43:36',NULL),
(9,'303','Salle','<p>Bloc opératoire</p>','Restreint Soins',1,18,NULL,'2025-06-10 08:43:01','2025-10-08 13:43:21',NULL),
(10,'304','Salle','<p>Soins intensifs</p>','Restreint Soins',1,18,NULL,'2025-06-10 08:44:58','2025-10-08 13:44:04',NULL),
(11,'401','Bureau','<p>Administration</p>','Restreint',1,19,NULL,'2025-06-10 08:45:20','2025-10-08 13:44:15',NULL),
(12,'403','IT','<p>Local technique</p>','Restreint',1,19,NULL,'2025-06-10 08:45:44','2025-10-08 13:43:29',NULL),
(13,'404','Bureau','<p>Logistique</p>','Restreint',1,19,NULL,'2025-06-10 08:46:05','2025-10-08 13:44:28',NULL),
(14,'402','Bureau','<p>Direction</p>','Restreint',1,19,NULL,'2025-06-11 10:23:11','2025-10-08 13:44:21',NULL),
(15,'302','IT','<p>Local technique</p>','Restreint',1,18,NULL,'2025-06-14 05:57:11','2025-10-08 13:43:07',NULL),
(16,'ET1','Etage','<p>Premier étage</p>','Public',1,NULL,NULL,'2025-10-08 13:39:08','2025-10-08 13:44:41',NULL),
(17,'ET2','Etage','<p>Etage numéro deux</p>','Public',1,NULL,NULL,'2025-10-08 13:40:44','2025-10-08 13:40:44',NULL),
(18,'ET3','Etage','<p>Etage 3</p>','Restreint',1,NULL,NULL,'2025-10-08 13:41:07','2025-10-08 13:41:07',NULL),
(19,'ET4','Etage','<p>Quatrième étage - Direction + IT</p>','Restreint',1,NULL,NULL,'2025-10-08 13:41:54','2025-10-08 13:41:54',NULL);
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
-- Dumping data for table `application_certificate`
--

LOCK TABLES `application_certificate` WRITE;
/*!40000 ALTER TABLE `application_certificate` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_certificate` ENABLE KEYS */;
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
INSERT INTO `clusters` (`id`, `name`, `type`, `attributes`, `icon_id`, `description`, `address_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'CLUSTER01','XZWare','C1 C2 C3',NULL,'<p>Cluster principal.</p>','10.10.8.2','2025-06-12 11:51:05','2025-10-19 09:23:17',NULL),
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
-- Dumping data for table `application_container`
--

LOCK TABLES `application_container` WRITE;
/*!40000 ALTER TABLE `application_container` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_container` ENABLE KEYS */;
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
INSERT INTO `data_processing` (`id`, `name`, `legal_basis`, `description`, `responsible`, `purpose`, `categories`, `recipients`, `transfert`, `retention`, `controls`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'User account management','Obligation légale / Intérêt légitime.','<p>Creation, modification and deletion of IT accounts for access to internal digital services.</p>','<p>Information Systems Manager.</p>','<p>Information system access rights management.</p>','<p>Internal staff (employees, trainees).<br>Technical provider</p>','<p>Internal IT team<br>DPO<br>IAM provider</p>','<p>No transfers outside the EU.</p>','<p>1 year after end of contract or departure of user.</p>',NULL,'2025-06-14 10:55:47','2025-06-16 04:36:28',NULL),
(2,'Traçabilité, détection et gestion des incidents de cybersécurité','Obligation légale / Intérêt légitime.','<p>Enregistrement et analyse des incidents ou anomalies affectant la sécurité du SI (ex. : tentative d’intrusion, compromission de compte).</p>','<p>Responsable de la sécurité des systèmes d\'information (RSSI).</p>','<p>Traçabilité, détection et gestion des incidents de cybersécurité.</p>','<ul><li>RSSI, DSI (catégorie : <strong>Service interne</strong>)</li><li>Prestataires de cybersécurité (catégorie : <strong>Prestataire technique</strong>)</li><li>Autorités compétentes en cas de notification (catégorie : <strong>Autorité publique</strong>)</li></ul>','<p>RSSI, DSI, prestataires de cybersécurité.</p>','<p>Aucun, sauf réquisition judiciaire avec coopération internationale.</p>','<p>Trois ans après la clôture de l’incident.</p>',NULL,'2025-06-14 10:56:18','2025-06-14 11:01:37',NULL),
(3,'Information system analysis (mapping)','Intérêt légitime.','<p>Gathering and structuring information on assets, their application_flows and those responsible for them, as part of a risk management approach.Information systems manager.</p>','<p>Information Systems Manager.</p>','<p>Referencing and analysis of IS components for security, audit and compliance purposes.</p>','<p>In-house<br>Authorized third-party service provider</p>','<p>IT team<br>CISO, internal auditors<br>External auditors<br>&nbsp;</p>','<p>None.</p>','<p>Data retained as long as the asset is present in the IS.</p>',NULL,'2025-06-14 11:12:15','2025-06-16 04:34:29',NULL),
(4,'Computerized patient record (CPR) management','Mission d’intérêt public','<p>Record, update and consult patient health data relating to the care provided by the facility (diagnoses, prescriptions, reports, imaging, etc.).</p>','<p>Hospital Director / Medical Director.</p>','<p>Provide medical and administrative care for patients.</p>','<ul><li>&nbsp;Service interne habilité</li><li>&nbsp;Service interne</li><li>Prestataire technique habilité</li><li>Organismes publics ou privés autorisés</li><li>Autorité publique</li></ul>','<ul><li>Professionnels de santé (médecins, infirmiers, secrétaires médicales) – <strong>Catégorie</strong> :</li><li>Services administratifs internes (facturation, admissions) – <strong>Catégorie</strong> :</li><li>Hébergeur de données de santé agréé (HDS) – <strong>Catégorie</strong> :&nbsp;</li><li>Organismes de sécurité sociale, mutuelles – <strong>Catégorie</strong> :&nbsp;</li><li>Autorités de santé (ARS, CNAM, etc.) – <strong>Catégorie</strong> :&nbsp;</li></ul>','<p>Toutes les données sont hébergées chez un prestataire certifié HDS situé au Luxembourg ou dans l’UE.</p>','<p>20 ans après la dernière prise en charge (Code de la santé publique, art. R1112-7)</p>',NULL,'2025-06-14 11:15:41','2025-06-16 04:37:57',NULL);
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
-- Dumping data for table `application_data_processing`
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
INSERT INTO `databases` (`id`, `name`, `type`, `description`, `responsible`, `external`, `entity_resp_id`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'MEDIC','DB3','<p>Base de données médicale</p>','Paul','Inerne',15,4,4,4,4,NULL,'2025-06-10 10:06:13','2025-06-12 13:14:11',NULL),
(2,'BIBLIO','MySQL','<p>Base de données des publications médicales</p>','Paul','Inerne',9,1,1,1,1,NULL,'2025-06-12 12:48:43','2025-06-12 13:02:56',NULL),
(3,'COMPTA','SOP','<p>Base de données de la comptabilité</p>','Paul','Inerne',3,4,4,4,4,NULL,'2025-06-12 12:50:49','2025-06-14 05:55:03',NULL),
(4,'ADN','MySQL','<p>Base de données ADN</p>','Paul',NULL,2,4,4,4,4,NULL,'2025-06-12 12:52:36','2025-06-12 12:53:05',NULL);
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
INSERT INTO `domains` (`id`, `name`, `description`, `domain_ctrl_cnt`, `user_count`, `machine_count`, `relation_inter_domaine`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OPENHOST','<p>Domain Active Directory Open Hospital</p>',1,120,30,'N/A','2025-06-12 11:24:48','2025-06-12 11:24:48',NULL);
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `entities`
--

LOCK TABLES `entities` WRITE;
/*!40000 ALTER TABLE `entities` DISABLE KEYS */;
INSERT INTO `entities` (`id`, `name`, `icon_id`, `security_level`, `contact_point`, `description`, `type`, `attributes`, `reference`, `parent_entity_id`, `external_ref_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Big Health Tech',NULL,'<p>ISO 27001 - HDS</p>','<p>Support Technique<br><a href="mailto:support@bighealthtech.com">support@bighealthtech.com</a><br>---<br>John Borg&nbsp;<br>Sales Manager<br><a href="mailto:john@gibhealthtech.com">john@gibhealthtech.com</a><br>+33 45 67 89 01<br>&nbsp;</p>','<p>Société éditrice de l\'o</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-10 16:53:12','2025-06-10 16:54:20',NULL),
(2,'OPENHOSP-IT',NULL,'<p>ISO 27001</p>','<p>Mail : helpdesks@openhop.net<br>Tel: 88 800</p>','<p>Département informatique de l\'Open Hospital</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 12:16:17','2025-06-12 12:25:34',NULL),
(3,'OPENHOSP',NULL,'<p>CERT-Med+</p>','<p>Mail: <a href="mailto:contact@openhosp.net">contact@openhosp.net</a><br>Tel: +33 44</p>','<p>The Open Hospital</p>','Interne',NULL,NULL,NULL,NULL,'2025-06-12 12:16:56','2025-06-14 05:55:03',NULL),
(4,'OPENHOSP-LAB',NULL,'<p>None</p>','<p>Mail : <a href="mailto:labo@opennosp.net">labo@opennosp.net</a><br>Tel : 23 45</p><p>&nbsp;</p>','<p>Labaoratoire de l\'Open Hospital</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 12:17:53','2025-06-12 12:18:43',NULL),
(5,'OPENHOSP-DIR',NULL,'<p>Néant</p>','<p>Mail: <a href="mailto:direction@openhosp.net">direction@openhosp.net</a><br>Tel : 57 32</p>','<p>Direction de l\'open Hospital</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 12:19:31','2025-06-12 12:20:24',NULL),
(6,'OPENHOSP-COM',NULL,'<p>Néant</p>','<p>mail: <a href="mailto:comminucation@openhosp.net">comminucation@openhosp.net</a><br>Tel : 859 43</p>','<p>Cellule communication de l\'Open Hospital</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 12:21:18','2025-06-12 12:21:18',NULL),
(7,'OPENHOSP-URG',NULL,'<p>Néant</p>','<p>Mail : <a href="mailto:urgences@openhosp.net">urgences@openhosp.net</a><br>tel : 11 11</p>','<p>Service des urgence de l\'Open Hospital</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 12:22:13','2025-06-12 12:22:13',NULL),
(8,'OPENHOSP-RX',NULL,'<p>Néant</p>','<p>Mail : <a href="mailto:radiologie@openhosp.net">radiologie@openhosp.net</a><br>Tel : 57 43</p>','<p>Service de radiologie</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 12:24:23','2025-06-12 12:24:23',NULL),
(9,'Medi+',NULL,'<p>None</p>','<p>Mail : <a href="mailto:Support@mediplus.com">support@mediplus.com</a><br>Tel : 12 43 43</p>','<p>Editeur d\'applications médicales</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 12:47:34','2025-06-12 13:02:56',NULL),
(10,'General Sys',NULL,'<p>ISO 27001 - SYS/DSS 32</p>','<p>Mail : <a href="mailto:contact@general-sys.com">contact@general-sys.com</a><br>Tel : 32 54 65</p>','<p>Société éditeur de logiciels</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 12:56:14','2025-06-12 12:56:14',NULL),
(11,'LTR',NULL,'<p>None</p>','<p>Paul Right&nbsp;<br>Tel : 32 54 32<br>Mail : paul@ltr.com</p>','<p>Little Things Right - Consulting</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 12:57:43','2025-06-12 12:57:43',NULL),
(12,'NONESoft',NULL,'<p>None</p>','<p>Mail : <a href="mailto:info@nonesoft.com">info@nonesoft.com</a><br>Tel : 32 432 432</p>','<p>No more Software LTD</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 13:01:26','2025-06-12 13:01:26',NULL),
(13,'HAL',NULL,'<p>CSP+, ISO 27001, FDM, RRLF, FOSDEM</p>','<p>Mail : <a href="mailto:contact@hal.com">contact@hal.com</a><br>Tel : 32 43 54</p>','<p>Big IT provider</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 13:02:39','2025-06-12 13:02:39',NULL),
(14,'BigBrainLab',NULL,'<p>ISO 27001</p>','<p>Mail : <a href="mailto:info@bigbrain.com">info@bigbrain.com</a><br>Tel : 99 43 74</p>','<p>The Big Brain Laboratory</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 13:04:14','2025-06-12 13:04:14',NULL),
(15,'Tech24',NULL,'<p>ISO 27001 - HDS</p>','<p>Mail : <a href="mailto:tech@tech24.com">tech@tech24.com</a><br>Phone : 21 45 32</p>','<p>The Tech 24 application provider</p>','Fournisseur','extern',NULL,NULL,NULL,'2025-06-12 13:14:11','2025-06-12 13:14:11',NULL),
(16,'OHF',NULL,'<p>ISO 27001</p>','<p>Mail : <a href="mailto:contact@ohf.net">contact@ohf.net</a><br>Tel : 32 54 23</p>','<p>Open Hospital Federation</p>','Fournisseur',NULL,NULL,NULL,NULL,'2025-06-12 13:24:04','2025-06-12 13:24:04',NULL),
(17,'OPENHOSP-RH',NULL,'<p>None</p>','<p>Mail : <a href="mailto:rh@openhosp.net">rh@openhosp.net</a><br>Tel : 87 43 54</p>','<p>Service des ressources humaines</p>','Interne',NULL,NULL,3,NULL,'2025-06-12 17:04:31','2025-06-12 17:04:31',NULL);
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
INSERT INTO `application_flows` (`id`, `name`, `attributes`, `description`, `application_source_id`, `service_source_id`, `module_source_id`, `database_source_id`, `application_dest_id`, `service_dest_id`, `module_dest_id`, `database_dest_id`, `crypted`, `bidirectional`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Facturation Médicale',NULL,'<p>Envoie de la facturation aux patients</p>',1,NULL,NULL,NULL,2,NULL,NULL,NULL,0,0,'API','2025-06-12 17:29:15','2025-06-12 17:29:15',NULL),
(2,'Disponibilité',NULL,'<p>Disponibilité des soignants</p>',4,NULL,NULL,NULL,1,NULL,NULL,NULL,0,0,'API','2025-06-12 17:29:45','2025-06-12 17:29:45',NULL),
(3,'Gardes',NULL,'<p>Paiement des gardes</p>',4,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:30:10','2025-06-12 17:30:17',NULL),
(4,'Prestations Médicales',NULL,'<p>Paiement des prestations</p>',1,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:30:39','2025-06-12 17:30:39',NULL),
(5,'Recrutement',NULL,'<p>Gestion des recrutements</p>',12,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:31:16','2025-06-12 17:31:16',NULL),
(6,'Recrutement',NULL,'<p>Gestion des nouveaux employés</p>',12,NULL,NULL,NULL,11,NULL,NULL,NULL,0,0,'API','2025-06-12 17:34:20','2025-06-12 17:34:20',NULL),
(7,'Synchronisation',NULL,'<p>Ajout suppression des utilisateurs</p>',11,NULL,NULL,NULL,13,NULL,NULL,NULL,0,0,'API','2025-06-12 17:45:07','2025-06-12 17:45:07',NULL),
(8,'Images',NULL,'<p>Ajouter des données dans le dossier médical</p>',9,NULL,NULL,NULL,1,NULL,NULL,NULL,0,0,'API','2025-06-12 17:46:21','2025-06-12 17:46:21',NULL),
(9,'Prescriptions',NULL,'<p>Gestion des prescriptions medicales</p>',10,NULL,NULL,NULL,1,NULL,NULL,NULL,0,0,'API','2025-06-12 17:47:42','2025-06-12 17:47:42',NULL);
/*!40000 ALTER TABLE `application_flows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `forest_ads`
--

LOCK TABLES `forest_ads` WRITE;
/*!40000 ALTER TABLE `forest_ads` DISABLE KEYS */;
INSERT INTO `forest_ads` (`id`, `name`, `description`, `zone_admin_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'LDAP open source','<p>Forêt active Directory de l\'Open Hospital</p>',1,'2025-06-12 11:23:11','2025-06-12 11:28:33',NULL);
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
INSERT INTO `information` (`id`, `name`, `description`, `owner`, `administrator`, `storage`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `sensitivity`, `constraints`, `retention_period`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Information 1','<p>Description de l\'information 1</p>','Etablissement','Nom de l\'administrateur','Type de stockage',3,3,3,3,NULL,'Donnée à caractère personnel','<p>Description des contraintes règlementaires et normatives</p>',NULL,'2020-06-13 00:06:43','2020-06-22 06:12:26','2020-06-22 06:12:26'),
(2,'information 2','<p>Description de l\'information</p>',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,NULL,'2020-06-13 00:09:13','2020-06-22 06:12:26','2020-06-22 06:12:26'),
(3,'information 3','<p>Descripton de l\'information 3</p>','Proriétaire',NULL,NULL,3,3,3,3,NULL,NULL,NULL,NULL,'2020-06-13 00:10:07','2020-06-22 06:12:26','2020-06-22 06:12:26'),
(4,'Nom du patient','<p>Nom et prénom du patient</p>','Etablissement','Open-Hosp','sécurisé',3,3,3,3,NULL,'Donnée à caractère personnel','<p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0107/index.html\">Identification des personnes physiques (loi 2013)&nbsp;</a></p><p>Loi du 19 juin 2013 relative à l\'identification des personnes physiques, au registre national des personnes physiques, à la carte d\'identité, aux registres communaux des personnes physiques et portant modification de 1) l\'article 104 du Code civil; 2</p><p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0208/index.html\">Identification des personnes physiques - modalités d\'application (règlement grand-ducal 2013)&nbsp;</a></p><p>Règlement grand-ducal du 28 novembre 2013 fixant les modalités d\'application de la loi du 19 juin 2013 relative à l\'identification des personnes physiques. Modalités d\'application de la loi du 19 juin 2013 relative à l\'identification des personnes phy</p>',NULL,'2020-07-02 05:58:39','2021-05-19 05:42:48',NULL),
(5,'Numéro de sécurité sociale','<p>Numéro d’identification national à 13 positions.</p>','Etablissement','Open-Hosp','sécurisé',3,3,3,3,NULL,'Donnée à caractère personnel','<p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0107/a107.pdf#page=2\">Loi du 19 juin 2013</a> relative à l\'identification des personnes physiques, au registre national des personnes physiques, à la carte d\'identité, aux registres communaux des personnes physiques</p><p><a href=\"http://www.legilux.public.lu/leg/a/archives/2013/0208/a208.pdf#page=2\">Règlement grand-ducal du 28 novembre 2013</a> fixant les&nbsp; modalités d\'application de la loi du 19 juin 2013 relative à l\'identification des personnes physiques</p>',NULL,'2020-07-02 06:02:03','2021-05-19 05:45:06',NULL),
(6,'Numéro de compte','<p>Coordonnées bancaire - code IBAN</p>','Etablissement','Open-Hosp','sécurisé',3,3,3,4,NULL,'Donnée à caractère personnel','<p>Règlement général sur la protection des données à caractère personnel (RGPD)</p>',NULL,'2020-07-07 10:48:21','2021-05-25 08:38:11',NULL),
(7,'Adresse','<p>Adresse physique d\'une personne - lieu d\'habitation principal</p>','Etablissement','Open-Hosp','local',3,3,3,3,NULL,'Donnée à caractère personnel','<p>Règlement général sur la protection des données à caractère personnel (RGPD)</p>',NULL,'2020-07-07 10:49:11','2021-05-19 05:42:01',NULL),
(8,'Diagnostic','<p>Identification de la nature d\'une situation, d\'un mal, d\'une difficulté, etc.</p><p>Raisonnement menant à l\'identification d\'une maladie.&nbsp;</p>','Professionel de santé','Open-Hosp','Sécurisé',3,3,3,4,NULL,'donnée médicale','<p>Règlement général sur la protection des données à caractère personnel (RGPD)</p>',NULL,'2020-07-07 11:42:36','2021-05-25 08:37:42',NULL),
(9,'Prescription / ordonnance','<p>Acte par lequel le médecin, après un diagnostic, décrit le traitement que devra suivre le patient.</p>','Professionel de santé','Open-Hosp','sécurisé',3,3,3,3,NULL,'donnée médicale','<p>Règlement général sur la protection des données à caractère personnel (RGPD)</p>',NULL,'2020-07-07 11:42:56','2021-05-19 05:45:34',NULL),
(10,'Adresse IP','<p>Numéro d\'identification qui est attribué de façon permanente ou provisoire à chaque périphérique relié à un réseau informatique qui utilise l\'Internet Protocol.</p>','Etablissement','Open-Hosp','local',2,3,2,2,NULL,'donnée technique','<p>Règlement général sur la protection des données à caractère personnel (RGPD)</p>',NULL,'2020-07-08 06:19:37','2021-05-25 08:37:26',NULL),
(11,'Adresse électronique','<p>Chaîne de caractères permettant d\'aOpenHospiner du courrier électronique dans une boîte aux lettres informatique.&nbsp;</p>','Etablissement','Open-Hosp','local',3,3,3,3,NULL,'Donnée à caractère personnel','<p>Réglement général sur la protection des données à caractère personnel</p>',NULL,'2020-07-08 06:20:12','2021-05-19 05:43:22',NULL),
(12,'Numéro de téléphone interne','<p>Suite de chiffres qui identifie de façon unique un terminal au sein d\'un réseau téléphonique.</p>','Etablissement','Open-Hosp','local',3,3,3,3,NULL,'donnée générale','<p>Néant</p>',NULL,'2020-07-08 06:21:13','2021-05-19 05:45:23',NULL),
(13,'Nom du professionel de santé','<p>Nom et prénom d\'un professionnel de santé</p>','Etablissement','Open-Hosp','public',2,2,2,2,NULL,'Donnée à caractère personnel','<p>Néant</p>',NULL,'2020-07-08 06:21:44','2021-05-25 08:38:02',NULL),
(14,'Données médicales','<p>Données générales médicales d\'un dossier patient</p>','Etablissement','Open-Hosp','Base de données',3,3,3,3,NULL,'Donnée médicale',NULL,NULL,'2020-09-04 12:45:08','2021-05-19 05:44:30',NULL),
(15,'Données administratives patient','<p>Données administratives du patient et de ces séjours</p>','Etablissement','Open-Hosp','Base de données',3,3,3,3,NULL,'Donnée à caractère personnel',NULL,NULL,'2020-09-04 14:59:33','2021-05-19 05:43:43',NULL),
(16,'Données facturation patient','<p>Données de facturation du patient et de ces séjours</p>','Etablissement','Open-Hosp','Base de données',3,3,3,3,NULL,'Donnée à caractère personnel',NULL,NULL,'2020-09-04 15:00:14','2021-05-19 05:44:20',NULL),
(17,'Données comptables','<p>Données comptables</p>','Etablissement','Open-Hosp','Base de données',3,3,3,3,NULL,'Donnée à caractère personnel',NULL,NULL,'2020-10-22 09:52:29','2021-05-19 05:44:10',NULL),
(18,'Données techniques','<p>Données techniques sur le fonctionnement interne du système d\'information</p>','Etablissement','Open-Hosp','sécurisé',3,3,3,3,NULL,'donnée technique',NULL,NULL,'2021-10-26 12:17:08','2021-10-26 12:17:08',NULL),
(19,'Date de naissance','<p>Date de naissance d\'une personne physique</p>','Etablissement','Open-Hosp','local',3,3,3,3,NULL,'Donnée à caractère personnel',NULL,NULL,'2021-10-28 03:19:52','2021-10-28 03:20:16',NULL),
(20,'Données de test','<p>Données utilisées pour des tests</p>','Etablissement','Open-Hosp','Base de données',1,2,2,2,NULL,'Données de test','<p>Ne peut pas contenir de données de production.</p>',NULL,'2023-04-27 07:57:24','2023-04-27 09:30:47',NULL);
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
INSERT INTO `logical_servers` (`id`, `name`, `icon_id`, `type`, `active`, `description`, `net_services`, `configuration`, `operating_system`, `address_ip`, `cpu`, `memory`, `environment`, `disk`, `disk_used`, `install_date`, `update_date`, `attributes`, `patching_frequency`, `next_update`, `domain_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'SRV01',NULL,'APP',1,'<p>Serveur01</p>','SSH',NULL,'Linux','10.10.25.9','12','64','Prod',512,154,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-11 10:42:11','2025-06-18 08:33:37',NULL),
(2,'SRV02',NULL,'APP',1,'<p>Serveur applicatif</p>','SSH, HTTP, HTTPS',NULL,'Linux','10.10.25.24','4','10','Prod',120,80,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 11:52:23','2025-06-18 08:33:37',NULL),
(3,'SRV03',NULL,'DEV',1,'<p>Development server</p>','SSH, HTTP, HTTPS',NULL,'Linux','10.10.25.23','4','8','Dev',120,40,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 11:53:52','2025-06-18 08:33:37',NULL),
(4,'DB01',NULL,'DB',1,'<p>Database server 01</p>',NULL,NULL,'Linux','10.10.25.4',NULL,NULL,'Prod',NULL,NULL,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 13:07:22','2025-06-14 09:13:04',NULL),
(5,'DB02',NULL,'DB',1,'<p>Databse server 02</p>',NULL,NULL,'Linux','10.10.25.7','2','32',NULL,512,120,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 13:08:16','2025-06-14 09:13:13',NULL),
(6,'DB-TST',NULL,'DB',1,'<p>Test Database Server</p>','SSH, DB',NULL,'Linux','10.10.25.3','2','10','TEST',1024,130,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 13:09:20','2025-06-18 08:33:37',NULL),
(7,'SRV-DEV',NULL,'DEV',1,'<p>Serveur de développement</p>','SSH',NULL,'Linux','10.10.25.8','2','16','Dev',250,50,'2025-01-01',NULL,'',NULL,NULL,1,'2025-06-12 17:52:19','2025-06-18 08:33:37',NULL);
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
INSERT INTO `applications` (`id`, `name`, `description`, `vendor`, `product`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `responsible`, `functional_referent`, `type`, `icon_id`, `technology`, `external`, `users`, `editor`, `entity_resp_id`, `application_block_id`, `documentation`, `version`, `rto`, `rpo`, `install_date`, `update_date`, `attributes`, `patching_frequency`, `next_update`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Dossier Médical','<p>Logiciel de gestion des dossier médicaux</p>',NULL,NULL,4,4,4,4,NULL,'Pierre','Jean','Fat Client',NULL,'Web','Internal','> 100','Tech24',15,3,'//documentation/dossier_medical',NULL,60,240,'2025-01-01',NULL,'',NULL,NULL,'2025-06-10 10:05:14','2025-06-18 08:28:48',NULL),
(2,'Compta+','<p>Logiciel comptable</p>',NULL,NULL,3,3,3,3,NULL,'Sue','Pierre','Software',NULL,'Web','Internal','10',NULL,13,4,'//Share/Documentation/Compta',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 11:56:56','2025-06-12 13:05:59',NULL),
(3,'Biblio+','<p>Application de gestion des publications médicales</p>',NULL,NULL,1,1,1,1,NULL,'Marc','Marc','Internal',NULL,'Web',NULL,'10',NULL,9,3,NULL,NULL,4320,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 12:46:36','2025-06-12 13:02:56',NULL),
(4,'Guard','<p>Gestion des gardes hospitalières</p>',NULL,NULL,2,2,2,2,NULL,'David','Julien','Internal',NULL,'Web','Internal','> 100',NULL,2,5,'//Share/Documentation/Guard',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:16:32','2025-06-12 13:16:32',NULL),
(5,'MediLab','<p>Gestion des analyses de laboratoire</p>',NULL,NULL,0,0,0,0,NULL,'Sophie',NULL,'Internal',NULL,'Web','Internal','10',NULL,2,2,'//Share/Documentation/MediLab',NULL,0,0,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:19:03','2025-06-12 13:19:03',NULL),
(6,'Apache','<p>Serveur Web Apache</p>',NULL,NULL,2,2,2,2,NULL,'Henri',NULL,'Logiciel',NULL,'Software','external','> 100','Apache Fundation',2,6,'/share/doc/website',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:44:03','2025-06-12 13:44:03',NULL),
(7,'JDev','<p>Application de Développement Java</p>',NULL,NULL,1,1,1,1,NULL,'Nicolas','Nicolas','Fat Client',NULL,'Software','Internal','5','JDev',2,6,'//Share/Documentation/JDEV',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:50:59','2025-06-12 13:50:59',NULL),
(8,'GITLab','<p>Gestion des sources IT</p>',NULL,NULL,1,1,1,1,NULL,'Nicolas','Nicolas','Logiciel',NULL,'Software','Internal','10','GITLab',2,6,'//Share/Documentation/GITLab',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:52:22','2025-06-18 08:29:34',NULL),
(9,'RXMaker','<p>Application d\'imagerie médicale</p>',NULL,NULL,3,3,3,3,NULL,'Carole','Sylvie','Internal',NULL,'Software','Internal','10','BIG Elec',2,3,'//documentation/RX',NULL,120,120,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:54:46','2025-06-12 13:54:46',NULL),
(10,'PharamaMag','<p>Gestion de la pharmacie</p>',NULL,NULL,3,3,3,3,NULL,'Anne','Anne','Logiciel',NULL,'Fat Client','Internal','30','PharaMaker',2,3,NULL,NULL,120,120,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 13:57:51','2025-06-12 13:57:51',NULL),
(11,'SalaryPay','<p>Application de gestion de la paye</p>',NULL,NULL,3,3,2,3,NULL,'Véronique','Véronique','Internal dev',NULL,'Web','Internal','10','OPENHOSP',17,5,'//documentation/SalaryPay',NULL,2880,240,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 15:28:22','2025-06-12 17:08:10',NULL),
(12,'Jobs','<p>Application de gestion des recrutements</p>',NULL,NULL,3,3,3,3,NULL,'Véronique','Véronique','Logiciel',NULL,'Web','Internal','10','OPENHOSP',2,5,NULL,NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 17:03:00','2025-06-12 17:06:35',NULL),
(13,'SyncAD','<p>Synchronisation de l\'active directory</p>',NULL,NULL,3,3,3,3,NULL,'Marc','Julien','Internal dev',NULL,'Job','Internal','5','OPENHOSP',2,6,'//documentation/jobs',NULL,1440,1440,'2025-01-01',NULL,'',NULL,NULL,'2025-06-12 17:33:13','2025-06-12 17:33:13',NULL),
(14,'LibreOffice','<p>Text</p>',NULL,NULL,1,1,1,1,NULL,'Carole','Marc',NULL,NULL,NULL,NULL,'> 100','Apache Fundation',2,1,NULL,NULL,1440,1440,NULL,NULL,'',NULL,NULL,'2025-06-14 05:50:17','2025-06-14 05:50:17',NULL),
(15,'LibreCalc','<p>Feuille de calcul</p>',NULL,NULL,1,1,1,1,NULL,'Carole','Marc',NULL,NULL,NULL,NULL,'> 100','Apache Fundation',2,1,NULL,NULL,1440,1440,NULL,NULL,'',NULL,NULL,'2025-06-14 05:51:20','2025-06-14 05:51:20',NULL);
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `macro_processuses`
--

LOCK TABLES `macro_processuses` WRITE;
/*!40000 ALTER TABLE `macro_processuses` DISABLE KEYS */;
INSERT INTO `macro_processuses` (`id`, `name`, `description`, `io_elements`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `owner`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Macro Processus 1','<p>Description du macro-processus de test<br>Test uniquement</p>','<ul><li>donnée 1</li><li>donnée 2</li><li>donnée 3</li></ul>',2,NULL,NULL,NULL,NULL,'propriétaire de test','2020-06-10 07:02:16','2020-06-22 06:07:55','2020-06-22 06:07:55'),
(2,'Maro-processus 2','<p>Description du macro-processus</p>',NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-06-13 01:03:42','2020-06-22 06:07:55','2020-06-22 06:07:55'),
(3,'Soins',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-08-21 08:32:46','2020-08-21 08:44:59','2020-08-21 08:44:59'),
(4,'Ressources Humaines','<p>Ressources humaines</p>',NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-08-21 08:34:19','2020-08-21 08:41:36','2020-08-21 08:41:36'),
(5,'Faire fonctionner l’hôpital','<p>Processus de support au soin et à la gestion : ensemble des processus qui contribuent au bon déroulement des autres processus, en leur fournissant les ressources nécessaires, aussi bien matérielles qu’immatérielles.</p>','<p>Entrant :<br>- besoins en ressources<br>Sortant :<br>- attribution de ressources<br>- reporting sur qualité des soins</p>',3,3,3,3,NULL,'Directeur administratif et financier','2020-08-21 08:38:01','2025-06-14 19:00:27',NULL),
(6,'Diriger l\'hôpital','<p>Processus qui retranscrivent la stratégie, les objectifs et permettent de piloter la démarche qualité tout en assurant son amélioration continue.</p>','<p>Entrant :&nbsp;</p><ul><li>information sur le fonctionnement des processus</li></ul><p>Sortant :</p><ul><li>rapports</li><li>tableaux de bord</li></ul>',3,3,3,2,NULL,'Directeur administratif et financier','2020-08-21 08:43:31','2025-06-14 19:00:10',NULL),
(7,'Traiter le patient','<p>Processus de prise en charge des patients en hospitalisation, en chirurgie, en ambulatoire et aux urgences</p>','<p>Entrant :</p><ul><li>Nom du patient</li><li>Numéro de sécurité sociale</li><li>Adresse du patient</li></ul><p>Sortant :</p><ul><li>Diagnostic</li><li>Prescription</li></ul>',3,3,3,3,NULL,'Directeur médical','2020-08-21 08:44:47','2025-06-14 19:00:15',NULL),
(8,'Soigner le patient','<p>Ensemble des processus de support clinique : Hygiène hospitalière, laboratoire, circuit du médicament et stérilisation</p>','<p>Entrée:<br>- ordonance<br>- besoins médicaux<br>Sortie:<br>- soin prodigué<br>- médication<br>- Support aux soins</p>',3,3,3,3,NULL,'Directeur médical','2020-09-07 07:22:46','2025-06-14 19:00:21',NULL),
(9,'Pilotage de l\'hôpital',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,'2020-09-07 07:28:46','2020-09-07 08:05:55','2020-09-07 08:05:55');
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
INSERT INTO `networks` (`id`, `name`, `description`, `protocol_type`, `responsible`, `responsible_sec`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OPENHOSP-INT','<p>Réseau interne de l\'hôpital</p>','TCP/IP','Paul','Jean',3,3,3,3,NULL,'2025-06-12 11:41:43','2025-06-12 11:42:02',NULL);
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
INSERT INTO `operations` (`id`, `name`, `description`, `process_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Operation 1','<p>Description de l\'opération</p>',NULL,'2020-06-13 00:02:42','2020-06-22 06:12:11','2020-06-22 06:12:11'),
(2,'Operation 2','<p>Description de l\'opération</p>',NULL,'2020-06-13 00:02:58','2020-06-22 06:12:11','2020-06-22 06:12:11'),
(3,'Opération 3','<p>Desciption de l\'opération</p>',NULL,'2020-06-13 00:03:11','2020-06-22 06:12:11','2020-06-22 06:12:11'),
(4,'Operation de test','<p>Description test</p>',NULL,'2020-07-16 06:53:24','2020-07-24 09:42:13','2020-07-24 09:42:13'),
(5,'Helpdesk','<p>Support informatique aux utilisateurs</p>',NULL,'2020-08-13 05:44:38','2020-08-13 05:48:19','2020-08-13 05:48:19'),
(6,'Inventaire des assets','<p>Maintient de l\'inventaire informatique</p>',NULL,'2020-08-13 06:35:04','2020-08-13 06:37:29',NULL),
(7,'Revue de l\'inventaire','<p>Revue de l\'inventaire des assets informatique</p>',NULL,'2020-08-13 06:36:28','2020-08-13 06:36:28',NULL),
(8,'Encodage incidents et demandes','<p>Capturer les demandes de résolution d\'incident et les demandes de services</p><p>Identification (documentation)</p><p>Catégorisation (routage vers le groupe frontline correscpondant)</p><p>Priorisation (gestion de l\'urgence de l\'incident ou de la requête)</p>',NULL,'2020-09-16 12:19:26','2020-09-16 12:19:26',NULL);
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
(2,'BigCluster01','HAL',NULL,'<p>Big Cluster Master&nbsp;</p>',NULL,NULL,NULL,'Nestor',NULL,1,12,1,NULL,'10.30.4.5','48','512','1024','304','OS34','2025-01-01 00:00:00',NULL,NULL,NULL,NULL,'2025-06-11 12:57:47','2025-06-18 08:33:37',NULL),
(3,'DataStore','DB',NULL,'<p>The database server</p>',NULL,NULL,NULL,'Paul',NULL,1,12,2,NULL,'10.50.3.1','4','64','3045','2025','DB23',NULL,NULL,NULL,NULL,NULL,'2025-06-11 12:58:58','2025-06-11 12:58:58',NULL),
(4,'Backup','Storage',NULL,'<p>The backup server</p>',NULL,NULL,NULL,'John',NULL,1,12,2,NULL,'10.10.34.3','4','64','5673','2132','OS34','2025-01-01 00:00:00',NULL,NULL,NULL,NULL,'2025-06-11 13:00:19','2025-06-14 18:27:34',NULL),
(5,'BigCluster02','HAL',NULL,'<p>Big Cluster Slave</p>',NULL,NULL,NULL,NULL,NULL,1,12,1,NULL,NULL,'48','512','1024','394','OS34','2025-01-01 00:00:00',NULL,NULL,NULL,NULL,'2025-06-12 17:54:40','2025-06-18 08:33:37',NULL);
/*!40000 ALTER TABLE `physical_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `physical_switches`
--

LOCK TABLES `physical_switches` WRITE;
/*!40000 ALTER TABLE `physical_switches` DISABLE KEYS */;
INSERT INTO `physical_switches` (`id`, `name`, `type`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `bay_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'SW01','Nortel','<p>Switch 1er étage</p>',NULL,NULL,NULL,1,1,NULL,'2025-06-14 05:56:03','2025-06-14 05:58:42',NULL),
(2,'SW02','Nortel','<p>Switch 2ème étage</p>',NULL,NULL,NULL,1,6,NULL,'2025-06-14 05:56:19','2025-06-14 05:58:30',NULL),
(3,'SW03','Nortel','<p>Switch 3ème étage</p>',NULL,NULL,NULL,1,15,NULL,'2025-06-14 05:57:18','2025-06-14 05:57:27',NULL),
(4,'SW04','Nortel','<p>Switch 4ème étage</p>',NULL,NULL,NULL,1,11,NULL,'2025-06-14 05:58:16','2025-06-14 05:58:16',NULL);
/*!40000 ALTER TABLE `physical_switches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `processes`
--

LOCK TABLES `processes` WRITE;
/*!40000 ALTER TABLE `processes` DISABLE KEYS */;
INSERT INTO `processes` (`id`, `name`, `icon_id`, `description`, `owner`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `in_out`, `macroprocess_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Processus 1',NULL,'<p>Description du processus 1</p>','OpenHosp - Facturation',3,3,3,3,NULL,'<ul><li>pommes</li><li>poires</li><li>cerises</li></ul>',NULL,'2020-06-17 14:36:24','2020-06-22 06:12:00','2020-06-22 06:12:00'),
(2,'Processus 2',NULL,'<p>Description du processus 2</p>','OpenHosp - Admission',3,3,3,3,NULL,NULL,NULL,'2020-06-17 14:36:58','2020-06-22 06:12:00','2020-06-22 06:12:00'),
(3,'Acceuil des visiteurs',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-06-22 13:49:28','2020-06-22 13:49:46','2020-06-22 13:49:46'),
(4,'Resources humaines',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-06-22 13:50:04','2020-08-21 08:34:48','2020-08-21 08:34:48'),
(5,'Urgences',NULL,'<p>Accueil et prise en charge des patients adressés aux urgences / qui se présentent aux urgences</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrée :&nbsp;<br>- patients</p><p>Sortie :<br>- soins prodigués</p>',7,'2020-06-22 13:50:19','2025-06-14 19:00:15',NULL),
(6,'Cellule Communication',NULL,'<p>Gestion de la communication interne et externe</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :<br>- besoin de communication<br>- contexte de l\'organisation<br>Sortie :<br>- communications<br>- reporting</p>',5,'2020-06-22 14:43:24','2025-06-14 19:00:27',NULL),
(7,'Organisation et performance',NULL,'<p>Cellule Qualité/Relations avec les patients</p><p>Contient également la le controlling</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrant :<br>- &nbsp;Données sur le fonctionnement de l\'hopital</p><p>Sortant :<br>- Statistiques<br>- Analyses de risques<br>- Rapports</p>',6,'2020-06-22 14:50:06','2025-06-14 19:00:10',NULL),
(8,'Soins',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-06-22 14:50:23','2020-08-21 08:46:12','2020-08-21 08:46:12'),
(9,'Informatique',NULL,'<p>Service informatique du OpenHosp</p>','CIO du OpenHosp',3,3,3,3,NULL,'<p>Entrée :&nbsp;<br>- besoins d\'utilisation des technologies de l\'information<br>Sortie :&nbsp;<br>- besoins couverts</p>',5,'2020-06-24 06:20:23','2025-06-14 19:00:27',NULL),
(10,'Sécurité',NULL,'<p>Sécurité physique et envirnmenttal de l\'hopital</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :&nbsp;<br>- besoins de sécurité physique et environmentale<br>Sortie :&nbsp;<br>- sécurité physique et environmentale appliquée</p>',5,'2020-07-31 11:51:06','2025-06-14 19:00:27',NULL),
(11,'Finances et Accueil Patients',NULL,'<p>Est responsable de :</p><ul><li>Comptabilité générale</li><li>Comptabilité fournisseurs</li><li>Facturation</li><li>Front office</li><li>Back office &amp; Téléphonie</li></ul>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>IN :<br>- budgets<br>- facture<br>- offre validées<br>Out :<br>- reporting comptable</p>',5,'2020-07-31 11:53:40','2025-06-14 19:00:27',NULL),
(12,'Médico-technique',NULL,'<p>Gestion du matériel médical et de soins</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :<br>- besoins de matériel médical<br>Sortie :&nbsp;<br>- matériel géré</p>',5,'2020-07-31 12:05:45','2025-06-14 19:00:27',NULL),
(13,'Hôtellerie',NULL,'<p>Est responsable de &nbsp;:</p><ul><li>Restauration</li><li>Nettoyage</li></ul>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :<br>- besoins de services d\'hotelerie<br>Sortie :&nbsp;<br>- besoins d\'hotelerie couverts</p>',5,'2020-07-31 12:06:49','2025-06-14 19:00:27',NULL),
(14,'Pilotage de l\'hôpital',NULL,'<p><strong>Le processus</strong> de « <strong>pilotage</strong> » contribuent à la détermination de la stratégie d\'entreprise et au déploiement des objectifs dans l\'organisation.&nbsp;<br>Sous la responsabilité de la direction, ils permettent d\'orienter et d\'assurer la cohérence des <strong>processus</strong> de réalisation et de support.</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>IN :<br>- données de l\'hopital (Datawarehouse)<br>OUT :<br>- Rapports de pilotage</p>',6,'2020-08-03 07:32:25','2025-06-14 19:00:10',NULL),
(15,'Recrutement',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:35:10','2020-08-21 08:41:20','2020-08-21 08:41:20'),
(16,'Admission',NULL,'<p>Admission et prise de rendez-vous</p>',NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:47:46','2020-09-07 07:53:43','2020-09-07 07:53:43'),
(17,'Gestion des lits',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:48:30','2020-09-07 08:02:12','2020-09-07 08:02:12'),
(18,'Hospitalisation',NULL,'<p>Prise en charge des patients en hospitalisation</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrant :<br>- patients</p><p>Sortie<br>- soins prodigués</p>',7,'2020-08-21 08:51:29','2025-06-14 19:00:15',NULL),
(19,'Ambulatoire',NULL,'<p>Services de prise en charge des patients pour examens, thérapies, consultations, soins hors hospitalisation</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrant :<br>- patients<br>- pathologies<br>Sortie :<br>Soins prodigués</p>',7,'2020-08-21 08:51:40','2025-06-14 19:00:15',NULL),
(20,'Sécurité informatique',NULL,'<p>Sécurité de l\'informatique</p>','CIO du OpenHosp',3,3,3,3,NULL,NULL,NULL,'2020-08-21 08:54:25','2020-08-24 06:15:57','2020-08-24 06:15:57'),
(21,'Laboratoire',NULL,'<p>Gestion des prescriptions médicales, analyses et communications des résultats</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrant :&nbsp;<br>- prescriptions<br>Sortie :<br>- résultats d\'analyse</p>',8,'2020-08-21 08:58:05','2025-06-14 19:00:21',NULL),
(22,'Formation e-learning',NULL,NULL,NULL,3,3,3,3,NULL,NULL,5,'2020-08-21 09:01:30','2021-02-08 06:20:07','2021-02-08 06:20:07'),
(23,'Pharmacie',NULL,NULL,NULL,3,3,3,3,NULL,NULL,NULL,'2020-08-21 09:15:08','2020-09-07 08:08:45','2020-09-07 08:08:45'),
(24,'Information médicale',NULL,'<p>Département d\'information médicale</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrée :&nbsp;<br>- codification médicale<br>- actes médicaux prestés par le OpenHosp<br>Sortie :&nbsp;<br>- actes codifiés</p>',5,'2020-08-21 09:16:07','2025-06-14 19:00:27',NULL),
(25,'Cellule Juridique',NULL,NULL,'Direction Générale',3,3,3,3,NULL,NULL,NULL,'2020-08-21 09:17:32','2020-09-07 08:01:46','2020-09-07 08:01:46'),
(26,'Infrastructures et logistique',NULL,'<p>Est responsable de :</p><ul><li>Bâtiment</li><li>Maintenance préventive</li><li>Construction</li><li>Sécurité &amp; Environnement</li></ul>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :<br>- besoins d\'infrastructure et logistiques<br>Sortie :<br>- besoins couverts</p>',5,'2020-08-21 10:22:58','2025-06-14 19:00:27',NULL),
(27,'Logistique et restauration',NULL,'<p>Est responsable de :</p><ul><li>Transport Logistique et Patients</li><li>Achats et Magasin</li></ul>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :&nbsp;<br>- besoins de transport et restauration<br>Sortie :<br>- besoisn couverts</p>',5,'2020-08-21 10:23:50','2025-06-14 19:00:27',NULL),
(28,'Ressources Humaines',NULL,'<p>Gestion des ressources humaines du OpenHosp</p>','Direction des Ressources Humaines',3,3,3,3,NULL,'<p>IN :<br>- demande de recrutement<br>- ...<br>OUT :&nbsp;<br>- Gestion du temps<br>- personnel en poste<br>- fiches de salaire</p>',5,'2020-08-21 10:30:24','2025-06-14 19:00:27',NULL),
(29,'Protection des données',NULL,'<p>S’assurer du bon respect du Règlement Général pour la Protection des Données (RGPD) au OpenHosp.</p>','DPO',3,3,3,3,NULL,'<p>In:<br>- Contexte du OpenHosp</p><p>Out:<br>- recommandations de conformité</p><p>&nbsp;</p>',5,'2020-08-24 06:16:37','2025-06-14 19:00:27',NULL),
(30,'Stérilisation',NULL,'<p>Service de stérilisation</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrée :<br>- demandes de stérilisation<br>Sortié :<br>- matériel stérilisé</p>',8,'2020-09-07 07:23:04','2025-06-14 19:00:21',NULL),
(31,'Hygiène hospitalière',NULL,'<p>Hygiène hospitalière</p>','Directeur Médical',2,2,2,3,NULL,'<p>Entrée :&nbsp;<br>- besoins d\'hygiènes<br>Sortie :<br>- règles d\'hygiène appliquées</p>',8,'2020-09-07 07:23:37','2025-06-14 19:00:21',NULL),
(32,'Circuit des médicaments',NULL,'<p>Gestion des médicaments</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrant :&nbsp;<br>- ordinances<br>Sortie :<br>- médicaments<br>- reporting</p>',8,'2020-09-07 07:23:48','2025-06-14 19:00:21',NULL),
(33,'Construction',NULL,'<p>Département de construction du OpenHosp</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>IN :<br>- Plan<br>- Changement<br>Out :<br>- Répalisation de travaux</p>',5,'2020-09-07 08:04:54','2025-06-14 19:00:27',NULL),
(34,'Infrastructure',NULL,'<p>Gestion de l\'infrastructure technique du OpenHosp</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :<br>- besoins techniques<br>- normes et standards à respecter<br>Sortie :<br>- installations techniques fonctionnelles</p>',5,'2020-09-07 08:05:20','2025-06-14 19:00:27',NULL),
(35,'Finances',NULL,NULL,NULL,3,3,3,3,NULL,NULL,5,'2020-09-07 08:05:32','2020-09-07 08:11:17','2020-09-07 08:11:17'),
(36,'Achats',NULL,'<p>Service des achats</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>In:<br>- demande d\'achat</p><p>Out:<br>- factures d\'achat</p>',5,'2020-09-07 08:05:45','2025-06-14 19:00:27',NULL),
(37,'Nettoyage',NULL,'<p>Service de nettoyage du OpenHosp</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>Entrée :<br>- besoins de nettoyage<br>Sortie :&nbsp;<br>- zones traitées</p>',5,'2020-09-07 08:10:26','2025-06-14 19:00:27',NULL),
(38,'Déchets',NULL,'<p>Gestion des déchets du centre hospitalier</p>','Directeur Médical',3,3,3,3,NULL,'<p>Entrée :&nbsp;<br>- déchets<br>Sortie :<br>- déchets recyclés/traités</p>',5,'2020-09-07 08:30:29','2025-06-14 19:00:27',NULL),
(39,'Gestion de l\'information',NULL,'<p>Gestion de l\'information médicale</p>','Direction Administrative et Financière',3,3,3,3,NULL,'<p>IN :<br>- normes internationales<br>- actes médicaux réalisés<br>Out :&nbsp;<br>- codification des actes médicaux</p>',6,'2020-09-07 08:53:46','2025-06-14 19:00:10',NULL);
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
INSERT INTO `relations` (`id`, `importance`, `name`, `type`, `description`, `source_id`, `destination_id`, `attributes`, `reference`, `responsible`, `order_number`, `active`, `start_date`, `end_date`, `comments`, `security_need_c`, `security_need_i`, `security_need_a`, `security_need_t`, `security_need_auth`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,2,'Licences Medi+','Fournisseur','<p>Foruniture des licens Medi+</p>',9,2,'Validé','1234567','Paul','1234567',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:30:24','2025-06-14 18:30:45',NULL),
(2,3,'Suport RX','Fournisseur','<p>Support imagerie médical - 24x7</p>',10,17,'Validé','1235948453','Henri','432043284382',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:32:03','2025-06-14 18:32:03',NULL),
(3,2,'Mission conseil LTR','Conseil','<p>Conseil IT</p>',11,2,'Validé','43943284320','Pierre','32443929432',1,'2025-01-01','2025-04-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:33:26','2025-06-14 18:33:26',NULL),
(4,3,'Support PharmaMan','Fournisseur','<p>Support Logiciel Pharmaman - 24x7</p>',1,4,'Validé','43943294329','Sophie','943294329432',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:35:36','2025-06-14 18:35:36',NULL),
(5,3,'Logiel Comptable','Fournisseur','<p>Licence logiciel comptable</p>',12,3,'Validé','42343243232','Henri','443224432',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:36:59','2025-06-14 18:36:59',NULL),
(6,3,'Suport Tech24','Fournisseur','<p>Support logiciel Dossier Médical 24x7</p>',15,3,'Validé','43294329432','Pierre','424329439',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:38:20','2025-06-14 18:38:20',NULL),
(7,3,'Maintenance Mainframe','Maintenance','<p>Maintenance 24x7 du Mainrame</p>',13,2,'Validé','439432943','Paul','1044384833',1,'2025-01-01','2025-12-31',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:39:56','2025-06-14 18:39:56',NULL),
(8,3,'Analyse aboratoire','Partenariat','<p>Partrnariat Analyse laboratoire</p>',14,4,'Validé','5943548354','Julie','60545965945',1,'2025-04-01','2026-04-01',NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:44:20','2025-06-14 18:44:20',NULL),
(9,3,'Filitation','Membre','<p>Membre de l\'Open Hospital Federation</p>',3,16,'','5439555453','Nathalie','06544569456',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 18:45:24','2025-06-14 18:45:36',NULL);
/*!40000 ALTER TABLE `relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `routers`
--

LOCK TABLES `routers` WRITE;
/*!40000 ALTER TABLE `routers` DISABLE KEYS */;
INSERT INTO `routers` (`id`, `name`, `type`, `description`, `rules`, `ip_addresses`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'LR01',NULL,'<p>Roueur principal de l\'Open Hosital</p>',NULL,'10.10.5.25','2025-06-12 11:45:47','2025-10-19 09:42:17',NULL);
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
INSERT INTO `security_controls` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Contrôle d’accès basé sur les rôles (RBAC)','<p>Seuls les professionnels habilités peuvent accéder aux données selon leur fonction (ex. : un médecin voit ses patients, mais pas ceux des autres).</p><p>Implémenté via Active Directory, SSO, badge, etc.</p>','2025-06-14 11:22:50','2025-06-14 11:26:02',NULL),
(2,'Traçabilité des accès et journalisation','<p>Tous les accès aux dossiers médicaux et systèmes critiques sont enregistrés.</p><p>Les journaux sont conservés, analysés et régulièrement audités pour détecter les abus ou incidents.</p>','2025-06-14 11:23:12','2025-06-14 11:26:29',NULL),
(3,'Authentification forte','<p>Utilisation de mots de passe complexes, d’une authentification à deux facteurs (2FA) pour les accès sensibles (ex. : DPI à distance, accès administrateur).</p>','2025-06-14 11:23:27','2025-06-14 11:23:27',NULL),
(4,'Verrouillage automatique des sessions','<p>Les postes de travail se verrouillent automatiquement après quelques minutes d’inactivité, afin de protéger les écrans visibles dans les zones de soin.</p>','2025-06-14 11:23:45','2025-06-14 11:23:45',NULL),
(5,'Isolation des environnements de test','<p>Les environnements de développement ou de test n’utilisent pas de vraies données patient, ou alors celles-ci sont anonymisées/pseudonymisées.</p>','2025-06-14 11:24:00','2025-06-14 11:25:45',NULL),
(6,'Sauvegardes régulières et testées','<p>Sauvegardes cryptées, quotidiennes, déportées, avec tests réguliers de restauration.</p><p>Objectif : résilience face aux sinistres (incendie, ransomware…).</p>','2025-06-14 11:24:15','2025-06-14 11:24:15',NULL),
(7,'Gestion des habilitations','<p>Habilitations attribuées selon la fiche de poste, révisées à chaque départ ou changement de fonction.</p><p>Suivi et traçabilité des demandes d’accès.</p>','2025-06-14 11:24:34','2025-06-14 11:26:15',NULL),
(8,'Sensibilisation et formation du personnel','<p>Formation annuelle à la sécurité des SI et au RGPD pour tous les agents hospitaliers.</p><p>Affichage de consignes claires sur la confidentialité dans les locaux.</p>','2025-06-14 11:24:53','2025-06-14 11:26:22',NULL),
(9,'Plan de continuité et de reprise d’activité (PCA/PRA)','<p>Procédures écrites et testées pour continuer les soins en cas de panne informatique (ex. : formulaires papier, téléphonie de secours, accès au DPI en lecture seule…).</p>','2025-06-14 11:25:08','2025-06-14 11:25:08',NULL),
(10,'Chiffrement des postes et des échanges','<p>Chiffrement des disques durs des postes mobiles (ordinateurs portables, tablettes).</p><p>Utilisation du chiffrement TLS pour les échanges inter-applicatifs et de messagerie sécurisée de santé (MSSanté).</p>','2025-06-14 11:25:26','2025-06-14 11:25:56',NULL);
/*!40000 ALTER TABLE `security_controls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `security_devices`
--

LOCK TABLES `security_devices` WRITE;
/*!40000 ALTER TABLE `security_devices` DISABLE KEYS */;
INSERT INTO `security_devices` (`id`, `name`, `description`, `vendor`, `product`, `version`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'IDS-Rock','<p>Équipement de détection d\'intrusion réseau</p>',NULL,NULL,NULL,'2025-06-12 11:46:25','2025-06-12 11:46:25',NULL);
/*!40000 ALTER TABLE `security_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
INSERT INTO `sites` (`id`, `name`, `icon_id`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'The Open Hospital',NULL,'<p>Adresse:<br>1, rue de la Santé<br>1024 Bonsite</p>','2025-06-10 08:31:32','2025-06-11 10:23:30',NULL);
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
INSERT INTO `subnetworks` (`id`, `name`, `description`, `address`, `ip_allocation_type`, `responsible_exp`, `dmz`, `wifi`, `connected_subnets_id`, `gateway_id`, `zone`, `vlan_id`, `network_id`, `default_gateway`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OPENHOSP-DMZ','<p>Zone démilitarisée de l\'hôpital</p>','10.10.25.0/24','Static','Paul','Oui','Non',NULL,NULL,'ZONE2',4,1,'10.10.25.1','2025-06-12 11:43:52','2025-06-14 09:25:24',NULL),
(2,'OPENHOSP-LAB','<p>Zone réseau du laboratoire</p>','10.10.8.0/24','Static','Paul','Non','Non',NULL,NULL,'Non',1,1,'10.10.8.1','2025-06-12 11:44:27','2025-06-14 09:24:53',NULL),
(3,'OPENHOSP-LAN','<p>Réseau PC</p>','10.10.2.0/24','Static','Paul','Oui','Non',NULL,NULL,'ZONE1',3,1,'10.10.2.1','2025-06-14 08:57:49','2025-06-14 09:25:10',NULL),
(4,'OPENHOSP-ADIN','<p>Administration sub network</p>','10.10.5.0/24','Dynamic','Paul','Oui','Non',NULL,NULL,'DMZ',2,1,'10.10.5.1','2025-06-14 09:05:42','2025-06-14 09:23:39',NULL);
/*!40000 ALTER TABLE `subnetworks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Tâche 2','Descriptionde la tâche 2','2020-06-13 00:04:07','2020-06-22 06:12:15','2020-06-22 06:12:15'),
(2,'Tache 1','Description de la tâche 1','2020-06-13 00:04:21','2020-06-22 06:12:15','2020-06-22 06:12:15'),
(3,'Tâche 3','Description de la tâche 3','2020-06-13 00:04:41','2020-06-22 06:12:15','2020-06-22 06:12:15');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `vlans`
--

LOCK TABLES `vlans` WRITE;
/*!40000 ALTER TABLE `vlans` DISABLE KEYS */;
INSERT INTO `vlans` (`id`, `name`, `description`, `vlan_id`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'VLAN-LAB','<p>VLAN Laboratoire</p>',1,'2025-06-12 11:54:55','2025-06-12 11:54:55',NULL),
(2,'VLAN-ADMIN','<p>Administration VLAN</p>',10,'2025-06-14 09:19:18','2025-06-14 09:19:18',NULL),
(3,'VLAN-PC','<p>Vlan des PC</p>',15,'2025-06-14 09:19:53','2025-06-14 09:19:53',NULL),
(4,'VLAN-SRV','<p>VLAN Serveurs</p>',17,'2025-06-14 09:26:07','2025-06-14 09:26:07',NULL);
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
INSERT INTO `wifi_terminals` (`id`, `name`, `type`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `address_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'WIFI-DIR','NOTEL','<p>Borne Wifi Direction</p>',NULL,NULL,NULL,1,13,'10.10.5.11','2025-06-12 12:31:17','2025-06-14 09:15:17',NULL),
(2,'WIFI-LABO','NOTEL','<p>Borne Wifi laboratoire</p>',NULL,NULL,NULL,1,6,'10.10.5.14','2025-06-12 12:32:22','2025-06-14 09:15:39',NULL),
(3,'WIFI-GUETS','NOTEL','<p>Borde Wifi patients</p>',NULL,NULL,NULL,1,1,'10.10.5.13','2025-06-12 12:33:16','2025-06-14 09:15:27',NULL);
/*!40000 ALTER TABLE `wifi_terminals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `workstations`
--

LOCK TABLES `workstations` WRITE;
/*!40000 ALTER TABLE `workstations` DISABLE KEYS */;
INSERT INTO `workstations` (`id`, `entity_id`, `name`, `description`, `vendor`, `product`, `version`, `site_id`, `building_id`, `physical_switch_id`, `type`, `icon_id`, `operating_system`, `address_ip`, `cpu`, `memory`, `disk`, `user_id`, `other_user`, `status`, `manufacturer`, `model`, `serial_number`, `last_inventory_date`, `warranty_end_date`, `domain_id`, `warranty`, `warranty_start_date`, `warranty_period`, `agent_version`, `update_source`, `network_id`, `network_port_type`, `mac_address`, `purchase_date`, `fin_value`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,NULL,'PC034','<p>PC acceuil</p>',NULL,NULL,NULL,1,1,NULL,'Black',NULL,'Windows','10.10.2.35','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:35:44','2025-06-14 08:59:15',NULL),
(2,NULL,'PC035','<p>PC Consultation</p>',NULL,NULL,NULL,1,3,NULL,'Black',NULL,'Windows','10.10.2.36','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:36:26','2025-06-14 08:59:42',NULL),
(3,NULL,'PC037','<p>PC Consultation</p>',NULL,NULL,NULL,1,4,NULL,'Black',NULL,'Windows','10.10.2.37','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:37:02','2025-06-14 09:00:03',NULL),
(4,NULL,'PC038','<p>PC Urgences</p>',NULL,NULL,NULL,1,5,NULL,'Black',NULL,'Windows','10.10.2.38','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:37:37','2025-06-14 09:00:16',NULL),
(5,NULL,'PC040','<p>PC Labo</p>',NULL,NULL,NULL,1,6,NULL,'Black',NULL,'Windows','10.10.8.5','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:38:30','2025-06-14 09:00:33',NULL),
(6,NULL,'PC041','<p>PC Labo</p>',NULL,NULL,NULL,1,6,NULL,'Black',NULL,'Windows','10.10.8.6','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:39:24','2025-06-14 09:00:57',NULL),
(7,NULL,'PC043','<p>PC Pharmacie</p>',NULL,NULL,NULL,1,7,NULL,'Black',NULL,'Windows','10.10.8.7','i5','4',120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-06-14 05:40:04','2025-06-14 09:04:09',NULL),
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
INSERT INTO `zone_admins` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'OpenHospital','<p>Zone principale d\'administration de l\'Open Hospital</p>','2025-06-11 10:21:37','2025-06-12 11:22:07',NULL);
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
