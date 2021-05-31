<?php
function auction_upgrade_database() {
	register_site_page('auction-add', array('title' => 'Add Auction', 'column_type' => THREE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'auction-add', 'content', 'middle');
		Widget::add(null, 'auction-add', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-add', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-add', 'plugin::auction|cart', 'right');
		Widget::add(null, 'auction-add', 'plugin::auction|top', 'right');
		Menu::saveMenu('main-menu', 'auction::auction', 'auction', 'manual', 1, 'ion-android-alarm-clock');
	});
	register_site_page('auction-edit', array('title' => 'Edit Auction Details', 'column_type' => THREE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'auction-edit', 'content', 'middle');
		Widget::add(null, 'auction-edit', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-edit', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-edit', 'plugin::auction|cart', 'right');
		Widget::add(null, 'auction-edit', 'plugin::auction|top', 'right');
	});
	register_site_page('auction-list', array('title' => 'Auction List', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-list', 'content', 'middle');
		Widget::add(null, 'auction-list', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-list', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-list', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-list', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-details', array('title' => 'Auction Details', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-details', 'content', 'middle');
		Widget::add(null, 'auction-details', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-details', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-details', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-details', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-cart', array('title' => 'Auction Cart', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-cart', 'content', 'middle');
		Widget::add(null, 'auction-cart', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-cart', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-cart', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-cart', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-my-bids', array('title' => 'My Bids Auction', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-my-bids', 'content', 'middle');
		Widget::add(null, 'auction-my-bids', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-my-bids', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-my-bids', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-my-bids', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-my-offer', array('title' => 'My Offer Auction', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-my-offer', 'content', 'middle');
		Widget::add(null, 'auction-my-offer', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-my-offer', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-my-offer', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-my-offer', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-mine', array('title' => 'My Auctions', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-mine', 'content', 'middle');
		Widget::add(null, 'auction-my-mine', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-my-mine', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-my-mine', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-my-mine', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-activate', array('title' => 'Activate Auction', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-activate', 'content', 'middle');
		Widget::add(null, 'auction-activate', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-activate', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-activate', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-activate', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-approved-bids', array('title' => 'Auction Friend Bids', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-approved-bids', 'content', 'middle');
		Widget::add(null, 'auction-approved-bids', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-approved-bids', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-approved-bids', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-approved-bids', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-friend-bids', array('title' => 'Auction Friend Bids', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-friend-bids', 'content', 'middle');
		Widget::add(null, 'auction-friend-bids', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-friend-bids', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-friend-bids', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-friend-bids', 'plugin::auction|top', 'left');
	});
	register_site_page('auction-friends', array('title' => 'Friend Auction', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
		Widget::add(null, 'auction-friends', 'content', 'middle');
		Widget::add(null, 'auction-friends', 'plugin::auction|menu', 'left');
		Widget::add(null, 'auction-friends', 'plugin::auction|category', 'left');
		Widget::add(null, 'auction-friends', 'plugin::auction|cart', 'left');
		Widget::add(null, 'auction-friends', 'plugin::auction|top', 'left');
	});
	db()->query("ALTER TABLE `auction_new` ADD `mobile` VARCHAR(50) NOT NULL AFTER `description`;");
	db()->query("ALTER TABLE `auction_bid` ADD `bought` INT(2) NOT NULL AFTER `tmp_approve`;");
	db()->query("ALTER TABLE `auction_bid` ADD `bid_type` VARCHAR(50) NOT NULL AFTER `bought`;");
	db()->query("UPDATE `auction_new` SET status = 2 WHERE status = 3;");
}
