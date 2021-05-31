<?php

function birthday_upgrade_database() {
    /*register_site_page('birthdays', array('title' => 'birthday::birthdays', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'birthdays', 'content', 'middle');
        Widget::add(null, 'birthdays', 'plugin::birthday|months', 'left');
        Widget::add(null, 'birthdays', 'plugin::birthday|todaysbirthday', 'left');
        Widget::add(null, 'profile', 'plugin::birthday|profilebirthday', 'left');
    });*/

    db()->query("CREATE TABLE IF NOT EXISTS `birthday_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci NOT NULL,
  `admin` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

}