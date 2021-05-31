<?php
return array(
	'title' => lang('livestream::featured-livestreams'),
	'description' => lang('livestream::featured-livestreams-widget-desc'),
	'settings' => array(
		'limit' => array(
			'type' => 'text',
			'title' => lang('livestream::num-text-display'),
			'description' => lang('livestream::num-text-display-desc'),
			'value' => 3
		)
	)
);