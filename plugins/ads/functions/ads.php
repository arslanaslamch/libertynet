<?php

function ads_create($val, $result, $admin = false) {
	/*   if ($ads_class == 'video') {
		   $validator = validator($val, array(
			   'name' => 'required|unique:ads',
		   ));
	   } elseif ($ads_class == 'picture') {
		   $validator = validator($val, array(
			   'name' => 'required|unique:ads',
			   'title' => 'required',
			   'description' => 'required',
			   'plan_id' => 'required',
			   'link' => 'required'
		   ));
	   }
   */
	$expected = array(
		'status' => 0,
		'paid' => 0,
		'activate' => 0,
		'page_id' => 0,
		'gender' => 'all'
	);

	/**
	 * @var $name
	 * @var $title
	 * @var $page_id
	 * @var $ads_class
	 * @var $description
	 * @var $link
	 * @var $type
	 * @var $plan_id
	 * @var $country
	 * @var $gender
	 * @var $activate
	 * @var $status
	 * @var $enable
	 * @var $paid
	 */
	extract(array_merge($expected, $val));
	$imagePath = null;
	$video_path = null;

	if($ads_class == 'video') {
		$validator = validator($val, array(
			'name' => 'required|unique:ads',
		));
	} elseif($ads_class == 'picture') {
		$validator = validator($val, array(
			'name' => 'required|unique:ads',
			'title' => 'required',
			'description' => 'required',
			'plan_id' => 'required',
			'link' => 'required'
		));
	}

	if(validation_passes()) {
		if(input_file('file')):
			if($ads_class == 'video') {
				$videoFile = (input_file('file'));
				$uploader = new Uploader($videoFile, 'video');
				if($uploader->passed()) {
					$uploader = new Uploader($videoFile, 'video');
					$uploader->setPath(get_userid().'/'.date('Y').'/videos/');
					$uploader->uploadVideo();
					$video_path = $uploader->result();
				} else {
					$result['status'] = 0;
					$result['message'] = $uploader->getError();
					return json_encode($result);
				}
			} else {
				$imagePath = get_userid().'/'.date('Y').'/ads/videos/';
				//    if (input_file('file')) {
				$uploader = new Uploader(input_file('file'), 'image');
				if($uploader->passed()) {
					$path = get_userid().'/'.date('Y').'/ads/photos/';
					$uploader->setPath($path);
					$imagePath = $uploader->resize(550, null)->result();
				} else {
					$result['status'] = 0;
					$result['message'] = $uploader->getError();
					return json_encode($result);
				}
				//   }
			}
		else:
			if(plugin_loaded('page')) {
				if(!$imagePath && !$page_id && $ads_class != 'video') {
					$result['message'] = lang('ads::ads-image-not-found');
					return json_encode($result);
				}

				if(!$imagePath && $page_id) {
					$page = find_page($page_id);
					if($page['page_logo']) {
						$imagePath = $page['page_logo'];
					} elseif($ads_class != 'video') {
						$result['message'] = lang('ads::ads-image-not-found');
						return json_encode($result);
					}
				}
			} else {
				if(!$imagePath && $ads_class != 'video') {
					$result['message'] = lang('ads::ads-image-not-found');
					return json_encode($result);
				}
			}

		endif;

		$plan = get_plan($plan_id);
		$quantity = $plan['quantity'];
		$user_id = get_userid();
		$location = serialize($country);
		$ad_type = ($page_id) ? 'page' : 'website';
		$time = time();
		$name = sanitizeText($name);
		$title = sanitizeText($title);
		$description = sanitizeText($description);
		$link = sanitizeText($link);
		$page_id = sanitizeText($page_id);
		$ads_class = sanitizeText($ads_class);
		$ad_type = sanitizeText($ad_type);
		$plan_id = sanitizeText($plan_id);
		$gender = sanitizeText($gender);
		$status = sanitizeText($status);
		db()->query("INSERT INTO ads (time,name,title,description,ads_class,video,user_id,link,page_id,type,image,plan_type,plan_id,quantity,target_location,target_gender,status)VALUES(
            '{$time}','{$name}','{$title}','{$description}','{$ads_class}','{$video_path}','{$user_id}','{$link}','{$page_id}','{$ad_type}','{$imagePath}','{$type}','{$plan_id}','{$quantity}','{$location}','{$gender}','{$status}'
        )");

		$adsId = db()->insert_id;

		if($admin) {
			$status = sanitizeText($status);
			db()->query("UPDATE ads SET status='{$status}',paid='{$paid}' WHERE ads_id='{$adsId}'");
		}
		fire_hook('ads.created', null, array(db()->insert_id, $val));
		fire_hook("ads.arg", db()->insert_id, array());
		$result['status'] = 1;
		$result['message'] = lang('ads::ads-created-success');

		$result['link'] = ($activate) ? url_to_pager('ads-activate', array('id' => $adsId)) : url_to_pager('ads-manage');
		if($admin) $result['link'] = url_to_pager('admin-ads-list');
		return json_encode($result);

	} else {
		$result['message'] = validation_first();
		return json_encode($result);
	}

}

