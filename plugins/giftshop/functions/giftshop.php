<?php

function giftshop_get_gifts($filter = null) {
	$filter = $filter ? $filter : array();
	$db = db();
	$sql = "SELECT *, id AS gift_id FROM giftshop WHERE 1";
	if(isset($filter['category_id']) && $filter['category_id']) {
		$sql .= " AND category = ".$filter['category_id'];
	}
	if(isset($filter['term']) && $filter['term']) {
		$sql .= " AND (name LIKE '%".$filter['term']."%' OR description LIKE '%".$filter['term']."%')";
	}
	$limit = isset($filter['limit']) && $filter['limit'] ? $filter['limit'] : 10;
	return paginate($sql, $limit);
}

function giftshop_get_sent_gifts($filter = null) {
	$filter = $filter ? $filter : array();
	$user_id = isset($filter['user_id']) && $filter['user_id'] ? $filter['user_id'] : get_userid();
	$db = db();
	$sql = "SELECT giftshop.*, giftshop_mygift.gift_id, giftshop_mygift.receiver_id AS user_id, COUNT(giftshop_mygift.id) AS count FROM giftshop_mygift LEFT JOIN giftshop ON giftshop.id = giftshop_mygift.gift_id WHERE giftshop_mygift.sender_id = ".$user_id." GROUP BY giftshop_mygift.gift_id, giftshop_mygift.receiver_id";
	if(isset($filter['category_id']) && $filter['category_id']) {
		$sql .= " AND giftshop.category = ".$filter['category_id'];
	}
	if(isset($filter['term']) && $filter['term']) {
		$sql .= " AND (giftshop.name LIKE '%".$filter['term']."%' OR giftshop.description LIKE '%".$filter['term']."%')";
	}
	$limit = isset($filter['limit']) && $filter['limit'] ? $filter['limit'] : 10;
	return paginate($sql, $limit);
}

function giftshop_get_received_gifts($filter = null) {
	$filter = $filter ? $filter : array();
	$user_id = isset($filter['user_id']) && $filter['user_id'] ? $filter['user_id'] : get_userid();
	$db = db();
	$sql = "SELECT giftshop.*, giftshop_mygift.sender_id AS user_id, giftshop_mygift.gift_id AS group_id, COUNT(giftshop_mygift.id) AS count, giftshop.* LEFT JOIN giftshop ON giftshop.id = giftshop_mygift.gift_id WHERE giftshop_mygift.receiver_id = ".$user_id." GROUP BY giftshop_mygift.gift_id, giftshop_mygift.sender_id";
	if(isset($filter['category_id']) && $filter['category_id']) {
		$sql .= " AND giftshop.category = ".$filter['category_id'];
	}
	if(isset($filter['term']) && $filter['term']) {
		$sql .= " AND (giftshop.name LIKE '%".$filter['term']."%' OR giftshop.description LIKE '%".$filter['term']."%')";
	}
	$limit = isset($filter['limit']) && $filter['limit'] ? $filter['limit'] : 10;
	return paginate($sql, $limit);
}

function add_giftshop_category($val) {
	$db = db();
	$cat = $val['category'];
	$db->query("INSERT INTO giftshop_category (category) VALUE('$cat')");
}

function get_giftshop_category() {
	$db = db();
	$query = $db->query("SELECT * FROM giftshop_category");
	return fetch_all($query);
}

function get_giftshop_cat_count($cat) {
	$db = db();
	$cat = $cat;
	$query = $db->query("SELECT * FROM giftshop WHERE category='".$db->real_escape_string($cat)."'");
	return $query->num_rows;
	//return COUNT($query->fetch_assoc());
}

function get_giftshop_category_id($id) {
	$db = db();
	$query = $db->query("SELECT * FROM giftshop_category WHERE id ='$id'");
	return fetch_all($query);
}

function update_giftshop($id, $val) {
	$db = db();
	$category = $val['category'];
	$db->query("UPDATE giftshop_category SET category='$category' WHERE id =$id ");
}

function delete_giftshop_cat($id) {
	$db = db();
	$db->query("DELETE FROM giftshop_category WHERE id='$id' ");
}

function delete_giftshop_gift($id) {
	$db = db();
	$db->query("DELETE FROM giftshop WHERE id='$id' ");
}

