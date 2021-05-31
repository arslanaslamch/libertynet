<?php
load_functions('livestream::livestream');

register_asset('livestream::css/livestream.css');

register_asset('js/webrtc-adapter.js');

register_asset('livestream::js/livestream.js');

register_hook('role.permissions', function ($roles) {
    $roles[] = array(
        'title' => lang('livestream::livestream-permissions'),
        'description' => '',
        'roles' => array(
            'can-add-livestream' => array('title' => lang('livestream::can-add-livestream'), 'value' => 1),
        )
    );
    return $roles;
});

register_pager('admincp/livestream/category/list', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-category-list',
        'use' => 'livestream::admincp@category_list_page')
);

register_pager('admincp/livestream/category/add', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-category-add',
        'use' => 'livestream::admincp@category_add_page')
);

register_pager('admincp/livestream/category/edit', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-category-edit',
        'use' => 'livestream::admincp@category_edit_page')
);

register_pager('admincp/livestream/category/order', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-category-order',
        'use' => 'livestream::admincp@category_order_page')
);

register_pager('admincp/livestream/category/delete', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-category-delete',
        'use' => 'livestream::admincp@category_delete_page')
);
register_pager('admincp/livestream/server/list', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-server-list',
        'use' => 'livestream::admincp@server_list_page')
);

register_pager('admincp/livestream/server/add', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-server-add',
        'use' => 'livestream::admincp@server_add_page')
);

register_pager('admincp/livestream/server/edit', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-server-edit',
        'use' => 'livestream::admincp@server_edit_page')
);

register_pager('admincp/livestream/server/delete', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-server-delete',
        'use' => 'livestream::admincp@server_delete_page')
);

register_pager('admincp/livestream/list', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-list',
        'use' => 'livestream::admincp@livestream_list_page')
);

register_pager('admincp/livestream/edit', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-edit',
        'use' => 'livestream::admincp@livestream_edit_page')
);

register_pager('admincp/livestream/delete', array(
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-delete',
        'use' => 'livestream::admincp@livestream_delete_page')
);

register_pager('admincp/livestream/action/batch', array(
        'use' => 'livestream::admincp@livestream_action_batch_pager',
        'filter' => 'admin-auth',
        'as' => 'admin-livestream-batch-action')
);

if (is_loggedIn() && user_has_permission('can-add-livestream')) {
    register_pager('livestream/start', array(
            'filter' => 'user-auth',
            'as' => 'livestream-add',
            'use' => 'livestream::add@add_page')
    );
}

register_pager('livestream/ajax', array(
        'as' => 'livestream-ajax',
        'use' => 'livestream::ajax@ajax_pager')
);

register_pager('livestream/edit', array(
        'filter' => 'user-auth',
        'as' => 'livestream-edit',
        'use' => 'livestream::edit@edit_page')
);

register_pager('livestream/delete', array(
        'filter' => 'user-auth',
        'as' => 'livestream-delete',
        'use' => 'livestream::delete@delete_livestream_page')
);

register_pager('livestreams', array(
        'as' => 'livestream-list',
        'use' => 'livestream::list@list_page')
);

register_pager('livestream/{slug}', array(
    'filter' => 'user-auth',
    'as' => 'livestream-view',
    'use' => 'livestream::view@view_page'))->where(array('slug' => '.*')
);

register_pager('{id}/livestreams', array(
        'use' => 'livestream::user_profile@list_page',
        'as' => 'profile-livestream-list',
        'filter' => 'profile')
)->where(array('id' => '[a-zA-Z0-9\_\-\.]+'));

register_hook('profile.started', function ($user) {
    add_menu('user-profile-more', array('title' => lang('livestream::livestreams'), 'as' => 'livestreams', 'link' => profile_url('livestreams', $user)));
});

register_hook('search-dropdown-start', function ($content, $term) {
    $filter = array('keywords' => $term, 'limit' => 5);
    $livestreams = Livestream::getLivestreams($filter);
    if ($livestreams->total) {
        $content .= view('livestream::search/dropdown', array('livestreams' => $livestreams));
    }
    return $content;
});

register_hook('admin-started', function () {
    get_menu('admin-menu', 'plugins')->addMenu(lang('livestream::livestream-manager'), '#', 'admin-livestream-manager');
    get_menu('admin-menu', 'plugins')->findMenu('admin-livestream-manager')->addMenu(lang('livestream::categories'), url_to_pager('admin-livestream-category-list'), 'categories');
    get_menu('admin-menu', 'plugins')->findMenu('admin-livestream-manager')->addMenu(lang('livestream::livestreams'), url_to_pager('admin-livestream-list'), 'livestreams');
    get_menu('admin-menu', 'plugins')->findMenu('admin-livestream-manager')->addMenu(lang('livestream::ice-servers'), url_to_pager('admin-livestream-server-list'), 'admin-livestream-server-list');
});

