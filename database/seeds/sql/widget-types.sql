# ************************************************************
# Sequel Pro SQL dump
# Versión 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: donorsfirstcrm.com (MySQL 5.5.5-10.2.6-MariaDB-10.2.6+maria~xenial-log)
# Base de datos: missionpillars_dev_qa
# Tiempo de Generación: 2018-01-15 18:35:57 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla widget_types
# ------------------------------------------------------------

DROP TABLE IF EXISTS `widget_types`;

CREATE TABLE `widget_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parameters` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'col-sm-12',
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `type` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `widget_types_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `widget_types_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `widget_types` WRITE;
/*!40000 ALTER TABLE `widget_types` DISABLE KEYS */;

INSERT INTO `widget_types` (`id`, `tenant_id`, `name`, `description`, `parameters`, `size`, `created_by`, `updated_by`, `created_by_session_id`, `updated_by_session_id`, `created_at`, `updated_at`, `deleted_at`, `type`)
VALUES
	(1,NULL,'Welcome Widget','Your dashboard can be customized so you can see what you want, when you want. Widgets can be easily added, moved, or removed.',NULL,'col-sm-12 grid-item-width-12 grid-item-height-4',NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:04',NULL,'welcome'),
	(2,NULL,'Fundraising KPIs','Displays fundraising key performance indicators, as recommended by industry experts.','{\"name\":\"Fundraising KPIs\",\"description\":\"Displays fundraising key performance indicators, as recommended by industry experts.\",\"type\":\"kpis\",\"measurement\":\"$\",\"calculate\":\"average\",\"what\":\"giving\",\"period\":\"current_year\",\"from\":null,\"to\":null,\"group_by\":\"years\",\"filter\":\"none\",\"include_last_year\":\"1\",\"slug\":\"fundraising_kpis\",\"metric\":{\"type\":\"fundraising_kpis\"},\"options\":{\"checkboxes\":{\"average_annual_giving_donor\":\"true\",\"average_gift\":\"true\",\"donors_in_database\":\"true\",\"donors_retention_rate\":\"true\",\"donor_attrition_rate\":\"true\",\"donor_participation_rate\":\"true\"}}}','col-sm-6 grid-item-width-6 grid-item-height-5',NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:04',NULL,'kpis'),
	(3,NULL,'Calendar','Display a calendar events','{\n	\"id\": \"1\"\n}','col-sm-6 grid-item-width-6 grid-item-height-7',NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:04',NULL,'calendar') 
	-- , (4,NULL,'Next Month Incoming','Helps to forcast what money will come in over a period of time one month in advace.','{}','col-sm-6 grid-item-width-6 grid-item-height-5',NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:04',NULL,'incoming-money')
    ;

/*!40000 ALTER TABLE `widget_types` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