function ads_save($val, $result, $ads, $admin = false) {

	$validator = validator($val, array(
		'title' => 'required',
		'description' => 'required',
		'plan_id' => 'required',
		'link' => 'required'
	));

	$expected = array(
		'paid' => 0,
		'refresh' => 0,
		'page_id' => 0,
		'gender' => 'all'
	);
	/**
	 * @var $name
	 * @var $title
	 * @var $page_id
	 * @var $description
	 * @var $ads_class
	 * @var $link
	 * @var $type
	 * @var $plan_id
	 * @var $country
	 * @var $gender
	 * @var $activate
	 * @var $status
	 * @var $refresh
	 * @var $paid
	 */
	extract(array_merge($expected, $val));
	$imagePath = $ads['image'];
	$video_path = $ads['video'];

	$paid = $ads['paid'];
	$type = isset($type) ? $type : $ads['plan_type'];
	$plan_id = isset($plan_id) ? $plan_id : $ads['plan_id'];

	if($ads_class == 'video') {
		$validator = validator($val, array(
			'plan_id' => 'required',
			'link' => 'required'
		));
	} elseif($ads_class == 'picture') {
		$validator = validator($val, array(
			'title' => 'required',
			'description' => 'required',
			'plan_id' => 'required',
			'link' => 'required'
		));
	}

	if(validation_passes()) {
		if(input_file('file')) {
			if($ads_class == 'video') {
				$videoFile = (input_file('file'));
				$uploader = new Uploader($videoFile, 'video');
				if($uploader->passed()) {
					$uploader = new Uploader($videoFile, 'video');
					$uploader->setPath(get_userid().'/'.date('Y').'/videos/');
					$uploader->uploadVideo();
					$video_path = $uploader->result();
				} else {
					$result['status'] = 0;
					$result['message'] = $uploader->getError();
					return json_encode($result);
				}
			} else {
				$imagePath = get_userid().'/'.date('Y').'/ads/videos/';
				if(input_file('file')) {
					$uploader = new Uploader(input_file('file'), 'image');
					if($uploader->passed()) {
						$path = get_userid().'/'.date('Y').'/ads/photos/';
						$uploader->setPath($path);
						$imagePath = $uploader->resize(550, null)->result();
					} else {
						$result['status'] = 0;
						$result['message'] = $uploader->getError();
						return json_encode($result);
					}
				}
			}
		} else {
			if(plugin_loaded('page')) {
				if(!$imagePath && !$page_id && $ads_class != 'video') {
					$result['message'] = lang('ads::ads-image-not-found');
					return json_encode($result);
				}
				if(!$imagePath && $page_id != '0') {
					$page = find_page($page_id);
					if($page['page_logo']) {
						$imagePath = $page['page_logo'];
					} elseif($ads_class != 'video') {
						$result['message'] = lang('ads::ads-image-not-found');
						return json_encode($result);
					}
				}
			} else {
				if(!$imagePath && $ads_class != 'video') {
					$result['message'] = lang('ads::ads-image-not-found');
					return json_encode($result);
				}
			}
		}

		$plan = get_plan($plan_id);
		$quantity = ($admin and $refresh) ? $plan['quantity'] : $ads['quantity'];
		$user_id = get_userid();
		$location = serialize($country);
		$ad_type = ($page_id) ? 'page' : 'website';
		$id = $ads['ads_id'];
		if($ads_class == 'picture') {
			$title = sanitizeText($title);
			$description = sanitizeText($description);
		}
		$link = sanitizeText($link);
		$page_id = sanitizeText($page_id);
		$ad_type = sanitizeText($ad_type);
		$ads_class = sanitizeText($ads_class);
		$plan_id = sanitizeText($plan_id);
		$gender = sanitizeText($gender);
		if(!is_admin()) {
			$type = $paid ? $ads['plan_type'] : $type;
			$plan_id = $paid ? $ads['plan_id'] : $plan_id;
		}
		if($ads_class == 'picture') {
			db()->query("UPDATE ads SET status='{$status}', ads_class='{$ads_class}', page_id='{$page_id}',link='{$link}',title='{$title}',description='{$description}',plan_type='{$type}',plan_id='{$plan_id}',quantity='{$quantity}', target_location='{$location}',target_gender='{$gender}',image='{$imagePath}'  WHERE ads_id='{$id}'");
		} else {
			db()->query("UPDATE ads SET status='{$status}',image='{$imagePath}', video='{$video_path}', ads_class='{$ads_class}', page_id='{$page_id}',link='{$link}',plan_type='{$type}',plan_id='{$plan_id}',quantity='{$quantity}', target_location='{$location}',target_gender='{$gender}' WHERE ads_id='{$id}'");
		}
		if($admin) {
			db()->query("UPDATE ads SET paid='{$val['paid']}' WHERE ads_id='{$id}'");
		}

		fire_hook("ads.arg", $id, array());
		fire_hook("updated.ads", $id, array($val));
		$result['status'] = 1;
		$result['message'] = lang('ads::ads-save-success');

		$result['link'] = url_to_pager('ads-manage');
		if($admin) $result['link'] = url_to_pager('admin-ads-list');
		return json_encode($result);

	} else {
		$result['message'] = validation_first();
		return json_encode($result);
	}
}

