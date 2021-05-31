<?php
function page_add($val) {
	/**
	 * @var $name
	 * @var $page_url
	 * @var $description
	 * @var $category
	 * @var $fields
	 */
	$expected = array('name' => '', 'page_url' => '', 'description' => '', 'category' => '', 'fields' => array());
	extract(array_merge($expected, $val));
	$userid = get_userid();
	$time = time();
	$name = sanitizeText($name);
	$description = sanitizeText($description);
	$category = sanitizeText($category);
	db()->query("INSERT INTO `pages` (page_user_id, page_title, page_desc, page_url, page_created_at, page_category_id) VALUES ('".$userid."', '".$name."', '".$description."', '".$page_url."', '".$time."', '".$category."')");
	$insertedId = db()->insert_id;
	/**
	 * For custom field values lets insert
	 */
	if(!empty($fields)) {
		$sqlFields = "`id`";
		$sqlValues = "'{$insertedId}'";
		foreach($fields as $field => $value) {
			$sqlFields .= ",`{$field}`";
			$value = sanitizeText($value);
			$sqlValues .= ",'{$value}'";
		}
		db()->query("INSERT INTO `page_details` ({$sqlFields}) VALUES({$sqlValues})");
	}
	if(plugin_loaded('like')) like_item('page', $insertedId, 1);
	fire_hook("page.created", null, array($insertedId, $val));
	fire_hook('plugins.users.category.updater', null, array('pages', $val, $insertedId, 'page_id'));
	return $insertedId;
}

function find_page($id, $few = false) {
	$sql = "SELECT * FROM `pages` LEFT JOIN `page_details` ON pages.page_id=page_details.id WHERE ".(is_numeric($id) ? "page_id = ".$id : "page_url = '".$id."'");
	if($few) $sql = "SELECT page_id,verified,page_title,page_user_id,page_desc,page_url,page_logo,page_cover_resized FROM `pages`  WHERE ".(is_numeric($id) ? "page_id = ".$id : "page_url = '".$id."'");
	$query = db()->query($sql);
	if($query) return $query->fetch_assoc();
	return false;
}

function get_pages($type = 'mine', $term = '', $limit = null, $category = 'all', $filter = 'all') {
    $limit = $limit ? $limit : 12;
	$sql = "SELECT featured,page_id,verified,page_user_id,page_title,page_desc,page_url,page_logo,page_cover_resized,page_cover,page_category_id,page_created_at FROM pages";
	$sql = fire_hook("use.different.pages.query", $sql, array());
	switch($type) {
		case 'saved' :
			$saved = get_user_saved('page');
			$saved[] = 0;
			$saved = implode(',', $saved);
			$sql .= " WHERE page_id IN ({$saved})";
			$sql = fire_hook("more.pages.query.filter", $sql, array());
			$sql = fire_hook('users.category.filter', $sql, array($sql));
		break;
		case 'mine':
			$userid = get_userid();
			$sql .= " WHERE page_user_id='{$userid}'";
			if($category and $category != 'all') {
				$sql .= " AND page_category_id='{$category}' ";
			}
			if($filter and $filter == 'featured') {
				$sql .= " AND featured = '1' ";
			}
		break;
		case 'browse':
			$sql .= " WHERE page_title!='' AND (page_title LIKE '%{$term}%' OR page_desc LIKE '%{$term}%')";
			if($category and $category != 'all') {
				$sql .= " AND page_category_id='{$category}' ";
			}
			if($filter and $filter == 'featured') {
				$sql .= " AND featured = '1' ";
			}
			$sql = fire_hook("more.pages.query.filter", $sql, array());
			$sql = fire_hook('users.category.filter', $sql, array($sql));
		break;
		case 'search':
			$sql .= " WHERE page_title LIKE '%{$term}%' OR page_desc LIKE '%{$term}%'";
		break;
		case 'likes':
			if(plugin_loaded('like')) {
				$likes = get_liked_items('page', $term);
				$likes = implode(',', $likes);
				$sql .= " WHERE page_id IN ({$likes}) ";
			}
		break;
		case 'all' :

		break;
		case 'suggestion':
			$pageLiked = get_liked_items('page');
			$friendsPage = array(0);
			$friendsPage = fire_hook('system.relationship.method', $friendsPage, array('page'));
			$pageLiked = implode(',', $pageLiked);
			$friendsPage = implode(',', $friendsPage);
			$userid = get_userid();

			$sql .= " WHERE page_user_id != '{$userid}' AND page_id NOT IN ({$pageLiked}) AND (page_id IN ({$friendsPage}) OR page_title != '' )";
			//echo $sql;
			$sql = fire_hook("more.pages.query.filter", $sql, array());
			$sql = fire_hook('users.category.filter', $sql, array($sql));
		break;
	}
	$sql .= " GROUP BY page_id ORDER BY page_created_at DESC ";

	return paginate($sql, $limit);
}

