<?php

function add_category($val) {
	$expected = array(
		'title' => '',
		'description' => '',
	);
	/**
	 * @var $title
	 * @var $description
	 */
	extract(array_merge($expected, $val));
	$db = db();
	$q = $db->query("INSERT INTO auction_categories(title, description) VALUES('{$title}', '{$description}')");
	$cat_id = $db->insert_id;
	// fire_hook()
	return $cat_id;
}

function get_auction_categories() {
	$db = db();
	$query = $db->query("SELECT * FROM `auction_categories` ORDER BY `id` ASC");
	return fetch_all($query);
}

function get_auction_category($id) {
	$db = db();
	$query = $db->query("SELECT * FROM `auction_categories` WHERE `id`='{$id}'");
	return $query->fetch_assoc();

}

function get_auction_category_all($id) {
	$db = db();
	$query = $db->query("SELECT * FROM `auction_categories` WHERE `id`='{$id}'");
	return fetch_all($query);
}

function delete_auction_category($id) {
	$db = db();
	$query = $db->query("DELETE FROM `auction_categories` WHERE `id`='{$id}'");
	return true;
}

function update_auction_category($val, $cat_id) {
	$expected = array(
		'title' => '',
		'description' => ''
	);
	/**
	 * @var $title
	 * @var $description
	 */
	extract(array_merge($expected, $val));
	$db = db();
	$query = $db->query("UPDATE `auction_categories` SET `title`='{$title}', `description`='{$description}' WHERE id='{$cat_id}'");
	return true;
}

function add_auction($val) {
	$expected = array(
		'title' => '',
		'category' => '',
		'quantity' => '',
		'description' => '',
		'start_date' => '',
		'end_date' => '',
		'reserved_price' => '',
		'buy_price' => '',
		'country' => '',
		'state' => '',
		'city' => '',
		'mobile' => ''
	);
	/**
	 * @var $title
	 * @var $category
	 * @var $quantity
	 * @var $description
	 * @var $start_date
	 * @var $end_date
	 * @var $reserved_price
	 * @var $buy_price
	 * @var $country
	 * @var $state
	 * @var $city
	 * @var $ship_details
	 * @var $mobile
	 */
	extract(array_merge($expected, $val));
	$image = '';
	$file = input_file('image');
	if($file) {
		$upload = new Uploader($file);
		if($upload->passed()) {
			$upload->setPath('auctions/items/');
			$image = $upload->resize(700, 500)->result();
		}
	}
	$status = 2;
	$db = db();
	$user_id = get_userid();
	$query = $db->query("INSERT INTO `auction_new`(title,category_id,quantity,description,picture,start_date,end_date,user_id,reserved_price,buy_price,current_bid,country,state,city,ship_details,status,mobile)VALUES('{$title}','{$category}','{$quantity}', '{$description}', '{$image}','{$start_date}', '{$end_date}','{$user_id}', '{$reserved_price}', '{$buy_price}','{$reserved_price}','{$country}', '{$state}', '{$city}','{$ship_details}','{$status}','{$mobile}')");
	$auction_id = $db->insert_id;
	$owner_id = $user_id;
	fire_hook('auction.added', null, array($owner_id, $auction_id, $mobile));
	return $auction_id;
}