register_hook('register-search-menu', function ($term) {
    add_menu('search-menu', array('title' => lang('livestream::livestreams'), 'id' => 'livestreams', 'link' => form_search_link('livestreams', $term)));
});

register_hook('search-result', function ($content, $term, $type) {
    if ($type == 'livestreams') {
        get_menu('search-menu', 'livestreams')->setActive();
        $filter = array('keywords' => $term, 'limit' => config('livestreams-page-limit', 20));
        $livestreams = Livestream::getLivestreams($filter);
        $content = view('livestream::search/page', array('livestreams' => $livestreams));
    }
    return $content;
});


register_hook('user.delete', function ($user_id) {
    $paginator = Livestream::getLivestreams(array('user_id' => $user_id));
    foreach ($paginator->results() as $livestream) {
        Livestream::count(array('id' => $livestream['id']));
    }
});

register_hook('admin.statistics', function ($stats) {
    $stats['livestream'] = array(
        'count' => Livestream::count(),
        'title' => lang('livestream::livestream'),
        'icon' => 'ion-information-circled',
        'link' => url_to_pager('admin-livestream-list'),
    );
    return $stats;
});

register_hook('livestream.uploaded', function ($livestream) {
	if (plugin_loaded('activity')) {
		add_activity(url_to_pager('livestream', array('slug' => $livestream['slug'])), null, 'livestream', $livestream['id'], 0);
	}
    add_feed(array(
        'entity_id' => $livestream['entity_id'],
        'entity_type' => $livestream['entity_type'],
        'type' => 'feed',
        'type_id' => 'livestream',
        'type_data' => $livestream['id'],
        'privacy' => $livestream['privacy'],
        'images' => '',
        'auto_post' => true,
        'can_share' => 1
    ));
    if (config('enable-auto-video-processing')) {
        @Livestream::process($livestream);
    } else {
        send_notification($livestream['user_id'], 'livestream.processing', $livestream['id']);
    }
});

register_hook('feed-title', function ($feed) {
    if ($feed['type_id'] == 'livestream') {
        echo lang('livestream::shared-livestream');
    }
});

register_hook('uploader.types.image', function ($image_types) {
    $allowed_image_types = array('webp');
    foreach ($allowed_image_types as $type) {
        if (!in_array($type, $image_types)) {
            $image_types[] = $type;
        }
    }
    return $image_types;
});

register_hook('feed.post.plugins.hook', function ($feed) {
    if ($feed['type_id'] === 'livestream') {
        $livestream = Livestream::get($feed['type_data']);
        return view('livestream::feed/post', array('livestream' => $livestream, 'feed' => $feed));
    }
});

register_hook('livestream.deleted', function ($id) {
    $query = db()->query("SELECT feed_id FROM feeds WHERE type_id = 'livestream' AND type_data = ".$id);
    if ($query->num_rows > 0) {
        $feeds = fetch_all($query);
        foreach ($feeds as $feed) {
            remove_feed($feed['feed_id']);
        }
    }
    $comments = get_comments('livestream', $id);
    foreach ($comments as $comment) {
        delete_comment($comment['comment_id']);
    }
    delete_likes('livestream', $id);
    return true;
});

register_hook('feed.editor.actions', function () {
    $html = view('livestream::feed/editor_action');
    echo $html;
});

register_hook('footer', function () {
    echo view('livestream::layout/modal');
});