function page_url($slug = null, $page) {
	return url_to_pager("page-profile", array('slug' => $page['page_url'])).'/'.$slug;
}

function manage_page_url($slug = 'general', $page) {
	return url('page/'.$page['page_id'].'/manage/'.$slug);
}

function save_page_general_setings($val, $page) {
	/**
	 * @var $name
	 * @var $page_url
	 * @var $description
	 * @var $category
	 * @var $fields
	 */

	$expected = array(
		'name' => '',
		'description' => '',
		'category' => '',
	);

	extract(array_merge($expected, $val));

	$page_id = $page['page_id'];
	$name = $name;
	$description = $description;
	$category = $category;
	$page_url = $page['page_title'] == $val['name'] ? $page['page_url'] : unique_slugger($val['name']);
	$sql = "UPDATE `pages` SET `page_title` = '".$name."', `page_desc` = '".$description."', page_url = '".$page_url."', `page_category_id` = '".$category."' WHERE `page_id` = '".$page_id."'";
	$db = db();
	$query = $db->query($sql);
	if($query) {
		fire_hook('page.updated', null, array($page, $val));
		fire_hook('plugins.users.category.updater', null, array('pages', $val, $page_id, 'page_id'));
		return true;
	}
}

function save_page_fields_setings($val, $page) {
	$expected = array('fields' => array());
	/**
	 * @var $fields
	 */
	extract(array_merge($expected, $val));
	$pageId = $page['page_id'];
	$query = db()->query("SELECT * FROM `page_details` WHERE `id`='{$pageId}'");

	if($query->num_rows) {
		$sqlFields = "";
		foreach($fields as $field => $value) {
			$value = sanitizeText($value);
			$sqlFields .= ($sqlFields) ? ",`{$field}`='{$value}'" : "`{$field}`='{$value}'";

		}

		$query = db()->query("UPDATE `page_details` SET {$sqlFields} WHERE `id`='{$pageId}'");
	} else {
		$sqlFields = "`id`";
		$sqlValues = "'{$pageId}'";
		foreach($fields as $field => $value) {
			$sqlFields .= ",`{$field}`";
			$value = sanitizeText($value);
			$sqlValues .= ",'{$value}'";
		}
		$query = db()->query("INSERT INTO `page_details` ({$sqlFields}) VALUES({$sqlValues})");
	}

	fire_hook("page.updated", null, array($page, $val));
	return true;
}

function save_page_roles($val, $page) {
	$pageId = $page['page_id'];
	foreach($val as $userid => $role) {
		if($userid != $page['page_user_id']) {
			$query = db()->query("SELECT * FROM `page_roles` WHERE `role_user_id`='{$userid}' AND `role_page_id`='{$pageId}'");
			if($query->num_rows > 0) {
				db()->query("UPDATE `page_roles` SET `page_role`='{$role}' WHERE `role_user_id`='{$userid}' AND `role_page_id`='{$pageId}'");
			} else {
				send_notification($userid, 'page.new.role', $pageId, array('role' => $role));
				db()->query("INSERT INTO `page_roles` (role_user_id,role_page_id,page_role)VALUES('{$userid}','{$pageId}','{$role}')");
			}
		}
	}

	forget_cache("page-editors-".$pageId);
	forget_cache("page-moderators-".$pageId);
	forget_cache("page-admins-".$pageId);
	fire_hook("page.roles.updated", null, array($page));
	return true;
}

function get_page_roles($id) {
	$query = db()->query("SELECT * FROM `page_roles` RIGHT JOIN `users` ON page_roles.role_user_id=users.id WHERE page_roles.role_page_id='{$id}'");
	if($query) return fetch_all($query);
	return array();
}