function get_auction_filter($type, $category = 'all', $term = null, $userid = null, $limit = 2, $filter = 'all', $withTitle = false) {

	$sql = "SELECT * FROM `auction_new`";
	$whereClause = " status = 2 ";
	if($category and $category != 'all') $whereClause .= ($whereClause) ? " AND category_id='{$category}'" : "category_id='{$category}'";
	//if ($filter and $filter == 'featured') $whereClause .= ($whereClause) ? " AND featured='1' " : " featured='1' ";
	if($term) $whereClause .= ($whereClause) ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%' OR country LIKE '%{$term}%' OR state LIKE '%{$term}%' OR city LIKE '%{$term}%') " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%' OR country LIKE '%{$term}%' OR state LIKE '%{$term}%' OR city LIKE '%{$term}%')";
	if($whereClause) $sql .= " WHERE {$whereClause} ";
	if($filter and $filter == 'top') {
		$sql = "SELECT * FROM `auction_new` WHERE `auction_new.id` IN (SELECT ` likes.type_id` FROM `likes`)";
	} elseif($filter && $filter == 'featured') {
		$sql .= "ORDER BY `id` desc";
	} elseif($filter && $filter == 'all') {
		$sql .= "ORDER BY `id` DESC";
	}

	$limit = ($limit) ? $limit : config('auction-list-limit', 5);
	return paginate($sql, $limit);
	//$where = "";
	//if($sort =='top') {
	//    $where = "ORDER BY num_likes";
	//}
	//if($sort == 'newest') {
	//    $where = "ORDER BY `id` DESC";
	//}
	//if($sort == 'oldest') {
	//    $where = "ORDER BY `id` ASC";
	//}
	//if($sort == 'A-Z' ) {
	//    $where = "ORDER BY `title` ASC";
	//}
	//if($sort == 'Z-A') {
	//    $where = "ORDER BY `title` DESC";
	//}
	//if($limit == 24) {
	//    $where = "LIMIT 24";
	//}
	//if($limit == 36) {
	//  $where = "LIMIT 36";
	//}
	//if($search != null) {
	//    $where = "WHERE `title` LIKE '%{$search}%'";
	//}
	//if($city != null) {
	//    $where = "WHERE `city`='{$city}'";
	//}
	//if($country != null) {
	//    $where = "WHERE `country` = '{$country}'";
	//}
	//if($state != null) {
	//    $where="WHERE `state`= '{$state}'";
	//}
	//if($where) $sql .= " {$where} ";
	//  $sql .= "ORDER BY `title` desc";
	//$db = db();
	//$query = $db->query("SELECT * FROM `auction_new`");
	//return fetch_all($db->query($sql));
	//$status = 0;
	//$db = db();
	//$where_sql = " WHERE `status` = '{$status}'";
	//if($filter['featured']) {
	//    $where_sql = " WHERE `status` = '{$status}'";
	//}
	//if($filter['order'] == 'top') {
	//    $order_sql = " ORDER BY num_likes";
	//} else {
	//    $order_sql = "";
	//}
	//$query = $db->query("SELECT *, (SELECT COUNT(`like_id`) FROM likes WHERE type = 'auction' and `type_id` = `id`) AS `num_likes` FROM `auction_new`".$where_sql.$order_sql);
	//return fetch_all($query);
}

function get_auctions() {
	$db = db();
	$query = $db->query("SELECT * FROM `auction_new`");
	return fetch_all($query);
}

function get_auction($id) {
	$db = db();
	$sql = "SELECT * FROM auction_new WHERE id = $id";
	$query = $db->query($sql);
	$auction = $query->fetch_assoc();
	return $auction;
}

function get_auction_all($id) {
	$db = db();
	$query = $db->query("SELECT * FROM `auction_new` WHERE `id` ='{$id}'");
	return fetch_all($query);
}

function buy_auction($auction_id) {
	$db = db();
	$q1 = $db->query("UPDATE `auction_new` WHERE id='{$auction_id}' SET status=1");
	return true;
}

function get_auction_by_Category($cat_id, $limit = 5) {
	$db = db();
	$query = "SELECT * FROM `auction_new` WHERE `category_id`='{$cat_id}'";
	$limit = ($limit) ? $limit : config('auction-list-limit');
	return paginate($query, $limit);
}

function auction_update($id, $val) {
	$expected = array(
		'title' => '',
		'category' => '',
		'quantity' => '',
		'description' => '',
		'start_date' => '',
		'end_date' => '',
		'reserved_price' => '',
		'buy_price' => '',
		'country' => '',
		'state' => '',
		'city' => '',
		'ship_details' => ''

	);
	/**
	 * @var $title
	 * @var $category
	 * @var $quantity
	 * @var $description
	 * @var $start_date
	 * @var $end_date
	 * @var $reserved_price
	 * @var $buy_price
	 * @var $country
	 * @var $state
	 * @var $city
	 * @var $ship_details
	 */
	extract(array_merge($expected, $val));
	extract(array_merge($expected, $val));
	$db = db();
	$db->query("UPDATE `auction_new` SET `title`='{$title}', `category_id`='{$category}',`quantity` = '{$description}', `end_date`='{$end_date}', `reserved_price`='{$reserved_price}', `buy_price`='{$buy_price}', `country`='{$country}', `state`='{$state}', `city`='{$city}', `ship_details`='{$ship_details}' WHERE `id`='{$id}'");
	return true;
}

