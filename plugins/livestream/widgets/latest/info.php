<?php
return array(
	'title' => lang('livestream::latest-livestreams'),
	'description' => lang('livestream::latest-livestreams-widget-desc'),
	'settings' => array(
		'limit' => array(
			'type' => 'text',
			'title' => lang('livestream::num-text-display'),
			'description' => lang('livestream::num-text-display-desc'),
			'value' => 3
		)
	)
);