function remove_page_role($user, $pageId) {
	db()->query("DELETE FROM `page_roles` WHERE `role_user_id`='{$user}' AND `role_page_id`='{$pageId}'");
	forget_cache("page-editors-".$pageId);
	forget_cache("page-moderators-".$pageId);
	forget_cache("page-admins-".$pageId);
}

function get_page_cover($page = null, $original = true) {
	$default = img("images/cover.jpg");
	if(!$original and !empty($page['page_cover_resized'])) return url_img($page['page_cover_resized']);
	if(!empty($page['page_cover'])) return url_img($page['page_cover']);
	return ($original) ? '' : $default;
}

function get_page_logo($size, $page = null) {
	$avatar = $page['page_logo'];
	if($avatar) {
		return url(str_replace('%w', $size, $avatar));
	} else {

		return $image = img("images/page-avatar.png");
	}
}

function get_page_details($index, $page = null) {
	$page = ($page) ? $page : app()->profilePage;
	if(isset($page[$index])) return $page[$index];
	return false;
}

function is_page_admin($page) {
	if(!is_loggedIn()) return false;
	if($page['page_user_id'] == get_userid()) return true;
	$pageAdmins = get_page_admins($page);
	if(in_array(get_userid(), $pageAdmins)) return true;
	return false;
}

function is_page_moderator($page) {
	if(!is_loggedIn()) return false;
	if($page['page_user_id'] == get_userid()) return true;
	$pageModerators = get_page_moderators($page);
	if(in_array(get_userid(), $pageModerators)) return true;
	return false;
}

function is_page_editor($page) {
	if(!is_loggedIn()) return false;
	if($page['page_user_id'] == get_userid()) return true;
	$pageEditors = get_page_editors($page);
	if(in_array(get_userid(), $pageEditors)) return true;
	return false;
}

function get_page_editors($page) {
	$cacheName = "page-editors-".$page['page_id'];
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		$pageId = $page['page_id'];
		$query = db()->query("SELECT * FROM `page_roles` WHERE page_role='3' AND role_page_id='{$pageId}'");
		if($query) {
			$results = fetch_all($query);
			$editors = array();
			foreach($results as $role) {
				$editors[] = $role['role_user_id'];
			}
			set_cacheForever($cacheName, $editors);
			return $editors;
		}
	}
	return array();
}

function get_page_moderators($page) {
	$cacheName = "page-moderators-".$page['page_id'];
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		$pageId = $page['page_id'];
		$query = db()->query("SELECT * FROM `page_roles` WHERE page_role='2' AND role_page_id='{$pageId}'");
		if($query) {
			$results = fetch_all($query);
			$moderators = array();
			foreach($results as $role) {
				$moderators[] = $role['role_user_id'];
			}
			set_cacheForever($cacheName, $moderators);
			return $moderators;
		}
	}
	return array();
}

function get_page_admins($page) {
	$cacheName = "page-admins-".$page['page_id'];
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		$pageId = $page['page_id'];
		$query = db()->query("SELECT * FROM `page_roles` WHERE page_role='1' AND role_page_id='{$pageId}'");
		if($query) {
			$results = fetch_all($query);
			$admins = array();
			foreach($results as $role) {
				$admins[] = $role['role_user_id'];
			}
			set_cacheForever($cacheName, $admins);
			return $admins;
		}
	}
	return array();
}

function update_page_details($fields, $pageId) {
	$sqlFields = "";
	foreach($fields as $key => $value) {
		$value = sanitizeText($value);
		$sqlFields .= (empty($sqlFields)) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
	}
	db()->query("UPDATE `pages` SET {$sqlFields} WHERE `page_id`='{$pageId}'");
	fire_hook("page.updated", $fields, array($pageId));
}

function page_add_category($val) {
	$expected = array(
		'title' => '',
		'desc' => ''
	);

	/**
	 * @var $title
	 * @var $desc
	 */
	extract(array_merge($expected, $val));
	$titleSlug = "page_category_".md5(time().serialize($val)).'_title';
	$descriptionSlug = "page_category_".md5(time().serialize($val))."_description";

	foreach($title as $langId => $t) {
		add_language_phrase($titleSlug, $t, $langId, 'page');
	}
	foreach($desc as $langId => $t) {
		add_language_phrase($descriptionSlug, $t, $langId, 'page');
	}

	$time = time();
	$query = db()->query("INSERT INTO `page_categories`(
            `category_title`,`category_desc`,`category_created_at`) VALUES(
            '{$titleSlug}','{$descriptionSlug}','{$time}'
            )
        ");

	return true;
}

