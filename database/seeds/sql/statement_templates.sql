# ************************************************************
# Sequel Pro SQL dump
# Versión 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: donorsfirstcrm.com (MySQL 5.5.5-10.2.6-MariaDB-10.2.6+maria~xenial-log)
# Base de datos: missionpillars_dev_demo
# Tiempo de Generación: 2018-01-16 16:49:48 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla statement_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `statement_templates`;

CREATE TABLE `statement_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `statement_templates_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `statement_templates_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `statement_templates` WRITE;
/*!40000 ALTER TABLE `statement_templates` DISABLE KEYS */;

INSERT INTO `statement_templates` (`id`, `tenant_id`, `name`, `content`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `created_by_session_id`, `updated_by_session_id`)
VALUES
	(1,NULL,'Standard','<p>[:name:]</p>\n<p>[:address:]</p>\n<p>Dear [:first_name:],</p>\n<p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We\'ve listed the contributions you\'ve made below:</p>\n<p>&nbsp;</p>\n<p>[:item_list:]</p>\n<p>&nbsp;</p>\n<p>Thanks again,&nbsp;</p>\n<p>[:organization_name:]</p>',NULL,'2018-01-16 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(2,NULL,'Window Envelopes','<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\">\n<tbody>\n<tr>\n<td style=\"width: 28.7942%;\">&nbsp;</td>\n<td style=\"width: 71.2058%;\">\n<p>[:name:]<br />[:address:]</p>\n</td>\n</tr>\n</tbody>\n</table>\n<p>[:name:]<br />[:address:]</p>\n<p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We\'ve listed the contributions you\'ve made below:</p>\n<p>&nbsp;</p>\n<p>[:item_list:]</p>\n<p>&nbsp;</p>\n<p>Sincerely, <br />[:organization_name:]</p>',NULL,'2018-01-16 00:00:03',NULL,NULL,NULL,NULL,NULL),
	(3,NULL,'Minimalist','<p>[:name:]<br />[:address:]</p>\n<p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.</p>\n<p>Sincerely, <br />[:organization_name:]</p>',NULL,'2018-01-16 00:00:03',NULL,NULL,NULL,NULL,NULL);

/*!40000 ALTER TABLE `statement_templates` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
