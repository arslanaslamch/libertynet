<?php
function add_video($val) {
    $expected = array(
        'title' => '',
        'description' => '',
        'privacy' => 1,
        'code' => '',
        'source' => '',
        'status' => 1,
        'entity' => '',
        'category_id' => '',
        'photo_path' => '',
        'auto_posted' => 0,
        'file_path' => '',
        'video_file' => null,
		'generated_thumbnail' => '',
    );

    /**
     * @var $title
     * @var $description
     * @var $privacy
     * @var $code
     * @var $source
     * @var $entity
     * @var $status
     * @var $category_id
     * @var $photo_path
     * @var $auto_posted
     * @var $video_file
	 * @var $generated_thumbnail
     */
    extract(array_merge($expected, $val));

    $entity = explode('-', $entity);
    if (count($entity) == 2) {
        $entity_type = $entity[0];
        $entity_id = $entity[1];
    }
    if (!isset($entity_type) || !isset($entity_type)) {
        return false;
    }
	//without ffmpeg thumbnail
	
	$gen = isset($val['generated_thumbnail']) ? $val['generated_thumbnail'] : '';
	if($gen != '') {
		$image = imagecreatefromstring(base64_decode(str_replace("data:image/png;base64,", "", $gen)));

		$temp_dir = 'storage/tmp'.'/videos/thumbnails';
		$file_name = 'thumb_'.get_userid().'_'.time();
		if(!is_dir($temp_dir)) {
			mkdir($temp_dir, 0777, true);
		} 
		$img = imagepng($image,$temp_dir.'/'.$file_name,9);
		$locale = $temp_dir.'/'.$file_name;
	}else {$locale = '';}
	//without ffmpeg thumbnail

    $result = array('result' => true);

    $result = fire_hook('can.post.video', $result, array($entity_type, $entity_id));
    if (!$result['result']) return false;
    if(!isset($video_file) || !$video_file) {
        $video_file = input_file('video_file');
    }
    if ($video_file) {
        $uploader = new Uploader($video_file, 'video');
        $uploader->setPath(get_userid().'/'.date('Y').'/videos/')->disableCDN();
        $uploader->uploadVideo();
        $file_path = $uploader->result();
        $status = 0;
    }
    $time = time();
    $userid = get_userid();
    $slug = unique_slugger($title);
    if (empty($slug)) $slug = md5(time());
    if (video_exists($slug)) {
        $slug = md5($slug.time());
    }
	if(config('ignore-ffmpeg', true)) {
		$photo_path = $locale;
	}

    db()->query("INSERT INTO videos (auto_posted,title,slug,description,user_id,entity_type,entity_id,photo_path,category_id,source,code,status,file_path,privacy,time) VALUES(
        '{$auto_posted}','{$title}','{$slug}', '{$description}','{$userid}','{$entity_type}','{$entity_id}','{$photo_path}','{$category_id}','{$source}','{$code}','{$status}','{$file_path}','{$privacy}','{$time}'
    )");

    $video_id = db()->insert_id;
    $video = get_video($video_id);
    fire_hook('video.added', null, array($video, $video_id, $auto_posted));
    fire_hook("creditgift.addcredit.addvideo", null, array(get_userid()));
    fire_hook('plugins.users.category.updater', null, array('videos', $val, $video_id));
    return $video;
}

function is_video_owner($video) {
    if (!is_loggedIn()) return false;
    if ($video['user_id'] == get_userid()) return true;
    return false;
}

function save_video($val, $video) {
    $expected = array(
        'title' => '',
        'description' => '',
        'featured' => 0,
        'privacy' => $video['privacy'],
        'category_id' => $video['category_id']
    );
    /**
     * @var $title
     * @var $description
     * @var $featured
     * @var $privacy
     * @var $category_id
     */
    extract(array_merge($expected, $val));
    $video_id = $video['id'];
    db()->query("UPDATE videos SET title='{$title}',description='{$description}',featured='{$featured}',category_id='{$category_id}',privacy='{$privacy}' WHERE id='{$video_id}'");
    fire_hook('video.admin.edited', null, array($video_id));
    fire_hook('video.saved', null, array($video_id, $val));
    fire_hook('plugins.users.category.updater', null, array('videos', $val, $video_id));
    return true;
}

