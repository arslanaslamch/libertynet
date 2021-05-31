<?php
function add_comment($val) {
    $expected = array(
        'text' => '',
        'type' => '',
        'type_id' => '',
        'image_path' => '',
        'gif_path' => '',
        'voice_path' => '',
        'entity_id' => get_userid(),
        'entity_type' => 'user'
    );
    $result = array(
        'status' => 1,
        'message' => '',
        'feed' => ''
    );

    /**
     * @var $text
     * @var $image_path
     * @var $voice_path
     * @var $type
     * @var $gif_path
     * @var $type_id
     * @var $entity_id
     * @var $entity_type
     */
    extract(array_merge($expected, $val));

    //if (!is_numeric($entity_id)) return false;
    $image = input_file('image');
    if ($image) {
        $uploader = new Uploader($image);
        if ($uploader->passed()) {
            $path = get_userid().'/'.date('Y').'/photos/comments/';
            $uploader->setPath($path);
            $image_path = $uploader->resize()->toDB('posts')->result();
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
            return json_encode($result);
        }
    }

    $voice = input('voice');
    if ($voice) {
        list($header, $voice) = array_pad(explode(',', $voice), 2, '');
        preg_match('/data\:audio\/(.*?);base64/i', $header, $matches);
        $default_extension = 'webm';
        $extension = isset($matches[1]) ? $matches[1] : $default_extension;
        if (!in_array($extension, array($default_extension))) {
            exit('Invalid Format '.$extension);
        }
        $voice = base64_decode(str_replace(' ', '+', $voice));
        $temp_dir = config('temp-dir', path('storage/tmp')).'/comment/voice';
        $file_name = 'voice_'.get_userid().'_'.time();
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }
        $temp_path = $temp_dir.'/'.$file_name.'.'.$extension;
        file_put_contents($temp_path, $voice);
        $uploader = new Uploader($temp_path, 'audio', false, true);
        if ($uploader->passed()) {
            $path = get_userid().'/'.date('Y').'/voices/comments/';
            $uploader->setPath($path);
            $voice_path = $uploader->uploadFile()->toDB('posts')->result();
        } else {
            $result['status'] = 0;
            $result['message'] = $uploader->getError();
            return json_encode($result);
        }
    }
    $gifImage = input('gif');
    if ($gifImage) {
        $file_path = get_userid().'/'.date('Y').'/gif/comment/posts/';
        $gif_path = gifImageProcessing($gifImage, $file_path, 'posts', $entity_type, $entity_id);
        if(!$gif_path) {
            $result['status'] = 0;
            $result['message'] = lang('gif-upload-error');
            return json_encode($result);
        }
    }
    $userid = get_userid();
    $time = time();
    $entity_id = sanitizeText($entity_id);
    $entity_type = sanitizeText($entity_type);
    $type_id = sanitizeText($type_id);
    $text = sanitizeText($text);	
	if(!trim($text) && !$gifImage && !$voice && !$image) {
		$result['status'] = 0;
		$result['message'] = lang('comment::comment-editor-error');
		return json_encode($result);
	}
    $query = db()->query("INSERT INTO `comments` (user_id, entity_id, entity_type, type, type_id, text, image, gif, voice, time) VALUES('".$userid."', '".$entity_id."', '".$entity_type."', '".$type."', '".$type_id."', '".$text."', '".$image_path."', '".$gif_path."', '".$voice_path."', '".$time."')");

    if ($query) {
        $commentId = db()->insert_id;
        refresh_comment_cache($type, $type_id);
        add_subscriber($userid, $type, $type_id);
        if ($type != 'comment') add_subscriber($userid, 'comment', $commentId);
        fire_hook('comment.add', null, array($type, $type_id, $text));
        fire_hook("creditgift.addcredit.comment", null, array($userid));
        return json_encode(array(
            'status' => 1,
            'comment' => (string)view("comment::display", array('comment' => find_comment($commentId))),
            'count' => count_comments($type, $type_id),
            'message' => lang('comment::comment-inserted-successfully')
        ));
    }
}

