<?php
return array(
	'title' => lang('livestream::menu'),
	'description' => lang('livestream::livestream-menu-widget-desc'),
	'settings' => array(
		'limit' => array(
			'type' => 'text',
			'title' => lang('livestream::num-text-display'),
			'description' => lang('livestream::num-text-display-desc'),
			'value' => 3
		)
	)
);