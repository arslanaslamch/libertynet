<?php
function livestream_upgrade_database() {

	register_site_page('livestream-list', array('title' => 'livestream::livestreams', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'livestream-list', 'plugin::livestream|menu', 'right');
		Widget::add(null, 'livestream-list', 'content', 'middle');
		Widget::add(null, 'livestream-list', 'plugin::livestream|latest', 'right');
		Widget::add(null, 'livestream-list', 'plugin::livestream|ongoing', 'right');
		Widget::add(null, 'livestream-list', 'plugin::livestream|categories', 'right');
		Widget::add(null, 'livestream-list', 'plugin::livestream|top', 'right');
		Menu::saveMenu('main-menu', 'livestream::livestreams', 'livestreams', 'manual', true, 'ion-radio-waves', 0, null, false);
	}, '7.2.4');

	register_site_page('livestream-view', array('title' => 'livestream::livestream', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'livestream-view', 'content', 'middle');
	}, '7.2.4');

	register_site_page('livestream-add', array('title' => 'livestream::livestream-add', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'livestream-add', 'content', 'middle');
	}, '7.2.4');

	register_site_page('livestream-edit', array('title' => 'livestream::livestream-edit', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
		Widget::add(null, 'livestream-edit', 'plugin::livestream|menu', 'right');
		Widget::add(null, 'livestream-edit', 'content', 'middle');
		Widget::add(null, 'livestream-edit', 'plugin::livestream|latest', 'right');
        Widget::add(null, 'livestream-list', 'plugin::livestream|ongoing', 'right');
		Widget::add(null, 'livestream-edit', 'plugin::livestream|categories', 'right');
		Widget::add(null, 'livestream-edit', 'plugin::livestream|top', 'right');
	}, '7.2.4');
}