function get_page_categories() {
	$query = db()->query("SELECT * FROM `page_categories` ORDER BY `category_order` ASC");
	return fetch_all($query);
}

function get_page_category($id) {
	$query = db()->query("SELECT * FROM `page_categories` WHERE `category_id`='{$id}'");
	if($query) return $query->fetch_assoc();
}

function get_page_category_title($id) {
	$category = get_page_category($id);
	if($category) return lang($category['category_title']);
	return false;
}

function save_page_category($val, $category) {
	$expected = array(
		'title' => '',
		'desc' => ''
	);

	/**
	 * @var $title
	 * @var $desc
	 */
	extract(array_merge($expected, $val));
	$titleSlug = $category['category_title'];
	$descSlug = $category['category_desc'];
	foreach($title as $langId => $t) {
		(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'page') : add_language_phrase($titleSlug, $t, $langId, 'page');

	}

	foreach($desc as $langId => $t) {
		(phrase_exists($langId, $descSlug)) ? update_language_phrase($descSlug, $t, $langId, 'page') : add_language_phrase($descSlug, $t, $langId, 'page');

	}
}

function delete_page_category($id, $category) {
	delete_all_language_phrase($category['category_title']);
	delete_all_language_phrase($category['category_desc']);
	db()->query("DELETE FROM `page_categories` WHERE `category_id`='{$id}'");

	return true;
}

function update_page_category_order($id, $order) {
	db()->query("UPDATE `page_categories` SET `category_order`='{$order}' WHERE  `category_id`='{$id}'");
}

function get_invite_friends($term = null, $limit = 20, $offset = 0, $page_id = null) {
	$friends = get_friends();
	$friends[] = 0;
	$invited_friends = isset($page_id) ? fetch_all(db()->query("SELECT user_id FROM page_invites WHERE page_id = '{$page_id}' AND user_id IN '".implode(',', $friends)."'")) : array();
	$not_invited_friends = array_diff($friends, $invited_friends);
	$not_invited_friends = implode(',', $not_invited_friends);
	$sql = "SELECT id,first_name,last_name,avatar,username FROM users WHERE id IN ({$not_invited_friends})";
	if($term) $sql .= "  AND (first_name LIKE '%{$term}%' OR last_name LIKE '%{$term}%' OR username LIKE '%{$term}%' OR email_address LIKE '%{$term}%')";
	$sql .= " LIMIT {$offset},{$limit}";

	$query = db()->query($sql);
	return fetch_all($query);
}

function count_total_pages() {
	$q = db()->query("SELECT * FROM pages");
	return $q->num_rows;
}

function count_pages_in_month($n, $year) {
	$q = db()->query("SELECT * FROM pages WHERE YEAR(timestamp)={$year} AND MONTH(timestamp)={$n}");
	return $q->num_rows;
}

function delete_page($id, $page = null) {
	$page = ($page) ? $page : find_page($id);
	if(!is_page_admin($page) and !is_admin()) return false;
	//delete the posts
	delete_posts('page', $id);
	if(plugin_loaded('photo')) delete_photos('page', $id);
	if(plugin_loaded('like')) delete_likes('page', $id);

	//delete page covers
	if($page['page_cover']) delete_file(path($page['page_cover']));
	if($page['page_cover_resized']) delete_file(path($page['page_cover_resized']));

	db()->query("DELETE FROM page_details WHERE id='{$id}'");
	db()->query("DELETE FROM pages WHERE page_id='{$id}'");
	fire_hook("page.deleted", $id);
	return true;
}

function has_page_invited($page, $user) {
	$userid = get_userid();
	$query = db()->query("SELECT page_id FROM page_invites WHERE page_id='{$page}' AND inviter_id='{$userid}' AND user_id='{$user}'");
	if($query->num_rows) return true;
	return false;
}

function add_page_invite($page, $user) {
	$userid = get_userid();
	return db()->query("INSERT INTO page_invites (page_id,inviter_id,user_id)VALUES('{$page}','{$userid}','{$user}')");

}

function get_page_list() {
	$query = db()->query("SELECT * FROM pages");
	return fetch_all($query);
}

