<?php
/**
 * function to check if user has like an item
 * @param string $type
 * @param string $typeId
 * @param int $userid
 * @return boolean
 */
function has_liked($type, $typeId, $likeType = null, $userid = null) {
    if(!isset($likeType)) {
        $likeType = config('feed-like-type', 'regular') == 'regular' ? 1 : 3;
    }
	if(!is_loggedIn()) return false;
	$likes = get_likes($type, $typeId, $likeType);
	$userid = ($userid) ? $userid : get_userid();

	if(in_array($userid, $likes)) return true;

	return false;
}

/**
 * function to know if user has reacted to the post
 * @param string $type
 * @param int $typeId
 * @param int $userid
 * @return boolean
 */
function has_reacted($type, $typeId, $userid = null) {
	if(!is_loggedIn()) return false;
	$likes = get_likes($type, $typeId, 3);
	$userid = $userid ? $userid : get_userid();

	if(in_array($userid, $likes)) return true;

	return false;
}

/**
 * Method to get reactors of a post or photo
 */

function count_reactors($type, $typeId) {
	$db = db();
	$query = $db->query("SELECT COUNT(like_id) FROM likes WHERE type = '".$type."' AND type_id = '".$typeId."'");
	$result = $query->fetch_row();
	$count = $result[0];
	return $count;
}

function get_reactors($type, $typeId, $limit = 10) {
	$fields = get_users_fields();
	$query = db()->query("SELECT like_type,{$fields} FROM likes INNER JOIN users ON likes.user_id = users.id WHERE type='{$type}' AND type_id='{$typeId}' ORDER BY time DESC LIMIT {$limit}");
	return fetch_all($query);
}

function get_reactions($type, $type_id) {
    $reactions = array();
    $db = db();
    $sql = "SELECT DISTINCT like_type FROM likes WHERE type = '".$type."' AND type_id = '".$type_id."'";
    $query = $db->query($sql);
    while ($row = $query->fetch_row()) {
        $reactions[] = $row[0];
    }
	return $reactions;
}

function get_react_image($type) {
	$array = array(
		'0' => 'dislike',
		'1' => 'like',
		'4' => 'love',
		'5' => 'haha',
		'7' => 'wow',
		'8' => 'sad',
		'9' => 'angry'
	);
	$type = isset($array[$type]) ? $array[$type] : 'like';
	return img('images/reaction/'.$type.'.png');
}

/**
 * Method to react to a post or photo
 *
 * @param string $type
 * @param int $typeId
 * @param int $code
 * @return boolean
 */
function like_react($type, $typeId, $code, $userid = null) {
	$userid = ($userid) ? $userid : get_userid();
	if(!$code) {
		//we are remove it from the reactors
		db()->query("DELETE FROM `likes` WHERE `user_id`='{$userid}' AND `type`='{$type}' AND `type_id`='{$typeId}'");
		forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-3');
	} else {
		if(!has_reacted($type, $typeId, $userid)) {
			$time = time();
			db()->query("INSERT INTO `likes` (user_id,type,type_id,time,like_type)VALUES('{$userid}','{$type}','{$typeId}','{$time}','{$code}')");
			fire_hook("like.react", null, array($type, $typeId, $code, $userid));
			fire_hook("creditgift.addcredit.like", null, array($userid));
			forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-3');
		} else {
			db()->query("UPDATE `likes` SET like_type='{$code}' WHERE `user_id`='{$userid}' AND `type`='{$type}' AND `type_id`='{$typeId}'");
			forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-3');
			fire_hook("like.react", null, array($type, $typeId, $code, $userid));
		}
	}
    return true;
}

/**
 * @param $type
 * @param $typeId
 * @param null $userid
 * @return bool
 */

function has_disliked($type, $typeId, $userid = null) {
	return has_liked($type, $typeId, 0, $userid);
}

function get_likes($type, $typeId, $likeType = 1) {
	$cacheName = "likes-".$type.'-'.$typeId.'-'.$likeType;
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		$sql = "SELECT `user_id` FROM `likes` WHERE `type`='{$type}' AND `type_id`='{$typeId}' ";
		if($likeType != '3') {
			$sql .= "AND `like_type`='{$likeType}'";
		}
		$query = db()->query($sql);
		$result = array();
		if($query) {
			while($fetch = $query->fetch_assoc()) {
				$result[] = $fetch['user_id'];
			}
		}
		//set_cacheForever($cacheName, $result);
		return $result;
	}
}

function count_likes($type, $typeId, $likeType = 1) {
	$likes = get_likes($type, $typeId, $likeType);
	return (count($likes)) ? count($likes) : 0;
}

