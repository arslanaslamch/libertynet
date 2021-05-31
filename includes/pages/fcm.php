<?php

function send($app) {
    $data = input('data', array());
    foreach ($data as $index => $message) {
        $json = json_decode($message, true);
        $message = json_last_error() == JSON_ERROR_NONE ? $json : $message;
        FCMPusher::onMessage($message, $index);
    }
}

function token_update($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => ''
    );
    $user_id = get_userid();
    $token = input('token');
    $updated = fcm_token_add($token, $user_id);
    if($updated) {
        $result['status'] = 1;
    }
    $response = json_encode($result);
    return $response;
}