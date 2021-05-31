<?php
load_functions("notification::notification");
register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		//register assets
		register_asset("notification::css/notification.css");
		register_asset("notification::js/notification.js");
	    if(config('pusher-driver') == 'fcm') {
            register_asset("js/firebase-app.js");
            register_asset("js/firebase-database.js");
            register_asset("js/firebase-messaging.js");
        }
	}
    register_asset("notification::js/service-worker.js", 'service-worker');
});


register_get_pager("notification/load/latest", array('use' => 'notification::notification@notification_load_pager', 'filter' => 'auth'));
register_get_pager("notification/preload", array('use' => 'notification::notification@preload_pager', 'filter' => 'auth'));
register_get_pager("notification/mark", array("use" => 'notification::notification@notification_mark_pager', 'filter' => 'auth'));
register_get_pager("notification/delete", array("use" => 'notification::notification@notification_delete_pager', 'filter' => 'auth'));
register_get_pager("notifications", array("use" => 'notification::notification@notifications_pager', 'filter' => 'auth', 'as' => 'notifications'));

register_hook('user.delete', function($userid) {
	db()->query("DELETE FROM notifications WHERE from_userid='{$userid}' OR to_userid='{$userid}'");
});

register_hook('footer', function() {
	if(is_loggedIn() and !isMobile()) echo "<div id='notification-popup'><div id='content'></div><a onclick='return notification.pop.close()' class='close' href=''><i class='ion-close'></i></a></div>";
});

register_hook('before-render-css', function($html) {
	$html .= '<link rel="manifest" href="'.asset_link('js/manifest.json').'">';

	return $html;
});

register_hook('service-worker.js.constants', function($constants) {
    $enable_background_notification = config('enable-background-notification', 1);
    $constants .= "\nconst enableBackgroundNotification = $enable_background_notification;";
    return $constants;
});

register_hook('pusher.notifications', function($pusher, $type, $detail, $template) {
    $result = $template;
    if($type == 'notification') {
        $count = count($detail);
        if($count) {
            foreach($detail as $notification_id) {
                $notification_id = is_array($notification_id) ? $notification_id[0] : $notification_id;
                $notifications = fire_hook('notification.pusher.notifications', array('notifications' => array()), array(find_notification($notification_id), $template))['notifications'];
                if($notifications) {
                    $count--;
                }
                foreach ($notifications as $notification) {
                    $pusher['notifications'][] = $notification;
                }
            }
            if($count) {
                $result['options']['title'] = $count > 1 ? lang('notification::notifications') : lang('notification::notification');
                $result['options']['body'] = $count > 1 ? lang('notification::notifications-notification', array('num' => $count)) : lang('notification::notification-notification', array('num' => $count));
                $result['options']['link'] = url_to_pager('notifications');
                $result['type'] = 'notification';
                $result['status'] = true;
                $pusher['notifications'][] = $result;
            }
        }
    }
    return $pusher;
});