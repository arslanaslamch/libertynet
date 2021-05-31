<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("giftshop-manager")->setActive();

function giftshop_category_pager($app) {
	$app->setTitle(lang('giftshop::manage-gift'));
	$msg = '';
	$val = input('val');
	if($val != '') {
		add_giftshop_category($val);
	}
	$cat = get_giftshop_category();
	return $app->render(view('giftshop::admincp/category', array('msg' => $msg, 'cats' => $cat)));

}

function giftshop_category_edit_pager($app) {
	$app->setTitle(lang('giftshop::giftshop-edit'));
	$msg = '';
	$action = input('action');
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_giftshop_cat($id);
			redirect_back();
		break;
		case 'edit':
			$id = input('id');
			$val = input('val');
			if($val) {
				update_giftshop($id, $val);
				redirect_to_pager('admincp-giftshop-category');
			}
		break;
	}
	$edit = get_giftshop_category_id($id);
	return $app->render(view('giftshop::admincp/edit', array('msg' => $msg, 'edits' => $edit)));
}

function giftshop_manage_pager($app) {
	$app->setTitle(lang('creditgift::giftshop'));
	$msg = '';
	$val = input('val');
	$image = input_file('image');
	if($val != '') {
		add_gift($val, $image);
	}
	$cat = get_giftshop_category();
	$gift = get_giftshop_gift();
	return $app->render(view('giftshop::admincp/manage', array('msg' => $msg, 'cats' => $cat, 'gifts' => $gift)));
}

function giftshop_gift_edit_pager($app) {
	$app->setTitle(lang('giftshop::giftshop'));
	$msg = '';
	$action = input('action');
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_giftshop_gift($id);
			redirect_back();
		break;
		case 'edit':
			$id = input('id');
			$val = input('val');
			$image = input_file('image');
			if($val) {
				update_giftshop_gift($id, $val, $image);
				redirect_to_pager('admincp-giftshop');
			}
		break;
	}
	$cat = get_giftshop_category();
	$edit = get_giftshop_gift_id($id);
	return $app->render(view('giftshop::admincp/giftedit', array('msg' => $msg, 'edits' => $edit, 'cats' => $cat)));
}


function giftshop_action_batch_pager($app) {
	$action = input('action');
	$ids = explode(',', input('ids'));

	foreach($ids as $id) {
		switch($action) {
			case 'delete_cat':
				delete_giftshop_cat($id);
			break;

			case 'delete':
				delete_giftshop_gift($id);
			break;
		}
	}
	return redirect_back();
}