<?php
function add_quiz($val) {
	$expected = array(
		'title' => '',
		'content' => '',
		'tags' => '',
		'category' => '',
		'entity' => '',
		'privacy' => '',
		'pass_mark' => '',
	);
	/**
	 * @var $title
	 * @var $pass_mark
	 * @var $content
	 * @var $status
	 * @var $category
	 * @var $entity
	 * @var $featured
	 * @var $privacy
	 *
	 */
	extract(array_merge($expected, $val));

    $db = db();
    $image = '';
	$file = input_file('image');
	if($file) {
		$uploader = new Uploader($file);
		if($uploader->passed()) {
			$uploader->setPath('quizes/preview/');
			$image = $uploader->resize(700, 500)->result();
		}
	}

	$time = time();
	$userid = get_userid();
	$slug = unique_slugger($title);
	$content = html_purifier_purify($content);
	$content = mysqli_real_escape_string($db, $content);
	$entity = explode('-', $entity);
	if(count($entity) == 2) {
		$entity_type = $entity[0];
		$entity_id = $entity[1];
	}
	if(!isset($entity_id) || !isset($entity_type)) {
		return false;
	}
	
	$db->query("INSERT INTO quizes (user_id, entity_type, entity_id, title, content, slug, image, status, tags, update_time, time, category_id, privacy,pass_mark) VALUES ('".$userid."', '".$entity_type."', '".$entity_id."', '".$title."', '".$content."', '".$slug."', '".$image."', '".$status."', '".$tags."', '".$time."', '".$time."', '".$category."', '".$privacy."','".$pass_mark."')");
	$quizId = $db->insert_id;
	$quiz = get_quiz($quizId);
	fire_hook("quiz.added", null, array($quizId, $quiz));
	return $quizId;
}
function is_quiz_owner($quiz) {
	if(!is_loggedIn()) return false;
	if($quiz['user_id'] == get_userid()) return true;
	return false;
}
function get_quizes($type, $category = null, $term = null, $user_id = null, $limit = null, $filter = 'all', $quiz = null, $entity_type = 'user', $entity_id = null) {
	$limit = $limit ? $limit : 10;
	$sql = "SELECT * FROM quizes ";
	$user_id = $user_id ? $user_id : get_userid();
	$sql = fire_hook("use.different.quizes.query", $sql, array());
	if($type == 'mine') {
		$sql .= " WHERE user_id = '".$user_id."' ";
		$sql .= $filter == 'featured' ? " AND featured = '1' " : '';
	} elseif($type == 'related') {
		$title = $quiz['title'];
		$explode = explode(' ', $title);
		$w = '';
		foreach($explode as $t) {
			$w .= $w ? " OR  (title LIKE '%".$t."%' OR content LIKE '%".$t."') " : "  (title LIKE '%".$t."%' OR content LIKE '%".$t."')";
		}
		$quiz_id = $quiz['id'];
		$privacy_sql = fire_hook('privacy.sql', ' ');
		$sql .= " WHERE (".$w.") AND status = '1' AND id != '".$quiz_id."' AND (".$privacy_sql.") ";
		$sql = fire_hook("more.quizes.query.filter", $sql, array($entity_type, $entity_id));
	} else {
		if($term && !$category) {
			$sql .= " WHERE status = 1 AND (title LIKE '%".$term."%' OR content LIKE '%".$term."')";
		} elseif($term && $category != 'all') {
            $subCategories = get_quiz_parent_categories($category);
            if(!empty($subCategories)) {
                $subIds = array();
                foreach($subCategories as $cat) {
                    $subIds[] = $cat['id'];
                }
                $subIds = implode(',', $subIds);
                $sql .= " WHERE status = 1 AND (category_id = '".$category."' OR category_id IN ({$subIds})) AND (title LIKE '%".$term."%' OR content LIKE '%".$term."')";
            } else {
                $sql .= " WHERE status = 1 AND category_id = '".$category."' AND (title LIKE '%".$term."%' OR content LIKE '%".$term."')";
            }
		} elseif($term && $category == 'all') {
			$sql .= " WHERE status = 1 AND (title LIKE '%".$term."%' OR content LIKE '%".$term."')";
		} elseif($category && $category != 'all') {
			$sql .= " WHERE status = 1 AND category_id = '".$category."'";
		} else {
			$sql .= " WHERE status = '1'";
		}
		$sql .= $filter == 'featured' ? " AND featured = '1' " : '';
		$privacy_sql = fire_hook('privacy.sql', ' ');
		$sql .= " AND (".$privacy_sql.") ";
		if($entity_type && $entity_id) {
			$entity_sql = "entity_type = '".$entity_type."' AND entity_id = ".$entity_id;
			$sql .= " AND (".$entity_sql.") ";
		}
		$sql = fire_hook("more.quizes.query.filter", $sql, array($entity_type, $entity_id));
	}
	$sql = fire_hook('users.category.filter', $sql, array($sql));
	if($filter == 'top') {
		$sql .= " ORDER BY views desc";
	} else {
		$sql .= " ORDER BY time desc";
	}
	return paginate($sql, $limit);
}
function get_quiz($id) {
	$db = db();
	$query = $db->query("SELECT * FROM quizes WHERE ".(is_numeric($id) ? "id = ".$id : "slug = '".$id."'"));
	$quiz = $query->fetch_assoc();
	return $quiz ? arrange_quiz($quiz) : $quiz;
}
function delete_quiz($id) {
	$quiz = get_quiz($id);
	if($quiz['image']) delete_file(path($quiz['image']));
	$feed_typeId = "quiz-added";
	db()->query("DELETE FROM feeds WHERE `type_id` = '".$feed_typeId."' AND `type_data` ='".$id."'");
	db()->query("DELETE FROM quiz_questions WHERE `quiz_id` ='".$id."'");
	db()->query("DELETE FROM quiz_answers WHERE `quiz_id` ='".$id."'");
	db()->query("DELETE FROM quiz_status WHERE `quiz_id` ='".$id."'");
	return db()->query("DELETE FROM quizes WHERE id='".$id."'");
}
function get_quiz_categories() {
	$query = db()->query("SELECT * FROM `quiz_categories` WHERE parent_id ='0' ORDER BY `category_order` ASC");
	return fetch_all($query);
}
function count_total_quizes() {
	$query = db()->query("SELECT * FROM quizes");
	return $query->num_rows;
}
function quiz_add_category($val) {
	$expected = array(
		'title' => '',
        'category' => ''
	);

	/**
	 * @var $title
	 * @var $desc
	 * @var $category
	 */
	extract(array_merge($expected, $val));
	$titleSlug = "quiz_category_".md5(time().serialize($val)).'_title';
	foreach($title as $langId => $t) {
		add_language_phrase($titleSlug, $t, $langId, 'quiz');
	}
	$order = db()->query('SELECT id FROM quiz_categories');
	$order = $order->num_rows;
	db()->query("INSERT INTO `quiz_categories`(`title`,`category_order`,`parent_id`) VALUES('".$titleSlug."','".$order."','".$category."')");
	return true;
}

