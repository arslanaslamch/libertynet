<?php
function like_upgrade_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS  `ratings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
	 `user_id` int(11) unsigned NOT NULL,
	 `type` varchar (100) NOT NULL,
	 `type_id` int(11) unsigned NOT NULL,
	 `star_1` int(11) unsigned NOT NULL,
	 `star_2` int(11) unsigned NOT NULL,
	 `star_3` int(11) unsigned NOT NULL,
	 `star_4` int(11) unsigned NOT NULL,
	 `star_5` int(11) unsigned NOT NULL,
	 PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
}