<?php
$arr =  array(
    'title' => 'Contest Plugin',
    'description' => '',
    'settings' => array(
        'allow-members-create-contests' => array(
            'type' => 'boolean',
            'title' => lang('contest::allow-members-to-create-contests'),
            'description'=> lang('contest::allow-member-to-create-contests-desc'),
            'value' => 1,
        ),
        'enable-premium-contest' => array(
            'type' => 'boolean',
            'title' => lang('contest::enable-premium-contest'),
            'description'=> lang('contest::enable-premium-contest-desc'),
            'value' => 1,
        ),
        'default-contest-subject' => array(
            'type' => 'text',
            'title' => lang('contest::contest-default-subject'),
            'description'=> lang('contest::contest-default-subject-desc'),
            'value' => getContestsDefaultSubject(),
        ),
        'default-contest-message' => array(
            'type' => 'textarea',
            'title' => lang('contest::contest-default-message'),
            'description'=> lang('contest::contest-default-message-desc'),
            'value' => getContestsDefaultMessage(),
        ),
        'contest-enable-music-type' => array(
            'type' => 'boolean',
            'title' => lang('contest::contest-enable-music-type'),
            'description'=> lang('contest::contest-enable-music-type-desc'),
            'value' => 1,
        ),
        'contest-enable-video-type' => array(
            'type' => 'boolean',
            'title' => lang('contest::contest-enable-video-type'),
            'description'=> lang('contest::contest-enable-video-type-desc'),
            'value' => 1,
        ),
        'contest-enable-photo-type' => array(
            'type' => 'boolean',
            'title' => lang('contest::contest-enable-photo-type'),
            'description'=> lang('contest::contest-enable-photo-type-desc'),
            'value' => 1,
        ),
        'contest-enable-blog-type' => array(
            'type' => 'boolean',
            'title' => lang('contest::contest-enable-blog-type'),
            'description'=> lang('contest::contest-enable-blog-type-desc'),
            'value' => 1,
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