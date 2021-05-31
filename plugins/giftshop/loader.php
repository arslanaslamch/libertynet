<?php
load_functions('giftshop::giftshop');
register_asset("giftshop::css/giftshop.css");
register_asset("giftshop::js/giftshop.js");
register_pager("giftshop", array('use' => 'giftshop::giftshop@giftshop_pager', 'filter' => 'auth', 'as' => 'giftshop'));
register_pager("giftshop/ajax_show_friends", array('use' => "giftshop::giftshop@giftshop_ajax_show_friends", 'filter' => 'auth', 'as' => 'giftshop-show-friends'));
register_pager("giftshop/category", array('use' => "giftshop::giftshop@giftshop_category_pager", 'filter' => 'auth', 'as' => 'giftshop-category'));
register_pager("giftshop/mine", array('use' => "giftshop::giftshop@giftshop_mine_pager", 'filter' => 'auth', 'as' => 'giftshop-mine'));


//admincp pages
//register the menu on admin cp
register_hook("admin-started", function() {
	get_menu('admin-menu', 'plugins')->addMenu(lang("giftshop::giftshop-manager"), url('admincp/giftshop'), "giftshop-manager");

	get_menu("admin-menu", "plugins")->findMenu("giftshop-manager")->addMenu(lang("giftshop::manage-gift"), url('admincp/giftshop/manage'), "giftshop-manage");
	get_menu("admin-menu", "plugins")->findMenu("giftshop-manager")->addMenu(lang("giftshop::gift-category"), url('admincp/giftshop/category'), "giftshop-category");
});

register_pager("admincp/giftshop/action/batch", array('use' => "giftshop::admincp@giftshop_action_batch_pager", 'filter' => 'admin-auth', 'as' => 'admin-giftshop-batch-action'));

register_pager("admincp/giftshop/manage", array('use' => 'giftshop::admincp@giftshop_manage_pager', 'as' => 'admincp-giftshop', 'filter' => 'admin-auth'));
register_pager("admincp/giftshop/category", array('use' => "giftshop::admincp@giftshop_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-giftshop-category'));
register_pager("admincp/giftshop/edit", array('use' => "giftshop::admincp@giftshop_category_edit_pager", 'filter' => 'admin-auth', 'as' => 'admincp-giftshop-category-edit'));
register_pager("admincp/giftshop/giftedit", array('use' => "giftshop::admincp@giftshop_gift_edit_pager", 'filter' => 'admin-auth', 'as' => 'admincp-giftshop-gift-edit'));

add_available_menu('giftshop::giftshop', 'giftshop', 'ion-bag');

register_hook('admin.statistics', function($stats) {
	$stats['giftshop'] = array(
		'count' => giftshop_num_gifts(),
		'title' => lang('giftshop::gifts'),
		'icon' => 'ion-bag',
		'link' => url_to_pager('admincp-giftshop'),
	);
	return $stats;
});

register_hook('display.notification', function($notification) {
	if($notification['type'] == 'gift.send') {
		$gift = giftshop_get_gift($notification['type_id'])[0];
		return view('giftshop::notifications/gift_received', array('notification' => $notification, 'gift' => $gift));
	}
});