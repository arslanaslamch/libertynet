<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-onlinetvs')->setActive();

function import_pager($app){
    $app->setTitle(lang("onlinetv::import-tv"));
    $action = input('action');
    $message = "";
    $type = input('type');
    switch ($action) {
        case 'radios':
            if ($type == 'add') {
                deleteTvsImported();
                importTv();
                $message = lang("onlinetv::tv-imported-successfully");
            }
            if ($type == 'delete') {
                deleteTvsImported();
                $message = lang("onlinetv::tv-imported-deleted");
            }
            break;
    }
    return $app->render(view("onlinetv::admincp/import", array('message' => $message)));
}
function lists_pager($app) {
	$app->setTitle('Manage TV');
	return $app->render(view('onlinetv::admincp/lists', array('onlinetvs' => admin_get_onlinetvs(input('term')))));
}


function manage_pager($app) {
	$action = input('action', 'order');
	$app->setTitle(lang('onlinetv::manage-onlinetvs'));
	$message = null;
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_onlinetv($id);
			return redirect_back();
		break;
		case 'edit':
			$id = input('id');
			$onlinetv = get_onlinetv($id);
			if(!$onlinetv) return redirect_back();
            $val = input('val',null,array('source_embed','source_url'));
			if($val) {
				CSRFProtection::validate();
				save_onlinetv($val, $onlinetv, true);
				return redirect_to_pager('admincp-onlinetvs');
			}
			return $app->render(view('onlinetv::admincp/edit', array('onlinetv' => $onlinetv,'message'=>$message)));
		break;
		default:
			$ids = input('data');
			for($i = 0; $i < count($ids); $i++) {
				update_help_category_order($ids[$i], $i);
			}
		break;
	}
}

function add_pager($app) {
	$app->setTitle(lang('onlinetv::add-new-onlinetv'));
	$message = null;
    $val = input('val',null,array('source_embed','source_url'));
	if($val) {
		CSRFProtection::validate();
        $required = array(
            'category' => 'required',
            'name' => 'required',
            'source' => 'required',
        );
        if($val['source'] == 'embed'){
            //the embed field must not be empty
            if(empty($val['source_embed'])) {
                $required['source_embed'] = 'required';
            }

        }
        if($val['source'] == 'url'){
            //the embed field must not be empty
            if(empty($val['source_url'])) {
                $required['source_url'] = 'required';
            }
        }
        $validate = validator($val, $required);

		if(validation_passes()) {
            $onlinetv_id = add_onlinetv($val);
			return redirect_to_pager('admincp-onlinetvs');
		} else {
			$message = validation_first();
		}
	}
	return $app->render(view('onlinetv::admincp/add', array('message' => $message)));
}

function categories_pager($app) {
	$app->setTitle(lang('onlinetv::manage-categories'));
    $categories = (input('id')) ? get_onlinetv_parent_categories(input('id')) : get_onlinetv_categories();
	return $app->render(view('onlinetv::admincp/categories/lists', array('categories' => $categories)));
}

function categories_add_pager($app) {
	$app->setTitle(lang('onlinetv::add-category'));
	$message = null;

	$val = input('val');
    if($val) {
        CSRFProtection::validate();
		onlinetv_add_category($val);
		return redirect_to_pager('admincp-onlinetv-categories');
		//redirect to category lists
	}

	return $app->render(view('onlinetv::admincp/categories/add', array('message' => $message)));
}

function manage_category_pager($app) {
	$action = input('action', 'order');
	$id = input('id');
	switch($action) {
		default:
			$ids = input('data');
			for($i = 0; $i < count($ids); $i++) {
				update_onlinetv_category_order($ids[$i], $i);
			}
		break;
		case 'edit':
			$message = null;
			$image = null;
			$val = input('val');
			$app->setTitle(lang('onlinetv::edit-category'));
			$category = get_onlinetv_category($id);
			if(!$category) return redirect_to_pager('admincp-onlinetv-categories');
			if($val) {
				CSRFProtection::validate();
				$file = input_file('file');

				save_onlinetv_category($val, $category);
				return redirect_to_pager('admincp-onlinetv-categories');
				//redirect to category lists
			}
			return $app->render(view('onlinetv::admincp/categories/edit', array('message' => $message, 'category' => $category)));
		break;
		case 'delete':
			$category = get_onlinetv_category($id);
			if(!$category) return redirect_to_pager('admincp-onlinetv-categories');
			delete_onlinetv_category($id, $category);
			return redirect_to_pager('admincp-onlinetv-categories');
		break;
	}
	return $app->render();
}