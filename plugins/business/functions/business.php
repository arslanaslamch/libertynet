<?php
// return all business categories
function business_get_categories() {
	$db = db();
	$categories = $db->query("SELECT * FROM business_category ORDER BY id");
	return fetch_all($categories);
}

function find_cat_name($id) {
	$db = db();
	$categories = $db->query("SELECT * FROM business_category WHERE id=".$id);
	$cat = $categories->fetch_assoc();
	return $cat['category'];
}

//plan

function business_get_plans() {
	$db = db();
	$categories = $db->query("SELECT * FROM business_plans ORDER BY id");
	return fetch_all($categories);
}

// country
function business_get_country() {
	$db = db();
	$categories = $db->query("SELECT * FROM countries ORDER BY id");
	return fetch_all($categories);
}

function find_country_name($id) {
	$db = db();
	$country = $db->query("SELECT * FROM countries WHERE id=".$id);
	$cat = $country->fetch_assoc();
	return $cat['country_name'];
}

// return all business type
function business_get_type() {
	$db = db();
	$type = $db->query("SELECT * FROM business_type ORDER BY id");
	return fetch_all($type);
}

// check if business category exist
function business_is_category_exist($category_id) {
	$db = db();
	if($db->query("SELECT id FROM business_category WHERE id = ".$category_id)->num_rows == 0) {
		return false;
	} else {
		return true;
	}
}

function business_get_claimed_business($category, $limit = 20) {
	$db = db();
	$query = "SELECT DISTINCT business.*, business_category.category AS category_title, users.username FROM business LEFT JOIN business_category ON business.category_id = business_category.id LEFT JOIN users ON business.user_id = users.id WHERE business.business_type_id = '".$category."' AND admin_app ='1'";
	$business = paginate($query, $limit);
	if($business) {
		return $business;
	}
}

function approve_claimable_business($businessId){
    db()->query("UPDATE business SET admin_app = 1 WHERE id =".$businessId);

}
// return a selected category
function business_get_category($category_id) {
	$db = db();
	$category = $db->query("SELECT id, category FROM business_category WHERE id = ".$category_id);
	return $category->fetch_assoc();
}

function business_get_business($business_id, $type = 'm', $admin = false) {
	$db = db();
	$admin_sql = $admin ? '' : ' AND business.approved = 1 AND business.active = 1';
	$admin_sql = $type == 'm' ? ' AND business.active = 1' : $admin_sql;
	$where_sql = $admin_sql;
	$business = $db->query("SELECT DISTINCT business.timezone, business.id, business.business_number, business.fax, business.slug, business.business_name, business.description, business.time, business.category_id, business.tags, business.company_address, business.company_email, business.website, business.company_logo, business.user_id, business.views, business.last_viewed, business.price, business.featured, business.approved, business.paid, business.plan_id, business.expiry_date, business.active, business_category.category AS category_title, users.username FROM business LEFT JOIN business_category ON business.category_id = business_category.id LEFT JOIN users ON business.user_id = users.id WHERE business.id = ".$business_id.$where_sql);

	return $business->fetch_assoc();
}

function business_get_claim_business($business_id, $type = 'm', $admin = false) {
	$db = db();
	$admin_sql = $admin ? '' : ' AND business.approved = 0 AND business.active = 0';
	$admin_sql = $type == 'm' ? ' AND business.active = 0' : $admin_sql;
	$where_sql = $admin_sql;
	$business = $db->query("SELECT DISTINCT business.*, business.id, business.slug, business.business_name, business.description, business.time, business.category_id, business.tags, business.company_address, business.company_email, business.website, business.company_logo, business.user_id, business.views, business.last_viewed, business.price, business.featured, business.approved, business.paid, business.plan_id, business.expiry_date, business.active, business_category.category AS category_title, users.username FROM business LEFT JOIN business_category ON business.category_id = business_category.id LEFT JOIN users ON business.user_id = users.id WHERE business.id = ".$business_id);
	return $business->fetch_assoc();
}

function get_business_hour($business_id) {
	$hour = "SELECT * FROM business_hours WHERE business_id=".$business_id;
	$business = paginate($hour, $limit = 10);
	if($business) {
		return $business;
	}
}

