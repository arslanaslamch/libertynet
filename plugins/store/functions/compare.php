<?php

//check if the product is in compared session
function is_been_compared($pid){
    $ccp = session_get("compared-products",array());
    //current compared products
    if($pid && in_array($pid,$ccp)) return true;
    return false;
}

function compare_mark($item,$key){
	if($item[$key]){
		return  "<span style='font-size:20px;text-align:center;' class='text-success'><i class='ion-checkmark-circled'></i></span>";
	}
	return "<span style='font-size:20px;text-align:center;' class='text-danger'><i class='ion-close-circled'></i></span>";
}