function save_quiz_category($val, $category) {
	$expected = array(
		'title' => ''
	);

	/**
	 * @var $title
	 */
	extract(array_merge($expected, $val));
	$titleSlug = $category['title'];

	foreach($title as $langId => $t) {
		(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, ' quiz') : add_language_phrase($titleSlug, $t, $langId, 'quiz');
	}

	return true;
}

function get_quiz_parent_categories($id) {
    $db = db()->query("SELECT * FROM `quiz_categories` WHERE parent_id='{$id}' ORDER BY `category_order` ASC");
    $result = fetch_all($db);
    return $result;
}

function get_quiz_category($id) {
	$query = db()->query("SELECT * FROM `quiz_categories` WHERE `id`='".$id."'");
	return $query->fetch_assoc();
}

function arrange_quiz($quiz) {
	$category = get_quiz_category($quiz['category_id']);
	if($category) {
		$quiz['category'] = $category;
	}
	$quiz = fire_hook('quiz.arrange', $quiz);
	$quiz['publisher'] = get_quiz_publisher($quiz);
	return $quiz;
}

function get_quiz_publisher($quiz) {
	if($quiz['entity_type'] == 'user') {
		$user = find_user($quiz['entity_id']);
		$publisher = array(
			'id' => $user['username'],
			'name' => get_user_name($user),
			'avatar' => get_avatar(200, $user)
		);
	} else {
		$publisher = fire_hook('entity.data', array(false), array($quiz['entity_type'], $quiz['entity_id']));
	}
	return $publisher;
}

function delete_quiz_category($id, $category) {
	delete_all_language_phrase($category['title']);
	db()->query("DELETE FROM `quiz_categories` WHERE `id`='".$id."'");
	return true;
}