function delete_video($id) {
    $video = get_video($id);
    db()->query("DELETE FROM videos WHERE id='".$id."'");
    if ($video && $video['source'] == 'upload') {
        delete_file(path($video['photo_path']));
        delete_file(path($video['file_path']));
    }
    $query = db()->query("SELECT feed_id FROM feeds WHERE ((type_id='upload-video' AND type_data = ".$id.") OR video = ".$id.") AND photos = '' AND link_details = ''");
    if ($query->num_rows > 0) {
        $feeds = fetch_all($query);
        foreach ($feeds as $feed) {
            remove_feed($feed['feed_id']);
        }
    }
    return true;
}

function video_exists($slug) {
    $query = db()->query("SELECT slug FROM videos WHERE slug='{$slug}' LIMIT 1");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_video($id) {
    $db = db();
    $query = $db->query("SELECT videos.*, ((SELECT COUNT(video_viewers.id) FROM video_viewers WHERE video_viewers.video_id = videos.id AND bot = 0) + videos.view_count) AS views FROM videos WHERE ".(is_numeric($id) ? "id = ".$id : "slug = '".$id."'"));
    $video = $query->fetch_assoc();
    return $video ? $video : false;
}

function get_all_videos($cat, $term, $limit = 10) {

    $sql = "SELECT videos.*, ((SELECT COUNT(video_viewers.id) FROM video_viewers WHERE video_viewers.video_id = videos.id AND bot = 0) + videos.view_count) AS views FROM videos ";
    $sql = fire_hook("use.different.videos.query", $sql, array());
    $where = "";
    if ($cat and $cat != 'all') {
        $where = " category_id='{$cat}' ";
    }
    if ($term) {
        $where .= ($where) ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%' ) " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%' )";
    }
    if ($where) {
        $sql .= "WHERE {$where} ";
        $sql = fire_hook("more.videos.query.filter", $sql, array());
    }
    $sql .= "ORDER BY time desc";
    return paginate($sql, $limit);
}

function get_videos($type, $category = 'all', $term = null, $userid = null, $limit = null, $filter = 'all', $withTitle = false, $entity_type = null, $entity_id = null) {
    $limit = $limit ? $limit : 12;
    $sql = "SELECT videos.*, ((SELECT COUNT(video_viewers.id) FROM video_viewers WHERE video_viewers.video_id = videos.id AND bot = 0) + videos.view_count) AS views FROM videos ";
    $sql = fire_hook("use.different.videos.query", $sql, array());
    $where_clause = "";
    $userid = ($userid) ? $userid : get_userid();
    if ($type == 'mine') {
        $where_clause .= ($where_clause) ? " AND user_id='{$userid}' " : " user_id='{$userid}'";
    }

    if ($type == 'user-profile') {
        $privacy_sql = fire_hook('privacy.sql', ' ');
        $user_profile_sql = " entity_type = 'user' AND user_id = '".$userid."' AND (".$privacy_sql.") ";
        $where_clause .= $where_clause ? " AND (".$user_profile_sql.") " : " (".$user_profile_sql.") ";
    }

    if ($category and $category != 'all') {
        $where_clause .= ($where_clause) ? " AND category_id='{$category}' " : "category_id='{$category}' ";
    }

    if ($filter and $filter == 'featured') {
        $where_clause .= ($where_clause) ? " AND featured='1' " : " featured='1' ";
    }

    if ($term) {
        $where_clause .= ($where_clause) ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%' ) " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%' )";
    }

    if ($type == 'browse') {
        $privacy_sql = fire_hook('privacy.sql', ' ');
        $where_clause .= $where_clause ? " AND (".$privacy_sql.") " : " (".$privacy_sql.") ";
    }

    if (!in_array($filter, array('featured', 'top', 'all'))) {
        $where_clause = $where_clause ? $where_clause : " 1 ";
        $where_clause = fire_hook('video.filter.extend', $where_clause, array($filter));
    }

    if ($entity_type && $entity_id) {
        $entity_sql = "entity_type = '".$entity_type."' AND entity_id = ".$entity_id;
        $where_clause .= $where_clause ? " AND (".$entity_sql.") " : " (".$entity_sql.") ";
    }

    $where_clause = fire_hook('users.category.filter', $where_clause, array($where_clause));
    if ($where_clause) $sql .= " WHERE {$where_clause} ";
    if ($type != 'mine') $sql .= " AND status='1'";
    if ($withTitle) $sql .= " AND title != '' ";
    $sql = fire_hook("more.videos.query.filter", $sql, array());
    if ($filter and $filter == 'top') {
        $sql .= " ORDER BY views desc";
    } else {
        $sql .= " ORDER BY time desc";
    }

    return paginate($sql, $limit);
}

