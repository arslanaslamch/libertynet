<?php

load_functions('covid::covid');

register_asset('covid::css/covid.css');
register_asset('covid::css/dataTables.bootstrap4.min.css');

register_asset('covid::js/jquery.dataTables.min.js');
register_asset('covid::js/covid.js');

register_pager('covid19', array(
    'use' => 'covid::covid@dashboard_pager',
    'as' => 'covid-page',
));

add_available_menu('covid::covid-19-statistics', 'covid19', 'ion-ios-medkit-outline');