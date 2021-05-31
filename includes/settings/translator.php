<?php
return array(
    'site-settings' => array(
        'title' => lang('translator'),
        'description' => '',
        'id' => 'system-task-settings',
        'settings' => array(
            'enable-text-translation' => array(
                'type' => 'boolean',
                'title' => 'Translate Text',
                'description' => '',
                'value' => 0,
            ),
            'translator-method' => array(
                'type' => 'selection',
                'title' => lang('translator-method'),
                'description' => '',
                'value' => 'ajax',
                'data' => array(
                    'microsoft' => lang('microsoft'),
                    'google' => lang('google'),
                )
            ),
            'microsoft-translate-text-api-key-1' => array(
                'type' => 'text',
                'title' => 'Microsoft Translate Text API KEY 1',
                'description' => '',
                'value' => ''
            ),

            'microsoft-translate-text-api-key-2' => array(
                'type' => 'text',
                'title' => 'Microsoft Translate Text API KEY 2',
                'description' => '',
                'value' => ''
            ),

            'google-translate-project-id' => array(
                'type' => 'text',
                'title' => 'Google Translate Project Id',
                'description' => '',
                'value' => ''
            ),
        )
    )
);
 