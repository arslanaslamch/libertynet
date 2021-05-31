<?php
return array(
    'site-other-settings' => array(
        'title' => 'Credit Bonus',
        'description' => "Allocates unit of credit for signup",
        'settings' => array(
            'creditgift-credit-worth' => array(
                'type' => 'text',
                'title' => 'Credit  Value',
                'description' => 'Set the exchange rate(value of which if multiply by credit will be equivalent to default currency),
            e.g if default currency is dollar and you input 100,100credit will be equal to 1 dollar.Default is 1,which make 1 credit equal to 1 dollar for example.',
                'value' => 1
            ),
            'creditgift-signup-bonus' => array(
                'type' => 'text',
                'title' => 'Signup  bonus',
                'description' => 'Allocate unit of credit for signup',
                'value' => ''
            ),
            'creditgift-addfriend-bonus' => array(
                'type' => 'text',
                'title' => 'Add friend Bonus ',
                'description' => 'Allocates unit of credit to members for adding friends',
                'value' => ''
            ),

            'creditgift-videoupload-bonus' => array(
                'type' => 'text',
                'title' => 'Video Upload Bonus',
                'description' => 'Allocates unit of credit to members for uploading video',
                'value' => ''
            ),

            'creditgift-photoupload-bonus' => array(
                'type' => 'text',
                'title' => 'Photo Upload Bonus',
                'description' => 'Insert your wordpress database name',
                'value' => ''
            ),

            'creditgift-like-bonus' => array(
                'type' => 'text',
                'title' => 'Like Bonus',
                'description' => 'Allocates unit of credit to members for likes',
                'value' => ''
            ),
            'creditgift-comment-bonus' => array(
                'type' => 'text',
                'title' => 'Comment Bonus',
                'description' => 'Allocates unit of credit to members for comments',
                'value' => ''
            ),
            'creditgift-share-bonus' => array(
                'type' => 'text',
                'title' => 'Share Bonus',
                'description' => 'Allocates unit of credit to members for shares',
                'value' => ''
            ),

        )
    )
);