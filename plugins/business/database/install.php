<?php
function business_install_database() {
	$db = db();

	$db->query("CREATE TABLE IF NOT EXISTS `business` (
     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     `user_id` varchar(100) NOT NULL,
     `business_name` varchar(225) NOT NULL,
     `business_number` varchar(50) NOT NULL,
     `business_type_id` varchar(10) NOT NULL,
     `category_id` varchar(255) NOT NULL,
     `description` text NOT NULL,
     `company_logo` varchar(255) NOT NULL,
     `company_address` varchar(255) NOT NULL,
     `company_email` varchar(100) NOT NULL,
     `company_phone` varchar(100) NOT NULL,
     `size` varchar(50) NOT NULL,
     `website` varchar(100) DEFAULT NULL,
     `fax` varchar(50) NOT NULL,
     `business_hr` varchar(2) NOT NULL,
     `timezone` varchar(100) NOT NULL,
     `time` timestamp NULL DEFAULT NULL,
     `rating` varchar(100) DEFAULT NULL,
     `claimed` varchar(5) DEFAULT NULL,
     `claimed_user_id` tinyint(100) DEFAULT NULL,
     `views` int(11) DEFAULT NULL,
     `featured` int(6) DEFAULT NULL,
     `approved` varchar(5) DEFAULT NULL,
     `tags` varchar(225) NOT NULL,
     `plan_id` varchar(5) DEFAULT NULL,
     `paid` tinyint(1) DEFAULT NULL,
     `price` int(11) NOT NULL,
     `expiry_date` timestamp NULL DEFAULT NULL,
     `active` varchar(5) DEFAULT NULL,
     `last_viewed` timestamp NULL DEFAULT NULL,
     `slug` varchar(225) NOT NULL,
     `country` varchar(225) NOT NULL,
     `can_create` int(1) NOT NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;");

	$db->query("CREATE TABLE IF NOT EXISTS `business_category` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `category` varchar(225) NOT NULL,
     `slug` varchar(225) NOT NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;");

	/** @var $title */
	if($db->query("SELECT COUNT(id) FROM business_category")->fetch_row()[0] == 0) {
		$preloaded_categories = array('Electronics', 'Services');
		foreach($preloaded_categories as $preloaded_category) {
			foreach(get_all_languages() as $language) {
				$post_vars['title'][$language['language_id']] = $preloaded_category;
			}
			$expected = array('title' => '');
			extract(array_merge($expected, $post_vars));
			$titleSlug = 'business_category_'.md5(time().serialize($post_vars)).'_title';
			foreach($title as $langId => $t) {
				add_language_phrase($titleSlug, $t, $langId, 'business');
			}
			foreach($title as $langId => $t) {
				(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'business') : add_language_phrase($titleSlug, $t, $langId, 'business');
			}
			$db->query("INSERT INTO business_category(slug, category) VALUES('".trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', lang($titleSlug))), '-')."', '".$titleSlug."')");
		}
	}


	$db->query("CREATE TABLE IF NOT EXISTS `business_favourite` (
         `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
         `business_id` int(225) NOT NULL,
         `user_id` int(225) NOT NULL,
         `role` int(3) NOT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");


	$db->query("CREATE TABLE IF NOT EXISTS `business_images` (
     `id` bigint(100) NOT NULL AUTO_INCREMENT,
     `image` varchar(225) NOT NULL,
     `business_id` bigint(100) NOT NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");


	$db->query("CREATE TABLE IF NOT EXISTS `business_member` (
         `id` bigint(20) NOT NULL AUTO_INCREMENT,
         `business_id` bigint(50) NOT NULL,
         `user_id` bigint(50) NOT NULL,
         `role` bigint(5) NOT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 ;");


	$db->query("CREATE TABLE IF NOT EXISTS `business_plans` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(225) NOT NULL,
        `description` varchar(225) NOT NULL,
        `days` int(11) NOT NULL,
        `featured` tinyint(1) NOT NULL,
        `price` int(11) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");

	if($db->query("SELECT COUNT(id) FROM business_plans")->fetch_row()[0] == 0) {
		$preloaded_plans = array(
			array(
				'title' => 'Basic',
				'description' => 'Basic Plan',
				'days' => 365,
				'featured' => 0,
				'price' => 0
			),
			array(
				'title' => 'Premium',
				'description' => 'Premium Plan',
				'days' => 365,
				'featured' => 1,
				'price' => 30
			)
		);
		foreach($preloaded_plans as $preloaded_plan) {
			$db->query("INSERT INTO business_plans(title, description, days, featured, price) VALUES('".$preloaded_plan['title']."', '".$preloaded_plan['description']."', ".$preloaded_plan['days'].", ".$preloaded_plan['featured'].", ".$preloaded_plan['price'].")");
		}
	}

	$db->query("CREATE TABLE IF NOT EXISTS `business_reviews` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `user_id` bigint(100) NOT NULL,
        `business_id` bigint(100) NOT NULL,
        `comment` text NOT NULL,
        `time` int(15) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");


	$db->query("CREATE TABLE IF NOT EXISTS `business_type` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `categories` varchar(225) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");

	$db->query("CREATE TABLE IF NOT EXISTS `business_views` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `user_id` bigint(100) NOT NULL,
        `business_id` bigint(100) NOT NULL,
        `time` int(15) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");

	$db->query("CREATE TABLE IF NOT EXISTS  `business_rating` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
	 `user_id` int(11) unsigned NOT NULL,
	 `business_id` int(11) unsigned NOT NULL,
	 `star_1` int(11) unsigned NOT NULL,
	 `star_2` int(11) unsigned NOT NULL,
	 `star_3` int(11) unsigned NOT NULL,
	 `star_4` int(11) unsigned NOT NULL,
	 `star_5` int(11) unsigned NOT NULL,
	 PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query(" CREATE TABLE IF NOT EXISTS `business_hours` (
	 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	 `business_id` int(20) NOT NULL,
	 `day` varchar(50) NOT NULL,
	 `open_time` time NOT NULL,
	 `close_time` time NOT NULL,
	 PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");
}