function count_dislikes($type, $typeId) {
	return count_likes($type, $typeId, 0);
}

function like_item($type, $typeId, $w, $userid = null) {
    $result = array(
        'status' => 0,
        'likes' => 0,
        'dislikes' => 0
    );
	$userid = ($userid) ? $userid : get_userid();
	$likeType = 1;
	if(!is_loggedIn()) return false;
	if($w == 1) {
		//like item
		if(!has_liked($type, $typeId, $likeType, $userid)) {
			if(has_disliked($type, $typeId)) {
				db()->query("DELETE FROM `likes` WHERE `user_id`='{$userid}' AND `type`='{$type}' AND `type_id`='{$typeId}'");
				forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-0');
			}
			$time = time();
			//rank point starts
			if(plugin_loaded('ranker')) {
			$point = get_likes_point();
			}else {$point['point'] = 0;}
			db()->query("INSERT INTO `likes` (user_id,type,type_id,time,like_type,point)VALUES('{$userid}','{$type}','{$typeId}','{$time}','1','".$point['point']."')");
			fire_hook("like.item", null, array($type, $typeId, $userid));
			fire_hook("creditgift.addcredit.like", null, array($userid));
		}
	} else {
		//unlike item
		db()->query("DELETE FROM `likes` WHERE `user_id`='{$userid}' AND `type`='{$type}' AND `type_id`='{$typeId}'");
		fire_hook("like.delete.item", null, array($type, $typeId, $userid));
		fire_hook("creditgift.addcredit.dislike", null, array($userid));
	}

	forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-1');
	forget_cache("likes-".$type.'-'.$userid);
	$result['status'] = 1;
	$result['likes'] = count_likes($type, $typeId);
	$result['dislikes'] = count_likes($type, $typeId, 0);
	return $result;
}

function dislike_item($type, $typeId, $w, $userid = null) {
    $result = array(
        'status' => 0,
        'likes' => 0,
        'dislikes' => 0
    );
	$userid = ($userid) ? $userid : get_userid();
	if(!is_loggedIn()) return false;
	if($w == 1) {
		//dislike item
		if(!has_disliked($type, $typeId, $userid)) {
			if(has_liked($type, $typeId)) {
				db()->query("DELETE FROM `likes` WHERE `user_id`='{$userid}' AND `type`='{$type}' AND `type_id`='{$typeId}'");
				forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-1');
			}
			$time = time();
			db()->query("INSERT INTO `likes` (user_id,type,type_id,time,like_type)VALUES('{$userid}','{$type}','{$typeId}','{$time}','0')");
			fire_hook("dislike.item", null, array($type, $typeId, $userid));
		}
	} else {
		//undislike
		db()->query("DELETE FROM `likes` WHERE `user_id`='{$userid}' AND `type`='{$type}' AND `type_id`='{$typeId}'");
		fire_hook("dislike.delete.item", null, array($type, $typeId, $userid));
	}

	forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-0');
	forget_cache("likes-".$type.'-'.$userid);
    $result['status'] = 1;
    $result['likes'] = count_likes($type, $typeId);
    $result['dislikes'] = count_likes($type, $typeId, 0);
}

function get_likes_people($type, $typeId, $action) {
	$query = db()->query("SELECT username,first_name,last_name,avatar FROM likes INNER JOIN `users` on likes.user_id=users.id WHERE type='{$type}' and type_id='{$typeId}' AND like_type='{$action}'");
	return fetch_all($query);
}

function get_liked_items($type, $userid = null) {
	$userid = ($userid) ? $userid : get_userid();
	$cacheName = "likes-".$type.'-'.$userid;
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		$q = db()->query("SELECT type_id FROM likes WHERE user_id='{$userid}' AND type='{$type}'");
		$r = array(0);
		while($fetch = $q->fetch_assoc()) {
			$r[] = (int) $fetch['type_id'];
		}
		//set_cacheForever($cacheName, $r);
		return $r;
	}

}

function delete_likes($type, $typeId) {
	db()->query("DELETE FROM likes WHERE type='{$type}' AND type_id='{$typeId}'");
	forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-0');
	forget_cache($cacheName = "likes-".$type.'-'.$typeId.'-1');

	return true;
}

function get_type_like($type, $typeId, $userid = null) {
	$userid = ($userid) ? $userid : get_userid();
	$sql = db()->query("SELECT * FROM `likes` WHERE `type`='{$type}' AND `type_id`='{$typeId}' AND `user_id`='{$userid}'");
	if($sql) {
		return $sql->fetch_assoc();
	}
	return false;
}