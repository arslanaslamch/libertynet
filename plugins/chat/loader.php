<?php
load_functions('chat::chat');
register_hook("role.permissions", function ($roles) {
    $roles[] = array(
        'title' => lang('chat::chat-permissions'),
        'description' => '',
        'roles' => array(
            'can-send-message' => array('title' => lang('can-send-message'), 'value' => 1),
        )
    );
    return $roles;
});

register_pager("messages", array('as' => 'messages', 'use' => 'chat::message@messages_pager', 'filter' => 'auth'));
register_pager('chat/friends/online', array('as' => 'chat-friends-online', 'use' => 'chat::message@chat_friends_online'));

register_hook('system.started', function ($app) {
    $user_id = get_userid();
    if ($user_id) {
        $reset = true;
        $pushes = pusher()->result($user_id);
        $pushes = fire_hook('ajax.push.check.result', $pushes, array($pushes));
        $pushes = json_decode($pushes, true);
        $chat_group_member_left = isset($pushes['types']['chat.group.member.left']) ? $pushes['types']['chat.group.member.left'] : array();
        if ($chat_group_member_left) {
            foreach ($chat_group_member_left as $push) {
                if (isset($push['time']) && time() - $push['time'] < config('pusher-timeout', 300) + (config('ajax-polling-interval', 5000) / 1000) && time() - ($push['time'] > config('ajax-polling-interval', 5000) / 1000) + 10) {
                    $reset = false;
                    break;
                }
            }
            if ($reset) {
                pusher()->reset('chat.group.member.left');
            }
        }
    }
});

if (user_has_permission('can-send-message')) {
    register_hook('system.started', function ($app) {
        if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
            register_asset("chat::css/chat.css");
            register_asset("chat::js/chat.js");
        }
    });

    register_hook("user.profile.buttons", function ($user) {
        //echo view('chat::button', array('user' => $user));
    });

    register_hook('ajax.push.result', function ($pushes, $user_id = null) {
        $user_id = isset($user_id) ? $user_id : get_userid();
        $count = count_unread_messages(null, $user_id);
        $pushes['types']['unread'] = $count;
        $users = chat_get_onlines($user_id);
        $countOnline = count($users);
        $users = array_merge($users, get_few_offlines($user_id));
        $pushes['types']['onlines'] = view('chat::load-onlines', array('users' => $users));
        $pushes['types']['count-onlines'] = $countOnline;
        $pushes['types']['chat-groups'] = view('chat::load-groups');
        $pushes['types']['chat-opened'] = isset(app()->chatBoxDelete) ? array() : get_cache('user-chat-opened-'.$user_id, array());
        $cache_name = 'typing-'.$user_id;
        $result = array();
        if (cache_exists($cache_name)) $result = get_cache($cache_name);
        $pushes['types']['chat-typing'] = array('now' => time(), 'cid' => $result, 'typing' => lang('chat::typing'), 'img' => img('images/typing.gif'));
        $cache_name = 'message-waiting-'.$user_id;
        $result = array();
        if (cache_exists($cache_name)) $result = get_cache($cache_name);
        $seen = array();
        if (is_array($result)) {
            foreach ($result as $cid => $detail) {
                if (has_read_message($detail[0], $detail[1]) and !isset($detail[2])) {
                    $seen[$cid] = $detail[0];
                    $result[$cid][2] = 'seen';
                }
            }
        }
        set_cacheForever($cache_name, $result);
        $pushes['types']['chat-seen'] = $seen;
        return $pushes;
    });

    register_hook('footer', function () {
        if (is_loggedIn() and !isMobile()) echo view('chat::footer');
    });

    register_hook("privacy-settings", function () {
        echo view('chat::privacy');
    });
    register_pager("chat/send", array('as' => 'chat-send', 'use' => 'chat::message@chat_send_pager', 'filter' => 'auth'));
    register_pager("chat/load/messages", array('use' => 'chat::message@chat_load_messages_pager', 'filter' => 'auth'));
    register_pager("chat/load/search", array('use' => 'chat::message@chat_load_search_pager', 'filter' => 'auth'));
    register_pager("chat/load/conversations", array('use' => 'chat::message@chat_load_conversations_pager', 'filter' => 'auth'));
    register_pager("chat/load/groups", array('use' => 'chat::message@chat_load_groups_pager', 'filter' => 'auth'));
    register_pager("chat/preload", array('use' => 'chat::message@chat_preload_pager', 'filter' => 'auth'));
    register_pager("chat/typing", array('use' => 'chat::message@chat_typing_pager', 'filter' => 'auth'));
    register_pager("chat/remove/typing", array('use' => 'chat::message@chat_remove_typing_pager', 'filter' => 'auth'));
    register_pager("chat/mark/read", array('use' => 'chat::message@chat_mark_read_pager', 'filter' => 'auth'));
    register_pager("chat/load/dropdown", array('use' => 'chat::message@chat_load_dropdown_pager', 'filter' => 'auth'));
    register_pager("chat/register/open", array('use' => 'chat::message@chat_register_opened_pager', 'filter' => 'auth'));
    register_pager("chat/get/conversations", array('use' => 'chat::message@chat_get_conversations_pager', 'filter' => 'auth'));
    register_pager("chat/set/status", array('use' => 'chat::message@chat_set_status_pager', 'filter' => 'auth'));
    register_pager("chat/paginate", array('use' => 'chat::message@chat_paginate_pager', 'filter' => 'auth'));
    register_pager("chat/delete/conversation", array('use' => 'chat::message@chat_delete_conversation_pager', 'filter' => 'auth'));
    register_pager("chat/delete/message", array('use' => 'chat::message@chat_delete_message_pager', 'filter' => 'auth'));
    register_pager("chat/update/send/privacy", array('use' => 'chat::message@chat_update_send_privacy_pager', 'filter' => 'auth'));
    register_pager("mobile/online", array('as' => 'mobile-online', 'use' => 'chat::message@mobile_online_pager', 'filter' => 'auth'));
    register_pager("chat/download", array('as' => 'chat-download', 'use' => 'chat::message@chat_download_pager'));
    register_pager("chat/settings", array('as' => 'chat-settings', 'use' => 'chat::message@chat_settings_pager'));
    register_pager("chat/leave/conversation", array('as' => 'chat-leave-conversation', 'use' => 'chat::message@chat_leave_conversation_pager'));
    register_pager("chat/accept/conversation", array('as' => 'chat-accept-conversation', 'use' => 'chat::message@chat_accept_conversation_pager'));
    register_pager("chat/set/color", array('use' => 'chat::message@chat_set_color_pager', 'filter' => 'auth'));

}

