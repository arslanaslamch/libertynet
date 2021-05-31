<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-blogs')->setActive();
function lists_pager($app) {
	$app->setTitle('Manage Members');
	$filter = input('filter', 'active');
	$users = get_users($filter, 20, input('val'));
	return $app->render(view('ranker::admincp/lists', array('users' => $users, 'filter' => $filter)));
}
/*function categories_pager($app) {
	$app->setTitle(lang('ranker::manage-categories'));
    $categories = get_rank_categories();
	return $app->render(view('ranker::admincp/categories/lists', array('categories' => $categories)));
}*/
function categories_add_pager($app) {
	$app->setTitle(lang('ranker::add-category'));
	$message = null;

	$val = input('val');
    if($val) {
        CSRFProtection::validate();
		rank_add_category($val);
		return redirect_to_pager('admincp-rank-categories');
	}

	return $app->render(view('ranker::admincp/categories/add', array('message' => $message)));
}
function manage_category_pager($app) {
	$action = input('action');
	$id = input('id');
	switch($action) {
		case 'edit':
			$message = null;
			$image = null;
			$val = input('val');
			$app->setTitle(lang('ranker::edit-category'));
			$category = get_rank_category($id);
			if(!$category) return redirect_to_pager('admincp-rank-categories');
			if($val) {
				CSRFProtection::validate();
				save_rank_category($val, $category);
				return redirect_to_pager('admincp-rank-categories');
			}
			return $app->render(view('ranker::admincp/categories/edit', array('message' => $message, 'category' => $category)));
		break;
		case 'delete':
			$category = get_rank_category($id);
			if(!$category) return redirect_to_pager('admincp-rank-categories');
			delete_rank_category($id, $category);
			return redirect_to_pager('admincp-rank-categories');
		break;
	}
	return $app->render();
}
function ranks_pager($app) {
	$app->setTitle(lang('ranker::ranks'));
    $ranks = get_ranks();
	return $app->render(view('ranker::admincp/ranks', array('ranks' => $ranks)));
}
function add_rank_pager($app) {
	$app->setTitle(lang('ranker::add-rank'));
	$message = null;
    $val = input('val');
	if($val) {
		CSRFProtection::validate();
		$validate = validator($val, array(
			'title' => 'required',
			'point' => 'required',
		));

		if(validation_passes()) {
			add_rank($val);
			return redirect_to_pager('admincp-ranks');
		} else {
			$message = validation_first();
		}
	}
	return $app->render(view('ranker::admincp/add-rank', array('message' => $message)));
}

function manage_rank_point($app) {
	$action = input('action');
	$id = input('id');
	switch($action) {
		case 'edit':
			$message = null;
			$image = null;
			$val = input('val');
			$app->setTitle(lang('ranker::edit-point'));
			$point = get_rank_point($id);
			if(!$point) return redirect_to_pager('admincp-ranks');
			if($val) {
				CSRFProtection::validate();
				save_rank_point($val, $id);
				return redirect_to_pager('admincp-ranks');
			}
			return $app->render(view('ranker::admincp/edit', array('message' => $message, 'point' => $point)));
		break;
		case 'delete':
			$point = get_rank_point($id);
			if(!$point) return redirect_to_pager('admincp-ranks');
			delete_rank_points($id);
			return redirect_to_pager('admincp-ranks');
		break;
	}
	return $app->render();
}
function ranks_range_pager($app) {
	$app->setTitle(lang('ranker::ranks-range'));
	$val = input('val');
    if($val) {
        CSRFProtection::validate();
		rank_add_range($val);
		return redirect_to_pager('admincp-ranges');
	}
	return $app->render(view('ranker::admincp/ranks-range', array()));
}
function ranges_pager($app) {
	$app->setTitle(lang('ranker::ranges'));
    $ranges = get_r_ranges();
	return $app->render(view('ranker::admincp/ranges', array('ranges' => $ranges)));
}
function manage_range_pager($app) {
	$action = input('action');
	$id = input('id');
	switch($action) {
		case 'edit':
			$message = null;
			$image = null;
			$val = input('val');
			$app->setTitle(lang('ranker::edit-range'));
			$range = get_rank_range($id);
			if(!$range) return redirect_to_pager('admincp-ranges');
			if($val) {
				CSRFProtection::validate();
				save_rank_range($val, $id);
				return redirect_to_pager('admincp-ranges');
			}
			return $app->render(view('ranker::admincp/edit-ranges', array('message' => $message, 'range' => $range)));
		break;
		case 'delete':
			$range = get_rank_range($id);
			if(!$range) return redirect_to_pager('admincp-ranges');
			delete_rank_range($id);
			return redirect_to_pager('admincp-ranges');
		break;
	}
	return $app->render();
}