function update_quiz_category_order($id, $order) {
	db()->query("UPDATE `quiz_categories` SET `category_order`='".$order."' WHERE  `id`='".$id."'");
}
function save_quiz($val, $quiz, $admin = false) {
	$expected = array(
		'title' => '',
		'pass_mark' => '',
		'slug' => '',
		'content' => '',
		'tags' => '',
		'category' => '',
		'privacy' => '',
		'featured' => $quiz['featured']
	);
	/**
	 * @var $title
	 * @var $pass_mark
	 * @var $slug
	 * @var $content
	 * @var $tags
	 * @var $status
	 * @var $category
	 * @var $privacy
	 * @var $featured
	 */
	if(!$admin) $val['featured'] = $quiz['featured'];
	extract(array_merge($expected, $val));
	$image = $quiz['image'];
	$id = $quiz['id'];
	$slug = unique_slugger($title, 'quiz', $quiz['id']);
	$file = input_file('image');
	if($file) {
		$uploader = new Uploader($file);
		if($uploader->passed()) {
			$uploader->setPath('quizes/preview/');
			$image = $uploader->resize(700, 500)->result();
		}
	}

	$time = time();
    $db = db();
    $content = html_purifier_purify($content);
    $content = mysqli_real_escape_string($db, $content);
	$db->query("UPDATE quizes SET slug = '".$slug."', featured = '".$featured."', image = '".$image."', title = '".$title."', pass_mark = '".$pass_mark."', tags = '".$tags."', content = '".$content."', status = '".$status."', update_time = '".$time."', privacy = '".$privacy."', category_id = '".$category."' WHERE id = '".$id."'");
	$db->query("UPDATE quiz_status SET title = '".$title."' WHERE quiz_id = '".$id."'");
	$db->query("UPDATE quiz_questions SET quiz_title = '".$title."' WHERE quiz_id = '".$id."'");
	$db->query("UPDATE quiz_answers SET quiz_title = '".$title."' WHERE quiz_id = '".$id."'");
	return true;
}
//for quiz feed
function add_quiz_feed($val, $api = false) {
    $val = fire_hook('feed.add.val', $val, array($api));
    $defined = array(
        'to_user_id' => '',
        'entity_id' => '',
        'entity_type' => 'user',
        'type' => '',
        'type_id' => '',
        'type_data' => '',
        'content' => '',
        'privacy' => '',
        'media_content' => '',
        'tags' => array(),
        'userid' => get_userid(),
        'can_share' => 1,
		'status' => 0,
        'auto_post' => false,
        'enable_post' => '',
    );

    /**
     * @var $to_user_id
     * @var $type
     * @var $type_id
     * @var $type_data
     * @var $privacy
     * @var $tags
     * @var $content
     * @var $entity_id
     * @var $entity_type
     * @var $userid
     * @var $can_share
	 * @var $status
     * @var $auto_post
     * @var $enable_post
     */
    extract(array_merge($defined, $val));
    if ((isset($enable_post) && $enable_post) || $auto_post || $api) {
        $result = array(
            'status' => 0,
            'message' => lang('feed::failed-to-post'),
            'feed' => ''
        );
        $db = db();
        if (!can_post_to_feed($entity_type, $entity_id, $to_user_id)) return json_encode($result);
        if (empty($content) and empty($images) and empty($files) and empty($music) and empty($gif) and empty($list) and empty($video) and empty($voice) and empty($type_data) and empty($feeling_text) and empty($feeling_data) and empty($location)) return ($api) ? false : json_encode($result);


        $time = time();
        $tags_data = serialize($tags);
        $userid = get_userid();
        $content = config('enable-feed-text-limit', false) ? substr($content, 0, config('maximum-feed-text-limit', 150)) : $content;
        $content = sanitizeText($content);
        $entity_id = sanitizeText($entity_id);
        $entity_type = sanitizeText($entity_type);
        $userId = get_userid(); 
		$custom_column_sql = '';
        $custom_value_sql = '';
        $custom_column_sql = fire_hook('feed.custom.column.sql', array($custom_column_sql), array($val))[0];

        $custom_value_sql = fire_hook('feed.custom.value.sql', array($custom_value_sql), array($val))[0];
		
        $feed = $db->query("INSERT INTO `feeds` (to_user_id,can_share,user_id,tags,entity_id,entity_type,type,type_id,type_data".$custom_column_sql.",feed_content,privacy,time,status) VALUES('".$to_user_id."','".$can_share."', '".$userid."','".$tags_data."', '".$entity_id."', '".$entity_type."', '".$type."', '".$type_id."', '".$type_data."'".$custom_value_sql.",'".$content."', '".$privacy."','".$time."','".$status."')");
        if ($feed) {
            session_put(md5($type.$type_id), time());
            if (!$auto_post) {
                session_put(md5('feed'), time());
                session_put(md5('timeline'.get_userid()), time());
            }
            $feed_id = $db->insert_id;
            if ($tags and user_has_permission('can-tag-users-feed')) {
                add_user_tags($tags, 'post', $feed_id);
            }

            if ($to_user_id and $to_user_id != get_userid()) {
                //send notification to this user
                send_notification($to_user_id, 'post-on-timeline', $feed_id);

                $privacy = get_privacy('email-notification', 1, $to_user_id);
                if (config('enable-email-notification', true) and $privacy) {
                    $mailer = mailer();
                    $user = find_user($to_user_id);
                    if (!user_is_online($user)) {
                        $mailer->setAddress($user['email_address'], get_user_name($user))->template("post-on-wall", array(
                            'link' => url('feed/'.$feed_id),
                            'fullname' => get_user_name(),
                        ));
                        $mailer->send();
                    }
                }
            }

            add_subscriber($userid, 'feed', $feed_id);
            fire_hook("feed.added", null, array($feed_id, $val));
            if ($api) {
                return find_feed($feed_id);
            }
            return json_encode(array(
                'status' => 1,
                'message' => lang('feed::feed-successfully-posted'),
                'feed' => view('feed::feed', array('feed' => find_feed($feed_id)))
            ));
        }
    }
}
function add_quiz_question($val) {
   /**
      * @var $quiz_id
      * @var $question
      * @var $answer
      * @var $correct_answer
      */
      extract($val);
	  foreach ($val['answer'] as $key => $value) {
		if(array_key_exists($key, $val['correct_answer'])) {
			$val['correct_answer'][$key] = $value;
		}
	  }

	  $quiz = get_quiz($quiz_id);
	  $answers = implode(',', $answer);
	  $correct_answer = implode(',',$val['correct_answer']);
      $user_id = get_userid();
      db()->query("INSERT INTO quiz_questions (user_id,quiz_title,quiz_id,question,answers,correct_answers,pass_mark) VALUES('".$user_id."','".$quiz['title']."','".$quiz_id."', '".$question."','".$answers."','".$correct_answer."','".$quiz['pass_mark']."')");
      $insert_id = db()->insert_id;
      fire_hook('quiz.question.add', null, $insert_id);
      return $insert_id;
 }
 function get_quiz_questions($id) {
	$db = db();
	$query = $db->query("SELECT * FROM `quiz_questions` WHERE `quiz_id` = '".$id."'");
	return fetch_all($query);
}
function if_is_correct_answer($answer,$correct) {
	foreach($correct as $c) {
		if($answer == $c) {
			return true;
		}
	}
}
function count_quiz_user_questions($id) {
	$query = db()->query("SELECT * FROM quiz_questions WHERE `quiz_id` = '".$id."'");
	return $query->num_rows;
}

