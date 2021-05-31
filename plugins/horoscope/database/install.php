<?php
function horoscope_install_database()
{
    register_site_page("horoscope", array('title' => lang('horoscope::daily-horoscope'), 'column_type' => TOP_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'horoscope', 'content', 'middle');
        Menu::saveMenu('main-menu', 'daily::horoscope', 'horoscope', 'manual', 1, 'ion-ios-moon');
    });
}
