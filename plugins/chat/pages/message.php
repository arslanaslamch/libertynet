<?php
function messages_pager($app) {
    $app->setTitle(lang('chat::messages'));
    $app->onFooter = false;
    $app->sideChat = false;
    $app->fullContainer = true;
    $cid = input('cid', 'last');
    $userid = input('userid');
    $content = '';
    $conversation = null;
    $messageContent = null;
    if ($userid) {
        $theirCid = get_conversation_id(array($userid));
        $cid = ($theirCid) ? $theirCid : 'new';
    }

    if ($cid === 'last') {
        $conversation = get_last_conversation();
        $cid = $conversation ? $conversation['cid'] : 'new';
    }
    if ($cid !== 'new' && $cid !== 'mobile') {
        $conversation = $conversation ? $conversation : get_conversation($cid);
        if (!$conversation || !is_conversation_member($cid)) return redirect(url_to_pager('messages').'?cid=new');
        $messages = get_chat_messages($cid);
        $app->setTitle($conversation['title']);
        $messageContent = '';
        foreach ($messages as $message) {
            $messageContent .= view('chat::messenger/message', array('message' => $message, 'conversation' => $conversation));
        }
    } else {
        $app->setTitle(lang('chat::new-message'));
    }

    $app->setLayout('chat::layout');
    $limit = input('limit', config('chat-conversation-list-limit', 10));
    $conversations = get_user_conversations($limit);
    $content = view('chat::messenger/index', array(
        'cid' => $cid,
        'userid' => $userid ? $userid : (($conversation && $conversation['type'] === 'single') ? (get_userid() == $conversation['user1'] ? $conversation['user2'] : $conversation['user1']) : null),
        'conversations' => $conversations,
        'conversations_limit' => $limit,
        'conversate' => $conversation,
        'messageContent' => $messageContent
    ));
    return $app->render($content);
}

