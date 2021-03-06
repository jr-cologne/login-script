CREATE DATABASE IF NOT EXISTS `login-script`;
USE `login-script`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `token` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `google_id` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_init_password` tinyint(1) unsigned DEFAULT NULL,
  `twitter_id` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_init_password` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
