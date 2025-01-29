# ************************************************************
# Sequel Pro SQL dump
# Versión 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: donorsfirstcrm.com (MySQL 5.5.5-10.2.6-MariaDB-10.2.6+maria~xenial-log)
# Base de datos: missionpillars_dev_qa
# Tiempo de Generación: 2018-01-15 18:37:17 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla settings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'NULL',
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `settings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;

INSERT INTO `settings` (`id`, `tenant_id`, `key`, `class_name`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `created_by_session_id`, `updated_by_session_id`)
VALUES
	(1,NULL,'PLEDGE_EMAIL_REMINDER_SWITCH','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(2,NULL,'PLEDGE_EMAIL_REMINDER_TEXT_EVERY','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(3,NULL,'PLEDGE_EMAIL_REMINDER_TEXT_STARTING','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(4,NULL,'PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(5,NULL,'PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(6,NULL,'PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_CONTACT','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(7,NULL,'PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN','App\\Models\\Pledge',NULL,'2018-01-15 00:00:03',NULL,NULL,NULL,NULL,NULL);

/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
