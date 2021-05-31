<?php

function unix_ma_time($from, $end)
{
    $from = explode('/', trim($from));
    $f = @mktime(0, 0, 0, $from[0], $from[1], $from[2]);
    $end = explode('/', trim($end));
    $e = @mktime(23, 59, 0, $end[0], $end[1], $end[2]);
    return array('f' => $f, 'e' => $e);
}

function date_ma_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y' ) {

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

function getMailAutomationGraphData($from, $end)
{
    //echo $end;die();
    $fe = unix_ma_time($from, $end);
    $f = $fe['f'];
    $e = $fe['e'];
    if($e < $f) return false;
    $sql = "SELECT *  FROM mailautomation_stats WHERE time >= '{$f}' AND time <= '{$e}'";
    $query = db()->query($sql);
    $arr = array();
    $arr_record_time = array();
    if ($query->num_rows > 0) {
        //let us work here
        while ($a = $query->fetch_assoc()) {
            $time = date('j/n/Y', $a['time']);

            if ($a['matype'] == 'auto') {
                if (!in_array($time, $arr_record_time)) {
                    $pre_arr = array('x' => $time);
                    $pre_arr['y'] = 1; //automation
                    $pre_arr['z'] = 0; //z is highlihts
                    $arr_record_time[] = $time;
                } else {
                    $pre_arr = $arr[$time];
                    $pre_arr['y'] = $pre_arr['y'] + 1;
                    $pre_arr['z'] = $pre_arr['z'] + 0; //z is highlihts
                    $arr_record_time[$time] = $pre_arr;
                }
            }
            if ($a['matype'] == 'highlights') {
                if (!in_array($time, $arr_record_time)) {
                    $pre_arr = array('x' => $time);
                    $pre_arr['y'] = 0; //automation
                    $pre_arr['z'] = 1; //z is highlihts
                    $arr_record_time[] = $time;
                } else {
                    $pre_arr = $arr[$time];
                    $pre_arr['y'] = $pre_arr['y'] + 0;
                    $pre_arr['z'] = $pre_arr['z'] + 1; //z is highlihts
                    $arr_record_time[$time] = $pre_arr;
                }
            }
            $arr[$time] = $pre_arr;
            //$result[] = $pre_arr;
        }
    }
    /*$period = new DatePeriod(
        new DateTime($from),
        new DateInterval('P1D'),
        new DateTime($end)
    );
    foreach ($period as $key => $value) {
        $date[] = $value->format('j/F/Y');
    }*/
    $dates = date_ma_range($from,$end, '+1 day','j/n/Y');
    $result = array();
    foreach($dates as $d){
        if(in_array($d,array_keys($arr))){
            $result[] = $arr[$d];
        }else{
            $result[] = array('x'=>$d,'y'=>0,'z'=>0);
        }
    }
    //echo '<pre>', print_r($result);'</pre>';
    //die();
    return $result;
}

function getMailAutomationStats($limit)
{
    return paginate("SELECT * FROM mailautomation_stats ORDER BY time DESC", $limit);
}

function maStatus($a)
{
    if ($a['status'] == 1) {
        return '<span class="badge badge-pill badge-info">' . lang("mailautomation::sent") . '</span>';
    }

    if ($a['status'] == 2) {
        return '<span class="badge badge-pill badge-success">' . lang("mailautomation::opened") . '</span>';
    }

    if ($a['status'] == 0) {
        return '<span class="badge badge-pill badge-danger">' . lang("mailautomation::not-sent") . '</span>';
    }
}

