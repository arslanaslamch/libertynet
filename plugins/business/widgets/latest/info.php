<?php
return array(
	'title' => lang('business::latest-business'),
	'description' => lang('business::latest-properties-desc'),
	'settings' => array(
		'limit' => array(
			'title' => lang('business::businesses-block-limit'),
			'description' => lang('business::businesses-block-limit-desc'),
			'type' => 'text',
			'value' => 6
		)
	)
);