function get_comment_fields() {
    $fields = "user_id,comment_id,entity_id,entity_type,type,type_id,text,image,gif,voice,time";
    return fire_hook("comment.table.fields", $fields, array($fields));
}

function find_comment($id, $all = true) {
    $fields = get_comment_fields();
    $query = db()->query("SELECT ".$fields." FROM comments  WHERE `comment_id`='".$id."'");
    if ($query) return ($all) ? arrange_comment($query->fetch_assoc()) : $query->fetch_assoc();
    return false;
}

function get_comments($type, $typeId, $limit = 10, $offset = 0, $owner = null, $sort = 'top') {
    $fields = get_comment_fields();
    $sql ="";
    if ($sort == 'top'){
        $sql .= "SELECT ".$fields.", comment_id AS id, (SELECT count(*) FROM comments WHERE type='comment' AND type_id=id) AS top FROM comments  WHERE `type`='".$type."' and `type_id`='".$typeId."' ORDER BY top DESC LIMIT ".$limit." OFFSET ".$offset."";
    }else{
        $sql .= "SELECT ".$fields." FROM comments  WHERE `type`='".$type."' and `type_id`='".$typeId."' ORDER BY `time` DESC LIMIT ".$limit." OFFSET ".$offset."";
    }
    $query = db()->query($sql);
    if ($query) {
        $comments = array();
        while ($fetch = $query->fetch_assoc()) {
            $comment = arrange_comment($fetch, $owner);
            if ($comment) $comments[] = $comment;
        }
        return array_reverse($comments);
    }

    return array();
}

function arrange_comment($fetch, $owner = null) {
    $fetch['owner'] = $owner;
    $comment = $fetch;
    if ($fetch['entity_type'] == 'user') {
        $user = find_user($fetch['user_id'], false);
        if ($user) {
            $comment['publisher'] = $user;
            $comment['publisher']['avatar'] = get_avatar(75, $user);
            $comment['publisher']['url'] = profile_url(null, $user);
        }
    } else {
        $comment['publisher'] = fire_hook('comment.get.publisher', null, array($comment));
    }
    if (!isset($comment['publisher']) || !$comment['publisher']) return false;

    $commentId = $comment['comment_id'];
    //count replies of this comment
    if (config('enable-comment-replies', true)) {
        $query = db()->query("SELECT `comment_id` FROM `comments` WHERE `type`='comment' AND `type_id`='".$commentId."'");
        $comment['replies'] = $query->num_rows;
    }

    $comment['editor'] = array(
        'avatar' => get_avatar(75),
        'id' => get_userid(),
        'type' => 'user'
    );
    //$comment['text'] = output_text($comment['text']);
    $comment = fire_hook("comment.arrange", $comment);
    return $comment;
}

function count_comments($type, $typeId) {
    $cacheName = 'comment-counts-'.$type.'-'.$typeId;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $query = db()->query("SELECT comment_id FROM `comments` WHERE `type`='".$type."' and `type_id`='".$typeId."'");
        if ($query) {
            set_cacheForever($cacheName, $query->num_rows);
            return $query->num_rows;
        }
    }

    return 0;
}

function can_post_comment($entity_type, $entity_id) {
    $result = array('result' => true);
    $result = fire_hook('can.post.comment', $result, array($entity_type, $entity_id));
    return $result['result'];
}

function can_edit_comment($comment) {
    $user = get_user();
    if (!is_loggedIn()) return false;
    if ($comment['entity_type'] == 'user' and $user['id'] == $comment['entity_id']) return true;
    if (is_admin() or is_moderator()) return true;
    $result = array('status' => false);
    $result = fire_hook('comment.can-edit', $result, array($comment));
    return $result['status'];
}

function delete_comment($id) {
    $comment = find_comment($id);
    if (!can_edit_comment($comment)) return false;
    refresh_comment_cache($comment['type'], $comment['type_id']);
    do_delete_comment($comment);
    db()->query("DELETE FROM `comments` WHERE `comment_id`='".$id."'");
    return true;
}

