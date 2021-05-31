<?php
//get_menu('admin-menu', 'business-manager')->setActive();
get_menu('admin-menu', 'plugins')->setActive();
get_menu('admin-menu', 'plugins')->findMenu('admin-business-manager')->setActive();

function categories_pager($app) {
	$app->setTitle(lang('business::categories'));

	return $app->render(view('business::admincp/category/list', array('categories' => business_get_categories())));
}

function add_category_pager($app) {

	$app->setTitle(lang('business::add-category'));
	$val = input('val');
	$messages = null;
	if($val) {
		CSRFProtection::validate();
		business_execute_form($val);
		return redirect_to_pager('admin-business-categories-list');
	}
	return $app->render(view('business::admincp/category/add', array('messages' => $messages)));
}

function edit_category_pager($app) {
	$app->setTitle(lang('business::edit-category'));
	$id = input('id');
	$category_id = $id ? $id : 0;
	$val = input('val');
	$messages = null;
	if($val) {
		CSRFProtection::validate();
		business_execute_form($val);
		return redirect_to_pager('admin-business-categories-list');
	}
	return $app->render(view('business::admincp/category/edit', array('messages' => $messages, 'category_id' => $category_id, 'category' => business_get_category($category_id))));
}

function delete_category_pager($app) {

	$app->setTitle(lang('business::delete-category'));
	$val = input('val');
	$id = input('id');
	$messages = null;
	$category_id = $id ? $id : 0;
	$category = business_get_category($category_id);
	$categories = business_get_categories();
	if(!business_is_category_exist($category_id)) {
		$messages = 'The category you want to delete does not exist';
	}
	if($val) {
		CSRFProtection::validate();
		if(business_is_category_exist($category_id)) {
			business_execute_form($val);
			return redirect_to_pager('admin-business-categories-list');
		} else {
			$messages = 'The category you want to delete does not exist';
		}
	}
	return $app->render(view('business::admincp/category/delete', array('messages' => $messages, 'category_id' => $category_id, 'category' => $category, 'categories' => $categories)));
}

function businesses_pager($app) {
//    get_menu('admin-menu', 'business-manager')->findMenu('admin-business-businesses')->setActive();
	$app->setTitle(lang('business::manage-businesses'));
	$category_id = input('c');
	$type = input('t');
	$search = input('s');
	$page = input('page');
	$limit = 20;
	$mine = input('m');
	$user_id = input('u');
	$approval = input('a');
	$featured = input('f');
	$filter = array('category_id' => $category_id, 'keywords' => $search, 'mine' => $mine, 'user_id' => $user_id, 'approval' => $approval, 'featured' => $featured, 'page' => $page, 'limit' => $limit, 'admin' => true);
	$categories = business_get_categories();
	$category = $category_id ? lang(business_get_category($category_id)['category']) : lang('business::all-categories');
	$appends = $_GET;
	unset($appends['page']);
	$active_class = array('l' => '', 'm' => '');
	$businesses = business_get_businesses($filter)->append($appends);
	$message = null;
	$url = (isset(parse_url($_SERVER['REQUEST_URI'])['query'])) ? url_to_pager('admin-business-businesses-list').'?'.parse_url($_SERVER['REQUEST_URI'])['query'] : url_to_pager('admin-business-businesses-list').'?';
	return $app->render(view('business::admincp/business/list', array('categories' => $categories, 'category' => $category, 'businesses' => $businesses, 'url' => $url, 'active_class' => $active_class, 'message' => $message)));
}

function edit_business_pager($app) {
	$app->setTitle(lang("business::edit-business"));
	$message = null;
	$categories = business_get_categories();
	$val = input('val');
	$business_id = input('id');
	if($business_id) {
		if($val) {
			CSRFProtection::validate();
			$image = input_file('image');
			$image_path = '';
			if($image) {
				$uploader = new Uploader($image);
				if($uploader->passed()) {
					$uploader->setPath('business/businesses/images/');
					$image_path = $uploader->resize()->result();
				} else {
					$message = $uploader->getError();
				}
				$val['image_path'] = $image_path;
			}
			if(!$message) {
				business_execute_form($val);
				return redirect_to_pager('admin-business-businesses-list');
			}
		}
		$business = get_business_details($business_id);
		return $app->render(view('business::edit_business', array('categories' => $categories, 'business' => $business, 'business_id' => $business_id, 'message' => $message)));
	}
}

function admin_edit_business_pager($app) {
	$app->setTitle(lang("business::edit-business"));
	$message = null;
	$categories = business_get_categories();
	$val = input('val');
	$business_id = input('id');
	if($business_id) {
		if($val) {
			CSRFProtection::validate();
			$image = input_file('image');
			$image_path = '';
			if($image) {
				$uploader = new Uploader($image);
				if($uploader->passed()) {
					$uploader->setPath('business/businesses/images/');
					$image_path = $uploader->resize()->result();
				} else {
					$message = $uploader->getError();
				}
				$val['image_path'] = $image_path;
			}
			if(!$message) {
				business_execute_form($val);
				return redirect_to_pager('admin-business-businesses-list');
			}
		}
		$business = get_business_details($business_id);
		return $app->render(view('business::admincp/business/edit', array('categories' => $categories, 'business' => $business, 'business_id' => $business_id, 'message' => $message)));
	}
}

function delete_business_pager($app) {
	$business_id = input('id', 0);
	$business = business_get_business($business_id, null, true);
	$val = array('business_id' => $business_id, 'type' => 'delete_business');
	business_execute_form($val);
	return redirect_to_pager('admin-business-businesses-list');
}

