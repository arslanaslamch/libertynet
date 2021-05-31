<?php

function rating_index_pager($app){
    $type = input('type');
    $type_id = input('type_id');
    $rating = input('rating');
    $uid = get_userid();
    //if user has rated before
    //update his new ratings
   if(hasRated($type,$type_id)){
       db()->query("UPDATE lp_ratings SET rating='{$rating}' WHERE type='{$type}' AND type_id='{$type_id}' AND user_id='{$uid}'");
   }else{
       //insert new
       rateItem($type,$type_id,$rating);
   }
    return true;

}