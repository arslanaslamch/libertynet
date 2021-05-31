<?php

function covid_upgrade_database() {
    register_site_page('covid-page', array('title' => 'Covid19 Page', 'column_type' => ONE_COLUMN_LAYOUT, function() {
        Widget::add(null, 'covid-page', 'content', 'middle');
    }));
}