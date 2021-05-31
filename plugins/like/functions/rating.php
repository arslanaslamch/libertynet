<?php
function has_rated($type, $typeId, $user_id = null) {
    $user_id = isset($user_id) ? $user_id : get_userid();
    $db = db();
    $result = $db->query("SELECT COUNT(id) FROM ratings WHERE user_id = ".$user_id." AND `type` ='{$type}' AND type_id = ".$typeId);
    $check = $result->fetch_row();
    return $check[0] ? true : false;
}

function delete_rating($type, $typeId, $user_id = null) {
    $user_id = isset($user_id) ? $user_id : get_userid();
    $db = db();
    $db->query("DELETE FROM ratings WHERE user_id = ".$user_id." AND `type` ='{$type}' AND type_id = ".$typeId);
    return true;
}

function add_rating($rate, $type, $typeId) {
    $db = db();
    $user_id = get_userid();
    $rating = "star_".$rate;
    if(has_rated($type, $typeId)) {
        delete_rating($type, $typeId);
    }
    $db->query("INSERT INTO ratings (user_id,`type`,type_id,$rating) VALUES ('$user_id', '$type','$typeId','$rate')");
    $id = $db->insert_id;
    fire_hook('rate.added', $type, array($typeId, $rate, $id));
    return $id;
}

function get_ratings($type, $typeId) {
    $db = db();
    $star_1 = $db->query("SELECT star_1 ,SUM(star_1) AS star_1 FROM ratings WHERE `type` ='{$type}' AND type_id='$typeId'");
    $star_1 = $star_1->fetch_assoc();
    $star1 = $star_1['star_1'] * 1;
    $star_2 = $db->query("SELECT star_2 ,SUM(star_2) AS star_2 FROM ratings WHERE `type` ='{$type}' AND type_id='$typeId'");
    $star_2 = $star_2->fetch_assoc();
    $star2 = $star_2['star_2'] * 2;
    $star_3 = $db->query("SELECT star_3 ,SUM(star_3) AS star_3 FROM ratings WHERE `type` ='{$type}' AND type_id='$typeId'");
    $star_3 = $star_3->fetch_assoc();
    $star3 = $star_3['star_3'] * 3;
    $star_4 = $db->query("SELECT star_4 ,SUM(star_4) AS star_4 FROM ratings WHERE `type` ='{$type}' AND type_id='$typeId'");
    $star_4 = $star_4->fetch_assoc();
    $star4 = $star_4['star_4'] * 4;
    $star_5 = $db->query("SELECT star_5 ,SUM(star_5) AS star_5 FROM ratings WHERE `type` ='{$type}' AND type_id='$typeId'");
    $star_5 = $star_5->fetch_assoc();
    $star5 = $star_5['star_5'] * 5;
    $star = $star1 + $star2 + $star3 + $star4 + $star5;
    $total = $star_1['star_1'] + $star_2['star_2'] + $star_3['star_3'] + $star_4['star_4'] + $star_5['star_5'];
    if($total == 0) {
        $total = 1;
    }
    $total_rate = $star / $total;
    return $total_rate;
}


function get_user_rating($type, $typeId, $userid = null) {
    $db = db();
    $user_id = isset($userid) ? $userid : get_userid();
    $query = $db->query("SELECT * FROM ratings WHERE `type` ='{$type}' AND type_id = ".$typeId." AND user_id = ".$user_id);
    $rates = $query->fetch_assoc();
    $rate = 0;
    if($rates) {
        $star_fields = array('star_1', 'star_2', 'star_3', 'star_4', 'star_5');
        foreach($rates as $field => $rate) {
            if(in_array($field, $star_fields) && $rate > 0) {
                $rate = $rate;
                break;
            }
        }
    } else {
        $rate = 0;
    }
    return $rate;
}

function rating_counts($type, $typeId) {
    $db = db();
    $record = $db->query("SELECT * FROM ratings WHERE `type` ='{$type}' AND type_id='".$typeId."'");
    return $record->num_rows;
}