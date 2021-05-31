<?php
return array(
    'user' => array(
        'title' => lang('users'),
        'description' => lang('user-settings-description'),
        'id' => 'user-site-settings',
        'settings' => array(
            'design-profile' => array(
                'type' => 'boolean',
                'title' => lang('enable-profile-design'),
                'description' => lang('enable-profile-design-desc'),
                'value' => 1,
            ),
            'enable-last-seen' => array(
                'type' => 'boolean',
                'title' => lang('enable-profile-lastseen'),
                'description' => '',
                'value' => 1,
            ),

            'request-verification' => array(
                'type' => 'boolean',
                'title' => lang('verification-request'),
                'description' => lang('verification-request-desc'),
                'value' => 1,
            ),

            'email-verification-resend-timeout' => array(
                'type' => 'text',
                'title' => lang('email-verification-resend-timeout'),
                'description' => lang('email-verification-resend-timeout-desc'),
                'value' => '60'
            ),

            'email-verification-code-life-time' => array(
                'type' => 'text',
                'title' => lang('email-verification-code-life-time'),
                'description' => lang('email-verification-code-life-time-desc'),
                'value' => '3600'
            ),

            'phone-no-verification-resend-timeout' => array(
                'type' => 'text',
                'title' => lang('phone-no-verification-resend-timeout'),
                'description' => lang('phone-no-verification-resend-timeout-desc'),
                'value' => '60'
            ),

            'phone-no-verification-code-life-time' => array(
                'type' => 'text',
                'title' => lang('phone-no-verification-code-life-time'),
                'description' => lang('phone-no-verification-code-life-time-desc'),
                'value' => '3600'
            ),

            'enable-suspicious-activity-detection' => array(
                'type' => 'boolean',
                'title' => lang('enable-suspicious-activity-detection'),
                'description' => lang('enable-suspicious-activity-detection-desc'),
                'value' => 0
            ),

            'default-profile-privacy' => array(
                'type' => 'selection',
                'title' => lang('default-profile-privacy'),
                'description' => lang('default-profile-privacy-desc'),
                'value' => 1,
                'data' => array(
                    '1' => lang('everyone'),
                    '2' => lang('friends-followers')
                )
            ),
            'default-birthdate-privacy' => array(
                'type' => 'selection',
                'title' => lang('default-birthdate-privacy'),
                'description' => lang('default-birthdate-privacy-desc'),
                'value' => 1,
                'data' => array(
                    '1' => lang('everyone'),
                    '2' => lang('friends-followers')
                )
            ),
            'enable-birthdate' => array(
                'type' => 'boolean',
                'title' => lang('enable-birthdate'),
                'description' => lang('enable-birthdate-desc'),
                'value' => 1
            ),

            'birthdate-min-age' => array(
                'type' => 'text',
                'title' => lang('birthdate-minimum-age'),
                'description' => lang('birthdate-minimum-age-desc'),
                'value' => '10'
            ),

            'genders' => array(
                'type' => 'textarea',
                'title' => lang('genders'),
                'description' => lang('genders-desc'),
                'value' => 'male, female'
            ),

            'login-trial-enabled' => array(
                'type' => 'boolean',
                'title' => lang('enable-login-trial'),
                'description' => lang('enable-login-trial-desc'),
                'value' => '1',
            ),

            'login-trials-limit' => array(
                'type' => 'selection',
                'title' => lang('login-trial-limit'),
                'description' => lang('login-trial-limit-desc'),
                'value' => '5',
                'data' => array('5' => '5 Times', '10' => '10 Times', '15' => '15 Times', '20' => '20 Times')
            ),

            'login-trial-wait-time' => array(
                'type' => 'selection',
                'title' => lang('login-trial-wait-time'),
                'description' => lang('login-trial-wait-time'),
                'value' => '15',
                'data' => array(
                    '5' => '5 '.lang('minutes'),
                    '10' => '10 '.lang('minutes'),
                    '15' => '15 '.lang('minutes'),
                    '20' => '20 '.lang('minutes'),
                    '30' => '30 '.lang('minutes')
                )
            ),

            'allow-change-email' => array(
                'type' => 'boolean',
                'title' => lang('allow-change-email'),
                'description' => lang('allow-change-email-desc'),
                'value' => '1',
            ),


            'allow-change-username' => array(
                'type' => 'boolean',
                'title' => lang('allow-change-username'),
                'description' => lang('allow-change-username-desc'),
                'value' => '1',
            ),

            'loose-verify-badge-username' => array(
                'type' => 'boolean',
                'title' => lang('remove-verify-badge-change-username'),
                'description' => lang('remove-verify-badge-change-username-desc'),
                'value' => '1',
            ),

            'enable-phone-no' => array(
                'type' => 'boolean',
                'title' => lang('enable-phone-no'),
                'description' => lang('enable-phone-no-desc'),
                'value' => '0',
            ),

            'allow-phone-no-change' => array(
                'type' => 'boolean',
                'title' => lang('allow-phone-no-change'),
                'description' => lang('allow-phone-no-change-desc'),
                'value' => '1',
            ),

            'enable-gender' => array(
                'type' => 'boolean',
                'title' => lang('enable-gender-display'),
                'description' => lang('enable-gender-display-desc'),
                'value' => '1',
            ),

            'enable-gender-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-gender-filter'),
                'description' => lang('enable-gender-filter-desc'),
                'value' => true
            ),
            'enable-age-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-age-filter'),
                'description' => lang('enable-age-filter-desc'),
                'value' => true
            ),
            'enable-country-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-country-filter'),
                'description' => lang('enable-country-filter-desc'),
                'value' => true
            ),
            'enable-state-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-state-filter'),
                'description' => lang('enable-state-filter-desc'),
                'value' => true
            ),
            'enable-city-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-city-filter'),
                'description' => lang('enable-city-filter-desc'),
                'value' => true
            ),
            'enable-online-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-online-filter'),
                'description' => lang('enable-gender-filter-desc'),
                'value' => true
            ),
            'enable-feature-filter' => array(
                'type' => 'boolean',
                'title' => lang('enable-feature-filter'),
                'description' => lang('enable-gender-filter-desc'),
                'value' => true
            ),
            'max-page-result' => array(
                'type' => 'text',
                'title' => lang('max-page-result'),
                'description' => lang('max-page-result-desc'),
                'value' => '20'
            ),
            'featured-badge-bg-color' => array(
                'title' => lang('featured-badge-bg-color'),
                'description' => lang('featured-badge-bg-color-desc'),
                'type' => 'text',
                'value' => 'rgba(255, 0, 0, 0.5)'
            ),

            'featured-badge-text-color' => array(
                'title' => lang('featured-badge-text-color'),
                'description' => lang('featured-badge-text-color-desc'),
                'type' => 'text',
                'value' => '#FFCCCC'
            ),
            'people-dashboard-menu-link' => array(
                'type' => 'boolean',
                'title' => lang('show-people-dashboard-link'),
                'description' => lang('show-people-dashboard-link-desc'),
                'value' => false
            ),
            'people-explorer-menu-link' => array(
                'type' => 'boolean',
                'title' => lang('show-people-explorer-link'),
                'description' => lang('show-people-explorer-link-desc'),
                'value' => true
            ),
            'people-footer-menu-link' => array(
                'type' => 'boolean',
                'title' => lang('show-people-footer-link'),
                'description' => lang('show-people-footer-link-desc'),
                'value' => false
            ),
            'people-list-limit' => array(
                'type' => 'text',
                'title' => lang('people-limit-per-page'),
                'description' => lang('people-limit-per-page-desc'),
                'value' => 12
            ),
            'user-name-option' => array(
                'type' => 'boolean',
                'title' => lang('enable-username-display'),
                'description' => lang('enable-username-display-desc'),
                'value' => false
            )
        )
    )
);