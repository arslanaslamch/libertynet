<?php
load_functions('auction::auction');

register_asset('auction::css/style.css');
register_asset('auction::css/datepicker.css');
register_asset('auction::js/auction.js');
register_asset('auction::js/datepicker.js');
register_asset('auction::js/jquery.countdown.js');
register_asset('auction::js/jtruncate.js');
register_asset('auction::js/jquery.bxslider.js');
register_asset('auction::css/jquery.bxslider.css');

register_pager("admincp/auction/action/batch", array('use' => "auction::admincp@auction_action_batch_pager", 'filter' => 'admin-auth', 'as' => 'admin-auction-batch-action'));

register_pager('auction/add', array('use' => 'auction::create@auction_create_pager', 'filter' => 'auth', 'as' => 'auction-add'));
register_pager('admincp/auction/category/add', array('use' => 'auction::admincp@add_category_pager', 'filter' => 'admin-auth', 'as' => 'admincp-category-add'));
register_pager('admincp/auction/category/list', array('use' => 'auction::admincp@list_category_pager', 'filter' => 'admin-auth', 'as' => 'admincp-category-list'));
register_pager('admincp/auction/category/edit', array('use' => 'auction::admincp@edit_category_pager', 'filter' => 'admin-auth', 'as' => 'admincp-category-edit'));
register_pager('admincp/auction', array('use' => 'auction::admincp@list_auction_pager', 'filter' => 'admin-auth', 'as' => 'admincp-auction-list'));
register_pager('auction', array('use' => 'auction::create@auction_list_all_pager', 'filter' => 'auth', 'as' => 'auction-list'));
register_pager('auction/detail', array('use' => 'auction::create@auction_detail', 'filter' => 'auth', 'as' => 'auction-details'));
register_pager('auction/edit', array('use' => 'auction::create@auction_edit', 'filter' => 'auth', 'as' => 'auction-edit'));
register_pager('auction/bid', array('use' => 'auction::create@auction_bid', 'filter' => 'auth', 'as' => 'auction-bid'));
register_pager('auction/cart', array('use' => 'auction::create@auction_cart', 'filter' => 'auth', 'as' => 'auction-cart'));
register_pager('auction/my-bids', array('use' => 'auction::create@auction_my_bid_pager', 'filter' => 'auth', 'as' => 'auction-my-bids'));
register_pager('auction/my-offer', array('use' => 'auction::create@auction_my_offer_pager', 'filter' => 'auth', 'as' => 'auction-my-offer'));
register_pager('auction/my-auction', array('use' => 'auction::create@auction_my_auction_pager', 'filter' => 'auth', 'as' => 'auction-mine'));
register_pager('auction/approve', array('use' => 'auction::create@auction_approve_pager', 'filter' => 'auth', 'as' => 'auction-approve'));
register_pager('auction/delete', array('use' => 'auction::create@auction_delete_pager', ',filter' => 'auth', 'as' => 'auction-delete'));
register_pager('auction/activate', array('use' => 'auction::create@auction_activate_pager', 'filter' => 'auth', 'as' => 'auction-activate'));
register_pager('auction/friends/bid', array('use' => 'auction::create@auction_friend_bid_pager', 'filter' => 'auth', 'as' => 'auction-friend-bids'));
register_pager('auction/friends', array('use' => 'auction::create@auction_friends_pager', 'filter' => 'auth', 'as' => 'auction-friends'));
register_pager('auction/ops', array('use' => 'auction::create@auction_ops_pager', 'filter' => 'auth', 'as' => 'auction-ops'));
register_pager("auction/payment/success", array('use' => "auction::create@auction_payment_success_pager", 'filter' => 'auth', 'as' => 'auction-payment-success'));
register_pager("auction/payment/cancel", array('use' => "auction::create@auction_payment_cancel_pager", 'filter' => 'auth', 'as' => 'auction-payment-cancel'));
register_pager('auction/approved/bids', array('use' => 'auction::create@auction_approved_bid_pager', 'filter' => 'auth', 'as' => 'auction-approved-bids'));


