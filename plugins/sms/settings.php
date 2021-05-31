<?php
$arr =  array(
    'title' => 'SMS System',
    'description' => lang("sms::sms-verification-desc"),
    'settings' => array(
        'show-sms-verification-after-signup' => array(
            'type' => 'boolean',
            'title' => "Show SMS Verification after Signup",
            'description' => "You can disable this if you prefer email activation. it is enable, it will replace email verification",
            'value' => '1'
        ),
        'Allow-new-users-to-skip-process' => array(
            'type' => 'boolean',
            'title' => lang('sms::allow-members-to-skip-sms-verification'),
            'description' => lang('sms::allow-members-to-skip-sms-verification'),
            'value' => '1'
        ),
        'allow-users-to-login-with-mobile-number' => array(
            'type' => 'boolean',
            'title' => lang('sms::allow-users-to-login-with-mobile-number'),
            'description' => lang('sms::allow-users-to-login-with-mobile-number-desc'),
            'value' => '1'
        ),
        'sms-how-many-account-can-be-connnect' => array(
            'type' => 'text',
            'title' => lang('sms::sms-how-many-account-can-be-connnect'),
            'description' => lang('sms::sms-how-many-account-can-be-connnect-desc'),
            'value' => '1'
        ),
        'backup-code-limit' => array(
            'type' => 'text',
            'title' => 'Two Factor Authentication',
            'description' => 'Back up Code Limit',
            'value' => '20'
        ),
        'set-sms-verification-message-content' => array(
            'type' => 'textarea',
            'title' => lang('sms::message-content'),
            'description' => lang('sms::message-content-desc'),
            'value' => lang('sms::sms-verification-msg-content')
        ),
        'sms-invite-message' => array(
            'type' => 'textarea',
            'title' => lang('sms::invite-message-content'),
            'description' => '',
            'value' => "You have been invited to join our social network. Follow this link to sign up. Thanks".'<br/>'.url_to_pager('signup')
        ),

        'sms-from'=>array(
            'type'=>'text',
            'description'=>lang('sms::from-desc'),
            'title'=>lang('sms::from'),
            'value'=>'Lightedphp',
        ),
        'num-characters-to-send'=>array(
            'type'=>'text',
            'description'=>'',
            'title'=>lang('sms::num-characters-to-send'),
            'value'=>'5',
        ),
        'sms-gateway'=>array(
            'type'=>'selection',
            'description'=>lang('sms::select-sms-gateway'),
            'title'=>lang('sms::select-sms-gateway'),
            'value'=>'1',
            'data'=>array(
                '1'=>'Twilio',
                '2'=>'46elks',
                '3'=>'Clickatell',
                '4'=>'Quick SMS',
                '10'=>'Textlocal'

            )
        ),
        'twilio-account-id'=>array(
            'type'=>'text',
            'description'=>lang('sms::twilio-account-id-desc'),
            'title'=>lang('sms::twilio-account-id'),
            'value'=>'',
        ),
        'twilio-token-id'=>array(
            'type'=>'text',
            'description'=>lang('sms::twilio-token-id-desc'),
            'title'=>lang('sms::twilio-token-id'),
            'value'=>'',
        ),
        'from-twilio-number'=>array(
            'type'=>'text',
            'description'=>lang('sms::from-twilio-number-desc'),
            'title'=>lang('sms::from-twilio-number'),
            'value'=>'',
        ),

        '46elks_app_id'=>array(
            'type'=>'text',
            'description'=>lang('sms::46elks_app_id-desc'),
            'title'=>lang('sms::46elks_app_id'),
            'value'=>'',
        ),
        '46elks_api_password'=>array(
            'type'=>'text',
            'description'=>lang('sms::46elks_api_password-desc'),
            'title'=>lang('sms::46elks_api_password'),
            'value'=>'',
        ),
        'clickatell-username'=>array(
            'type'=>'text',
            'description'=>lang('sms::clicktall-username-desc'),
            'title'=>lang('sms::clicktall-username'),
            'value'=>'',
        ),
        'clickatell-password'=>array(
            'type'=>'text',
            'description'=>lang('sms::clicktall-password-desc'),
            'title'=>lang('sms::clicktall-password'),
            'value'=>'',
        ),
        'clickatell-app_id'=>array(
            'type'=>'text',
            'description'=>lang('sms::clickatell-app_id-desc'),
            'title'=>lang('sms::clickatell-app_id'),
            'value'=>'',
        ),
        'quicksms_username'=>array(
            'type'=>'text',
            'description'=>'',
            'title'=>lang('sms::quicksms_username'),
            'value'=>''
        ),
        'quicksms_password'=>array(
            'type'=>'text',
            'description'=>'',
            'title'=>lang('sms::quicksms_password'),
            'value'=>''
        ),
        'text-local-api-key'=>array(
            'type'=>'text',
            'description'=>'',
            'title'=>'TextLocal API key https://www.textlocal.com',
            'value'=>''
        )


    )
);

$version = 7.1;
if (version_compare(app()->version,$version) == 1) {
    //if app()->version is greater than $version=7.1
    return array(
        'site-other-settings' => $arr
    );
} else {
    return $arr;
}
 