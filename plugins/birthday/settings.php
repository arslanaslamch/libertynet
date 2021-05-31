<?php
return array(
    'site-other-settings' => array(
        'title' => 'Birthday System',
        'description' => '',
        'settings' => array(
            'enable-birthday-search' => array(
                'type' => 'boolean',
                'title' => "Enable Search on Birthday",
                'description' => "Enable if you want users to search for their friends by name",
                'value' => 0,
            ),
            'enable-birthday-timeline-wish' => array(
                'type' => 'boolean',
                'title' => "Enable Auto Wish (Timeline Post from Admin)",
                'description' => "Enable Auto Wish (Timeline Post from Admin)",
                'value' => 1,
            ),
            'enable-birthday-auto-email' => array(
                'type' => 'boolean',
                'title' => "Enable Auto Email Birthday Wish (News Letter)",
                'description' => "Enable Auto Email Birthday Wish(News Letter)",
                'value' => 1,
            ),
            'enable-birthday-reminder' => array(
                'type' => 'boolean',
                'title' => "Enable Auto Email to Friends of Birthday Celebrant",
                'description' => "Enable Auto Email to Friends of Birthday Celebrant",
                'value' => 1,
            ),
            'birthday-widget-friends-only' => array(
                'type' => 'boolean',
                'title' => "Birthday Widget Show Friends Only",
                'description' => "If No, It will show all users instead of Friends Only",
                'value' => 1,
            ),
        )
    )
);