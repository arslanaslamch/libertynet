<?php
function export_upgrade_database() {
    register_site_page("data_personal_page", array('title' => 'export::download-information', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'data_personal_page', 'content', 'middle');
        Widget::add(null, 'data_personal_page', 'plugin::export|menu', 'left');
    });
}