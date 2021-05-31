<?php
return array(
    'site-other-settings' => [
        'title' => 'Matchmaker',
        'description' => lang('matchmaker::matchmaker-settings-desc'),
        'settings' => [
            'default-miles' => [
                'type' => 'text',
                'title' => lang('matchmaker::default-miles'),
                'description' => lang('matchmaker::default-miles-desc'),
                'value' => '20'
            ],
            'default-unit' => [
                'type' => 'selection',
                'title' => lang('matchmaker::default-unit'),
                'description' => lang('matchmaker::default-unit-desc'),
                'value' => 'M',
                'data' => [
                    'M' => lang('matchmaker::miles'),
                    'K' => lang('matchmaker::kilometers'),
                ],
            ],
            'max-miles' => [
                'type' => 'text',
                'title' => lang('matchmaker::max-miles'),
                'description' => lang('matchmaker::max-miles-desc'),
                'value' => '100'
            ],
        ],
    ],
);