function auction_delete($auction_id) {
	$db = db();
	$db->query("DELETE FROM `auction_new` WHERE `id`='{$auction_id}'");
	$db->query("DELETE FROM `auction_bid` WHERE `auction_id`='{$auction_id}'");
	$db->query("DELETE FROM `auction_cart` WHERE `auction_id`='{$auction_id}'");
	$db->query("DELETE FROM `auction_offer` WHERE `auction_id`='{$auction_id}'");
	$db->query("DELETE FROM `auction_temp` WHERE `auction_id`='{$auction_id}'");
	$db->query("DELETE FROM `auction_view` WHERE `auction_id`='{$auction_id}'");
	return true;
}

function count_auction_by_cat($cat_id) {
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_new` WHERE `category_id`='{$cat_id}'");
	return $q1->num_rows;
}

function time_countdown($time) {
	$rem = date(strtotime($time) - time());
	$day = abs(floor($rem / 86400));
	$hr = abs(floor(($rem % 86400) / 3600));
	$min = abs(floor(($rem % 3600) / 60));
	$sec = abs(($rem % 60));
	$result = "<strong>{$day}</strong> Day(s) <strong>{$hr}</strong> Hours";
	return $result;
}

function getMonth($pdate) {
	//  $date = DateTime::createFromFormat("dd/mm/yyyy", $pdate);
	//  return $date->format("m");
	return date("m", strtotime($pdate));
}

function getDay($pdate) {
	//$date = DateTime::createFromFormat("dd/mm/yyyy", $pdate);
	//return $date->format("d");
	return date("d", strtotime($pdate));
}

function getYear($pdate) {
	//   $date = DateTime::createFromFormat("dd/mm/yyyy", $pdate);
	// return $date->format("Y");
	return date("Y", strtotime($pdate));
}

function add_auction_view($user_id, $auction_id) {
	$auction_view = 0;
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_view` WHERE `user_id`='{$user_id}' AND `auction_id`='{$auction_id}'");
	$count = $q1->num_rows;
	if($count <= 0) {
		foreach(get_auction_all($auction_id) as $auction):
			$auction_view = $auction['view_count'];
		endforeach;
		$auction_view += 1;
		$q2 = $db->query("INSERT INTO `auction_view`(`user_id`,`auction_id`)VALUES('{$user_id}','{$auction_id}')");
		$q3 = $db->query("UPDATE `auction_new` SET `view_count`='{$auction_view}' WHERE `id`='{$auction_id}'");
		return true;
	}
}

function count_auction_view($auction_id) {
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_view` WHERE `auction_id`='{$auction_id}'");
	return $q1->num_rows;
}

function count_auction_bid($auction_id) {
	$a_id = $auction_id;
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_bid` WHERE `auction_id`='{$a_id}'");
	return $q1->num_rows;
}

function add_aunction_bid($auction_id, $price, $type = null) {
	$user_id = get_userid();
	$db = db();
	$q2 = $db->query("INSERT INTO `auction_bid`(`auction_id`,`user_id`,`price`, `bid_type`)VALUES('{$auction_id}','{$user_id}','{$price}','{$type}')");
	$q1 = $db->query("UPDATE `auction_new` SET `current_bid`='{$price}' WHERE `id`='{$auction_id}'");
	$q3 = $db->query("SELECT * FROM `auction_new` WHERE `id`='{$auction_id}'");
	$row = $q3->fetch_assoc();
	$owner_id = $row['user_id'];
	fire_hook('auction.bid.added', null, array($owner_id, $auction_id, $price));
	return true;
}

function get_auction_bid_by_id($user_id, $auction_id, $type = 'bid') {
	$db = db();
	$q2 = $db->query("SELECT * FROM `auction_bid` WHERE `user_id`='{$user_id}' AND `auction_id`='{$auction_id}' AND bid_type='{$type}'");
	return $q2->fetch_assoc();
}

function update_bid_id($auction_id, $price, $userid) {
	$db = db();
	$q2 = $db->query("UPDATE `auction_bid` SET `price`='{$price}' WHERE `auction_id`='{$auction_id}' AND `user_id`='{$userid}'");
	return true;
}

function add_auction_offer($auction_id, $price) {
	$user_id = get_userid();
	$db = db();
	$q2 = $db->query("INSERT INTO `auction_offer`(`auction_id`,`user_id`,`price`)VALUES ('{$auction_id}','{$user_id}','{$price}')");
	$q1 = $db->query("SELECT * FROM `auction_new` WHERE `id`='{$auction_id}'");
	$row = $q1->fetch_assoc();
	$owner_id = $row['user_id'];
	fire_hook('auction.offer.added', null, array($owner_id, $auction_id, $price));
	return true;
}