function business_get_businesses($filter) {
	/**
	 * @var $category_id
	 * @var $keywords
	 * @var $type
	 * @var $page
	 * @var $limit
	 * @var $admin
	 * @var $mine
	 * @var $user_id
	 * @var $location
	 * @var $pending
	 * @var $featured
	 */
	$default = array('category_id' => null, 'keywords' => null, 'type' => null, 'page' => 1, 'limit' => null, 'mine' => null, 'user_id' => null, 'location' => null, 'pending' => null, 'featured' => null, 'admin' => false);
	extract(array_merge($default, $filter));
	$mine_sql = "";
    $where_sql = "";
	if ($mine){
        $where_sql = " AND business.user_id ='".get_userid()."'";
    } else{
        $admin_sql = $admin ? '' : ' AND business.approved = 1 AND business.active = 1 AND business.paid = 1 AND UNIX_TIMESTAMP(business.expiry_date) < NOW()';
        $category_id_sql = $category_id ? ' AND business.category_id = '.mysqli_real_escape_string(db(), $category_id) : '';
        $pending_sql = $pending ? ' AND business.approved = 0' : '';
        $featured_sql = $featured ? ' AND business.featured = 1' : '';
        $user_sql = is_numeric($user_id) ? ' AND business.user_id = '.$user_id : '';
        $location_sql = (isset($location)) ? " AND business.company_address LIKE '%".$location."%'" : '';
        $keywords_sql = (isset($keywords)) ? " AND (business.business_name LIKE '%".$keywords."%' OR business.tags LIKE '%".$keywords."%')" : '';
        $where_sql = $category_id_sql.$mine_sql.$user_sql.$location_sql.$featured_sql.$keywords_sql.$pending_sql.$admin_sql;
    }
	$query = "SELECT DISTINCT business.*, business.slug, business.business_name, business.description, business.price, business.time, business.views, business.user_id, business.category_id, business.tags, business.company_logo, business.company_address, business.company_email, business.website, business.company_phone, business.last_viewed, business.featured, business.approved, business.paid, business.plan_id, business.expiry_date, business.active, business_category.category AS category_title, users.username FROM business LEFT JOIN business_category ON business.category_id = business_category.id LEFT JOIN users ON business.user_id = users.id WHERE 1 = 1 {$where_sql} ORDER BY time DESC";
	//exit($query);
	$business = paginate($query, $limit);
	if($business) {
		return $business;
	}
}

function get_business_reviews($business_id, $limit = null) {
	$limit = $limit ? $limit : 20;
	$query = "SELECT * FROM business_reviews WHERE business_id =".trim($business_id);
	$review = paginate($query, $limit);
	if($review) {
		return $review;
	}
}

function get_business_details($Id) {
	$db = db()->query("SELECT * FROM business WHERE id = '{$Id}'");
	if($db) {
		return $db->fetch_assoc();
	}
}

function business_get_num_business_comments($business_id) {
	$db = db();
	return $db->query("SELECT COUNT(comment_id) FROM `comments` WHERE `type` = 'property' and `type_id` = '".$business_id."'")->fetch_row()[0];
}


function submit_contact_business($val) {
	$subject = sanitizeText($val['subject']);
	$message = sanitizeText($val['message']);
	$header = '<p>From: '.sanitizeText($val['name']).' | '.sanitizeText($val['email']).'</p>';
	$message = html_purifier_purify($header.$message);
	mailer()->setAddress(config('email-from-address'))->setSubject($subject)->setMessage($message)->send();
	return true;
}


function business_remove_get_var($url = null, $var) {
	$url = isset($url) ? $url : (http_build_query($_GET) == '' ? url_to_pager('businesses', array('appends' => '')) : url_to_pager('businesses', array('appends' => '')).'?'.http_build_query($_GET));
	$scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
	$host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
	$path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
	$query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
	$fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
	$variables = array();
	if(!is_null($query)) {
		parse_str($query, $variables);
	}
	if(isset($variables[$var])) {
		unset($variables[$var]);
	}
	$s = empty($scheme) ? '' : '://';
	$q = empty($variables) ? '' : '?';
	$h = empty($fragment) ? '' : '#';
	return $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment;
}

function business_assign_get_var($url = null, $var, $val) {
	$url = isset($url) ? $url : (http_build_query($_GET) == '' ? url_to_pager('all-business', array('appends' => '')) : url_to_pager('all-business', array('appends' => '')).'?'.http_build_query($_GET));
	$scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
	$host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
	$path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
	$query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
	$fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
	$variables = array();
	if(!is_null($query)) {
		parse_str($query, $variables);
	}
	$variables[$var] = $val;
	$s = empty($scheme) ? '' : '://';
	$q = empty($variables) ? '' : '?';
	$h = empty($fragment) ? '' : '#';
	return $scheme.$s.$host.$path.$q.http_build_query($variables).$h.$fragment;
}


function get_user_email($category) {
	$db = db()->query("SELECT * FROM users WHERE id = '{$category}'");
	if($db->num_rows == 0) {
		$db = db()->query("SELECT email_address FROM users WHERE id = '{$category}'");
	}
	return $db->fetch_assoc();
}

// get username of a user
function get_username($id) {
	$db = db()->query("SELECT username FROM users WHERE id = '{$id}'");
	if($db->num_rows == 1) {
		return $db->num_rows;
	}
}

// get user id
function get_user_id($username) {
	$db = db()->query("SELECT id FROM users WHERE username = '{$username}'");
	$record = $db->fetch_assoc();
	return $record['id'];

}

function business_delete_business($business_id) {
	$db = db();
	$db->query("DELETE FROM business WHERE id = ".$business_id);
	$obsolete_images = db()->query("SELECT id, image FROM business_images WHERE business_id = ".$business_id);
	while($obsolete_image = $obsolete_images->fetch_assoc()) {
		$db->query("DELETE FROM property_images WHERE id = ".$obsolete_images['id']);
		delete_file(path($obsolete_images['image']));
	}
}

function business_get_business_images($business_id) {
	$db = db();
	$business_images = $db->query("SELECT id, image FROM business_images WHERE business_id = ".$business_id);
	return fetch_all($business_images);
}

