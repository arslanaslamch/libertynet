<?php

function auction_install_database() {
	$db = db();

	$db->query("CREATE TABLE IF NOT EXISTS `auction_bid` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `auction_id` int(11) NOT NULL,
      `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `price` varchar(32) NOT NULL DEFAULT '0.00',
      `tmp_approve` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE IF NOT EXISTS `auction_cart` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `auction_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `price` varchar(32) NOT NULL DEFAULT '0.00',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE IF NOT EXISTS `auction_categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(200) NOT NULL,
      `description` text NOT NULL,
      `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	/** @var $title */
	if($db->query("SELECT COUNT(id) FROM auction_categories")->fetch_row()[0] == 0) {
		$preloaded_categories = array('Electronics', 'Home, Office, Garden');
		foreach($preloaded_categories as $preloaded_category) {
			foreach(get_all_languages() as $language) {
				$post_vars['title'][$language['language_id']] = $preloaded_category;
			}
			$expected = array('title' => '');
			extract(array_merge($expected, $post_vars));
			$titleSlug = 'auction_category_'.md5(time().serialize($post_vars)).'_title';
			foreach($title as $langId => $t) {
				add_language_phrase($titleSlug, $t, $langId, 'auction');
			}
			foreach($title as $langId => $t) {
				(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'auction') : add_language_phrase($titleSlug, $t, $langId, 'auction');
			}
			$db->query("INSERT INTO auction_categories(title) VALUES('".$titleSlug."')");
		}
	}

	$db->query("CREATE TABLE IF NOT EXISTS `auction_new` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(200) NOT NULL,
          `category_id` int(11) NOT NULL,
          `quantity` int(11) NOT NULL,
          `description` text NOT NULL,
          `picture` varchar(200) NOT NULL,
          `start_date` varchar(20) NOT NULL,
          `end_date` varchar(20) NOT NULL,
          `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `user_id` int(11) NOT NULL,
          `reserved_price` varchar(32) NOT NULL DEFAULT '0.00',
          `buy_price` varchar(32) NOT NULL DEFAULT '0.00',
          `view_count` int(11) NOT NULL DEFAULT '0',
          `current_bid` int(11) NOT NULL DEFAULT '0',
          `featured` int(11) DEFAULT '0',
          `status` int(11) DEFAULT '0',
          `country` varchar(50) NOT NULL,
          `city` varchar(50) NOT NULL,
          `state` varchar(50) NOT NULL,
          `ban` int(11) NOT NULL,
          `ship_details` text NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE IF NOT EXISTS `auction_offer` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `auction_id` int(11) NOT NULL,
          `price` varchar(32) NOT NULL DEFAULT '0.00',
          `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE IF NOT EXISTS `auction_temp` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `auction_id` int(11) NOT NULL,
      `qty` int(11) NOT NULL,
      `total` int(11) NOT NULL,
      `price` varchar(32) NOT NULL DEFAULT '0.00',
      `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

	$db->query("CREATE TABLE IF NOT EXISTS `auction_view` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `auction_id` int(11) NOT NULL,
      `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
}