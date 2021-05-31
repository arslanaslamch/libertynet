<?php
load_functions("like::like");
load_functions("like::rating");

register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		//register assets
		register_asset("like::css/like.css");
		register_asset("like::js/like.js");
		register_asset("like::js/rating.js");
	}
});

register_hook("footer", function() {
	echo view('like::modal');
});
register_get_pager("like/item", array('use' => 'like::like@like_item_pager', 'filter' => 'auth'));
register_get_pager("like/react", array('use' => 'like::like@react_pager', 'filter' => 'auth'));
register_get_pager("like/load/people", array('use' => 'like::like@load_people_pager'));

register_get_pager("add/rating", array('use' => 'like::rating@add_rating_pager', 'filter' => 'auth'));

register_hook('user.delete', function($userid) {
	db()->query("DELETE FROM likes WHERE user_id='{$userid}'");
    db()->query("DELETE FROM ratings WHERE user_id='{$userid}'");
});