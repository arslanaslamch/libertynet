<?php
function giftshop_pager($app) {
	$app->setTitle(lang('creditgift::giftshop'));
	$msg = '';
	$gift = get_giftshop_gift_all();
	return $app->render(view('giftshop::giftshop', array('msg' => $msg, 'gifts' => $gift)));
}

function giftshop_category_pager($app) {
	$app->setTitle(lang('creditgift::giftshop'));
	$msg = '';
	$category = input('cat');
	$gift = get_giftshop_gift_cat($category);
	return $app->render(view('giftshop::giftshop', array('msg' => $msg, 'gifts' => $gift)));
}

function giftshop_mine_pager($app) {
	$app->setTitle(lang('creditgift::my-gift'));
	$msg = '';
	$gift_id = get_giftshop_gift_m();
	return $app->render(view('giftshop::mine', array('msg' => $msg, 'gift_ids' => $gift_id)));
}

function giftshop_ajax_show_friends($app) {
	$message = null;
	$id = input('id');
	$cost = input('cost');
	$credit_reason = "Purchased Gift";
	$friend = input('friend');
	if($friend) {
		$send = giftshop_send($id, $friend);
		creditgift_spend($cost, $credit_reason);
		return json_encode($send);
		//redirect_to_pager('giftshop');
	}
	$gift = get_giftshop_gift_id($id);
	$limit = config('gift-friends-limit', 5);
	$page = input('page', 1);
	$friend_ids = get_friends();
	$friend_ids[] = get_userid();
	$friends = list_connections($friend_ids, $limit);
	$total_pages = ceil(count(get_friends()) / $limit);
	return $app->view('giftshop::ajax_show_friends', array('id' => $id, 'gift' => $gift, 'friends' => $friends, 'total_pages' => $total_pages, 'page' => $page, 'message' => $message));
}