function add_ads_plan($val) {
	/**
	 * @var $type
	 * @var $name
	 * @var $desc
	 * @var $price
	 * @var $banner_width
	 * @var $banner_height
	 * @var $quantity
	 */
	extract($val);
	$titleSlug = "ads_plan_".time().'_title';
	$descriptionSlug = "ads_plan_".time()."_description";
	foreach($name as $langId => $t) {
		add_language_phrase($titleSlug, $t, $langId, 'ads');
	}
	foreach($desc as $langId => $t) {
		add_language_phrase($descriptionSlug, $t, $langId, 'ads');
	}

	$q = db()->query("SELECT name FROM ads_plans");
	$order = $q->num_rows + 1;
	db()->query("INSERT INTO ads_plans (name,type,description,price,banner_width,banner_height,quantity,ads_order)VALUES(
        '{$titleSlug}','{$type}','{$descriptionSlug}','{$price}','{$banner_width}','{$banner_height}','{$quantity}','{$order}'
    )");
	fire_hook('ads.plan.add', null, array(db()->insert_id, $val));
	return true;
}

function save_ads_plan($val, $plan) {
	/**
	 * @var $type
	 * @var $name
	 * @var $desc
	 * @var $price
	 * @var $banner_height
	 * @var $banner_width
	 * @var $quantity
	 */
	extract($val);
	$titleSlug = $plan['name'];
	$descSlug = $plan['description'];
	foreach($name as $langId => $t) {
		(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'ads') : add_language_phrase($titleSlug, $t, $langId, 'ads');
	}

	foreach($desc as $langId => $t) {
		(phrase_exists($langId, $descSlug)) ? update_language_phrase($descSlug, $t, $langId, 'ads') : add_language_phrase($descSlug, $t, $langId, 'ads');
	}

	$id = $plan['id'];
	db()->query("UPDATE ads_plans SET type='{$type}', price='{$price}',banner_width='{$banner_width}',banner_height='{$banner_height}', quantity='{$quantity}' WHERE id='{$id}'");
	return true;
}

function get_ads_plans($type = null) {
	$sql = "SELECT * FROM ads_plans ORDER BY ads_order";
	if($type) {
		$sql = "SELECT * FROM ads_plans WHERE type='{$type}' ORDER BY ads_order";
	}
	$q = db()->query($sql);
	return fetch_all($q);
}

function get_plan($id) {
	$q = db()->query("SELECT * FROM ads_plans WHERE id='{$id}'");
	return $q->fetch_assoc();
}

function update_ads_plan_order($id, $order) {

	db()->query("UPDATE `ads_plans` SET `ads_order`='{$order}' WHERE  `id`='{$id}'");

}

function delete_ads_plan($id) {
	db()->query("DELETE FROM ads_plans WHERE id='{$id}'");
	return true;
}

function get_user_ads() {
	$userid = get_userid();
	$q = db()->query("SELECT * FROM ads WHERE user_id='{$userid}' ORDER BY ads_id DESC");
	return fetch_all($q);
}

function find_ads($id) {
	$q = db()->query("SELECT * FROM ads WHERE ads_id='{$id}'");
	return $q->fetch_assoc();
}

