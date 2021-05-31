<?php
function feed_upgrade_database() {
	$db = db();
	try {
		$db->query("ALTER TABLE `feeds` ADD `background` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `feed_content`");
	} catch(Exception $e) {}

	try {
		$db->query("ALTER TABLE `feeds` ADD `version` INT NOT NULL DEFAULT '0';");
	} catch(Exception $e) {}

	try {
		$db->query("UPDATE `feed` SET privacy = 1, version = 1 WHERE version = 0 AND privacy = 5;");

	} catch(Exception $e) {}

	try {
		$db->query("ALTER TABLE `feeds` ADD `gif` TEXT NOT NULL AFTER `video`;");
	} catch(Exception $e) {
	}

	try {
		$db->query("ALTER TABLE `feeds` ADD `voice` TEXT NOT NULL AFTER `video`;");
	} catch(Exception $e) {}

	try {
		$db->query("ALTER TABLE `feeds` ADD `list` TEXT NOT NULL AFTER `gif`;");
	} catch(Exception $e) {}

    try {
        $db->query("ALTER TABLE `feeds` ADD `hide_comment` INT(5) NOT NULL DEFAULT '0' AFTER `list`;");
    } catch(Exception $e) {}

    try {
        $db->query("ALTER TABLE `feeds` ADD `background_image` VARCHAR(255) NOT NULL AFTER `background`, ADD `background_color` VARCHAR(20) NOT NULL AFTER `background_image`;");
    } catch(Exception $e) {}

    try {
        $db->query("ALTER TABLE feeds CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    } catch(Exception $e) {}
	try {
		$db->query("ALTER TABLE `feeds` ADD `custom_friends` TEXT NOT NULL AFTER `tags`;");
	} catch(Exception $e) {}

	$db->query("CREATE TABLE IF NOT EXISTS `feed_lists` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");

		try{
		$db = db();
		if($db->query("SELECT COUNT(id) FROM feed_lists")->fetch_row()[0] == 0) {
			$preloaded_lists = array(
			"ion-android-lock" => 'Items I never leave home without',
			"ion-ios-location" => 'Places I want to visit',
		    "ion-heart" => 'What I love about my best friend',
		    "ion-sad" => 'Life lessons I learned the hard way',
		    "ion-ios-people" => 'People I am grateful for',
		    "ion-quote" => 'Quotes that inspire me',
		    "la_la-recycle" => 'Moments I wish I could re-live',
		    "la_la-cubes" => 'Reasons I love my friends',
			"fa_fa-align-right" => "This year's highlights",
			"fa_fa-play-circle" => "Songs I can't stop listening to",
			"fa_fa-thumbs-down" => 'My worst habits',
			"fa_fa-users" => 'Friends I miss',
			"la_la-spoon" => 'My favorite foods',
			"fa_fa-child" => 'Things I miss from childhood',
			"la_la-file-video-o" => 'Movies I want to see',
			"fa_fa-list-alt" => 'Things I need to get done',
			"fa_fa-eye" => "Things I can see from where I'm sitting right now",
			"la_la-book" => "My education history",
			"fa_fa-archive" => "Things most people don't know about me",
			"la_la-volume-up" => 'Things I say all the time',
			"la_la-th-list" => "My lifetime bucket list",
			"fa_fa-music" => "Song titles that describe my life",
			"la_la-smile-o" => "Little things that make me smile",
			"fa_fa-book" => "My plans for the weekend",
			"la_ la-reorder" => 'Things I want',
			);
			$i = 1;
			$post_vars = array();
			foreach($preloaded_lists as $key => $preloaded_list) {
				$icon = $key;
				$iconL = explode('_', $key);
				if (count($iconL) > 1) {
					$icon = $iconL[0]." ".$iconL[1];
				}
				foreach(get_all_languages() as $language) {
					$post_vars['list'][$language['language_id']] = $preloaded_list;
				}	
				$expected = array('list' => '');
				extract(array_merge($expected, $post_vars));
				$titleSlug = 'feed_list_'.md5(time().serialize($post_vars)).'_title';
				foreach($list as $langId => $t) {
					add_language_phrase($titleSlug, $t, $langId, 'feed');					}
					foreach($list as $langId => $t) {
						(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'feed') : add_language_phrase($titleSlug, $t, $langId, 'feed');
					}
					$db->query("INSERT INTO feed_lists(title, icon) VALUES('".$titleSlug."', '".$icon."')");
					$i++;
				}
			}
		} catch (Exception $e){

		}
		//hide reaction custom
		try {
			$db->query("ALTER TABLE `feeds` ADD `hide_reaction` INT(5) NOT NULL DEFAULT '0';");
		  } catch(Exception $e) {}
		  //notify
			$db->query("CREATE TABLE IF NOT EXISTS `notifypost` (
			  `notify_id` int(11) NOT NULL AUTO_INCREMENT,
			  `from_userid` int(11) NOT NULL,
			  `to_userid` int(11) NOT NULL,
			  `type` int(11) NOT NULL,
			  PRIMARY KEY (`notify_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
}
