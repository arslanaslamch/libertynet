<?php
function quiz_install_database() {
	$db = db();
	$db->query("CREATE TABLE IF NOT EXISTS `quizes` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` int(11) NOT NULL,
		`entity_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`entity_id` int(11) NOT NULL,
		`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`content` text COLLATE utf8_unicode_ci NOT NULL,
		`slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`image` text COLLATE utf8_unicode_ci NOT NULL,
		`tags` text COLLATE utf8_unicode_ci NOT NULL,
		`status` int(11) NOT NULL DEFAULT '0',
		`featured` int(11) NOT NULL DEFAULT '0',
		`views` int(11) NOT NULL DEFAULT '0',
		`privacy` int(11) NOT NULL DEFAULT '1',
		`category_id` int(11) NOT NULL,
		`update_time` int(11) NOT NULL,
		`time` int(11) NOT NULL,
		`pass_mark` text COLLATE utf8_unicode_ci NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
	
	$db->query("CREATE TABLE IF NOT EXISTS `quiz_questions` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` int(11) NOT NULL,
		`quiz_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`quiz_id` int(11) NOT NULL,
        `question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`answers` text COLLATE utf8_unicode_ci NOT NULL,
		`correct_answers` text COLLATE utf8_unicode_ci NOT NULL,
		`pass_mark` text COLLATE utf8_unicode_ci NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
	
	$db->query("CREATE TABLE IF NOT EXISTS `quiz_answers` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`quiz_id` int(11) NOT NULL,
	`question_id` int(11) NOT NULL,
	`time` int(11) NOT NULL,
	 `quiz_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	 `chk_question` text COLLATE utf8_unicode_ci NOT NULL,
	`chk_answer` text COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id` (`user_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
	
	$db->query("
    CREATE TABLE IF NOT EXISTS `quiz_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `category_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");
	$db->query("
    CREATE TABLE IF NOT EXISTS `quiz_status` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	  `quiz_id` int(11) NOT NULL,
	  `status` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");
	
	/** @var $title */
	if($db->query("SELECT COUNT(id) FROM quiz_categories")->fetch_row()[0] == 0) {
		$preloaded_categories = array('Entertainment', 'Tech', 'Politics', 'Food', 'Sport');
		$i = 1;
		foreach($preloaded_categories as $preloaded_category) {
			foreach(get_all_languages() as $language) {
				$post_vars['title'][$language['language_id']] = $preloaded_category;
			}
			$expected = array('title' => '');
			extract(array_merge($expected, $post_vars));
			$titleSlug = 'quiz_category_'.md5(time().serialize($post_vars)).'_title';
			foreach($title as $langId => $t) {
				add_language_phrase($titleSlug, $t, $langId, 'quiz');
			}
			foreach($title as $langId => $t) {
				(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'quiz') : add_language_phrase($titleSlug, $t, $langId, 'quiz');
			}
			$db->query("INSERT INTO quiz_categories(title, category_order) VALUES('".$titleSlug."', '".$i."')");
			$i++;
		}
	}
}