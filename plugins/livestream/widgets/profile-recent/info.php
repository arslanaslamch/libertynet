<?php
return array(
	'title' => lang('livestream::recent-livestreams'),
	'description' => lang('livestream::profile-recent-livestreams-widget-desc'),
	'settings' => array(
		'limit' => array(
			'type' => 'text',
			'title' => lang('livestream::num-text-display'),
			'description' => lang('livestream::num-text-display-desc'),
			'value' => 3
		)
	)
);