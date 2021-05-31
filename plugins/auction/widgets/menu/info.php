<?php
return array(
	'title' => 'Menu',
	'description' => 'Shows Auction By Category',
	'settings' => array(
		'limit' => array(
			'title' => lang('auction::listings-block-limit'),
			'description' => lang('auction::listings-block-limit-desc'),
			'type' => 'text',
			'value' => 6
		)
	)
);