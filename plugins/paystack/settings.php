<?php
return array(
    'integrations' => array(
        'title' => lang('paystack::paystack'),
        'description' => lang("paystack::paystack-setting-description"),
        'settings' => array(
            'enable-paystack' => array(
                'type' => 'boolean',
                'title' => lang('enable-paystack'),
                'description' => lang('enable-paystack-desc'),
                'value' => 0,
            ),
            'paystack-public-key' => array(
                'title' => lang("paystack::api-public-key"),
                'description' => lang("paystack::api-public-key-desc"),
                'type' => 'text',
                'value' => ''
            ),
            'paystack-secret-key' => array(
                'title' => lang("paystack::api-secret-key"),
                'description' => lang("paystack::api-secret-key-desc"),
                'type' => 'text',
                'value' => ''
            ),
        ),
    )
);