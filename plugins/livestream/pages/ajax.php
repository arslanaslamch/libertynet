<?php
/**
 * Livestream Ajax Page
 * @param App $app
 * @return bool|string
 */
function ajax_pager($app) {
	CSRFProtection::validate(false);
	$action = input('action');
	$result = null;
	switch($action) {
		case 'end':
            pusher()->reset('livestream.keep.alive');
            pusher()->reset('livestream.session.description');
            pusher()->reset('livestream.ice.candidate');
            pusher()->reset('livestream.member.count');
            pusher()->reset('livestream.comment');
            pusher()->reset('livestream.ended');
            $livestream_id = input('livestream_id');
		    $record = input_file('record');
            $data = Livestream::end($livestream_id, $record);
            $result = json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
		break;

		default:

		break;
	}
	return $result;
}