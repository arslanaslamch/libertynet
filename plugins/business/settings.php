<?php
return array(
    'site-other-settings' => array(
        'title' => 'Business Directory Settings',
        'description' => 'Business Directory Setting',
        'settings' => array(
            'business-per-page' => array(
                'type' => 'text',
                'title' => 'Business per page',
                'description' => ' The numbers of business are configured here',
                'value' => 20
            ),
            'currency' => array(
                'title' => lang('business::currency'),
                'description' => lang('business::currency-desc'),
                'type' => 'text',
                'value' => '$'
            ),
            'pagination-limit-businesses' => array(
                'type' => 'text',
                'title' => lang('business::pagination-limit-properties'),
                'description' => lang('property::pagination-limit-properties-desc'),
                'value' => '20'
            )
        )
    )
);