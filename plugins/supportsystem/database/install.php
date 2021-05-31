<?php

function supportsystem_install_database() {
    db()->query("CREATE TABLE IF NOT EXISTS `support_system_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `category_order` int(11) NOT NULL,
  `parent_id` tinyint(50) NOT NULL DEFAULT '0',
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    db()->query("CREATE TABLE IF NOT EXISTS `suppport_system_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` tinyint(50) NOT NULL DEFAULT '0',
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `update_time` int(11) NOT NULL,
  `rp_yes` int(11) NOT NULL,
  `rp_no` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    db()->query("CREATE TABLE IF NOT EXISTS `suppport_system_moderators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` tinyint(50) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    db()->query("CREATE TABLE IF NOT EXISTS `suppport_system_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` tinyint(50) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `files` text COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `ticket_id` tinyint(50) NOT NULL DEFAULT '0',
  `owner` tinyint(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    db()->query("CREATE TABLE IF NOT EXISTS `suppport_system_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entity_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(11) NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `files` text COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `views` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '1',
  `update_time` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


}