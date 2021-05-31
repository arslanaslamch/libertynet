<?php
load_functions('creditgift::creditgift');
register_asset("creditgift::css/creditgift.css");
register_asset("creditgift::js/creditgift.js");
register_pager("creditgift", array('use' => 'creditgift::creditgift@creditgift_pager', 'filter' => 'auth', 'as' => 'creditgift'));
register_pager("creditgift/creditrank", array('use' => 'creditgift::creditgift@creditrank_pager', 'filter' => 'auth', 'as' => 'creditgift-creditrank'));
register_pager("creditgift/pay", array('use' => 'creditgift::creditgift@creditgift_spend_pager', 'filter' => 'auth', 'as' => 'creditgift-pay'));
register_pager("creditgift/payment/success", array('use' => "creditgift::creditgift@creditgift_payment_success_pager", 'filter' => 'auth', 'as' => 'creditgift-payment-success'));
register_pager("creditgift/payment/cancel", array('use' => "creditgift::creditgift@creditgift_payment_cancel_pager", 'filter' => 'auth', 'as' => 'creditgift-payment-cancel'));
register_pager("creditgift/ajax_show_friends", array('use' => "creditgift::creditgift@creditgift_ajax_show_friends", 'filter' => 'auth', 'as' => 'creditgift-show-friends'));
register_pager("creditgift/purchase", array('use' => "creditgift::creditgift@creditgift_purchase_pager", 'filter' => 'auth', 'as' => 'creditgift-purchase'));
register_pager("creditgift/receipt", array('use' => "creditgift::creditgift@creditgift_receipt_pager", 'filter' => 'auth', 'as' => 'creditgift-receipt'));
register_hook("admin-started", function() {
	get_menu('admin-menu', 'plugins')->addMenu(lang("creditgift::creditgift-manager"), url('admincp/creditgift'), "creditgift-manager");

	get_menu("admin-menu", "plugins")->findMenu("creditgift-manager")->addMenu(lang("creditgift::manage-rank"), url('admincp/creditgift/rank'), "manage-rank");
	get_menu("admin-menu", "plugins")->findMenu("creditgift-manager")->addMenu(lang("creditgift::send-credit"), url('admincp/creditgift/send'), "send-credit");
	get_menu("admin-menu", "plugins")->findMenu("creditgift-manager")->addMenu(lang("creditgift::credit-sale-plan"), url('admincp/creditgift/salesplan'), "credit-sale-plan");
	get_menu("admin-menu", "plugins")->findMenu("creditgift-manager")->addMenu(lang("creditgift::credit-sales"), url('admincp/creditgift/creditsales'), "credit-sales");
});

register_pager("admincp/creditgift/action/batch", array('use' => "ads::admincp@creditgift_action_batch_pager", 'filter' => 'admin-auth', 'as' => 'admin-creditgift-batch-action'));

register_pager("admincp/creditgift/rank", array('use' => "creditgift::admincp@creditgift_rank_pager", 'filter' => 'admin-auth', 'as' => 'admincp-creditgift-rank'));
register_pager("admincp/creditgift/edit", array('use' => "creditgift::admincp@creditgift_edit_pager", 'filter' => 'admin-auth', 'as' => 'admincp-creditgift-edit'));
register_pager("admincp/creditgift/send", array('use' => "creditgift::admincp@creditgift_send_pager", 'filter' => 'admin-auth', 'as' => 'admincp-creditgift-send'));
register_pager("admincp/creditgift/salesplan", array('use' => "creditgift::admincp@creditgift_salesplan_pager", 'filter' => 'admin-auth', 'as' => 'admincp-salesplan'));
register_pager("admincp/creditgift/editplan", array('use' => "creditgift::admincp@creditgift_editplan_pager", 'filter' => 'admin-auth', 'as' => 'admincp-editplan'));
register_pager("admincp/creditgift/creditsales", array('use' => "creditgift::admincp@creditgift_creditsales_pager", 'filter' => 'admin-auth', 'as' => 'admincp-creditsales'));
if(is_loggedIn()) {
	add_menu("dashboard-menu", array("icon" => "<i class='ion-social-bitcoin'></i>", "id" => "creditgift", "title" => lang("creditgift::creditgift"), "link" => profile_url('creditgift')));
}
register_hook("creditgift.addcredit.signup", function($params) {
	$user_id = $params[0];
	$type = "Signup Bonus";
	$amount = config('creditgift-signup-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
	return $params;
});
register_hook("creditgift.addcredit.addfriend", function($userid) {
	$user_id = $userid;
	$type = "Add Friend Bonus";
	$amount = config('creditgift-addfriend-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.addvideo", function($userid) {
	$user_id = $userid;
	$type = "Adding 1  Video";
	$amount = config('creditgift-videoupload-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.addphoto", function($userid) {
	$user_id = $userid;
	$type = "Adding 1 Photo";
	$amount = config('creditgift-photoupload-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.like", function($userid) {
	$user_id = $userid;
	$type = "Like Bonus";
	$amount = config('creditgift-like-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.dislike", function($userid) {
	$user_id = $userid;
	$type = "Like Deleted";
	$amount = config('creditgift-like-bonus');
	remove_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.comment", function($userid) {
	$user_id = $userid;
	$type = "Adding Comment";
	$amount = config('creditgift-comment-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.uncomment", function($userid) {
	$user_id = $userid;
	$type = "Comment Deleted";
	$amount = config('creditgift-comment-bonus');
	remove_creditgift_bonus($user_id, $amount, $type);
});
register_hook("creditgift.addcredit.share", function($userid) {
	$user_id = $userid;
	$type = "Sharing Bonus";
	$amount = config('creditgift-share-bonus');
	add_creditgift_bonus($user_id, $amount, $type);
});
register_hook("display.notification", function($notification) {
	if($notification['type'] == 'credit.send') {
		$transaction = get_creditgift_transaction($notification['type_id']);
		return view('creditgift::notifications/credit_received', array('notification' => $notification, 'transaction' => $transaction));
	}
});

register_hook('membership.segment.allowed', function($allowed_segments) {
	$allowed_segments[] = 'creditgift';
	return $allowed_segments;
});

register_hook('user.welcome.segment.allowed', function($allowed_segments) {
	$allowed_segments[] = 'creditgift';
	return $allowed_segments;
});

register_hook("payment.buttons.extend", function($type, $id, $name, $description, $price, $return_url, $cancel_url, $coupon = null) {
	$user_id = get_userid();
	$creditgift_balance = get_creditgift_balance($user_id);
	$credit_balance = $creditgift_balance[0]['credit_balance'];
	$credit_worth = config('creditgift-credit-worth', 1);
	$credit_worth = $credit_worth ? $credit_worth : 1;
	$credit_price = $price * $credit_worth;
	if($credit_balance >= $credit_price) {
		echo '<div class="payment-method credit"><a href="'.url_to_pager('creditgift-pay').'?id='.$id.'&type='.$type.'&name='.urlencode($name).'&description='.urlencode($description).'&price='.$price.'&return_url='.urlencode($return_url).'&cancel_url='.urlencode($cancel_url).'&coupon='.$coupon.'" class="btn btn-info action-button" >'.config('site_title').' '.lang('creditgift::pay').'</a></div>';
	}
});

add_available_menu('creditgift::creditgift', 'creditgift', 'ion-social-bitcoin');

register_hook('user.delete', function($user_id) {
	$db = db();
	$db->query("DELETE FROM confession WHERE user_id = '".$user_id."'");
	$db->query("DELETE FROM confession_rating WHERE user_id = '".$user_id."'");
	return $user_id;
});