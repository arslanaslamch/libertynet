<?php
load_functions("onlinetv::onlinetv");
register_asset("onlinetv::css/onlinetv.css");
register_asset("onlinetv::js/onlinetv.js");
register_hook("role.permissions", function($roles) {
	$roles[] = array(
		'title' => lang('onlinetv::onlinetv-permissions'),
		'description' => '',
		'roles' => array(
			'can-create-onlinetv' => array('title' => lang('onlinetv::can-create-onlinetv'), 'value' => 1),

		)
	);
	return $roles;
});
register_pager("onlinetvs", array('use' => 'onlinetv::onlinetv@onlinetv_pager', 'as' => 'onlinetvs'));
register_pager("onlinetv/add", array('use' => 'onlinetv::onlinetv@add_onlinetv_pager', 'filter' => 'auth', 'as' => 'onlinetv-add'));
register_pager("onlinetv/pay", array('use' => 'onlinetv::onlinetv@pay_onlinetv_pager', 'filter' => 'auth', 'as' => 'purchase-tv'));
register_pager("onlinetv/manage", array('use' => 'onlinetv::onlinetv@manage_pager', 'as' => 'onlinetv-manage', 'filter' => 'auth'));
register_pager("onlinetvs/api", array('use' => 'onlinetv::onlinetv@onlinetv_api_pager'));
register_pager("onlinetv/{slugs}", array('use' => 'onlinetv::onlinetv@onlinetv_page_pager', 'as' => 'onlinetv-page'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));


