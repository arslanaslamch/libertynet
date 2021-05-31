<?php
load_functions('whos_online::online');
register_asset("whos_online::css/online.css");
register_asset("whos_online::js/online.js");

register_pager(
    'members/online',array(
    'as'=>'online-members',
    'use'=>'whos_online::online@index_pager',
    'filter'=>'auth'
));

register_pager('online/members/check',array(
    'as'=>'online-members-check',
    'use'=>'whos_online::online@check_pager',
    'filter'=>'auth'
));