function viewCount($business_id, $user_id) {
	$time = time();
	$db = db();
	$record = $db->query("SELECT * FROM business_views WHERE business_id='".$business_id."' AND user_id=".$user_id);
	$number = $record->num_rows;
	if($number == 0) {
		db()->query("INSERT INTO business_views (user_id, business_id, time) VALUES(
                                      '{$user_id}', '{$business_id}', '{$time}')");
		$businessInsert = db()->insert_id;
		if($businessInsert) {
			return $businessInsert;
		}
	}
}

function viewCounts($business) {
	$db = db();
	$record = $db->query("SELECT * FROM business_views WHERE business_id='".$business."'");
	return $record->num_rows;
}

function reviewcounts($business) {
	$db = db();
	$record = $db->query("SELECT * FROM business_reviews WHERE business_id='".$business."'");
	return $record->num_rows;
}

function add_review($val) {
	$user_id = get_userid();
	/**
	 * @var string $reviews
	 * @var $business_id
	 */
	$expected = array(
		'business_id' => '', 'review' => '');
	extract(array_merge($expected, $val));
	$time = time();

	db()->query("INSERT INTO business_reviews (user_id, business_id, comment, time) VALUES(
                                      '{$user_id}', '{$business_id}', '{$reviews}', '{$time}')");
	$businessInsert = db()->insert_id;
	if($businessInsert) {
		return $businessInsert;
	}

}

function membership_type($id) {
	$db = db()->query("SELECT membership_type FROM users WHERE id = '{$id}'");
	return $db->fetch_assoc();
}

function business_followers_business($business_id) {
	$user_id = get_userid();
	$db = db();
	$record = $db->query("SELECT * FROM business_member WHERE business_id='".$business_id."' AND user_id=".$user_id);
	$number = $record->num_rows;
	if($number == 0) {
		db()->query("INSERT INTO business_member (user_id, business_id) VALUES('{$user_id}', '{$business_id}')");
	} else {
		return true;
	}
}

function business_unfollower_business($business_id) {

	$delete = db()->query("DELETE FROM business_member WHERE business_id = ".$business_id." AND user_id=".get_userid());

}


function business_favorites_business($business_id) {
	$user_id = get_userid();
	$db = db();
	$record = $db->query("SELECT * FROM business_favourite WHERE business_id='".$business_id."' AND user_id=".$user_id);
	$number = $record->num_rows;
	if($number == 0) {
		db()->query("INSERT INTO business_favourite (user_id, business_id) VALUES(
                                      '{$user_id}', '{$business_id}')");
		$businessInsert = db()->insert_id;
		if($businessInsert) {
			return $businessInsert;
		}
	} else {
		return true;
	}
}

function business_unfavourites_business($business_id) {

	$delete = db()->query("DELETE FROM business_favourite WHERE business_id = ".$business_id." AND user_id = ".get_userid());

}

//delete member
function business_delete_member($business_id, $user_id) {
	return db()->query("DELETE FROM business_member WHERE business_id = ".$business_id." AND user_id = ".$user_id);
}

// admin role to member
function business_member_admin($member_id, $business_id) {
	db()->query("UPDATE business_member SET role = 1 WHERE id=".$member_id." AND business_id =".$business_id);
}

function business_member_remove_admin($member_id, $business_id) {
	db()->query("UPDATE business_member SET role = 0 WHERE id=".$member_id." AND business_id =".$business_id);
}

function rowscount($table, $id) {
	$db = db();
	$record = $db->query("SELECT * FROM $table WHERE business_id=".$id);
	$number = $record->num_rows;
	return $number;
}

function claimed_file($val) {
	$business_id = $val['business_id'];
	$user_id = get_userid();
	$db = db();
	$db->query("UPDATE business SET claimed = 1, claimed_user_id ='".$user_id."' WHERE id=".$business_id);
	return true;

}

function approve_claimed_file($business_id, $user_id) {
	$db = db();
	$db->query("UPDATE business SET claimed = 0, claimed_user_id = 0, business_type_id = 1, user_id = '{$user_id}' WHERE id=".$business_id);
}

function disapprove_claimed_file($business_id, $user_id) {
	$db = db();
	$db->query("UPDATE business SET claimed = 0, claimed_user_id = 0, business_type_id = 2, approved = 0, paid = 0, active = 0,  WHERE id=".$business_id);

}

function check_business_followers($business_id) {
	$user_id = get_userid();
	$db = db();
	$record = $db->query("SELECT * FROM business_member WHERE business_id='".$business_id."' AND user_id=".$user_id);
	return $number = $record->num_rows;
}


function check_business_favourite($business_id) {
	$user_id = get_userid();
	$db = db();
	$record = $db->query("SELECT * FROM business_favourite WHERE business_id='".$business_id."' AND user_id=".$user_id);
	return $number = $record->num_rows;
}

function business_members_get($business_id, $limit = null) {
	$limit = $limit ? $limit : 20;
	$record = "SELECT * FROM business_member WHERE business_id=".$business_id;
	$members = paginate($record, $limit);
	if($members) {
		return $members;
	}
}

function business_update($id, $business) {
    $data = array();
    foreach ($business as $key => $value) {
        $data[] = "`".$key."` = '".$value."'";
    }
    $db = db();
    $sql = "UPDATE `business` SET ".implode(', ', $data)." WHERE `id` = ".$id;
    $query = $db->query($sql);
    if ($query) {
        return true;
    } else {
        return false;
    }
}

