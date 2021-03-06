<?php

$arr =  array(
    'title' => 'Post Booster',
    'description' => lang("booster::post-booster-settings"),
    'settings' => array(
        'display-sponsored-post-after' => array(
            'type' => 'text',
            'title' => lang('booster::enable-post-inline-booster'),
            'description' => lang('booster::enable-post-inline-booster-desc'),
            'value' => 2,
        ),
        'boost-listing-header-color' => array(
            'type' => 'text',
            'title' => lang('booster::boost-listing-header-color'),
            'description' => lang('booster::enable-post-inline-booster-desc'),
            'value' => '#233241',
        ),
        'booster-star-color' => array(
            'type' => 'text',
            'title' => lang('booster::booster-star-color'),
            'description' => lang('booster::booster-star-color-desc'),
            'value' => '#26A65B',
        )
    ));

$version = 7.1;
if (version_compare(app()->version,$version) == 1) {
    //if app()->version is greater than $version=7.1
    return array(
        'site-other-settings' => $arr
    );
} else {
    return $arr;
}