<?php



function pay_onlinetv_pager($app){
    $id = input('id');
    //echo $id;die();
    $onlinetv = get_onlinetv($id);
    if(!$onlinetv or (!$onlinetv['status'] and !is_onlinetv_owner($onlinetv))) return redirect(url('onlinetvs'));
    //pay with wallet
    tvPayWithWallet($onlinetv);
    return redirect(url('onlinetvs'));
}
function onlinetv_pager($app) {
	$app->setTitle(lang('onlinetv::onlinetvs'));
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
	$country = input('country', '');
    $app->leftMenu = false;
    $title = lang('onlinetv::onlinetvs');
    if($type == 'mine'){
        $title = lang("onlinetvs::my-tv");
    }
    if($category){
        $category_str = get_onlinetv_category($category);
        if($category_str){
            $title = lang($category_str['title']).' '.lang('onlinetv::tv');
        }
    }
	return $app->render(view('onlinetv::lists', array('title'=>$title,'onlinetvs' => get_onlinetvs($type, $category, $term, null, 12, $filter,null,'user',null,$country))));
}

function onlinetv_page_pager($app) {
	$slug = segment(1);
	$onlinetv = get_onlinetv($slug);
	if(!$onlinetv or (!$onlinetv['status'] and !is_onlinetv_owner($onlinetv))) return redirect(url('onlinetvs'));
	//we now want to check if tv is paid
    if(can_not_watch_tv($onlinetv)){
        $app->setTitle(lang('onlinetv::paid-tv'));
        return $app->render(view("onlinetv::pay",array('tv'=>$onlinetv)));
    }
	$app->onlinetv = $onlinetv;
	$app->leftMenu = false;
	if($onlinetv['status']) db()->query("UPDATE onlinetvs SET views = views + 1 WHERE slug='{$slug}'");
	$app->setTitle($onlinetv['name'])->setKeywords($onlinetv['tags'])->setDescription(str_limit(strip_tags($onlinetv['description']), 100));
	set_meta_tags(array('name' => get_setting("site_title", "Crea8social"), 'title' => $onlinetv['name'], 'description' => str_limit(strip_tags($onlinetv['description']), 100), 'image' => $onlinetv['image'] ? url_img($onlinetv['image'], 200) : '', 'keywords' => $onlinetv['tags']));
	return $app->render(view('onlinetv::view', array('onlinetv' => $onlinetv)));
}

function manage_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$action = input('type');
	$app->setTitle(lang('onlinetv::manage-onlinetvs'));
	$id = input('id');
	$onlinetv = get_onlinetv($id);
	if(is_onlinetv_owner($onlinetv)) {
		switch($action) {
			case 'delete':
				delete_onlinetv($id);
				return redirect(url('onlinetvs?type=mine'));
			break;
			case 'edit':
				$id = input('id');
				$onlinetv = get_onlinetv($id);
				$val = input('val',null,array('source_embed','source_url'));
				$message = null;
				if($val) {
					CSRFProtection::validate();
                    $required = array(
                        'category' => 'required',
                        'name' => 'required',
                        'source' => 'required',
                    );
                    if($val['source'] == 'embed'){
                        //the embed field must not be empty
                        if(empty($val['source_embed'])){
                            $required['source_embed'] = 'required';
                        }


                        //it means the soure_url must be empty
                        //$val['source_url'] = '';source_embed
                    }
                    if($val['source'] == 'url'){
                        //the embed field must not be empty
                        if(empty($val['source_url'])) {
                            $required['source_url'] = 'required';
                        }

                        //it means the soure_url must be empty
                        //$val['source_embed'] = '';
                    }
                    $validate = validator($val, $required);
                    //print_r($val);die();
					if(validation_passes()) {
						$save = save_onlinetv($val, $onlinetv);
						if($save) {
							$onlinetv = get_onlinetv($onlinetv['id']);
							$status = 1;
							$message = lang('onlinetv::onlinetv-edit-success');
							$redirect_url = url('onlinetv/'.$onlinetv['slug']);
						} else {
							$message = lang('onlinetv::onlinetv-save-error');
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
					if($redirect_url) {
						return redirect($redirect_url);
					}
				}
				return $app->render(view('onlinetv::edit', array('onlinetv' => $onlinetv, 'message' => $message)));
			break;
		}
	} else {
		$message = lang('onlinetv::onlinetv-edit-permission-denied');
		redirect(url('onlinetvs'));
	}
}

function add_onlinetv_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$app->setTitle(lang('onlinetv::add-new-onlinetv'));
    $val = input('val',null,array('source_embed','source_url'));
	if(user_has_permission('can-create-onlinetv') && config('allow-members-create-onlinetv', true)) {
		if($val) {
			CSRFProtection::validate();
            //check if either of the tv source is empty
            $required = array(
                'category' => 'required',
                'name' => 'required',
                'source' => 'required',
            );
            if($val['source'] == 'embed'){
                //the embed field must not be empty
                $required['source_embed'] = 'required';
            }
            if($val['source'] == 'url'){
                //the embed field must not be empty
                $required['source_url'] = 'required';
            }
			$validate = validator($val, $required);

			if(validation_passes()) {

				$onlinetv_id = add_onlinetv($val);
				if($onlinetv_id) {
					$status = 1;
					$message = lang('onlinetv::onlinetv-add-success');
					$onlinetv = get_onlinetv($onlinetv_id);
					if($onlinetv['status']) {
						$redirect_url = url('onlinetv/'.$onlinetv['slug']);
					} else {
						$redirect_url = url('onlinetvs?type=mine');
					}
				} else {
					$message = lang('onlinetv::onlinetv-add-error');
				}
			} else {
				$message = validation_first();
			}
		}
	} else {
		$message = lang('onlinetv::onlinetv-add-permission-denied');
		$redirect_url = url('onlinetvs');
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

	if($redirect_url) {
		return redirect($redirect_url);
	}

	return $app->render(view("onlinetv::add", array('message' => $message)));
}

function onlinetv_api_pager($app) {
	$onlinetvs = get_onlinetvs(true);
	$b = array();
	foreach($onlinetvs->results() as $onlinetv) {
		$onlinetv = arrange_onlinetv($onlinetv);
		$b[] = $onlinetv;
	}
	return json_encode($b);
}