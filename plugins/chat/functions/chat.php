<?php
function get_conversation_id($users, $create = true, $entityType = 'user', $entityId = '') {
    $id = get_single_conversation_id($users, $entityType, $entityId);
    $entityId = $entityId?$entityId:get_userid();
    if (count($users) > 1 or !$id and $create) {
        $type = (count($users) > 1) ? 'multiple' : 'single';
        $time = time();
        $user1 = null;
        $user2 = null;
        if (count($users) == 1) {
            $user1 = get_userid();
            $user2 = $users[0];
        }
        db()->query("INSERT INTO conversations (type, time, last_update_time, user1, user2, entity_type, entity_id)
        VALUES('".$type."', '".$time."', '".$time."', '".$user1."', '".$user2."', '".$entityType."', '".$entityId."')");
        $id = db()->insert_id;
        $users[] = get_userid();

        foreach ($users as $user) {
            add_conversation_member($id, $user, $type, $entityType, $entityId);
        }

    }

    return $id;
}

function get_single_conversation_id($users, $entityType = 'user', $entityId = null) {
    if (count($users) > 1) return false;
    $userid = $users[0];
    $iUser = get_userid();
    if ($entityType == 'user'){
        $query = db()->query("SELECT cid FROM conversations WHERE type = 'single' AND entity_type='{$entityType}' AND ((user1 = '".$userid."' AND user2 = '".$iUser."') OR (user1 = '".$iUser."' AND user2 = '".$userid."'))");
    } else {
        $query = db()->query("SELECT cid FROM conversations WHERE type = 'single' AND entity_type='{$entityType}' AND entity_id='{$entityId}' AND ((user1 = '".$userid."' AND user2 = '".$iUser."') OR (user1 = '".$iUser."' AND user2 = '".$userid."'))");
    }
    $result = $query->fetch_assoc();
    if ($result) return $result['cid'];
    return false;
}

function add_conversation_member($id, $user, $type = 'single', $entityType = 'user', $entityId = null) {
    $members = get_conversation_members($id);
    if (in_array($user, $members) or $user == 0) return false;
	$status = 1;
	if($type == 'multiple' && $user != get_userid()) $status = 0;
    $entityId = $entityId?$entityId:get_userid();
    $time = time();
    $db = db()->query("INSERT INTO conversation_members (member_cid, user_id, time, status, entity_type, entity_id) VALUES('".$id."', '".$user."', '".$time."', '".$status."', '".$entityType."', '".$entityId."')");

    fire_hook('conversation.member.added', null, array(db()->insert_id));
    forget_cache("chat-conversation-members-".$id);
    return db()->insert_id;
}

function get_conversation_members($id) {
    $cacheName = "chat-conversation-members-".$id;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $users = array();
        $q = db()->query("SELECT user_id FROM conversation_members WHERE member_cid = '".$id."'");
        while ($fetch = $q->fetch_assoc()) {
            $users[] = $fetch['user_id'];
        }

        set_cacheForever($cacheName, $users);
        return $users;
    }
}

function is_conversation_member($cid, $userid = null) {
    $members = get_conversation_members($cid);
    $userid = ($userid) ? $userid : get_userid();
    if (in_array($userid, $members)) return true;
    return false;
}

function has_accepted_group_conversation($conversation){
	if($conversation['type'] == 'multiple'){
		$id = $conversation['cid'];
		$userId = get_userid();
		$q = db()->query("SELECT * FROM conversation_members WHERE member_cid = '".$id."' AND user_id = '".$userId."'");
		$membership = $q->fetch_assoc();
		$status = $membership['status'];
		if($status == 1) return true;
		return false;
	}
	return true;
}

function accept_group_conversation($conversation){
	if($conversation['type'] == 'multiple'){
		$id = $conversation['cid'];
		$userId = get_userid();
		$q = db()->query("UPDATE conversation_members SET status = 1 WHERE member_cid = '".$id."' AND user_id = '".$userId."'");
		if($q) return true;
	}
	return false;
}

function delete_conversation($cid) {
    $user_id = get_userid();
    $db = db();
    $db->query("UPDATE conversation_members SET active = 0 WHERE member_cid = '".$cid."' AND user_id = '".$user_id."'");
    delete_all_messages($cid);
}

