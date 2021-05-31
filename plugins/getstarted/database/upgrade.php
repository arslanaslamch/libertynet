<?php
function getstarted_upgrade_database() {
	register_site_page("signup-welcome", array('title' => 'getstarted', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'signup-welcome', 'content', 'middle');
	}, '7.4.0');
}