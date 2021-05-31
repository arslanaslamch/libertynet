<?php

function return_query_array($query){
    $data = db()->query($query);
    //echo db()->error;
   /* echo $query;
    die();*/
    $result =  array();
    if(db()->error){
        return $result;
    }
    if($data->num_rows > 0){
        while($a = $data->fetch_assoc()){
            $result[] = $a;
        }
    }
    return $result;
}