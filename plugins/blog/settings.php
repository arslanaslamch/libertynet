<?php
return array(
    'site-other-settings' => array(
        'title' => 'Blog',
        'description' => '',
        'settings' => array(
            'allow-members-create-blog' => array(
                'type' => 'boolean',
                'title' => lang('blog::allow-member-to-create-blog'),
                'description' => lang('blog::allow-member-to-create-blog-desc'),
                'value' => 1,
            ),
            'blog-list-limit' => array(
                'type' => 'text',
                'title' => 'Blog Listing Per Page',
                'description' => 'Set your limit per page listing of blogs',
                'value' => 12
            )
        )
    )
);