function pending_business($limit = null) {
	$limit = $limit ? $limit : 20;
    $query = "SELECT business.*, business_category.category AS category_title FROM business LEFT JOIN business_category ON business.category_id = business_category.id WHERE business.approved = 0 AND business.active = 0 AND business.business_type_id = 1 AND business.paid = 0";
    $business = paginate($query, $limit);
    if($business) {
        return $business;
    }
}

function active_business($limit = null) {
	$limit = $limit ? $limit : 20;
    $query = "SELECT business.*, business_category.category AS category_title FROM business LEFT JOIN business_category ON business.category_id = business_category.id WHERE business.approved = 1 AND business.active = 1 AND business.business_type_id = 1 AND business.paid = 1";
    $business = paginate($query, $limit);
    if($business) {
        return $business;
    }
}

function claimable_business($limit = null) {
	$limit = $limit ? $limit : 20;
    $query = "SELECT business.*, business_category.category AS category_title FROM business LEFT JOIN business_category ON business.category_id = business_category.id WHERE business.approved = 0 AND business.active = 0 AND business.business_type_id = 2 AND business.paid = 0 AND business.admin_app = '0'";
    $business = paginate($query, $limit);
    if($business) {
        return $business;
    }
}

function claimed_business($limit = null) {
	$limit = $limit ? $limit : 20;
    $query = "SELECT business.*, business_category.category AS category_title FROM business LEFT JOIN business_category ON business.category_id = business_category.id WHERE business.approved = 0 AND business.active = 0 AND business.business_type_id = 2 AND business.paid = 0 AND business.claimed = 1 AND admin_app = 1";
    $business = paginate($query, $limit);
    if($business) {
        return $business;
    }

}

function favouriteList($limit = null) {
	$limit = $limit ? $limit : 20;
    $user_id = get_userid();
    $query = "SELECT business.*, business_category.category AS category_title FROM business LEFT JOIN business_favourite ON business_favourite.business_id = business.id LEFT JOIN business_category ON business.category_id = business_category.id WHERE business_favourite.user_id = ".$user_id;
    $business = paginate($query, $limit);
    if($business) {
        return $business;
    }
}


function followList($limit = null) {
	$limit = $limit ? $limit : 20;
    $user_id = get_userid();
    $query = "SELECT business.*, business_category.category AS category_title FROM business LEFT JOIN business_member ON business_member.business_id = business.id LEFT JOIN business_category ON business.category_id = business_category.id WHERE business_member.user_id = ".$user_id;
    $business = paginate($query, $limit);
    if($business) {
        return $business;
    }
}

function get_business_contact($field, $business_id) {
    $db = db()->query("SELECT $field FROM business WHERE id = '{$business_id}'");
    $contact = $db->fetch_assoc();
    return $contact[$field];

}

function check_admin_role($user_id) {
    $db = db()->query("SELECT role FROM business_member WHERE role = 1 AND user_id='{$user_id}'");
    return $db->num_rows;
}

function business_create_claimable($userId) {
    $member_type = membership_type($userId);
    $db = db()->query("SELECT * FROM business WHERE can_create = 1 AND user_id='{$userId}'");
    if(($db->num_rows > 0) || ($member_type['membership_type'] != 'free') || ($member_type['membership_type'] != '')) {
        return true;
    } else {
        return false;
    }
}