register_hook("display.notification", function($notification) {
	if($notification['type'] == 'auction.bid.added') {
		$auction = get_auction($notification['type_id']);
		if($auction) {
			$data = $auction;
			return view("auction::notifications/bid_added", array('notification' => $notification, 'auction' => $auction));
		} else {
			delete_notification($notification['notification_id']);
		}

	} elseif($notification['type'] == 'auction.offer.added') {
		$auction = get_auction($notification['type_id']);
		if($auction) {
			$data = $auction;
			return view("auction::notifications/offer_added", array('notification' => $notification, 'auction' => $data));
		} else {
			delete_notification($notification['notification_id']);
		}
	} elseif($notification['type'] == 'auction.tmp.approve') {
		$auction = get_auction($notification['type_id']);
		if($auction) {
			$data = $auction;
			return view("auction::notifications/temp_approve", array('notification' => $notification, 'auction' => $data));
		} else {
			delete_notification($notification['notification_id']);
		}
	} elseif($notification['type'] == 'auction.tmp.cancel') {
		$auction = get_auction($notification['type_id']);
		if($auction) {
			$data = $auction;
			return view("auction::notifications/temp_cancel", array('notification' => $notification, 'auction' => $data));
		} else {
			delete_notification($notification['notification_id']);
		}
	}
//    elseif ($notification['type']=='auction.added')
//    {
//        $auction=get_auction($notification['type_id']);
//        if($auction)
//        {
//            $data=$auction;
//            return view("auction::notification/auction_added", array('notification' => $notification, 'auction' => $data));
//        }
//        else
//        {
//            delete_notification($notification['notification_id']);
//        }
//    }Z
});

//Register Admin Menu
register_hook("admin-started", function() {
	get_menu("admin-menu", "plugins")->addMenu(lang('auction::auction-manager'), '#', 'admin-auctions');
	get_menu("admin-menu", "plugins")->findMenu("admin-auctions")->addMenu(lang("auction::auction-add"), url_to_pager('admincp-category-add'), 'Add Category');
	get_menu("admin-menu", "plugins")->findMenu("admin-auctions")->addMenu(lang("auction::admincp-category-title"), url_to_pager('admincp-category-list'), 'List Category');
	get_menu("admin-menu", "plugins")->findMenu("admin-auctions")->addMenu(lang("auction::admincp-auction"), url_to_pager('admincp-auction-list'), 'List Auctions');
});

register_hook('auction.bid.added', function($owner_id, $auction_id, $price) {
	send_notification($owner_id, 'auction.bid.added', $auction_id);
});
register_hook('auction.offer.added', function($owner_id, $auction_id, $price) {
	send_notification($owner_id, 'auction.offer.added', $auction_id);
});
register_hook('auction.added', function($owner_id, $auction_id, $mobile = null) {
	$sql = "UPDATE auction_new SET mobile = {$mobile} WHERE id = $auction_id";
});
register_hook('auction.tmp.approve', function($owner_id, $to_user, $data) {
	send_notification($to_user, 'auction.tmp.approve', $data['id']);
});
register_hook('auction.tmp.cancel', function($owner_id, $to_user, $auction_id) {
	send_notification($to_user, 'auction.tmp.cancel', $auction_id);
});

add_menu_location('business-menu', 'business::business-menu');
add_available_menu('auction::auction', 'auction', 'ion-android-alarm-clock');

register_hook('user.delete', function($user_id) {
	$db = db();
	$query = $db->query("SELECT id FROM auction_new WHERE user_id = '".$user_id."'");
	while($row = $query->fetch_row()) {
		auction_delete($row[0]);
	}
	empty_cart($user_id);
	$db->query("DELETE FROM auction_bid WHERE user_id = '".$user_id."'");
	$db->query("DELETE FROM auction_temp WHERE user_id = '".$user_id."'");
	return $user_id;
});