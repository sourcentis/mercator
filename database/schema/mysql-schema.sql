/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `recovery_time_objective` int(11) DEFAULT NULL,
  `maximum_tolerable_downtime` int(11) DEFAULT NULL,
  `recovery_point_objective` int(11) DEFAULT NULL,
  `maximum_tolerable_data_loss` int(11) DEFAULT NULL,
  `drp` text DEFAULT NULL,
  `drp_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_perimeter_id_index` (`perimeter_id`),
  KEY `activities_name_index` (`name`),
  KEY `activities_created_by_foreign` (`created_by`),
  KEY `activities_updated_by_foreign` (`updated_by`),
  KEY `activities_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activities_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activities_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `activities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_application` (
  `application_id` int(10) unsigned NOT NULL,
  `activity_id` int(10) unsigned NOT NULL,
  KEY `application_id_fk_0394834858` (`application_id`),
  KEY `process_id_fk_394823838` (`activity_id`),
  CONSTRAINT `activity_application_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `activity_m_application_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_document` (
  `activity_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `activity_id_fk_1472714` (`activity_id`),
  KEY `operation_id_fk_1472714` (`document_id`),
  CONSTRAINT `activity_id_fk_1472784` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `operation_id_fk_1472794` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_impact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_impact` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(10) unsigned NOT NULL,
  `impact_type` varchar(255) NOT NULL,
  `severity` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_impact_activity_id_foreign` (`activity_id`),
  CONSTRAINT `activity_impact_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_operation` (
  `activity_id` int(10) unsigned NOT NULL,
  `operation_id` int(10) unsigned NOT NULL,
  KEY `activity_id_fk_1472704` (`activity_id`),
  KEY `operation_id_fk_1472704` (`operation_id`),
  CONSTRAINT `activity_id_fk_1472704` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `operation_id_fk_1472704` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_process` (
  `process_id` int(10) unsigned NOT NULL,
  `activity_id` int(10) unsigned NOT NULL,
  KEY `process_id_fk_1627616` (`process_id`),
  KEY `activity_id_fk_1627616` (`activity_id`),
  CONSTRAINT `activity_id_fk_1627616` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `process_id_fk_1627616` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `actor_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `actor_operation` (
  `operation_id` int(10) unsigned NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  KEY `operation_id_fk_1472680` (`operation_id`),
  KEY `actor_id_fk_1472680` (`actor_id`),
  CONSTRAINT `actor_id_fk_1472680` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `operation_id_fk_1472680` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `actors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `actors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `nature` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `actors_perimeter_id_index` (`perimeter_id`),
  KEY `actors_name_index` (`name`),
  KEY `actors_created_by_foreign` (`created_by`),
  KEY `actors_updated_by_foreign` (`updated_by`),
  KEY `actors_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `actors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `actors_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `actors_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `actors_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_user_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_user_application` (
  `admin_user_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`admin_user_id`,`application_id`),
  KEY `admin_user_m_application_m_application_id_foreign` (`application_id`),
  CONSTRAINT `admin_user_application_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `admin_user_m_application_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_user_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_user_zone` (
  `admin_user_id` int(10) unsigned NOT NULL,
  `zone_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`admin_user_id`,`zone_id`),
  KEY `admin_user_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `admin_user_zone_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_user_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `domain_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_id_user_id_unique` (`domain_id`,`user_id`,`deleted_at`),
  KEY `domain_id_fk_69385935` (`domain_id`),
  KEY `document_id_fk_129487` (`icon_id`),
  KEY `admin_users_perimeter_id_index` (`perimeter_id`),
  KEY `admin_users_created_by_foreign` (`created_by`),
  KEY `admin_users_updated_by_foreign` (`updated_by`),
  KEY `admin_users_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `admin_users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admin_users_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admin_users_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `admin_users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_id_fk_129487` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `domain_id_fk_69385935` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `annuaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `annuaires` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `solution` varchar(255) DEFAULT NULL,
  `zone_admin_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `application_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zone_admin_fk_1482666` (`zone_admin_id`),
  KEY `annuaires_application_id_foreign` (`application_id`),
  KEY `annuaires_perimeter_id_index` (`perimeter_id`),
  KEY `annuaires_name_index` (`name`),
  KEY `annuaires_created_by_foreign` (`created_by`),
  KEY `annuaires_updated_by_foreign` (`updated_by`),
  KEY `annuaires_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `annuaires_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `annuaires_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `annuaires_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `annuaires_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `annuaires_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zone_admin_fk_1482666` FOREIGN KEY (`zone_admin_id`) REFERENCES `zone_admins` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_application_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_application_service` (
  `application_id` int(10) unsigned NOT NULL,
  `application_service_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_1482585` (`application_id`),
  KEY `application_service_id_fk_1482585` (`application_service_id`),
  CONSTRAINT `application_application_service_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `application_service_id_fk_1482585` FOREIGN KEY (`application_service_id`) REFERENCES `application_services` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_blocks_perimeter_id_index` (`perimeter_id`),
  KEY `application_blocks_name_index` (`name`),
  KEY `application_blocks_created_by_foreign` (`created_by`),
  KEY `application_blocks_updated_by_foreign` (`updated_by`),
  KEY `application_blocks_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `application_blocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_blocks_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_blocks_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `application_blocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_certificate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_certificate` (
  `certificate_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  KEY `certificate_id_fk_4584393` (`certificate_id`),
  KEY `m_application_id_fk_4584393s` (`application_id`),
  CONSTRAINT `application_certificate_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `certificate_m_application_certificate_id_foreign` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_container`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_container` (
  `container_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  KEY `container_id_fk_549854345` (`container_id`),
  KEY `m_application_id_fk_344234340` (`application_id`),
  CONSTRAINT `application_container_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `container_m_application_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_data_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_data_processing` (
  `data_processing_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  KEY `data_processing_id_fk_6948435` (`data_processing_id`),
  KEY `m_applications_id_fk_4384483` (`application_id`),
  CONSTRAINT `application_data_processing_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `data_processing_id_fk_49838437` FOREIGN KEY (`data_processing_id`) REFERENCES `data_processing` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_database`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_database` (
  `application_id` int(10) unsigned NOT NULL,
  `database_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_1482586` (`application_id`),
  KEY `database_id_fk_1482586` (`database_id`),
  CONSTRAINT `application_database_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `database_id_fk_1482586` FOREIGN KEY (`database_id`) REFERENCES `databases` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_document` (
  `application_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `application_id_fk_7812341` (`application_id`),
  KEY `document_id_fk_7812342` (`document_id`),
  CONSTRAINT `application_id_fk_7812341` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_7812342` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_entity` (
  `application_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_1488611` (`application_id`),
  KEY `entity_id_fk_1488611` (`entity_id`),
  CONSTRAINT `application_entity_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `entity_id_fk_1488611` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ext_refs` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  `message` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_application_events_user_id_foreign` (`user_id`),
  KEY `m_application_events_m_application_id_foreign` (`application_id`),
  CONSTRAINT `application_events_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `m_application_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_flow_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_flow_information` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `flux_id` int(10) unsigned NOT NULL,
  `information_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flux_information_flux_id_information_id_unique` (`flux_id`,`information_id`),
  KEY `application_flow_information_information_id_foreign` (`information_id`),
  CONSTRAINT `application_flow_information_flux_id_foreign` FOREIGN KEY (`flux_id`) REFERENCES `application_flows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_flow_information_information_id_foreign` FOREIGN KEY (`information_id`) REFERENCES `information` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_flows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_flows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `application_source_id` int(10) unsigned DEFAULT NULL,
  `service_source_id` int(10) unsigned DEFAULT NULL,
  `module_source_id` int(10) unsigned DEFAULT NULL,
  `database_source_id` int(10) unsigned DEFAULT NULL,
  `application_dest_id` int(10) unsigned DEFAULT NULL,
  `service_dest_id` int(10) unsigned DEFAULT NULL,
  `module_dest_id` int(10) unsigned DEFAULT NULL,
  `database_dest_id` int(10) unsigned DEFAULT NULL,
  `crypted` tinyint(1) DEFAULT NULL,
  `bidirectional` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_source_fk_1485545` (`application_source_id`),
  KEY `service_source_fk_1485546` (`service_source_id`),
  KEY `module_source_fk_1485547` (`module_source_id`),
  KEY `database_source_fk_1485548` (`database_source_id`),
  KEY `application_dest_fk_1485549` (`application_dest_id`),
  KEY `service_dest_fk_1485550` (`service_dest_id`),
  KEY `module_dest_fk_1485551` (`module_dest_id`),
  KEY `database_dest_fk_1485552` (`database_dest_id`),
  KEY `application_flows_perimeter_id_index` (`perimeter_id`),
  KEY `application_flows_name_index` (`name`),
  KEY `application_flows_created_by_foreign` (`created_by`),
  KEY `application_flows_updated_by_foreign` (`updated_by`),
  KEY `application_flows_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `application_dest_fk_1485549` FOREIGN KEY (`application_dest_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `application_flows_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_flows_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_flows_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `application_flows_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_source_fk_1485545` FOREIGN KEY (`application_source_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `database_dest_fk_1485552` FOREIGN KEY (`database_dest_id`) REFERENCES `databases` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `database_source_fk_1485548` FOREIGN KEY (`database_source_id`) REFERENCES `databases` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `module_dest_fk_1485551` FOREIGN KEY (`module_dest_id`) REFERENCES `application_modules` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `module_source_fk_1485547` FOREIGN KEY (`module_source_id`) REFERENCES `application_modules` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `service_dest_fk_1485550` FOREIGN KEY (`service_dest_id`) REFERENCES `application_services` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `service_source_fk_1485546` FOREIGN KEY (`service_source_id`) REFERENCES `application_services` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_logical_server` (
  `application_id` int(10) unsigned NOT NULL,
  `logical_server_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_1488616` (`application_id`),
  KEY `logical_server_id_fk_1488616` (`logical_server_id`),
  CONSTRAINT `application_logical_server_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `logical_server_id_fk_1488616` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_module_application_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_module_application_service` (
  `application_service_id` int(10) unsigned NOT NULL,
  `application_module_id` int(10) unsigned NOT NULL,
  KEY `application_service_id_fk_1492414` (`application_service_id`),
  KEY `application_module_id_fk_1492414` (`application_module_id`),
  CONSTRAINT `application_module_id_fk_1492414` FOREIGN KEY (`application_module_id`) REFERENCES `application_modules` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `application_service_id_fk_1492414` FOREIGN KEY (`application_service_id`) REFERENCES `application_services` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_module_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_module_entity` (
  `application_module_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`application_module_id`,`entity_id`),
  KEY `application_module_entity_entity_id_foreign` (`entity_id`),
  CONSTRAINT `application_module_entity_application_module_id_foreign` FOREIGN KEY (`application_module_id`) REFERENCES `application_modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_module_entity_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `comments` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_modules_perimeter_id_index` (`perimeter_id`),
  KEY `application_modules_name_index` (`name`),
  KEY `application_modules_created_by_foreign` (`created_by`),
  KEY `application_modules_updated_by_foreign` (`updated_by`),
  KEY `application_modules_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `application_modules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_modules_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_modules_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `application_modules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_peripheral`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_peripheral` (
  `application_id` int(10) unsigned NOT NULL,
  `peripheral_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_9878654` (`application_id`),
  KEY `peripheral_id_fk_6454564` (`peripheral_id`),
  CONSTRAINT `application_peripheral_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `peripheral_id_fk_6454564` FOREIGN KEY (`peripheral_id`) REFERENCES `peripherals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_physical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_physical_server` (
  `application_id` int(10) unsigned NOT NULL,
  `physical_server_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_5483543` (`application_id`),
  KEY `physical_server_id_fk_4543543` (`physical_server_id`),
  CONSTRAINT `application_physical_server_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `physical_server_id_fk_4543543` FOREIGN KEY (`physical_server_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_process` (
  `application_id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_1482573` (`application_id`),
  KEY `process_id_fk_1482573` (`process_id`),
  CONSTRAINT `application_process_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `process_id_fk_1482573` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_security_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_security_control` (
  `security_control_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  KEY `security_control_id_fk_5920381` (`security_control_id`),
  KEY `m_application_id_fk_5837573` (`application_id`),
  CONSTRAINT `application_security_control_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `security_control_id_fk_49294573` FOREIGN KEY (`security_control_id`) REFERENCES `security_controls` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_security_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_security_device` (
  `security_device_id` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  KEY `security_device_id_fk_304832731` (`security_device_id`),
  KEY `m_application_id_fk_41923483` (`application_id`),
  CONSTRAINT `application_security_device_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `security_device_id_fk_304832731` FOREIGN KEY (`security_device_id`) REFERENCES `security_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `comments` longtext DEFAULT NULL,
  `exposition` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_services_perimeter_id_index` (`perimeter_id`),
  KEY `application_services_name_index` (`name`),
  KEY `application_services_created_by_foreign` (`created_by`),
  KEY `application_services_updated_by_foreign` (`updated_by`),
  KEY `application_services_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `application_services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_services_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_services_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `application_services_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_workstation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_workstation` (
  `application_id` int(10) unsigned NOT NULL,
  `workstation_id` int(10) unsigned NOT NULL,
  KEY `m_application_id_fk_1486547` (`application_id`),
  KEY `workstation_id_fk_1486547` (`workstation_id`),
  CONSTRAINT `application_workstation_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  CONSTRAINT `workstation_id_fk_1486547` FOREIGN KEY (`workstation_id`) REFERENCES `workstations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `comments` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `functional_referent` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `technology` varchar(255) DEFAULT NULL,
  `external` varchar(255) DEFAULT NULL,
  `hosting` varchar(255) DEFAULT NULL,
  `urls` varchar(255) DEFAULT NULL,
  `users` varchar(255) DEFAULT NULL,
  `editor` varchar(255) DEFAULT NULL,
  `entity_resp_id` int(10) unsigned DEFAULT NULL,
  `application_block_id` int(10) unsigned DEFAULT NULL,
  `documentation` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `rto` int(11) DEFAULT NULL,
  `rpo` int(11) DEFAULT NULL,
  `install_date` date DEFAULT NULL,
  `prod_date` date DEFAULT NULL,
  `update_date` date DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `patching_frequency` int(11) DEFAULT NULL,
  `next_update` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_resp_fk_1488612` (`entity_resp_id`),
  KEY `application_block_fk_1644592` (`application_block_id`),
  KEY `document_id_fk_4394343` (`icon_id`),
  KEY `applications_perimeter_id_index` (`perimeter_id`),
  KEY `applications_name_index` (`name`),
  KEY `applications_created_by_foreign` (`created_by`),
  KEY `applications_updated_by_foreign` (`updated_by`),
  KEY `applications_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `application_block_fk_1644592` FOREIGN KEY (`application_block_id`) REFERENCES `application_blocks` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `applications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `applications_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_id_fk_4394343` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `entity_resp_fk_1488612` FOREIGN KEY (`entity_resp_id`) REFERENCES `entities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `subject_id` int(10) unsigned DEFAULT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `properties` text DEFAULT NULL,
  `host` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backup_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_logical_server` (
  `backup_id` int(10) unsigned NOT NULL,
  `logical_server_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`backup_id`,`logical_server_id`),
  KEY `backup_logical_server_logical_server_id_foreign` (`logical_server_id`),
  CONSTRAINT `backup_logical_server_backup_id_foreign` FOREIGN KEY (`backup_id`) REFERENCES `backups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `backup_logical_server_logical_server_id_foreign` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backup_storage_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_storage_device` (
  `backup_id` int(10) unsigned NOT NULL,
  `storage_device_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`backup_id`,`storage_device_id`),
  KEY `backup_storage_device_storage_device_id_foreign` (`storage_device_id`),
  CONSTRAINT `backup_storage_device_backup_id_foreign` FOREIGN KEY (`backup_id`) REFERENCES `backups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `backup_storage_device_storage_device_id_foreign` FOREIGN KEY (`storage_device_id`) REFERENCES `storage_devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `attributes` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `backup_frequency` tinyint(3) unsigned DEFAULT NULL COMMENT '1=daily,2=weekly,3=monthly',
  `backup_cycle` tinyint(3) unsigned DEFAULT NULL COMMENT 'Ex: 1 full/day + 1 weekly/month',
  `backup_retention` smallint(5) unsigned DEFAULT NULL COMMENT 'Retention in days',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_perimeter_id_index` (`perimeter_id`),
  KEY `backups_name_index` (`name`),
  KEY `backups_created_by_foreign` (`created_by`),
  KEY `backups_updated_by_foreign` (`updated_by`),
  KEY `backups_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `backups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `backups_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `backups_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `backups_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bay_wifi_terminal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bay_wifi_terminal` (
  `wifi_terminal_id` int(10) unsigned NOT NULL,
  `bay_id` int(10) unsigned NOT NULL,
  KEY `wifi_terminal_id_fk_1485509` (`wifi_terminal_id`),
  KEY `bay_id_fk_1485509` (`bay_id`),
  CONSTRAINT `bay_id_fk_1485509` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wifi_terminal_id_fk_1485509` FOREIGN KEY (`wifi_terminal_id`) REFERENCES `wifi_terminals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_fk_1483441` (`building_id`),
  KEY `site_id_fk_1483442` (`site_id`),
  KEY `bays_perimeter_id_index` (`perimeter_id`),
  KEY `bays_name_index` (`name`),
  KEY `bays_created_by_foreign` (`created_by`),
  KEY `bays_updated_by_foreign` (`updated_by`),
  KEY `bays_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bays_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bays_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bays_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `bays_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `building_fk_1483441` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `site_id_fk_1483442` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `building_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `building_zone` (
  `building_id` int(10) unsigned NOT NULL,
  `zone_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`building_id`,`zone_id`),
  KEY `building_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `building_zone_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `building_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1483431` (`site_id`),
  KEY `building_id_fk_94821232` (`building_id`),
  KEY `document_id_fk_49574431` (`icon_id`),
  KEY `buildings_perimeter_id_index` (`perimeter_id`),
  KEY `buildings_name_index` (`name`),
  KEY `buildings_created_by_foreign` (`created_by`),
  KEY `buildings_updated_by_foreign` (`updated_by`),
  KEY `buildings_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `building_id_fk_94821232` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `buildings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `buildings_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `buildings_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `buildings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_id_fk_49574431` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `site_fk_1483431` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cartographers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cartographers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cartographiable_type` varchar(255) NOT NULL,
  `cartographiable_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `role_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cartographers_cartographiable_type_cartographiable_id_index` (`cartographiable_type`,`cartographiable_id`),
  KEY `cartographers_user_id_index` (`user_id`),
  KEY `cartographers_role_id_index` (`role_id`),
  CONSTRAINT `cartographers_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cartographers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certificate_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificate_logical_server` (
  `certificate_id` int(10) unsigned NOT NULL,
  `logical_server_id` int(10) unsigned NOT NULL,
  KEY `certificate_id_fk_9483453` (`certificate_id`),
  KEY `logical_server_id_fk_9483453` (`logical_server_id`),
  CONSTRAINT `certificate_logical_server_certificate_id_foreign` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `certificate_logical_server_logical_server_id_foreign` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `start_validity` date DEFAULT NULL,
  `end_validity` date DEFAULT NULL,
  `last_notification` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certificates_perimeter_id_index` (`perimeter_id`),
  KEY `certificates_name_index` (`name`),
  KEY `certificates_created_by_foreign` (`created_by`),
  KEY `certificates_updated_by_foreign` (`updated_by`),
  KEY `certificates_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `certificates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certificates_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certificates_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `certificates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cluster_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cluster_logical_server` (
  `cluster_id` int(10) unsigned NOT NULL,
  `logical_server_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cluster_id`,`logical_server_id`),
  KEY `cluster_logical_server_logical_server_id_index` (`logical_server_id`),
  CONSTRAINT `cluster_logical_server_cluster_id_foreign` FOREIGN KEY (`cluster_id`) REFERENCES `clusters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cluster_logical_server_logical_server_id_foreign` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cluster_physical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cluster_physical_server` (
  `cluster_id` int(10) unsigned NOT NULL,
  `physical_server_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cluster_id`,`physical_server_id`),
  KEY `cluster_physical_server_physical_server_id_index` (`physical_server_id`),
  CONSTRAINT `cluster_physical_server_cluster_id_foreign` FOREIGN KEY (`cluster_id`) REFERENCES `clusters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cluster_physical_server_physical_server_id_foreign` FOREIGN KEY (`physical_server_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cluster_router`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cluster_router` (
  `cluster_id` int(10) unsigned NOT NULL,
  `router_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cluster_id`,`router_id`),
  KEY `cluster_router_router_id_index` (`router_id`),
  CONSTRAINT `cluster_router_cluster_id_foreign` FOREIGN KEY (`cluster_id`) REFERENCES `clusters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cluster_router_router_id_foreign` FOREIGN KEY (`router_id`) REFERENCES `routers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clusters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clusters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id_fk_495432841` (`icon_id`),
  KEY `clusters_perimeter_id_index` (`perimeter_id`),
  KEY `clusters_name_index` (`name`),
  KEY `clusters_created_by_foreign` (`created_by`),
  KEY `clusters_updated_by_foreign` (`updated_by`),
  KEY `clusters_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `clusters_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clusters_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clusters_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `clusters_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_id_fk_495432841` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_database`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_database` (
  `database_id` int(10) unsigned NOT NULL,
  `container_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`database_id`,`container_id`),
  KEY `container_database_container_id_foreign` (`container_id`),
  CONSTRAINT `container_database_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `container_database_database_id_foreign` FOREIGN KEY (`database_id`) REFERENCES `databases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_logical_server` (
  `container_id` int(10) unsigned NOT NULL,
  `logical_server_id` int(10) unsigned NOT NULL,
  KEY `container_id_fk_54933032` (`container_id`),
  KEY `logical_server_id_fk_4394832` (`logical_server_id`),
  CONSTRAINT `container_logical_server_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `container_logical_server_logical_server_id_foreign` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `containers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id_fk_43948593` (`icon_id`),
  KEY `containers_perimeter_id_index` (`perimeter_id`),
  KEY `containers_created_by_foreign` (`created_by`),
  KEY `containers_updated_by_foreign` (`updated_by`),
  KEY `containers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `containers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `containers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `containers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `containers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_id_fk_434833774` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cpe_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpe_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cpe_vendor_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpe_products_cpe_vendor_id_name_unique` (`cpe_vendor_id`,`name`),
  KEY `cpe_product_fk_1485479` (`cpe_vendor_id`),
  CONSTRAINT `cpe_vendor_fk_1454431` FOREIGN KEY (`cpe_vendor_id`) REFERENCES `cpe_vendors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cpe_vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpe_vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `part` char(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpe_vendors_part_name_unique` (`part`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cpe_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpe_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cpe_product_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpe_versions_cpe_product_id_name_unique` (`cpe_product_id`,`name`),
  KEY `cpe_version_fk_1485479` (`cpe_product_id`),
  CONSTRAINT `cpe_product_fk_1447431` FOREIGN KEY (`cpe_product_id`) REFERENCES `cpe_products` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_processing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `legal_basis` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `responsible` longtext DEFAULT NULL,
  `purpose` longtext DEFAULT NULL,
  `lawfulness` text DEFAULT NULL,
  `lawfulness_consent` tinyint(1) DEFAULT NULL,
  `lawfulness_contract` tinyint(1) DEFAULT NULL,
  `lawfulness_legal_obligation` tinyint(1) DEFAULT NULL,
  `lawfulness_vital_interest` tinyint(1) DEFAULT NULL,
  `lawfulness_public_interest` tinyint(1) DEFAULT NULL,
  `lawfulness_legitimate_interest` tinyint(1) DEFAULT NULL,
  `categories` longtext DEFAULT NULL,
  `data_source` text DEFAULT NULL,
  `data_collection_obligation` text DEFAULT NULL,
  `recipients` longtext DEFAULT NULL,
  `transfert` longtext DEFAULT NULL,
  `automated_decision_making` text DEFAULT NULL,
  `retention` longtext DEFAULT NULL,
  `data_subject_rights` text DEFAULT NULL,
  `update_date` date DEFAULT NULL,
  `controls` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_processing_perimeter_id_index` (`perimeter_id`),
  KEY `data_processing_name_index` (`name`),
  KEY `data_processing_created_by_foreign` (`created_by`),
  KEY `data_processing_updated_by_foreign` (`updated_by`),
  KEY `data_processing_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `data_processing_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `data_processing_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `data_processing_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `data_processing_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_processing_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_processing_document` (
  `data_processing_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `data_processing_id_fk_6930583` (`data_processing_id`),
  KEY `operation_id_fk_4355431` (`document_id`),
  CONSTRAINT `data_processing_id_fk_42343234` FOREIGN KEY (`data_processing_id`) REFERENCES `data_processing` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_3439483` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_processing_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_processing_information` (
  `data_processing_id` int(10) unsigned NOT NULL,
  `information_id` int(10) unsigned NOT NULL,
  KEY `data_processing_id_fk_58305863` (`data_processing_id`),
  KEY `information_id_fk_4384483` (`information_id`),
  CONSTRAINT `data_processing_id_fk_493438483` FOREIGN KEY (`data_processing_id`) REFERENCES `data_processing` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `information_id_fk_0483434` FOREIGN KEY (`information_id`) REFERENCES `information` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_processing_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_processing_process` (
  `data_processing_id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  KEY `data_processing_id_fk_5435435` (`data_processing_id`),
  KEY `process_id_fk_594358` (`process_id`),
  CONSTRAINT `data_processing_id_fk_764545345` FOREIGN KEY (`data_processing_id`) REFERENCES `data_processing` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `process_id_fk_0483434` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `database_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_entity` (
  `database_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  KEY `database_id_fk_1485563` (`database_id`),
  KEY `entity_id_fk_1485563` (`entity_id`),
  CONSTRAINT `database_id_fk_1485563` FOREIGN KEY (`database_id`) REFERENCES `databases` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `entity_id_fk_1485563` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `database_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_information` (
  `database_id` int(10) unsigned NOT NULL,
  `information_id` int(10) unsigned NOT NULL,
  KEY `database_id_fk_1485570` (`database_id`),
  KEY `information_id_fk_1485570` (`information_id`),
  CONSTRAINT `database_id_fk_1485570` FOREIGN KEY (`database_id`) REFERENCES `databases` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `information_id_fk_1485570` FOREIGN KEY (`information_id`) REFERENCES `information` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `database_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_logical_server` (
  `database_id` int(10) unsigned NOT NULL,
  `logical_server_id` int(10) unsigned NOT NULL,
  KEY `database_id_fk_1542475` (`database_id`),
  KEY `logical_server_id_fk_1542475` (`logical_server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `databases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `databases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `external` varchar(255) DEFAULT NULL,
  `entity_resp_id` int(10) unsigned DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_resp_fk_1485569` (`entity_resp_id`),
  KEY `databases_icon_id_foreign` (`icon_id`),
  KEY `databases_perimeter_id_index` (`perimeter_id`),
  KEY `databases_name_index` (`name`),
  KEY `databases_created_by_foreign` (`created_by`),
  KEY `databases_updated_by_foreign` (`updated_by`),
  KEY `databases_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `databases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `databases_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `databases_icon_id_foreign` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `databases_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `databases_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entity_resp_fk_1485569` FOREIGN KEY (`entity_resp_id`) REFERENCES `entities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dhcp_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dhcp_servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dhcp_servers_perimeter_id_index` (`perimeter_id`),
  KEY `dhcp_servers_name_index` (`name`),
  KEY `dhcp_servers_created_by_foreign` (`created_by`),
  KEY `dhcp_servers_updated_by_foreign` (`updated_by`),
  KEY `dhcp_servers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `dhcp_servers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dhcp_servers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dhcp_servers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `dhcp_servers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dnsservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dnsservers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dnsservers_perimeter_id_index` (`perimeter_id`),
  KEY `dnsservers_name_index` (`name`),
  KEY `dnsservers_created_by_foreign` (`created_by`),
  KEY `dnsservers_updated_by_foreign` (`updated_by`),
  KEY `dnsservers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `dnsservers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dnsservers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dnsservers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `dnsservers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_external_connected_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_external_connected_entity` (
  `external_connected_entity_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `external_connected_entity_idx_2143243` (`external_connected_entity_id`),
  KEY `document_idx_434934839` (`document_id`),
  CONSTRAINT `document_id_fk_4394384` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `external_connected_entity_id_fk_434854` FOREIGN KEY (`external_connected_entity_id`) REFERENCES `external_connected_entities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_logical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_logical_server` (
  `logical_server_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `logical_server_id_fk_43832473` (`logical_server_id`),
  KEY `document_id_fk_1284334` (`document_id`),
  CONSTRAINT `document_id_fk_1284334` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `logical_server_id_fk_43832473` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_relation` (
  `relation_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `relation_id_fk_6948334` (`relation_id`),
  KEY `document_id_fk_5492844` (`document_id`),
  CONSTRAINT `document_id_fk_5492844` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `relation_id_fk_6948334` FOREIGN KEY (`relation_id`) REFERENCES `relations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ext_refs` varchar(255) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `mimetype` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_created_by_foreign` (`created_by`),
  KEY `documents_updated_by_foreign` (`updated_by`),
  KEY `documents_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `domain_forest_ad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_forest_ad` (
  `forest_ad_id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned NOT NULL,
  KEY `forest_ad_id_fk_1492084` (`forest_ad_id`),
  KEY `domaine_ad_id_fk_1492084` (`domain_id`),
  CONSTRAINT `domain_id_fk_1492084` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `forest_ad_id_fk_1492084` FOREIGN KEY (`forest_ad_id`) REFERENCES `forest_ads` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domains` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `domain_ctrl_cnt` int(11) DEFAULT NULL,
  `user_count` int(11) DEFAULT NULL,
  `machine_count` int(11) DEFAULT NULL,
  `relation_inter_domaine` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domains_perimeter_id_index` (`perimeter_id`),
  KEY `domains_name_index` (`name`),
  KEY `domains_created_by_foreign` (`created_by`),
  KEY `domains_updated_by_foreign` (`updated_by`),
  KEY `domains_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `domains_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domains_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `domains_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `domains_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `security_level` longtext DEFAULT NULL,
  `contact_point` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `parent_entity_id` int(10) unsigned DEFAULT NULL,
  `external_ref_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `entity_id_fk_4398013` (`parent_entity_id`),
  KEY `document_id_fk_129486` (`icon_id`),
  KEY `entities_perimeter_id_index` (`perimeter_id`),
  KEY `entities_name_index` (`name`),
  KEY `entities_created_by_foreign` (`created_by`),
  KEY `entities_updated_by_foreign` (`updated_by`),
  KEY `entities_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_id_fk_129486` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `entities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entities_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entities_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `entities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entity_id_fk_4398013` FOREIGN KEY (`parent_entity_id`) REFERENCES `entities` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_document` (
  `entity_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  KEY `activity_id_fk_4325433` (`entity_id`),
  KEY `operation_id_fk_5837593` (`document_id`),
  CONSTRAINT `document_id_fk_4355430` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `entity_id_fk_4325432` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_process` (
  `process_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  KEY `process_id_fk_1627958` (`process_id`),
  KEY `entity_id_fk_1627958` (`entity_id`),
  CONSTRAINT `entity_id_fk_1627958` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `process_id_fk_1627958` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `external_connected_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_connected_entities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `security` text DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `network_id` int(10) unsigned DEFAULT NULL,
  `src` varchar(255) DEFAULT NULL,
  `src_desc` varchar(255) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  `dest_desc` varchar(255) DEFAULT NULL,
  `contacts` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_id_fk_1295034` (`entity_id`),
  KEY `network_id_fk_8596554` (`network_id`),
  KEY `external_connected_entities_perimeter_id_index` (`perimeter_id`),
  KEY `external_connected_entities_name_index` (`name`),
  KEY `external_connected_entities_created_by_foreign` (`created_by`),
  KEY `external_connected_entities_updated_by_foreign` (`updated_by`),
  KEY `external_connected_entities_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `entity_id_fk_1295034` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `external_connected_entities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `external_connected_entities_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `external_connected_entities_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `external_connected_entities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `network_id_fk_8596554` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `external_connected_entity_subnetwork`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_connected_entity_subnetwork` (
  `external_connected_entity_id` int(10) unsigned NOT NULL,
  `subnetwork_id` int(10) unsigned NOT NULL,
  KEY `external_connected_entity_idx_59458458` (`external_connected_entity_id`),
  KEY `subnetwork_idx_4343848` (`subnetwork_id`),
  CONSTRAINT `external_connected_entity_id_fk_4302049` FOREIGN KEY (`external_connected_entity_id`) REFERENCES `external_connected_entities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subnetwork_id_fk_09848239` FOREIGN KEY (`subnetwork_id`) REFERENCES `subnetworks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forest_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `forest_ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `zone_admin_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zone_admin_fk_1482667` (`zone_admin_id`),
  KEY `forest_ads_perimeter_id_index` (`perimeter_id`),
  KEY `forest_ads_name_index` (`name`),
  KEY `forest_ads_created_by_foreign` (`created_by`),
  KEY `forest_ads_updated_by_foreign` (`updated_by`),
  KEY `forest_ads_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `forest_ads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `forest_ads_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `forest_ads_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `forest_ads_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zone_admin_fk_1482667` FOREIGN KEY (`zone_admin_id`) REFERENCES `zone_admins` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gateways`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gateways` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `authentification` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gateways_perimeter_id_index` (`perimeter_id`),
  KEY `gateways_name_index` (`name`),
  KEY `gateways_created_by_foreign` (`created_by`),
  KEY `gateways_updated_by_foreign` (`updated_by`),
  KEY `gateways_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `gateways_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gateways_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gateways_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `gateways_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `graphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `graphs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graphs_name_index` (`name`),
  KEY `graphs_created_by_foreign` (`created_by`),
  KEY `graphs_updated_by_foreign` (`updated_by`),
  KEY `graphs_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `graphs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `graphs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `graphs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `information` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `administrator` varchar(255) DEFAULT NULL,
  `storage` varchar(255) DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `sensitivity` varchar(255) DEFAULT NULL,
  `constraints` longtext DEFAULT NULL,
  `retention` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `information_perimeter_id_index` (`perimeter_id`),
  KEY `information_name_index` (`name`),
  KEY `document_id_fk_61928374` (`icon_id`),
  KEY `information_created_by_foreign` (`created_by`),
  KEY `information_updated_by_foreign` (`updated_by`),
  KEY `information_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_id_fk_61928374` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `information_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `information_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `information_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `information_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `information_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `information_information` (
  `information_id` int(10) unsigned NOT NULL,
  `child_information_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`information_id`,`child_information_id`),
  KEY `information_information_child_information_id_foreign` (`child_information_id`),
  CONSTRAINT `information_information_child_information_id_foreign` FOREIGN KEY (`child_information_id`) REFERENCES `information` (`id`) ON DELETE CASCADE,
  CONSTRAINT `information_information_information_id_foreign` FOREIGN KEY (`information_id`) REFERENCES `information` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `information_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `information_process` (
  `information_id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  KEY `information_id_fk_1473025` (`information_id`),
  KEY `process_id_fk_1473025` (`process_id`),
  CONSTRAINT `information_id_fk_1473025` FOREIGN KEY (`information_id`) REFERENCES `information` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `process_id_fk_1473025` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lan_man`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lan_man` (
  `man_id` int(10) unsigned NOT NULL,
  `lan_id` int(10) unsigned NOT NULL,
  KEY `man_id_fk_1490345` (`man_id`),
  KEY `lan_id_fk_1490345` (`lan_id`),
  CONSTRAINT `lan_id_fk_1490345` FOREIGN KEY (`lan_id`) REFERENCES `lans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `man_id_fk_1490345` FOREIGN KEY (`man_id`) REFERENCES `mans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lan_wan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lan_wan` (
  `wan_id` int(10) unsigned NOT NULL,
  `lan_id` int(10) unsigned NOT NULL,
  KEY `wan_id_fk_1490368` (`wan_id`),
  KEY `lan_id_fk_1490368` (`lan_id`),
  CONSTRAINT `lan_id_fk_1490368` FOREIGN KEY (`lan_id`) REFERENCES `lans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wan_id_fk_1490368` FOREIGN KEY (`wan_id`) REFERENCES `wans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lans_perimeter_id_index` (`perimeter_id`),
  KEY `lans_name_index` (`name`),
  KEY `lans_created_by_foreign` (`created_by`),
  KEY `lans_updated_by_foreign` (`updated_by`),
  KEY `lans_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `lans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lans_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lans_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `lans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `logical_flows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logical_flows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `chain` varchar(255) DEFAULT NULL,
  `interface` varchar(255) DEFAULT NULL,
  `router_id` int(10) unsigned DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `protocol` varchar(255) DEFAULT NULL,
  `source_ip_range` varchar(255) DEFAULT NULL,
  `dest_ip_range` varchar(255) DEFAULT NULL,
  `source_port` varchar(255) DEFAULT NULL,
  `dest_port` varchar(255) DEFAULT NULL,
  `logical_server_source_id` int(10) unsigned DEFAULT NULL,
  `peripheral_source_id` int(10) unsigned DEFAULT NULL,
  `physical_server_source_id` int(10) unsigned DEFAULT NULL,
  `storage_device_source_id` int(10) unsigned DEFAULT NULL,
  `workstation_source_id` int(10) unsigned DEFAULT NULL,
  `physical_security_device_source_id` int(10) unsigned DEFAULT NULL,
  `security_device_source_id` int(10) unsigned DEFAULT NULL,
  `subnetwork_source_id` int(10) unsigned DEFAULT NULL,
  `cluster_source_id` int(10) unsigned DEFAULT NULL,
  `logical_server_dest_id` int(10) unsigned DEFAULT NULL,
  `peripheral_dest_id` int(10) unsigned DEFAULT NULL,
  `physical_server_dest_id` int(10) unsigned DEFAULT NULL,
  `storage_device_dest_id` int(10) unsigned DEFAULT NULL,
  `workstation_dest_id` int(10) unsigned DEFAULT NULL,
  `physical_security_device_dest_id` int(10) unsigned DEFAULT NULL,
  `security_device_dest_id` int(10) unsigned DEFAULT NULL,
  `subnetwork_dest_id` int(10) unsigned DEFAULT NULL,
  `cluster_dest_id` int(10) unsigned DEFAULT NULL,
  `users` varchar(255) DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `router_id_fk_4382393` (`router_id`),
  KEY `logical_flows_logical_server_source_id_foreign` (`logical_server_source_id`),
  KEY `logical_flows_peripheral_source_id_foreign` (`peripheral_source_id`),
  KEY `logical_flows_physical_server_source_id_foreign` (`physical_server_source_id`),
  KEY `logical_flows_storage_device_source_id_foreign` (`storage_device_source_id`),
  KEY `logical_flows_workstation_source_id_foreign` (`workstation_source_id`),
  KEY `logical_flows_physical_security_device_source_id_foreign` (`physical_security_device_source_id`),
  KEY `logical_flows_logical_server_dest_id_foreign` (`logical_server_dest_id`),
  KEY `logical_flows_peripheral_dest_id_foreign` (`peripheral_dest_id`),
  KEY `logical_flows_physical_server_dest_id_foreign` (`physical_server_dest_id`),
  KEY `logical_flows_storage_device_dest_id_foreign` (`storage_device_dest_id`),
  KEY `logical_flows_workstation_dest_id_foreign` (`workstation_dest_id`),
  KEY `logical_flows_physical_security_device_dest_id_foreign` (`physical_security_device_dest_id`),
  KEY `logical_flows_subnetwork_source_id_index` (`subnetwork_source_id`),
  KEY `logical_flows_subnetwork_dest_id_index` (`subnetwork_dest_id`),
  KEY `logical_flows_security_device_source_id_foreign` (`security_device_source_id`),
  KEY `logical_flows_security_device_dest_id_foreign` (`security_device_dest_id`),
  KEY `logical_flows_cluster_source_id_foreign` (`cluster_source_id`),
  KEY `logical_flows_cluster_dest_id_foreign` (`cluster_dest_id`),
  KEY `logical_flows_perimeter_id_index` (`perimeter_id`),
  KEY `logical_flows_name_index` (`name`),
  KEY `logical_flows_created_by_foreign` (`created_by`),
  KEY `logical_flows_updated_by_foreign` (`updated_by`),
  KEY `logical_flows_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `logical_flows_cluster_dest_id_foreign` FOREIGN KEY (`cluster_dest_id`) REFERENCES `clusters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_cluster_source_id_foreign` FOREIGN KEY (`cluster_source_id`) REFERENCES `clusters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_logical_server_dest_id_foreign` FOREIGN KEY (`logical_server_dest_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_logical_server_source_id_foreign` FOREIGN KEY (`logical_server_source_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `logical_flows_peripheral_dest_id_foreign` FOREIGN KEY (`peripheral_dest_id`) REFERENCES `peripherals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_peripheral_source_id_foreign` FOREIGN KEY (`peripheral_source_id`) REFERENCES `peripherals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_physical_security_device_dest_id_foreign` FOREIGN KEY (`physical_security_device_dest_id`) REFERENCES `physical_security_devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_physical_security_device_source_id_foreign` FOREIGN KEY (`physical_security_device_source_id`) REFERENCES `physical_security_devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_physical_server_dest_id_foreign` FOREIGN KEY (`physical_server_dest_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_physical_server_source_id_foreign` FOREIGN KEY (`physical_server_source_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_security_device_dest_id_foreign` FOREIGN KEY (`security_device_dest_id`) REFERENCES `security_devices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_security_device_source_id_foreign` FOREIGN KEY (`security_device_source_id`) REFERENCES `security_devices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_storage_device_dest_id_foreign` FOREIGN KEY (`storage_device_dest_id`) REFERENCES `storage_devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_storage_device_source_id_foreign` FOREIGN KEY (`storage_device_source_id`) REFERENCES `storage_devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_subnetwork_dest_id_foreign` FOREIGN KEY (`subnetwork_dest_id`) REFERENCES `subnetworks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_subnetwork_source_id_foreign` FOREIGN KEY (`subnetwork_source_id`) REFERENCES `subnetworks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_flows_workstation_dest_id_foreign` FOREIGN KEY (`workstation_dest_id`) REFERENCES `workstations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logical_flows_workstation_source_id_foreign` FOREIGN KEY (`workstation_source_id`) REFERENCES `workstations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `router_id_fk_4382393` FOREIGN KEY (`router_id`) REFERENCES `routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `logical_server_physical_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logical_server_physical_server` (
  `logical_server_id` int(10) unsigned NOT NULL,
  `physical_server_id` int(10) unsigned NOT NULL,
  KEY `logical_server_id_fk_1657961` (`logical_server_id`),
  KEY `physical_server_id_fk_1657961` (`physical_server_id`),
  CONSTRAINT `logical_server_id_fk_1657961` FOREIGN KEY (`logical_server_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_server_id_fk_1657961` FOREIGN KEY (`physical_server_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `logical_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logical_servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `net_services` varchar(255) DEFAULT NULL,
  `configuration` longtext DEFAULT NULL,
  `operating_system` varchar(255) DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `memory` varchar(255) DEFAULT NULL,
  `environment` varchar(255) DEFAULT NULL,
  `disk` int(11) DEFAULT NULL,
  `disk_used` int(11) DEFAULT NULL,
  `install_date` date DEFAULT NULL,
  `update_date` date DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `patching_frequency` int(11) DEFAULT NULL,
  `next_update` date DEFAULT NULL,
  `domain_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domain_id_fk_493844` (`domain_id`),
  KEY `logical_servers_active` (`active`),
  KEY `document_id_fk_51303394` (`icon_id`),
  KEY `logical_servers_perimeter_id_index` (`perimeter_id`),
  KEY `logical_servers_name_index` (`name`),
  KEY `logical_servers_created_by_foreign` (`created_by`),
  KEY `logical_servers_updated_by_foreign` (`updated_by`),
  KEY `logical_servers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_id_fk_51303394` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `domain_id_fk_493844` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `logical_servers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_servers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logical_servers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `logical_servers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `macro_processuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `macro_processuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `io_elements` longtext DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `macro_processuses_perimeter_id_index` (`perimeter_id`),
  KEY `macro_processuses_name_index` (`name`),
  KEY `macro_processuses_created_by_foreign` (`created_by`),
  KEY `macro_processuses_updated_by_foreign` (`updated_by`),
  KEY `macro_processuses_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `macro_processuses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `macro_processuses_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `macro_processuses_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `macro_processuses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `man_wan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `man_wan` (
  `wan_id` int(10) unsigned NOT NULL,
  `man_id` int(10) unsigned NOT NULL,
  KEY `wan_id_fk_1490367` (`wan_id`),
  KEY `man_id_fk_1490367` (`man_id`),
  CONSTRAINT `man_id_fk_1490367` FOREIGN KEY (`man_id`) REFERENCES `mans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wan_id_fk_1490367` FOREIGN KEY (`wan_id`) REFERENCES `wans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `parent_man_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `man_id_fk_4385454` (`parent_man_id`),
  KEY `mans_perimeter_id_index` (`perimeter_id`),
  KEY `mans_name_index` (`name`),
  KEY `mans_created_by_foreign` (`created_by`),
  KEY `mans_updated_by_foreign` (`updated_by`),
  KEY `mans_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `man_id_fk_4385454` FOREIGN KEY (`parent_man_id`) REFERENCES `mans` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `mans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mans_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mans_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `mans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mercator_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mercator_modules` (
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monarc_sync_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `monarc_sync_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model` varchar(255) NOT NULL,
  `mercator_id` int(10) unsigned NOT NULL,
  `object_uuid` char(36) NOT NULL,
  `anr_id` int(10) unsigned NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `monarc_sync_items_unique` (`anr_id`,`model`,`mercator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `network_switch_physical_switch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_switch_physical_switch` (
  `network_switch_id` int(10) unsigned NOT NULL,
  `physical_switch_id` int(10) unsigned NOT NULL,
  KEY `network_switch_id_fk_543323` (`network_switch_id`),
  KEY `physical_switch_id_fk_4543143` (`physical_switch_id`),
  CONSTRAINT `network_switch_id_fk_543323` FOREIGN KEY (`network_switch_id`) REFERENCES `network_switches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_switch_id_fk_4543143` FOREIGN KEY (`physical_switch_id`) REFERENCES `physical_switches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `network_switch_vlan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_switch_vlan` (
  `network_switch_id` int(10) unsigned NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`network_switch_id`,`vlan_id`),
  KEY `network_switch_vlan_network_switch_id_index` (`network_switch_id`),
  KEY `network_switch_vlan_vlan_id_index` (`vlan_id`),
  CONSTRAINT `network_switch_vlan_network_switch_id_foreign` FOREIGN KEY (`network_switch_id`) REFERENCES `network_switches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `network_switch_vlan_vlan_id_foreign` FOREIGN KEY (`vlan_id`) REFERENCES `vlans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `network_switches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_switches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `network_switches_perimeter_id_index` (`perimeter_id`),
  KEY `network_switches_name_index` (`name`),
  KEY `network_switches_created_by_foreign` (`created_by`),
  KEY `network_switches_updated_by_foreign` (`updated_by`),
  KEY `network_switches_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `network_switches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `network_switches_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `network_switches_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `network_switches_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `networks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `protocol_type` varchar(255) DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `responsible_sec` varchar(255) DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `networks_perimeter_id_index` (`perimeter_id`),
  KEY `networks_name_index` (`name`),
  KEY `networks_created_by_foreign` (`created_by`),
  KEY `networks_updated_by_foreign` (`updated_by`),
  KEY `networks_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `networks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `networks_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `networks_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `networks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` char(80) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` char(80) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_id` char(36) NOT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` char(36) NOT NULL,
  `owner_type` varchar(255) DEFAULT NULL,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `redirect_uris` text NOT NULL,
  `grant_types` text NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_owner_type_owner_id_index` (`owner_type`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_device_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_device_codes` (
  `id` char(80) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` char(36) NOT NULL,
  `user_code` char(8) NOT NULL,
  `scopes` text NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `user_approved_at` datetime DEFAULT NULL,
  `last_polled_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_device_codes_user_code_unique` (`user_code`),
  KEY `oauth_device_codes_user_id_index` (`user_id`),
  KEY `oauth_device_codes_client_id_index` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` char(80) NOT NULL,
  `access_token_id` char(80) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `operation_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operation_task` (
  `operation_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  KEY `operation_id_fk_1472749` (`operation_id`),
  KEY `task_id_fk_1472749` (`task_id`),
  CONSTRAINT `operation_id_fk_1472749` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `task_id_fk_1472749` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `process_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_id_fk_7945129` (`process_id`),
  KEY `operations_perimeter_id_index` (`perimeter_id`),
  KEY `operations_name_index` (`name`),
  KEY `operations_created_by_foreign` (`created_by`),
  KEY `operations_updated_by_foreign` (`updated_by`),
  KEY `operations_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `operations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operations_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `operations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `process_id_fk_7945129` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parameters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parameters_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `perimeters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `perimeters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perimeters_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `peripherals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `peripherals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `bay_id` int(10) unsigned DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `provider_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `domain_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485449` (`site_id`),
  KEY `building_fk_1485450` (`building_id`),
  KEY `bay_fk_1485451` (`bay_id`),
  KEY `entity_id_fk_4383234` (`provider_id`),
  KEY `document_id_fk_129484` (`icon_id`),
  KEY `peripherals_domain_id_foreign` (`domain_id`),
  KEY `peripherals_perimeter_id_index` (`perimeter_id`),
  KEY `peripherals_name_index` (`name`),
  KEY `peripherals_created_by_foreign` (`created_by`),
  KEY `peripherals_updated_by_foreign` (`updated_by`),
  KEY `peripherals_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bay_fk_1485451` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `building_fk_1485450` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_129484` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `entity_id_fk_4383234` FOREIGN KEY (`provider_id`) REFERENCES `entities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `peripherals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `peripherals_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `peripherals_domain_id_foreign` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`),
  CONSTRAINT `peripherals_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `peripherals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `site_fk_1485449` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  KEY `role_id_fk_1470794` (`role_id`),
  KEY `permission_id_fk_1470794` (`permission_id`),
  CONSTRAINT `permission_id_fk_1470794` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `role_id_fk_1470794` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ext_refs` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `phones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `physical_switch_id` int(10) unsigned DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485479` (`site_id`),
  KEY `building_fk_1485480` (`building_id`),
  KEY `physical_switch_fk_5738332` (`physical_switch_id`),
  KEY `phones_perimeter_id_index` (`perimeter_id`),
  KEY `phones_name_index` (`name`),
  KEY `phones_created_by_foreign` (`created_by`),
  KEY `phones_updated_by_foreign` (`updated_by`),
  KEY `phones_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `building_fk_1485480` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `phones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `phones_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `phones_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `phones_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_switch_fk_5738332` FOREIGN KEY (`physical_switch_id`) REFERENCES `physical_switches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `site_fk_1485479` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `src_port` varchar(255) DEFAULT NULL,
  `dest_port` varchar(255) DEFAULT NULL,
  `peripheral_src_id` int(10) unsigned DEFAULT NULL,
  `phone_src_id` int(10) unsigned DEFAULT NULL,
  `physical_router_src_id` int(10) unsigned DEFAULT NULL,
  `physical_security_device_src_id` int(10) unsigned DEFAULT NULL,
  `physical_server_src_id` int(10) unsigned DEFAULT NULL,
  `physical_switch_src_id` int(10) unsigned DEFAULT NULL,
  `storage_device_src_id` int(10) unsigned DEFAULT NULL,
  `wifi_terminal_src_id` int(10) unsigned DEFAULT NULL,
  `workstation_src_id` int(10) unsigned DEFAULT NULL,
  `logical_server_src_id` int(10) unsigned DEFAULT NULL,
  `network_switch_src_id` int(10) unsigned DEFAULT NULL,
  `router_src_id` int(10) unsigned DEFAULT NULL,
  `peripheral_dest_id` int(10) unsigned DEFAULT NULL,
  `phone_dest_id` int(10) unsigned DEFAULT NULL,
  `physical_router_dest_id` int(10) unsigned DEFAULT NULL,
  `physical_security_device_dest_id` int(10) unsigned DEFAULT NULL,
  `physical_server_dest_id` int(10) unsigned DEFAULT NULL,
  `physical_switch_dest_id` int(10) unsigned DEFAULT NULL,
  `storage_device_dest_id` int(10) unsigned DEFAULT NULL,
  `wifi_terminal_dest_id` int(10) unsigned DEFAULT NULL,
  `workstation_dest_id` int(10) unsigned DEFAULT NULL,
  `logical_server_dest_id` int(10) unsigned DEFAULT NULL,
  `network_switch_dest_id` int(10) unsigned DEFAULT NULL,
  `router_dest_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peripheral_src_id_fk` (`peripheral_src_id`),
  KEY `phone_src_id_fk` (`phone_src_id`),
  KEY `physical_router_src_id_fk` (`physical_router_src_id`),
  KEY `physical_security_device_src_id_fk` (`physical_security_device_src_id`),
  KEY `physical_server_src_id_fk` (`physical_server_src_id`),
  KEY `physical_switch_src_id_fk` (`physical_switch_src_id`),
  KEY `storage_device_src_id_fk` (`storage_device_src_id`),
  KEY `wifi_terminal_src_id_fk` (`wifi_terminal_src_id`),
  KEY `workstation_src_id_fk` (`workstation_src_id`),
  KEY `peripheral_dest_id_fk` (`peripheral_dest_id`),
  KEY `phone_dest_id_fk` (`phone_dest_id`),
  KEY `physical_router_dest_id_fk` (`physical_router_dest_id`),
  KEY `physical_security_device_dest_id_fk` (`physical_security_device_dest_id`),
  KEY `physical_server_dest_id_fk` (`physical_server_dest_id`),
  KEY `physical_switch_dest_id_fk` (`physical_switch_dest_id`),
  KEY `storage_device_dest_id_fk` (`storage_device_dest_id`),
  KEY `wifi_terminal_dest_id_fk` (`wifi_terminal_dest_id`),
  KEY `workstation_dest_id_fk` (`workstation_dest_id`),
  KEY `router_src_id_fk` (`router_src_id`),
  KEY `router_dest_id_fk` (`router_dest_id`),
  KEY `network_switches_src_id_fk` (`network_switch_src_id`),
  KEY `network_switches_dest_id_fk` (`network_switch_dest_id`),
  KEY `logical_server_src_id_fk` (`logical_server_src_id`),
  KEY `logical_server_dest_id_fk` (`logical_server_dest_id`),
  KEY `physical_links_perimeter_id_index` (`perimeter_id`),
  KEY `physical_links_created_by_foreign` (`created_by`),
  KEY `physical_links_updated_by_foreign` (`updated_by`),
  KEY `physical_links_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `logical_server_dest_id_fk` FOREIGN KEY (`logical_server_dest_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `logical_server_src_id_fk` FOREIGN KEY (`logical_server_src_id`) REFERENCES `logical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `network_switch_dest_id_fk` FOREIGN KEY (`network_switch_dest_id`) REFERENCES `network_switches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `network_switch_src_id_fk` FOREIGN KEY (`network_switch_src_id`) REFERENCES `network_switches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `peripheral_dest_id_fk` FOREIGN KEY (`peripheral_dest_id`) REFERENCES `peripherals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `peripheral_src_id_fk` FOREIGN KEY (`peripheral_src_id`) REFERENCES `peripherals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `phone_dest_id_fk` FOREIGN KEY (`phone_dest_id`) REFERENCES `phones` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `phone_src_id_fk` FOREIGN KEY (`phone_src_id`) REFERENCES `phones` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_links_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_links_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_links_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `physical_links_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_router_dest_id_fk` FOREIGN KEY (`physical_router_dest_id`) REFERENCES `physical_routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_router_src_id_fk` FOREIGN KEY (`physical_router_src_id`) REFERENCES `physical_routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_security_device_dest_id_fk` FOREIGN KEY (`physical_security_device_dest_id`) REFERENCES `physical_security_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_security_device_src_id_fk` FOREIGN KEY (`physical_security_device_src_id`) REFERENCES `physical_security_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_server_dest_id_fk` FOREIGN KEY (`physical_server_dest_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_server_src_id_fk` FOREIGN KEY (`physical_server_src_id`) REFERENCES `physical_servers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_switch_dest_id_fk` FOREIGN KEY (`physical_switch_dest_id`) REFERENCES `physical_switches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `physical_switch_src_id_fk` FOREIGN KEY (`physical_switch_src_id`) REFERENCES `physical_switches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `router_dest_id_fk` FOREIGN KEY (`router_dest_id`) REFERENCES `routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `router_src_id_fk` FOREIGN KEY (`router_src_id`) REFERENCES `routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `storage_device_dest_id_fk` FOREIGN KEY (`storage_device_dest_id`) REFERENCES `storage_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `storage_device_src_id_fk` FOREIGN KEY (`storage_device_src_id`) REFERENCES `storage_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wifi_terminal_dest_id_fk` FOREIGN KEY (`wifi_terminal_dest_id`) REFERENCES `wifi_terminals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `wifi_terminal_src_id_fk` FOREIGN KEY (`wifi_terminal_src_id`) REFERENCES `wifi_terminals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `workstation_dest_id_fk` FOREIGN KEY (`workstation_dest_id`) REFERENCES `workstations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `workstation_src_id_fk` FOREIGN KEY (`workstation_src_id`) REFERENCES `workstations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_router_router`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_router_router` (
  `router_id` int(10) unsigned NOT NULL,
  `physical_router_id` int(10) unsigned NOT NULL,
  KEY `router_id_fk_958343` (`router_id`),
  KEY `physical_router_id_fk_124983` (`physical_router_id`),
  CONSTRAINT `physical_router_id_fk_124983` FOREIGN KEY (`physical_router_id`) REFERENCES `physical_routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `router_id_fk_958343` FOREIGN KEY (`router_id`) REFERENCES `routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_router_vlan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_router_vlan` (
  `physical_router_id` int(10) unsigned NOT NULL,
  `vlan_id` int(10) unsigned NOT NULL,
  KEY `physical_router_id_fk_1658250` (`physical_router_id`),
  KEY `vlan_id_fk_1658250` (`vlan_id`),
  CONSTRAINT `physical_router_id_fk_1658250` FOREIGN KEY (`physical_router_id`) REFERENCES `physical_routers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `vlan_id_fk_1658250` FOREIGN KEY (`vlan_id`) REFERENCES `vlans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_routers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_routers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `bay_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485497` (`site_id`),
  KEY `building_fk_1485498` (`building_id`),
  KEY `bay_fk_1485499` (`bay_id`),
  KEY `physical_routers_perimeter_id_index` (`perimeter_id`),
  KEY `physical_routers_name_index` (`name`),
  KEY `physical_routers_created_by_foreign` (`created_by`),
  KEY `physical_routers_updated_by_foreign` (`updated_by`),
  KEY `physical_routers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bay_fk_1485499` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `building_fk_1485498` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `physical_routers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_routers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_routers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `physical_routers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `site_fk_1485497` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_security_device_security_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_security_device_security_device` (
  `security_device_id` int(10) unsigned NOT NULL,
  `physical_security_device_id` int(10) unsigned NOT NULL,
  KEY `security_device_id_fk_43329392` (`security_device_id`),
  KEY `physical_security_device_id_fk_6549543` (`physical_security_device_id`),
  CONSTRAINT `physical_security_device_id_fk_6549543` FOREIGN KEY (`physical_security_device_id`) REFERENCES `physical_security_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `security_device_id_fk_43329392` FOREIGN KEY (`security_device_id`) REFERENCES `security_devices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_security_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_security_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `bay_id` int(10) unsigned DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485517` (`site_id`),
  KEY `building_fk_1485518` (`building_id`),
  KEY `bay_fk_1485519` (`bay_id`),
  KEY `document_id_fk_493827312` (`icon_id`),
  KEY `physical_security_devices_perimeter_id_index` (`perimeter_id`),
  KEY `physical_security_devices_name_index` (`name`),
  KEY `physical_security_devices_created_by_foreign` (`created_by`),
  KEY `physical_security_devices_updated_by_foreign` (`updated_by`),
  KEY `physical_security_devices_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bay_fk_1485519` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `building_fk_1485518` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_493827312` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `physical_security_devices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_security_devices_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_security_devices_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `physical_security_devices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `site_fk_1485517` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `configuration` longtext DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `bay_id` int(10) unsigned DEFAULT NULL,
  `physical_switch_id` int(10) unsigned DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `memory` varchar(255) DEFAULT NULL,
  `disk` varchar(255) DEFAULT NULL,
  `disk_used` varchar(255) DEFAULT NULL,
  `operating_system` varchar(255) DEFAULT NULL,
  `install_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `patching_group` varchar(255) DEFAULT NULL,
  `patching_frequency` int(11) DEFAULT NULL,
  `next_update` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485322` (`site_id`),
  KEY `building_fk_1485323` (`building_id`),
  KEY `bay_fk_1485324` (`bay_id`),
  KEY `physical_switch_fk_8732342` (`physical_switch_id`),
  KEY `document_id_fk_5328384` (`icon_id`),
  KEY `physical_servers_perimeter_id_index` (`perimeter_id`),
  KEY `physical_servers_name_index` (`name`),
  KEY `physical_servers_created_by_foreign` (`created_by`),
  KEY `physical_servers_updated_by_foreign` (`updated_by`),
  KEY `physical_servers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bay_fk_1485324` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `building_fk_1485323` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_5328384` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `physical_servers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_servers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_servers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `physical_servers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_switch_fk_8732342` FOREIGN KEY (`physical_switch_id`) REFERENCES `physical_switches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `site_fk_1485322` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `physical_switches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `physical_switches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `bay_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485488` (`site_id`),
  KEY `building_fk_1485489` (`building_id`),
  KEY `bay_fk_1485493` (`bay_id`),
  KEY `physical_switches_icon_id_foreign` (`icon_id`),
  KEY `physical_switches_perimeter_id_index` (`perimeter_id`),
  KEY `physical_switches_name_index` (`name`),
  KEY `physical_switches_created_by_foreign` (`created_by`),
  KEY `physical_switches_updated_by_foreign` (`updated_by`),
  KEY `physical_switches_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bay_fk_1485493` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `building_fk_1485489` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `physical_switches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_switches_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `physical_switches_icon_id_foreign` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `physical_switches_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `physical_switches_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `site_fk_1485488` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `processes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `in_out` longtext DEFAULT NULL,
  `macroprocess_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_fk_4342342` (`macroprocess_id`),
  KEY `document_id_fk_5938654` (`icon_id`),
  KEY `processes_perimeter_id_index` (`perimeter_id`),
  KEY `processes_name_index` (`name`),
  KEY `processes_created_by_foreign` (`created_by`),
  KEY `processes_updated_by_foreign` (`updated_by`),
  KEY `processes_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_id_fk_5938654` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `processes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `processes_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `processes_ibfk_1` FOREIGN KEY (`macroprocess_id`) REFERENCES `macro_processuses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `processes_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `processes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `relation_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relation_values` (
  `relation_id` int(10) unsigned NOT NULL,
  `date_price` date DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `relation_id_fk_43243244` (`relation_id`),
  CONSTRAINT `relation_id_fk_43243244` FOREIGN KEY (`relation_id`) REFERENCES `relations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `importance` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `source_id` int(10) unsigned NOT NULL,
  `destination_id` int(10) unsigned NOT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `responsible` varchar(255) DEFAULT NULL,
  `order_number` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `security_need_c` int(11) DEFAULT NULL,
  `security_need_i` int(11) DEFAULT NULL,
  `security_need_a` int(11) DEFAULT NULL,
  `security_need_t` int(11) DEFAULT NULL,
  `security_need_auth` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `source_fk_1494372` (`source_id`),
  KEY `destination_fk_1494373` (`destination_id`),
  KEY `relations_perimeter_id_index` (`perimeter_id`),
  KEY `relations_name_index` (`name`),
  KEY `relations_created_by_foreign` (`created_by`),
  KEY `relations_updated_by_foreign` (`updated_by`),
  KEY `relations_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `destination_fk_1494373` FOREIGN KEY (`destination_id`) REFERENCES `entities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `relations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `relations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `relations_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `relations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `source_fk_1494372` FOREIGN KEY (`source_id`) REFERENCES `entities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  KEY `user_id_fk_1470803` (`user_id`),
  KEY `role_id_fk_1470803` (`role_id`),
  CONSTRAINT `role_id_fk_1470803` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `user_id_fk_1470803` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roles_perimeter_id_index` (`perimeter_id`),
  CONSTRAINT `roles_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `routers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `routers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `rules` longtext DEFAULT NULL,
  `ip_addresses` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `routers_perimeter_id_index` (`perimeter_id`),
  KEY `routers_name_index` (`name`),
  KEY `routers_created_by_foreign` (`created_by`),
  KEY `routers_updated_by_foreign` (`updated_by`),
  KEY `routers_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `routers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `routers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `routers_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `routers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `saved_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `saved_queries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `query` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`query`)),
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saved_queries_user_id_is_public_index` (`user_id`,`is_public`),
  KEY `saved_queries_name_index` (`name`),
  CONSTRAINT `saved_queries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `security_control_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_control_process` (
  `security_control_id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  KEY `security_control_id_fk_54354353` (`security_control_id`),
  KEY `process_id_fk_5837573` (`process_id`),
  CONSTRAINT `process_id_fk_49485754` FOREIGN KEY (`process_id`) REFERENCES `processes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `security_control_id_fk_54354354` FOREIGN KEY (`security_control_id`) REFERENCES `security_controls` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `security_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_controls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `security_controls_perimeter_id_index` (`perimeter_id`),
  KEY `security_controls_name_index` (`name`),
  KEY `security_controls_created_by_foreign` (`created_by`),
  KEY `security_controls_updated_by_foreign` (`updated_by`),
  KEY `security_controls_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `security_controls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_controls_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_controls_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `security_controls_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `security_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id_fk_432938439` (`icon_id`),
  KEY `security_devices_perimeter_id_index` (`perimeter_id`),
  KEY `security_devices_name_index` (`name`),
  KEY `security_devices_created_by_foreign` (`created_by`),
  KEY `security_devices_updated_by_foreign` (`updated_by`),
  KEY `security_devices_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_id_fk_43948313` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `security_devices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_devices_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_devices_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `security_devices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id_fk_129485` (`icon_id`),
  KEY `sites_perimeter_id_index` (`perimeter_id`),
  KEY `sites_name_index` (`name`),
  KEY `sites_created_by_foreign` (`created_by`),
  KEY `sites_updated_by_foreign` (`updated_by`),
  KEY `sites_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_id_fk_129485` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `sites_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sites_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sites_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `sites_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `storage_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `storage_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `bay_id` int(10) unsigned DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485361` (`site_id`),
  KEY `building_fk_1485362` (`building_id`),
  KEY `bay_fk_1485363` (`bay_id`),
  KEY `document_id_fk_53812733` (`icon_id`),
  KEY `storage_devices_perimeter_id_index` (`perimeter_id`),
  KEY `storage_devices_name_index` (`name`),
  KEY `storage_devices_created_by_foreign` (`created_by`),
  KEY `storage_devices_updated_by_foreign` (`updated_by`),
  KEY `storage_devices_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `bay_fk_1485363` FOREIGN KEY (`bay_id`) REFERENCES `bays` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `building_fk_1485362` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_53812733` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `site_fk_1485361` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `storage_devices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `storage_devices_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `storage_devices_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `storage_devices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subnetworks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subnetworks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `ip_allocation_type` varchar(255) DEFAULT NULL,
  `responsible_exp` varchar(255) DEFAULT NULL,
  `dmz` varchar(255) DEFAULT NULL,
  `wifi` varchar(255) DEFAULT NULL,
  `connected_subnets_id` int(10) unsigned DEFAULT NULL,
  `gateway_id` int(10) unsigned DEFAULT NULL,
  `zone` varchar(255) DEFAULT NULL,
  `vlan_id` int(10) unsigned DEFAULT NULL,
  `network_id` int(10) unsigned DEFAULT NULL,
  `subnetwork_id` int(10) unsigned DEFAULT NULL,
  `default_gateway` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `connected_subnets_fk_1483256` (`connected_subnets_id`),
  KEY `gateway_fk_1492376` (`gateway_id`),
  KEY `vlan_fk_6844934` (`vlan_id`),
  KEY `network_fk_5476544` (`network_id`),
  KEY `subnetworks_subnetwork_id_index` (`subnetwork_id`),
  KEY `subnetworks_perimeter_id_index` (`perimeter_id`),
  KEY `subnetworks_name_index` (`name`),
  KEY `subnetworks_created_by_foreign` (`created_by`),
  KEY `subnetworks_updated_by_foreign` (`updated_by`),
  KEY `subnetworks_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `connected_subnets_fk_1483256` FOREIGN KEY (`connected_subnets_id`) REFERENCES `subnetworks` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gateway_fk_1492376` FOREIGN KEY (`gateway_id`) REFERENCES `gateways` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `network_fk_5476544` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `subnetworks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subnetworks_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subnetworks_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `subnetworks_subnetwork_id_foreign` FOREIGN KEY (`subnetwork_id`) REFERENCES `subnetworks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subnetworks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vlan_fk_6844934` FOREIGN KEY (`vlan_id`) REFERENCES `vlans` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_perimeter_id_index` (`perimeter_id`),
  KEY `tasks_name_index` (`name`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `tasks_updated_by_foreign` (`updated_by`),
  KEY `tasks_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `tasks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `granularity` int(11) DEFAULT NULL,
  `language` varchar(2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `flow_label` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vlans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `vlan_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vlans_perimeter_id_index` (`perimeter_id`),
  KEY `vlans_name_index` (`name`),
  KEY `vlans_created_by_foreign` (`created_by`),
  KEY `vlans_updated_by_foreign` (`updated_by`),
  KEY `vlans_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `vlans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vlans_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vlans_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `vlans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wans_perimeter_id_index` (`perimeter_id`),
  KEY `wans_name_index` (`name`),
  KEY `wans_created_by_foreign` (`created_by`),
  KEY `wans_updated_by_foreign` (`updated_by`),
  KEY `wans_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `wans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wans_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wans_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `wans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wifi_terminals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wifi_terminals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485507` (`site_id`),
  KEY `building_fk_1485508` (`building_id`),
  KEY `wifi_terminals_perimeter_id_index` (`perimeter_id`),
  KEY `wifi_terminals_name_index` (`name`),
  KEY `wifi_terminals_created_by_foreign` (`created_by`),
  KEY `wifi_terminals_updated_by_foreign` (`updated_by`),
  KEY `wifi_terminals_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `building_fk_1485508` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `site_fk_1485507` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `wifi_terminals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wifi_terminals_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wifi_terminals_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `wifi_terminals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workstations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `workstations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `site_id` int(10) unsigned DEFAULT NULL,
  `building_id` int(10) unsigned DEFAULT NULL,
  `physical_switch_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `icon_id` int(10) unsigned DEFAULT NULL,
  `operating_system` varchar(255) DEFAULT NULL,
  `address_ip` varchar(255) DEFAULT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `memory` varchar(255) DEFAULT NULL,
  `disk` int(11) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `other_user` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `last_inventory_date` date DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `domain_id` int(10) unsigned DEFAULT NULL,
  `warranty` varchar(255) DEFAULT NULL,
  `warranty_start_date` date DEFAULT NULL,
  `warranty_period` varchar(255) DEFAULT NULL,
  `agent_version` varchar(255) DEFAULT NULL,
  `update_source` varchar(255) DEFAULT NULL,
  `network_id` int(10) unsigned DEFAULT NULL,
  `network_port_type` varchar(255) DEFAULT NULL,
  `mac_address` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `fin_value` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_fk_1485332` (`site_id`),
  KEY `building_fk_1485333` (`building_id`),
  KEY `physical_switch_fk_0938434` (`physical_switch_id`),
  KEY `document_id_fk_129483` (`icon_id`),
  KEY `workstations_entity_id_foreign` (`entity_id`),
  KEY `workstations_user_id_foreign` (`user_id`),
  KEY `workstations_domain_id_foreign` (`domain_id`),
  KEY `workstations_network_id_foreign` (`network_id`),
  KEY `workstations_perimeter_id_index` (`perimeter_id`),
  KEY `workstations_name_index` (`name`),
  KEY `workstations_created_by_foreign` (`created_by`),
  KEY `workstations_updated_by_foreign` (`updated_by`),
  KEY `workstations_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `building_fk_1485333` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `document_id_fk_129483` FOREIGN KEY (`icon_id`) REFERENCES `documents` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `physical_switch_fk_0938434` FOREIGN KEY (`physical_switch_id`) REFERENCES `physical_switches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `site_fk_1485332` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `workstations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workstations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workstations_domain_id_foreign` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`),
  CONSTRAINT `workstations_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`),
  CONSTRAINT `workstations_network_id_foreign` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`),
  CONSTRAINT `workstations_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `workstations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workstations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `zone_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `zone_admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zone_admins_perimeter_id_index` (`perimeter_id`),
  KEY `zone_admins_name_index` (`name`),
  KEY `zone_admins_created_by_foreign` (`created_by`),
  KEY `zone_admins_updated_by_foreign` (`updated_by`),
  KEY `zone_admins_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `zone_admins_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zone_admins_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zone_admins_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `zone_admins_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `zone_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `zone_zone` (
  `zone_id` int(10) unsigned NOT NULL,
  `related_zone_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`zone_id`,`related_zone_id`),
  KEY `zone_zone_related_zone_id_foreign` (`related_zone_id`),
  CONSTRAINT `zone_zone_related_zone_id_foreign` FOREIGN KEY (`related_zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `zone_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perimeter_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ext_refs` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `attributes` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zones_perimeter_id_index` (`perimeter_id`),
  KEY `zones_created_by_foreign` (`created_by`),
  KEY `zones_updated_by_foreign` (`updated_by`),
  KEY `zones_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `zones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zones_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zones_perimeter_id_foreign` FOREIGN KEY (`perimeter_id`) REFERENCES `perimeters` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `zones_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `rand_person` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`mercator_user`@`localhost` FUNCTION `rand_person`() RETURNS varchar(100) CHARSET utf8mb3 COLLATE utf8mb3_general_ci
    DETERMINISTIC
BEGIN
    DECLARE prenom VARCHAR(20);
    DECLARE nom    VARCHAR(20);
    SET prenom = ELT(1 + FLOOR(RAND() * 10),
        'Alice','Bruno','Claire','Denis','Emma',
        'Fabien','Gaëlle','Hugo','Inès','Jules');
    SET nom = ELT(1 + FLOOR(RAND() * 10),
        'Martin','Bernard','Dupont','Moreau','Simon',
        'Laurent','Lefebvre','Michel','Garcia','Roux');
    RETURN CONCAT(prenom, ' ', nom);
END
;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `rand_short` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`mercator_user`@`localhost` FUNCTION `rand_short`() RETURNS varchar(64) CHARSET utf8mb3 COLLATE utf8mb3_general_ci
    DETERMINISTIC
BEGIN
    RETURN CONCAT(SUBSTRING(MD5(RAND()),1,8), '-', SUBSTRING(MD5(RAND()),1,8));
END
;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `rand_text` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`mercator_user`@`localhost` FUNCTION `rand_text`() RETURNS text CHARSET utf8mb3 COLLATE utf8mb3_general_ci
    DETERMINISTIC
BEGIN
    RETURN CONCAT(
        'Lorem ', SUBSTRING(MD5(RAND()),1,8), ' ipsum ',   SUBSTRING(MD5(RAND()),1,12), ' dolor sit amet. ',
        'Sed ',   SUBSTRING(MD5(RAND()),1,6), ' ut ',      SUBSTRING(MD5(RAND()),1,10), ' consequat arcu. ',
        'Nulla ', SUBSTRING(MD5(RAND()),1,8), ' finibus ', SUBSTRING(MD5(RAND()),1,8),  ' purus eu felis.'
    );
END
;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `rand_word` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`mercator_user`@`localhost` FUNCTION `rand_word`(n INT) RETURNS varchar(255) CHARSET utf8mb3 COLLATE utf8mb3_general_ci
    DETERMINISTIC
BEGIN
    RETURN SUBSTRING(MD5(RAND()), 1, n);
END
;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2021_05_08_191249_create_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2021_05_08_191249_create_activity_operation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2021_05_08_191249_create_activity_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2021_05_08_191249_create_actor_operation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2021_05_08_191249_create_actors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2021_05_08_191249_create_annuaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2021_05_08_191249_create_application_blocks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2021_05_08_191249_create_application_module_application_service_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2021_05_08_191249_create_application_modules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2021_05_08_191249_create_application_service_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2021_05_08_191249_create_application_services_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2021_05_08_191249_create_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2021_05_08_191249_create_bay_wifi_terminal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2021_05_08_191249_create_bays_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2021_05_08_191249_create_buildings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2021_05_08_191249_create_database_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2021_05_08_191249_create_database_information_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2021_05_08_191249_create_database_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2021_05_08_191249_create_databases_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2021_05_08_191249_create_dhcp_servers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2021_05_08_191249_create_dnsservers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2021_05_08_191249_create_domaine_ad_forest_ad_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2021_05_08_191249_create_domaine_ads_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2021_05_08_191249_create_entities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2021_05_08_191249_create_entity_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2021_05_08_191249_create_entity_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2021_05_08_191249_create_external_connected_entities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2021_05_08_191249_create_external_connected_entity_network_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2021_05_08_191249_create_fluxes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2021_05_08_191249_create_forest_ads_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2021_05_08_191249_create_gateways_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2021_05_08_191249_create_information_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2021_05_08_191249_create_information_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2021_05_08_191249_create_lan_man_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2021_05_08_191249_create_lan_wan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2021_05_08_191249_create_lans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2021_05_08_191249_create_logical_server_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2021_05_08_191249_create_logical_server_physical_server_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2021_05_08_191249_create_logical_servers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2021_05_08_191249_create_m_application_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2021_05_08_191249_create_m_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2021_05_08_191249_create_macro_processuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2021_05_08_191249_create_man_wan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2021_05_08_191249_create_mans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2021_05_08_191249_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2021_05_08_191249_create_network_subnetword_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2021_05_08_191249_create_network_switches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2021_05_08_191249_create_networks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2021_05_08_191249_create_operation_task_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2021_05_08_191249_create_operations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2021_05_08_191249_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2021_05_08_191249_create_peripherals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2021_05_08_191249_create_permission_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2021_05_08_191249_create_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2021_05_08_191249_create_phones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2021_05_08_191249_create_physical_router_vlan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2021_05_08_191249_create_physical_routers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2021_05_08_191249_create_physical_security_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2021_05_08_191249_create_physical_servers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2021_05_08_191249_create_physical_switches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2021_05_08_191249_create_processes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2021_05_08_191249_create_relations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2021_05_08_191249_create_role_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2021_05_08_191249_create_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2021_05_08_191249_create_routers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2021_05_08_191249_create_security_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2021_05_08_191249_create_sites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2021_05_08_191249_create_storage_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2021_05_08_191249_create_subnetworks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2021_05_08_191249_create_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2021_05_08_191249_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2021_05_08_191249_create_vlans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2021_05_08_191249_create_wans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2021_05_08_191249_create_wifi_terminals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2021_05_08_191249_create_workstations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2021_05_08_191249_create_zone_admins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2021_05_08_191251_add_foreign_keys_to_activity_operation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2021_05_08_191251_add_foreign_keys_to_activity_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2021_05_08_191251_add_foreign_keys_to_actor_operation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2021_05_08_191251_add_foreign_keys_to_annuaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2021_05_08_191251_add_foreign_keys_to_application_module_application_service_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2021_05_08_191251_add_foreign_keys_to_application_service_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2021_05_08_191251_add_foreign_keys_to_bay_wifi_terminal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2021_05_08_191251_add_foreign_keys_to_bays_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2021_05_08_191251_add_foreign_keys_to_buildings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2021_05_08_191251_add_foreign_keys_to_database_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2021_05_08_191251_add_foreign_keys_to_database_information_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2021_05_08_191251_add_foreign_keys_to_database_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2021_05_08_191251_add_foreign_keys_to_databases_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2021_05_08_191251_add_foreign_keys_to_domaine_ad_forest_ad_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2021_05_08_191251_add_foreign_keys_to_entity_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2021_05_08_191251_add_foreign_keys_to_entity_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2021_05_08_191251_add_foreign_keys_to_external_connected_entity_network_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2021_05_08_191251_add_foreign_keys_to_fluxes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2021_05_08_191251_add_foreign_keys_to_forest_ads_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2021_05_08_191251_add_foreign_keys_to_information_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2021_05_08_191251_add_foreign_keys_to_lan_man_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2021_05_08_191251_add_foreign_keys_to_lan_wan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2021_05_08_191251_add_foreign_keys_to_logical_server_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2021_05_08_191251_add_foreign_keys_to_logical_server_physical_server_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2021_05_08_191251_add_foreign_keys_to_m_application_process_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2021_05_08_191251_add_foreign_keys_to_m_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2021_05_08_191251_add_foreign_keys_to_man_wan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2021_05_08_191251_add_foreign_keys_to_network_subnetword_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2021_05_08_191251_add_foreign_keys_to_operation_task_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2021_05_08_191251_add_foreign_keys_to_peripherals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2021_05_08_191251_add_foreign_keys_to_permission_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2021_05_08_191251_add_foreign_keys_to_phones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2021_05_08_191251_add_foreign_keys_to_physical_router_vlan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2021_05_08_191251_add_foreign_keys_to_physical_routers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2021_05_08_191251_add_foreign_keys_to_physical_security_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2021_05_08_191251_add_foreign_keys_to_physical_servers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2021_05_08_191251_add_foreign_keys_to_physical_switches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2021_05_08_191251_add_foreign_keys_to_processes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2021_05_08_191251_add_foreign_keys_to_relations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2021_05_08_191251_add_foreign_keys_to_role_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2021_05_08_191251_add_foreign_keys_to_storage_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2021_05_08_191251_add_foreign_keys_to_subnetworks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2021_05_08_191251_add_foreign_keys_to_wifi_terminals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2021_05_08_191251_add_foreign_keys_to_workstations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2021_05_13_180642_add_cidt_criteria',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2021_05_19_161123_rename_subnetwork',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2021_06_22_170555_add_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2021_07_14_071311_create_certificates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2021_08_08_125856_config_right',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2021_08_11_201624_certificate_application_link',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2021_08_18_171048_network_redesign',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2021_08_20_034757_default_gateway',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2021_08_28_152910_cleanup',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2021_09_19_125048_relation-inportance',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2021_09_21_161028_add_router_ip',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2021_09_22_114706_add_security_ciat',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2021_09_23_192127_rename_descrition',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2021_09_28_205405_add_direction_to_flows',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2021_10_12_210233_physical_router_name_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2021_10_19_102610_add_address_ip',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2021_11_23_204551_add_app_version',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2022_02_08_210603_create_cartographer_m_application_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2022_02_22_32654_add_cert_status',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2022_02_27_162738_add_functional_referent_to_m_application',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2022_02_27_163129_add_editor_to_m_application',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2022_02_27_192155_add_date_fields_to_m_application',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2022_02_28_205630_create_m_application_event_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2022_05_02_123756_add_update_to_logical_servers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2022_05_18_140331_add_is_external_column_to_entities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2022_05_21_103208_add_type_property_to_entities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2022_06_27_061444_application_workstation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2022_07_28_105153_add_link_operation_process',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2022_08_11_165441_add_vpn_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2022_09_13_204845_cert_last_notification',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2022_12_17_115624_rto_rpo',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2023_01_03_205224_database_logical_server',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2023_01_08_123726_add_physical_link',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2023_01_27_165009_add_flux_nature',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2023_01_28_145242_add_logical_devices_link',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2023_02_09_164940_gdpr',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2023_03_16_123031_create_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2023_03_22_185812_create_cpe',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2023_04_18_123308_add_gdpr_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2023_05_29_161406_security_controls_links',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2023_06_14_120958_add_physical_address_ip',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2023_08_06_100128_add_physicalserver_size',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2023_08_07_183714_application_physical_server',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2023_09_04_111440_add_application_patching',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2023_09_26_074104_iot',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2023_10_28_124418_add_cluster',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2023_11_30_070804_fix_migration_typo',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2024_02_21_085107_application_patching',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2024_02_29_134239_patching_attributes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2024_03_14_165211_router_ip_lenght',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2024_03_19_195927_contracts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2024_04_06_161307_nomalization',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2024_04_08_105719_network_flow',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2024_04_14_072101_add_parent_entity',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2024_04_28_075916_normalize_process_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2024_05_09_180526_improve_network_flow',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2024_05_15_212326_routers_log_phys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2024_06_03_165236_add_user',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2024_06_11_060639_external_connnected_entities_desc',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2024_06_18_125723_link_domain_lservers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2024_08_27_200851_normalize_storage_devices',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2024_09_22_112404_add_icon',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2024_09_24_044657_move_icons_to_docs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2024_09_24_084005_move_icons_to_docs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2024_09_26_160952_building_attributes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2024_10_31_220940_add_vlan_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2024_11_13_183902_add_type_to_logical_servers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2024_11_26_130914_add_authenticity',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2025_01_03_130604_create_graphs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2025_01_10_123601_logical_server_disabled',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2025_01_16_120601_add_icon_to_process',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2025_01_17_133444_add_table_containers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2025_02_18_073549_application_assignation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2025_03_17_094654_datetime_to_date',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2025_03_24_132409_remove_unique_name_deleted_at_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2025_03_26_133906_external_nullable',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2025_04_27_084635_add_icon_to_servers',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2025_04_27_123200_add_legal_basis',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2025_06_19_072710_add_databases_to_containers',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2025_06_28_155312_add_glpi_fields',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2025_07_01_065432_admin_user_fields',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2025_07_02_123433_rename_permission',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2025_07_07_070846_add_admin_user_application',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2025_07_08_065626_add_references_to_logical_flows',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2025_07_17_145227_activities_bia',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2025_07_17_150249_create_activity_impact_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2025_08_23_111003_add_pra',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2025_08_27_135552_physical_logical_security_devices',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2025_09_05_141822_add_external_ref_id_to_entities',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2025_09_21_134035_add_cluster_router',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2025_09_23_113715_add_user_login',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2025_09_29_193233_external_connected_entities_complement',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2025_10_02_205209_add_permissions',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2025_10_04_153231_add_attribut_on_fluxes',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2025_10_08_100558_more_buildings',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2025_10_17_073218_add_cluster_logical_server',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2025_10_17_085635_add_fields_to_cluster',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2025_10_18_174616_add_cluster_physical_server',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2025_10_18_183453_add_cluster_router',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2025_10_21_104026_add_lawfullness',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2025_10_27_095802_add_link_applications_security_devices',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2025_10_27_104704_add_fields_security_device',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2025_10_30_081134_change_active_nullable_on_lservers',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2025_11_01_123622_drop_unique_graphs_name_unique_on_graphs_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2025_11_09_103559_add_network_switch_vlan',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2025_11_10_090632_add_subnetwork_link',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2025_11_17_110452_add_graph_class',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2025_11_21_123505_add_chain_to_logical_flow',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2025_11_24_152357_create_mercator_modules_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2025_11_30_123016_add_module_to_permissions_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2025_12_03_105527_add_ip_to_security_devices',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2025_12_03_111641_add_security_device_to_logical_flows',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2025_12_04_135457_add_manage_module_role',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2026_01_16_114842_add_icon_to_databases',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2026_01_24_091900_add_information_flow_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2026_02_13_134714_add_cpe_to_application_modules',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2026_02_18_085500_add_type_color_to_physical_links',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (287,'2016_06_01_000001_create_oauth_auth_codes_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (288,'2016_06_01_000002_create_oauth_access_tokens_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (289,'2016_06_01_000003_create_oauth_refresh_tokens_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (290,'2016_06_01_000004_create_oauth_clients_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (291,'2024_06_01_000001_create_oauth_device_codes_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (298,'2026_03_02_000001_upgrade_oauth_clients_uuid',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (299,'2026_03_03_184523_add_parent_man',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (300,'2026_03_05_114042_add_icon_to_physical_switches',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (303,'2026_03_11_134833_add_cluster_to_logical_flows',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (305,'2026_03_11_175208_add_information_information',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (307,'2026_03_22_121031_add_data_processing_fields',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (309,'2026_03_30_095429_add_document_permissions',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (315,'2026_04_05_180615_add_table_backups',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (319,'2026_04_15_100554_add_application_module_entity',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (325,'2026_04_17_211419_add_query_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (326,'2026_05_03_184011_rename_m_applications_to_applications',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (327,'2026_05_03_185943_rename_m_application_events_to_application_events',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (328,'2026_05_03_192017_rename_m_application_id_in_application_events',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (329,'2026_05_03_193002_rename_m_application_permissions',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (330,'2026_05_03_230714_consolidate_application_permissions',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (331,'2026_05_03_234356_drop_application_cartographer_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (332,'2026_05_07_215942_rename_fluxes_to_application_flows',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (333,'2026_05_08_120804_rename_flux_information_to_application_flow_information',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (336,'2026_05_08_182110_rename_domaine_ads_to_domaines',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (337,'2026_05_08_200000_rename_domaines_to_domains',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (340,'2026_05_08_212557_rename_domaines_to_domains',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (341,'2026_05_11_000001_create_parameters_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (342,'2026_05_12_113635_drop_unique_name_from_annuaires',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (343,'2026_05_12_150000_create_zones_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (344,'2026_05_12_150001_add_zone_permissions',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (345,'2026_05_20_000000_restructure_backups_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (348,'2026_05_31_000000_create_cartographers_table',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (349,'2026_05_31_000001_rename_irregular_permissions',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (350,'2026_06_04_090735_create_jobs_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (351,'2026_06_07_094101_add_soft_deletes_to_cartographers_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (352,'2026_06_11_000000_create_application_document_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (353,'2026_06_18_000000_add_ext_refs_to_mapped_objects',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (354,'2026_06_21_000000_move_mercator_config_to_parameters',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (355,'2026_06_22_000000_rename_room_id_to_building_id_in_bays',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (356,'2026_06_22_000001_add_type_and_attributes_columns',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (357,'2026_06_22_000002_add_description_and_attributes_to_physical_links',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (358,'2026_06_28_000000_drop_unique_name_from_backups_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (359,'2019_12_14_000001_create_personal_access_tokens_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (360,'2026_07_05_000000_change_peripheral_domain_to_domain_id',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (361,'2026_07_18_000000_create_monarc_sync_items_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (362,'2026_07_24_000000_add_status_hosting_fields_to_applications',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (363,'2026_08_04_000000_add_application_id_to_annuaires',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (364,'2026_08_04_000000_fix_physical_servers_patching_typo',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (365,'2026_08_11_000000_add_cairn_access_permission',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (366,'2026_09_02_000000_add_flow_label_to_users_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (367,'2026_09_08_000000_add_icon_to_storage_devices',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (378,'2026_09_09_000000_add_type_attributes_to_cartography_objects',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (379,'2026_09_09_000001_rename_nature_to_type_on_application_flows',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (380,'2026_09_10_000000_rename_entity_type_to_type_on_entities',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (381,'2026_09_10_000001_move_is_external_to_attributes_on_entities',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (382,'2026_09_14_100000_create_perimeters_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (383,'2026_09_14_100001_add_perimeter_id_to_roles_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (384,'2026_09_15_000000_add_perimeter_id_to_mapped_objects',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (385,'2026_09_16_000000_drop_unique_name_indexes_on_mapped_objects',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (386,'2026_09_16_000001_add_perimeter_id_to_admin_users_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (387,'2026_09_16_100000_add_name_indexes_to_frequently_sorted_tables',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (388,'2026_09_15_113000_add_comments_to_applications',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (389,'2026_09_21_000000_rename_nom_to_name_in_perimeters_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (390,'2026_09_24_000000_add_comments_to_application_services',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (391,'2026_09_24_000001_add_comments_to_application_modules',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (392,'2026_10_10_000000_sanitize_stored_html_fields',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (393,'2026_10_28_000000_add_icon_to_information',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (394,'2026_11_06_000000_add_blame_columns_to_audited_tables',53);
