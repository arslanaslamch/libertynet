<?php

function getMyStores($uid = null){
    $uid = ($uid) ? $uid : get_userid();
    if(segment(0) == 'admincp'){
        //this is admin modifying, then 
        return paginate("SELECT * FROM lp_stores",'all');
    }
    return paginate("SELECT * FROM lp_stores WHERE user_id='{$uid}'",'all');
}