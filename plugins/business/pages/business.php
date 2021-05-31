<?php
function business_create_pager($app) {
	$id = segment(2);
	$plans_id = array();
	$plans = get_business_plans();
	foreach($plans as $plan) {
		$plans_id[] = $plan['id'];
	}
	if(in_array($id, $plans_id)) {
		$app->setTitle(lang('business::create-business'));
		$message = null;
		$val = input('val');
		if($val) {
			$plan = get_business_plan($val['id']);
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
				$val['plan'] = $plan;
				$id = add_business($val);
				$slug = business_get_slug($id, 'business');
				if(($plan['price'] == 0) && $val['type_id'] == 1) {
					business_activate($id);
				}
				return redirect_to_pager('business-page', array('slug' => $slug));
			}
		}
		$categories = business_get_categories();
		$countries = business_get_country();
		$types = business_get_type();
		return $app->render(view('business::create_business', array('types' => $types, 'categories' => $categories, 'message' => $message, 'countries' => $countries, 'id' => $id)));
	} else {
		$app->setTitle(lang('business::choose-plan'));
		$message = null;
		return $app->render(view('business::choose_plan', array('plans' => $plans, 'message' => $message)));
	}
}

function business_businesses_pager($app) {
	$app->setTitle('Businesses');

	$category_id = input('c');
	$type = input('t');
	$search = input('s');
	$page = input('page');
	$limit = config('pagination-limit-businesses', 20);
	$mine = input('m');
	$user_id = input('u');
	$pending = input('p');
	$featured = input('f');
	$min_price = input('p0');
	$max_price = input('p1');
	$has_photo = input('i');
	$location = input('l');
	$filter = array('category_id' => $category_id, 'keywords' => $search, 'mine' => $mine, 'user_id' => $user_id, 'min_price' => $min_price, 'max_price' => $max_price, 'has_photo' => $has_photo, 'location' => $location, 'pending' => $pending, 'featured' => $featured, 'page' => $page, 'limit' => $limit, 'admin' => false);
	$categories = business_get_categories();
	$category = $category_id ? lang(business_get_category($category_id)['category']) : lang('business::all-businesses');
	$appends = $_GET;
	unset($appends['page']);
	$businesses = business_get_businesses($filter)->append($appends);
	$message = null;
	return $app->render(view('business::businesses', array('categories' => $categories, 'category' => $category, 'category_id' => $category_id, 'businesses' => $businesses, 'search' => $search, 'type' => $type, 'message' => $message)));
}

function busınesses_slug_pager() {
	$path = (isset(parse_url($_SERVER['REQUEST_URI'])['path']) && parse_url($_SERVER['REQUEST_URI'])['path'] != '/') ? parse_url($_SERVER['REQUEST_URI'])['path'] : null;
	if(preg_match('/\/category\/(.*?)(\/|$|\?|#)/i', $path)) {
		preg_match('/\/category\/(.*?)(\/|$|\?|#)/i', $path, $matches);
		$_GET['c'] = isset($_GET['c']) ? $_GET['c'] : business_get_slug_id($matches[1], 'category');
	}
	if(preg_match('/\/my-businesses(\/|$|\?|#)/i', $path)) {
		$_GET['m'] = isset($_GET['m']) ? $_GET['m'] : 1;
	}
	if(preg_match('/\/([0-9]+)(\/|$|\?|#)/i', $path)) {
		preg_match('/\/([0-9]+)(\/|$|\?|#)/i', $path, $matches);
		switch($matches[1]) {
			case 'featured':
				$_GET['f'] = isset($_GET['f']) ? $_GET['f'] : 1;
			break;
			case 'my-businesses':
				$_GET['m'] = isset($_GET['m']) ? $_GET['m'] : 1;
			break;
		}
	}
	return business_businesses_pager(app());
}

function business_contact_pager($app) {
	$app->setTitle('Busıness Contact');
	$id = input(user_id);
	$email = get_user_email($id);
	$message = null;
	$val = input('val', null, array('message'));
	if($val) {
		CSRFProtection::validate();
		$message = submit_contact_business($val) ? lang('contact::message-sent') : lang('contact::message-not-sent');
	}
	return $app->render(view('business::contact', array('email' => $email, 'message' => $message)));
}

function business_claim_pager($app) {
	$app->setTitle('Business Clam');
	$businesses = business_get_claimed_business(2, $limit = 20);
	return $app->render(view('business::businesses', array('businesses' => $businesses, 'title' => lang('business::claimed-businesses'))));
}


function business_favoured_pager($app) {
	$app->setTitle('Favourite Businesses');
	$businesses = favouriteList();
	return $app->render(view('business::businesses', array('businesses' => $businesses, 'title' => lang('business::favourite-businesses'))));
}


function business_followed_pager($app) {
	$app->setTitle('Followed Businesses');
	$businesses = followList();
	return $app->render(view('business::businesses', array('businesses' => $businesses, 'title' => lang('business::followed-businesses'))));
}

function business_review_pager($app) {
	$app->setTitle('Business Review');
	$message = NULL;
	$business_id_slug = input('business_id');
	$business_id = business_get_slug_id($business_id_slug, 'business');
	$val = input('val');
	if($val) {
		CSRFProtection::validate();

		$validator = validator($val, array(
			'message' => 'required'));
		if(validation_fails()) {
			$message = validation_first();
		} else {
			if(!empty($val['reviews'])) {
				$added = add_review($val);
				if($added) {
					return redirect(url_to_pager('business-review').'?business_id='.$business_id_slug);
				} else {
					$message = 'fill all the required fields';
				}
			} else {
				$message = lang('Fil all the required fields');
			}
		}
	}
	$business = get_business_details($business_id);
	$reviews = get_business_reviews($business_id);
	return $app->render(view('business::create_review', array('business_id' => $business_id, 'message' => $message, 'reviews' => $reviews, 'business' => $business)));
}