function is_published($id) {
	$db = db();
	$quiz = get_quiz($id);
	$published = $db->query("SELECT * FROM `quiz_status` WHERE `quiz_id`='".$id."'");
	$count = $published->num_rows;
	if($count > 0) {
		return true;
	}
}
function publish_quizz($type, $id) {
	$time = time();
	$userid = get_userid();
	$type_id = "quiz-added";
	$quiz = get_quiz($id);
    if($type == 'off') {
		$status = 0;
		db()->query("DELETE FROM `quiz_status` WHERE `quiz_id`='".$id."'");
        db()->query("UPDATE quizes SET status = '".$status."' WHERE user_id = '".$userid."' AND `id`='".$id."'");
		db()->query("UPDATE feeds SET status = '".$status."',time = '".$time."' WHERE type_data = '".$id."' AND type_id = '".$type_id."' AND `quiz`='".$id."'");
    } else {
        $status = 1;
		db()->query("INSERT INTO `quiz_status`(`title`,`quiz_id`,`status`)VALUES('".$quiz['title']."','".$id."','".$status."')");
        db()->query("UPDATE quizes SET status = '".$status."',time = '".$time."',update_time = '".$time."' WHERE user_id = '".$userid."' AND `id`='".$id."'");
		db()->query("UPDATE feeds SET status = '".$status."',time = '".$time."' WHERE type_data = '".$id."' AND type_id = '".$type_id."' AND `quiz`='".$id."'");
    }
}
//admin
function admin_get_quizes($term = null, $limit = 10) {
	$sql = '';

	if($term) $sql .= " WHERE title LIKE '%".$term."%' OR content LIKE '%".$term."%' OR tags LIKE '%".$term."%'";
	return paginate("SELECT * FROM quizes ".$sql." ORDER BY TIME DESC", $limit);
}


