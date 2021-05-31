<?php

function auction_create_pager($app) {
	$app->setTitle(lang('auction::auction_create_title'));
	$message = null;
	$status = 0;
	$redirect_url = null;
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		validator($val, array(
			'title' => 'required',
			'category' => 'required',
			'quantity' => 'required',
			'description' => 'required',
			'start_date' => 'required',
			'end_date' => 'required',
			'picture' => 'required',
			'state' => 'required',
			'country' => 'required',
			'city' => 'required',
			'ship_details' => 'required',
			'mobile' => 'required',
		));
		if(validation_passes()) {
			$added = add_auction($val);
			if($added) {
				$status = 1;
				$redirect_url = url('auction/detail').'?id='.$added;
			}
		} else {
			$message = validation_first();
		}
		if(input('ajax')) {
			$result = array(
				'status' => (int) $status,
				'message' => (string) $message,
				'redirect_url' => (string) $redirect_url,
			);
			$response = json_encode($result);
			return $response;
		}
	}
	if($redirect_url) {
		return redirect($redirect_url);
	}

	return $app->render(view('auction::create', array('message' => $message)));
}

function auction_list_all_pager($app) {
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
	$auctions = get_auction_filter($type, $category, $term, 2, null, $filter);
	$message = null;
	$app->setTitle("All Auction");
	return $app->render(view('auction::list', array('auctions' => $auctions, 'message' => $message)));
}

function auction_detail($app) {
	$user_id = get_userid();
	$auction_id = input('id');
	//session_put('auction-id', $auction_id);
	//session_put()
	add_auction_view($user_id, $auction_id);
	$message = null;
	$app->setTitle('Auction');
	return $app->render(view('auction::details', array('id' => $auction_id, 'message' => $message)));
}

function auction_edit($app) {
	$id = input('id');
	$delete = input('delete');
	if($delete == 1) {
		auction_delete($id);
		return redirect(url_to_pager('auction-list').'?type=delete&status=1');
	}
	$message = null;
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		validator($val, array(
			'title' => 'required',
			'category' => 'required',
			'quantity' => 'required',
			'description' => 'required',
			'start_date' => 'required',
			'end_date' => 'required',
			'reserved_price' => 'required',
			'buy_price' => 'required',
			'city' => 'required',
			'state' => 'required',
			'country' => 'required',
			'ship_details' => 'required',

		));
		if(validation_passes()) {
			auction_update($id, $val);
			return redirect(url_to_pager('auction-details', $message).'?id='.$id.'&status=update');
		}
	}
	$app->render("Edit Auction Details");
	return $app->render(view('auction::edit', array('message' => $message)));
}

function auction_bid($app) {
	$message = null;
	$auction_id = input('id');
	$auc_id = session_get('auction-id');
	$price = input('price');
	$status = input('status');
	if($status == 'bid') {
		if($auction_id):
			add_aunction_bid($auction_id, $price, $status);
			//redirect_to_pager('auction-list', $message);
			return redirect(url_to_pager('auction-details').'?id='.$auction_id.'&type=bid&success=1');
		endif;
	}
	if($status == 'offer') {
		if($auction_id):
			add_auction_offer($auction_id, $price);
			//redirect_to_pager('auction-list', $message);
			return redirect(url_to_pager('auction-details').'?id='.$auction_id.'&type=offer&success=1');

		endif;
	}
	if($status == 'update') {
		$userid = get_userid();
		if($auction_id):
			update_bid_id($auction_id, $price, $userid);
			return redirect(url_to_pager('auction-details').'?id='.$auction_id.'&type=update&sucess=1');
		endif;
	} else {
		add_auction_cart($auc_id);
		redirect_to_pager('auction-cart');
	}
	return $app->render();
}

