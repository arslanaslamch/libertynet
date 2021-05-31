<?php
function creditgift_pager($app) {
	$app->setTitle(lang('creditgift::creditgift'));
	$msg = '';
	$transaction = get_creditgift_transactions(get_userid());
	$balance = get_creditgift_balance(get_userid());
	//add_creditgift_signup($userid);
	return $app->render(view('creditgift::creditgift', array('msg' => $msg, 'transactions' => $transaction, 'balances' => $balance)));

}

function creditrank_pager($app) {
	$app->setTitle(lang('creditgift::creditgift-creditrank'));
	$msg = '';
	$rank = get_creditgift_rank();
	return $app->render(view('creditgift::creditrank', array('msg' => $msg, 'ranks' => $rank)));

}

function creditgift_ajax_show_friends($app) {
	$message = null;
	$cost = input('amount');
	$id = $cost;
	$friend = input('friend');
	if($friend && ($cost > 0)) {
		$friend_name = get_user_name($friend);
		$sender_name = get_user_name(get_userid());
		$credit_reason_1 = "Sent Credit to ".$friend_name;
		$credit_reason_2 = "Recieved Credit from ".$sender_name;
		$send = creditgift_send($cost, $friend, $credit_reason_1, $credit_reason_2);
		return json_encode($send);
		redirect_to_pager('creditgift');
	}
	$user_id = get_userid();
	$credit = get_creditgift_balance($user_id)[0];
	$limit = config('gift-friends-limit', 5);
	$page = input('page', 1);
	$friends = list_connections(get_friends(), $limit);
	$total_pages = ceil(count(get_friends()) / $limit);
	return $app->view('creditgift::ajax_show_friends', array('id' => $id, 'credit' => $credit, 'friends' => $friends, 'total_pages' => $total_pages, 'page' => $page, 'message' => $message));
}

function creditgift_purchase_pager($app) {
	$app->setTitle(lang('creditgift::creditgift-purchase'));
	$msg = '';
	$purchase = get_creditgift_plan();
	return $app->render(view('creditgift::purchase', array('msg' => $msg, 'purchases' => $purchase)));

}

function creditgift_receipt_pager($app) {
	$app->setTitle(lang('creditgift::creditgift-purchase'));
	$msg = '';
	$planId = input('planId');
	return $app->render(view('creditgift::receipt', array('msg' => $msg, 'planId' => $planId)));
}

function payment_pager($app) {
	$id = segment(3);
	$credits = get_creditgift_planId($id);
	if(!$credits or $credits['id'] != $id) return redirect_to_pager('creditgift-purchase');
	$app->setTitle(lang('creditgift::credit-purchase'));
    return creditgift_render(view('ads::activate', array('credits' => $credits)));
}

function creditgift_render($content, $type = "manage") {
	return app()->render($content);
}

function creditgift_spend_pager($app) {
	$user_id = get_userid();
	$type = input('type');
	$id = input('id');
	$name = input('name');
	$description = urldecode(input('name'));
	$price = input('price');
	$return_url = urldecode(input('return_url'));
	$cancel_url = urldecode(input('cancel_url'));
	$credit = get_creditgift_balance($user_id);
	$creditgift_balance = $credit[0]['credit_balance'];
	$credit_price = $price * config('creditgift-credit-worth', 1);
	$credit_reason = $name;
	$paid = creditgift_spend($credit_price, $credit_reason);
	fire_hook("payment.aff", null, array($type, $id));
	if($paid) {
		if(config('promotion-coupon', 0)) {
			fire_hook('payment.coupon.completed', input('coupon'));
		}
		return redirect($return_url);
	} else {
		return redirect($cancel_url);
	}
}

function creditgift_send_pager($app) {
	$app->setTitle(lang('creditgift::creditgift-send'));
	$msg = '';
	$member = get_creditgift_user();
	return $app->render(view('creditgift::send', array('msg' => $msg, 'members' => $member)));
}

function creditgift_payment_success_pager($app) {
	CSRFProtection::validate();
	$app->setTitle(lang('creditgift::creditgift-purchase'));
	$id = input('id');
	$plan = get_creditgift_planId($id);
	activate_creditgift($plan);
	$msg = '';
	return $app->render(view('creditgift::payment/success', array('msg' => $msg, 'planId' => $id)));
}

function creditgift_payment_cancel_pager($app) {
	$app->setTitle(lang('creditgift::creditgift-canceled'));
	return $app->render(view('creditgift::payment/cancel'));
}