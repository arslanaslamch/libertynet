<?php

function business_pager($app) {
	$business_id = input('id');
	$business_slug = business_get_slug($business_id, 'business');
	$message = null;
	$rate = input('rate');
	if($rate) {
		add_business_rating($rate, $business_id);
		return redirect(url_to_pager('business-page', array('slug' => $business_slug)));
	}
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		return add_comment($val);
	}
	if($business_id) {
		$business = business_get_claim_business($business_id);
		if(!$business) {
			return MyError::error404();
		}
		if(($business['paid'] == 0) && ($business['business_type_id'] == 1) && ($business['approved'] == 0) && ($business['active'] == 0)) {
			if($business['user_id'] == get_userid()) {
				return redirect(url_to_pager('business-payment').'?id='.$business_id);
			} else {
				redirect_back('the page is not available');
			}
		} else if(($business['paid'] == 0) && ($business['business_type_id'] > 1) && ($business['approved'] == 0)) {
			$app->setTitle('Claimable Business::'.$business['business_name']);
			$business_images = business_get_business_images($business_id);
			set_meta_tags(array('name' => get_setting("site_title", "Crea8social"), 'title' => get_setting("site_title", "Crea8social").' - '.$business['title'], 'description' => $business['description'], 'image' => $business['image'] ? url_img(business['image'], 200) : img('business::images/no_image.jpg', 75), 'keywords' => $business['tags']));
			$num_business_images = count($business_images);
			$description = trim($business['description']) == '' ? '<em>'.lang('business::no-description').'</em>' : $business['description'];
			$price = is_numeric($business['price']) ? config('currency', '$').$business['price'] : $business['price'];
			$price = trim($business['price']) == '' ? '<div class="business-price business-price-free business-detail">'.lang('business::free').'</div>' : '<div class="business-price business-price-paid business-detail">'.$price.'</div>';
			$address = trim($business['company_address']) == '' ? '<em>'.lang('business::no-address').'</em>' : $business['company_address'];
			$avatar = is_loggedIn() ? get_avatar(75) : null;
			$entityId = is_loggedIn() ? get_userid() : null;
			$entityType = 'user';
			$num_business_comments = business_get_num_business_comments($business_id);
			$_SESSION['business_id'] = $business_id;
			return $app->render(view('business::business', array('business' => $business, 'description' => $description, 'price' => $price, 'address' => $address, 'entityType' => $entityType, 'property_images' => $business_images, 'num_business_images' => $num_business_images, 'entityId' => $entityId, 'avatar' => $avatar, 'num_business_comments' => $num_business_comments, 'message' => $message, 'business_images' => $business_images,)));
		} else if(($business['paid'] == 1) && ($business['business_type_id'] == 1) && ($business['approved'] == 1) && ($business['active'] == 1)) {
			$app->setTitle($business['business_name']);
			$business_images = business_get_business_images($business_id);
			// set_meta_tags(array('name' => get_setting("site_title", "Crea8social"), 'title' => get_setting("site_title", "Crea8social").' - '.$business['title'], 'description' => $business['description'], 'image' => $business['image'] ? url_img(business['image'], 200) : img('business::images/no_image.jpg', 75), 'keywords' => $business['tags']));
			$num_business_images = count($business_images);
			$description = trim($business['description']) == '' ? '<em>'.lang('business::no-description').'</em>' : $business['description'];
			$price = is_numeric($business['price']) ? config('currency', '$').$business['price'] : $business['price'];
			$price = trim($business['price']) == '' ? '<div class="business-price business-price-free business-detail">'.lang('business::free').'</div>' : '<div class="business-price business-price-paid business-detail">'.$price.'</div>';
			$address = trim($business['company_address']) == '' ? '<em>'.lang('business::no-address').'</em>' : $business['company_address'];
			$avatar = is_loggedIn() ? get_avatar(75) : null;
			$entityId = is_loggedIn() ? get_userid() : null;
			$entityType = 'user';
			$num_business_comments = business_get_num_business_comments($business_id);
			$_SESSION['business_id'] = $business_id;
			return $app->render(view('business::business', array('business' => $business, 'description' => $description, 'price' => $price, 'address' => $address, 'entityType' => $entityType, 'property_images' => $business_images, 'num_business_images' => $num_business_images, 'entityId' => $entityId, 'avatar' => $avatar, 'num_business_comments' => $num_business_comments, 'message' => $message, 'business_images' => $business_images,)));
		} else {
			return MyError::error404();
		}
	}
}


function business_slug_pager() {
	$path = (isset(parse_url($_SERVER['REQUEST_URI'])['path']) && parse_url($_SERVER['REQUEST_URI'])['path'] != '/') ? parse_url($_SERVER['REQUEST_URI'])['path'] : null;
	if(preg_match('/\/business\/(.*?)(\/|$|\?|#)/i', $path)) {
		preg_match('/\/business\/(.*?)(\/|$|\?|#)/i', $path, $matches);
		$_GET['id'] = business_get_slug_id($matches[1], 'business');
		return business_pager(app());
	}
}