function update_conversation($data = array(), $conversationId){
    $db = db();
    array_walk($data, function(&$v, $k) {return $v = "`".$k."` = '".$v."'"; });
    $sql = "UPDATE `conversations` SET ".implode(', ', $data)." WHERE `cid` = ".$conversationId;
    $query = $db->query($sql);
    if($query) {
        return true;
    }
    return false;
}

function chat_register_box($cid, $uid, $user_id = null) {
    $user_id = $user_id ? $user_id : get_userid();
    $cache_name = 'user-chat-opened-'.$user_id;
    $cids = get_cache($cache_name, array());
    if (!in_array($cid, $cids)) {
        $cids[$cid] = $uid;
    }
    set_cacheForever($cache_name, $cids);
    return true;
}

function chat_unregister_box($cid, $user_id = null) {
    $user_id = $user_id ? $user_id : get_userid();
    $cache_name = 'user-chat-opened-'.$user_id;
    $cids = get_cache($cache_name, array());
    $c = array();
    foreach ($cids as $ci => $u) {
        if ($cid != $ci) {
            $c[$ci] = $u;
        }
    }
    $cids = $c;
    set_cacheForever($cache_name, $cids);
    return true;
}

function delete_conversation_member($cid, $user_id = null) {
    $members = get_conversation_members($cid);
    $user_id = $user_id ? $user_id : get_userid();
    if (in_array($user_id, $members)) {
        $cache_name = 'chat-conversation-members-'.$cid;
        $members = get_conversation_members($cid);
        $db = db();
        if (count($members) > 2) {
            $db->query("DELETE FROM conversation_members WHERE member_cid = '".$cid."' AND user_id ='".$user_id."'");
            $users = array();
            foreach ($members as $member) {
                if ($member != $user_id) {
                    $users[] = $member;
                    pusher()->sendMessage($member, 'chat.group.member.left', array('user_id' => $member, 'cid' => $cid, 'time' => time()));
                }
            }
            chat_unregister_box($cid, $user_id);
            set_cacheForever($cache_name, $users);
        } else {
            $db->query("DELETE FROM conversation_members WHERE member_cid = '".$cid."'");
            forget_cache($cache_name);
            delete_conversation($cid);
        }
        return true;
    }
    return false;
}

function send_chat_message($cid, $message, $image = null, $file = null, $voice = null, $gifPath = null, $entityType = 'user', $entityId = null) {
    $time = time();
    $sender = get_userid();
    $message = sanitizeText($message);
    //update the cid last update time
    db()->query("UPDATE conversations SET last_update_time = '".$time."' WHERE cid = '".$cid."'");
    $db = db()->query("INSERT INTO conversation_messages (cid, sender, message, time, files, image, voice, gif, entity_type, entity_id) VALUES('".$cid."', '".$sender."', '".$message."', '".$time."', '".$file."', '".$image."', '".$voice."', '".$gifPath."', '".$entityType."', '".$entityId."')");
    $insertId = db()->insert_id;
    //lets send the push to each members of this conversations
    $members = get_conversation_members($cid);
    foreach ($members as $userid) {
        if ($userid != get_userid()) {
            $messages = get_chat_messages_by_ids($insertId);
            fire_hook('send.message.notification', $cid, array($userid, $entityType, $entityId));
            $html = view('chat::messenger/messages', array('messages' => $messages, 'mark' => false, 'user_id' => $userid, 'conversation' => get_conversation($cid)));
            pusher()->sendMessage($userid, 'chat', array('user' => get_userid(), 'id' => $insertId, 'html' => $html), $cid);
        } else {
            //the essence of this is to prevent seen pushes update to prevent sound alert
            //pusher()->sendMessage($userid, 'chat', array('user' => get_userid(), 'id' => $insertId), $cid, false);
        }
    }

    return $insertId;
}

function get_conversation_message_fields() {
    return "conversation_messages.message_id, conversation_messages.cid, conversation_messages.sender, conversation_messages.message, conversation_messages.files, conversation_messages.voice, conversation_messages.gif, conversation_messages.image, conversation_messages.time";
}

function get_chat_message($id) {
    $fields = get_conversation_message_fields();
    $fields .= ", users.first_name, users.username, users.last_name, users.avatar, users.gender";
    $db = db();
    $query = $db->query("SELECT ".$fields." FROM conversation_messages LEFT JOIN users ON conversation_messages.sender = users.id WHERE message_id = '".$id."'");
    if ($query && $query->num_rows > 0) {
        $message = $query->fetch_assoc();
        $query = $db->query("SELECT time FROM conversation_messages WHERE time < ".$message['time']." AND cid = ".$message['cid']." ORDER BY time DESC LIMIT 1");
        $row = $query->fetch_row();
        $previous_message_time = isset($row[0]) ? $row[0] : 0;
        $message['last_message_time_difference'] = $message['time'] - $previous_message_time;
        return $message;
    }
    return false;
}

