<?php
function quiz_upgrade_database() {
	$db = db();
	
	try {
		$db->query("ALTER TABLE `feeds` ADD `quiz` TEXT NOT NULL AFTER `video`");
	} catch(Exception $e) {
	}
	$db->query("UPDATE `quizes` SET entity_type = 'user' WHERE entity_type = ''");
	$db->query("UPDATE `quizes` SET entity_id = user_id WHERE entity_id = 0");
	register_site_page('quizes', array('title' => 'quiz::quizes', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'quizes', 'content', 'middle');
		Widget::add(null, 'quizes', 'plugin::quiz|menu', 'right');
		Widget::add(null, 'feed', 'plugin::quiz|latest', 'right');
		Widget::add(null, 'profile', 'plugin::quiz|profile-recent', 'right');
		Menu::saveMenu('main-menu', 'quiz::quizes', 'quizes', 'manual', true, 'fa fa-quiz');
	});
	register_site_page('quiz-add', array('title' => 'quiz::quizes-add-page', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'quiz-add', 'content', 'middle');
	});
	register_site_page('quiz-manage', array('title' => 'quiz::manage-quizes', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'quiz-manage', 'content', 'middle');
	});
	register_site_page('quiz-question-create', array('title' => 'quiz::create-questions', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'quiz-question-create', 'content', 'middle');
	});
	register_site_page('quiz-questions', array('title' => 'quiz::manage-questions', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'quiz-questions', 'content', 'middle');
	});
	register_site_page('quiz-page', array('title' => 'quiz::quiz-view-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'quiz-page', 'content', 'middle');
		Widget::add(null, 'quiz-page', 'plugin::quiz|menu', 'right');
		Widget::add(null, 'quiz-page', 'plugin::quiz|related', 'right');
	});
	
	try {
		$db->query("ALTER TABLE `quizes` ADD `entity_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `user_id`");
	} catch(Exception $e) {
	}
	try {
        $db->query("ALTER TABLE `quiz_categories` ADD  `parent_id` TINYINT(50) NOT NULL DEFAULT  '0'");	} catch(Exception $e) {
	}
	try {
		$db->query("ALTER TABLE `quizes` ADD `entity_id` INT(11) NOT NULL AFTER `entity_type`");
	} catch(Exception $e) {
	}
	try {
		$db->query("ALTER TABLE `quiz_answers` ADD `question_id` INT(11) NOT NULL AFTER `chk_answer`");
	} catch(Exception $e) {
	}
	try {
		$query = $db->query("SELECT COUNT(id) FROM quiz_categories");
		if($query) {
			$row = $query->fetch_row();
			if($row && !$row[0]) {
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
	} catch (Exception $e){}
}