function business_execute_form($post_vars) {
    /** @var $title */

    $db = db();
    $type = isset($post_vars['type']) ? $post_vars['type'] : null;
    $errors = array();
    switch($type) {
        case 'add_category':
            $expected = array('title' => '');
            extract(array_merge($expected, $post_vars));
            $db->query("INSERT INTO business_category(category) VALUES('".$title."')");
        break;

        case 'edit_category':
            $expected = array('title' => '');
            extract(array_merge($expected, $post_vars));
            $db->query("UPDATE business_category SET category = '".$post_vars['title']."' WHERE id = ".$post_vars['category_id']);
        break;

        case 'delete_category':
            $new_category_id = $post_vars['new_category_id'] == 'NULL' ? $post_vars['category_id'] : $post_vars['new_category_id'];
            $db->query("DELETE FROM business_category WHERE id = ".$post_vars['category_id']);
            $db->query("UPDATE business SET category_id = ".$new_category_id." WHERE category_id = ".$post_vars['category_id']);
        break;


        case 'edit_business':
            $db->query("UPDATE business SET business_name = '".$post_vars['business_name']."', description = '".html_purifier_purify($post_vars['description'])."', category_id = ".$post_vars['category_id'].", tags = '".$post_vars['tag_list']."', company_address = '".$post_vars['address']."', company_email = '".$post_vars['email']."', website = '".$post_vars['link']."', company_logo = '".$post_vars['image_path']."', fax =  '".$post_vars['fax']."', business_number = '".$post_vars['phone']."', size =  '".$post_vars['size']."', company_phone =  '".$post_vars['phone']."', timezone =  '".$post_vars['timezone']."', price =  '".$post_vars['price']."', country =  '".$post_vars['country']."', business_type_id =  '".$post_vars['type_id']."', category_id =  '".$post_vars['category_id']."' WHERE id = ".$post_vars['business_id']);
            $always_open = '';
            $visiting_hours_dayofweek_id = isset($post_vars['visiting_hours_dayofweek_id']) ? $post_vars['visiting_hours_dayofweek_id'] : '';
            $counter = $post_vars['counter'];
            $visiting_hours_hour_starttime = isset($post_vars['visiting_hours_hour_starttime']) ? $post_vars['visiting_hours_hour_starttime'] : '';
            $visiting_hours_hour_endtime = isset($post_vars['visiting_hours_hour_endtime']) ? $post_vars['visiting_hours_hour_endtime'] : '';
            $business_id = $post_vars['business_id'];
            if(!$always_open && $visiting_hours_dayofweek_id) {
                $db->query("DELETE FROM business_hours WHERE business_id = ".$post_vars['business_id']);
                for($x = 0; $x < intval($counter); $x++) {
                    $week = $visiting_hours_dayofweek_id[$x];
                    $starttime = $visiting_hours_hour_starttime[$x];
                    $endtime = $visiting_hours_hour_endtime[$x];
                    db()->query("INSERT INTO business_hours (business_id, day, open_time, close_time) VALUES('{$business_id}','{$week}','{$starttime}', '{$endtime}')");
                }
            }
        break;

        case 'edit_business_admin':
            if(is_admin()) {
                $db->query("UPDATE business SET business_name = '".$post_vars['business_name']."', description = '".html_purifier_purify($post_vars['description'])."', category_id = ".$post_vars['category_id'].", tags = '".$post_vars['tag_list']."', company_address = '".$post_vars['address']."', company_email = '".$post_vars['email']."', website = '".$post_vars['link']."', company_logo = '".$post_vars['image_path']."', fax =  '".$post_vars['fax']."', business_number = '".$post_vars['phone']."', featured = '".$post_vars['featured']."', can_create = '".$post_vars['can_create']."', size =  '".$post_vars['size']."', company_phone =  '".$post_vars['phone']."', timezone =  '".$post_vars['timezone']."', price =  '".$post_vars['price']."', country =  '".$post_vars['country']."', business_type_id =  '".$post_vars['type_id']."', category_id =  '".$post_vars['category_id']."' WHERE id = ".$post_vars['business_id']);
                $always_open = '';
				if(isset($post_vars['visiting_hours_dayofweek_id'])){
					$visiting_hours_dayofweek_id = $post_vars['visiting_hours_dayofweek_id'];
					$counter = $post_vars['counter'];
					$visiting_hours_hour_starttime = $post_vars['visiting_hours_hour_starttime'];
					$visiting_hours_hour_endtime = $post_vars['visiting_hours_hour_endtime'];
					$business_id = $post_vars['business_id'];
					if(!$always_open && $visiting_hours_dayofweek_id) {
						$db->query("DELETE FROM business_hours WHERE business_id = ".$post_vars['business_id']);
						for($x = 0; $x < intval($counter); $x++) {
							$week = $visiting_hours_dayofweek_id[$x];
							$starttime = $visiting_hours_hour_starttime[$x];
							$endtime = $visiting_hours_hour_endtime[$x];
							db()->query("INSERT INTO business_hours (business_id, day, open_time, close_time) VALUES('{$business_id}','{$week}','{$starttime}', '{$endtime}')");
						}
					}
				}
            } else {
                return MyError::error404();
            }
        break;

        case 'delete_business':
            business_delete($post_vars['business_id']);
        break;

        case 'add_images':
            foreach($post_vars['images_paths'] as $image_path) {
                $db->query("INSERT INTO business_images (image, business_id) VALUES('".$image_path."', ".$post_vars['business_id'].")");
            }
        break;

        case 'delete_photo':
            $db->query("DELETE FROM business_images WHERE id = ".$post_vars['image_id']);
        break;

        default:
            return false;
        break;
    }
}


// plans


function add_business_plan($val) {
    /**
     * @var $title
     * @var $desc
     * @var $days
     * @var $featured
     * @var $price
     */
    $expected = array('title' => '', 'desc' => '', 'days' => '', 'featured' => '', 'price' => '');
    extract(array_merge($expected, $val));
    $db = db();
    $db->query("INSERT INTO business_plans (title, description, days, featured, price) VALUES('".$title."', '".$desc."', ".$days.", ".$featured.", ".$price.")");
    return true;
}

function save_business_plan($val, $plan) {
    /**
     * @var $title
     * @var $desc
     * @var $days
     * @var $featured
     * @var $price
     */
    $expected = array('title' => '', 'desc' => '', 'days' => '', 'featured' => '', 'price' => '');
    extract(array_merge($expected, $val));

    $id = $plan['id'];
    $db = db();
    $edit = $db->query("UPDATE business_plans SET title = '".$title."',  price = '".$price."', days = '".$days."', description = '".$desc."', featured = '".$featured."' WHERE id = ".$id);
    if($edit) {
        return true;
    }
}

function delete_business_plan($id) {
    db()->query("DELETE FROM business_plans WHERE id = ".$id);
}

function get_business_plans() {
    $query = db()->query("SELECT * FROM business_plans");
    return fetch_all($query);
}

function get_business_plan($id) {
    $query = db()->query("SELECT * FROM business_plans WHERE id = ".$id);
    return $query->fetch_assoc();
}


