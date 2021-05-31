<?php
return array(
    'title' => lang('whos_online::whos_online'),
    'description' => 'Show Online members',
    'settings' => array(
        'limit' => array(
            'type' => 'text',
            'title' => 'Number of members to display at time',
            'description' => 'Number of members to display at time',
            'value' => 12
        )
    )
);