register_pager("admincp/onlinetv", array('use' => "onlinetv::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-onlinetvs'));
register_pager("admincp/onlinetv/add", array('use' => "onlinetv::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-onlinetv-addnew'));
register_pager("admincp/onlinetv/manage", array('use' => "onlinetv::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-onlinetv-manage'));
register_pager("admincp/onlinetv/categories", array('use' => "onlinetv::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-onlinetv-categories'));
register_pager("admincp/onlinetv/categories/add", array('use' => "onlinetv::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-onlinetv-categories-add'));
register_pager("admincp/onlinetv/category/manage", array('use' => "onlinetv::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-onlinetv-manage-category'));
register_pager("admincp/onlinetv/import", array('use' => "onlinetv::admincp@import_pager", 'filter' => 'admin-auth', 'as' => 'import-tv'));


register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		register_asset("help::css/help.css");
		register_asset("help::js/help.js");
	}
});

register_hook("admin-started", function() {

	add_menu("admin-menu", array('icon' => 'ion-document-text', "id" => "admin-onlinetvs", "title" => lang('onlinetv::manage-onlinetvs'), "link" => '#'));
	get_menu("admin-menu", "plugins")->addMenu(lang('onlinetv::onlinetvs-manager'), '#', 'admin-onlinetvs');
	get_menu("admin-menu", "plugins")->findMenu('admin-onlinetvs')->addMenu(lang('onlinetv::manage-tvs'), url_to_pager("admincp-onlinetvs"), "manage");
    get_menu("admin-menu", "plugins")->findMenu('admin-onlinetvs')->addMenu(lang('onlinetv::manage-categories'), url_to_pager("admincp-onlinetv-categories"), "categories");
    get_menu("admin-menu", "plugins")->findMenu('admin-onlinetvs')->addMenu(lang('onlinetv::add-new-tv'), url_to_pager("admincp-onlinetv-addnew"), "add-new");
    get_menu("admin-menu", "plugins")->findMenu('admin-onlinetvs')->addMenu(lang('onlinetv::import-tv'), url_to_pager("import-tv"), "imports-tv");
});

register_hook('admin.statistics', function($stats) {
	$stats['onlinetvs'] = array(
		'count' => count_total_onlinetvs(),
		'title' => lang('onlinetv::onlinetvs'),
		'icon' => 'ion-videocamera',
		'link' => url_to_pager('admincp-onlinetvs'),
	);
	return $stats;
});

register_hook("comment.add", function($type, $typeId, $text) {
	if($type == 'onlinetv') {
		$onlinetv = get_onlinetv($typeId);
		$subscribers = get_subscribers($type, $typeId);
		foreach($subscribers as $userid) {
			if($userid != get_userid()) {
				send_notification_privacy('notify-site-comment', $userid, 'onlinetv.comment', $typeId, $onlinetv, null, $text);
			}
		}

	}
});

register_hook("like.item", function($type, $typeId, $userid) {
	if($type == 'onlinetv') {
		$onlinetv = get_onlinetv($typeId);
		if($onlinetv['user_id'] and $onlinetv['user_id'] != get_userid()) {
			send_notification_privacy('notify-site-like', $onlinetv['user_id'], 'onlinetv.like', $typeId, $onlinetv);
		}
	} elseif($type == 'comment') {
		$comment = find_comment($typeId, false);
		if($comment and $comment['user_id'] != get_userid()) {
			if($comment['type'] == 'onlinetv') {
				$onlinetv = get_onlinetv($comment['type_id']);
				send_notification_privacy('notify-site-like', $comment['user_id'], 'onlinetv.like.comment', $comment['type_id'], $onlinetv);
			}
		}
	}
});

register_hook("comment.add", function($type, $typeId, $text) {
	if($type == 'onlinetv') {
		$onlinetv = get_onlinetv($typeId);
		$subscribers = get_subscribers($type, $typeId);
		if(!in_array($onlinetv['user_id'], $subscribers)) {
			$subscribers[] = $onlinetv['user_id'];
		}
		foreach($subscribers as $userid) {
			if($userid != get_userid()) {
				send_notification_privacy('notify-site-comment', $userid, 'onlinetv.comment', $typeId, $onlinetv, null, $text);
			}
		}

	}
});

register_hook("display.notification", function($notification) {
	if($notification['type'] == 'onlinetv.like') {
		return view("onlinetv::notifications/like", array('notification' => $notification, 'onlinetv' => unserialize($notification['data'])));
		delete_notification($notification['notification_id']);
	} elseif($notification['type'] == 'onlinetv.like.comment') {
		return view("onlinetv::notifications/like-comment", array('notification' => $notification, 'onlinetv' => unserialize($notification['data'])));
		delete_notification($notification['notification_id']);
	} elseif($notification['type'] == 'onlinetv.comment') {
		return view("onlinetv::notifications/comment", array('notification' => $notification, 'onlinetv' => unserialize($notification['data'])));
		delete_notification($notification['notification_id']);
	}
});

add_menu_location('onlinetvs-menu', lang('onlinetv::onlinetvs-menu'));
add_available_menu('onlinetv::onlinetv-menu', 'onlinetvs', 'ion-videocamera');

register_hook("shortcut.menu.images",function($arr){
    $arr['ion-videocamera'] = img("onlinetv::images/tv.png");
    return $arr;
});

register_pager("{id}/onlinetvs", array("use" => "onlinetv::user-profile@onlinetvs_pager", "as" => "profile-onlinetvs", 'filter' => 'profile'))
	->where(array('id' => '[a-zA-Z0-9\_\-\.]+'));


register_hook('profile.started', function($user) {
	add_menu('user-profile-more', array('title' => lang('onlinetv::onlinetvs'), 'as' => 'onlinetvs', 'link' => profile_url('onlinetvs', $user)));
});

register_block("onlinetv::block/profile-recent", lang('onlinetv::user-profile-recent-onlinetvs'), null, array(
		'limit' => array(
			'title' => lang('list-limit'),
			'description' => lang('list-limit-desc'),
			'type' => 'text',
			'value' => 6
		))
);

register_block("onlinetv::block/latest", lang('onlinetv::latest-onlinetvs'), null, array(
		'limit' => array(
			'title' => lang('list-limit'),
			'description' => lang('list-limit-desc'),
			'type' => 'text',
			'value' => 6
		),)
);


//page blocks
register_hook('admin-started', function() {
	register_block_page('onlinetvs', lang('onlinetv::onlinetvs'));

});

register_hook('user.delete', function($userid) {
	$d = db()->query("SELECT * FROM onlinetvs WHERE user_id='{$userid}'");
	while($onlinetv = $d->fetch_assoc()) {
		delete_onlinetv($onlinetv['id']);
	}
});

register_hook('uid.check', function($result, $value, $type = null, $type_id = null) {
	if(!$type || $type == 'onlinetv') {
		$onlinetv = get_onlinetv($value);
		if($onlinetv) {
			if(!$type_id || ($type_id && $type_id != $onlinetv['id'])) {
				$result[0] = false;
			}
		}
	}
	return $result;
});

register_hook('onlinetv.added', function($onlinetvId, $onlinetv) {
    if($onlinetv['entity_type'] == 'user' and $onlinetv['status']) {
        add_activity($onlinetv['slug'], null, 'onlinetv', $onlinetvId, $onlinetv['privacy']);
        add_feed(array(
            'entity_id' => $onlinetv['entity_id'],
            'entity_type' => $onlinetv['entity_type'],
            'type' => 'feed',
            'type_id' => 'onlinetv-added',
            'type_data' => $onlinetv['id'],
            'onlinetv' => $onlinetvId,
            'privacy' => $onlinetv['privacy'],
            'images' => '',
            'auto_post' => true,
            'can_share' => 1
        ));
    }
});

/*register_hook('feed.arrange', function($feed) {
    if(is_numeric($feed['onlinetv'])) {
        $onlinetv = get_onlinetv($feed['onlinetv']);

        if($onlinetv) {
            if($onlinetv['status'] == 0 and ($onlinetv['user_id'] != get_userid())) $feed['status'] = 0;
            $feed['onlinetvDetails'] = $onlinetv;
        }
    }
    return $feed;
});*/
register_hook("activity.title", function($title, $activity, $user) {
    switch($activity['type']) {
        case 'onlinetv':
            $onlinetvId = $activity['type_id'];
            $onlinetv = get_onlinetv($onlinetvId);
            if(!$onlinetv) return "invalid";
            $link = $onlinetv['slug'];
            $owner = get_onlinetv_publisher($onlinetv);
            $owner['link'] = url($owner['id']);
            return activity_form_link($owner['link'], $owner['name'], true)." ".lang("activity::added-new")." ".activity_form_link($activity['link'], lang('onlinetv::onlinetv'), true, true);
            break;
    }
    return $title;
});

register_hook('feed-title', function($feed) {
    if($feed['type_id'] == "onlinetv-added") {
        echo lang('onlinetv::added-onlinetv');
    }
});

/*register_hook('feed.custom.column.sql', function ($columns, $val){
    if (isset($val['onlinetv'])){
        $columns[0] = !($columns) ? '' : ',onlinetv';
    }
    return $columns;
});*/

register_hook('feed.custom.value.sql', function ($value, $val){
    if (isset($val['onlinetv'])){
        $value[0] = !($value) ? '' : ",'{$val['onlinetv']}'";
    }
    return $value;
});

/*register_hook('feeds.query.fields', function ($fields, $more = null){
    $fields .=",onlinetv";
   return $fields;
});
register_hook('feed.post.plugins.hook', function($feed) {
    if ($feed['onlinetv']){
        $onlinetv = get_onlinetv($feed['onlinetv']);
        if ($onlinetv) echo view("onlinetv::feed-content", array('onlinetv'=> $onlinetv));
    }
});*/