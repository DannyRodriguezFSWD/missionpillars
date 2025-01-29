# ************************************************************
# Sequel Pro SQL dump
# Versión 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: donorsfirstcrm.com (MySQL 5.5.5-10.2.6-MariaDB-10.2.6+maria~xenial-log)
# Base de datos: missionpillars_dev_qa
# Tiempo de Generación: 2018-01-15 18:36:34 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla charts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `charts`;

CREATE TABLE `charts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `measurement` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `calculate` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sum',
  `what` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'giving',
  `period` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'current_year',
  `from` timestamp NULL DEFAULT NULL,
  `to` timestamp NULL DEFAULT NULL,
  `group_by` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'months',
  `filter` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `include_last_year` smallint(6) NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'chart',
  PRIMARY KEY (`id`),
  KEY `charts_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `charts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `charts` WRITE;
/*!40000 ALTER TABLE `charts` DISABLE KEYS */;

INSERT INTO `charts` (`id`, `tenant_id`, `name`, `description`, `type`, `measurement`, `calculate`, `what`, `period`, `from`, `to`, `group_by`, `filter`, `include_last_year`, `created_by`, `updated_by`, `created_by_session_id`, `updated_by_session_id`, `created_at`, `updated_at`, `deleted_at`, `slug`, `category`)
VALUES
	(1,NULL,'Fundraising Metrics Line Chart','Displays sum of all givings from this year','line.metric','$','sum','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'all_donations_current_year','chart'),
	(2,NULL,'Pie Chart','Displays a pie chart comparing two data points based on the selected metric, such as online vs. offline donations.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'online_vs_offline_donations','chart'),
	(3,NULL,'Average Gift Amount Line Chart','The total dollars received divided by the total number of gifts received.','line.metric','$','average','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'donations_average_current_year','chart'),
	(4,NULL,'Donation source by device','Displays a pie chart comparing devices used to make donations.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'device_category_donations','metric'),
	(5,NULL,'Donation source by application','Displays a pie chart comparing application used to make donations.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'transaction_type_donations','metric'),
	(6,NULL,'Online vs. offline Donations','Displays a pie chart comparing two data points based on the selected metric, such as online vs. offline donations.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'online_vs_offline_donations','metric'),
	(7,NULL,'Fundraising Metrics','Displays sum of all givings from this year','line.metric','$','sum','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'all_donations_current_year','metric'),
	(8,NULL,'Average Gift Amount','The total dollars received divided by the total number of gifts received.','line.metric','$','average','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'donations_average_current_year','metric'),
	(9,NULL,'Recurring vs one time donations','Displays a pie chart comparing two data points based on the selected metric, such as recurring vs one time donations.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'pie_recurring_vs_one_time_donations','metric'),
	(10,NULL,'Donation Status','Displays a pie chart comparing status fo donations.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'pie_status_donations','metric'),
	(11,NULL,'Credit Card Vs ACH Payment','Displays a pie chart comparing two data points based on the selected metric, such as Credit Card Vs ACH payments.','pie.metric','#','count','giving','current_year',NULL,NULL,'months','none',0,NULL,NULL,NULL,NULL,NULL,'2018-01-15 00:00:03',NULL,'pie_credit_card_vs_ach_payments','metric');

/*!40000 ALTER TABLE `charts` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
