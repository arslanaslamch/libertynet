<?php
function giftshop_upgrade_database() {
	$db = db();
	try {
		$db->query("ALTER TABLE `giftshop` CHANGE `image` `image` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
	} catch(Exception $e) {
		$error = $e;
	}
	try {
		$db->query("ALTER TABLE `giftshop_mygift` CHANGE `owner_id` `sender_id` INT( 11 ) UNSIGNED NOT NULL;");
	} catch(Exception $e) {
		$error = $e;
	}
	try {
		$db->query("ALTER TABLE `giftshop_mygift` ADD `receiver_id` INT( 11 ) UNSIGNED NOT NULL AFTER `sender_id`;");
	} catch(Exception $e) {
		$error = $e;
	}
	register_site_page("giftshop", array('title' => 'giftshop::giftshop', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'giftshop', 'content', 'middle');
		Widget::add(null, 'giftshop', 'plugin::giftshop|giftshop', 'left');
		Widget::add(null, 'giftshop', 'plugin::giftshop|lastrecieved', 'left');
		Widget::add(null, 'giftshop', 'plugin::giftshop|lastsent', 'left');
		Menu::saveMenu('main-menu', 'giftshop::giftshop', 'giftshop', 'manual', 1, 'ion-bag');
	});
	register_site_page("giftshop-category", array('title' => 'giftshop::giftshop', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'giftshop-category', 'content', 'middle');
		Widget::add(null, 'giftshop-category', 'plugin::giftshop|giftshop', 'left');
		Widget::add(null, 'giftshop-category', 'plugin::giftshop|lastrecieved', 'left');
		Widget::add(null, 'giftshop-category', 'plugin::giftshop|lastsent', 'left');
	});
	register_site_page("giftshop-mine", array('title' => 'giftshop::giftshop', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'giftshop-mine', 'content', 'middle');
		Widget::add(null, 'giftshop-mine', 'plugin::giftshop|giftshop', 'left');
		Widget::add(null, 'giftshop-mine', 'plugin::giftshop|lastrecieved', 'left');
		Widget::add(null, 'giftshop-mine', 'plugin::giftshop|lastsent', 'left');
	});
}