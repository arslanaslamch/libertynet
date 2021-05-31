<?php
function ss_increase_faq_feedback($aid,$type){
    if($type == 1){
        db()->query("UPDATE suppport_system_articles SET rp_yes = rp_yes + 1 WHERE id='{$aid}'");
    }else{
        db()->query("UPDATE suppport_system_articles SET rp_no = rp_no + 1 WHERE id='{$aid}'");
    }
}
function has_responded_to_article($aid){
    $saved_name = 'ss_articles';
    $stored = session_get($saved_name,array());
    if(in_array($aid,$stored)) return true;
    return false;
}
 function ss_rand_color($count = 1) {
     $arr = array();
     for ($i = 0; $i < $count; $i++){
         $arr[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
     }
     return $arr;
}

function ss_get_chart_data(){
     $arr = array();
    $q = db()->query("SELECT COUNT(id) AS n, category_id  FROM suppport_system_tickets GROUP BY category_id");
    if($q->num_rows > 0){
        while ($r = $q->fetch_assoc()){
            $category = get_ss_category($r['category_id']);
            if($category){
                $title = lang($category['title']);
            }else{
                $title = lang("supportsystem::unknown");
            }
            $arr[$title] = $r['n'];
        }
    }else{
        $arr[lang("supportsystem::unknown")] = 0;
    }
    return $arr;
}
function ss_ticket_counts($type)
{
    $total = 0;
    switch ($type) {
        case 'open':
            $q = db()->query("SELECT * FROM suppport_system_tickets WHERE status='0'");
            $total = $q->num_rows;
            break;

        case 'closed':
            $q = db()->query("SELECT * FROM suppport_system_tickets WHERE status='2'");
            $total = $q->num_rows;
            break;

            case 'moderators':
            $q = db()->query("SELECT * FROM suppport_system_moderators");
            $total = $q->num_rows;
            break;

        case 'articles':
            $q = db()->query("SELECT * FROM suppport_system_articles");
            $total = $q->num_rows;
            break;
    }
    return $total;
}

//articles
function ss_article_delete($id)
{
    if (is_admin()) {
        db()->query("DELETE FROM suppport_system_articles WHERE id='{$id}'");
    }
}

function ss_faq_category_url($category_id){
     return url_to_pager("supportsystem-faq-category-page", array('slugs' =>$category_id));
}

function ss_article_url($article)
{
    return url_to_pager("supportsystem-article-page", array('slugs' => $article['slug']));
}

function ss_save_article($val, $type = 'add')
{
    $db = db();
    $category_id = $val['category'];
    $subject = sanitizeText($val['title']);
    $slug = toAscii($subject);
    $content = $val['content'];
    $content = mysqli_real_escape_string($db, $content);
    $user_id = get_userid();
    $time = time();
    if ($type == 'add') {
        $db->query("INSERT INTO suppport_system_articles(user_id,content,subject,category_id,time,slug,update_time) 
VALUES ('{$user_id}','{$content}','{$subject}','{$category_id}','{$time}','{$slug}','{$time}')");
        $id = $db->insert_id;
        fire_hook("ss.article.created", null, array($id));
    } else {
        $id = $val['id'];
        $db->query("UPDATE suppport_system_articles SET content='{$content}', subject='{$subject}', category_id='{$category_id}', update_time='{$time}', slug='{$slug}' WHERE id='{$id}'");
        fire_hook("ss.article.saved", null, array($id));
    }
}

function ss_get_articles($type = 'single', $id = null, $limit = 10, $term = null)
{
    switch ($type) {
        case 'single':
            $q = db()->query("SELECT * FROM suppport_system_articles WHERE id='{$id}' OR slug='{$id}'");
            if ($q->num_rows) return $q->fetch_assoc();
            return false;
            break;

        case 'category':
            return paginate("SELECT * FROM suppport_system_articles WHERE category_id='{$id}' ", $limit);
            break;

        case 'admin':
            $sql = "SELECT * FROM suppport_system_articles WHERE time > 0 ";
            if ($term) {
                $sql .= " AND subject LIKE '%{$term}%' ";
            }
            $sql .= " ORDER BY id DESC";
            return paginate($sql, $limit);
            break;
    }
}

function ss_print($content)
{
    echo nl2br($content);
}

function ss_ticket_url($ticket)
{
    return url_to_pager("supportsystem-view-page", array('slugs' => $ticket['id']));
}

function get_ss_ticket_id($ticket)
{
    $id = $ticket['id'];
    return '#' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

function ss_get_last_reply($tid)
{
    $q = db()->query("SELECT * FROM suppport_system_reply WHERE ticket_id='{$tid}' ORDER BY time DESC LIMIT 1");
    if ($q->num_rows) return $q->fetch_assoc();
    return false;
}

function ss_ticket_replies($tid)
{
    $q = db()->query("SELECT * FROM suppport_system_reply WHERE ticket_id='{$tid}'");
    return fetch_all($q);
}

function ss_get_ticket_reply($id)
{
    $q = db()->query("SELECT * FROM suppport_system_reply WHERE id='{$id}'");
    if ($q->num_rows > 0) return $q->fetch_assoc();
    return false;
}

function ss_upload_files()
{
    $files = "";
    $f = input_file('file');
    if ($f && (isset($f[0]['name']) && $f[0]['name'] != '')) {
        $uploaded_files = array();
        $validate = new Uploader(null, 'file', $f);
        $i = 0;
        if ($validate->passed()) {
            foreach ($f as $file) {
                $uploader = new Uploader($file, 'file');
                $path = get_userid() . '/' . date('Y') . '/supportsystem/files/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $file = $uploader->uploadFile()->result();
                    $uploaded_files[$i] = array(
                        'path' => $file,
                        'name' => $uploader->sourceName,
                        'extension' => $uploader->extension
                    );
                } else {
                    $result['status'] = 0;
                    $result['message'] = $uploader->getError();
                    return $result;
                }
                $i++;
            }
        } else {
            $result['status'] = 0;
            $result['message'] = $validate->getError();
            return $result;
        }
        if ($uploaded_files) {
            $files = perfectSerialize($uploaded_files);
        }
    }
    return $files;
}

function ss_close_ticket($tid)
{
    $status = 2;
    db()->query("UPDATE suppport_system_tickets SET status='{$status}' WHERE id='{$tid}'");
}

function ss_open_ticket($tid)
{
    $status = 0;
    db()->query("UPDATE suppport_system_tickets SET status='{$status}' WHERE id='{$tid}'");
}

function ss_get_last_moderator($ticket_id){
    $uid = get_userid();
    $q = db()->query("SELECT * FROM suppport_system_reply WHERE ticket_id='{$ticket_id}' AND user_id != '{$uid}'");
    if($q->num_rows > 0){
        $r = $q->fetch_assoc();
        return $r['user_id'];
    }
    return 0;
}

function ss_save_ticket_reply($val)
{
    $content = sanitizeText($val['content']);
    $files = ss_upload_files();
    $user_id = get_userid();
    $time = time();
    $ticket_id = $val['ticket_id'];
    $owner = $val['owner'];
    db()->query("INSERT INTO suppport_system_reply(ticket_id,user_id,time,content,files,owner) VALUES('{$ticket_id}','{$user_id}','{$time}','{$content}','{$files}','{$owner}')");
    $id = db()->insert_id;
    ss_open_ticket($ticket_id); //make ticket open
    $ticket = ss_get_ticket($ticket_id);
    $owner = $ticket['user_id'];
    if($owner == $user_id){
        $moderator = ss_get_last_moderator($ticket_id);
        send_notification($moderator, 'support.system.owner.replied', $ticket_id);
        /*$moderators = ss_get_ticket_moderators('all');
        if($moderators){
            foreach ($moderators as $moderator){
                send_notification($moderator, 'video.processed.processed', $id);
            }
        }*/
    }else{
        send_notification($owner, 'support.system.moderator.replied', $ticket_id);
    }
    fire_hook("support.system.reply.added", $id, array());
    return $id;
}

function ss_can_moderate_ticket($uid = null)
{
    $userid = ($uid) ? $uid : get_userid();
    $moderators = ss_get_ticket_moderators();
    if (in_array($userid, $moderators)) return true;
    if (is_admin()) return true;
    return false;
}

function ss_remove_moderator($uid)
{
    db()->query("DELETE FROM suppport_system_moderators WHERE user_id='{$uid}'");
}

function ss_add_moderator($val)
{
    $userids = $val['selected'];
    $db = db();
    $time = time();
    foreach ($userids as $userid) {
        ss_remove_moderator($userid);
        $db->query("INSERT INTO suppport_system_moderators(user_id,`time`) VALUES ('{$userid}','{$time}')");
    }
}

function ss_get_ticket_moderators($type = 'all')
{
    if ($type == 'admin') {
        return paginate("SELECT * FROM suppport_system_moderators", 10);
    }
    $arr = array(0);
    $q = db()->query("SELECT * FROM suppport_system_moderators");
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            $arr[] = $r['user_id'];
        }
    }
    return $arr;
}

