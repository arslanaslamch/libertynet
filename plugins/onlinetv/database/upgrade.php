<?php
function onlinetv_upgrade_database() {
	$db = db();

	try {
		$db->query("ALTER TABLE `onlinetvs` ADD `entity_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `user_id`");
	} catch(Exception $e) {
	}

	try {
		$db->query("ALTER TABLE `onlinetvs` ADD `entity_id` INT(11) NOT NULL AFTER `entity_type`");
	} catch(Exception $e) {
	}

	try {
		$db->query("ALTER TABLE `feeds` ADD `onlinetv` TEXT NOT NULL AFTER `video`");
	} catch(Exception $e) {
	}

	$db->query("UPDATE `onlinetvs` SET entity_type = 'user' WHERE entity_type = ''");
	$db->query("UPDATE `onlinetvs` SET entity_id = user_id WHERE entity_id = 0");

	register_site_page('onlinetvs', array('title' => 'onlinetv::onlinetvs', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'onlinetvs', 'content', 'middle');
		Widget::add(null, 'onlinetvs', 'plugin::onlinetv|menu', 'right');
		Widget::add(null, 'onlinetvs', 'plugin::onlinetv|categories', 'right');
		Widget::add(null, 'feed', 'plugin::onlinetv|latest', 'right');
		Widget::add(null, 'profile', 'plugin::onlinetv|profile-recent', 'right');
		Menu::saveMenu('main-menu', 'onlinetv::onlinetvs', 'onlinetvs', 'manual', true, 'ion-android-clipboard');
	});
	register_site_page('onlinetv-add', array('title' => 'onlinetv::onlinetvs-add-page', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'onlinetv-add', 'content', 'middle');
	});
	register_site_page('onlinetv-manage', array('title' => 'onlinetv::manage-onlinetvs', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'onlinetv-manage', 'content', 'middle');
	});
	register_site_page('onlinetv-page', array('title' => 'onlinetv::onlinetv-view-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'onlinetv-page', 'content', 'middle');
		Widget::add(null, 'onlinetv-page', 'plugin::onlinetv|menu', 'right');
		Widget::add(null, 'onlinetv-page', 'plugin::onlinetv|related', 'right');
	});

	$db = db();
	try {
		$db->query("ALTER TABLE `onlinetvs` ADD  `featured` TINYINT(1) NOT NULL DEFAULT  '0'");
	} catch(Exception $e) {
	}
	try {
		$db->query("ALTER TABLE `onlinetvs` ADD `entity_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `user_id`");
	} catch(Exception $e) {
	}
	try {
        $db->query("ALTER TABLE `onlinetv_categories` ADD  `parent_id` TINYINT(50) NOT NULL DEFAULT  '0'");	} catch(Exception $e) {
	}
	try {
		$db->query("ALTER TABLE `onlinetvs` ADD `entity_id` INT(11) NOT NULL AFTER `entity_type`");
	} catch(Exception $e) {
	}
	try{
        /** @var $title */
        if($db->query("SELECT COUNT(id) FROM onlinetv_categories")->fetch_row()[0] == 0) {
            $preloaded_categories = array('Arts and Culture','Health','Business','Technology',
                'Sports', 'Horror', 'Entertainment', 'Music','Comedy','Kids','Documentary','News TV','LifeStyle','Religion','Education','Others');
            $i = 1;
            foreach($preloaded_categories as $preloaded_category) {
                foreach(get_all_languages() as $language) {
                    $post_vars['title'][$language['language_id']] = $preloaded_category;
                }
                $expected = array('title' => '');
                extract(array_merge($expected, $post_vars));
                $titleSlug = 'onlinetv_category_'.md5(time().serialize($post_vars)).'_title';
                foreach($title as $langId => $t) {
                    add_language_phrase($titleSlug, $t, $langId, 'onlinetv');
                }
                foreach($title as $langId => $t) {
                    (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'onlinetv') : add_language_phrase($titleSlug, $t, $langId, 'onlinetv');
                }
                $db->query("INSERT INTO onlinetv_categories(title, category_order) VALUES('".$titleSlug."', '".$i."')");
                $i++;
            }
        }
		} catch (Exception $e){}
}