function business_activate($id) {
    $business = get_business_details($id);
    $plan = get_business_plan($business['plan_id']);
    $expiry_date = !$business['expiry_date'] && $business['approved'] ? date('Y-m-d H:i:s', (time() + ($plan['days'] * 86400))) : $business['expiry_date'];
    db()->query("UPDATE business SET paid = 1, active = 1, approved = 1, expiry_date = '".$expiry_date."' WHERE id = ".$id);
    return true;
}

function business_deactivate($id) {
    $expiry_date = time();
    db()->query("UPDATE business SET paid = 0, active = 0, approved = 0, featured = 0, expiry_date = '".$expiry_date."' WHERE id = ".$id);
    return true;
}


function business_num_businesses() {
    $db = db();
    $num_business = $db->query("SELECT COUNT(id) FROM business");
    if($db->error) {
        return 0;
    } else {
        return $num_business->fetch_row()[0];
    }
}

function business_num_pending_businesses() {
    $db = db();
    $num_pending_business = $db->query("SELECT COUNT(id) FROM business WHERE approved = 0");
    if($db->error) {
        return 0;
    } else {
        return $num_pending_business->fetch_row()[0];
    }
}


function admin_add_business($val) {
    /**
     * @var string $business_name
     * @var string $type_id
     * @var string $category_id
     * @var string $phone
     * @var string $description
     * @var $address
     * @var $country
     * @var $fax
     * @var $email
     * @var $link
     * @var $size
     * @var $tag_list
     * @var $always_open
     * @var $timezone
     * @var $plan
     */
    $expected = array(
        'user_name' => '',
        'type_id' => '',
        'category_id' => '',
        'business_name' => '',
        'description' => '',
        'address' => '',
        'country' => '',
        'phone' => '',
        'fax' => '',
        'email' => '',
        'plan' => '',
        'link' => '',
        'size' => '',
        'tag_list' => '',
        'always_open' => '',
        'timezone' => '',);
    extract(array_merge($expected, $val));
    $time = time();
    $user_id = get_user_id(trim($val['user_name']));
    /** @var string $phone */
    $file_path = $val['image_path'];
    $slug = business_unique_slugger($business_name, 'business');
    db()->query("INSERT INTO business (user_id, business_name, business_number, business_type_id, category_id, description, company_logo, company_address, company_email, company_phone, fax, website, time, rating, claimed, claimed_user_id, views, featured, approved, plan_id, paid, expiry_date, active, business_hr, timezone, last_viewed,size,slug) VALUES('{$user_id}', '{$business_name}', '{$phone}', '{$type_id}', '{$category_id}', '{$description}', '{$file_path}', '{$address}', '{$email}', '{$phone}', '{$fax}', '{$link}', '{$time}', '0', '0','0', '0','0', '0', '{$plan}', '0', '0', '0', '{$always_open}', '{$timezone}', '{$size}', '0', '{$slug}')");
    $business_id = db()->insert_id;
    if($business_id) {
        foreach($val['images_paths'] as $image_path) {
            db()->query("INSERT INTO business_images (image, business_id) VALUES('".$image_path."', ".$business_id.")");
        }
        fire_hook('business.create', null, array($type = 'business.create', $type_id = db()->insert_id, $text = $val['business_name']));

        return $business_id;
    }
}

function business_delete($id) {
    $db = db();
    $db->query("DELETE FROM business WHERE id = ".$id);
    $sql = "SELECT id FROM business_images WHERE business_id = ".$id;
    $query = $db->query($sql);
    while($row = $query->fetch_row()) {
        business_delete_image($row[0]);
    }
    $db->query("DELETE FROM business_favourite WHERE business_id = ".$id);
    $db->query("DELETE FROM business_member WHERE business_id = ".$id);
    $db->query("DELETE FROM business_reviews WHERE business_id = ".$id);
    $db->query("DELETE FROM business_views WHERE business_id = ".$id);
    $db->query("DELETE FROM business_rating WHERE business_id = ".$id);
    $db->query("DELETE FROM business_hours WHERE business_id = ".$id);
    return;
}

function business_delete_image($id) {
    $db = db();
    $sql = "SELECT image FROM business_images WHERE id = ".$id;
    $query = $db->query($sql);
    $row = $query->fetch_row();
    $image = $row[0];
    delete_file(path($image));
}

