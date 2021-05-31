<?php

function tip_feed_passed($userid=null){
   $uid = ($userid) ? $userid : get_userid();
    $q = db()->query("SELECT * FROM tips WHERE user_id='{$uid}' AND feed='1'");
    if($q->num_rows > 0) return true;
    return false;
}

function tip_profile_passed($userid=null){
   $uid = ($userid) ? $userid : get_userid();
    $q = db()->query("SELECT * FROM tips WHERE user_id='{$uid}' AND profile='1'");
    if($q->num_rows > 0) return true;
    return false;
}

function my_tips_exist($userid=null){
    $uid = ($userid) ? $userid : get_userid();
    $q = db()->query("SELECT * FROM tips WHERE user_id='{$uid}'");
    if($q->num_rows > 0) return true;
    return false;
}