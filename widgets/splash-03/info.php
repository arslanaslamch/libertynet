<?php
return array(
	'title' => 'Awesome Splash Landing 03',
	'description' => 'This widget enable splash design for homepage',
	'settings' => array(
		'show_latest_members' => array(
			'type' => 'boolean',
			'title' => lang('show-latest-members'),
			'description' => '',
			'value' => 1
		),
		'show_all_signup_fields' => array(
			'type' => 'boolean',
			'title' => lang('show-all-signup-fields'),
			'description' => '',
			'value' => 0
		),
		'splash_color' => array(
			'type' => 'color',
			'title' => lang('splash-color'),
			'description' => '',
			'value' => '#4C4C4E'
		)
	)
);