function get_chat_messages_by_ids($ids) {
    $fields = get_conversation_message_fields();
    $fields .= ", users.first_name, users.username, users.last_name, users.avatar, users.gender";
    $query = db()->query("SELECT ".$fields." FROM conversation_messages LEFT JOIN users ON conversation_messages.sender = users.id WHERE message_id IN (".$ids.")");
    if ($query) {
        $previous_message_time = 0;
        $messages = array_reverse(fetch_all($query));
        foreach ($messages as $index => $message) {
            $messages[$index]['last_message_time_difference'] = $message['time'] - $previous_message_time;
            $previous_message_time = $message['time'];
        }
        return $messages;
    }
    return false;
}

function get_chat_messages($cid, $limit = null, $offset = null, $user_id = null) {
    $limit = isset($limit) ? $limit : 10;
    $offset = isset($offset) ? $offset : 0;
    $user = isset($user_id) ? find_user($user_id) : null;
    $fields = get_conversation_message_fields();
    $fields .= ", users.first_name, users.username, users.last_name, users.avatar, users.gender";
    $ids = get_privacy('delete-messages', array(0), $user);
    $ids = implode(', ', $ids);

    $query = db()->query("SELECT ".$fields." FROM conversation_messages LEFT JOIN users ON users.id = conversation_messages.sender WHERE conversation_messages.cid = '".$cid."' AND conversation_messages.message_id NOT IN (".$ids.") ORDER BY conversation_messages.time desc LIMIT ".$offset.", ".$limit."");
    if ($query) {
        $previous_message_time = 0;
        $messages = array_reverse(fetch_all($query));
        foreach ($messages as $index => $message) {
            $messages[$index]['last_message_time_difference'] = $message['time'] - $previous_message_time;
            $previous_message_time = $message['time'];
        }
        return $messages;
    }
    return false;
}

function get_last_read_message_id($cid, $user_id = null) {
    $db = db();
    $message_ids = '0';
    foreach (get_chat_messages($cid) as $message) {
        $message_ids .= ', '.$message['message_id'];
    }
    $user_id = $user_id ? $user_id : get_userid();
    $query = $db->query("SELECT message_id FROM conversation_messages_read WHERE user_id = '".$user_id."' AND message_id IN (".$message_ids.") ORDER BY message_id DESC LIMIT 1");
    $row = $query->fetch_row();
    $message_id = $row ? $row[0] : 0;
    return $message_id;
}