function approve_business_pager($app) {
	$business_id = input('id', 0);
	business_activate($business_id);
	return redirect_to_pager('admin-business-businesses-list');
}


function disapprove_business_pager($app) {
	$business_id = input('id', 0);
	business_deactivate($business_id);
	return redirect_to_pager('admin-business-businesses-list');
}


function plans_pager($app) {
	$app->setTitle(lang('business::business-plans'));
	return $app->render(view('business::admincp/plan/list', array('plans' => get_business_plans())));
}

function add_plan_pager($app) {
	$app->setTitle(lang('business::business-plans'));
	$message = null;
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		$validator = validator($val, array(
			'title' => 'required',
			'desc' => 'required'
		));
		if(validation_passes()) {
			add_business_plan($val);
			redirect(url_to_pager('admin-business-plans'));
		} else {
			$message = validation_first();
		}
	}
	return $app->render(view('business::admincp/plan/add', array('message' => $message)));
}

function edit_plan_pager($app) {
	$app->setTitle(lang('business::business-plans'));
	$message = null;
	$id = input('id');
	$val = input('val');
	$plan = get_business_plan($id);
	if($plan) {
		if($val) {
			CSRFProtection::validate();
			$validator = validator($val, array(
				'title' => 'required',
				'desc' => 'required'
			));
			if(validation_passes()) {
				save_business_plan($val, $plan);
				redirect(url_to_pager('admin-business-plans'));
			} else {
				$message = validation_first();
			}
		}
	} else {
		$message = 'Plan does not exist';
	}
	return $app->render(view('business::admincp/plan/edit', array('message' => $message, 'plan' => $plan)));
}

function delete_plan_pager($app) {
	$app->setTitle(lang('business::business-plans'));
	$message = null;
	$id = input('id');
	delete_business_plan($id);
	redirect(url_to_pager('admin-business-plans'));
}

function add_admin_business_pager($app) {
	$message = null;
	$val = input('val');
	if($val) {
	    $plan = get_business_plan($val['plan']);
	    if(!$plan) redirect(url_to_pager('business-choose-plan'));
			CSRFProtection::validate();
			$image = input_file('image');
			if($image) {
				$uploader = new Uploader($image);
				if($uploader->passed()) {
					$uploader->setPath('business/businesses/images/');
					$image_path = $uploader->resize()->result();
					$val['image_path'] = $image_path;
				} else {
					$message = $uploader->getError();
				}
			} else {
				$image_path = '';
				$val['image_path'] = $image_path;
			}
			$images = input_file('images');
			if($images && isset($images[0]['name']) && $images[0]['name']) {
				$uploader = new Uploader(null, 'image', $images);
				if($uploader->passed()) {
					foreach($images as $image) {
						$uploader = new Uploader($image);
						if($uploader->passed()) {
							$uploader->setPath('business/businesses/images/');
							$image_path = $uploader->resize()->result();
							$images_paths[] = $image_path;
						} else {
							$message = $uploader->getError();
							break;
						}
					}
					$val['images_paths'] = $images_paths;
				} else {
					$message = $uploader->getError();
				}
			} else {
				$images_paths = array();
				$val['images_paths'] = $images_paths;
			}
			if(!$message) {
				$id = admin_add_business($val);
				if($plan['price'] == 0) {
					business_activate($id);
				}
				return redirect_to_pager('admin-business-businesses-list');
			}
		}
	$categories = business_get_categories();
	$countries = business_get_country();
	$types = business_get_type();
	$id = business_get_plans();
	return $app->render(view('business::admincp/business/create_business', array('types' => $types, 'categories' => $categories, 'message' => $message, 'countries' => $countries, 'plans' => $id)));
}

function pending_business_pager($app) {
	$app->setTitle(lang('business::business-pending'));
	return $app->render(view('business::admincp/business/pending', array('businesses' => pending_business(20))));
}

function active_business_pager($app) {
	$app->setTitle(lang('business::business-active'));
	return $app->render(view('business::admincp/business/active', array('businesses' => active_business(20))));
}

function claimable_business_pager($app) {
	$app->setTitle(lang('business::business-claimable'));
	return $app->render(view('business::admincp/business/claimable', array('businesses' => claimable_business(20))));
}

function claimed_business_pager($app) {
	$app->setTitle(lang('business::business-claimed'));
	$business_id = input("id");
	if ($business_id){
	    $business = get_business_details($business_id);
	    if ($business){
	        $userId = $business['claimed_user_id'];
            approve_claimed_file($business_id, $userId);
        }
    }
	return $app->render(view('business::admincp/business/claimed', array('businesses' => claimed_business(20))));
}

function claimable_business_approve_pager($app){
    $business_id = input('id');
    approve_claimable_business($business_id);
    redirect(url('admincp/business/business/claimable'));
}

function business_action_batch_pager($app) {
	$action = input('action');
	$ids = explode(',', input('ids'));
	foreach($ids as $id) {
		switch($action) {
			case 'approve':
				business_activate($id);
			break;
			case 'disapprove':
				business_deactivate($id);

			break;
			case 'delete_cat':
				db()->query("DELETE FROM business_category WHERE id ='{$id}'");
			break;
			case 'delete':
				db()->query("DELETE FROM business WHERE id = '{$id}'");
			break;

			case 'delete_plan':
				delete_business_plan($id);
			break;
		}
	}
	return redirect_back();
}