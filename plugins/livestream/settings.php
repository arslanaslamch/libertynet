<?php
return array(
    'site-other-settings' => array(
        'title' => lang('livestream::livestream'),
        'description' => lang('livestream::livestream-setting-desc'),
        'settings' => array(
            'livestream-ice-transport-policy' => array(
                'type' => 'selection',
                'title' => lang('livestream::ice-transport-policy'),
                'description' => lang('livestream::ice-transport-policy-desc'),
                'value' => 'all',
                'data' => array(
                    'all' => lang('livestream::all'),
                    'relay' => lang('livestream::relay')
                )
            ),
            'livestreams-listing-limit' => array(
                'type' => 'text',
                'title' => lang('livestream::livestream-list-limit'),
                'description' => lang('livestream::livestream-list-limit-desc'),
                'value' => 12
            ),
            'livestream-ongoing-widget-list-limit' => array(
                'type' => 'text',
                'title' => lang('livestream::livestream-ongoing-widget-list-limit'),
                'description' => lang('livestream::livestream-ongoing-widget-list-limit-desc'),
                'value' => 5
            )
        )
    )
);