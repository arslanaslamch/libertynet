<?php
function business_upgrade_database() {
    $db = db();

    $db->query("ALTER TABLE  `business` ADD `can_create` INT NOT NULL DEFAULT  '1'");

    $db->query("CREATE TABLE IF NOT EXISTS `business_rating` (
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

    business_dump_site_pages();
}

function business_dump_site_pages() {
    register_site_page('all-business', array('title' => 'business::businesses', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'all-business', 'content', 'middle');
        Widget::add(null, 'all-business', 'plugin::business|menu', 'left');
        Widget::add(null, 'all-business', 'plugin::business|categories', 'left');
        Widget::add(null, 'all-business', 'plugin::business|filter', 'left');
        Menu::saveMenu('main-menu', 'business::business', 'businesses', 'manual', 1, 'ion-briefcase');
    });

    register_site_page('business-page', array('title' => 'business::business', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-page', 'content', 'middle');
        Widget::add(null, 'business-page', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-page', 'plugin::business|info', 'left');
    });

    register_site_page('business-create', array('title' => 'business::create-business', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-create', 'content', 'middle');
        Widget::add(null, 'business-create', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-create', 'plugin::business|latest', 'left');
    });

    register_site_page('business-claim-page', array('title' => 'business::business-claims', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-claim-page', 'content', 'middle');
        Widget::add(null, 'business-claim-page', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-claim-page', 'plugin::business|latest', 'left');
    });

    register_site_page('business-favoured', array('title' => 'business::business-favourite', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-favoured', 'content', 'middle');
        Widget::add(null, 'business-favoured', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-favoured', 'plugin::business|latest', 'left');
    });

    register_site_page('business-followed', array('title' => 'business::business-followed', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-followed', 'content', 'middle');
        Widget::add(null, 'business-followed', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-followed', 'plugin::business|latest', 'left');
    });

    register_site_page('business-claim', array('title' => 'business::business-claim', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-claim', 'content', 'middle');
        Widget::add(null, 'business-claim', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-claim', 'plugin::business|latest', 'left');
    });

    register_site_page('business-contact', array('title' => 'business::business-contact', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-contact', 'content', 'middle');
        Widget::add(null, 'business-contact', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-contact', 'plugin::business|latest', 'left');
    });

    register_site_page('business-delete-business', array('title' => 'business::business-delete', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-delete-business', 'content', 'middle');
        Widget::add(null, 'business-delete-business', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-delete-business', 'plugin::business|latest', 'left');
    });

    register_site_page('business-delete-business', array('title' => 'Business Delete', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-delete-business', 'content', 'middle');
        Widget::add(null, 'business-delete-business', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-delete-business', 'plugin::business|latest', 'left');
    });

    register_site_page('business-reviews', array('title' => 'business::business-create-review', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-reviews', 'content', 'middle');
        Widget::add(null, 'business-reviews', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-reviews', 'plugin::business|latest', 'left');
    });

    register_site_page('business-payment', array('title' => 'business::business-payment', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-payment', 'content', 'middle');
        Widget::add(null, 'business-payment', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-payment', 'plugin::business|latest', 'left');
    });

    register_site_page('business-edit-business', array('title' => 'business::business-edit', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-edit-business', 'content', 'middle');
        Widget::add(null, 'business-edit-business', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-edit-business', 'plugin::business|latest', 'left');
    });

    register_site_page('business-add-images', array('title' => 'business::business-add-photo', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-add-images', 'content', 'middle');
        Widget::add(null, 'business-add-images', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-add-images', 'plugin::business|latest', 'left');
    });

    register_site_page('business-member', array('title' => 'business::business-member', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-member', 'content', 'middle');
        Widget::add(null, 'business-member', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-member', 'plugin::business|latest', 'left');
    });

    register_site_page('business-compare', array('title' => 'business::business-compare', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'business-compare', 'content', 'middle');
        Widget::add(null, 'business-compare', 'plugin::business|menu', 'left');
        Widget::add(null, 'business-compare', 'plugin::business|latest', 'left');
    });
}