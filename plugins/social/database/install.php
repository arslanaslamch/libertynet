<?php
function social_install_database() {
	$db = db();
	$db->query("CREATE TABLE IF NOT EXISTS `social_imports` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `avatar` text COLLATE utf8_unicode_ci NOT NULL,
	  `user_id` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
}