function add_gift($val, $image) {
	$db = db();
	$name = $val['name'];
	$category = $val['category'];
	$price = $val['price'];
	$description = $val['description'];
	if($image) {
		$uploader = new Uploader($image, 'image');
		$uploader->setPath('gifshop/');
		if($uploader->passed()) {
			$original = $uploader->resize($uploader->getWidth(300), null, 'inside', 'down')->result();
		} else {
			return false;
		}
	} else {
		return false;
	}
	$db->query("INSERT INTO giftshop (name,category,price,description,image) VALUES ('$name','$category','$price','$description','$original')");
}

function get_giftshop_gift() {
	$db = db();
	$query = $db->query("SELECT * FROM giftshop");
	return fetch_all($query);
}

function get_giftshop_gift_id($id) {
	$db = db();
	$query = $db->query("SELECT * FROM giftshop WHERE id='$id'");
	return $query->fetch_assoc();
}

function get_giftshop_mygift_id($id) {
	$db = db();
	return paginate("SELECT * FROM giftshop WHERE id='$id'");
}

function update_giftshop_gift($id, $val, $image) {
	$db = db();
	$name = $val['name'];
	$category = $val['category'];
	$price = $val['price'];
	$description = $val['description'];
	if($image) {
		$uploader = new Uploader($image, 'image');
		$uploader->setPath('gifshop/');
		if($uploader->passed()) {
			$original = $uploader->resize($uploader->getWidth(300), null, 'inside', 'down')->result();
		}
	} else {
		$original = $val['image'];
	}
	$query = $db->query("UPDATE giftshop SET name='$name',category='$category',price='$price',description='$description',image='$original' WHERE id =$id ");
}

function get_giftshop_gift_all() {
	$db = db();
	return paginate("SELECT * FROM giftshop", 10);
}

function get_giftshop_gift_m() {
	$db = db();
	$id = get_userid();
	$query = $db->query("SELECT * FROM giftshop_mygift WHERE receiver_id='$id'");
	return fetch_all($query);
}

function giftshop_get_gift_send($id) {
	$db = db();
	$sql = "SELECT * FROM giftshop_mygift WHERE id = ".$id;
	$query = $db->query($sql);
	return $query->fetch_assoc();
}

function get_giftshop_gift_cat($category) {
	$db = db();
	return paginate("SELECT * FROM giftshop WHERE category='$category'", 10);
}

function get_giftshop_userbalance() {
	$db = db();
	$id = get_userid();
	$query = $db->query("SELECT * FROM users WHERE id='$id'");
	return fetch_all($query);
}


function giftshop_get_gift($id) {
	$db = db();
	$query = $db->query("SELECT * FROM giftshop WHERE id='$id'");
	return fetch_all($query);
}

function giftshop_send($id, $receiver_id) {
	$db = db();
	$sender_id = get_userid();
	$query = $db->query("INSERT INTO giftshop_mygift (sender_id,receiver_id,gift_id) VALUES('$sender_id','$receiver_id','$id')");
	if($query) {
		$gift = giftshop_get_gift($id)[0];
		$data = array('receiver_id' => $receiver_id, 'sender_id' => $sender_id, 'name' => $gift['name'], 'image' => $gift['image']);
		send_notification($receiver_id, 'gift.send', $id, giftshop_get_gift($id)[0]);
		fire_hook("gift.received", null, array($receiver_id, $data));
		$status = true;
		$message = lang('gift::gift-sent');
	} else {
		$status = false;
		$message = lang('gift::gift-not-sent');
	}
	return array('status' => $status, 'message' => $message);
}

function giftshop_last_recieved($limit) {
	$db = db();
	$id = get_userid();
	$query = $db->query("SELECT * FROM giftshop_mygift WHERE receiver_id='$id' ORDER BY id DESC LIMIT $limit");
	return fetch_all($query);
}

function giftshop_num_gifts() {
	$db = db();
	$query = $db->query("SELECT COUNT(id) FROM giftshop");
	$result = $query->fetch_row();
	return $result[0];
}

function giftLastSent($limit) {
	$db = db();
	$id = get_userid();
	$query = $db->query("SELECT * FROM giftshop_mygift WHERE sender_id='$id' ORDER BY id DESC LIMIT $limit");
	return fetch_all($query);
}