function business_reviews_pager($app) {
	$app->setTitle('Business Reviews');
	return $app->render(view('business::reviews'));
}


function business_claim_business_pager($app) {
	$app->setTitle('business::business-claims');
	$val = input('val');
	if($val) {
		CSRFProtection::validate();

		$validator = validator($val, array(
			'business_id' => 'required'));
		if(validation_fails()) {
			$message = validation_first();
		} else {
			if(!empty($val['business_id'])) {
				$added = claimed_file($val);
				if($added) {
					redirect_to_pager('all-business');
				} else {
					$message = 'fill all the required fields';
				}
			} else {
				$message = lang('Fil all the required fields');
			}
		}
	}
	$message = NULL;
	$business_id = input('business_id');
	if($business_id){
		$business = get_business_details(business_get_slug_id($business_id, 'business'));

		return $app->render(view('business::membership_true', array('business' => $business)));
	}
}

function business_claim_membership_pager($app) {
	$app->setTitle('Membership Not Allowed');
	return $app->render(view('business::membership'));
}

function add_follow_pager($app) {
	$app->setTitle('Business Follow');
	$business_id = input('id');
	$business_id = business_get_slug_id($business_id, 'business');
	$business_action = input('action');
	if($business_action == 'follow') {
		$follow = business_followers_business($business_id);
		$count = rowscount('business_member', $business_id);
		$result = array(
			'name' => lang('business::unfollow'),
			'action' => 'unfollow',
			'count' => $count
		);
	} else if($business_action == 'unfollow') {
		$unfollow = business_unfollower_business($business_id);
		$count = rowscount('business_member', $business_id);
		$result = array(
			'name' => lang('business::follow'),
			'action' => 'follow',
			'count' => $count
		);
	} else {
		$result = array(
			'name' => '',
			'action' => ''
		);
	}
	return json_encode($result);
}

function add_favorite_pager($app) {
	$business_id = input('id');
	$business_id = business_get_slug_id($business_id, 'business');
	$business_action = input('action');
	if($business_action == 'favourite') {
		$follow = business_favorites_business($business_id);
		$count = rowscount('business_favourite', $business_id);
		$result = array(
			'name' => lang('business::unfollow'),
			'action' => 'unfavourite',
			'count' => $count
		);
	} else if($business_action == 'unfavourite') {
		$unfollow = business_unfavourites_business($business_id);
		$count = rowscount('business_favourite', $business_id);
		$result = array(
			'name' => lang('business::follow'),
			'action' => 'favourite',
			'count' => $count
		);
	} else {
		$result = array(
			'name' => '',
			'action' => ''
		);
	}
	return json_encode($result);
}


function business_members_pager($app) {
	$app->setTitle('Business Members');
	$business_id = input('business_id');
	if((input('delete'))) {
		$member_delete = business_delete_member(input('delete'));
	}
	$business_id = business_get_slug_id($business_id, 'business');
	$members = business_members_get($business_id);
	$business = get_business_details($business_id);
	return $app->render(view('business::members', array('members' => $members, 'business' => $business)));
}

function add_compare_pager($app) {
	$business_id = input('id');
	//return redirect(url_to_pager('business-compare') . '?id=' . $business_id);

	return $app->render(view('business::business_compare', array('businesses' => $business_id)));
}

function showcontact_pager($app) {
	$business_id = input('id');
	$business_id = business_get_slug_id($business_id, 'business');
	$business_action = input('action');
	if($business_action == 'phone') {
		$contact = get_business_contact('business_number', $business_id);
		$result = array(
			'contact' => $contact,
			'title' => 'Phone'
		);
	} else if($business_action == 'website') {
		$contact = get_business_contact('website', $business_id);
		$result = array(
			'contact' => $contact,
			'title' => 'Website'
		);
	} else {
		$result = array(
			'contact' => ''
		);
	}
	return json_encode($result);
}

function business_admin_management_pager() {
	$business_id = input('business_id');
	$business_id = business_get_slug_id($business_id, 'business');
	$member_action = input('action');
	$member_id = input('id');
	if($member_action == 'admin') {
		$admin = business_member_admin($member_id, $business_id);
		$result = array(
			'name' => lang('business::admin-remove'),
			'action' => 'adminr'
		);

	} else if($member_action == 'adminr') {
		$admin_r = business_member_remove_admin($member_id, $business_id);
		$result = array(
			'name' => lang('business::admin-make'),
			'action' => 'admin'
		);
	} else {
		$result = array(
			'name' => ''
		);
	}
	return json_encode($result);
}

function business_image_upload_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => '',
        'src' => ''
    );
    $id = input('id');
    if($id) {
        $image = input_file('image');
        if($image) {
            $uploader = new Uploader($image);
            if($uploader->passed()) {
                $uploader->setPath('business/businesses/images/');
                $image_path = $uploader->resize()->result();
                $val['company_logo'] = $image_path;
                if(business_update($id, $val)) {
                    $result['status'] = 1;
                    $result['src'] = url_img($image_path, 920);
                    $result['message'] = lang('business::image-updated');
                } else {
                    $result['message'] = lang('business::image-not-updated');
                }
            } else {
                $result['message'] = $uploader->getError();
            }
        } else {
            $result['message'] = lang('business::invalid-request');
        }
    }
    $response = json_encode($result);
    return $response;
}