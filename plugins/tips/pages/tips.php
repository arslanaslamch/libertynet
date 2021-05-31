<?php
function tips_pager()
{
    if(!is_loggedIn()) return false;
    $action = input('action');
    $last = input('u');
    //return segment(0);
    if($action == 'feed'){
        if (!tip_feed_passed() && ($last == '/' || $last == 'feed')) {
            return json_encode(
                array(
                    'result'=>1,
                    '.navbar-brand ' => array(
                        'position'=>1,
                        'title' => lang('tips::hi-welcome',array('name'=>get_first_name())),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::welcome-to',array('title'=>config('site_title'))),
                            'last' => 0,
                            'next'=>'#friend-request-dropdown-container',
                            'ex'=>'.navbar-brand',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),
                    '#friend-request-dropdown-container' => array(
                        'position'=>2,
                        'title' => lang('tips::friend-requests'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::friend-requests-c'),
                            'last' => 0,
                            'next'=>'#notification-dropdown-container',
                            'ex'=>'#friend-request-dropdown-container',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),
                    '#notification-dropdown-container' => array(
                        'position'=>3,
                        'title' => lang('tips::notification'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::notifications-content'),
                            'last' => 0,
                            'next'=>'#feed-editor-image-selector',
                            'ex'=>'#notification-dropdown-container',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),
                    '#feed-editor-image-selector' => array(
                        'position'=>4,
                        'title' => lang('tips::add-images'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::add-images-to-post'),
                            'last' => 0,
                            'next'=>'#feed-editor-check-in-input-selector',
                            'ex'=>'#feed-editor-image-selector',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),
                    '#feed-editor-check-in-input-selector' => array(
                        'position'=>5,
                        'title' => lang('tips::add-location-to-your-post'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::add-location-to-your-post-desc'),
                            'last' => 0,
                            'next'=>'.onlines-container .friend',
                            'ex'=>'#feed-editor-check-in-input-selector',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),
                    '.onlines-container .friend' => array(
                        'position'=>6,
                        'title' => lang('tips::online-friends'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::your-online-friends-c'),
                            'last' => 0,
                            'next'=>'#header-account-menu',
                            'ex'=>'.onlines-container .friend',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),
                    '#header-account-menu' => array(
                        'position'=>7,
                        'title' => lang('tips::account-menu'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::account-menu-c'),
                            'last' => 1,
                            'next'=>'null',
                            'ex'=>'#header-account-menu',
                            'loc'=>'feed',
                            'place'=>'bottom',
                        ))
                    ),


                ));
        }
    }

    if($action == 'profile'){
        $username = get_user_data('username');
        if (!tip_profile_passed() && $username == $last) {
            return json_encode(
                array(
                    'result'=>1,
                    '.design-button' => array(
                        'position'=>1,
                        'title' => lang('tips::design-your-profile'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::design-your-profile-content'),
                            'last' => 0,
                            'next'=>'.profile-actions .dropdown-button',
                            'ex'=>'.design-button',
                            'loc'=>'profile',
                            'place'=>'bottom',
                        ))
                    ),
                    '.profile-actions .dropdown-button' => array(
                        'position'=>2,
                        'title' => lang('tips::profile-actions'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::profile-actions-c'),
                            'last' => 0,
                            'next'=>'#profile-menu .dropdown',
                            'ex'=>'.profile-actions .dropdown-button',
                            'loc'=>'profile',
                            'place'=>'bottom',
                        ))
                    ),
                    '#profile-menu .dropdown' => array(
                        'position'=>3,
                        'title' => lang('tips::more-profile-menu'),
                        'v' => view('tips::box', array(
                            'c' => lang('tips::more-profile-menu-c'),
                            'last' => 1,
                            'next'=>'null',
                            'ex'=>'#profile-menu .dropdown',
                            'loc'=>'profile',
                            'place'=>'bottom',
                        ))
                    )
                )
            );
        }
    }
    //return json_encode(array("error-code"=>array('')));
    return json_encode(array("result"=>0));
}

function tips_update_pager($app){
    $location = trim(input('loc'));
    $uid = get_userid();
    if(my_tips_exist()){
        //just update
        $q = "UPDATE tips SET `{$location}` = '1' WHERE user_id='{$uid}'";
        db()->query($q);
        echo $q;die();
    }else{
        //add new one then update
        db()->query("INSERT INTO tips(user_id,feed,profile) VALUES ({$uid},0,0)");
        db()->query("UPDATE tips SET `$location` = '1' WHERE user_id='{$uid}'");
    }
}