register_hook('user.delete', function ($userid) {
    db()->query("DELETE FROM conversation_messages WHERE sender='{$userid}'");
    db()->query("DELETE FROM conversation_members WHERE user_id='{$userid}'");
});

register_hook('plugin.loaded.result', function ($result, $plugin = null) {
    if ($plugin == 'chat') {
        $result['result'] = user_has_permission('can-send-message') ? $result['result'] : false;
    }
    return $result;
});

register_hook('pusher.notifications', function ($pusher, $type, $detail, $template) {
    $result = $template;
    if ($type === 'chat') {
        $sender_id = $detail['user'];
        $sender = find_user($sender_id);
        $message_id = $detail['id'];
        $message = get_chat_message($message_id);
        $result['options']['title'] = lang('chat::message-from', array('name' => get_user_name($sender)));
        $result['options']['body'] = str_limit(strip_tags($message['message']), 200);
        $result['options']['link'] = url_to_pager('messages');
        $result['options']['icon'] = get_avatar('200', $sender);
        $result['type'] = 'chat';
        $result['status'] = true;
        $pusher['notifications'][] = $result;
    }
    return $pusher;
});

/*register_hook("feed.added", function ($feedId, $val) {
    if ($val && $feedId && isset($val['messenger'])) {
        $messenger = array_filter($val['messenger']);
        $feed = find_feed($feedId);
        if ($messenger && $feed){
            $users = $val['messenger'];
            $link = url_to_pager('view-post', array('id' => $feed['feed_id']));
            foreach ($users as $user){
                if ($user){
                    if (!is_array($user)) $user = array($user);
                    $conversationId = get_conversation_id($user);
                    send_chat_message($conversationId, $link);
                }
            }
        }
    }
});*/
register_hook("after.add.feed", function ($val) {
    if ($val && $val['content'] && isset($val['messenger']) && count($val['messenger']) > 0) {
        $entity_type = $val['entity_type'];
        $images = "";
        $files = "";
        $voice = "";
        $gif = "";
        $content = $val['content'];
        $entity_id = $val['entity_id'];
        $privacy = $val['privacy'];
        $content = sanitizeText($content);
        $images_file = input_file('image');
        $images_file = $images_file[0];
        if ($images_file) {
            $uploader = new Uploader($images_file);
            $path = get_userid().'/'.date('Y').'/photos/messages/';
            $uploader->setPath($path);
            if ($uploader->passed()) {
                $images = $uploader->noThumbnails()->resize()->result();
            } else {
                $result['status'] = 0;
                $result['error'] = $uploader->getError();
                return json_encode($result);
            }
        }
        $f = input_file('file');
        if ($f) {
            $uploader = new Uploader($f, 'file');
            $path = get_userid().'/'.date('Y').'/files/posts/';
            $uploader->setPath($path);
            if ($uploader->passed()) {
                $file = $uploader->uploadFile()->result();
                $messageFile = array(
                    'path' => $file,
                    'name' => $uploader->sourceName,
                    'extension' => $uploader->extension
                );
                $files = perfectSerialize($messageFile);
            } else {
                $result['status'] = 0;
                $result['error'] = $uploader->getError();
                return json_encode($result);
            }
        }
        $voiceInput = input('voice');
        if ($voiceInput) {
            list($header, $voice) = array_pad(explode(',', $voiceInput), 2, '');
            preg_match('/data\:audio\/(.*?);base64/i', $header, $matches);
            $default_extension = 'webm';
            $extension = isset($matches[1]) ? $matches[1] : $default_extension;
            if (!in_array($extension, array($default_extension))) {
                exit('Invalid Format '.$extension);
            }
            $voice = base64_decode(str_replace(' ', '+', $voice));
            $temp_dir = config('temp-dir', path('storage/tmp')).'/feed/voice';
            $file_name = 'voice_'.get_userid().'_'.time();
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }
            $temp_path = $temp_dir.'/'.$file_name.'.'.$extension;
            file_put_contents($temp_path, $voice);
            $uploader = new Uploader($temp_path, 'audio', false, true);
            if ($uploader->passed()) {
                $path = get_userid().'/'.date('Y').'/voices/feeds/';
                $uploader->setPath($path);
                $voice = $uploader->uploadFile()->toDB('posts')->result();
            } else {
                $result['status'] = 0;
                $result['message'] = $uploader->getError();
                return json_encode($result);
            }
        }
        $gifInput = $val['gif'];
        if ($gifInput) {
            $file_path = get_userid().'/'.date('Y').'/gif/posts/';
            $gif = gifImageProcessing($gifInput, $file_path, 'posts', $entity_type, $entity_id, $privacy);
        }
        $users = $val['messenger'];
        foreach ($users as $user) {
            if ($user) {
                if (!is_array($user)) $user = array($user);
                $conversationId = get_conversation_id($user);
                send_chat_message($conversationId, $content, $images, $files, $voice, $gif);
            }
        }

        return $result = array(
            'status' => 1,
            'message' => lang('feed::post-sent'),
            'feed' => ''
        );
    }
});

register_hook('pusher.on.message', function ($message, $index) {
    $sender_id = get_userid();
    if ($sender_id && isset($message['type'])) {
        if ($message['type'] == 'chat.conversation.last.update.time') {
            pusher()->reset('chat.conversation.last.update.time');
            $conversation_last_update_times = array();
            $cids = $message['cids'];
            foreach ($cids as $cid) {
                $conversation = get_conversation($cid);
                $conversation_last_update_times[$cid] = str_time($conversation['last_update_time']);
            }
			if($conversation_last_update_times) {
				pusher()->sendMessage($sender_id, 'chat.conversation.last.update.time', $conversation_last_update_times, null, false);
			}
        } elseif ($sender_id && isset($message['type'])) {
            if ($message['type'] == 'chat.registration') {
                app()->chatBoxDelete = true;
            }
        }
    }
    return $message;
});