register_hook('like.item', function ($type, $type_id, $user_id) {
    if ($type == 'livestream') {
        $livestream = Livestream::get($type_id);
        if ($livestream['user_id'] and $livestream['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $livestream['user_id'], 'livestream.like', $type_id, $livestream);
        }
    } elseif ($type == 'comment') {
        $comment = find_comment($type_id, false);
        if ($comment and $comment['user_id'] != get_userid()) {
            if ($comment['type'] == 'livestream') {
                $livestream = Livestream::get($comment['type_id']);
                send_notification_privacy('notify-site-like', $comment['user_id'], 'livestream.like.comment', $comment['type_id'], $livestream);
            }
        }
    }
});

register_hook('like.react', function ($type, $type_id, $code, $user_id) {
    if ($type == 'livestream') {
        $livestream = Livestream::get($type_id);
        if ($livestream['user_id'] and $livestream['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $livestream['user_id'], 'livestream.like.react', $type_id, $livestream, $code);
        }
    }
});

register_hook('comment.add', function ($type, $type_id, $text) {
    if ($type == 'livestream') {
        $livestream = Livestream::get($type_id);
        $subscribers = get_subscribers($type, $type_id);
        if (!in_array($livestream['user_id'], $subscribers)) {
            $subscribers[] = $livestream['user_id'];
        }
        $view = view('livestream::comment/list', array('livestream' => $livestream));
        foreach ($subscribers as $user_id) {
            if ($user_id != get_userid()) {
                send_notification_privacy('notify-site-comment', $user_id, 'livestream.comment', $type_id, $livestream, null, $text);
                pusher()->sendMessage($user_id, 'livestream.comment', array('livestream_id' => $livestream['id'], 'view' => $view), null, false);
            }
        }

    }
});

register_hook('reply.add', function ($comment_id, $type, $type_id, $text) {
    if ($type == 'livestream') {
        $livestream = Livestream::get($type_id);
        $subscribers = get_subscribers('comment', $comment_id);
        if (!in_array($livestream['user_id'], $subscribers)) {
            $subscribers[] = $livestream['user_id'];
        }
        foreach ($subscribers as $user_id) {
            if ($user_id != get_userid()) {
                send_notification_privacy('notify-site-reply-comment', $user_id, 'livestream.comment.reply', $type_id, $livestream, null, $text);
            }
        }
    }
});

register_hook('display.notification', function ($notification) {
    if ($notification['type'] == 'livestream.like') {
        return view('livestream::notifications/like', array('notification' => $notification, 'type' => 'like'));
    } elseif ($notification['type'] == 'livestream.like.react') {
        return view('livestream::notifications/react', array('notification' => $notification));
    } elseif ($notification['type'] == 'livestream.dislike') {
        return view('livestream::notifications/like', array('notification' => $notification, 'type' => 'dislike'));
    } elseif ($notification['type'] == 'livestream.like.comment') {
        return view('livestream::notifications/like-comment', array('notification' => $notification, 'type' => 'like'));
    } elseif ($notification['type'] == 'livestream.dislike.comment') {
        return view('livestream::notifications/like-comment', array('notification' => $notification, 'type' => 'dislike'));
    } elseif ($notification['type'] == 'livestream.comment') {
        $livestream = Livestream::get($notification['type_id']);
        if ($livestream) {
            return view('livestream::notifications/comment', array('notification' => $notification, 'livestream' => $livestream));
        } else {
            delete_notification($notification['notification_id']);
        }
    } elseif ($notification['type'] == 'livestream.comment.reply') {
        return view('livestream::notifications/reply', array('notification' => $notification));
    } elseif ($notification['type'] == 'livestream.started') {
        return view('livestream::notifications/started', array('notification' => $notification));
    } elseif ($notification['type'] == 'livestream.ended') {
        return view('livestream::notifications/ended', array('notification' => $notification));
    } elseif ($notification['type'] == 'livestream.processing') {
        return view('livestream::notifications/processing', array('notification' => $notification));
    } elseif ($notification['type'] == 'livestream.processed') {
        return view('livestream::notifications/processed', array('notification' => $notification));
    }
});

register_hook('pusher.on.message', function ($message, $index) {
    if (isset($message['type'])) {
        $sender_id = get_userid();
        if ($sender_id) {
            if ($message['type'] == 'livestream.session.description') {
                $livestream = Livestream::get($message['livestream_id']);
                if (is_array($message['data']) && $message['data'] && Livestream::canView($message['livestream_id'])) {
                    if ($message['data']['type'] == 'offer') {
                        if ($sender_id != $livestream['user_id'] && $message['user_id'] == $livestream['user_id']) {
                            pusher()->sendMessage($livestream['user_id'], 'livestream.session.description', array('index' => (integer)$index, 'user_id' => (integer)$message['user_id'], 'sender_id' => (integer)$sender_id, 'token' => $message['token'], 'livestream_id' => (integer)$livestream['id'], 'time' => time(), 'data' => $message['data']), null, false);
                        }
                    } elseif ($message['data']['type'] == 'answer') {
                        if ($sender_id != $message['user_id']) {
                            pusher()->sendMessage($message['user_id'], 'livestream.session.description', array('index' => (integer)$index, 'user_id' => (integer)$message['user_id'], 'sender_id' => (integer)$sender_id, 'token' => $message['token'], 'livestream_id' => (integer)$livestream['id'], 'time' => time(), 'data' => $message['data']), null, false);
                        }
                    }
                }
            } elseif ($message['type'] == 'livestream.ice.candidate') {
                if (Livestream::canView($message['livestream_id'])) {
                    pusher()->sendMessage((integer)$message['user_id'], 'livestream.ice.candidate', array(array('index' => (integer)$index, 'user_id' => (integer)$message['user_id'], 'sender_id' => (integer)$sender_id, 'token' => $message['token'], 'livestream_id' => (integer)$message['livestream_id'], 'time' => time(), 'data' => $message['data'])), null, false);
                }
            } elseif ($message['type'] == 'livestream.keep.alive') {
                Livestream::keepAlive($message['livestream_id']);
            } elseif ($message['type'] == 'livestream.member.count') {
                $livestream = Livestream::get($message['livestream_id']);
                if ($livestream['privacy'] < 3) {
                    $friends = get_friends();
                    $followers = get_followers();
                    $subscribers = array_unique(array_merge($friends, $followers), SORT_REGULAR);
                    foreach ($subscribers as $subscriber_id) {
                        if ($subscriber_id != $livestream['user_id']) {
                            pusher()->sendMessage($subscriber_id, 'livestream.member.count', array('livestream_id' => (integer)$livestream['id'], 'count' => (integer)$message['count']), null, false);
                        }
                    }
                }
            } elseif ($message['type'] == 'livestream.started') {
                Livestream::edit(array('id' => $message['livestream_id'], 'start_time' => $message['data']['start_time']));
            } elseif ($message['type'] == 'livestream.ended') {
				if(isset($message['data']['record'])) {
					Livestream::end($message['livestream_id'], $message['data']['record']);
				}
            }
        }
    }
    return $message;
});

register_hook('ajax.push.result', function ($pushes, $user_id = null) {
    $limit = config('livestream-ongoing-widget-list-limit', 5);
    $livestreams = Livestream::getLivestreams(array('status' => 1), $limit);
    $pushes['types']['livestream.widget.list.ongoing'] = view('livestream::livestream/widget_list', array('title' => lang('livestream::ongoing-livestreams'), 'livestreams' => $livestreams));
    return $pushes;
});

register_hook('uid.check', function ($result, $value, $type = null, $type_id = null) {
    if (!$type || $type == 'livestream') {
        $livestream = Livestream::get($value);
        if ($livestream) {
            if (!$type_id || ($type_id && $type_id != $livestream['id'])) {
                $result[0] = false;
            }
        }
    }
    return $result;
});

register_hook('system.started', function ($app) {
    Livestream::endInactive();
});

register_hook('ajax.push.notification.api.exception', function ($result, $type, $user_id, $detail, $message) {
    if ($type === 'livestream.session.description') {
        $result[0] = true;

        $user = find_user($user_id);

        $key = md5(mt_rand(0, 9999).time().mt_rand(0, 9999).get_userid().mt_rand(0, 9999));
        set_cacheForever('livestream-sdp-'.$key, $message);

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
            'data' => array('message' => array('type' => $type, 'user' => api_arrange_user(get_user()), 'data' => array('key' => $key, 'livestream_id' => (integer) $message['data']['livestream_id'])),
            'sound' => 'default',
            'priority' => 'high',
            'content_available' => true
        ))));
        curl_exec($ch);
        curl_close($ch);
    }

    return $result;
});

