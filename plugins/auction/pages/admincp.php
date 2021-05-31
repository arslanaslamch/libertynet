<?php
function add_category_pager($app) {
	$message = null;
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		$validate = validator($val, array(
			'title' => 'required',
			'description' => 'required',
		));
		if(validation_passes()) {
			add_category($val);
			return redirect_to_pager('admincp-category-list');
		} else {
			$message = validation_first();
		}
	}
	$app->setTitle(lang('admincp-category-title'));
	return $app->render(view('auction::admincp/category/add', array('message' => $message)));
}

function list_category_pager($app) {
	$message = null;
	$app->setTitle('Auction Categories');
	return $app->render(view('auction::admincp/category/list', array('message' => $message)));
}

function edit_category_pager($app) {
	$id = input('id');
	$message = null;
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		$validate = validator($val, array(
			'title' => 'required',
			'description' => 'required',
		));
		if(validation_passes()) {
			update_auction_category($val, $id);
			return redirect_to_pager('admincp-category-list');
		} else {
			$message = validation_first();
		}
	}
	//  get_auction_category($id);

	$app->setTitle('Edit Auction Category');
	return $app->render(view('auction::admincp/category/edit', array('id' => $id, 'message' => $message)));
}

function list_auction_pager($app) {
	$delete = input('delete');
	$auction = input('id');
	if($delete == 1) {
		auction_delete($auction);
		return redirect(url_to_pager('admincp-auction-list').'?success=1');
	}
	$message = null;
	$app->setTitle("Admin List Auctions");
	return $app->render(view('auction::admincp/auction/list'));
}

function list_auction_bidder($app) {
	$message = nulll;
	$app->setTitle("Admin Auction Bidder List");
	return $app->render(view('auction::admincp/auction/list-bidder'));
}

function auction_action_batch_pager($app) {
	$action = input('action');
	$ids = explode(',', input('ids'));

	foreach($ids as $id) {
		switch($action) {
			case 'delete':
				$auction_id = $id;
				auction_delete($auction_id);
			break;
			case 'delete_cat':
				delete_auction_category($id);
			break;
		}
	}
	return redirect_back();
}
