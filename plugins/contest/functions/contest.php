<?php

/**
 * @param $contest
 * @return Paginator
 */
function getContestParticipants($contest){
    $id = $contest['id'];
    return paginate("SELECT username,first_name,last_name,gender,avatar,user_id FROM contest_participants p LEFT JOIN users u ON u.id=p.user_id WHERE cid='{$id}'",'all');
}

/**
 * @param $val
 * @param string $type
 * @return bool|int|mixed
 */
function save_music_contest($val, $type = 'fresh')
{
    $expected = array(
        'title' => '',
        'description' => '',
        'code' => '',
        'source' => '',
        'status' => 1,
        'file_path' => '',
        'photo_path' => '',
        'contest_id' => '',
        'link' => '',
    );

    /**
     * @var $title
     * @var $description
     * @var $code
     * @var $source
     * @var $file_path
     * @var $photo_path
     * @var $auto_posted
     * @var $contest_id
     * @var $link
     */
    extract(array_merge($expected, $val));

    $entity_type = 'user';
    $entity_id = get_userid();
    $result = array('result' => true);
    $result = fire_hook('can.post.music', $result, array($entity_type, $entity_id));
    if (!$result['result']) return false;
    $music_file = input_file('music_file');
    if ($music_file) {
        $uploader = new Uploader($music_file, 'audio');
        $uploader->setPath(get_userid() . '/' . date('Y') . '/musics/');
        $uploader->uploadFile();
        $file_path = $uploader->result();
        $status = 1;
        $source = 'upload';
    }
    $time = time();
    $userid = get_userid();
    $music_id = 0;
    $slug = unique_slugger($title);
    if (get_contest_music($slug)) {
        $slug = md5($slug . time());
    }
    $code = mysqli_real_escape_string(db(), htmlentities($code));
    if ($code) {
        $source = 'external';
    }
    if ($type == 'fresh') {
        db()->query("INSERT INTO contest_music (code,contest_id,user_id,status,time,title,slug,source,image,description,file_path) 
VALUES ('{$code}','{$contest_id}','{$userid}','{$status}','{$time}','{$title}','{$slug}','{$source}','{$photo_path}','{$description}','{$file_path}')");
        //echo db()->error;
        $music_id = db()->insert_id;
        fire_hook("creditgift.addcredit.add.contest.music", null, array(get_userid()));
    }

    if ($type == 'update') {
        $id = $val['id'];
        db()->query("UPDATE contest_music SET title='{$title}',source='{$source}', 
         image='{$photo_path}', description='{$description}', file_path='{$file_path}',code='{$code}',status='{$status}',slug='{$slug}' WHERE id='{$id}'");

        $music_id = $id;
        fire_hook("creditgift.addcredit.edit.contest.music", null, array(get_userid()));
    }


    return $music_id;
}

/**
 * @param $val
 * @param string $type
 * @return bool|int|mixed
 */

function save_video_contest($val, $type = 'fresh')
{
    $expected = array(
        'title' => '',
        'description' => '',
        'code' => '',
        'source' => '',
        'status' => 1,
        'file_path' => '',
        'photo_path' => '',
        'contest_id' => '',
        'link' => '',
    );

    /**
     * @var $title
     * @var $description
     * @var $code
     * @var $source
     * @var $file_path
     * @var $photo_path
     * @var $auto_posted
     * @var $contest_id
     * @var $link
     */
    extract(array_merge($expected, $val));

    $entity_type = 'user';
    $entity_id = get_userid();
    $result = array('result' => true);
    $result = fire_hook('can.post.video', $result, array($entity_type, $entity_id));
    if (!$result['result']) return false;
    $video_file = input_file('video_file');
    if ($video_file) {
        $uploader = new Uploader($video_file, 'video');
        $uploader->setPath(get_userid() . '/' . date('Y') . '/videos/')->disableCDN();
        $uploader->uploadVideo();
        $file_path = $uploader->result();
        $status = 0;
    }
    $time = time();
    $userid = get_userid();
    $video = array();
    $link = urlencode($link);
    $video_id = 0;
    $slug = unique_slugger($title);
    if (empty($slug)) $slug = md5(time());
    if (get_contest_video($slug)) {
        $slug = md5($slug . time());
    }

    if ($val['source'] == 'upload') {
        $path_info = pathinfo($file_path);
        $ext = strtolower($path_info['extension']);
        if ($ext == 'mp4') {
            $status = 1;
        }
    }

    if ($type == 'fresh') {
        db()->query("INSERT INTO contest_videos (link,code,contest_id,user_id,status,time,title,slug,source,image,description,file_path) 
VALUES ('{$link}','{$code}','{$contest_id}','{$userid}','{$status}','{$time}','{$title}','{$slug}','{$source}','{$photo_path}','{$description}','{$file_path}')");

        $video_id = db()->insert_id;
        $video = get_contest_video($video_id);
        if ($val['source'] == 'upload') {
            if (config('enable-auto-video-processing')) {
                @video_contest_processing($video);
            }
        }
        fire_hook("creditgift.addcredit.add.contest.video", null, array(get_userid()));
    }

    if ($type == 'update') {
        $id = $val['id'];
        db()->query("UPDATE contest_videos SET link='{$link}',title='{$title}',source='{$source}', 
         image='{$photo_path}', description='{$description}', file_path='{$file_path}',code='{$code}',status='{$status}' WHERE id='{$id}'");

        $video_id = $id;
        $video = get_contest_video($video_id);
        if ($val['source'] == 'upload') {
            if (config('enable-auto-video-processing')) {
                @video_contest_processing($video);
            }
        }
        fire_hook("creditgift.addcredit.edit.contest.video", null, array(get_userid()));
    }


    return $video_id;
}

/**
 * @param $video
 */

function video_contest_processing($video)
{
    $file_path = $video['file_path'];
    if ($file_path and file_exists(path($file_path))) {
        try {
            $status = 0;
            $path_info = pathinfo($file_path);
            $ext = strtolower($path_info['extension']);
            if ($ext == 'mp4') {
                $output_file = $file_path;
            } else {
                $output_file = preg_replace('/\.' . preg_quote($ext, '$/') . '/i', '.mp4', $file_path);
                exec('"' . config('video-ffmpeg-path') . '" -i "' . path($file_path) . '" -vcodec h264 -acodec aac -strict -2 "' . path($output_file) . '"');
            }
            $db = db();
            if (file_exists(path($output_file))) {
                if (path($file_path) != path($output_file)) {
                    delete_file(path($file_path));
                }
                $db->query("UPDATE contest_videos SET file_path = '" . $output_file . "' WHERE id = " . $video['id']);
                if (empty($video['photo_path']) || empty($video['thumbnail'])) {
                    $isset_thumbnail = false;
                    $isset_preview = false;
                    $maxwidth = 480;
                    $video_attributes = get_video_attributes(path($output_file), config('video-ffmpeg-path'));
                    $width = $video_attributes['width'];
                    $height = $video_attributes['height'];
                    if ($width && $height) {
                        $ratio = $width / $height;
                        $modwidth = $width / ($width / $maxwidth);
                        $modheight = round($modwidth / $ratio);
                        $size = $modwidth . 'x' . $modheight;
                        $start = floor($video_attributes['length'] / 2);
                        $frames = 1;
                        if (empty($video['photo_path'])) {
                            $thumbnail_dir = 'storage/uploads/video/photos/';
                            $thumbnail_file = $video['slug'] . '.jpg';
                            @mkdir(path($thumbnail_dir), 0777, true);
                            $thumbnail_path = $thumbnail_dir . $thumbnail_file;
                            exec('"' . config('video-ffmpeg-path') . '" -y  -i "' . path($output_file) . '" -an  -s ' . $size . ' -ss ' . $start . ' -vframes 1 -r 1 "' . path($thumbnail_path) . '"');
                            if (file_exists(path($thumbnail_path))) {
                                $db->query("UPDATE contest_videos SET photo_path = '" . $thumbnail_path . "' WHERE id = " . $video['id']);
                                $isset_thumbnail = true;
                            }
                        } else {
                            $isset_thumbnail = false;
                        }
                        if (empty($video['thumbnail'])) {
                            $preview_dir = 'storage/uploads/video/previews/';
                            $preview_file = $video['slug'] . '.gif';
                            @mkdir(path($preview_dir), 0777, true);
                            $preview_path = $preview_dir . $preview_file;
                            exec('"' . config('video-ffmpeg-path') . '" -y  -i "' . path($output_file) . '" -an  -s ' . $size . ' -ss ' . $start . ' -vframes 240 -r 24 "' . path($preview_path) . '"');
                            if (file_exists(path($preview_path))) {
                                $db->query("UPDATE contest_videos SET thumbnail = '" . $preview_path . "' WHERE id = " . $video['id']);
                                $isset_preview = true;
                            }
                        } else {
                            $isset_preview = true;
                        }
                        if ($isset_thumbnail && $isset_preview) {
                            $status = 1;
                        }
                    }
                } else {
                    $status = 1;
                }
            }
            try {
                if (config('ignore-ffmpeg', false)) {
                    @rename(path($file_path), path($output_file));
                    $db->query("UPDATE contest_videos SET file_path = '" . $output_file . "' WHERE id = " . $video['id']);
                    $status = 1;
                }
                if ($status) {
                    $db->query("UPDATE contest_videos SET status = 1 WHERE id = " . $video['id']);
                    send_notification($video['user_id'], 'video.processed.processed', $video['id']);
                    fire_hook('video.processed.contest', null, array($video, $video['id']));
                }
            } catch (Exception $e) {
                exit($e->getMessage());
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }
}

/**
 * @param $file
 * @return bool
 */
function contest_url_valid_image($file)
{
    $size = getimagesize($file);
    return (strtolower(substr($size['mime'], 0, 5)) == 'image' ? true : false);
}

/**
 * @param $id
 * @return array
 */
function get_contest_photo($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM contest_photos WHERE " . (is_numeric($id) ? "id = " . $id : "slug = '" . $id . "'"));
    $photo = $query->fetch_assoc();
    return $photo;
}

/**
 * @param $id
 * @return array
 */
function get_contest_video($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM contest_videos WHERE " . (is_numeric($id) ? "id = " . $id : "slug = '" . $id . "'"));
    $video = $query->fetch_assoc();
    return $video;
}

/**
 * @param $id
 * @return array
 */

function get_contest_music($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM contest_music WHERE " . (is_numeric($id) ? "id = " . $id : "slug = '" . $id . "'"));
    $music = $query->fetch_assoc();
    return $music;
}

/**
 * @param $val
 * @param string $tp
 * @return mixed
 */
function saveContestPhoto($val, $tp = 'fresh')
{
    $title = sanitizeText($val['title']);
    $description = sanitizeText($val['description']);
    $image = $val['image'];
    $userid = get_userid();
    $time = time();
    $contest_id = $val['contest_id'];
    if ($tp == 'fresh') {
        $query = db()->query("INSERT INTO contest_photos(user_id,contest_id,time,title,description,image) 
VALUES ('{$userid}','{$contest_id}','{$time}','{$title}','{$description}','{$image}')");
        $id = db()->insert_id;
        $slug = $id . uniqid();
        db()->query("UPDATE contest_photos SET slug='{$slug}' WHERE id='{$id}'");
        fire_hook("contest.photo.entries.added", null, $val);
        return $id;
    }

    if ($tp == 'update') {
        $id = $val['id'];
        db()->query("UPDATE contest_photos SET title='{$title}',description='{$description}',image='{$image}' WHERE id='{$id}'");
        fire_hook("contest.photo.entries.edited", null, $val);
        return $id;
    }

}

/** contest photos end **/
/**
 * @param $aid
 * @param $cid
 * @return bool
 */
function deleteContestAnn($aid, $cid, $admin = false)
{
    $uid = get_userid();
    if($admin){
        db()->query("DELETE FROM contest_announcement WHERE id='{$aid}' AND contest_id='{$cid}'");
    }else{
        db()->query("DELETE FROM contest_announcement WHERE user_id='{$uid}' AND id='{$aid}' AND contest_id='{$cid}'");
    }
    return true;
}

/**
 * @param $val
 * @return bool
 */
function saveContestAnnouncement($val)
{
    //echo 'here';
    $user_id = get_userid();
    $headline = sanitizeText($val['headline']);
    $content = sanitizeText($val['content']);
    $atype = $val['atype'];
    $id = $val['aid'];
    $contest_id = sanitizeText($val['contest_id']);
    $time = time();
    $link = "";
    if ($val['link']) {
        $link = urlencode($val['link']);
    }
    if ($atype == 'new') {
        db()->query("INSERT INTO contest_announcement(user_id,contest_id,title,content,time,link) 
VALUES ('{$user_id}','{$contest_id}','{$headline}','{$content}','{$time}','$link')");
        fire_hook("contest.new.announcement",null , array($contest_id));
    }
    if ($atype == 'update') {
        db()->query("UPDATE contest_announcement SET title='{$headline}',content='{$content}',link='{$link}' WHERE id='{$id}'");
    }
    //echo db()->error;die();
    //notify followers about announcement
    return true;
}

/**
 * @param $id
 * @param string $limit
 * @return Paginator
 */
function getContestAnnouncements($id = null, $limit = 'all')
{
    if($id){
        return paginate("SELECT * FROM contest_announcement WHERE contest_id='{$id}' ORDER BY time DESC", $limit);
    }
    return paginate("SELECT * FROM contest_announcement ORDER BY time DESC", $limit);
}

function contestBadgeStatus($contest, $type)
{
    $now = time();
    switch ($type) {
        case 'contest':
            $start_time = $contest['contest_start'];
            $end_time = $contest['contest_end'];
            break;
        case 'entries':
            $start_time = $contest['entries_start'];
            $end_time = $contest['entries_end'];
            break;
        case 'voting':
            $start_time = $contest['voting_start'];
            $end_time = $contest['voting_end'];
            break;

    }
    if ($now > $end_time) {
        //expired
        return ' <span class="badge badge-danger">' . lang("contest::closed") . '</span> ';
    } elseif ($now >= $start_time && $now && $now < $end_time) {
        return '<span class="badge badge-info">' . lang("contest::ongoing") . '</span>';
    } else {
        return '<span class="badge badge-primary">' . lang("contest::not-yet-started") . '</span>';
    }
}

function entryViewsUpdate($contest, $entry)
{
    $type = $contest['type'];
    $slug = $entry['slug'];
    $entry_slug = $slug.$type . '_' . $entry['id'];

    switch ($type) {
        case 'blog':
            if (session_get('entry_view', 0) != $entry_slug) {
                db()->query("UPDATE contest_blog SET views = views + 1 WHERE slug='{$slug}'");
                session_put('entry_view', $entry_slug);
            }
            break;
        case 'photo':
            if (session_get('entry_view', 0) != $entry_slug) {
                db()->query("UPDATE contest_photos SET views = views + 1 WHERE slug='{$slug}'");
                session_put('entry_view', $entry_slug);
            }
            break;
        case 'video':
            if (session_get('entry_view', 0) != $entry_slug) {
                db()->query("UPDATE contest_videos SET views = views + 1 WHERE slug='{$slug}'");
                session_put('entry_view', $entry_slug);
            }
            break;

        case 'music':
            if (session_get('entry_view', 0) != $entry_slug) {
                db()->query("UPDATE contest_music SET views = views + 1 WHERE slug='{$slug}'");
                session_put('entry_view', $entry_slug);
            }
            break;
    }

}

function nextEntryUrl($contest, $entry)
{
    $type = $contest['type'];
    $contest_id = $contest['id'];
    switch ($type) {
        case 'blog':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_blog WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id > {$id} LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
        case 'photo':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_photos WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id > {$id} LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
        case 'video':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_videos WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id > {$id} LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
        case 'music':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_music WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id > {$id} LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
    }
}

/**
 * @param $contest
 * @param $entry
 * @return string
 */
function prevEntryUrl($contest, $entry)
{
    $type = $contest['type'];
    $contest_id = $contest['id'];
    switch ($type) {
        case 'blog':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_blog WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id < {$id} ORDER BY id DESC LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
        case 'photo':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_photos WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id < {$id} ORDER BY id DESC LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
        case 'video':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_videos WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id < {$id} ORDER BY id DESC LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
        case 'music':
            $id = $entry['id'];
            $query = db()->query("SELECT * FROM contest_music WHERE contest_id='{$contest_id}' AND id !='{$id}' AND id < {$id} ORDER BY id DESC LIMIT 1 ");
            if ($query->num_rows > 0) {
                $entry = $query->fetch_assoc();
            }
            return getEntryContestUrl($contest, $entry);
            break;
    }
}


/**
 * @param $contest
 * @return int
 */
function contestCountMyExistingEntries($contest){
    $type = $contest['type'];
    $id = $contest['id'];
    $uid = get_userid();
    switch ($type) {
        case 'blog':
            $sql = "SELECT * FROM contest_blog WHERE contest_id='{$id}' AND user_id='{$uid}'";
            break;
        case 'photo':
            $sql = "SELECT * FROM contest_photos WHERE contest_id='{$id}' AND user_id='{$uid}'";
            break;
        case 'video':
            $sql = "SELECT * FROM contest_videos WHERE contest_id='{$id}' AND user_id='{$uid}'";
            break;

        case 'music':
            $sql = "SELECT * FROM contest_music WHERE contest_id='{$id}' AND user_id='{$uid}'";
            break;
    }
    $q = db()->query($sql);
    return $q->num_rows;
}

/**
 * @param $type
 * @param string $method
 * @param null $user_id
 * @param null $term
 * @param int $limit
 * @return Paginator
 */
function getMyContests($type, $method = 'mine', $user_id = null, $term = null, $limit =10){
    $uid = ($user_id) ? $user_id : get_userid();
    $sql = "";
    switch ($type) {
        case 'blog':
            $sql = "SELECT * FROM contest_blog  ";
            break;
        case 'photo':
            $sql = "SELECT * FROM contest_photos  ";
            break;
        case 'video':
            $sql = "SELECT * FROM contest_videos ";
            break;

        case 'music':
            $sql = "SELECT * FROM contest_music ";
            break;
    }
    $sql .=" WHERE id != 0 ";
    if($method == 'mine'){
        $sql .=" AND user_id='{$uid}'";
    }
    if($term){
        $term = sanitizeText($term);
        $sql .=" AND (title LIKE '%$term%' OR description LIKE '%$term%')";
    }
    $sql .= " ORDER BY time DESC";
    return paginate($sql,$limit);
}

/**
 * @param $contest
 * @param int $limit
 * @param string $filter
 * @param null $term
 * @return Paginator
 */
function getContestEntries($contest, $limit = 10,$filter = 'latest',$term = null)
{
    $type = $contest['type'];
    $id = $contest['id'];
    switch ($type) {
        case 'blog':
            $sql = "SELECT * FROM contest_blog WHERE contest_id='{$id}' ";
            //return paginate(" ORDER  BY time DESC", $limit);
            break;
        case 'photo':
            $sql = "SELECT * FROM contest_photos WHERE contest_id='{$id}' ";
            //return paginate("SELECT * FROM contest_photos WHERE contest_id='{$id}' ORDER  BY time DESC", $limit);
            break;
        case 'video':
            $sql = "SELECT * FROM contest_videos WHERE contest_id='{$id}' ";
            //return paginate("SELECT * FROM contest_videos WHERE contest_id='{$id}' ORDER  BY time DESC", $limit);
            break;

        case 'music':
            $sql = "SELECT * FROM contest_music WHERE contest_id='{$id}' ";
            //return paginate("SELECT * FROM contest_music WHERE contest_id='{$id}' ORDER  BY time DESC", $limit);
            break;
    }
    if($term){
        $term = sanitizeText($term);
        $sql .=" AND (title LIKE '%$term%' OR description LIKE '%$term%')";
    }
    if($filter == 'most-views'){
        $sql .= "  ORDER BY views DESC";
    }elseif ($filter == 'latest'){
        $sql .= " ORDER BY time DESC";
    }elseif ($filter == 'highest-votes'){
        $before_filter_sql = $sql;
        $rep = " WHERE contest_id='{$id}' ";
        $sql = str_replace($rep,"",$sql);
        $q = db()->query($sql);
        if($q->num_rows > 0){
            while($r = $q->fetch_assoc()){
                $entry_id = $r['id'];
                $entry_type = $r['ref_name'];
                $votes = contestEntryVoteCount($entry_type,$entry_id);
                switch ($entry_type){
                    case 'photo_entry_contest':
                        db()->query("UPDATE contest_photos SET votes='{$votes}' WHERE id='{$entry_id}'");
                        break;
                    case 'blog_entry_contest':
                        db()->query("UPDATE contest_blog SET votes='{$votes}' WHERE id='{$entry_id}'");
                        break;
                    case 'music_entry_contest':
                        db()->query("UPDATE contest_music SET votes='{$votes}' WHERE id='{$entry_id}'");
                        break;
                    case 'video_entry_contest':
                        db()->query("UPDATE contest_videos SET votes='{$votes}' WHERE id='{$entry_id}'");
                        break;
                }
            }
        }

        //echo $sql;
        //die();
        $sql = $before_filter_sql." ORDER BY votes DESC";
    }
    //echo $sql;die();
    return paginate($sql,$limit);
}

/**
 * @param $contest
 * @param $entry
 * @return bool|mysqli_result
 */
function deleteContestEntry($contest, $entry)
{
    $type = $contest['type'];
    switch ($type) {
        case 'blog':
            $id = $entry['id'];
            if ($entry['image']) delete_file(path($entry['image']));
            return db()->query("DELETE FROM contest_blog WHERE id='" . $id . "'");
            break;
        case 'photo':
            $id = $entry['id'];
            if ($entry['image']) delete_file(path($entry['image']));
            return db()->query("DELETE FROM contest_photos WHERE id='" . $id . "'");
            break;
        case 'video':
            $video = $entry;
            $id = $entry['id'];
            db()->query("DELETE FROM contest_videos WHERE id='" . $id . "'");
            if ($video['source'] == 'upload') {
                delete_file(path($video['image']));
                delete_file(path($video['file_path']));
            }
            return true;
            break;
        case 'music':
            $music = $entry;
            $id = $entry['id'];
            db()->query("DELETE FROM contest_music WHERE id='" . $id . "'");
            if ($music['source'] == 'upload') {
                delete_file(path($music['image']));
                delete_file(path($music['file_path']));
            }
            return true;
            break;
    }
}

/**
 * @param $entry_type
 * @param $entry_id
 * @param $voter
 */

function contestVoterRemove($entry_type, $entry_id, $voter)
{
    $q = db()->query("SELECT contest_id FROM contest_votes WHERE entry_type='{$entry_type}' AND entry_id='{$entry_id}' AND voter='{$voter}'");
    if($q->num_rows > 0){
        $arr = $q->fetch_assoc();
        $contest_id = $arr['contest_id'];
        db()->query("DELETE FROM contest_votes WHERE entry_type='{$entry_type}' AND entry_id='{$entry_id}' AND voter='{$voter}'");
        $votes = contestEntryVoteCount($entry_type,$entry_id);
        //db()->query("UPDATE contests SET votes='{$votes}' WHERE id='{$contest_id}'");
        switch ($entry_type){
            case 'photo_entry_contest':
                db()->query("UPDATE contest_photos SET votes='{$votes}' WHERE id='{$entry_id}'");
                break;
            case 'blog_entry_contest':
                db()->query("UPDATE contest_blog SET votes='{$votes}' WHERE id='{$entry_id}'");
                break;
            case 'music_entry_contest':
                db()->query("UPDATE contest_music SET votes='{$votes}' WHERE id='{$entry_id}'");
                break;
            case 'video_entry_contest':
                db()->query("UPDATE contest_videos SET votes='{$votes}' WHERE id='{$entry_id}'");
                break;
        }
    }
}

function hasVotedEntryVote($entry_type, $entry_id)
{
    $user_id = get_userid();
    $q = db()->query("SELECT * FROM contest_votes WHERE entry_type='{$entry_type}' AND entry_id='{$entry_id}' AND voter='{$user_id}'");
    return $q->num_rows;
}


function contestEntryVoteCount($entry_type, $entry_id)
{
    $q = db()->query("SELECT * FROM contest_votes WHERE entry_type='{$entry_type}' AND entry_id='{$entry_id}'");
    return $q->num_rows;
}

function contestVoterRegister($contest_id, $entry_type, $entry_id, $voter = null)
{
    $user_id = ($voter) ? $voter : get_userid();
    $time = time();
    db()->query("INSERT INTO  contest_votes(contest_id,entry_type,entry_id,voter,time) 
VALUES ('{$contest_id}','{$entry_type}','{$entry_id}','{$user_id}','{$time}')");
    $voteid = db()->insert_id;

    //we want to update the votes on the contest_$type table
    $votes = contestEntryVoteCount($entry_type,$entry_id);
    switch ($entry_type){
        case 'photo_entry_contest':
            db()->query("UPDATE contest_photos SET votes='{$votes}' WHERE id='{$entry_id}'");
            break;
        case 'blog_entry_contest':
            db()->query("UPDATE contest_blog SET votes='{$votes}' WHERE id='{$entry_id}'");
            break;
        case 'music_entry_contest':
            db()->query("UPDATE contest_music SET votes='{$votes}' WHERE id='{$entry_id}'");
            break;
        case 'video_entry_contest':
            db()->query("UPDATE contest_videos SET votes='{$votes}' WHERE id='{$entry_id}'");
            break;
    }
    //db()->query("UPDATE contests SET votes='{$votes}' WHERE id='{$contest_id}'");
    fire_hook("contest.voted.entry", null, array($contest_id,$entry_type,$entry_id,$user_id));
}

function getContestEntry($contest, $slug)
{
    $type = $contest['type'];
    switch ($type) {
        case 'blog':
            return get_contest_blog($slug);
            break;
        case 'music':
            return get_contest_music($slug);
            break;
        case 'video':
            return get_contest_video($slug);
            break;
        case 'photo':
            return get_contest_photo($slug);
            break;
    }
}

function getEntryContestUrl($contest, $entry)
{
    return contestUrl($contest) . '/entry/' . $entry['slug'];
}

function save_contest_blog($val, $type = 'add', $blog = array())
{
    $contest_id = $val['contest_id'];
    $title = sanitizeText($val['title']);
    $content = $val['content'];
    $slug = unique_slugger($title);
    $content = html_purifier_purify($content);
    $image = '';
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file);
        if ($uploader->passed()) {
            $uploader->setPath('contest/blog/preview/');
            $image = $uploader->resize(700, 500)->result();
        }
    }
    $time = time();
    $user_id = get_userid();
    if ($type == 'add') {
        db()->query("INSERT INTO contest_blog(user_id,contest_id,title,content,slug,image,time) VALUES('{$user_id}','{$contest_id}','{$title}','{$content}','{$slug}','{$image}','{$time}')");
        $blog_id = db()->insert_id;

        fire_hook("contest.blog.added", null, array($val, $blog_id));
        return $blog_id;
    }
    if ($type == 'edit') {
        $id = $blog['id'];
        $image = ($image) ? $image : $blog['image'];
        db()->query("UPDATE contest_blog SET title='{$title}',content='{$content}',image='{$image}' WHERE id='{$id}'");

        fire_hook("contest.blog.added", null, array($val, $id));
        return $id;
    }

}

function get_contest_blog($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM contest_blog WHERE " . (is_numeric($id) ? "id = " . $id : "slug = '" . $id . "'"));
    $blog = $query->fetch_assoc();
    return $blog;
}

function getContestEntryView($contest, $default = 'add', $entry = array(), $list = false)
{

    $type = $contest['type'];
    switch ($type) {
        case 'blog':
            if ($list) return view("contest::lists/home", array('contest' => $contest, 'entry' => $entry));
            if ($default == 'display') return view("contest::entry/display/blog", array('contest' => $contest, 'entry' => $entry));
            return view("contest::entry/blog", array('contest' => $contest, 'entry' => $entry));
            break;
        case 'photo':
            if ($list) return view("contest::lists/home", array('contest' => $contest, 'entry' => $entry));
            if ($default == 'display') return view("contest::entry/display/photo", array('contest' => $contest, 'entry' => $entry));
            return view("contest::entry/photo", array('contest' => $contest, 'entry' => $entry));
            break;
        case 'video':
            if ($list) return view("contest::lists/home", array('contest' => $contest, 'entry' => $entry));
            if ($default == 'display') return view("contest::entry/display/video", array('contest' => $contest, 'entry' => $entry));
            return view("contest::entry/video", array('contest' => $contest, 'entry' => $entry));
            break;
        case 'music':
            if ($list) return view("contest::lists/home", array('contest' => $contest, 'entry' => $entry));
            if ($default == 'display') return view("contest::entry/display/music", array('contest' => $contest, 'entry' => $entry));
            return view("contest::entry/music", array('contest' => $contest, 'entry' => $entry));
            break;
    }
    return null;
}

function joinContest($contest, $user_id = null)
{
    $uid = ($user_id) ? $user_id : get_userid();
    $id = $contest['id'];
    db()->query("DELETE FROM contest_participants WHERE cid='{$id}' AND user_id='{$uid}'");
    $tm = time();
    db()->query("INSERT INTO contest_participants(cid,user_id,time) VALUES ('{$id}','{$uid}','{$tm}')");
    return $id;
}

function canJoinContest($contest)
{
    if (!is_loggedIn()) return false;
    $p = $contest['privacy'];
    if ($p == 1) return true;
    if ($p == 3 and $contest['user_id'] != get_userid()) return false;
    if ($p == 2 and relationship_valid($contest['user_id'], 1)) return true;
}

function isContestParticipant($contest, $user_id = null)
{
    $id = $contest['id'];
    $uid = ($user_id) ? $user_id : get_userid();
    $q = db()->query("SELECT * FROM contest_participants WHERE cid='{$id}' AND user_id='{$uid}'");
    if ($q->num_rows > 0) return true;
    return false;
}

//following start
function contestIsFollowing($id, $uid = null)
{
    $uid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM contest_followers WHERE cid='{$id}' AND user_id='{$uid}'");
    if ($q->num_rows > 0) return true;
    return false;
}

function contestDeleteMyOldFollowing($id, $uid = null)
{
    $uid = ($uid) ? $uid : get_userid();
    db()->query("DELETE FROM contest_followers WHERE user_id='{$uid}' AND cid='{$id}'");
    return true;
}

function newContestFollower($id, $uid = null)
{
    $uid = ($uid) ? $uid : get_userid();
    db()->query("INSERT INTO contest_followers (cid,user_id) VALUES ('{$id}','{$uid}')");
    return db()->insert_id;
}

/**
 * @param $id
 * @return array
 */
function getContestFollowers($id)
{
    $q = db()->query("SELECT * FROM contest_followers WHERE cid='{$id}'");
    $arr = array();
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            $arr[] = $r;
        }
    }
    return $arr;
}

//following end

function contestUrl($contest)
{
    return url('contest/' . $contest['slug']);
}

function contestEmailTemplateReplace($message, $contest)
{
    $message = str_replace('[contest_name]', $contest['name'], $message);
    $message = str_replace('[contest_url]', contestUrl($contest), $message);
    $message = str_replace('[start_time]', date('M j , Y  h:i A', $contest['contest_start']), $message);
    $message = str_replace('[end_time]', date('M j , Y  h:i A', $contest['contest_end']), $message);
    $message = str_replace('[award]', $contest['award'], $message);
    $siteTitle = get_setting("site_title", "Crea8social");
    $message = str_replace('[website_title]', $siteTitle, $message);
    return $message;
}

function contestGetFriendsLists($term)
{
    $friends = get_friends();
    $result = array();
    if ($friends) {
        $friends = implode(',', $friends);
    } else {
        $friends = implode(',', array(0));
    }
    $sql = "SELECT id,username,first_name,last_name,email_address,avatar FROM users
         WHERE username LIKE '{$term}%' OR first_name LIKE '{$term}%' OR last_name LIKE '{$term}%'
         OR email_address LIKE '{$term}%' AND id IN ({$friends}) GROUP BY id";
    //echo $sql;die();
    $q = db()->query($sql);
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            if ($r['id'] == get_userid()) continue;
            $result[] = $r;
        }
    }
    return $result;
}

function getContestsDefaultSubject()
{
    return 'Invitation Letter';
}

function getContestsDefaultMessage()
{
    return "
Hi,

You're invited to join contest [contest_name] Contest. Please find further information at <a href='[contest_url]' title ='[contest_name]' target='_blank'>[contest_url]</a>

Regards,

[website_title]";
}

function get_contest_icon($arr)
{
    $type = $arr['type'];
    switch ($type) {
        case 'photo':
            return 'ion-image';
            break;
        case 'video':
            return 'ion-ios-videocam';
            break;
        case 'blog':
            return 'ion-edit';
            break;
        case 'music':
            return 'ion-ios-musical-notes';
            break;

    }
}

function get_contest_image($contest)
{
    if ($contest['image']) return url_img($contest['image'], 700);
    return img('contest::image/default.jpg');
}

function convertContestTime($time)
{
    return strtotime($time);
}

function save_contest($val, $stype, $contest = array())
{
    $expected = array(
        'name' => '',
        'description' => '',
        'category' => '',
        'type' => 'blog',
        'award' => '',
        'terms' => '',
        'contest_start' => '',
        'contest_end' => '',
        'entries_start' => '',
        'entries_end' => '',
        'voting_start' => '',
        'voting_end' => '',
        'max_entries' => '0', //unlimited
        'who_vote' => '0', //none members can not vote
        'winners' => '1', //winners
        'auto_approve' => '1', //winners
        'entity' => '',
        'privacy' => '',
        'join_fee' => 0,
    );
    /**
     * @var $name
     * @var $description
     * @var $category
     * @var $entity
     * @var $type
     * @var $award
     * @var $terms
     * @var $contest_start
     * @var $contest_end
     * @var $entries_start
     * @var $entries_end
     * @var $voting_start
     * @var $voting_end
     * @var $max_entries
     * @var $who_vote
     * @var $winners
     * @var $auto_approve
     * @var $entity
     * @var $privacy
     * @var $join_fee
     *
     */
    extract(array_merge($expected, $val));

    $image = '';
    if ($contest) {
        $image = $contest['image'];
    }
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file);
        if ($uploader->passed()) {
            $uploader->setPath('contests/preview/');
            $image = $uploader->resize(700, 500)->result();
        }
    }

    $time = time();
    $userid = get_userid();
    $slug = unique_slugger($name);
    $description = html_purifier_purify($description);
    $entity = explode('-', $entity);
    if (count($entity) == 2) {
        $entity_type = $entity[0];
        $entity_id = $entity[1];
    }
    if (!isset($entity_type) || !isset($entity_id)) {
        return false;
    }
    if ($stype == 'new') {
        $status = 1;
        db()->query("INSERT INTO contests (category_id,`type`,award,terms,contest_start,contest_end,entries_start,
entries_end,voting_start,voting_end,who_vote,max_entries,winners,auto_approve,user_id,entity_type,entity_id,`name`,slug,
description,image,status,privacy,update_time,`time`,join_fee) VALUES('{$category}','{$type}','{$award}','{$terms}','{$contest_start}',
'{$contest_end}','{$entries_start}','{$entries_end}','{$voting_start}','{$voting_end}','{$who_vote}','{$max_entries}','{$winners}',
'{$auto_approve}','{$userid}','{$entity_type}','{$entity_id}','{$name}','{$slug}','{$description}','{$image}','{$status}','{$privacy}',
'{$time}','{$time}','{$join_fee}')");

        $contestId = db()->insert_id;
        $contest = get_contest($contestId);
        fire_hook("contest.added", null, array($contestId, $contest));
        fire_hook('plugins.users.category.updater', null, array('contests', $val, $contestId, 'id'));
        return $contestId;
    }

    if ($stype == 'update') {
        $id = $contest['id'];

        db()->query("UPDATE contests SET category_id='{$category}',`type`='{$type}',award='{$award}',terms='{$terms}',
contest_start='{$contest_start}',contest_end='{$contest_end}',entries_start='{$entries_start}',entries_end='{$entries_end}',
voting_start='{$voting_start}',voting_end='{$voting_end}',who_vote='{$who_vote}',max_entries='{$max_entries}',
winners='{$winners}',auto_approve='{$auto_approve}',entity_type='{$entity_type}',entity_id='{$entity_id}',`name`='{$name}',
`slug`='{$slug}',description='{$description}',image='{$image}',privacy='{$privacy}',update_time='{$time}',join_fee='{$join_fee}' WHERE id='{$id}'");

       // print_r($val);
  //echo db()->error;die();
        $contestId = $id;
        $contest = get_contest($contestId);
        fire_hook("contest.updated", null, array($contestId, $contest));
        return $contestId;
    }

}

function get_contest_publisher($contest)
{
    if ($contest['entity_type'] == 'user') {
        $user = find_user($contest['entity_id']);
        $publisher = array(
            'id' => $user['username'],
            'name' => get_user_name($user),
            'avatar' => get_avatar(200, $user)
        );
    } else {
        $publisher = fire_hook('entity.data', array(false), array($contest['entity_type'], $contest['entity_id']));
    }
    return $publisher;
}


function arrange_contest($contest)
{
    $category = get_contest_category($contest['category_id']);
    if ($category) {
        $contest['category'] = $category;
    }
    $contest = fire_hook('contest.arrange', $contest);
    $contest['publisher'] = get_contest_publisher($contest);
    return $contest;
}

function get_contest($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM contests WHERE " . (is_numeric($id) ? "id = " . $id : "slug = '" . $id . "'"));
    $contest = $query->fetch_assoc();
    return $contest ? arrange_contest($contest) : $contest;
}

function is_contest_owner($contest)
{
    if (!is_loggedIn()) return false;
    if ($contest['user_id'] == get_userid()) return true;
    return false;
}

function delete_contest($id)
{
    $contest = get_contest($id);
    if ($contest['image']) delete_file(path($contest['image']));
    //delete contest entries related to this contest
    $entries = getContestEntries($contest,'all');
    foreach($entries->results() as $entry){
        deleteContestEntry($contest,$entry);
    }
    if(plugin_loaded('favorite')){
        try{
            db()->query("DELETE FROM favorites WHERE `type`='contest' AND type_id='{$id}'");
        }catch (Exception $e){

        }
    }
    //delete comments and likes
    db()->query("DELETE FROM comments WHERE `type`='contest' AND type_id='{$id}'");
    db()->query("DELETE FROM likes WHERE `type`='contest' AND type_id='{$id}'");
    //delete followers to this con
    db()->query("DELETE FROM contest_followers WHERE cid='{$id}'");
    //delete patiricpants to this con
    db()->query("DELETE FROM contest_participants WHERE cid='{$id}'");
    //delete votes to this con
    db()->query("DELETE FROM contest_votes WHERE contest_id='{$id}'");
    //delete ann to this contest
    db()->query("DELETE FROM contest_announcement WHERE contest_id='{$id}'");
    //delete delete contest favorites to this contest
    return db()->query("DELETE FROM contests WHERE id='" . $id . "'");
}

function get_contests($type, $category = null, $term = null, $user_id = null, $limit = 10, $filter = 'all', $contest = null, $entity_type = 'user', $entity_id = null)
{
    $limit = isset($limit) ? $limit : 10;
    $sql = "SELECT * FROM contests ";
    $user_id = $user_id ? $user_id : get_userid();
    $sql = fire_hook("use.different.contests.query", $sql, array());
    if ($type == 'mine') {
        $sql .= " WHERE user_id = '" . $user_id . "' ";
        $sql .= $filter == 'featured' ? " AND featured = '1' " : '';
    }elseif ($type == 'friends'){
        $friends_arr = array(0);
        $friends = get_friends();
        $friends_arr = implode(',',$friends);
        $sql .= "WHERE status ='1' AND user_id IN (".$friends_arr.")";
    } elseif ($type == 'premium'){
        $sql .= "WHERE status ='1' AND join_fee != 0";
    } elseif ($type == 'featured'){
        $sql .= "WHERE status ='1' AND featured != 0";
    } elseif ($type == 'closed'){
        $time = time();
        $sql .= "WHERE status ='1' AND contest_end < '{$time}'";
    } elseif ($type == 'following'){
        $sql = "SELECT * FROM contests c LEFT JOIN contest_followers cf ON c.id=cf.cid WHERE cf.user_id='{$user_id}' GROUP BY cf.cid ";
    }
    elseif ($type == 'favorite'){
        $sql = "SELECT * FROM contests c LEFT JOIN favorites f ON c.id=f.type_id WHERE f.user_id='{$user_id}' AND f.type='contest' GROUP BY f.type_id ORDER BY c.time DESC";
        return paginate($sql,$limit);
    } elseif ($type == 'related') {
        $name = $contest['name'];
        $explode = explode(' ', $name);
        $w = '';
        foreach ($explode as $t) {
            $w .= $w ? " OR  (name LIKE '%" . $t . "%' OR descriptionLIKE '%" . $t . "') " : "  (name LIKE '%" . $t . "%' OR description LIKE '%" . $t . "')";
        }
        $contest_id = $contest['id'];
        $privacy_sql = fire_hook('privacy.sql', ' ');
        $sql .= " WHERE (" . $w . ") AND status = '1' AND id != '" . $contest_id . "' AND (" . $privacy_sql . ") ";
        $sql = fire_hook("more.contests.query.filter", $sql, array($entity_type, $entity_id));
    } else {
        if ($term && !$category) {
            $sql .= " WHERE status = 1 AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
        } elseif ($term && $category != 'all') {
            $subCategories = get_contest_parent_categories($category);
            if (!empty($subCategories)) {
                $subIds = array();
                foreach ($subCategories as $cat) {
                    $subIds[] = $cat['id'];
                }
                $subIds = implode(',', $subIds);
                $sql .= " WHERE status = 1 AND (category_id = '" . $category . "' OR category_id IN ({$subIds})) AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
            } else {
                $sql .= " WHERE status = 1 AND category_id = '" . $category . "' AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
            }
        } elseif ($term && $category == 'all') {
            $sql .= " WHERE status = 1 AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
        } elseif ($category && $category != 'all') {
            $sql .= " WHERE status = 1 AND category_id = '" . $category . "'";
        } else {
            $sql .= " WHERE status = '1'";
        }
        $sql .= $filter == 'featured' ? " AND featured = '1' " : '';
        $privacy_sql = fire_hook('privacy.sql', ' ');
        $sql .= " AND (" . $privacy_sql . ") ";
        if ($entity_type && $entity_id) {
            $entity_sql = "entity_type = '" . $entity_type . "' AND entity_id = " . $entity_id;
            $sql .= " AND (" . $entity_sql . ") ";
        }
        $sql = fire_hook("more.contests.query.filter", $sql, array($entity_type, $entity_id));
    }
    $sql = fire_hook('users.category.filter', $sql, array($sql));
    if ($filter == 'top') {
        $sql .= " ORDER BY views desc";
    } else {
        $sql .= " ORDER BY time desc";
    }
    //echo $sql;die();
    return paginate($sql, $limit);
}

function admin_get_contests($term = null, $limit = 10)
{
    $sql = '';

    if ($term) $sql .= " WHERE name LIKE '%" . $term . "%' OR description LIKE '%" . $term ."%'";
    return paginate("SELECT * FROM contests " . $sql . " ORDER BY TIME DESC", $limit);
}

function count_total_contests()
{
    $query = db()->query("SELECT * FROM contests");
    return $query->num_rows;
}

function contest_slug_exists($slug)
{
    $query = db()->query("SELECT COUNT(id) FROM `contests` WHERE  `slug`='" . $slug . "'");
    $result = $query->fetch_row();
    return $result[0] == 0 ? FALSE : TRUE;
}