<?php

function customized_ajax_pager($app){
    $action  = input('action');
    switch ($action){
        case 'assign':
            $uid = input('uid');
            $sid = input('sid');

            //update the lp_stores user_id
            //update the lp_products
            db()->query("UPDATE lp_stores SET user_id='{$uid}' WHERE s_id='{$sid}'");
            db()->query("UPDATE lp_products SET user_id='{$uid}' WHERE store_id='{$sid}'");

            break;
    }
    return true;
}