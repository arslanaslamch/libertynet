<?php
load_functions("statistic::statistic");
register_asset("statistic::css/statistic.css");
register_asset("statistic::js/statistic.js");

// account settings menu
register_hook('account.settings.menu', function () {
    add_menu('account-menu', array('id' => 'user-sessions', 'link' => url_to_pager('account').'?action=sessions', 'title' => lang('statistic::sessions'), 'icon' => array('class' => 'fa fa-save', 'color' => '#673ab7')));
});

register_hook('system.started', function ($app) {
    if (is_loggedIn()){
        $device = get_user_device_statistic();
        if($device){
            $checkDeviceSessionStatus = check_device_session_status($device);
            if($checkDeviceSessionStatus && $checkDeviceSessionStatus['active'] == '0'){
				$cache_name = 'device-'.$checkDeviceSessionStatus['ip'].'-'.$checkDeviceSessionStatus['os'].'-'.$checkDeviceSessionStatus['user_id'];
				if (!cache_exists($cache_name)) {
					logout_user();
					set_cacheForever($cache_name, $checkDeviceSessionStatus);
					redirect(url());
				} else {
					forget_cache($cache_name);
					user_statistic_add_sessions();
				}
            }
        }
    }
});
// account settings content
register_hook('account.settings', function ($action) {
    if ($action == 'sessions') {
        app()->setTitle(lang('statistic::sessions'));
        $delete = input('delete');
        $message = null;
        if ($delete) {
            $sessionId = input('id');
            user_statistic_delete_sessions($sessionId);
        }
        $sessions = user_statistic_sessions(get_userid());
        return view('statistic::sessions', array('sessions' => $sessions));
    }
});