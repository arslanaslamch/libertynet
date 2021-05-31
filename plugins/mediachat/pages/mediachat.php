<?php

function mediachat_call_init($app) {
    $is_caller = input('is_caller');

	if($is_caller) {
        $type = input('type', 'video');
        $receiver_id = input('user_id');
        $make = MediaChat::makeCall($receiver_id, $type);
        $status = $make['status'];
        $message = $make['message'];
        $call = $make['data'];
        $user = find_user($call['receiver_id']);
	} else {
        $id = input('id');
		$receive = MediaChat::receiveCall($id);
        $status = $receive['status'];
        $message = $receive['message'];
		$call = $receive['data'];
        $user = find_user($call['caller_id']);
	}

	$ice_servers = Mediachat::getICEServers(null, 1, true);
    $ice_transport_policy = config('mediachat-ice-transport-policy', 'all');
    return view('mediachat::call', array(
        'id' => $call['id'],
        'type' => $call['type'],
        'is_caller' => $call['caller_id'] == $user['id'] ? 0 : 1,
        'session_description' => $call['session_description'] ? $call['session_description'] : 'null',
        'ice_servers' => json_encode($ice_servers),
        'ice_transport_policy' => $ice_transport_policy,
        'user_name' => get_user_name($user),
        'user_avatar' => get_avatar(200, $user),
        'status' => $status,
        'message' => $message
    ));
}

function mediachat_call_end($app) {
    pusher()->reset('mediachat.call.session.description');
    pusher()->reset('mediachat.call.ice.candidate');
    pusher()->reset('mediachat.call.ended');
	$id = input('id');
	$status = $id ? MediaChat::endCall($id) : false;
	return json_encode(array('status' => $status));
}

function mediachat_conference_init($app)
{
    $is_caller = input('is_caller');
    $id = input('id');
    $other_users = input('other_users', []);
    if ($is_caller) {
        $receiver_id = input('user_id');
        $user = find_user($receiver_id);
    } else {
        $caller_id = input('user_id');
        $receiver_id = get_userid();
        $user = find_user($caller_id);
    }
    $ice_servers = Mediachat::getICEServers(null, 1, true);
    $ice_transport_policy = config('mediachat-ice-transport-policy', 'all');

    return view('mediachat::conference', array(
        'chat_id' => $id,
        'is_caller' => $is_caller ? 0 : 1,
        'ice_servers' => json_encode($ice_servers),
        'ice_transport_policy' => $ice_transport_policy,
        'user_name' => get_user_name($user),
        'user_avatar' => get_avatar(200, $user),
        'user_id' => $user['id'],
        'other_users' => $other_users,
    ));
}

function mediachat_friends($app)
{
    return $app->render(view("mediachat::call_friends"));
}
