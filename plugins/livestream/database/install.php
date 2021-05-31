<?php
function livestream_install_database() {
	$db = db();

	$old_livestreams = array();
	$query = $db->query("SHOW TABLES LIKE 'livestreams'");
	if($query->num_rows) {
        $query = $db->query("SELECT * FROM livestreams");
        $result = fetch_all($query);
        if(isset($result[0]['view_count'])) {
            $old_livestreams = $result;
        }
    }

	$old_livestream_categories = array();
	$query = $db->query("SHOW TABLES LIKE 'livestream_categories'");
	if($query->num_rows) {
        $query = $db->query("SELECT * FROM livestream_categories");
        $result = fetch_all($query);
        if(isset($result[0]['category_order'])) {
            $old_livestream_categories = $result;
        }
    }

	$db->query("DROP TABLE IF EXISTS `livestream_categories`");
	$db->query("CREATE TABLE IF NOT EXISTS `livestream_categories` (
	  `id` INT(11) NOT NULL AUTO_INCREMENT,
	  `slug` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `title` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `parent_id` INT(11) NOT NULL,
	  `order` INT(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");

    foreach($old_livestream_categories as $old_livestream_category) {
        $db->query("INSERT INTO livestream_categories (id, slug, title, parent_id, `order`) VALUES (".$old_livestream_category['id'].", '".$old_livestream_category['slug']."', '".$old_livestream_category['title']."', ".$old_livestream_category['parent_id'].", ".$old_livestream_category['category_order'].")");
    }

	if($db->query("SELECT COUNT(id) FROM livestream_categories")->fetch_row()[0] == 0) {
		$preloaded_categories = array('General', 'Entertainment', 'Sport', 'Science and Technology');
		foreach($preloaded_categories as $preloaded_category) {
			$val = array();
			foreach(get_all_languages() as $language) {
				$val['title'][$language['language_id']] = $preloaded_category;
			}
			$expected = array('title' => '');
			extract(array_merge($expected, $val));
			$title_slug = 'livestream_category_'.md5(time().serialize($val)).'_title';
			/** @var array $title */
			$t = '';
			foreach($title as $lang_id => $t) {
				add_language_phrase($title_slug, $t, $lang_id, 'livestream');
			}
			$slug = unique_slugger($t);
			$db->query("INSERT INTO `livestream_categories` (`slug`, `title`) VALUES('".$slug."', '".$title_slug."')");
			foreach($title as $lang_id => $t) {
				(phrase_exists($lang_id, $title_slug)) ? update_language_phrase($title_slug, $t, $lang_id) : add_language_phrase($title_slug, $t, $lang_id, 'livestream');
			}
		}
	}

    $db->query("DROP TABLE IF EXISTS `livestreams`");
	$db->query("CREATE TABLE IF NOT EXISTS `livestreams` (
	  `id` INT(11) NOT NULL AUTO_INCREMENT,
	  `slug` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `title` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `description` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `category_id` INT(11) COLLATE utf8_unicode_ci NOT NULL,
      `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `token` TEXT COLLATE utf8_unicode_ci,
	  `start_time` TIMESTAMP NULL DEFAULT NULL,
	  `end_time` TIMESTAMP NULL DEFAULT NULL,
	  `image` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `path` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `featured` TINYINT(1) NOT NULL,
	  `user_id` INT(11) COLLATE utf8_unicode_ci NOT NULL,
	  `entity_type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
	  `entity_id` INT(11) NOT NULL,
	  `privacy` TINYINT(1) NOT NULL,
	  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `status` TINYINT(1) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `livestream_viewers` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `livestream_id` INT(11) UNSIGNED NOT NULL,
        `user_id` VARCHAR(11) NULL,
        `ip` VARCHAR(15) NOT NULL,
        `last_view_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `bot` TINYINT(1) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        KEY `bot` (`bot`),
        KEY `last_view_time` (`last_view_time`),
        KEY `livestream_id` (`livestream_id`),
        KEY `user_id` (`user_id`)
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    foreach($old_livestreams as $old_livestream) {
        $db->query("INSERT INTO livestreams (id, slug, title, description, category_id, type, token, start_time, end_time, image, path, featured, user_id, entity_type, entity_id, privacy, time, status) VALUES (".$old_livestream['id'].", '".$old_livestream['slug']."', '".$old_livestream['title']."', '".$old_livestream['description']."', ".$old_livestream['category_id'].", 'video', '".$old_livestream['broadcast_token']."', ".$old_livestream['start_time'].", ".$old_livestream['end_time'].", '".$old_livestream['preview_path']."', '".$old_livestream['file_path']."', ".$old_livestream['featured'].", ".$old_livestream['user_id'].", '".$old_livestream['entity_type']."', ".$old_livestream['entity_id'].", ".$old_livestream['privacy'].", ".$old_livestream['start_time'].", ".($old_livestream['file_path'] ? 3 : 2).")");
        for($i = 0; $i < $old_livestream['view_count']; $i++) {
            $db->query("INSERT INTO livestream_viewers (livestream_id, user_id, ip, bot) VALUES (".$old_livestream['id'].", 0, '0.0.0.0', 0)");
        }
    }

    $db->query("CREATE TABLE IF NOT EXISTS `livestream_ice_servers` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `url` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `type` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL,
        `username` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `credential` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `active` TINYINT(1) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    if($db->query("SELECT COUNT(id) FROM livestream_ice_servers")->fetch_row()[0] == 0) {
        $db->query("INSERT INTO `livestream_ice_servers` (url, type, username, credential, active) VALUES
        ('stun:stun.l.google.com:19302', 'stun', '', '', 1),
        ('stun:stun.services.mozilla.com:3478', 'stun', '', '', 1),
        ('turn:turn.anyfirewall.com:443?transport=tcp', 'turn', 'webrtc', 'webrtc', 1);");
    }

    plugin_deactivate('livestreaming');
}