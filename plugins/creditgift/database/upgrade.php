<?php
function creditgift_upgrade_database() {
	register_site_page("creditgift", array('title' => 'creditgift::creditgift', 'column_type' => THREE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'creditgift', 'content', 'middle');
		Widget::add(null, 'creditgift', 'plugin::creditgift|creditgift', 'left');
		Widget::add(null, 'creditgift', 'plugin::creditgift|richestmember', 'left');
		Widget::add(null, 'creditgift', 'plugin::creditgift|sendcredit', 'left');
		Widget::add(null, 'creditgift', 'plugin::creditgift|statistics', 'right');
		Widget::add(null, 'creditgift', 'plugin::creditgift|myrank', 'right');
		Widget::add(null, 'feed', 'plugin::creditgift|creditgift', 'right');
		Widget::add(null, 'feed', 'plugin::creditgift|statistics', 'right');
	});

	register_site_page("creditgift-creditrank", array('title' => 'creditgift::creditgift-creditrank', 'column_type' => THREE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'creditgift-creditrank', 'content', 'middle');
		Widget::add(null, 'creditgift-creditrank', 'plugin::creditgift|creditgift', 'left');
		Widget::add(null, 'creditgift-creditrank', 'plugin::creditgift|richestmember', 'left');
		Widget::add(null, 'creditgift-creditrank', 'plugin::creditgift|sendcredit', 'left');
		Widget::add(null, 'creditgift-creditrank', 'plugin::creditgift|statistics', 'right');
		Widget::add(null, 'creditgift-creditrank', 'plugin::creditgift|myrank', 'right');
	});
	register_site_page("creditgift-giftshop", array('title' => 'creditgift::creditgift-giftshop', 'column_type' => THREE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'creditgift-giftshop', 'content', 'middle');
		Widget::add(null, 'creditgift-giftshop', 'plugin::creditgift|creditgift', 'left');
		Widget::add(null, 'creditgift-giftshop', 'plugin::creditgift|richestmember', 'left');
		Widget::add(null, 'creditgift-giftshop', 'plugin::creditgift|sendcredit', 'left');
		Widget::add(null, 'creditgift-giftshop', 'plugin::creditgift|statistics', 'right');
		Widget::add(null, 'creditgift-giftshop', 'plugin::creditgift|myrank', 'right');
	});
	register_site_page("creditgift-purchase", array('title' => 'creditgift::creditgift-purchase', 'column_type' => THREE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'creditgift-purchase', 'content', 'middle');
		Widget::add(null, 'creditgift-purchase', 'plugin::creditgift|creditgift', 'left');
		Widget::add(null, 'creditgift-purchase', 'plugin::creditgift|richestmember', 'left');
		Widget::add(null, 'creditgift-purchase', 'plugin::creditgift|sendcredit', 'left');
		Widget::add(null, 'creditgift-purchase', 'plugin::creditgift|statistics', 'right');
		Widget::add(null, 'creditgift-purchase', 'plugin::creditgift|myrank', 'right');
	});
	register_site_page("creditgift-receipt", array('title' => 'creditgift::creditgift-receipt', 'column_type' => ONE_COLUMN_LAYOUT), function() {
		Widget::add(null, 'creditgift-receipt', 'content', 'middle');
	});
}