//admin
function add_quiz_answers($val) {
    /**
      * @var $entity
      * @var $quiz_id
	  * @var $question_id
	  * @var $correct
      * @var $checkeditem
      */
     extract($val);
	
    $user_id = get_userid();
    $time = time();
    $checkbox_answer = implode(',',$checkeditem);
    $quiz = get_quiz($quiz_id);       
	//print_r($question_id);die;
    foreach (array_combine($question_id,$checkeditem) as $quest_id => $chk_a) {
        db()->query("INSERT INTO quiz_answers(user_id,quiz_id,quiz_title,time,question_id,chk_answer) VALUES('".$user_id."','".$quiz_id."','".$quiz['title']."','".$time."','".$quest_id."','".$chk_a."')");   
	}
    $insert_id = db()->insert_id;
     fire_hook('quiz.answers.add', null, $insert_id);
     return $insert_id;
 }
function get_quiz_answers($id) {
	$db = db();
	$user_id = get_userid();
	$query = $db->query("SELECT * FROM `quiz_answers` WHERE `quiz_id` = '".$id."' AND user_id = '".$user_id."'");
	return fetch_all($query);
}
function get_quiz_question_id($id) {
	$db = db();
	$u_id = $db->query("SELECT id FROM `quiz_questions` WHERE `quiz_id` = '".$id."'");
	$u_id = $u_id->fetch_assoc();
	return $u_id;
}
function get_quiz_question($id) {
	$db = db();
	$query = $db->query("SELECT * FROM quiz_questions WHERE ".(is_numeric($id) ? "id = ".$id : "slug = '".$id."'"));
	$quiz = $query->fetch_assoc();
	return $quiz;
}
function calculate_mark($id) {
	$db = db();
	$userid = get_userid();
	$questions = get_quiz_questions($id);
	$answers = get_quiz_answers($id);

	$sum = 0;
	foreach($answers as $answer) {
		$question = get_quiz_question($answer['question_id']);
		if($answer['chk_answer'] == $question['correct_answers']) {
			$sum += 1;
		}
	}
	$percentage = round(($sum/count($questions))*100);
	return $percentage;
}
function has_taken_quiz($id) {
	$db = db();
	$quiz = get_quiz($id);
	$user_id = get_userid();
	$published = $db->query("SELECT * FROM `quiz_answers` WHERE `quiz_id`='".$id."' AND user_id = '".$user_id."'");
	$count = $published->num_rows;
	if($count > 0) {
		return true;
	}
}
 function get_submitted_answers($id) {
	$db = db();
	$userid = get_userid();
	$query = $db->query("SELECT * FROM `quiz_questions` WHERE `quiz_id` = '".$id."'");
	return fetch_all($query);
}
function quiz_has_answers($id) {
	$db = db();
	$quiz = get_quiz($id);
	$answers = $db->query("SELECT * FROM `quiz_answers` WHERE `quiz_id`='".$id."'");
	$count = $answers->num_rows;
	if($count > 0) {
		return true;
	}
}
function get_quiz_participants($id) {
	$db = db();
	$userid = get_userid();
	$query = $db->query("SELECT DISTINCT(user_id) FROM `quiz_answers` WHERE `quiz_id` = '".$id."'");
	return fetch_all($query);
}
function get_quiz_participants_answers($id,$uid) {
	$db = db();
	$query = $db->query("SELECT * FROM `quiz_answers` WHERE `quiz_id` = '".$id."' AND user_id = '".$uid."'");
	return fetch_all($query);
}
function get_each_quiz_answers($uid,$quiz_id) {
	$db = db();
	$questions = get_quiz_questions($quiz_id);
	$answers = get_quiz_participants_answers($quiz_id,$uid);
	$array = array();
	$i=1;
	foreach($answers as $answer) {
		$question = get_quiz_question($answer['question_id']);
		echo '<span class="quiz-q-title"><b>'.$i.'</b>'.$question['question'].'</span>';$i++;
		echo '<span class="each-quiz-answer">'.$answer['chk_answer'].'</span>';
		echo '<span class="" style="color: green">'.lang('quiz::correct-answer').': '.$question['correct_answers'].'</span>';
		
	}
}
function count_quiz_participants($id) {
	$query = db()->query("SELECT DISTINCT(user_id) FROM quiz_answers WHERE `quiz_id` = '".$id."'");
	return $query->num_rows;
}