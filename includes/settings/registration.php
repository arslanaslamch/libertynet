<?php
return array(
    'user' => array(
        'title' => lang('registration'),
        'description' => lang('registration-settings-description'),
        'id' => 'registration-site-settings',
        'settings' => array(
            'user-signup' => array(
                'type' => 'boolean',
                'title' => lang('enable-user-registration'),
                'description' => lang('enable-user-registration-desc'),
                'value' => 1
            ),

            'user-activation' => array(
                'type' => 'boolean',
                'title' => lang('enable-user-account-activation'),
                'description' => lang('enable-user-account-activation-desc'),
                'value' => 0
            ),

            'auto-follow-users' => array(
                'type' => 'textarea',
                'title' => lang('add-auto-follow-accounts'),
                'description' => lang('add-auto-follow-accounts-desc'),
                'value' => ''
            ),

            'signup-hide-phone-no' => array(
                'type' => 'boolean',
                'title' => lang('signup-hide-phone-no'),
                'description' => lang('signup-hide-phone-no-desc'),
                'value' => '0',
            ),

            'signup-hide-email-address' => array(
                'type' => 'boolean',
                'title' => lang('signup-hide-email-address'),
                'description' => lang('signup-hide-email-address-desc'),
                'value' => '0',
            ),

            'signup-merge-email-phone' => array(
                'type' => 'boolean',
                'title' => lang('signup-merge-email-phone'),
                'description' => lang('signup-merge-email-phone-desc'),
                'value' => '0',
            ),

            'user-primary-contact' => array(
                'type' => 'selection',
                'title' => lang('user-primary-contact'),
                'description' => lang('user-primary-contact-desc'),
                'value' => 'email',
                'data' => array(
                    'email' => lang('email'),
                    'phone' => lang('phone')
                ),
            ),

            'user-require-birthday' => array(
                'type' => 'boolean',
                'title' => lang('require-birthday'),
                'description' => lang('require-birthday-desc'),
                'value' => 1
            ),

            'user-require-bio' => array(
                'type' => 'boolean',
                'title' => lang('require-bio'),
                'description' => lang('require-bio-desc'),
                'value' => 0
            ),

            'user-require-gender' => array(
                'type' => 'boolean',
                'title' => lang('require-gender'),
                'description' => lang('require-gender-desc'),
                'value' => 1
            ),

            'user-require-city' => array(
                'type' => 'boolean',
                'title' => lang('require-city'),
                'description' => lang('require-city-desc'),
                'value' => 0
            ),

            'user-require-state' => array(
                'type' => 'boolean',
                'title' => lang('require-state'),
                'description' => lang('require-state-desc'),
                'value' => 0
            ),

            'user-require-country' => array(
                'type' => 'boolean',
                'title' => lang('require-country'),
                'description' => lang('require-country-desc'),
                'value' => 0
            ),

            'user-require-avatar' => array(
                'type' => 'boolean',
                'title' => lang('require-avatar'),
                'description' => lang('require-avatar-desc'),
                'value' => 0
            )
        )
    )
);
