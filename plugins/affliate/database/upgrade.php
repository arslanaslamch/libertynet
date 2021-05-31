<?php

function affliate_upgrade_database() {
    /*register_site_page('affliate-home', array('title' => 'affliate::affliate-home', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'affliate-home', 'content', 'middle');
        Widget::add(null, 'affliate-home', 'plugin::affliate|menu', 'right');
    });*/
    register_site_page('affliate-home', array('title' => 'affliate::affliate-home', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-home', 'content', 'middle');
    });
    register_site_page('affliate-links', array('title' => 'affliate::affliate-links', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-links', 'content', 'middle');
    });
    register_site_page('affliate-network', array('title' => 'affliate::affliate-network', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-network', 'content', 'middle');
    });
    register_site_page('commision-rules', array('title' => 'affliate::commision-rules', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'commision-rules', 'content', 'middle');
    });
    register_site_page('affliate-code', array('title' => 'affliate::affliate-code', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-code', 'content', 'middle');
    });
    register_site_page('affliate-link-tracking', array('title' => 'affliate::affliate-link-tracking', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-link-tracking', 'content', 'middle');
    });
    register_site_page('affliate-commission-tracking', array('title' => 'affliate::affliate-commission-tracking', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-commission-tracking', 'content', 'middle');
    });
    register_site_page('affliate-stats', array('title' => 'affliate::affliate-stats', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'affliate-stats', 'content', 'middle');
    });
    register_site_page('aff-requests', array('title' => 'affliate::aff-requests', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'aff-requests', 'content', 'middle');
    });


    //echo db()->error;die();

    /*register_site_page('affliate-links', array('title' => 'affliate::affliate-links', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'affliate-links', 'content', 'middle');
        Widget::add(null, 'affliate-links', 'plugin::affliate|menu', 'right');
    });
    register_site_page('affliate-network', array('title' => 'affliate::affliate-network', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'affliate-network', 'content', 'middle');
        Widget::add(null, 'affliate-network', 'plugin::affliate|menu', 'right');
    });
    register_site_page('commision-rules', array('title' => 'affliate::commision-rules', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'commision-rules', 'content', 'middle');
        Widget::add(null, 'commision-rules', 'plugin::affliate|menu', 'right');
    });
    register_site_page('affliate-code', array('title' => 'affliate::affliate-code', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'affliate-code', 'content', 'middle');
        Widget::add(null, 'affliate-code', 'plugin::affliate|menu', 'right');
    });
    register_site_page('affliate-link-tracking', array('title' => 'affliate::affliate-link-tracking', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'affliate-link-tracking', 'content', 'middle');
        Widget::add(null, 'affliate-link-tracking', 'plugin::affliate|menu', 'right');
    });
    register_site_page('affliate-commission-tracking', array('title' => 'affliate::affliate-commission-tracking', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'affliate-commission-tracking', 'content', 'middle');
        Widget::add(null, 'affliate-commission-tracking', 'plugin::affliate|menu', 'right');
    });*/

}