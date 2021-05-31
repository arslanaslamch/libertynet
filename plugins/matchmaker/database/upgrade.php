<?php
function matchmaker_upgrade_database()
{
    register_site_page("matchmaker", array('title' => lang('matchmaker::matchmaker'), 'column_type' => TOP_TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'matchmaker', 'content', 'middle');
        Widget::add(null, 'matchmaker', 'plugin::matchmaker|menu', 'top');
        Widget::add(null, 'matchmaker', 'plugin::matchmaker|filter', 'left');
        Menu::saveMenu('main-menu', 'matchmaker::matchmaker', 'matchmaker', 'manual', 1, 'fa fa-heart');
    });

    register_site_page("matchmaker-matches", array('title' => lang('matchmaker::matchmaker-matches'), 'column_type' => TOP_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'matchmaker-matches', 'content', 'middle');
        Widget::add(null, 'matchmaker-matches', 'plugin::matchmaker|menu', 'top');
    });

    register_site_page("matchmaker-likes", array('title' => lang('matchmaker::matchmaker-likes'), 'column_type' => TOP_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'matchmaker-likes', 'content', 'middle');
        Widget::add(null, 'matchmaker-likes', 'plugin::matchmaker|menu', 'top');
    });
}
