<?php
return array(
	'title' => 'Video',
	'description' => 'Plays video from video directory, YouTube, Vimeo',
	'settings' => array(
		'uri' => array(
			'title' => lang('video-uri'),
			'description' => lang('video-widget-uri-desc'),
			'type' => 'text',
			'value' => ''
		),
		'width' => array(
			'title' => lang('video-width'),
			'description' => lang('video-widget-width-desc'),
			'type' => 'text',
			'value' => '100%'
		),
		'height' => array(
			'title' => lang('video-height'),
			'description' => lang('video-widget-height-desc'),
			'type' => 'text',
			'value' => '300'
		)
	)
);