function add_business($val) {
    /**
     * @var string $business_name
     * @var string $type_id
     * @var string $category_id
     * @var string $phone
     * @var string $description
     * @var $address
     * @var $country
     * @var $fax
     * @var $email
     * @var $link
     * @var $size
     * @var $tag_list
     * @var $always_open
     * @var $timezone
     * @var $id
     * @var $visiting_hours_dayofweek_id
     * @var $visiting_hours_hour_starttime
     * @var $visiting_hours_hour_endtime
     * @var $price
     * @var $counter
     */
    $expected = array(
        'type_id' => '',
        'category_id' => '',
        'business_name' => '',
        'description' => '',
        'address' => '',
        'country' => '',
        'phone' => '',
        'fax' => '',
        'email' => '',
        'id' => '',
        'link' => '',
        'counter' => '',
        'visiting_hours_dayofweek_id' => '',
        'visiting_hours_hour_starttime' => '',
        'visiting_hours_hour_endtime' => '',
        'price' => '',
        'size' => '',
        'tag_list' => '',
        'always_open' => '',
        'timezone' => '',);
    extract(array_merge($expected, $val));
    $time = date('Y-m-d H:i:s');
    $user_id = get_userid();
    /** @var string $phone */
    $file_path = $val['image_path'];
    $slug = business_unique_slugger($business_name, 'business');
    $slug = $slug.rand(1, 1000);
    db()->query("INSERT INTO business (user_id, business_name, business_number, business_type_id, category_id, description,
                                       company_logo, company_address, company_email, company_phone, fax, website, time, rating,
                                       claimed, claimed_user_id, views, featured, approved, plan_id, paid, expiry_date, active,
                                       business_hr, timezone, last_viewed,size,slug, price,country) VALUES(
                                      '{$user_id}', '{$business_name}', '{$phone}', '{$type_id}', '{$category_id}', 
                                      '{$description}', '{$file_path}', '{$address}', '{$email}', 
                                      '{$phone}', '{$fax}', '{$link}', '{$time}', '0',
                                      '0','0', '0','0', '0', '{$id}', '0', '0', '0', '{$always_open}', '{$timezone}', '0', '{$size}', '{$slug}','{$price}','{$country}')");
    $business_id = db()->insert_id;
    if($business_id) {
		if(isset($val['images_paths'])) {
			foreach($val['images_paths'] as $image_path) {
				db()->query("INSERT INTO business_images (image, business_id) VALUES('".$image_path."', ".$business_id.")");
			}
		}
        if(!$always_open && $visiting_hours_dayofweek_id) {
            for($x = 0; $x < intval($counter); $x++) {
                $week = $visiting_hours_dayofweek_id[$x];
                $starttime = $visiting_hours_hour_starttime[$x];
                $endtime = $visiting_hours_hour_endtime[$x];
                db()->query("INSERT INTO business_hours (business_id, day, open_time, close_time) VALUES('{$business_id}','{$week}','{$starttime}', '{$endtime}')");
            }
        }
        return $business_id;
    }
}


function business_output_text($content) {
    $tContent = $content;
    $original = $content;
    $content = format_output_text($content);
    if(is_rtl($content)) {
        $content = "<span style='direction: rtl;text-align: right;display: block'> {$content}</span>";

    }
    //too much text solution
    $id = md5($tContent.time());
    $result = "<span id=' {$id}' style='font-weight: normal !important'>";
    if(mb_strlen($tContent) > 50) {
        $result .= "<span class='text-full' style='display: none;font-weight: normal'> {$content}</span>";
        $tContent = format_output_text(str_limit($tContent, 50));
        if(is_rtl($tContent)) $tContent = "<span style='direction: rtl;text-align: right;display:block'> {$tContent}</span>";
        $result .= "<span style='font-weight: normal !important'>".$tContent."</span>";
        $result .= ' <a href="" onclick=\'return read_more(this, "'.$id.'")\'>'.lang('read-more').'</a>';
    } else {
        $result .= $content;
    }

    $result .= "</span>";
    if(config('enable-text-translation', false) and !empty($original) and !isEnglish($original)) {
        $trans = lang('see-translation');
        $result .= "<div id=' {$id}-translation' class='non-translated'><input name='text' type='hidden' value=' {$original}'/><button data-id=' {$id}' onclick='return translateText(this)'> {$trans}</button></div>";
    }


    return $result;
}

function business_slugger($str) {
    return trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $str)), '-');
}

function business_unique_slugger($title, $type) {
    $db = db();
    $table = $type == 'category' ? 'business_category' : 'business';
    $id = $db->query("SELECT id FROM ".$table." WHERE business_name = '".$title."'");
    $id = ($id->num_rows == 0) ? 0 : $id->fetch_row()[0];
    $slug = business_slugger(lang($title));
    if($db->query("SELECT COUNT(id) FROM ".$table." WHERE slug = '".$slug."' AND id != ".$id)->fetch_row()[0] == 0) {
        return $slug;
    } else {
        $i = 0;
        while($db->query("SELECT COUNT(id) FROM ".$table." WHERE slug = '".$slug."-".$i."' AND id != ".$id)->fetch_row()[0] > 0) {
            $i++;
        }
        return $slug.'-'.$i;
    }
}

function business_update_slugs($type) {
    $db = db();
    $table = $type == 'category' ? 'business_categories' : 'properties';
    $titles = $db->query("SELECT id, slug, title FROM ".$table);
    while($row_titles = $titles->fetch_assoc()) {
        $db->query("UPDATE ".$table." SET slug = '".business_unique_slugger($row_titles['title'], $type)."' WHERE id = '".$row_titles['id']."'");
    }
}

function business_get_slug_id($slug, $type) {
    $db = db();
    $table = $type == 'category' ? 'business_category' : 'business';
    return $db->query("SELECT id FROM ".$table." WHERE slug = '".$slug."'")->fetch_row()[0];
}

function business_get_slug($id, $type) {
    $db = db();
    $table = $type == 'category' ? 'business_category' : 'business';
    return $db->query("SELECT slug FROM ".$table." WHERE id = ".$id)->fetch_row()[0];
}