function ss_can_view_ticket($ticket)
{
    if (!is_loggedIn()) return false;
    $owner = $ticket['user_id'];
    $userid = get_userid();
    if (is_admin()) return true;
    if ($owner == $userid) return true;
    $moderators = ss_get_ticket_moderators();
    if (in_array($userid, $moderators)) return true;
    return false;
}

function ss_get_ticket($ticket_id)
{
    $q = db()->query("SELECT * FROM suppport_system_tickets WHERE id='{$ticket_id}' OR slug='{$ticket_id}'");
    if ($q->num_rows > 0) {
        return $q->fetch_assoc();
    }
    return false;
}

/*function ss_get_tickets_replied($type = 'mine'){
    switch ($type){
        case 'mine':
            $uid = get_userid();
            $q = db()->query("SELECT * FROM suppport_system_reply WHERE owner='{$uid}' GROUP BY ORDER BY time DESC");
            if($q->num_rows){

            }
            break;
    }
}*/

function ss_get_ticket_replied($type = 'mine', $user_id = null)
{
    switch ($type) {
        case 'mine':
            $uid = ($user_id) ? $user_id : get_userid();
            $result = array();
            //$q = db()->query("SELECT * FROM suppport_system_reply WHERE owner='{$uid}' ORDER BY ");
            //this will return all the last replied messages order by time and grouped by
            $q = db()->query("SELECT suppport_system_reply.* 
FROM suppport_system_reply 
WHERE owner ='{$uid}' AND (suppport_system_reply.time, suppport_system_reply.ticket_id) in 
  (SELECT max(time) , ticket_id
     FROM suppport_system_reply messages
     Group by ticket_id);");
            if ($q->num_rows) {
                while ($r = $q->fetch_assoc()) {
                    if ($r['user_id'] == $r['owner']) continue;
                    $result[] = $r['ticket_id'];
                }
            }
            return $result;
            break;

        case 'unanswered':
            $result = array();
            //$q = db()->query("SELECT * FROM suppport_system_reply WHERE owner='{$uid}' ORDER BY ");
            //this will return all the last replied messages order by time and grouped by
            $q = db()->query("SELECT suppport_system_reply.* 
FROM suppport_system_reply 
WHERE owner = user_id AND (suppport_system_reply.time, suppport_system_reply.ticket_id) in 
  (SELECT max(time) , ticket_id
     FROM suppport_system_reply messages
     Group by ticket_id);");
            if ($q->num_rows) {
                while ($r = $q->fetch_assoc()) {
                    if ($r['user_id'] == $r['owner']) continue;
                    $result[] = $r['ticket_id'];
                }
            }
            break;

        case 'answered':
            $result = array();
            //$q = db()->query("SELECT * FROM suppport_system_reply WHERE owner='{$uid}' ORDER BY ");
            //this will return all the last replied messages order by time and grouped by
            $q = db()->query("SELECT suppport_system_reply.* 
FROM suppport_system_reply 
WHERE owner != user_id AND (suppport_system_reply.time, suppport_system_reply.ticket_id) in 
  (SELECT max(time) , ticket_id
     FROM suppport_system_reply messages
     Group by ticket_id);");
            if ($q->num_rows) {
                while ($r = $q->fetch_assoc()) {
                    if ($r['user_id'] == $r['owner']) continue;
                    $result[] = $r['ticket_id'];
                }
            }
            break;
    }
}

function get_support_tickets($type, $uid = null, $term = null, $ticket_type = null, $limit = 10)
{
    $sql = "SELECT * FROM suppport_system_tickets WHERE time > 0 ";
    if ($type == 'mine') {
        $uid = ($uid) ? $uid : get_userid();
        $sql .= " AND user_id='{$uid}' ";
        if ($term) {
            $sql .= " AND subject LIKE '%{$term}%' ";
        }
        if ($ticket_type) {
            if ($ticket_type == 1) {
                //awaiting your reply
                //get all the replied tickets where owner is me and order time group by ticket_id
                $replied_tickets = ss_get_ticket_replied();
                $replied_tickets[] = 0;
                $ids = implode(', ', $replied_tickets);
                $sql .= " AND id IN (" . $ids . ") AND status=0 ";
            } else {
                $sql .= " AND status='{$ticket_type}' ";
            }
        }
        $sql .= " ORDER BY id DESC";
        //echo $sql;die();
    }

    if ($type == 'moderator' || $type == 'admincp') {
        //we are getting tickets this user can moderate
        if ($type != 'admincp') {
            $uid = get_userid();
            $sql .= " AND user_id != '{$uid}' ";
        }

        if ($term) {
            $sql .= " AND subject LIKE '%{$term}%' ";
        }
        //0 is unanswered ticket
        //1 is answered ticket
        //2 closed ticket
        //echo $ticket_type;die();
        if ($ticket_type != 'all') {
            if ($ticket_type == 1) {
                //awaiting your reply
                $replied_tickets = ss_get_ticket_replied('answered');
                //list of ticket id where the ticket owner is not the one that replied last
                $replied_tickets[] = 0;
                $ids = implode(', ', $replied_tickets);
                $sql .= " AND id IN (" . $ids . ") AND status=0 ";
            } elseif ($ticket_type == 2) {
                //2
                $sql .= "AND status='{$ticket_type}' ";
            } elseif ($ticket_type == 0) {
                //0 is open ticket the ticket no moderator has answered
                $replied_tickets = ss_get_ticket_replied('unanswered');
                //list of ticket id where the ticket owner is not the one that replied last
                $replied_tickets[] = 0;
                $ids = implode(', ', $replied_tickets);
                $sql .= " AND id IN (" . $ids . ") AND status=0 ";
                //$sql .= " AND (status='{$ticket_type}')";
            }
        }
        $sql .= " ORDER BY id DESC";
    }
    //echo $sql; die();
    return paginate($sql, $limit);
}

function get_ss_ticket_status($type = 'member', $ticket = array())
{
    $ticket_owner_status = array(
        0 => lang('supportsystem::open'),
        1 => lang("supportsystem::awaiting-reply"),
        2 => lang("supportsystem::closed"),
    );

    $ticket_admin_status = array(
        0 => lang('supportsystem::unaswered'),
        1 => lang("supportsystem::answered"),
        2 => lang("supportsystem::closed"),
    );

    if ($type == 'member') {
        if ($ticket) {
            if (isset($ticket_owner_status[$ticket['status']])) return $ticket_owner_status[$ticket['status']];
        }
        return $ticket_owner_status;
    }
    if ($type == 'admin') {
        if ($ticket) {
            if (isset($ticket_admin_status[$ticket['status']])) return $ticket_admin_status[$ticket['status']];
        }
        return $ticket_admin_status;
    }
}

function get_ss_priorities($key = null)
{
    $arr = array(
        1 => lang("supportsystem::critical"),
        2 => lang("supportsystem::high"),
        3 => lang("supportsystem::normal"),
        4 => lang("supportsystem::low"),
    );
    if ($key) {
        if (isset($arr[$key])) return $arr[$key];
        return false;
    }
    return $arr;
}

function save_support_ticket($val, $type = 'new', $ticket = array())
{
    $expected = array(
        'subject' => '',
        'content' => '',
        'tags' => '',
        'category' => '',
        'entity' => '',
        'priority' => '',
        'status' => 0
    );
    /**
     * @var $subject
     * @var $content
     * @var $category
     * @var $status
     * @var $privacy
     * @var $entity
     * @var $priority
     * @var $status
     *
     */
    extract(array_merge($expected, $val));

    $db = db();
    $files = ($ticket) ? $ticket['files'] : '';
    $uploaded = ss_upload_files();
    if ($uploaded) $files = $uploaded;
    //echo 'After process';die();
    $time = time();
    $userid = get_userid();
    $slug = toAscii($subject) . '-' . uniqid();
    $content = html_purifier_purify($content);
    $content = mysqli_real_escape_string($db, $content);
    $entity = explode('-', $entity);
    if (count($entity) == 2) {
        $entity_type = $entity[0];
        $entity_id = $entity[1];
    }
    if (!isset($entity_id) || !isset($entity_type)) {
        return array('message' => lang('something-went-wrong'));
    }
    if ($type == 'new') {
        $db->query("INSERT INTO suppport_system_tickets (user_id, entity_type, entity_id, subject, slug, content, files, update_time, time, status, category_id, priority) VALUES ('" . $userid . "', '" . $entity_type . "', '" . $entity_id . "', '" . $subject . "', '" . $slug . "', '" . $content . "', '" . $files . "', '" . $time . "', '" . $time . "', '" . $status . "', '" . $category . "', '" . $priority . "')");
        $TicketId = $db->insert_id;
        //echo db()->error;die();
        $moderators = ss_get_ticket_moderators('all');
        if($moderators){
            foreach ($moderators as $moderator){
                send_notification($moderator, 'support.system.ticket.new', $TicketId);
            }
        }
        fire_hook("support.system.ticket.added", null, array($TicketId));
    } else {
        //we are saving Ticket
        $TicketId = $ticket['id'];
        $db->query("UPDATE suppport_system_tickets SET slug = '" . $slug . "', files = '" . $files . "', subject = '" . $subject . "', content = '" . $content . "', status = '" . $status . "', update_time = '" . $time . "', privacy = '" . $privacy . "', category_id = '" . $category . "' WHERE id = '" . $TicketId . "'");

    }
    return $TicketId;
}

function can_create_support_article()
{
    if (!is_loggedIn()) return false;
    if (is_admin()) return true;
    return false;
}

function ss_add_category($val)
{
    $expected = array(
        'title' => '',
        'category' => '',
        'type' => 'ticket'
    );

    /**
     * @var $title
     * @var $desc
     * @var $category
     * @var $type
     */
    extract(array_merge($expected, $val));
    $titleSlug = "suppportsystem_category_" . md5(time() . serialize($val)) . '_title';
    foreach ($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'supportsystem');
    }
    //$type = "";
    $order = db()->query('SELECT id FROM support_system_categories');
    $order = $order->num_rows;
    db()->query("INSERT INTO `support_system_categories`(`title`,`category_order`,`parent_id`,`type`) VALUES('" . $titleSlug . "','" . $order . "','" . $category . "','{$type}')");
    return true;
}

function save_ss_category($val, $category)
{
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     */
    $titleSlug = $category['title'];
    //print_r($category);die();
    extract(array_merge($expected, $val));

    foreach ($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'supportsystem') : add_language_phrase($titleSlug, $t, $langId, 'supportsystem');
    }

    return true;
}

function get_ss_categories($type = 'ticket')
{
    $query = db()->query("SELECT * FROM `support_system_categories` WHERE parent_id ='0' AND `type`='{$type}' ORDER BY `category_order` ASC");
    return fetch_all($query);
}

function get_ss_parent_categories($id, $type = 'ticket')
{
    $db = db()->query("SELECT * FROM `support_system_categories` WHERE parent_id='{$id}' AND `type`='{$type}' ORDER BY `category_order` ASC");
    $result = fetch_all($db);
    return $result;
}

function get_ss_category($id)
{
    $query = db()->query("SELECT * FROM `support_system_categories` WHERE `id`='" . $id . "'");
    return $query->fetch_assoc();
}


function delete_ss_category($id, $category)
{
    delete_all_language_phrase($category['title']);
    db()->query("DELETE FROM `support_system_categories` WHERE `id`='" . $id . "'");
    return true;
}

function update_ss_category_order($id, $order, $type = 'ticket')
{
    db()->query("UPDATE `support_system_categories` SET `category_order`='" . $order . "' WHERE  `id`='" . $id . "' AND `type`='{$type}'");
}

function support_ticket_slug_exists($slug)
{
    $query = db()->query("SELECT COUNT(id) FROM `support_system_ticket` WHERE  `slug`='" . $slug . "'");
    $result = $query->fetch_row();
    return $result[0] == 0 ? FALSE : TRUE;
}