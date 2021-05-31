<?php
load_functions('mediachat::mediachat');

register_asset('mediachat::css/mediachat.css');

register_asset('js/webrtc-adapter.js');

register_asset('mediachat::js/mediachat.js');
//register_asset('mediachat::js/rooms.js');
register_asset('mediachat::js/conference.js');

register_hook('role.permissions', function($roles) {
	$roles[] = array(
		'title' => lang('mediachat::mediachat-permissions'),
		'description' => '',
		'roles' => array(
			'can-make-call' => array('title' => lang('mediachat::can-make-call'), 'value' => 1),
		)
	);
	return $roles;
});

register_hook('footer', function() {
	$html = '';
	if(is_loggedIn()) {
		$html .= view('mediachat::call_prompt');
        $html .= view('mediachat::conference_prompt');
		$html .= view('mediachat::call_form');
        $html .= view('mediachat::conference_form');
	}
	return $html;
});

register_pager('admincp/mediachat/server/list', array(
        'filter' => 'admin-auth',
        'as' => 'admin-mediachat-server-list',
        'use' => 'mediachat::admincp@server_list_page')
);

register_pager('admincp/mediachat/server/add', array(
        'filter' => 'admin-auth',
        'as' => 'admin-mediachat-server-add',
        'use' => 'mediachat::admincp@server_add_page')
);

register_pager('admincp/mediachat/server/edit', array(
        'filter' => 'admin-auth',
        'as' => 'admin-mediachat-server-edit',
        'use' => 'mediachat::admincp@server_edit_page')
);

register_pager('admincp/mediachat/server/delete', array(
        'filter' => 'admin-auth',
        'as' => 'admin-mediachat-server-delete',
        'use' => 'mediachat::admincp@server_delete_page')
);

register_pager('admincp/mediachat/action/batch', array(
        'use' => 'mediachat::admincp@mediachat_action_batch_pager',
        'filter' => 'admin-auth',
        'as' => 'admin-mediachat-server-batch-action')
);

if(user_has_permission('can-make-call')) {
	register_pager('mediachat/call/init', array(
			'as' => 'mediachat-call',
			'use' => 'mediachat::mediachat@mediachat_call_init', 'filter' => 'auth'
		)
    );

    register_pager('mediachat/conference', array(
        'as' => 'mediachat-conf',
        'use' => 'mediachat::mediachat@mediachat_conference_init', 'filter' => 'auth'
    ));

    register_pager('mediachat/friends', array(
        'as' => 'mediachat-friends',
        'use' => 'mediachat::mediachat@mediachat_friends', 'filter' => 'auth'
    ));

    /*register_pager('mediachat/rooms', ['as' => 'mediachat-room', 'use' => 'mediachat::mediachat@mediachat_rooms', 'filter' => 'auth']);*/

    register_pager('mediachat/call/end', array(
            'as' => 'mediachat-disconnect',
            'use' => 'mediachat::mediachat@mediachat_call_end', 'filter' => 'auth'
        )
    );

	register_hook('mediachat.button.call', function($user_id = 0, $type = null) {
        $html = '';
        if(user_has_permission('can-make-call', $user_id)) {
            $stream_support = Mediachat::streamSupport($user_id);
            $types = isset($type) ? array($type) : array('voice', 'video');
            foreach ($types as $type) {
                $html .= view('mediachat::call_button', array('user_id' => $user_id, 'type' => $type, 'stream_support' => $stream_support));
            }
        }
		return $html;
	});
}

register_hook('pusher.on.message', function($message, $index) {
    if(isset($message['type'])) {
        $sender_id = get_userid();
        if($sender_id) {
            if ($message['type'] === 'mediachat.can.stream') {
                $stream_support = $message['stream_support'];
                if ($stream_support && user_has_permission('can-make-call')) {
                    MediaChat::$streamSupport = true;
                    MediaChat::streamSupportUpdate();
                }
            } elseif($message['type'] == 'mediachat.call.session.description') {
                $call = MediaChat::getCall($message['id']);
                if($message['data']['type'] == 'offer') {
                    if($sender_id == $call['caller_id']) {
                        $session_description = json_encode($message['data']);
                        MediaChat::setSessionDescription($message['id'], $session_description);
                    }
                } elseif($message['data']['type'] == 'answer') {
                    if($sender_id == $call['receiver_id']) {
                        pusher()->sendMessage($call['caller_id'], 'mediachat.call.session.description', array('id' => (integer) $call['id'], 'data' => $message['data']), null, false);
                    }
                }
            } elseif($message['type'] == 'mediachat.call.ice.candidate') {
                $call = MediaChat::getCall($message['id']);
                $user_id = $call['caller_id'] == get_userid() ? $call['receiver_id'] : $call['caller_id'];
                pusher()->sendMessage((integer) $user_id, 'mediachat.call.ice.candidate', array(array('index' => (integer) $index, 'id' => (integer) $message['id'], 'data' => $message['data'])), null, false);
            } elseif ($message['type'] == 'rooms') {
                foreach (get_friends() as $user) {
                    //$user_id = isset($message['id']) ? $message['id'] : get_userid();
                    pusher()->sendMessage($user, 'rooms', $message['data']);
                }
            } elseif ($message['type'] == 'conference') {
                $user = isset($message['userId']) ? $message['userId'] : get_userid();
                pusher()->sendMessage($user, 'conference', $message['data']);
            } elseif ($message['type'] == 'conference.call') {
                $message['data']['caller'] = get_userid();
                pusher()->sendMessage($message['receiverId'], 'conference.call', $message['data']);
            } elseif ($message['type'] == 'conference.end') {
                $user = isset($message['userId']) ? $message['userId'] : get_userid();
                pusher()->sendMessage($user, 'conference.end', $message['data']);
            } elseif ($message['type'] == 'conference.ended') {
                pusher()->reset('conference.end');
                pusher()->reset('conference');
                pusher()->reset('conference.call');
                pusher()->reset('conference.ended');
            }
       }
    }
    return $message;
});

