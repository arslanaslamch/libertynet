<?php
return array(
	'title' => lang('business::featured-businesses'),
	'description' => lang('business::featured-businesses-desc'),
	'settings' => array(
		'limit' => array(
			'title' => lang('business::businesses-block-limit'),
			'description' => lang('business::businesses-block-limit-desc'),
			'type' => 'text',
			'value' => 6
		)
	)
);