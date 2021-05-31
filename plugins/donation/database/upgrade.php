<?php

function donation_upgrade_database() {
    register_site_page('single_donation', array('title' => 'donation::single-donation', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'single_donation', 'content', 'middle');
        Widget::add(null, 'single_donation', 'plugin::donation|menu', 'right');
        Widget::add(null, 'single_donation', 'plugin::donation|campaign-owner', 'right');
        Widget::add(null, 'single_donation', 'plugin::donation|campaign-menu', 'right');
        Widget::add(null, 'single_donation', 'plugin::donation|categories', 'right');
        Widget::add(null, 'single_donation', 'plugin::donation|stats', 'right');
    });

    register_site_page('donations-page', array('title' => 'donation::donations-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'donations-page', 'content', 'middle');
        Widget::add(null, 'donations-page', 'plugin::donation|menu', 'right');
        Widget::add(null, 'donations-page', 'plugin::donation|categories', 'right');
        Widget::add(null, 'donations-page', 'plugin::donation|stats', 'right');
    });

    register_site_page('single_donation_donate', array('title' => 'donation::donate-for-campaign', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'single_donation_donate', 'content', 'middle');
    });

    register_site_page('single_donation_donate', array('title' => 'donation::donate-for-campaign', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'single_donation_donate', 'content', 'middle');
    });

    register_site_page('more-fields', array('title' => 'donation::manage-campaign', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'more-fields', 'content', 'middle');
    });

    register_site_page('create-donation', array('title' => 'donation::create-donation', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'create-donation', 'content', 'middle');
    });

    db()->query("ALTER TABLE `lh_donations` ADD COLUMN `t_subject` VARCHAR(255) NULL DEFAULT 'Thank you for your contribution' AFTER `ytube`");
}