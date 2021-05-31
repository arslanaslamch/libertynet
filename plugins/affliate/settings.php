<?php
$arr =  array(
    'title' => 'Affiliate Plugin',
    'description' => lang("affliate::affliate-plugin-settings"),
    'settings' => array(
        'auto-approved-affliate-account' => array(
            'type' => 'boolean',
            'title' => lang('affliate::auto-approve-account'),
            'description'=> lang('affliates::auto-approve-account-desc'),
            'value' => 1,
        ),
        'auto-approved-commission-earnings' => array(
            'type' => 'boolean',
            'title' => lang('affliate::auto-approve-comission-earnings'),
            'description'=> lang('affliate::auto-approve-comission-earnings-desc'),
            'value' => 0,
        ),
        'enable-aff-membership' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-commission-on-membership'),
            'description'=> lang('affliate::enable-commission-on-membership-desc'),
            'value' => 1,
        ),
        'enable-aff-ads' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-commission-ads'),
            'description'=> lang('affliate::enable-commission-ads-desc'),
            'value' => 1,
        ),
        'enable-aff-booster' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-commission-booster'),
            'description'=> lang('affliate::enable-commission-booster-desc'),
            'value' => 1,
        ),
        'enable-aff-product-purchase' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-aff-product-purchase'),
            'description'=> lang('affliate::enable-aff-product-purchase-desc'),
            'value' => 1,
        ),
        'enable-aff-property' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-aff-property'),
            'description'=> lang('affliate::enable-aff-property-desc'),
            'value' => 1,
        ),
        'enable-aff-spotlight' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-aff-spotlight'),
            'description'=> lang('affliate::enable-aff-spotlight-desc'),
            'value' => 1,
        ),
        'affliate-to-menu' => array(
            'type' => 'boolean',
            'title' => lang('affliate::enable-top-menu'),
            'description'=> '',
            'value' => 1,
        ),
        'number-of-commision-levels' => array(
            'type' => 'text',
            'title' => lang('affliate::number-of-commision-levels'),
            'description'=> '',
            'value' => 5,
        ),

        'aff-conversion-rate' => array(
            'type' => 'text',
            'title' => lang('affliate::points-to-monetary-value-conversion'),
            'description'=> lang("affliate::conversion-rate-desc"),
            'value' => 1,
        ),
        'aff-minimum-points-request' => array(
            'type' => 'text',
            'title' => lang('affliate::minimum-request-points'),
            'description'=> lang("affliate::minimum-request-points-desc"),
            'value' => 1,
        ),
        'aff-maximum-points-request' => array(
            'type' => 'text',
            'title' => lang('affliate::max-request-points'),
            'description'=> lang("affliate::max-request-points-desc"),
            'value' => 1000,
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
 