function chat_send_pager($app) {
    CSRFProtection::validate(false);
    $val = input('val');
    /**
     * @var $user
     * @var $cid
     * @var $message
     */
    $defined = array(
		'entity_type' => 'user',
		'entity_id' => get_userid()
	);
	$val = array_merge($defined, $val);
    extract($val);
    $result = array(
        'status' => 0,
        'error' => 'Failed to send message',
    );
    $entityType = $val['entity_type'];
    $entityId = $val['entity_id'];
    if (empty($cid) && !isset($user)) {
        return json_encode($result);
    }
    if (isset($user)) {
        $user = fire_hook('conversation.second.user', $user, array($entityType, $entityId));
    }
    $image = null;
    $imageFile = input_file('image');
    if ($imageFile) {
        $uploader = new Uploader($imageFile);
        $path = get_userid().'/'.date('Y').'/photos/messages/';
        $uploader->setPath($path);
        if ($uploader->passed()) {
            $image = $uploader->noThumbnails()->resize()->result();
        } else {
            $result['status'] = 0;
            $result['error'] = $uploader->getError();
            return json_encode($result);
        }
    }
    $file = input_file('file');
    $messageFile = '';
    if ($file) {
        $uploader = new Uploader($file, 'file');
        $path = get_userid().'/'.date('Y').'/files/posts/';
        $uploader->setPath($path);
        if ($uploader->passed()) {
            $file = $uploader->uploadFile()->result();
            $messageFile = array(
                'path' => $file,
                'name' => $uploader->sourceName,
                'extension' => $uploader->extension
            );
            $messageFile = perfectSerialize($messageFile);
        } else {
            $result['status'] = 0;
            $result['error'] = $uploader->getError();
            return json_encode($result);
        }
    }

    $voice = input('voice');
    if ($voice) {
        list($header, $voice) = array_pad(explode(',', $voice), 2, '');
        preg_match('/data\:audio\/(.*?);base64/i', $header, $matches);
        $default_extension = 'webm';
        $extension = isset($matches[1]) ? $matches[1] : $default_extension;
        if (!in_array($extension, array($default_extension))) {
            exit('Invalid Format '.$extension);
        }
        $voice = base64_decode(str_replace(' ', '+', $voice));
        $temp_dir = config('temp-dir', path('storage/tmp')).'/chat/voice';
        $file_name = 'voice_'.get_userid().'_'.time();
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }
        $temp_path = $temp_dir.'/'.$file_name.'.'.$extension;
        file_put_contents($temp_path, $voice);
        $uploader = new Uploader($temp_path, 'audio', false, true);
        if ($uploader->passed()) {
            $path = get_userid().'/'.date('Y').'/voices/chats/';
            $uploader->setPath($path);
            $voice = $uploader->uploadFile()->result();
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
            return json_encode($result);
        }
    }
    $gifLink = input('gif');
    if ($gifLink) {
        $file_path = get_userid().'/'.date('Y').'/gif/chat/posts/';
        $gifLink = gifImageProcessing($gifLink, $file_path);
    }
    if (!$message && !$image && !$messageFile && !$voice && !$gifLink) return json_encode($result);
    $new = false;
    if (!$cid) {
        if (count($user) == 1) {
            //lets check if the user has not block each other
            if (is_blocked($user[0])) return json_encode($result);
        }
        $cid = get_conversation_id($user);
        $new = true;
    }

    if (!is_conversation_member($cid)) return json_encode($result);
    //send the message to the cid now
    $con = get_conversation($cid);
    if ($con['type'] == 'single') {
        $theUser = ($con['user1'] == get_userid()) ? $con['user2'] : $con['user1'];
        if (is_blocked($theUser)) return json_encode($result);
    }
    $messageId = send_chat_message($cid, $message, $image, $messageFile, $voice, $gifLink, $entityType, $entityId);
    $result['cid'] = $cid;
    $result['messageid'] = $messageId;
    $result['status'] = 1;
    $uid = null;
    if ($con['type'] == 'single') {
        $uid = ($con['user1'] == get_userid()) ? $con['user2'] : $con['user1'];
    }
    $result['type'] = $con['type'];
    $result['uid'] = $uid;
    if (!input('val.cid')) {
        $result['title'] = get_conversation_title($cid);
        $app->setTitle($result['title']);
        $result['sitetitle'] = $app->title;
        $result['url'] = url_to_pager('messages').'?cid='.$cid;
    }
    $conversation = get_conversation($cid);
    if ($new) {
        $user = $uid ? find_user($uid, false) : null;
        $result['conversation_head'] = view('chat::messenger/conversation-head', array('conversation' => $conversation, 'cid' => $cid, 'user' => $user));
        $result['conversation_messaging_info'] = view('chat::messenger/conversation-messaging-info', array('conversation' => $conversation, 'user' => $user));
        $result['conversation_info'] = view('chat::messenger/conversation-info', array('conversation' => $conversation, 'cid' => $cid));
        $result['message'] = view('chat::messenger/messages', array('messages' => get_chat_messages($cid), 'conversation' => $conversation));
    } else {
        $result['message'] = view('chat::messenger/message', array('message' => get_chat_message($messageId), 'conversation' => $conversation));
    }

    //ensure the conversation is not deleted
    revive_conversation($cid);

    if ($con['type'] == 'single') {
        remove_typing_status($con);
        register_waiting_message($messageId, $con);
    }
    $result['avatar1'] = isset($con['avatars'][0]) ? $con['avatars'][0] : '';
    $result['avatar2'] = isset($con['avatars'][1]) ? $con['avatars'][1] : '';
    fire_hook("chat.send", $val, array($cid));
    return json_encode($result);
}

function chat_load_messages_pager($app) {
    CSRFProtection::validate(false);
    $data = input('data');
    $result = array(
        'messages' => array()
    );

    if ($data) {
        foreach ($data as $cid) {
            $theCid = $cid[0];
            $s_m = array();
            $a_m = array();
            foreach ($cid[1] as $m) {
                if (is_array($m)) {
                    foreach ($m as $a) {
                        $a_m[] = $a;
                    }
                } else {
                    $s_m[] = $m;
                }
            }
            $ids = $a_m ? implode(',', $a_m) : implode(',', $s_m);
            if ($ids) {
                $messages = get_chat_messages_by_ids($ids);
                $content = view('chat::messenger/messages', array('messages' => $messages, 'mark' => false, 'conversation' => get_conversation($theCid)));
                $result['messages'][$theCid] = $content;
            }
        }
    }

    return json_encode($result);
}

function chat_load_groups_pager($app) {
    CSRFProtection::validate(false);
    echo view('chat::load-groups');
}

