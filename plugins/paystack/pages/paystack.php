<?php
function paystack_response_pager(){
    $url = "";
    $response = input('response');
    $response = json_decode($response, true);
    $status = $response['status'];
    if ($status == "success"){
        $type = input('type'); $id = input('id'); $returnUrl = input('return');
        $user_id = get_userid();
        fire_hook("payment.aff", null, array($type, $id, $user_id));
         $url = $returnUrl."?id=".$id;
    } else {
        $cancelUrl = input('cancel');
        $id = input('id');
        $url = $cancelUrl."?id=".$id;
    }
    return json_encode(array('redirect' => $url));
}