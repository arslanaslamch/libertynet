<?php
function notification_upgrade_database() {
    register_site_page("notifications", array('title' => 'notifications-page', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'notifications', 'content', 'middle');
	});
}