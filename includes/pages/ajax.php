<?php
function check_pager($app) {
	header('Content-Type: text/json');
	CSRFProtection::validate(false);
	$data = input('data', array(), false);
    foreach ($data as $index => $message) {
        $json = json_decode($message, true);
        $message = json_last_error() == JSON_ERROR_NONE ? $json : $message;
        AjaxPusher::onMessage($message, $index);
    }
    $send_notification = input('sw') ? true : false;
    $seen_update = input('sw') ? false : true;
    $seen_update_sw = input('sw') ? true : false;
    $pushes = AjaxPusher::result(get_userid(), $send_notification, $seen_update, $seen_update_sw);
	$pushes = fire_hook('ajax.push.check.result', $pushes, array($pushes));
    return $pushes;
}