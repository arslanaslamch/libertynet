<?php
$arr =  array(
    'title' => 'Mail Automation',
    'description' => '',
    'settings' => array(
        'enable-mail-hightlights' => array(
            'type' => 'boolean',
            'title' => lang('mailautomation::enable-mail-hightlights'),
            'description'=>lang('mailautomation::enable-mail-hightlights-desc'),
            'value' => 1,
        ),
        'number-of-users-to-send-hightlights' => array(
            'type' => 'text',
            'title' => lang('mailautomation::number-of-users-to-send-hightlights-to'),
            'description'=>lang('mailautomation::number-of-users-to-send-hightlights-to-desc'),
            'value' => 50,
        ),
        'number-of-posts-highlights' => array(
            'type' => 'text',
            'title' => lang('mailautomation::number-of-posts-highlights'),
            'description'=>lang('mailautomation::number-of-posts-highlights-desc'),
            'value' => 7,
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
 