register_hook('ajax.push.result', function($pushes, $user_id = null) {
	$user_id = isset($user_id) ? $user_id : get_userid();
	if(!MediaChat::userBusy($user_id) && MediaChat::$streamSupport) {
		$pending_calls = MediaChat::userPendingCalls($user_id);
		if(isset($pending_calls[0])) {
            MediaChat::seeCall($pending_calls[0]['id']);
            $details = array(
                'id' => $pending_calls[0]['id'],
                'type' => $pending_calls[0]['type'],
                'user_name' => get_user_name($pending_calls[0]['caller_id']),
                'user_avatar' => get_avatar(200, find_user($pending_calls[0]['caller_id']))
            );
			$pushes['types']['mediachat.call'] = $details;
		}
	}

	$available_friends = MediaChat::getAvailableFriends();
    $pushes['types']['mediachat.friends.available'] = $available_friends;

	return $pushes;
});

register_hook('ajax.push.notification.api.exception', function ($result, $type, $user_id, $detail, $message) {
    if ($type === 'mediachat.call.session.description') {
        $result[0] = true;

        $user = find_user($user_id);

        $key = md5(mt_rand(0, 9999).time().mt_rand(0, 9999).get_userid().mt_rand(0, 9999));
        set_cacheForever('mediachat-sdp-'.$key, $message);

        $ids = array();

        if (isset($user['gcm_token']) && $user['gcm_token']) {
            $ids[] = $user['gcm_token'];
        }

        if (isset($user['messenger_gcm_token']) && $user['messenger_gcm_token']) {
            $ids[] = $user['messenger_gcm_token'];
        }

        if (isset($user['ios_gcm_token']) && $user['ios_gcm_token']) {
            $ids[] = $user['ios_gcm_token'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: key='.config('google-fcm-api-key'),
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'registration_ids' => $ids,
            'data' => array('message' => array('type' => $type, 'user' => api_arrange_user(get_user()), 'data' => array('key' => $key, 'id' => (integer) $message['data']['id'])),
                'sound' => 'default',
                'priority' => 'high',
                'content_available' => true
            ))));
        $result = curl_exec($ch);
        curl_close($ch);
		$res = json_decode($result);
    }

    return $result;
});

register_hook('system.started', function($app) {
    MediaChat::endMissedCalls();
});

register_hook('before-render-js', function($html) {
    $connection_timeout = config('mediachat-connection-timeout', 60);
    $mediachat_phrases = json_encode(array(
        'device-not-supported' => lang('mediachat::device-not-supported'),
        'error-in-connection' => lang('mediachat::error-in-connection'),
        'user-unavailable-voice-call' => lang('mediachat::user-unavailable-voice-call'),
        'user-unavailable-video-call' => lang('mediachat::user-unavailable-video-call'),
        'video-call' => lang('mediachat::video-call'),
        'voice-call' => lang('mediachat::voice-call'),
    ));

    $html .= <<<EOT
        <script>
            let mediaChatConnectionTimeout = $connection_timeout;
            let mediaChatPhrases = $mediachat_phrases;
        </script>
EOT;
	return $html;
});

register_hook('user.delete', function($user_id) {
	$db = db();
	$db->query("DELETE FROM mediachat_calls WHERE caller_id = '".$user_id."' OR receiver_id = '".$user_id."'");
	return $user_id;
});

register_hook('pusher.notifications', function($pusher, $type, $detail, $template) {
    $result = $template;
	if($type === 'mediachat-call') {
		$pending_calls = MediaChat::userPendingCalls();
		if(isset($pending_calls[0])) {
			$from_user = find_user($pending_calls[0]['caller_id']);
			$result['options']['title'] = lang('mediachat::new-call');
			$result['options']['body'] = lang('mediachat::call-from', array('name' => get_user_name($from_user)));
			$result['options']['icon'] = get_avatar(200, $from_user);
            $result['type'] = 'mediachat-call';
			$result['status'] = true;
            $pusher['notifications'][] = $result;
		}
	}
	return $pusher;
});

register_hook('admin-started', function() {
    get_menu('admin-menu', 'plugins')->addMenu(lang('mediachat::mediachat-ice-servers'), url_to_pager('admin-mediachat-server-list'), 'admin-mediachat-server-list');
});