function activate_ads($ads) {
	$id = $ads['ads_id'];
	$plan = get_plan($ads['plan_id']);
	$quantity = $plan['quantity'];
	return db()->query("UPDATE ads SET paid='1', status='1',quantity='{$quantity}' WHERE ads_id='{$id}'");
}

function delete_ads($id) {
	db()->query("DELETE FROM ads WHERE ads_id='{$id}'");
}

function get_ads() {
	return paginate("SELECT * FROM ads ORDER BY ads_id DESC", 20);
}

function get_render_ads($type = 'all', $limit, $class = 'picture') {
	$fields = "ads_id,title,description,link,page_id,image,video,plan_type,plan_id,quantity,views,impression_stats";
	$sql = "SELECT * FROM ads WHERE ads_id!='' ";
	if(is_loggedIn()) {
		$country = get_user_data('country');
		$gender = get_user_data('gender');
		$sql .= " AND (target_location LIKE '%{$country}%' AND (target_gender ='all' or target_gender='{$gender}')) ";
		$sql = fire_hook("more.arg.filter", $sql, array());
	}

	if($type == 'page' or $type == 'website') {
		$sql .= " AND type='{$type}' ";
	}

	if($class == 'picture') {
		$sql .= " AND ads_class = '{$class}' ";
	} elseif($class == 'video') {
		$sql .= " AND ads_class = '{$class}' ";
	} else {
		$sql .= " AND ads_class = 'overlay' ";
	}


	$qLimit = config('ads-quantity-deduction-per-impression', 5);

	$sql .= " AND quantity>={$qLimit} AND paid='1' AND status='1'";

	$sql = fire_hook("location.filter.render.ads", $sql, array());
	$sql .= " ORDER BY rand() LIMIT {$limit}";
	$q = db()->query($sql);
	$result = array();
	while($fetch = $q->fetch_assoc()) {
		$views = $fetch['views'] + 1;
		$impressions = $fetch['impression_stats'];
		$quantity = $fetch['quantity'];

		if(is_loggedIn()) {
			$userImpressions = get_privacy('ads-impressions', array());
			if(!in_array($fetch['ads_id'], $userImpressions)) {
				$impressions += 1;
				$userImpressions[] = $fetch['ads_id'];
				if($fetch['plan_type'] == 2) {
					$quantity -= config('ads-quantity-deduction-per-impression', 5);
				}
				/*  if ($fetch['ads_class'] == 'video') {
					  $quantity -= config('video-ads-quantity-deduction-per-impression', 5);
				  }   */
				save_privacy_settings(array('ads-impressions' => $userImpressions));
			}
		}
		$adsId = $fetch['ads_id'];
		db()->query("UPDATE ads SET views='{$views}',impression_stats='{$impressions}',quantity='{$quantity}' WHERE ads_id='{$adsId}'");
		if(plugin_loaded('page')) {
			$page = find_page($fetch['page_id'], false);
			$fetch['page'] = $page;
		}
		if(plugin_loaded('page') and !$fetch['image'] and $fetch['page_id']) {
			$fetch['image'] = url_img($fetch['page']['page_logo'], 600);
		} elseif(!$fetch['image']) {
			$fetch['image'] = img('images/cover.jpg');
		} else {
			$fetch['image'] = url_img($fetch['image'], 600);
		}
		$result[] = $fetch;
	}

	return $result;
}

function count_user_ads_total($type) {
	$sql = "";
	switch($type) {
		case 'impressions':
			$sql = "SELECT SUM(impression_stats) as size FROM ads ";
		break;
		case 'clicks':
			$sql = "SELECT SUM(clicks_stats) as size FROM ads ";
		break;
		case 'views':
			$sql = "SELECT SUM(views) as size FROM ads ";
		break;
	}
	$userid = get_userid();
	$sql .= " WHERE user_id='{$userid}'";
	$q = db()->query($sql);
	$fetch = $q->fetch_assoc();
	return $fetch['size'] ? $fetch['size'] : 0;
}

function count_total_ads() {
	$q = db()->query("SELECT ads_id FROM ads ");
	return $q->num_rows;
}

function count_total_running_ads() {
	$qLimit = config('ads-quantity-deduction-per-impression', 5);
	$sql = "SELECT ads_id FROM ads WHERE  quantity>={$qLimit} AND paid='1' AND status='1'";
	$q = db()->query($sql);
	return $q->num_rows;
}

function count_ads_in_month($n, $year) {
	$q = db()->query("SELECT * FROM ads WHERE YEAR(timestamp)={$year} AND MONTH(timestamp)={$n}");
	return $q->num_rows;
}