function get_auction_offer_by_id($user_id, $auction_id) {
	$db = db();
	$q2 = $db->query("SELECT * FROM `auction_offer` WHERE `user_id`='{$user_id}' AND `auction_id`='{$auction_id}'");
	return $q2->num_rows;
}

function get_auction_offer($auction_id) {
	$db = db();
	$q2 = $db->query("SELECT * FROM `auction_offer` WHERE `auction_id`='{$auction_id}'");
	return $q2->fetch_assoc();
}

function get_auction_offer_all($auction) {
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_offer` WHERE `auction_id`='{$auction}'");
	return fetch_all($q1);
}

function add_auction_cart($auction_id) {
	$user_id = get_userid();
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_cart` WHERE `user_id`='{$user_id}' AND `auction_id`='{$auction_id}'");
	$row = $q1->num_rows;
	if($row <= 0) {
		$q2 = $db->query("INSERT INTO `auction_cart`(`auction_id`,`user_id`)VALUES('{$auction_id}', '{$user_id}')");
		return true;
	}
}

function get_auction_cart() {
	$db = db();
	$user_id = get_userid();
	$q1 = $db->query("SELECT * FROM `auction_cart` WHERE `user_id`='{$user_id}'");
	return fetch_all($q1);

}

function proceed_payment($auction_id) {
	$db = db();
	$user_id = get_userid();
	$auc_id = $auction_id;
	$bidType = "bid";
	$q2 = $db->query("SELECT * FROM `auction_bid` WHERE `user_id`='{$user_id}' AND `auction_id`='{$auc_id}' AND bid_type ='{$bidType}'");
	return $q2->fetch_assoc();

}

function empty_cart($user_id = null) {
	$user_id = isset($user_id) ? $user_id : get_userid();
	$db = db();
	$db->query("DELETE FROM `auction_cart` WHERE `user_id`='{$user_id}'");
	return true;
}

function remove_auction_cart($auction_id) {
	$db = db();
	$q1 = $db->query("DELETE FROM `auction_cart` WHERE `auction_id`='{$auction_id}'");
	return true;
}

function get_bid_user($userid = '') {
	$user_id = ($userid == '' ? get_userid() : $user_id = $userid);
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_bid` WHERE `user_id`='{$user_id}'");
	return fetch_all($q1);
}

function get_auction_bid($auction_id) {
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_bid` WHERE `auction_id`='{$auction_id}'");
	return $q1->fetch_assoc();
}

function get_auction_bid_all($auction_id) {
	$db = db();
	$bidType = "bid";
	$q1 = $db->query("SELECT * FROM `auction_bid` WHERE `auction_id`='{$auction_id}' AND bought =0 AND bid_type = '{$bidType}'");
	return fetch_all($q1);
}

function get_offer_user_all($userid = '') {
	$user_id = ($userid == '' ? get_userid() : $user_id = $userid);
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_offer` WHERE `user_id`='{$user_id}'");
	return fetch_all($q1);
}

function get_offer_user($user_id = null) {
	$user_id = isset($user_id) ? $user_id : get_userid();
	$db = db();
	$query = $db->query("SELECT * FROM `auction_offer` WHERE `user_id` = '".$user_id."'");
	return (array) $query->fetch_assoc();
}

function add_auction_to_feature() {

}

function get_my_auction() {
	$user_id = get_userid();
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_new` WHERE user_id='{$user_id}'");
	return fetch_all($q1);
}

function auction_approve($auction_id) {
	$status = 1;
	$db = db();
	$q1 = $db->query("UPDATE `auction_new` SET status='{$status}' WHERE id='{$auction_id}'");
	return true;
}

function auction_select_approve($auction_id, $tmp = 1, $type = null) {
	$tmp_approve = $tmp;
	$db = db();
	$bidType = "bid";
	$q1 = $db->query("SELECT * FROM `auction_bid` WHERE `tmp_approve`='{$tmp_approve}' AND `auction_id`='{$auction_id}' AND bid_type = '{$bidType}'");
	return fetch_all($q1->fetch_assoc());
}

