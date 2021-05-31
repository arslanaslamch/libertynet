<?php
load_functions('matchmaker::matchmaker');

register_asset('matchmaker::css/matchmaker.css');

register_asset('matchmaker::js/matchmaker.js');

register_pager('matchmaker', array(
    'as' => 'matchmaker',
    'use' => 'matchmaker::matchmaker@matchmaker_pager',
    'filter' => 'auth',
));

register_pager('matchmaker/likes', array(
    'as' => 'matchmaker-likes',
    'use' => 'matchmaker::matchmaker@likes_pager',
    'filter' => 'auth',
));

register_pager('matchmaker/matches', array(
    'as' => 'matchmaker-matches',
    'use' => 'matchmaker::matchmaker@matches_pager',
    'filter' => 'auth',
));

register_pager('matchmaker/like/{id}', [
    'use' => 'matchmaker::matchmaker@like_encounter_pager',
    'filter' => 'auth',
])->where(['id' => '[0-9]+']);

register_hook("display.notification", function ($notification) {
    if ($notification['type'] == 'matchmaker.liked') {
        return view("matchmaker::notifications/liked", ['notification' => $notification]);
    }
    if ($notification['type'] == 'matchmaker.matched') {
        return view("matchmaker::notifications/matched", ['notification' => $notification]);
    }
});

add_menu_location('matchmaker-menu', 'matchmaker::matchmaker-menu');

add_available_menu('matchmaker::matchmaker', 'matchmaker', '[icon]');
