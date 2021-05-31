<?php

function whos_online_upgrade_database(){
    register_site_page('online-members', array('title' => 'whos_online::whos_online', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'online-members', 'content', 'middle');
        Widget::add(null, 'online-members', 'plugin::relationship|friends', 'right');
        Widget::add(null, 'online-members', 'plugin::relationship|suggestions', 'right');
        Widget::add(null, 'online-members', 'plugin::relationship|following', 'right');
        Widget::add(null, 'online-members', 'plugin::relationship|followers', 'right');

    });
}