function get_read_messages($userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $cacheName = "messages_read_".$userid;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $result = array();
        $q = db()->query("SELECT message_id FROM conversation_messages_read WHERE user_id = '".$userid."'");
        while ($fetch = $q->fetch_assoc()) {
            $result[] = $fetch['message_id'];
        }
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function get_unread_messages($userid = null, $cids = null, $include_deleted_conversations = false, $entityType = 'user', $entityId = null) {
    $result = array();
    $userid = ($userid) ? $userid : get_userid();
    $ids = get_read_messages();
    $ids[] = 0;
    $ids = array_merge($ids, get_privacy('delete-messages', array(0)));
    $ids = implode(', ', $ids);
    if (!$cids) $cids = get_user_conversation_id(null, $entityType, $entityId);
    $cids[] = 0;
    $cids = implode(', ', $cids);
    if ($entityType == 'user'){
        $sql = "SELECT * FROM conversation_messages WHERE message_id NOT IN (".$ids.") AND sender != '".$userid."' AND cid IN (".$cids.") AND entity_type='{$entityType}'";
    } else {
        $sql = "SELECT * FROM conversation_messages WHERE message_id NOT IN (".$ids.") AND sender != '".$userid."' AND cid IN (".$cids.") AND entity_type='{$entityType}' AND entity_id='{$entityId}'";
    }
    if (!$include_deleted_conversations) {
        $deleted_conversations = array_merge(array(0), get_deleted_conversations());
        $sql .= " AND cid NOT IN (".implode(', ', $deleted_conversations).")";
    }
    $query = db()->query($sql);
	if($query) {
    while ($fetch = $query->fetch_assoc()) {
        $result[] = $fetch['message_id'];
    }
	}
    return $result;
}

function count_unread_messages($cids = null, $user_id = null, $include_deleted_conversations = false) {
    $unread_messages = get_unread_messages($user_id, $cids, $include_deleted_conversations);
    $count = count($unread_messages);
    return $count;
}

function has_read_message($messageId, $userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $cacheName = "messages_read_".$userid;
    $readMessages = get_read_messages($userid);
    if (in_array($messageId, $readMessages)) return true;
    return false;
}

function message_is_read($message_id) {
    $cache_name = 'message_read_'.$message_id;
    if (cache_exists($cache_name)) {
        return get_cache($cache_name);
    } else {
        $db = db();
        $sql = "SELECT COUNT(message_id) FROM conversation_messages_read WHERE message_id = ".$message_id;
        $query = $db->query($sql);
        $result = $query->fetch_row();
        set_cacheForever($cache_name, $result[0]);
        return $result[0];
    }
}

function mark_message_read($messageId, $userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $cacheName = "messages_read_".$userid;
    $readMessages = get_read_messages($userid);
    if (in_array($messageId, $readMessages)) return true;

    db()->query("INSERT INTO conversation_messages_read (message_id, user_id) VALUES('".$messageId."', '".$userid."')");
    forget_cache($cacheName);
    get_read_messages($userid);
    return true;
}

function get_conversation_title($cid, $c = null) {
    $noC = false;
    if (!$c) {
        $noC = true;
        $q = db()->query("SELECT cid, type, title, user1, user2 FROM conversations WHERE cid = '".$cid."' ");
        $c = $q->fetch_assoc();
    }

    if (!$c) return false;
    if ($c['type'] == 'single') {
        $userid = ($c['user1'] == get_userid()) ? $c['user2'] : $c['user1'];
        $q = db()->query("SELECT username, id, first_name, last_name FROM users WHERE id = '".$userid."'");
        if ($q and $fetch = $q->fetch_assoc()) return get_user_name($fetch);
    } else {
        if ($c['title']) return $c['title'];
        //lets get the members
        if ($noC) {
            $userid = get_userid();
            $q = db()->query("SELECT id, first_name, last_name, avatar, username FROM conversation_members LEFT JOIN users ON conversation_members.user_id = users.id WHERE member_cid = '".$cid."' AND user_id != '".$userid."' LIMIT 2");
            $title = "";
            while ($fetch = $q->fetch_assoc()) {
                $title .= ($title) ? ', '.$fetch['first_name'] : $fetch['first_name'];
            }
            return $title;
        }
    }
}

function get_user_conversation_id($userid = null, $entityType = 'user', $entityId = null) {
    $userid = ($userid) ? $userid : get_userid();
    $entityId = $entityId?$entityId:get_userid();
    $query = '';
    if ($entityType == 'user'){
        $query = db()->query("SELECT member_cid FROM conversation_members WHERE user_id = '".$userid."' AND entity_type='{$entityType}'");
    } else {
        $query = db()->query("SELECT member_cid FROM conversation_members WHERE entity_type='{$entityType}' AND entity_id ='{$entityId}'");
    }
    $ids = array();
	if($query) {
		while ($fetch = $query->fetch_assoc()) {
			$ids[] = $fetch['member_cid'];
		}
	}
    return $ids;
}

function get_user_conversations($limit = null, $multiple = false, $offset = 0, $term = null, $entityType = 'user', $entityId = null) {
    $ids = get_user_conversation_id(null, $entityType, $entityId);
    $unread_message_ids = get_unread_messages(null, $ids, false, $entityType, $entityId);
    $ids = implode(', ', $ids);
    if (empty($ids)) {
        return array();
    }
    $deleteConversations = get_deleted_conversations($entityType, $entityId);
    $dIds = implode(', ', $deleteConversations);
    $unread_cids = array(0);
    foreach ($unread_message_ids as $unread_message_id) {
        $unread_message = get_chat_message($unread_message_id);
        $unread_cids[] = $unread_message['cid'];
    }
	$mids = implode(', ', get_privacy('delete-messages', array(0), $entityType === 'user' && $entityId ? find_user($entityId) : get_user()));
    if ($entityType == 'user'){
        $sql = "SELECT conversations.cid, conversations.type, conversations.title, conversations.last_update_time, conversations.user1, conversations.user2, conversations.color1, conversations.color2 FROM conversations WHERE conversations.cid IN (".$ids.") AND conversations.cid NOT IN (".$dIds.") AND (SELECT COUNT(conversation_messages.message_id) FROM conversation_messages WHERE conversation_messages.cid = conversations.cid AND conversation_messages.message_id NOT IN (".$mids.")) > 0 AND conversations.entity_type='".$entityType."'";
    } else {
        $sql = "SELECT conversations.cid, conversations.type, conversations.title, conversations.last_update_time, conversations.user1, conversations.user2, conversations.color1, conversations.color2 FROM conversations WHERE conversations.cid IN (".$ids.") AND conversations.cid NOT IN (".$dIds.") AND (SELECT COUNT(conversation_messages.message_id) FROM conversation_messages WHERE conversation_messages.cid = conversations.cid AND conversation_messages.message_id NOT IN (".$mids.")) > 0 AND conversations.entity_type='".$entityType."' AND conversations.entity_id='".$entityId."'";
    }
    if ($multiple) {
        $sql .= " AND type = 'multiple' ";
    }
    $sql .= ' ORDER BY (cid IN ('.implode(', ', $unread_cids).')) DESC, last_update_time DESC';
    if ($limit && !$term) {
        $sql .= "  LIMIT ".$offset.", ".$limit;
    }
    $query = db()->query($sql);
    $conversations = array();

    while ($fetch = $query->fetch_assoc()) {
        $c = rearrange_conversation($fetch);
        if ($c && (!$term || ($term && preg_match('/'.preg_quote($term, '/').'/i', $c['title'])))) {
            $conversations[] = $c;
        }
    }

    return $conversations;
}

function get_last_conversation($entityType = 'user', $entityId = null) {
    $conversations = get_user_conversations(1, false, 0, null,$entityType, $entityId);
    if ($conversations) return $conversations[0];
}

function get_deleted_conversations($entityType = 'user', $entityId = null) {
    $a = array(0);
    $userid = get_userid();
    if ($entityType == 'user'){
        $q = db()->query("SELECT member_cid FROM conversation_members WHERE user_id = '".$userid."' AND active = 0 AND entity_type='{$entityType}'");
    } else {
        $q = db()->query("SELECT member_cid FROM conversation_members WHERE user_id = '".$userid."' AND active = 0 AND entity_type='{$entityType}' AND entity_id='{$entityId}'");
    }
	if($q) {
		while ($fetch = $q->fetch_assoc()) {
			$a[] = $fetch['member_cid'];
		}
	}
    return $a;
}

function rearrange_conversation($fetch, $all = true) {
    if (!$fetch) return false;
    $c = $fetch;
    //if (empty($c['title'])) $c['title'] = get_conversation_title($c['cid'], $c);
    //get last massage
    $cid = $c['cid'];
    $c['last_message'] = null;
    if ($all) {
        $q = db()->query("SELECT message, sender FROM conversation_messages WHERE cid = '".$cid."' ORDER BY time DESC LIMIT 1");
        if ($q and $fetch = $q->fetch_assoc()) {
            $c['last_message'] = $fetch['message'];
            $c['last_sender'] = $fetch['sender'];
        } else {
            $c['last_message'] = null;
            $c['last_sender'] = null;
        }

        //we need to count unread messages as well
        $c['unread'] = count_unread_messages(array($c['cid']));

        $userid = get_userid();
        $q = db()->query("SELECT id, first_name, last_name, avatar, username FROM conversation_members LEFT JOIN users ON conversation_members.user_id = users.id WHERE member_cid = '".$cid."' AND user_id != '".$userid."' LIMIT 2");
        if ($c['type'] === 'single') {
            $other_user = get_userid() == $c['user1'] ? find_user($c['user2']) : find_user($c['user1']);
            $c['uid'] = $other_user ? $other_user['id'] : 0;
            $c['title'] = get_user_name($other_user);
            $c['avatar'] = get_avatar(75, $other_user);
            if (!isset($c['avatar'])) return false;
        } else {
            $title = '';
            $avatars = array();
            while ($fetch = $q->fetch_assoc()) {
                $title .= $title ? ', '.$fetch['first_name'] : $fetch['first_name'];
                $avatars[] = get_avatar(75, $fetch);
            }
            $c['title'] = $title;
            $c['avatars'] = $avatars;
            if (count($avatars) < 1) {
                return false;
            }
        }
    }

    return $c;
}


function get_conversation($cid, $all = true) {
    $query = db()->query("SELECT cid, type, title, last_update_time, user1, user2,color1,color2 FROM conversations WHERE cid = '".$cid."'");
    return rearrange_conversation($query->fetch_assoc(), $all);
}

function chat_get_onlines($user_id = null) {
    $user_id = isset($user_id) ? $user_id : get_userid();
    $friends = get_friends($user_id);
    $friends[] = 0;
    $time = time() - 50;
    $friends = implode(', ', $friends);
    $mustAvoidIds = implode(', ', mostIgnoredUsers($user_id));
    $sql = "SELECT id, first_name, last_name, avatar, online_status, username, online_time FROM users WHERE id IN (".$friends.") AND id NOT IN (".$mustAvoidIds.") and online_time > ".$time." and online_status != 2 ";

    if ($term = input('term') and $term) {
        $sql .= "AND ( first_name LIKE '%".$term."%' OR last_name LIKE '%".$term."%' ) ";
    }
    $sql .= " ORDER BY online_time DESC";
    $q = db()->query($sql);
    return fetch_all($q);
}

function get_few_offlines($user_id = null) {
    $user_id = isset($user_id) ? $user_id : get_userid();
    $friends = get_friends($user_id);
    $friends[] = 0;
    $time = time() - 50;
    $friends = implode(', ', $friends);
    $mustAvoidIds = implode(', ', mostIgnoredUsers($user_id));
    $q = db()->query("SELECT id, first_name, last_name, avatar, online_status, username, online_time FROM users WHERE id IN (".$friends.") AND id NOT IN (".$mustAvoidIds.") and online_time < ".$time." ORDER BY online_time DESC LIMIT 10");
    return fetch_all($q);
}

function delete_all_messages($cid) {
    $q = db()->query("SELECT message_id FROM conversation_messages WHERE cid = '".$cid."'");
    $ids = array();
    while ($fetch = $q->fetch_assoc()) {
        $ids[] = $fetch['message_id'];
    }
    $deletedMessages = get_privacy('delete-messages', array(0));
    $deletedMessages = array_merge($deletedMessages, $ids);
    save_privacy_settings(array('delete-messages' => $deletedMessages));

    return true;
}

function revive_conversation($cid) {

    db()->query("UPDATE conversation_members SET active = 1  WHERE member_cid = '".$cid."' ");
    return true;
}

function remove_typing_status($con) {
    if ($con and $con['type'] == 'single') {
        $userid = ($con['user1'] == get_userid()) ? $con['user2'] : $con['user1'];
        $cacheName = "typing-".$userid."";
        $result = array();
        if (cache_exists($cacheName)) $result = get_cache($cacheName);
        $newResult = array();
        foreach ($result as $cid => $time) {
            if ($con['cid'] != $cid) {
                $newResult[$cid] = $time;
            } else {
                $newResult[$cid] = time() - 100;
            }
        }
        set_cacheForever($cacheName, $newResult);
    }
}

function register_waiting_message($messageId, $con) {
    $userid = get_userid();
    $cacheName = "message-waiting-".$userid."";
    $result = array();
    if (cache_exists($cacheName)) {
        $result = get_cache($cacheName);
    }
    $theUserid = ($con['user1'] == get_userid()) ? $con['user2'] : $con['user1'];
    $result[$con['cid']] = array($messageId, $theUserid);
    set_cacheForever($cacheName, $result);
}

function get_chat_url_contents($message) {
    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $message['message'], $match);
    $content = '';
    $urls = $match[0];
    if ($urls) {
        foreach ($urls as $url) {
            $url = perfect_url($url);
            $validUrl = urlExist($url);
            if ($validUrl) {
                $linkDetails = feed_process_link($url);
                if (!$linkDetails) return false;
                $content .= view('feed::link', array('details' => $linkDetails, 'editor' => false))."<br>";
            }
        }
    }
    return $content;
}

function GenerateRandomColor() {
    $color = '#';
    $colorHexLighter = array("1", "2", "3", "4", "5", "6", "0");
    for ($x = 0; $x < 6; $x++) :
        $color .= $colorHexLighter[array_rand($colorHexLighter, 1)];
    endfor;
    return substr($color, 0, 7);
}
