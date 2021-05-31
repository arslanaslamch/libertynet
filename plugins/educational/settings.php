<?php
$arr =  array(
    'title' => 'Educational/Books Plugin',
    'description' => '',
    'settings' => array(
        'allow-members-create-books' => array(
            'type' => 'boolean',
            'title' => lang('educational::allow-members-to-create-books'),
            'description'=> lang('educational::allow-member-to-create-books-desc'),
            'value' => 1,
        ),
        'books-extentions-allowed' => array(
            'type' => 'text',
            'title' => 'Ebooks Extentions Allowed on your website',
            'description'=> "Seperate each Books types by coma ',' ",
            'value' => 'pdf,docx,doc',
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