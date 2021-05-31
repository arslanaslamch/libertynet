<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("creditgift-manager")->setActive();

function creditgift_rank_pager($app) {
	$app->setTitle(lang('creditgift::creditgift-creditrank'));
	$val = input('val');
	if($val != '') {
		$rank_image = input_file('rank_image');
		creditgift_add_rank($val, $rank_image);
	}
	$rank = get_creditgift_rank();
	$msg = '';
	return $app->render(view('creditgift::admincp/rank', array('msg' => $msg, 'ranks' => $rank)));

}

function creditgift_category_pager($app) {
	$app->setTitle(lang('creditgift::gift-category'));
	$msg = '';
	$val = input('val');
	if($val != '') {
		add_creditgift_category($val);
	}
	$cat = get_creditgift_category();
	return $app->render(view('creditgift::admincp/category', array('msg' => $msg, 'cats' => $cat)));
}

function creditgift_edit_pager($app) {
	$app->setTitle(lang('creditgift::gift-edit'));
	$msg = '';
	$action = input('action');
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_rank($id);
			redirect_back();
		break;
		case 'edit':
			$id = input('id');
			$val = input('val');
			$img = input_file('rank_image');
			if($val) {
				update_creditgift($id, $val, $img);
				redirect_to_pager('admincp-creditgift-rank');
			}
		break;
	}
	$edit = get_creditgift_id($id);
	return $app->render(view('creditgift::admincp/edit', array('msg' => $msg, 'edits' => $edit)));
}

function creditgift_send_pager($app) {
	$app->setTitle(lang('creditgift::send-credit'));
	$msg = '';
	$member = get_creditgift_user();
	$search = input('search');
	if($search) {
		$member = search_users($search);
	}

	$sendTo_all = input('sendTo_all');
	if($sendTo_all != '') {
		creditgift_sendToAll($sendTo_all);
	}
	$sendTo_one = input('sendTo_one');
	if($sendTo_one != '') {
		$reciever_id = input('recieverId');
		creditgift_sendToOne($sendTo_one, $reciever_id);
	}
	$cat = '';//get_creditgift_gift();
	return $app->render(view('creditgift::admincp/send', array('msg' => $msg, 'members' => $member)));
}

function creditgift_salesplan_pager($app) {
	$app->setTitle(lang('creditgift::credit-sales'));
	$msg = '';
	$val = input('val');
	if($val) {
		add_creditgift_plan($val);
	}
	$plan = get_creditgift_plan();
	return $app->render(view('creditgift::admincp/salesplan', array('msg' => $msg, 'plans' => $plan)));
}

function creditgift_editplan_pager($app) {
	$app->setTitle(lang('creditgift::salesplan-edit'));
	$msg = '';
	$action = input('action');
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_plan($id);
			redirect_back();
		break;
		case 'edit':
			$id = input('id');
			$val = input('val');
			if($val) {
				update_creditgift_plan($id, $val);
				redirect_to_pager('admincp-salesplan');
			}
			$edit = get_creditgift_planId($id);
			return $app->render(view('creditgift::admincp/editplan', array('msg' => $msg, 'edit' => $edit)));
		break;
	}

	$edit = get_creditgift_planId($id);
	return $app->render(view('creditgift::admincp/editplan', array('msg' => $msg, 'edit' => $edit)));
}

function creditgift_creditsales_pager($app) {
	$app->setTitle(lang('creditgift::credit-sales'));
	$msg = '';
	$sale = get_creditgift_sales();
	return $app->render(view('creditgift::admincp/creditsales', array('msg' => $msg, 'sales' => $sale)));
}


function creditgift_action_batch_pager($app) {
	$action = input('action');
	$ids = explode(',', input('ids'));

	foreach($ids as $id) {
		switch($action) {
			case 'others':
				//     db()->query("UPDATE ads SET status='1' WHERE ads_id='{$id}'");
			break;

			case 'delete_plan':
				delete_plan($id);
			break;
		}
	}
	return redirect_back();
}