function get_related_videos($video, $limit = 6) {
    $title = $video['title'];
    $explode = explode(" ", $title);
    $video_id = $video['id'];
    $sql = "SELECT videos.*, ((SELECT COUNT(video_viewers.id) FROM video_viewers WHERE video_viewers.video_id = videos.id AND bot = 0) + videos.view_count) AS views FROM videos WHERE id != '{$video_id}'  AND (";
    $where = "";
    foreach ($explode as $t) {
        $where .= ($where) ? " OR title LIKE '%{$t}%' " : "title LIKE '%{$t}%'";
    }
    $sql .= $where.') ORDER B   Y time desc';
    return paginate($sql, $limit);

}

function get_video_owner($video) {
    $result = array('name' => '', 'image' => '', 'link' => '', 'id' => '');
    $entity = fire_hook('entity.info', $video);
    $result['name'] = $entity['name'];
    $result['image'] = $entity['avatar'];
    $result['link'] = url($entity['id']);
    $result['id'] = $entity['id'];
    return fire_hook('get.video.owner', $result, array($video));
}

function get_video_url($video) {
    return url('video/'.$video['slug']);
}

function video_categories_get_all() {
    $db = db();
    $sql = "SELECT * FROM video_categories ORDER BY category_order ASC";
    $query = $db->query($sql);
    $categories = fetch_all($query);
    return $categories;
}