function get_joined_pages($userid = null) {
	$userid = ($userid) ? $userid : get_userid();
	$cacheName = 'page-joined-'.$userid;
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		$q = db()->query("SELECT page_id FROM page_invites WHERE user_id='{$userid}'");
		$a = array();
		while($fetch = $q->fetch_assoc()) {
			$a[] = $fetch['page_id'];
		}
		set_cacheForever($cacheName, $a);
		return $a;
	}
}

function more_social_update_page ($id, $val){
    $sqlFields = "";
    foreach($val as $key => $value) {
        if(!in_array($key, array('category', 'name', 'description'))) {
            if($key === 'page_url') {
                $value = unique_slugger($value, null, $id);
            }
            $value = sanitizeText($value);
            $key = mysqli_real_escape_string(db(), $key);
            $sqlFields .= empty($sqlFields) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
        }
    }
    $sql = db()->query("UPDATE pages SET {$sqlFields} WHERE page_id = '{$id}'");
    //exit(db()->error);
    if ($sql) {
        return true;
    }
    return false;
}

function social_count_page_posts($page){
    $sql = "SELECT * FROM feeds WHERE entity_type ='page' AND entity_id ='{$page['page_id']}'";
    $query = db()->query($sql);
    $result = fetch_all($query);
    return $total = count($result);
}

//custom likes
function count_page_likes($type, $typeId,$typ, $likeType = 1) {
	$likes = get_page_count_likes($type, $typeId, $likeType, $typ);
	return (count($likes)) ? count($likes) : 0;
}
function get_page_count_likes($type, $typeId, $likeType = 1,$typ) {
	$cacheName = "likes-".$type.'-'.$typeId.'-'.$likeType;
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
		if($typ == 'year') {
			$day = Date('Y');
			$sql = "SELECT `user_id` FROM `likes` WHERE `type`='{$type}' AND `type_id`='{$typeId}' AND FROM_UNIXTIME(time, '%Y')='{$day}' ";
			
		}elseif($typ == 'month') {
			$day = Date('m');
			$sql = "SELECT `user_id` FROM `likes` WHERE `type`='{$type}' AND `type_id`='{$typeId}' AND FROM_UNIXTIME(time, '%m')='{$day}' ";
		}elseif($typ == 'week') {
			$day = Date('Y:m:d');
			$sql = "SELECT `user_id` FROM `likes` WHERE `type`='{$type}' AND `type_id`='{$typeId}' AND FROM_UNIXTIME(time, '%Y:%m:%d') > DATE_SUB(NOW(), INTERVAL 1 WEEK) ";
		}elseif($typ == 'today') {
			$day = Date('Y:m:d');
			$sql = "SELECT `user_id` FROM `likes` WHERE `type`='{$type}' AND `type_id`='{$typeId}' AND FROM_UNIXTIME(time, '%Y:%m:%d')='{$day}' ";
		}
		
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

function get_custom_page_likes($type, $typeId, $action,$typ) {
	if($typ == 'year') {
		$day = Date('Y');
		$query = db()->query("SELECT username,first_name,last_name,avatar FROM likes INNER JOIN `users` on likes.user_id=users.id WHERE type='{$type}' and type_id='{$typeId}' AND like_type='{$action}' AND FROM_UNIXTIME(time, '%Y')='{$day}'");	
	}elseif($typ == 'month') {
		$day = Date('m');
		$query = db()->query("SELECT username,first_name,last_name,avatar FROM likes INNER JOIN `users` on likes.user_id=users.id WHERE type='{$type}' and type_id='{$typeId}' AND like_type='{$action}' AND FROM_UNIXTIME(time, '%m')='{$day}'");
	}elseif($typ == 'week') {
		$query = db()->query("SELECT username,first_name,last_name,avatar FROM likes INNER JOIN `users` on likes.user_id=users.id WHERE type='{$type}' and type_id='{$typeId}' AND like_type='{$action}' AND FROM_UNIXTIME(time, '%Y:%m:%d') > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
	}elseif($typ == 'today') {
		$day = Date('Y:m:d');
		$query = db()->query("SELECT username,first_name,last_name,avatar FROM likes INNER JOIN `users` on likes.user_id=users.id WHERE type='{$type}' and type_id='{$typeId}' AND like_type='{$action}' AND FROM_UNIXTIME(time, '%Y:%m:%d')='{$day}'");
	}

	return fetch_all($query);
}