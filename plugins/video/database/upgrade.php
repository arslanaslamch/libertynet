<?php
function video_upgrade_database() {
	register_site_page("videos", array('title' => 'video::videos-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'videos', 'content', 'middle');
		Widget::add(null, 'videos', 'plugin::video|menu', 'right');
		Widget::add(null, 'videos', 'plugin::video|featured', 'right');
		Widget::add(null, 'videos', 'plugin::video|top', 'right');
		Widget::add(null, 'videos', 'plugin::video|latest', 'right');

		Widget::add(null, 'profile', 'plugin::video|profile-recent', 'right');
		Widget::add(null, 'feed', 'plugin::video|latest', 'right');
		Menu::saveMenu('main-menu', 'video::videos', 'videos', 'manual', true, 'ion-ios-videocam-outline');
	});

	register_site_page("video-create", array('title' => 'video::videos-create-page', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'video-create', 'content', 'middle');
	});


	register_site_page("video-page", array('title' => 'video::view-video-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'video-page', 'content', 'middle');
		Widget::add(null, 'video-page', 'plugin::video|menu', 'right');
		Widget::add(null, 'video-page', 'plugin::video|related', 'right');
		Widget::add(null, 'video-page', 'plugin::video|latest', 'right');
	});

	$db = db();
	$db->query("ALTER TABLE  `videos` ADD  `auto_posted` INT NOT NULL DEFAULT  '0' AFTER  `privacy` ;");
	$db->query("ALTER TABLE  `videos` ADD `thumbnail` text COLLATE utf8_unicode_ci NOT NULL  AFTER `photo_path`;");
	$db->query("UPDATE `videos` SET entity_type = 'user' WHERE entity_type = 'feed'");
    $db->query("CREATE TABLE IF NOT EXISTS `video_viewers` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `video_id` INT(11) UNSIGNED NOT NULL,
        `user_id` VARCHAR(11) NULL,
        `ip` VARCHAR(15) NOT NULL,
        `last_view_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `bot` TINYINT(1) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        KEY `bot` (`bot`),
        KEY `last_view_time` (`last_view_time`),
        KEY `video_id` (`video_id`),
        KEY `user_id` (`user_id`)
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

	if(function_exists('video_process_all')) {
		//video_process_all(true);
	}
	
}