function ads_service_list() {
	$db = db();
	$sql = "SELECT `ads_service`.`id`, `ads_service`.`title`, `ads_service`.`code`, `ads_service`.`excluded_pages`, GROUP_CONCAT(`ads_service_positions`.`position`) AS positions FROM `ads_service` LEFT JOIN `ads_service_positions` ON `ads_service`.`id` = `ads_service_positions`.`service_id` WHERE `ads_service_positions`.`service_id` IS NOT NULL GROUP BY `ads_service_positions`.`service_id`";
	$query = $db->query($sql);
	$list = fetch_all($query);
	return $list;
}

function ads_service_get($id) {
	$db = db();
	$sql = "SELECT `ads_service`.`id`, `ads_service`.`title`, `ads_service`.`code`, `ads_service`.`excluded_pages`, GROUP_CONCAT(`ads_service_positions`.`position`) AS positions FROM `ads_service` LEFT JOIN `ads_service_positions` ON `ads_service`.`id` = `ads_service_positions`.`service_id` WHERE `ads_service`.`id` = ".$id." AND `ads_service_positions`.`service_id` IS NOT NULL GROUP BY `ads_service_positions`.`service_id`";
	$query = $db->query($sql);
	$service = $query->fetch_assoc();
	return $service;
}

function ads_service_add($service) {
	$db = db();
	$sql = "INSERT INTO `ads_service`(title, code, excluded_pages) VALUES ('".$service['title']."', '".$service['code']."', '".$service['excluded_pages']."')";
	$query = $db->query($sql);
	$service_id = $db->insert_id;
	if($query) {
		if(isset($service['positions']) && $service['positions']) {
			ads_service_position_update($service_id, $service['positions']);
		}
	}
	return $query ? $service_id : false;
}

function ads_service_update($service) {
	$db = db();
	$sql = "UPDATE `ads_service` SET `title` = '".$service['title']."', `code` = '".$service['code']."', `excluded_pages` = '".$service['excluded_pages']."' WHERE `id` = ".$service['id'];
	$query = $db->query($sql);
	if($query) {
		$sql = "DELETE FROM `ads_service_positions` WHERE `service_id` = ".$service['id'];
		$query = $db->query($sql);
		if($query) {
			if(isset($service['positions']) && $service['positions']) {
				ads_service_position_update($service['id'], $service['positions']);
			}
		}
	}
	return $query ? true : false;
}

function ads_service_delete($id) {
	$db = db();
	$sql = "DELETE FROM `ads_service` WHERE `id` = ".$id;
	$query = $db->query($sql);
	if($query) {
		$sql = "DELETE FROM `ads_service_positions` WHERE `service_id` = ".$id;
		$query = $db->query($sql);
	}
	return $query ? true : false;
}

function ads_service_position_update($service_id, $positions) {
	$db = db();
	$sql = "INSERT INTO ads_service_positions (`service_id`, `position`) VALUE ";
	$i = 0;
	foreach($positions as $position) {
		if($i) {
			$sql .= ", ";
		}
		$sql .= "(".$service_id.", '".$position."')";
		$i++;
	}

	$query = $db->query($sql);
	return $query ? true : false;
}

function ads_service_positions($positions) {
	if(is_string($positions)) {
		$positions = explode(',', $positions);
	}

	$positions = (array) $positions;

	$positions_array = array(
		'top.before' => lang('ads::top-before'),
		'top.after' => lang('ads::top-after'),
		'right.before' => lang('ads::right-before'),
		'right.after' => lang('ads::right-after'),
		'bottom.before' => lang('ads::bottom-before'),
		'bottom.after' => lang('ads::bottom-after'),
		'left.before' => lang('ads::left-before'),
		'left.after' => lang('ads::left-after'),
		'middle.before' => lang('ads::middle-before'),
		'middle.after' => lang('ads::middle-after'),
		'feed.inline' => lang('ads::feed-inline')
	);

	array_walk($positions, function(&$v, $k) use ($positions_array) {
		$v = isset($positions_array[$v]) ? $positions_array[$v] : $v;
	});

	$positions = implode(', ', $positions);

	return $positions;
}

function render_ads_service($position) {
    $currentPage = Pager::getCurrentPage();
    $services = ads_service_list();
    foreach($services as $service) {
        $excluded_pages = array();
        foreach (json_decode($service['excluded_pages'] ? $service['excluded_pages'] : '[]', true) as $page) {
            $excluded_pages[] = $page['slug'];
        }
        if(in_array($position, explode(',', $service['positions'])) && !in_array($currentPage, $excluded_pages)) {
            echo $service['code'];
        }
    }
}