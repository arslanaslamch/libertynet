<?php
return array(
    'site-other-settings' => array(
        'title' => 'Online TV',
        'description' => '',
        'settings' => array(
            'allow-members-create-onlinetv' => array(
                'type' => 'boolean',
                'title' => lang('onlinetv::allow-member-to-create-onlinetv'),
                'description' => lang('onlinetv::allow-member-to-create-onlinetv-desc'),
                'value' => 1,
            ),
            'enable-country-filter' => array(
                'type' => 'boolean',
                'title' => 'Allow Country Filter',
                'description' => 'Allow country filter in tv',
                'value' => 1,
            ),
        )
    )
);