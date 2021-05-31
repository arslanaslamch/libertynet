<?php
return array(
    'site-other-settings' => array(
        'title' => lang('mediachat::mediachat'),
        'description' => lang("mediachat::mediachat-setting-description"),
        'settings' => array(
            'mediachat-ice-transport-policy' => array(
                'type' => 'selection',
                'title' => lang('mediachat::ice-transport-policy'),
                'description' => lang('mediachat::ice-transport-policy-desc'),
                'value' => 'all',
                'data' => array(
                    'all' => lang('mediachat::all'),
                    'relay' => lang('mediachat::relay')
                )
            ),
            'mediachat-connection-timeout' => array(
                'type' => 'text',
                'title' => lang('mediachat::connection-timeout'),
                'description' => lang('mediachat::connection-timeout-desc'),
                'value' => 60
            )
        )
    )
);