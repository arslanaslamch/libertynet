<?php
return array(
    'site-other-settings' => array(
        'title' => 'Music',
        'description' => lang('music::music-plugin setting'),
        'settings' => array(
            /*        'external-music' => array(
                        'type' => 'boolean',
                        'title' => lang('music::enable-external-music'),
                        'description' => lang('music::enable-external-music-desc'),
                        'value' => false
                    ),*/
            'music-list-limit' => array(
                'type' => 'text',
                'title' => lang('music::music-limit-per-page'),
                'description' => lang('music::music-limit-per-page-desc'),
                'value' => 12
            ),
            'default-music-privacy' => array(
                'type' => 'selection',
                'title' => lang('music::default-music-privacy'),
                'description' => lang('music::default-music-privacy-desc'),
                'value' => 1,
                'data' => array(
                    '1' => lang('music::public'),
                    '2' => lang('music::user-connections')
                )
            ),
            'enable-music-download' => array(
                'type' => 'boolean',
                'title' => lang('music::enable-music-download'),
                'description' => lang('music::enable-music-download-desc'),
                'value' => false
            )
        )
    )
);
 