function auction_cart($app, $title = '') {
	$message = null;
	$app->setTitle($title);
	$auction_id = input('auction_id');
	if($auction_id) {
		return $app->render(view('auction::cart', array('a_id' => $auction_id, 'message' => $message)));
	} else {
		$price = input('price');
		$type = input('type');
		$id = input('id');
		add_aunction_bid($id, $price, $type);
		return $app->render(view('auction::buyNow', array('id' => $id, 'message' => $message)));
	}
}

function auction_my_bid_pager($app) {
	$message = null;
	return $app->render(view('auction::myBids'));
}

function auction_approved_bid_pager($app) {
	$message = null;
	return $app->render(view('auction::approvedBid'));
}

function auction_my_offer_pager($app) {
	$message = null;
	return $app->render(view('auction::myOffer'));
}

function auction_my_auction_pager($app) {
	$message = null;
	return $app->render(view('auction::myAuction'));
}

function auction_approve_pager($app) {
	$message = null;
	$app->setTitle("");
	$auction_id = input('auction-id');
	$user_id = input('user_id');
	$type = input('type');
	$result = array();
	if($type == 'approve') {
		auction_temp_approve($auction_id, $user_id);
		return redirect(url_to_pager('auction-details').'?id='.$auction_id.'&type=approve&status=OK');
	} elseif($type == 'cancel') {
		$price = input('price');
		auction_temp_cancel($auction_id, $user_id, $price);
		return redirect(url_to_pager('auction-details').'?id='.$auction_id.'&type=cancel&status=OK');
	} elseif($type == 'sold') {
		$method = input('method');
		$bid = input('bid');
		$auction = get_auction($auction_id);
		$bidType = input('bid_type');
		if($method == 'oneTime') {
			if($auction['quantity'] == 1) {
				confirmAuctionPayment($auction_id, '', $bid, $bidType);
				if($bidType == 'buy') {
					buy_auction_delete($auction_id, $bid);
				}
				$result = array(
					"qty" => 0,
					"status" => true);
			} else {
				$result = array("qty" => 2);
			}
		} else {
			$qty = input('qty');
			$quantity = $auction['quantity'];
			$quantity = $quantity - $qty;
			if($quantity < 0) {
				$result = array(
					"qty" => 0,
					"status" => false,
					"message" => "Check the amount sold out");
			} else {
				confirmAuctionPayment($auction_id, $quantity, $bid, $bidType);
				if($quantity == 0) {
					if($bidType == 'buy') {
						buy_auction_delete($auction_id, $bid);
					}
				}
				$result = array(
					"qty" => 0,
					"status" => true
				);
			}
		}
	}
	return json_encode($result);
}

function auction_delete_pager($app) {
	$message = null;
	$app->setTitle("");
	return redirect(url_to_pager('auction-list').'?type=delete&status=OK');
}

function auction_activate_pager($app) {
	$auction_id = input('auction_id');
	$user_id = input('user_id');
	$auction = get_auction($auction_id);
	$total_price = input('total_price');
	$price = number_format((float) ($total_price / $auction['quantity']), 2, '.', '');
	$app->setTitle('Auction Activate');
    return $app->render(view('auction::activate', array('auction' => $auction)));
}

function auction_friend_bid_pager($app) {
	$app->setTitle("");
	return $app->render(view("auction::friendBids"));
}

function auction_friends_pager($app) {
	$app->setTitle("");
	return $app->render(view("auction::friends"));
}

function auction_ops_pager($app) {
	$ops = input('ops');
	if($ops == 'bid') {

	} elseif($ops == 'offer') {

	}

}

function auction_payment_success_pager($app) {
	CSRFProtection::validate();
	$app->setTitle(lang('auction::auction-purchase'));
	$id = input('id');
	auction_approve($id);
	return $app->render(view('auction::payment/success', array('id' => $id)));
}

function auction_payment_cancel_pager($app) {
	$app->setTitle(lang('auction::auction-canceled'));
	$id = input('id');
	return $app->render(view('auction::payment/cancel', array('id' => $id)));
}