function addNewAutomationStats($uid, $subject, $email, $type = 'auto')
{
    $time = time();
    $status = 1;
    db()->query("INSERT INTO mailautomation_stats(user_id,subject,email,status,`time`,matype) 
VALUES('{$uid}','{$subject}','{$email}','{$status}','{$time}','{$type}')");
    return db()->insert_id;
}

function hightlights_feeds($uid)
{
    $limit = config('number-of-posts-highlights', 7);
    $query = db()->query("SELECT * FROM feeds WHERE feed_content != '' OR photos !='' AND user_id !='{$uid}' GROUP BY feed_id ORDER BY time DESC LIMIT 0,{$limit} ");
    $results = array();
    if ($query) {
        while ($fetch = $query->fetch_assoc()) {
            $feed = get_arranged_feed($fetch);
            if ($feed and $feed['status'] == 1) {
                $results[] = $feed;
                // $view = view("mailautomation::feed-view-hightlights",array('feed'))
                // $view .= $view;
            }
        }
        return $results;
    };
    return $results;
}

function getMyfriendsPosts($user, $limit = 5)
{
    $userid = $user['id'];
    //echo $userid;
    $sql_fields = get_feed_fields();
    $offset = 0;
    $type = "feed";
    $sql = "SELECT " . $sql_fields . " FROM `feeds` WHERE";

    $where_clause = "";
    $users = array(0);
    $followings = array_merge($users, get_following($userid));
    $followings = implode(',', $followings);
    $where_clause .= " (entity_type='user' AND `privacy`='1' AND `entity_id` IN ({$followings}))";

    $friends = array_merge($users, get_friends($userid));
    $friends = implode(',', $friends);
    $where_clause .= " OR (entity_type='user' AND (privacy ='1' or privacy='2') AND `entity_id` IN ({$friends}) OR entity_id IN ({$followings}))";

    $where_clause .= " AND feed_content != '' AND user_id !='{$userid}'";

    $order_by = " GROUP BY feed_id ORDER BY RAND() DESC";
    $order_by .= " LIMIT " . $offset . ", " . $limit;
    $sql .= $where_clause . $order_by;
    $query = db()->query($sql);
    //echo $sql;die();
    if ($query) {
        $results = array();
        while ($fetch = $query->fetch_assoc()) {
            $feed = get_arranged_feed($fetch);
            if ($feed and $feed['status'] == 1) {
                $results[] = $feed;
            } else {
                //think we should delete this
            }
        }
        return $results;
    }
    return array();
}

function replaceMailAutomationStrings($content, $user, $fps = false)
{
    $username = $user['username'];
    $fullname = $user['first_name'] . ' ' . $user['last_name'];
    $firstname = $user['first_name'];
    $lastname = $user['last_name'];
    $email = $user['email_address'];
    $profile_url = profile_url(null, $user);
    $siteTitle = get_setting("site_title", "Crea8social");

    //replace start
    $content = str_replace('[username]', $username, $content);
    $content = str_replace('[full_name]', $fullname, $content);
    $content = str_replace('[first_name]', $firstname, $content);
    $content = str_replace('[last_name]', $lastname, $content);
    $content = str_replace('[email_address]', $email, $content);
    $content = str_replace('[profile_link]', $profile_url, $content);
    $content = str_replace('[site-title]', $siteTitle, $content);
    //replace end

    //get friends array post
    if ($fps) {
        $friends_post = getMyfriendsPosts($user);
        //print_r($friends_post);die();
        //echo "we here";die();
        if ($friends_post) {
            //now we can now

            $i = 1;
            foreach ($friends_post as $fp) {
                //this is the post array;
                $fpview = view("mailautomation::feed-view", array('feed' => $fp));
                $content = str_replace('[post-' . $i . ']', $fpview, $content);
                $i++;
            }
        }
        //echo $content;die();
    }
    //as a precautionary measure
    //we will now remove the post 1-5 incase no post was return or it is not sufficeint
    $arr_posts = array('[post-1]', '[post-2]', '[post-3]', '[post-4]', '[post-5]');
    foreach ($arr_posts as $ap) {
        $content = str_replace($ap, "", $content);
    }

    return $content;
}

function delete_mailautomation($id)
{
    db()->query("DELETE FROM mailautomations WHERE id='{$id}'");
}

function get_mailautomation($id)
{
    $q = db()->query("SELECT * FROM mailautomations WHERE id='{$id}'");
    return $q->fetch_assoc();
}

function save_mailautomation($val, $existing = array(), $admin = true)
{
    $expectedDetails = array(
        'title' => '',
        'subject' => '',
        'na_count' => 1,
        'body_content' => ''
    );

    /**
     * @var $title
     * @var $subject
     * @var $na_count
     * @var $body_content
     */
    extract(array_merge($expectedDetails, $val));
    $slug = unique_slugger($title);
    $time = time();
    $slug = ($slug) ? $slug : md5($time);
    $subjectSlug = $existing['subject'];
    $messageSlug = $existing['body_content'];
    foreach ($subject as $langId => $t) {
        //add_language_phrase($subjectSlug, $t, $langId, 'mail-automation');
        (phrase_exists($langId, $subjectSlug)) ? update_language_phrase($subjectSlug, $t, $langId, 'mail-automation') : add_language_phrase($subjectSlug, $t, $langId, 'mail-automation');
    }
    foreach ($body_content as $langId => $t) {
        //add_language_phrase($messageSlug, $t, $langId, 'mail-automation');
        (phrase_exists($langId, $messageSlug)) ? update_language_phrase($messageSlug, $t, $langId, 'mail-automation') : add_language_phrase($messageSlug, $t, $langId, 'mail-automation');
    }

    $id = $existing['id'];
    db()->query("UPDATE mailautomations SET slug='{$slug}',title='{$title}',na_count='{$na_count}' WHERE id='{$id}'");

    return true;
}

function add_mailautomation($val)
{
    $expectedDetails = array(
        'title' => '',
        'subject' => '',
        'na_count' => 1,
        'body_content' => ''
    );

    /**
     * @var $title
     * @var $subject
     * @var $na_count
     * @var $body_content
     */
    extract(array_merge($expectedDetails, $val));
    $slug = unique_slugger($title);
    $time = time();
    $slug = ($slug) ? $slug : md5($time);
    $subjectSlug = $slug . '_mail_automation_subject';
    $messageSlug = $slug . '_mail_automation_message';
    foreach ($subject as $langId => $t) {
        add_language_phrase($subjectSlug, $t, $langId, 'mail-automation');
    }
    foreach ($body_content as $langId => $t) {
        add_language_phrase($messageSlug, $t, $langId, 'mail-automation');
    }

    db()->query("INSERT INTO `mailautomations` (`slug`,`title`,`subject`,`body_content`,`time`,`na_count`)
            VALUES('" . $slug . "','" . $title . "','" . $subjectSlug . "','" . $messageSlug . "','" . $time . "','" . $na_count . "')");

    //echo db()->error;die();
    return true;
}

function admin_get_mailautomations($term)
{
    if ($term) {
        return paginate("SELECT * FROM mailautomations WHERE title LIKE '%{$term}'");
    }
    return paginate("SELECT * FROM mailautomations");
}

