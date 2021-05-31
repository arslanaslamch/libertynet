<?php

//returns the info of lastvisitors to the profile of the supplied userid
function get_last_visitors($userid = null, $limit = 6) {
	$userid = ($userid) ? $userid : get_userid();
	$users = array();
	$limit = ($limit == "none") ? "" : "LIMIT $limit";
	$query = db()->query("SELECT * FROM `lastvisitor_profile_view` WHERE `viewed_id`='{$userid}' ORDER BY id DESC $limit");
	if($query) return fetch_all($query);
	return $users;
}

function insert_view_visitors($vieweruserid, $vieweduserid, $datetimes, $gender = false, $avatar = '-1') {
	$newgender = ($gender == "" || !$gender) ? "" : ", `gender`='{$gender}' ";
	$newavatar = ($avatar == '-1') ? ", `has_avatar`='-1'" : ", `has_avatar`='1' ";
	if(views_exist($vieweruserid, $vieweduserid) == 1 && $vieweruserid != "" && $vieweduserid != "" && $datetimes != "") {
		$sql = "UPDATE `lastvisitor_profile_view` SET `view_date` = '{$datetimes}' {$newgender} {$newavatar} WHERE `viewer_id`='{$vieweruserid}' AND `viewed_id`='{$vieweduserid}' ";
		db()->query($sql);
	} else if(views_exist($vieweruserid, $vieweduserid) == -1 && $vieweduserid != "" && $vieweruserid != "" && $datetimes != "") {
		$sql = "INSERT INTO `lastvisitor_profile_view` (`viewer_id`,`viewed_id`,`view_date`,`gender`,`has_avatar`)VALUES(
                '{$vieweruserid}','{$vieweduserid}','{$datetimes}','{$gender}','{$avatar}')";
		db()->query($sql);
	}
}

function views_exist($vieweruserid, $vieweduserid) {
	$sql = "SELECT * FROM lastvisitor_profile_view WHERE viewer_id = '{$vieweruserid}' AND viewed_id = '{$vieweduserid}' ";
	$query = db()->query($sql);
	$test = fetch_all($query);
	if(!empty($test)) return 1;
	else return -1;
}

function lastvisitor_find_user($id, $gender = null, $opposite_gender = false, $has_avatar = false) {
	$avatar = '';
	if($opposite_gender == true && $gender == 'male') {
		$gender = 'female';
		$opposite_gender = " AND `gender` = '".$gender."'";
	} else if($opposite_gender == true && $gender == 'female') {
		$gender = 'male';
		$opposite_gender = " AND `gender` = '".$gender."'";
	} else {
		$opposite_gender = '';
	}

	if($has_avatar == true) {
		$avatar = " AND avatar != '' AND avatar IS NOT NULL";
	}

    $sql = "SELECT * FROM `users` WHERE id = ".$id.$opposite_gender.$avatar;
	$query = db()->query($sql);
	if($query) {
		$user = $query->fetch_assoc();
		return $user;
	}

	return false;
}

function AgoMin($time, $full = false) {
	$time = time() - $time; // to get the time since that moment
	$time = ($time < 1) ? 1 : $time;
	$tokens = array(
		31536000 => ' year ago',
		2592000 => ' month ago',
		604800 => ' week ago',
		86400 => ' day ago',
		3600 => ' hour ago',
		60 => ' minutes ago',
		1 => ' seconds ago'
	);

	foreach($tokens as $unit => $text) {
		if($time < $unit) continue;
		$numberOfUnits = floor($time / $unit);
		return $numberOfUnits.''.$text.(($numberOfUnits > 1) ? '' : '');
	}
}

