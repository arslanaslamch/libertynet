<?php
load_functions('horoscope::horoscope');

register_asset('horoscope::css/horoscope.css');

register_asset('horoscope::js/horoscope.js');

register_pager('horoscope', array(
    'as' => 'horoscope',
    'use' => 'horoscope::horoscope@horoscope_pager'
));

add_menu_location('horoscope-menu', 'horoscope::horoscope-menu');

add_available_menu('horoscope::horoscope', 'horoscope', 'ion-ios-moon');
