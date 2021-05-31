<?php
function giftshop_install_database() {
	$db = db();
	$db->query("CREATE TABLE IF NOT EXISTS `giftshop` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name`  varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	 `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `price`  int(11) unsigned NOT NULL,
	  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query("CREATE TABLE IF NOT EXISTS `giftshop_category` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	 `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	if($db->query("SELECT COUNT(id) FROM giftshop_category")->fetch_row()[0] == 0) {
		$preloaded_categories = array('Cards', 'Figurines');
		foreach($preloaded_categories as $preloaded_category) {
			$db->query("INSERT INTO giftshop_category(slug, category) VALUES('".slugger($preloaded_category)."', '".$preloaded_category."')");
		}
	}

	$db->query("CREATE TABLE IF NOT EXISTS `giftshop_mygift` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `sender_id`  int(11) unsigned NOT NULL,
	  `receiver`  int(11) unsigned NOT NULL,
	  `gift_id`  int(11) unsigned NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
}