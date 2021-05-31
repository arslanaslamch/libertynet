<?php
function statistic_upgrade_database() {
    db()->query("CREATE TABLE IF NOT EXISTS `statistics` (
		 `id` int(4) NOT NULL AUTO_INCREMENT,
		 `user_id` varchar(10) NOT NULL,
		 `os` varchar(100) NOT NULL,
		 `browser` varchar(100) NOT NULL,
		 `country` varchar(50) NOT NULL,
		 `region` varchar(50) NOT NULL,
		 `city` varchar(50) NOT NULL,
		 `ip` varchar(20) NOT NULL,
		 `time` varchar(50) NOT NULL,
		 `active` int(11) NOT NULL DEFAULT '1',
		 PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");

	register_site_page('statistic-ajax', array('title' => lang('statistic::statistic'), 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'feed', 'plugin::statistic|userstat', 'right');
		Widget::add(null, 'feed', 'plugin::statistic|sitestat', 'right');
	});
}