function do_delete_comment($comment) {
    if ($comment['image']) {
        @delete_file($comment['image']);
    }
    if (plugin_loaded('like')) delete_likes('comment', $comment['comment_id']);
    //lets delete replies
    $id = $comment['comment_id'];
    $db = db()->query("SELECT * FROM comments WHERE type='comment' AND type_id='".$id."'");
    while ($comment = $db->fetch_assoc()) {
        if ($comment['image']) {
            @delete_file($comment['image']);
        }
        if ($comment['voice']) {
            @delete_file($comment['voice']);
        }
        if (plugin_loaded('like')) delete_likes('comment', $comment['comment_id']);
    }
    fire_hook("creditgift.addcredit.uncomment", null, array(get_userid()));
    return true;
}

function delete_comments($type, $id) {
    $db = db()->query("SELECT * FROM comments WHERE type='".$type."' AND type_id='".$id."'");
    while ($comment = $db->fetch_assoc()) {
        do_delete_comment($comment);
    }
    db()->query("DELETE FROM comments WHERE type='".$type."' AND type_id='".$id."'");
    return true;
}

function refresh_comment_cache($type, $typeId) {
    $cacheName = 'comment-counts-'.$type.'-'.$typeId;
    forget_cache($cacheName);
}

function save_comment($text, $id) {
    $comment = find_comment($id);
    if (!$text or !can_edit_comment($comment)) return false;
    $text = sanitizeText($text);
    db()->query("UPDATE `comments` SET `text`='".$text."' WHERE `comment_id`='".$id."'");
    return $comment;
}


function count_total_comments() {
    $q = db()->query("SELECT comment_id FROM comments ");
    return $q->num_rows;
}

function postowner($userid) {
    $q = db()->query("SELECT * FROM comments WHERE postownerId='".$userid."'");
    return $q->num_rows ? true : false;
}

function get_comment_ids($type, $typeId){
    $db = db();
    $sql = "SELECT comment_id FROM comments  WHERE `type`='".$type."' and `type_id`='".$typeId."'";
    $query = $db->query($sql);
    $records = fetch_all($query);
    $commentIds = array();
    foreach ($records as $record){
        $commentIds[] = $record['comment_id'];
    }
    return $commentIds;
}

function commentsWithMostReplies($type, $typeId, $limit = 10, $offset = 0, $owner = null){
    $commentIds = implode(',', get_comment_ids($type, $typeId));
    $sql = "SELECT comment_id, count(type_id) as cnt FROM comments  WHERE `type`='".$type."' and `type_id`='".$typeId."' AND type_id IN ({$commentIds}) ORDER BY cnt,`time` DESC LIMIT ".$limit." OFFSET ".$offset." ";
    $query = db()->query($sql);
    if ($query) {
        $comments = array();
        while ($fetch = $query->fetch_assoc()) {
            $comment = arrange_comment($fetch, $owner);
            if ($comment) $comments[] = $comment;
        }
        return array_reverse($comments);
    }

    return array();
}

function topComments($type, $typeId, $limit = 10, $offset = 0, $owner = null){
    $commentIds = implode(',', get_comment_ids($type, $typeId));
    $friends = implode(',', get_friends());
    $fields = get_comment_fields();
    $sql = "SELECT {$fields}, count(type_id) as cnt FROM comments  WHERE `type`='".$type."' and `type_id`='".$typeId."' OR (type_id IN ({$commentIds}) AND (user_id IN ({$friends}) AND `type`='".$type."' AND `type_id`='".$typeId."')) ORDER BY cnt,`time` DESC LIMIT ".$limit." OFFSET ".$offset." ";
    $query = db()->query($sql);
    if ($query) {
        $comments = array();
        while ($fetch = $query->fetch_assoc()) {
            $comment = arrange_comment($fetch, $owner);
            if ($comment) $comments[] = $comment;
        }
        return array_reverse($comments);
    }

    return array();
}