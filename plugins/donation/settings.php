<?php
$arr =  array(
    'title' => 'Donation/Fundraising Plugin',
    'description' => '',
    'settings' => array(
        'allow-members-create-donations' => array(
            'type' => 'boolean',
            'title' => lang('donation::allow-members-to-create-donations'),
            'description'=> lang('donation::allow-member-to-create-donations-desc'),
            'value' => 1,
        ),

        'automatic-approve-created-donations' => array(
            'type' => 'boolean',
            'title' => lang('donation::automatic-approve-created-donations'),
            'description'=> lang('donation::automatic-approve-created-donations-desc'),
            'value' => 1,
        ),

        'donor-automatically-follow-campaign' => array(
            'type' => 'boolean',
            'title' => 'Campaign Donor Automatically Follow Campaign',
            'description'=> "If enabled, donor will receive notification when other people donate to the campaign ",
            'value' => 1,
        ),
        'donate-button-background-color' => array(
            'type' => 'color',
            'title' => 'Donate Button Background Color',
            'description'=> "Donate Button Background color and predefined button background color : Default is #297fc6",
            'value' => '#297fc6',
        ),


        'admin-donation-commision' => array(
            'type' => 'text',
            'title' => 'Admin Commssion on Donation',
            'description'=> "When value is 0, it means commssion is Disabled, 
            the value is in Percentage. i.e Set Value 10 to deduct 10% from the donated amount",
            'value' => 0,
        ),
        'donation-paypal-api-user' => array(
            'type' => 'text',
            'title' => 'Paypal API Username',
            'description'=> "get here:  https://www.paypal.com/businessexp/tools or open a support Ticket if you can not get it",
            'value' => "",
        ),
        'donation-paypal-api-password' => array(
            'type' => 'text',
            'title' => 'Paypal API Password',
            'description'=> "",
            'value' => "",
        ),
        'donation-paypal-api-signature' => array(
            'type' => 'text',
            'title' => 'Paypal API Signature',
            'description'=> "",
            'value' => "",
        ),


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