function business_get_business_slug_link($url) {
    $scheme = (isset(parse_url($url)['scheme'])) ? parse_url($url)['scheme'] : null;
    $host = (isset(parse_url($url)['host'])) ? parse_url($url)['host'] : null;
    $path = (isset(parse_url($url)['path']) && parse_url($url)['path'] != '/') ? parse_url($url)['path'] : null;
    $query = (isset(parse_url($url)['query'])) ? parse_url($url)['query'] : null;
    $fragment = (isset(parse_url($url)['fragment'])) ? parse_url($url)['fragment'] : null;
    $variables = array();
    if(!is_null($query)) {
        parse_str($query, $variables);
    }
    $category = null;
    $type = null;
    if(isset($variables['c'])) {
        $category = '/category/'.business_get_slug(($variables['c']), 'category');
        unset($variables['c']);
    }
    if(isset($variables['m'])) {
        if($variables['m'] == 1) {
            $type = '/my-businesses';
        }
        unset($variables['m']);
    }
    if(isset($variables['f'])) {
        if($variables['f'] == 1) {
            $type = '/featured';
        }
        unset($variables['f']);
    }
    if(isset($variables['u'])) {
        if(is_numeric($variables['u'])) {
            $type = '/'.$variables['u'];
        }
        unset($variables['u']);
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return $scheme.$s.$host.rtrim($path, '/').$category.$type.$q.http_build_query($variables).$h.$fragment;
}


function business_has_rated($business_id, $user_id = null) {
    $user_id = isset($user_id) ? $user_id : get_userid();
    $db = db();
    $result = $db->query("SELECT COUNT(id) FROM business_rating WHERE user_id = ".$user_id." AND business_id = ".$business_id);
    $check = $result->fetch_row();
    return $check[0] ? true : false;
}

function business_delete_rating($business_id, $user_id = null) {
    $user_id = isset($user_id) ? $user_id : get_userid();
    $db = db();
    $db->query("DELETE FROM business_rating WHERE user_id = ".$user_id." AND business_id = ".$business_id);
    return true;
}

function add_business_rating($rate, $id) {
    $db = db();
    $user_id = get_userid();
    $rating = "star_".$rate;
    if(business_has_rated($id)) {
        business_delete_rating($id);
    }
    $query = $db->query("INSERT INTO business_rating (user_id,business_id,$rating) VALUES ('$user_id','$id','$rate')");
    fire_hook('business.rate', null, array($id, $rate, $user_id));
    return $query;
}

function check_business_rating($cid) {
    $db = db();
    $user_id = get_userid();
    $query = $db->query("SELECT * FROM business_rating WHERE business_id='$cid' AND user_id='$user_id'");
    return $query->num_rows;
}

function get_business_rating($id) {
    $db = db();
    $star_1 = $db->query("SELECT star_1 ,SUM(star_1) AS star_1 FROM business_rating WHERE business_id='$id'");
    $star_1 = $star_1->fetch_assoc();
    $star1 = $star_1['star_1'] * 1;
    $star_2 = $db->query("SELECT star_2 ,SUM(star_2) AS star_2 FROM business_rating WHERE business_id='$id'");
    $star_2 = $star_2->fetch_assoc();
    $star2 = $star_2['star_2'] * 2;
    $star_3 = $db->query("SELECT star_3 ,SUM(star_3) AS star_3 FROM business_rating WHERE business_id='$id'");
    $star_3 = $star_3->fetch_assoc();
    $star3 = $star_3['star_3'] * 3;
    $star_4 = $db->query("SELECT star_4 ,SUM(star_4) AS star_4 FROM business_rating WHERE business_id='$id'");
    $star_4 = $star_4->fetch_assoc();
    $star4 = $star_4['star_4'] * 4;
    $star_5 = $db->query("SELECT star_5 ,SUM(star_5) AS star_5 FROM business_rating WHERE business_id='$id'");
    $star_5 = $star_5->fetch_assoc();
    $star5 = $star_5['star_5'] * 5;
    $star = $star1 + $star2 + $star3 + $star4 + $star5;
    $total = $star_1['star_1'] + $star_2['star_2'] + $star_3['star_3'] + $star_4['star_4'] + $star_5['star_5'];
    if($total == 0) {
        $total = 1;
    }
    $total_rate = $star / $total;
    $db->query("UPDATE business SET rating='$total_rate' WHERE id='$id'");
    return $total_rate;
}

function get_top_rated_business() {
    $db = db();
    return paginate("SELECT * FROM business WHERE status='active' ORDER  BY rating DESC ", 10);
}

function business_my_rating($business_id, $userid = null) {
    $db = db();
    $user_id = isset($userid) ? $userid : get_userid();
    $query = $db->query("SELECT * FROM business_rating WHERE business_id = ".$business_id." AND user_id = ".$user_id);
    $rates = $query->fetch_assoc();
    $rate = 0;
    if($rates) {
        $star_fields = array('star_1', 'star_2', 'star_3', 'star_4', 'star_5');
        foreach($rates as $field => $rate) {
            if(in_array($field, $star_fields) && $rate > 0) {
                $rate = $rate;
                break;
            }
        }
    } else {
        $rate = 0;
    }
    return $rate;
}

function ratingCounts($business) {
    $db = db();
    $record = $db->query("SELECT * FROM business_rating WHERE business_id='".$business."'");
    return $record->num_rows;
}