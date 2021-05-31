<?php

function hasRated($type,$typeid){
    $userid = get_userid();
    $sql = db()->query("SELECT * FROM lp_ratings WHERE type='{$type}' AND type_id='{$typeid}' AND user_id='{$userid}'");
    if($sql->num_rows > 0) return true;
    return false;
}

function rateItem($type,$type_id,$rating){
    $userid = get_userid();
    $sql = "INSERT INTO lp_ratings(type,type_id,user_id,rating) VALUES ('{$type}','{$type_id}','{$userid}','{$rating}')";
    db()->query($sql);
    return true;
}

function getUserRating($type,$typeid,$userid= null){
    $userid = ($userid) ? $userid : get_userid();
    $sql = db()->query("SELECT rating FROM lp_ratings WHERE type='{$type}' AND type_id='{$typeid}' AND user_id='{$userid}'");
    if($sql->num_rows > 0){
        $r = $sql->fetch_assoc();
        return $r['rating'];
    }
    return false;
}

function getItemRatings($type,$typeid){
    $sql = db()->query("SELECT * FROM lp_ratings WHERE type='{$type}' AND type_id='{$typeid}'");
    $response = array();
    if($sql->num_rows > 0){
       while($r = $sql->fetch_assoc()){
           $response[] = $r['rating'];
       }
        return $response;
    }
    return $response;
}

function getAverageRating($ratings_arr){
    $average = 0;
    if($ratings_arr){
        $count = count($ratings_arr);
        $total = array_sum($ratings_arr);
        $average= ceil($total/$count);
    }
    return $average;
}
