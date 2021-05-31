<?php
function get_rank_categories() {
	$query = db()->query("SELECT * FROM `rank_categories` ORDER BY `category_order` ASC");
	return fetch_all($query);
}
function rank_add_category($val) {
	$expected = array(
		'title' => '',
		'point' => '',
	);

	/**
	 * @var $title
	 * @var $point
	 */
	extract(array_merge($expected, $val));
	$order = db()->query('SELECT id FROM rank_categories');
	$order = $order->num_rows;
	db()->query("INSERT INTO `rank_categories`(`title`,`point`,`category_order`) VALUES('".$title."','".$point."','".$order."')");
	return true;
}
function get_rank_category($id) {
	$query = db()->query("SELECT * FROM `rank_categories` WHERE `id`='".$id."'");
	return $query->fetch_assoc();
}
function save_rank_category($val, $category) {
	$expected = array(
		'title' => '',
		'point' => '',
	);

	/**
	 * @var $title
	 * @var $point
	 */
	extract(array_merge($expected, $val));
	//print_r($category);die;
	$id = $category['id'];
	db()->query("UPDATE rank_categories SET title = '".$title."', point = '".$point."' WHERE id = '".$id."'");
	return true;
}
function delete_rank_category($id, $category) {
	db()->query("DELETE FROM `rank_categories` WHERE `id`='".$id."'");
	return true;
}

function add_rank($val) {

	$expected = array(
		'title' => '',
		'point' => '',
	);

	/**
	 * @var $title
	 * @var $point
	 */
	extract(array_merge($expected, $val));
	
	$time = time();
	$userid = get_userid();
	//print_r($title);die;
	$db = db();
	$db->query("INSERT INTO rank_points (user_id, title, point, time) VALUES ('".$userid."','".$title."','".$point."', '".$time."')");
	$rankId = $db->insert_id;
	return $rankId;
}

function get_ranks() {
	$query = db()->query("SELECT * FROM `rank_points`");
	return fetch_all($query);
}
function get_rank_point($id) {
	$query = db()->query("SELECT * FROM `rank_points` WHERE `id`='".$id."'");
	return $query->fetch_assoc();
}
function save_rank_point($val, $id) {
	$expected = array(
		'title' => '',
		'point' => '',
	);

	/**
	 * @var $title
	 * @var $point
	 */
	extract(array_merge($expected, $val));
	db()->query("UPDATE rank_points SET title = '".$title."', point = '".$point."' WHERE id = '".$id."'");
	return true;
}
function delete_rank_points($id) {
	db()->query("DELETE FROM `rank_points` WHERE `id`='".$id."'");
	return true;
}
function get_post_point() {
	$title = 'posts';
	$query = db()->query("SELECT point FROM `rank_points` WHERE `title`='".$title."'");
	return $query->fetch_assoc();
}
function get_likes_point() {
	$title = 'likes';
	$query = db()->query("SELECT point FROM `rank_points` WHERE `title`='".$title."'");
	return $query->fetch_assoc();
}
function user_post_points($user_id) {
    $db = db();
	//posts
	$posts = $db->query("SELECT SUM(point) AS post_points FROM `feeds` WHERE `user_id`='{$user_id}'");
    $posts = $posts->fetch_assoc();
    return $posts;
 }
 function user_likes_points($user_id) {
    $db = db();
	//posts
	$likes = $db->query("SELECT SUM(point) AS like_points FROM `likes` WHERE `user_id`='{$user_id}'");
    $likes = $likes->fetch_assoc();
    return $likes;
 }
 function get_user_total_points($user_id) {
	 $post = user_post_points($user_id);
	 $likes = user_likes_points($user_id);
	 $total = $post['post_points'] + $likes['like_points'];
	 return $total;
 }

 function rank_add_range($val) {
	$expected = array(
		'range' => '',
		'caption' => '',
	);

	/**
	 * @var $range
	 * @var $caption
	 */
	extract(array_merge($expected, $val));
	db()->query("INSERT INTO `rank_ranges`(`range`,`caption`) VALUES('".$range."','".$caption."')");
	return true;
}
function get_r_ranges() {
	$query = db()->query("SELECT * FROM `rank_ranges`");
	return fetch_all($query);
}
function get_rank($user_id) {
	 $point = get_user_total_points($user_id);
	 $ranges = get_r_ranges();
	 foreach ($ranges as $range) {
		 if($range['range'] == "0-40") {
			$min = 0;$max = 40;
			if(in_array($point, range(0, 40))) {
				return $range['caption'];
			}
		 }
		 if($range['range'] == "41-70") {
			if(in_array($point, range(41, 70))) {
				return $range['caption'];
			}
		 }
		  if($range['range'] == "71-100") {
			if(in_array($point, range(71, 100))) {
				return $range['caption'];
			}
		 }
	 } 
	 
 }
 function get_rank_range($id) {
	$query = db()->query("SELECT * FROM `rank_ranges` WHERE `id`='".$id."'");
	return $query->fetch_assoc();
}
function save_rank_range($val, $id) {
	$expected = array(
		'caption' => '',
	);

	/**
	 * @var $caption
	 */
	extract(array_merge($expected, $val));
	db()->query("UPDATE rank_ranges SET caption = '".$caption."' WHERE id = '".$id."'");
	return true;
}
function delete_rank_range($id) {
	db()->query("DELETE FROM `rank_ranges` WHERE `id`='".$id."'");
	return true;
}