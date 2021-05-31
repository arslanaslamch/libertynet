<?php

function like_item_pager($app) {
	CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'likes' => 0,
        'dislikes' => 0,
        'react_button' => '',
        'reacts' => '',
        'reactions' => '',
    );
    $type = input('type');
    $type_id = input('type_id');
    $hide_icon = input('hide_icon');
    $hide_count = input('hide_count');
	$action = input('action', 'like');
	if($action == 'like') {
		$action_result =  like_item($type, $type_id, input('w'));
	} else {
        $action_result = dislike_item($type, $type_id, input('w'));
	}
	if($action_result['status']) {
        $result['status'] = 1;
    }
    $result['likes'] = count_likes($type, $type_id);
    $result['dislikes'] = count_likes($type, $type_id, 0);
    $result['react_button'] = view("like::react-img", array('type' => $type, 'type_id' => $type_id, 'hide_icon' => $hide_icon, 'hide_count' => $hide_count));
    $result['reacts'] = view('like::reacts', array('type' => $type, 'type_id' => $type_id));
    $result['reactions'] = view('like::reactions', array('type' => $type, 'type_id' => $type_id));
    $result = fire_hook('like.item.result', $result, array($type, $type_id, $hide_icon, $hide_count, $action));
    $response = json_encode($result);
	return $response;
}

function load_people_pager($app) {
	CSRFProtection::validate(false);
	$action = input('action', 'like');
	$type = input('type');
	$type_id = input('type_id');
	if($action == 3) {
		return view('like::reactors', array('type' => $type, 'type_id' => $type_id));
	} else {
		return view('like::people', array('likes' => get_likes_people($type, $type_id, $action)));
	}
}

function react_pager($app) {
	CSRFProtection::validate(false);
	$result = array(
	    'status' => 0,
        'likes' => 0,
        'dislikes' => 0,
	    'react_button' => '',
	    'reacts' => '',
	    'reactions' => '',
    );
	$type = input('type');
	$type_id = input('type_id');
	$code = input('code');
    $hide_icon = input('hide_icon');
    $hide_count = input('hide_count');
	if(like_react($type, $type_id, $code)) {
        $result['status'] = 1;
    }
    $result['likes'] = count_likes($type, $type_id);
    $result['dislikes'] = count_likes($type, $type_id, 0);
    $result['react_button'] = view("like::react-img", array('type' => $type, 'type_id' => $type_id, 'hide_icon' => $hide_icon, 'hide_count' => $hide_count));
    $result['reacts'] = view('like::reacts', array('type' => $type, 'type_id' => $type_id));
    $result['reactions'] = view('like::reactions', array('type' => $type, 'type_id' => $type_id));
	$response = json_encode($result);
	return $response;
}