function chat_load_conversations_pager($app) {
    CSRFProtection::validate(false);
    $limit = input('limit', config('chat-conversation-list-limit', 10));
    $offset = input('offset', 0);
    $cid = input('cid');
    $term = input('term');
    $entityType = input('entity_type','user');
    $entityId = input('entity_id', get_userid());
    $result = array(
        'status' => 0,
        'offset' => $offset,
        'limit' => $limit,
        'html' => '',
        'cid' => ''
    );
    $conversations = get_user_conversations($term ? null : $limit, null, $term ? null : $offset, $term, $entityType, $entityId);
    if ($conversations) {
        foreach ($conversations as $conversation) {
            $result['html'] .= view('chat::messenger/conversations-list-item', array('conversation' => $conversation, 'cid' => $cid, 'searched' => true));
            $result['offset']++;
        }
        $result['status'] = 1;
    }

    $response = json_encode($result);
    return $response;
}

function chat_load_search_pager($app) {
    CSRFProtection::validate(false);
    $term = input('term');
    $paginator = list_connections(get_friends(), 10, $term);
    $users = $paginator->results();
    echo view('chat::load-search', array('users' => $users));
}

function chat_preload_pager($app) {
    CSRFProtection::validate(false);
    $cid = input('cid');
    $uid = input('uid');
    $entityType = input('entity_type','user');
    $entityId = input('entity_id', get_userid());
    if (!$cid) {
        $uids = array($uid);
        $user = fire_hook('conversation.second.user', $uids, array($entityType, $entityId));
        $cid = get_conversation_id($user, true, $entityType, $entityId);
    }
    $color = '';
    $conversation = $cid ? get_conversation($cid) : null;
    if ($conversation && ($conversation['color1'] || $conversation['color2'])){
        if ($conversation['user1'] == get_userid()) $color = $conversation['color1'];
        if ($conversation['user2'] == get_userid()) $color = $conversation['color2'];
    }
    $result = array(
        'cid' => $cid,
        'uid' => $uid,
        'messages' => '',
        'type' => ($conversation) ? $conversation['type'] : null,
        'header_color' => $color,
        'accepted' => 1,
    );
    if (empty($cid)) return json_encode($result);
    $result['messages'] = view('chat::messenger/messages', array('messages' => get_chat_messages($cid, 10), 'mark' => true, 'conversation' => $conversation));
    if($conversation['type'] == 'multiple' && !has_accepted_group_conversation($conversation)){
        $result['accepted'] = 0;
        $result['accept_view'] = view('chat::request_button', array('conversation' => $conversation));
    }
    return json_encode($result);
}

function chat_mark_read_pager($app) {
    CSRFProtection::validate(false);
    $ids = input('ids');
    foreach ($ids as $id) {
        if ($id) mark_message_read($id);
    }

    return count_unread_messages();
}

function chat_load_dropdown_pager($app) {
    CSRFProtection::validate(false);
    $conversations = get_user_conversations(10);
    $content = '';
    foreach ($conversations as $conversation) {
        $content .= view('chat::conversation/display', array('conversation' => $conversation, 'box' => true));
    }

    return $content;
}

function chat_register_opened_pager() {
    CSRFProtection::validate(false);
    $cid = input('cid');
    $uid = input('uid');
    $action = input('action', 'add');
    $action == 'add' ? chat_register_box($cid, $uid) : chat_unregister_box($cid);
}

function chat_get_conversations_pager() {
    CSRFProtection::validate(false);
    $cids = input('cids');
    $results = array();
    foreach ($cids as $cid) {
        $con = get_conversation($cid);
        $uid = null;
        if ($con['type'] == 'single') {
            $uid = $con['user1'] == get_userid() ? $con['user2'] : $con['user1'];
        }
        $results[$cid] = array(
            'title' => $con['title'],
            'uid' => $uid,
            'avatar' => $con['type'] == 'single' ? $con['avatar'] : $con['avatars'][0],
            'avatar2' => $con['type'] == 'single' ? '' : isset($con['avatars'][1]) ? $con['avatars'][1] : get_avatar(75),
        );
        $results[$cid] = fire_hook('chat.get.conversations.result', $results[$cid]);
    }

    return json_encode($results);
}

function chat_set_status_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type', 0);
    update_user(array('online_status' => $type));
}