function auction_temp_approve($auction_id, $user_id, $temp_price = '') {
	$status = 2;
	$db = db();
	$tmp = 1;
	$price = $temp_price;
	//$user_id= get_userid();
	$q1 = $db->query("UPDATE `auction_new` SET `status`='{$status}' WHERE id='{$auction_id}'");
	$q3 = $db->query("UPDATE `auction_bid` SET `tmp_approve`='{$tmp}' WHERE `auction_id`='{$auction_id}' AND `user_id`='{$user_id}'");
	$q2 = $db->query("SELECT * FROM `auction_new` WHERE `id`='{$auction_id}'");
	//$q4=$db->query("INSERT INTO `auction_cart`(`auction_id`,`price`,`user_id`)VALUES('{$auction_id}', '{$price}', '{$user_id}')");
	$data = $q2->fetch_assoc();
	$owner_id = $data['user_id'];
	$to_user = $user_id;
	fire_hook('auction.tmp.approve', null, array($owner_id, $to_user, $data));
}

function auction_temp_cancel($auction_id, $user_id, $price) {
	$to_user = $user_id;
	$owner_id = get_userid();
	$status = 0;
	$db = db();
	$tmp = 0;
	$q1 = $db->query("UPDATE `auction_new` `status`='{$status}' WHERE `id`='{$auction_id}'");
	$q2 = $db->query("UPDATE `auction_bid` SET `tmp_approve`='{$tmp}' WHERE `auction_id`='{$auction_id}' AND `user_id`='{$user_id}'");
	$q3 = $db->query("DELETE FROM `auction_cart` WHERE `user_id`='{$user_id}' AND `auction_id`='{$auction_id}'");
	fire_hook('auction.tmp.cancel', null, array($owner_id, $to_user, $auction_id));
}

function auction_hot() {
	$db = db();
	$q1 = $db->query("SELECT * FROM ");
}

function auction_most_view($limit = '') {
	$lmt = ($limit == '' ? $lmt = 5 : $lmt = $limit);
	$db = db();
	$q1 = $db->query("SELECT * FROM `auction_new` ORDER BY `view_count` DESC LIMIT {$lmt}");
	return fetch_all($q1);
}

function get_friends_auctions_bid() {
	$db = db();
	$friends = get_friends();
	$friends[] = 0;
	$time = time() - 50;
	$friends = implode(',', $friends);
	$query = $db->query("SELECT DISTINCT(user_id) FROM `auction_bid` WHERE `user_id` IN ({$friends})");
	return fetch_all($query);
}

function get_friends_auctions() {
	$db = db();
	$friends = get_friends();
	$friends[] = 0;
	$friends = implode(',', $friends);
	$query = $db->query("SELECT * FROM `auction_new` WHERE `user_id` IN ({$friends})");
	return fetch_all($query);
}

function get_max_bid($auction_id) {
	$db = db();
	$q1 = $db->query("SELECT MAX(price) as bid FROM `auction_bid` WHERE `auction_id`='{$auction_id}'");
	return $q1->fetch_assoc();
}

function confirmAuctionPayment($auctionId, $quantity = null, $bid, $type) {
	$status = 2;
	if($quantity == null || $quantity == 0) {
		$quantity = 0;
		$status = 1;
	}
	$db = db();
	$updateAcution = $db->query("UPDATE `auction_new` SET status='{$status}', quantity='{$quantity}' WHERE id='{$auctionId}'");
	if($updateAcution) {
		$status = 1;
		$db->query("UPDATE `auction_bid` SET bought='{$status}' WHERE id='{$bid}' AND bid_type = '{$type}'");
	}
	return true;
}

function approved_auction_bid($user) {
	$user = ($user) ? $user : get_userid();
	$db = db();
	$type = 'bid';
	$temp = 1;
	$record = $db->query("SELECT * FROM `auction_bid` WHERE `user_id`='{$user}' AND bid_type='{$type}' AND tmp_approve ='{$temp}'");
	return fetch_all($record);
}

function buy_auction_bid($auction_id, $userId = null) {
	$query = '';
	if($userId) {
		$query = " AND user_id =".$userId;
	}
	$db = db();
	$type = 'buy';
	$record = $db->query("SELECT * FROM `auction_bid` WHERE `auction_id`='{$auction_id}' AND bid_type='{$type}'{$query}");
	return fetch_all($record);
}

function buy_auction_delete($auction, $bid) {
	db()->query("DELETE FROM `auction_bid` WHERE `auction_id`='{$auction}' AND `id`='{$bid}' AND bought =0");
}
