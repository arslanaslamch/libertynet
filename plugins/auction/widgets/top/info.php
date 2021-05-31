<?php
return array(
	'title' => 'Most Viewed Auction',
	'description' => 'Shows Most Viewed Auctions',
	'settings' => array(
		'limit' => array(
			'title' => lang('auction::listings-block-limit'),
			'description' => lang('auction::listings-block-limit-desc'),
			'type' => 'text',
			'value' => 6
		)
	)
);