function chat_paginate_pager() {
    CSRFProtection::validate(false);
    $offset = input('offset');
    $cid = input('cid');
    $limit = 10;
    $newOffset = $offset + $limit;
    $result = array(
        'cid' => $cid,
        'offset' => $newOffset,
        'messages' => ''
    );
    if (empty($cid)) return json_encode($result);
    $result['messages'] = view('chat::messenger/messages', array('messages' => get_chat_messages($cid, 10, $newOffset), 'conversation' => get_conversation($cid)));

    return json_encode($result);

}

function chat_update_send_privacy_pager() {
    CSRFProtection::validate(false);
    $v = input('v');
    save_privacy_settings(array('chat-send-button' => $v));
}

function chat_delete_conversation_pager() {
    $cid = input('cid');
    delete_conversation($cid);
    return redirect_to_pager('messages');
}

function chat_delete_message_pager() {
    $id = input('id');
    $deletedMessages = get_privacy('delete-messages', array(0));
    $deletedMessages = array_merge($deletedMessages, array($id));
    save_privacy_settings(array('delete-messages' => $deletedMessages));

    return true;
}

function mobile_online_pager($app) {
    $app->setTitle("onlines");

    return $app->render(view("chat::mobile/onlines", array("users" => chat_get_onlines())));
}

function chat_typing_pager($app) {
    CSRFProtection::validate(false);
    $con = get_conversation(input('cid'), false);
    if (!$con || $con['type'] == 'multiple') return false;
    $userid = ($con['user1'] == get_userid()) ? $con['user2'] : $con['user1'];
    $cacheName = "typing-{$userid}";
    $result = array();
    if (cache_exists($cacheName)) $result = get_cache($cacheName);
    $result[input('cid')] = time();
    set_cacheForever($cacheName, $result);
    //print_r($result);
}

function chat_remove_typing_pager($app) {
    CSRFProtection::validate();
    $con = get_conversation(input('cid'), false);
    remove_typing_status($con);
}

function chat_friends_online($app) {
    CSRFProtection::validate();
    $users = array_merge(chat_get_onlines(), get_few_offlines());
    return view('chat::load-onlines', array('users' => $users));
}

function chat_download_pager($app) {
    $path = input('path');
    if ($path) {
        $name = input('name');
        $file_id = get_media_id($path);
        if ($file_id) {
            return download_file($path, $name);
        } else {
            return download_file($path, $name);
        }
    }
}

function chat_settings_pager() {
    $results = array(
        'status' => 0,
        'content' => ''
    );
    $chat_id = input('cid');
    $conversation = get_conversation($chat_id);
    if ($conversation['type'] == 'multiple') {
        $results['status'] = 1;
        $results['content'] = "<li><i class='fa fa-arrow-left'></i> <a href='' onclick='return leaveGroupChat(".$chat_id.")'>".lang('chat::leave-group-chat')."</a> </li>";
    }
    $results = fire_hook('chat.settings.content', $results);
    return json_encode($results);
}
function chat_leave_conversation_pager($app) {
    $result = array(
        'status' => 0,
    );
    $user_id = get_userid();
    $cid = input('cid');
    $ajax = input('ajax');
    if ($cid) {
        if(delete_conversation_member($cid, $user_id)) {
            $result['status'] = 1;
        }
    }
    return $ajax ? json_encode($result) : redirect_back();
}

function chat_accept_conversation_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('unknown-error')
    );
    $cid = input('cid');
    $ajax = input('ajax');
    $conversation = get_conversation($cid);
    if ($conversation) {
        if(accept_group_conversation($conversation)) {
            $result['status'] = 1;
            $result['message'] = lang('chat::joined-successfully');
        }
    }
    return $ajax ? json_encode($result) : redirect_back();
}

function chat_set_color_pager(){
    $result = array('status' => 0);
    $cid = input('cid');
    $color = input('color');
    if ($cid && $color){
		$color = '#'.$color;
        $conversation = get_conversation($cid);
        if ($conversation){
            $field = 'color1';
            if ($conversation['user2'] == get_userid()) $field = 'color2';
            $updateConversation = update_conversation(array($field => $color), $cid);
            if($updateConversation){
                $result['status'] = 1;
            }
        }
    }
    return json_encode($result);
}
