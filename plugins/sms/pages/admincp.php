<?php

function all_users_pager($app){
    get_menu('admin-menu',"plugins")->setActive();
    get_menu('admin-menu',"plugins")->findMenu('sms-all-users')->setActive();
    $app->setTitle(lang('sms::all-users'));
    $term = input('term',null);
    $lists = smsGetAllUsers($term);
    return $app->render(view('sms::admincp/all-users',array('lists'=>$lists)));
}

function send_single_pager($app){
    $msg = input('msg',null);
    $ph = input('n',null);
    $t = input('t','send');
    switch($t){
        case 'edit':
            $uid = input('uid');
            db()->query("UPDATE sms_verification SET phone_num='{$ph}' WHERE user_id='{$uid}'");
            return true;
            break;
        case 'send':
            if(!$msg){
                $view = view("sms::alert",array('message'=>lang("sms::message-cannot-be-empty")));
                return json_encode(array('view'=>$view));
            }
            send_sms($msg,$ph);
            $view = view("sms::alert",array('message'=>lang("sms::sms-successful")));
            return json_encode(array('view'=>$view));
            break;
    }
    return false;
}