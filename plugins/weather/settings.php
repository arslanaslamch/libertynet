<?php
return array(
    'site-other-settings' => array(
        'title' => lang('weather::weather-settings'),
        'description' => '',
        'settings' => array(
            'open-weather-api-key' => array(
                'type' => 'text',
                'title' => lang('weather::api-key'),
                'description' => 'https://openweathermap.org',
                'value' => '',
            ),
            'weather-enable-background' => array(
                'type' => 'boolean',
                'title' => lang('weather::enable-background'),
                'description' => '',
                'value' => '1',
            ),
            'weather-animate-background' => array(
                'type' => 'boolean',
                'title' => lang('weather::animate-background'),
                'description' => '',
                'value' => '1',
            ),
            'weather-temp-unit' => array(
                'type' => 'selection',
                'title' => lang('weather::temperature-unit'),
                'description' => '',
                'value' => 'F',
                'data' => array(
                    'C' => lang('weather::centigrade'),
                    'F' => lang('weather::fahrenheit')
                )
            )
        )
    )
);
