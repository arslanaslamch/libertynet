<?php

load_functions('contest::contest');
load_functions('contest::category');
register_hook('system.started', function($app) {
    //if($app->themeType == 'frontend') {
        register_asset('contest::css/tui-date-picker.css');
        register_asset('contest::css/tui-time-picker.css');
        register_asset('contest::css/contest.css');

        register_asset('contest::js/tui-code-snippet.js');
        register_asset('contest::js/tui-time-picker.js');
        register_asset('contest::js/tui-date-picker.js');
        register_asset('contest::js/contest.js');
    //}
});


register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('contest::contest-permissions'),
        'description' => '',
        'roles' => array(
            'can-create-contest' => array('title' => lang('contest::can-create-contest'), 'value' => 1),
        )
    );
    return $roles;
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'contest.invite') {
        return view("contest::notifications/invite", array('notification' => $notification, 'type' => 'contest'));
    }
    if ($notification['type'] == 'contest.notify') {
        return view("contest::notifications/notify", array('notification' => $notification, 'type' => 'contest'));
    }
});


register_pager("contests", array('use' => 'contest::contest@contest_pager', 'as' => 'contests'));
register_pager("contest/my-entries", array('use' => 'contest::contest@my_entries_pager', 'as' => 'contests-my-entries','filter'=>'auth'));
register_pager("contest/add", array('use' => 'contest::contest@add_contest_pager', 'filter' => 'auth', 'as' => 'contest-add'));
register_pager("contest/manage", array('use' => 'contest::contest@manage_pager', 'as' => 'contest-manage', 'filter' => 'auth'));
register_pager("contest/announcement", array('use' => 'contest::contest@announcement_pager', 'as' => 'contest-annoucement', 'filter' => 'auth'));
register_pager("contest/ajax", array('use' => 'contest::contest@ajax_pager', 'as' => 'ajax-contest', 'filter' => 'auth'));
register_pager("contest/promoted", array('use' => 'contest::contest@promote_pager', 'as' => 'contest-promoted-iframe'));
register_pager("contest/{slugs}", array('use' => 'contest::contest@contest_page_pager', 'as' => 'contest-page'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));
register_pager("contest/{slugs}/submit", array('use' => 'contest::contest@submit_entry_pager', 'as' => 'contest-submit-entry'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));
register_pager("contest/{slugs}/entries", array('use' => 'contest::contest@entries_pager', 'as' => 'contest-entries'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));
register_pager("contest/{slugs}/entry/{other}", array('use' => 'contest::contest@display_entry_pager', 'as' => 'contest-display-entry'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+','other' => '[a-zA-Z0-9\-\_\.]+'));

register_pager("admincp/entries/manage", array('use' => "contest::admincp@manage_entries_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contests-entries-manage'));
register_pager("admincp/contests", array('use' => "contest::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contests'));
register_pager("admincp/contest/add", array('use' => "contest::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contest-add'));
register_pager("admincp/contest/manage", array('use' => "contest::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contest-manage'));
register_pager("admincp/contest/categories", array('use' => "contest::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contest-categories'));
register_pager("admincp/contest/categories/add", array('use' => "contest::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contest-categories-add'));
register_pager("admincp/contest/category/manage", array('use' => "contest::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contest-manage-category'));
register_pager("admincp/contest/announcement", array('use' => "contest::admincp@announcement_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contests-ann-manage'));
register_pager("admincp/contest/ajax", array('use' => "contest::admincp@ajax_pager", 'filter' => 'admin-auth', 'as' => 'admincp-contest-ajax'));

register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang('contest::contests-manager'), '#', 'admin-contests');
    get_menu("admin-menu", "plugins")->findMenu('admin-contests')->addMenu(lang('contest::manage-categories'), url_to_pager("admincp-contest-categories"), "categories");
    get_menu("admin-menu", "plugins")->findMenu('admin-contests')->addMenu(lang('contest::manage-contests'), url_to_pager("admincp-contests"), "manage");
    get_menu("admin-menu", "plugins")->findMenu('admin-contests')->addMenu(lang('contest::add-new-contest'), url_to_pager("admincp-contest-add"), "add");
    get_menu("admin-menu", "plugins")->findMenu('admin-contests')->addMenu(lang('contest::manage-announcement'), url_to_pager("admincp-contests-ann-manage"), "manage-ann");

});

register_hook("display.notification", function($notification) {
    if($notification['type'] == 'contest.like') {
        return view("contest::notifications/like", array('notification' => $notification, 'type' => 'like'));
    } elseif($notification['type'] == 'contest.like.react') {
        return view("contest::notifications/react", array('notification' => $notification));
    } elseif($notification['type'] == 'contest.dislike') {
        return view("contest::notifications/like", array('notification' => $notification, 'type' => 'dislike'));
    } elseif($notification['type'] == 'contest.like.comment') {
        return view("contest::notifications/like-comment", array('notification' => $notification, 'type' => 'like'));
    } elseif($notification['type'] == 'contest.dislike.comment') {
        return view("contest::notifications/like-comment", array('notification' => $notification, 'type' => 'dislike'));
    } elseif($notification['type'] == 'contest.comment') {
        $contest = get_contest($notification['type_id']);
        if($contest) {
            return view("contest::notifications/comment", array('notification' => $notification, 'contest' => $contest));
        } else {
            delete_notification($notification['notification_id']);
        }
    } elseif($notification['type'] == 'contest.comment.reply') {
        return view("contest::notifications/reply", array('notification' => $notification));
    }
    elseif($notification['type'] == 'contest.announcement') {
        $contest = get_contest($notification['type_id']);
        return view("contest::notifications/announcement", array('notification' => $notification,'contest'=>$contest));
    }elseif($notification['type'] == 'contest.voted.entry') {
        $contest = get_contest($notification['type_id']);
        return view("contest::notifications/voted", array('notification' => $notification,'contest'=>$contest));
    }
});

register_hook("contest.voted.entry",function($contest_id,$entry_type,$entry_id,$user_id){
   //notify the entry_owner that some one has voted his entry
    $contest = get_contest($contest_id);
    $entry = getContestEntry($contest,$entry_id);
    if($entry){
        $owner = $entry['user_id'];
        //notify owner
        send_notification_privacy('notify-site-tag-you', $owner, 'contest.voted', $contest_id,$entry);
    }
});

register_hook("contest.new.announcement",function($contest_id){
   //notify the contest followers about announcements
    $followers = getContestFollowers($contest_id);
    if($followers){
        $contest = get_contest($contest_id);
        if($contest){
            foreach ($followers as $k=>$f){
                $user_id = $f['user_id'];
                send_notification_privacy('notify-site-tag-you', $user_id, 'contest.announcement', $contest_id,$contest);
            }
        }
    }
    return true;
});

register_hook("like.item", function($type, $typeId, $userid) {
    if($type == 'contest') {
        $contest = get_contest($typeId);
        if($contest['user_id'] and $contest['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $contest['user_id'], 'contest.like', $typeId, $contest);
        }
    } elseif($type == 'comment') {
        $comment = find_comment($typeId, false);
        if($comment and $comment['user_id'] != get_userid()) {
            if($comment['type'] == 'contest') {
                $contest = get_contest($comment['type_id']);
                send_notification_privacy('notify-site-like', $comment['user_id'], 'contest.like.comment', $comment['type_id'], $contest);
            }
        }
    }
});

register_hook("like.react", function($type, $typeId, $code, $userid) {
    if($type == 'contest') {
        $contest = get_contest($typeId);
        if($contest['user_id'] and $contest['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $contest['user_id'], 'contest.like.react', $typeId, $contest, $code);
        }
    }
});

register_hook("comment.add", function($type, $typeId, $text) {
    if($type == 'contest') {
        $contest = get_contest($typeId);
        $subscribers = get_subscribers($type, $typeId);
        foreach($subscribers as $userid) {
            if($userid != get_userid()) {
                send_notification_privacy('notify-site-comment', $userid, 'contest.comment', $typeId, $contest, null, $text);
            }
        }

    }
});

register_hook("reply.add", function($commentId, $type, $typeId, $text) {
    if($type == 'contest') {
        $contest = get_contest($typeId);
        $subscribers = get_subscribers('comment', $commentId);
        foreach($subscribers as $userid) {
            if($userid != get_userid()) {
                send_notification_privacy('notify-site-reply-comment', $userid, 'contest.comment.reply', $typeId, $contest, null, $text);
            }
        }
    }
});

register_pager("{id}/contests", array("use" => "contest::user-profile@contests_pager", "as" => "profile-contests", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-\.]+'));


register_hook('profile.started', function($user) {
    add_menu('user-profile-more', array('title' => lang('contest::contests'), 'as' => 'contests', 'link' => profile_url('contests', $user)));
});

add_available_menu('contest::contests', 'contests', 'ion-ios-star-half');

register_hook("shortcut.menu.images",function($arr){
    $arr['ion-ios-star-half'] = img("contest::image/contesticon.png");
    return $arr;
});

//add to feed
register_hook('contest.added', function($contestId, $contest) {
    if($contest['entity_type'] == 'user' and $contest['status']) {
        if(plugin_loaded('activity')){
            add_activity(contestUrl($contest), null, 'contest', $contestId, $contest['privacy']);
        }
        add_feed(array(
            'entity_id' => $contest['entity_id'],
            'entity_type' => $contest['entity_type'],
            'type' => 'feed',
            'type_id' => 'contest-added',
            'type_data' => $contest['id'],
            'privacy' => $contest['privacy'],
            'images' => '',
            'auto_post' => true,
            'can_share' => 1,
            'location' => 'contest-added'
        ));
    }
});

register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "contest-added") {
        $cid = $feed['type_data'];
        $contest = get_contest($cid);
        if($contest)
            $str =  lang('contest::created-a-new');
        $str .= '<a ajax="true" href="'.contestUrl($contest).'">';
        $str .= ' '.lang('contest::contest').'</a>';
        echo $str;
    }
});

register_hook("feed.arrange", function($feed) {
    if ($feed['location'] == 'contest-added') {
        $feed['location'] = '';
        $contest = get_contest($feed['type_data']);
        if (!$contest) {
            $feedId = $feed['feed_id'];
            db()->query("DELETE FROM feeds WHERE feed_id='{$feedId}'");
            return false;
        };
        $feed['contest'] = $contest;
    }
    return $feed;
});

register_hook("feed.content", function($feed) {
    if ($feed['type_id'] == 'contest-added') {
        if (isset($feed['contest']) and $feed['contest']) {
            $contest = $feed['contest'];
            echo  view('contest::feed_view',array('contest' =>$contest));
        }
    }
});
register_hook("activity.title", function($title, $activity, $user) {
    switch($activity['type']) {
        case 'contest':
            $contestId = $activity['type_id'];
            $contest = get_contest($contestId);
            if(!$contest) return "invalid";
            $link = $contest['slug'];
            $owner = get_contest_publisher($contest);
            $owner['link'] = url($owner['id']);
            return activity_form_link($owner['link'], $owner['name'], true)." ".lang("activity::added-new")." ".activity_form_link($activity['link'], lang('contest::contest'), true, true);
            break;
    }
    return $title;
});
