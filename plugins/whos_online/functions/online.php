<?php

function get_online_members()
{
    $time = time() - 50;
    $q = db()->query("SELECT * FROM users WHERE online_time > {$time}");
    $result = array();
    if($q->num_rows > 0){
        while($r = $q->fetch_assoc()){
            $result[] = $r['id'];
        }
    };
    //demo_auto_update_online();
    return $result;
}

/**  just for my demo websites***/
/*function demo_auto_update_online(){
    $q = db()->query("SELECT id FROM users WHERE auto_generate != '0' ORDER BY rand() Limit 35");
    while($r = $q->fetch_assoc()){
        $id = $r['id'];
        $time = time();
        db()->query("UPDATE users SET online_time='{$time}' WHERE id='{$id}'");
    }

}*/
/*** End **/