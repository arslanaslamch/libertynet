<?php
return array(
    'integrations' => array(
        'title' => 'Firebase',
        'description' => lang("firebase-setting-description"),
        'id' => 'firebase-settings',
        'settings' => array(
            'firebase-api-key' => array(
                'title' => lang('firebase-api-key'),
                'description' => lang('firebase-api-key-desc'),
                'type' => 'text',
                'value' => ''
            ),
            'firebase-api-key-legacy' => array(
                'title' => lang('firebase-api-key-legacy'),
                'description' => lang('firebase-api-key-legacy-desc'),
                'type' => 'text',
                'value' => ''
            ),
            'firebase-auth-domain' => array(
                'title' => lang('firebase-auth-domain'),
                'description' => lang('firebase-auth-domain-desc'),
                'type' => 'text',
                'value' => ''
            ),
            'firebase-database-url' => array(
                'title' => lang('firebase-database-url'),
                'description' => lang('firebase-database-url-desc'),
                'type' => 'text',
                'value' => ''
            ),
            'firebase-project-id' => array(
                'title' => lang('firebase-project-id'),
                'description' => lang('firebase-project-id-desc'),
                'type' => 'text',
                'value' => ''

            ),
            'firebase-storage-bucket' => array(
                'title' => lang('firebase-storage-bucket'),
                'description' => lang('firebase-storage-bucket-desc'),
                'type' => 'text',
                'value' => ''
            ),
            'firebase-messaging-sender-id' => array(
                'title' => lang('firebase-messaging-sender-id'),
                'description' => lang('firebase-messaging-sender-id-desc'),
                'type' => 'text',
                'value' => ''
            ),
            'firebase-public-vapid-key' => array(
                'title' => lang('firebase-public-vapid-key'),
                'description' => lang('firebase-public-vapid-key-desc'),
                'type' => 'text',
                'value' => ''
            )
        )
    )
);