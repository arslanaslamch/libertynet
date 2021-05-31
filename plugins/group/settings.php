<?php
return array(
    'site-other-settings' => array(
        'title' => 'Group',
        'description' => lang("group::group-setting-description"),
        'settings' => array(
            'enable-group-posts-in-timeline' => array(
                'title' => lang('group::enable-group-posts-in-timeline'),
                'description' => lang('group::enable-group-posts-in-timeline-desc'),
                'type' => 'boolean',
                'value' => 1
            ),
            'group-list-limit' => array(
                'type' => 'text',
                'title' => 'Group Listing Per Page',
                'description' => 'Set your limit per page listing of groups',
                'value' => 12
            )
        )
    )
);
 