function get_video_categories() {
    $cacheName = "video-categories";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT * FROM video_categories WHERE parent_id='0' ORDER BY category_order ASC");
        $result = fetch_all($db);
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function get_video_category($id) {
    $query = db()->query("SELECT * FROM video_categories WHERE id='{$id}'");
    if ($query) return $query->fetch_assoc();
    return false;
}

function get_video_parent_categories($id) {
    $cacheName = 'video-parent-categories-'.$id;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT * FROM video_categories WHERE parent_id='{$id}' ORDER BY category_order ASC");
        $result = fetch_all($db);
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function update_video_category_order($catId, $no, $parentId = null) {
    db()->query("UPDATE video_categories SET category_order='{$no}' WHERE id='{$catId}'");
    if ($parentId) {
        forget_cache('video-parent-categories-'.$parentId);
    } else {
        forget_cache("video-categories");
    }
    return true;
}

function delete_video_category($category) {
    $id = $category['id'];
    db()->query("DELETE FROM video_categories WHERE id='{$id}'");
    forget_cache('video-categories');
    if ($category['parent_id']) forget_cache('video-parent-categories-'.$category['parent_id']);
    return true;
}

function save_video_category($val, $cat) {
    /**
     * @var $title
     * @var $category
     */
    extract($val);
    $englishValue = $title['english'];
    $slug = $cat['title'];
    foreach ($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        (phrase_exists($langId, $slug)) ? update_language_phrase($slug, $t, $langId) : add_language_phrase($slug, $t, $langId, 'video-category');
    }

    $category_id = $cat['id'];
    db()->query("UPDATE video_categories SET parent_id='{$category}' WHERE id='{$category_id}'");
    fire_hook('video.category.edit', null, array($cat));
    forget_cache('video-categories');
    if ($category) forget_cache('video-parent-categories-'.$category);
    return true;
}

function video_add_category($val) {
    /**
     * @var $title
     * @var $category
     */
    extract($val);
    $titleSlug = 'video_category_'.md5(time().serialize($val)).'_title';
    $englishValue = $title['english'];
    foreach ($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        add_language_phrase($titleSlug, $t, $langId, 'video-category');
    }
    $slug = toAscii($englishValue);
    if (empty($slug)) $slug = md5(time());
    if (video_category_exists($slug)) {
        $slug = md5($slug.time());
    }

    db()->query("INSERT INTO video_categories (title,parent_id,slug) VALUES('{$titleSlug}','{$category}','{$slug}')");

    $insertedId = db()->insert_id;
    fire_hook('video.category.add', null, array($insertedId));
    forget_cache('video-categories');
    if ($category) forget_cache('video-parent-categories-'.$category);
    return true;
}

function video_category_exists($slug) {
    $db = db()->query("SELECT id FROM video_categories WHERE slug='{$slug}' LIMIT 1");
    if ($db and $db->num_rows > 0) return true;
    return false;
}

function is_youtube_video($url, $embed = false) {
    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
        return false;
    } else {
        $url = parse_url($url);
        if (!isset($url['host']) || !isset($url['path']) || ($embed && isset($url['path']) && !preg_match('/\/embed\/.{5,}/', $url['path']))) {
            return false;
        } else {
            $domain = $url['host'];
            if (!preg_match('/(?P<domain>[a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
                return false;
            } else {
                $domain_name = preg_replace('/\.(.*)$/', '', $regs['domain']);
                if ($domain_name != 'youtube') {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }
}

function get_video_attributes($video, $ffmpeg = null) {
    $ffmpeg = $ffmpeg ? $ffmpeg : config('video-ffmpeg-path', 'ffmpeg');
    if (($ffmpeg != 'ffmpeg' && !file_exists($ffmpeg)) || ($ffmpeg == 'ffmpeg' && !file_exists(shell_exec('which ffmpeg 2>&1')) && !file_exists(shell_exec('where ffmpeg 2>&1')))) {
        if ($ffmpeg != 'ffmpeg' && file_exists($ffmpeg) && !is_executable($ffmpeg)) {
            exit('FFMPEG is not executable');
        }
        exit('FFMPEG does not exist in specified path');
    }

    $video_attributes = array(
        'codec' => 'undefined',
        'width' => '0',
        'height' => '0',
        'hours' => '0',
        'mins' => '0',
        'secs' => '0',
        'ms' => '0',
    );
    if (($ffmpeg == 'ffmpeg' && (file_exists(trim(shell_exec('which ffmpeg 2>&1'))) || file_exists(trim(shell_exec('where ffmpeg 2>&1'))))) || ($ffmpeg != 'ffmpeg' && file_exists($ffmpeg) && is_executable($ffmpeg))) {
        $command = $ffmpeg.' -i '.$video.' -f null /dev/null 2>&1';
        $output = shell_exec($command);
        for ($i = 2; $i <= 3; $i++) {
            $regex = '([^,]*),';
            for ($j = 1; $j < $i; $j++) {
                $regex .= '([^,]*),';
            }
            $k = false;
            $regex_sizes = "/Video: ".$regex." ([0-9]{1,4})x([0-9]{1,4})/";
            if (preg_match($regex_sizes, $output, $regs)) {
                $video_attributes['codec'] = $regs [1] ? $regs [1] : null;
                $video_attributes['width'] = $regs [(count($regs) - 2)] ? $regs [(count($regs) - 2)] : null;
                $video_attributes['height'] = $regs [(count($regs) - 1)] ? $regs [(count($regs) - 1)] : null;
                $k = true;
            }
            if ($k) break;
        }
        $regex_duration = "/Duration: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}).([0-9]{1,2})/";
        if (preg_match($regex_duration, $output, $regs)) {
            $video_attributes['hours'] = $regs [1] ? $regs [1] : null;
            $video_attributes['mins'] = $regs [2] ? $regs [2] : null;
            $video_attributes['secs'] = $regs [3] ? $regs [3] : null;
            $video_attributes['ms'] = $regs [4] ? $regs [4] : null;
        }
        $video_attributes['length'] = (float)strtotime('1970-01-01 '.$video_attributes['hours'].':'.$video_attributes['mins'].':'.$video_attributes['secs'].' UTC').'.'.$video_attributes['ms'];
    }
    return $video_attributes;
}

function count_videos() {
    $db = db();
    $num_videos = $db->query("SELECT COUNT(id) FROM videos");
    return $db->error ? 0 : $num_videos->fetch_row()[0];
}

function video_process_all($force = false) {
    if (config('video-encoder') === 'ffmpeg') {
        $db = db();
        $sql = "SELECT videos.*, ((SELECT COUNT(video_viewers.id) FROM video_viewers WHERE video_viewers.video_id = videos.id AND bot = 0) + videos.view_count) AS views FROM videos WHERE source = 'upload'";
        if ($force) {
            $sql .= " AND status IN (0, 1)";
        } else {
            $sql .= " AND status = 0";
        }
        $query = $db->query($sql);
        while ($video = $query->fetch_assoc()) {
            @video_process($video);
        }
    }
}

function video_process($video) {
    $db = db();
    $status = 0;
    $file_path = $video['file_path'];
    $path_info = pathinfo($file_path);
    $output_file = $path_info['dirname'].'/'.$path_info['filename'].'_encoded.mp4';
    $i = 0;
    while (file_exists(path($output_file))) {
        $output_file = $path_info['dirname'].'/'.$path_info['filename'].'_encoded_'.$i.'.mp4';
        $i++;
    }
    $ffmpeg = config('video-ffmpeg-path', 'ffmpeg');
    $vcodec = config('video-ffmpeg-video-codec', 'h264');
    $acodec = config('video-ffmpeg-audio-codec', 'aac');
    $db->query('UPDATE videos SET status = 2 WHERE id = '.$video['id']);
    if (($ffmpeg == 'ffmpeg' && (file_exists(trim(shell_exec('which ffmpeg 2>&1'))) || file_exists(trim(shell_exec('where ffmpeg 2>&1'))))) || ($ffmpeg != 'ffmpeg' && file_exists($ffmpeg) && is_executable($ffmpeg))) {
        if ($file_path and file_exists(path($file_path))) {
            exec('"'.$ffmpeg.'" -y -i "'.path($file_path).'" -vcodec '.$vcodec.' -level:v 3.0 -acodec '.$acodec.' -strict -2 "'.path($output_file).'"');
            if (file_exists(path($output_file)) && filesize(path($output_file))) {
                if (path($file_path) != path($output_file)) {
                    delete_file(path($file_path));
                    $db->query("UPDATE videos SET file_path = '".$output_file."' WHERE id = ".$video['id']);
                }
                if (empty($video['photo_path']) || empty($video['thumbnail'])) {
                    $isset_thumbnail = false;
                    $isset_preview = false;
                    $maxwidth = 480;
                    $video_attributes = get_video_attributes(path($output_file), $ffmpeg);
                    $width = $video_attributes['width'];
                    $height = $video_attributes['height'];
                    if ($width && $height) {
                        $ratio = $width / $height;
                        $modwidth = $width / ($width / $maxwidth);
                        $modheight = round($modwidth / $ratio);
                        $size = $modwidth.'x'.$modheight;
                        $start = floor($video_attributes['length'] / 2);
                        if (empty($video['photo_path'])) {
                            $thumbnail_dir = 'storage/uploads/video/photos/';
                            $thumbnail_file = $video['slug'].'.jpg';
                            @mkdir(path($thumbnail_dir), 0777, true);
                            $thumbnail_path = $thumbnail_dir.$thumbnail_file;
                            exec('"'.$ffmpeg.'" -y  -i "'.path($output_file).'" -an  -s '.$size.' -ss '.$start.' -vframes 1 -r 1 "'.path($thumbnail_path).'"');
                            if (file_exists(path($thumbnail_path))) {
                                $db->query("UPDATE videos SET photo_path = '".$thumbnail_path."' WHERE id = ".$video['id']);
                                $isset_thumbnail = true;
                            }
                        } else {
                            $isset_thumbnail = false;
                        }
                        if (empty($video['thumbnail'])) {
                            $preview_dir = 'storage/uploads/video/previews/';
                            $preview_file = $video['slug'].'.gif';
                            @mkdir(path($preview_dir), 0777, true);
                            $preview_path = $preview_dir.$preview_file;
                            exec('"'.$ffmpeg.'" -y  -i "'.path($output_file).'" -an  -s '.$size.' -ss '.$start.' -vframes 240 -r 24 "'.path($preview_path).'"');
                            if (file_exists(path($preview_path))) {
                                $db->query("UPDATE videos SET thumbnail = '".$preview_path."' WHERE id = ".$video['id']);
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
            } elseif (file_exists(path($output_file))) {
                delete_file(path($output_file));
            }
        }
    }
    if (!$status && config('ignore-ffmpeg', false)) {
        @rename(path($file_path), path($output_file));
        $db->query("UPDATE videos SET file_path = '".$output_file."' WHERE id = ".$video['id']);
        $status = 1;
    }
    if ($status) {
        delete_file(path($file_path));
        $uploader = new Uploader(path($output_file), 'video', false, true);
        $uploader->setPath(preg_replace('/^storage(\/|\\\)uploads(\/|\\\)/', '', $path_info['dirname']).'/');
        $uploader->uploadVideo();
        $file_path = $uploader->result();
        if ($output_file !== $file_path) {
            delete_file(path($output_file));
        }
        $db->query("UPDATE videos SET file_path = '".$file_path."', status = 1 WHERE id = ".$video['id']);
        send_notification($video['user_id'], 'video.processed', $video['id']);
        fire_hook('video.processed', null, array($video, $video['id']));
    } else {
        $db->query('UPDATE videos SET status = 0 WHERE id = '.$video['id']);
    }
    return $status;
}

function video_viewed($video_id) {
    $db = db();
    $user_id = get_userid();
    $user_id = $user_id ? $user_id : 0;
    $ip = get_ip();
    $bot = !isset($_SERVER['HTTP_USER_AGENT']) || isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']) ? 1 : 0;
    $sql = "SELECT COUNT(id) FROM video_viewers WHERE video_id = ".$video_id." AND ((user_id IS NOT NULL AND user_id = ".$user_id.") OR ip = '".$ip."')";
    $query = $db->query($sql);
    $row = $query->fetch_row();
    $viewed = (bool)$row[0];
    if (!$viewed) {
        $sql = "INSERT INTO video_viewers (video_id, user_id, ip, last_view_time, bot) VALUES (".$video_id.", ".$user_id.", '".$ip."', NOW(), ".$bot.")";
        $query = $db->query($sql);
        if ($query) {
            fire_hook('video.viewed.unique', null, array($video_id));
        }
    }
    if ($query) {
        $sql = "UPDATE video_viewers SET last_view_time = NOW() WHERE video_id = ".$video_id." AND ((user_id IS NOT NULL AND user_id = ".$user_id.") OR ip = '".$ip."')";
        $query = $db->query($sql);
    }
    fire_hook('video.viewed');
    return $query ? true : false;
}
