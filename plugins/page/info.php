<?php
return array(
	'title' => 'Page Plugin',
	'description' => 'This plugin allow your members to create page with categories',
	'author' => 'Crea8social Team',
	'link' => 'http://www.crea8social.com',
	'version' => '1.0',
		'menu-items' => [
		'title' => 'page::pages',
		'link' => 'pages',
		'icon' => 'ion-flag',
		'menu-location' => array(
			'id' => 'blogs-menu',
			'title' => 'blog::blogs-menu'
		)
	]
);
 