register_hook('before-render-js', function ($html) {
    $livestream_phrases = json_encode(array(
        'browser-not-support-recording' => lang('livestream::browser-not-support-recording'),
        'camera' => lang('livestream::camera'),
        'complete' => lang('livestream::complete'),
        'connection-failed' => lang('livestream::connection-failed'),
        'disconnected' => lang('livestream::disconnected'),
        'joined' => lang('livestream::joined'),
        'joining' => lang('livestream::joining'),
        'livestream-close-confirm' => lang('livestream::livestream-close-confirm'),
        'livestream-ended' => lang('livestream::livestream-ended'),
        'livestream-not-supported' => lang('livestream::livestream-not-supported'),
        'microphone' => lang('livestream::microphone'),
        'modal-load-error' => lang('livestream::modal-load-error'),
        'record-limit-reached' => lang('livestream::record-limit-reached'),
        'stream-upload-failed' => lang('livestream::stream-upload-failed'),
        'uploading-stream' => lang('livestream::uploading-stream')
    ));
    $html .= <<<EOT
        <script>
            let livestreamPhrases = $livestream_phrases;
        </script>
EOT;
    return $html;
});

add_menu_location('livestream-menu', 'livestream::livestream-menu');

add_available_menu('livestream::livestream', url_to_pager('livestreams', array('appends' => '')), 'ion-information-circled');

TaskManager::add('livestream-processing', 'Livestream::processAll');
