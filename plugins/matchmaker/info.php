<?php
return [
    'title' => 'Matchmaker',
    'description' => 'A plugin to add matchmaking functionalities like encounters and matching',
    'author' => 'Crea8social Team',
    'link' => 'https://crea8social.com',
    'version' => '1.0',
    'menu-items' => [
        'title' => 'matchmaker::matchmaker',
        'link' => 'matchmaker',
        'icon' => 'fa fa-heart',
        'menu-location' => array(
            'id' => 'matchmaker-menu',
            'title' => 'matchmaker::matchmaker-menu'
        )
    ]
];
