<?php
function chat_upgrade_database() {
	register_site_page("messages", array('title' => 'chat::messages', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'messages', 'content', 'middle');
	});

	$db = db();
	try {
		$db->query("ALTER TABLE `conversation_messages` ADD `voice` TEXT NOT NULL AFTER `image`;");
	} catch(Exception $e) {}
	try {
		$db->query("ALTER TABLE `conversation_messages` ADD `gif` TEXT NOT NULL AFTER `image`;");
	} catch(Exception $e) {}
	try {
		$db->query("ALTER TABLE `conversations` ADD `color1` VARCHAR(50) NOT NULL AFTER `title`, ADD `color2` VARCHAR(50) NOT NULL AFTER `color1`;");
	} catch(Exception $e) {}
	
	try {
		$db->query("ALTER TABLE `conversation_members` ADD `status` VARCHAR(2) NOT NULL AFTER `active`;");
	} catch(Exception $e) {}

	try {
		$db->query("ALTER TABLE `conversations` ADD `entity_type` VARCHAR(50) NOT NULL DEFAULT 'user' AFTER `color2`, ADD `entity_id` VARCHAR(6) NOT NULL AFTER `entity_type`;");
	} catch(Exception $e) {}

	try {
		$db->query("ALTER TABLE `conversation_members` ADD `entity_type` VARCHAR(50) NOT NULL DEFAULT 'user' AFTER `status`, ADD `entity_id` VARCHAR(20) NOT NULL AFTER `entity_type`;");
	} catch(Exception $e) {}

	try {
		$db->query("ALTER TABLE `conversation_messages` ADD `entity_type` VARCHAR(20) NOT NULL DEFAULT 'user' AFTER `voice`, ADD `entity_id` VARCHAR(20) NOT NULL AFTER `entity_type`;");
	} catch(Exception $e) {}

    try {
        $db->query("ALTER TABLE `conversation_messages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    } catch(Exception $e) {}

}
