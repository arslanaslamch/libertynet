<?php
return array(
    'site-other-settings' => array(
        'title' => 'Photo',
        'description' => lang("relationship::relationship-setting-description"),
        'settings' => array(
            'default-photo-album-privacy' => array(
                'type' => 'selection',
                'title' => lang('photo::default-photo-album-privacy'),
                'description' => lang('photo::default-photo-album-privacy-desc'),
                'value' => 1,
                'data' => array(
                    '1' => lang('public'),
                    '2' => lang('photo::friends-or-followers')
                )
            ),
            'photo-listing-per-page' => array(
                'type' => 'text',
                'title' => lang('photo::photo-listing-per-page'),
                'description' => lang('photo::photo-listing-per-page-desc'),
                'value' => 20,

            ),

            'prevent-nude-photo-upload' => array(
                'type' => 'boolean',
                'title' => lang('photo::prevent-nude-photo-upload'),
                'description' => lang('prevent-nude-photo-upload-desc'),
                'value' => 0
            ),
            'photo-nudity-tolerance' => array(
                'type' => 'text',
                'title' => lang('photo::photo-nudity-tolerance'),
                'description' => lang('photo::photo-nudity-tolerance-desc'),
                'value' => 50,
            ),
            'sightengine-api-user' => array(
                'type' => 'text',
                'title' => lang('photo::sightengine-api-user'),
                'description' => lang('photo::sightengine-api-user-desc'),
                'value' => '',
            ),
            'sightengine-api-secret' => array(
                'type' => 'text',
                'title' => lang('photo::sightengine-api-secret'),
                'description' => lang('photo::sightengine-api-secret-desc'),
                'value' => '',
            ),
            'photo-album-listing-per-page' => array(
                'type' => 'text',
                'title' => lang('photo::photo-album-listing-per-page'),
                'description' => lang('photo::photo-album-listing-per-page-desc'),
                'value' => 20,
            ),
        )
    )
);
 