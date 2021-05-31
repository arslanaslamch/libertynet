<?php
function index_pager($app){
    $app->setTitle(lang("whos_online::whos_online"));
   return $app->render(view('whos_online::members'));
}
function check_pager($app){
    return json_encode(array('v'=>view('whos_online::online',array('limit'=>10))));
}
