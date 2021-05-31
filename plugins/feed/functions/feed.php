<?php
function add_feed($val, $api = false) {
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
        'link_details' => '',
        'tags' => array(),
		'custom_friends' => array(),
        'location' => '',
        'images' => '',
        'music' => '',
        'video' => '',
        'voice' => '',
        'blog' => '',
        'page' => '',
        'event' => '',
        'group' => '',
        'marketplace',
        'files' => '',
        'userid' => get_userid(),
        'can_share' => 1,
        'auto_post' => false,
        'feeling_type' => '',
        'feeling_text' => '',
        'feeling_data' => '',
        'poll' => '',
        'poll_options' => array(),
        'poll_multiple' => 0,
        'list' => '',
        'list_options' => array(),
        'list_background' => '',
        'list_title' => '',
        'list_icon' => '',
        'list_type' => '',
        'background' => '',
        'gif' => '',
        'enable_post' => '',
        'background_image' => '',
        'background_color' => '',
        'generated_thumbnail' => '',
        'feed_embed_code' => '',
        'feed_embed_type' => '',
    );

    /**
     * @var $to_user_id
     * @var $type
     * @var $type_id
     * @var $type_data
     * @var $privacy
     * @var $tags
	 * @var $custom_friends
     * @var $link_details
     * @var $content
     * @var $entity_id
     * @var $entity_type
     * @var $location
     * @var $images
     * @var $marketplace
     * @var $music
     * @var $video
     * @var $voice
     * @var $blog
     * @var $page
     * @var $group
     * @var $event
     * @var $files
     * @var $userid
     * @var $can_share
     * @var $auto_post
     * @var $feeling_type
     * @var $feeling_text
     * @var $feeling_data
     * @var $poll
     * @var $poll_options
     * @var $poll_multiple
     * @var $background
     * @var $background_color
     * @var $background_image
     * @var $gif
     * @var $list
     * @var $list_options
     * @var $list_background
     * @var $list_title
     * @var $list_icon
     * @var $list_type
     * @var $enable_post
     * @var $generated_thumbnail
     * @var $feed_embed_code
     * @var $feed_embed_type
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
        //check for images and videos upload
        $images_file = input_file('image');
        if (!$auto_post and $images_file and user_has_permission('can-upload-photo-feed')) {
            $images = array();
            $validate = new Uploader(null, 'image', $images_file);
            if ($validate->passed()) {
                foreach ($images_file as $im) {
                    $uploader = new Uploader($im);
                    $path = get_userid().'/'.date('Y').'/photos/posts/';
                    $uploader->setPath($path);
                    if ($uploader->passed()) {
                        $image = $uploader->noThumbnails()->resize()->toDB($entity_type.'-posts', $entity_id, $privacy, null, null, $entity_type, $entity_id)->result();
                        $images[$uploader->insertedId] = $image;
                    } else {
                        $result['status'] = 0;
                        $result['message'] = $uploader->getError();
                        return json_encode($result);
                    }
                }
            } else {
                $result['status'] = 0;
                $result['message'] = $validate->getError();
                return json_encode($result);
            }
            if (!empty($images)) {
                $images = perfectSerialize($images);
            }
        }

        $video_file = input_file('video');
        if ($video_file && !$images && user_has_permission('can-upload-video-feed')) {
            if (!plugin_loaded('video') || !config('video-upload', false) || config('video-encoder') === 'none') {
                app()->config['video-file-types'] = 'mp4';
            }
            if (plugin_loaded('video') && config('video-upload', false) && config('video-encoder') !== 'none') {
                $video_insert = add_video(array(
                    'title' => preg_replace('/\\.[^.\\s]{3,4}$/', '', sanitizeText(input_file('video')['name'])),
                    'description' => $content,
                    'privacy' => $privacy,
                    'source' => 'upload',
                    'entity_type' => $entity_type,
                    'entity_id' => $entity_id,
                    'status' => 0,
                    'video_file' => $video_file,
                    'auto_posted' => 1,
					'generated_thumbnail'=> $generated_thumbnail
                ));
                $video = $video_insert['id'];
            } else {
                $uploader = new Uploader($video_file, 'video');
                $path = get_userid().'/'.date('Y').'/videos/posts/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $video = $uploader->uploadVideo()->toDB($entity_type.'-posts', $entity_id, $privacy, null, null, $entity_type, $entity_id)->result();
                    $video_media_id = $uploader->insertedId;
                }
            }
        }
        $gif_id = '';
        if ($gif) {
            $file_path = get_userid().'/'.date('Y').'/gif/posts/';
            $gif_id = gifImageProcessing($gif, $file_path, 'posts', $entity_type, $entity_id, $privacy);
            if (!$gif_id) {
                $result['status'] = 0;
                $result['message'] = 'Invalid GIF Image';
                return json_encode($result);
            }
        }

        if (!$video && $link_details && plugin_loaded('video')) {
            $link_details = perfectUnserialize($link_details);
            if ($link_details['type'] === 'video' && isset($link_details['code']) && $link_details['code']) {
                $video_add = add_video(array(
                    'title' => sanitizeText($link_details['title']),
                    'description' => mysqli_real_escape_string(db(), sanitizeText($link_details['description'])),
                    'privacy' => $privacy,
                    'photo_path' => $link_details['image'],
                    'source' => 'external',
                    'entity_type' => $entity_type,
                    'entity_id' => $entity_id,
                    'status' => 1,
                    'code' => $link_details['code'],
                    'auto_posted' => 1
                ));
                $link_details = '';
                $video = $video_add['id'];
            } else {
                $link_details = perfectSerialize($link_details);
            }
        }

        $voiceInput = input('voice');
        if ($voiceInput) {
            list($header, $voice) = array_pad(explode(',', $voiceInput), 2, '');
            preg_match('/data\:audio\/(.*?);base64/i', $header, $matches);
            $default_extension = 'webm';
            $extension = isset($matches[1]) ? $matches[1] : $default_extension;
            if (!in_array($extension, array($default_extension))) {
                exit('Invalid Format '.$extension);
            }
            $voice = base64_decode(str_replace(' ', '+', $voice));
            $temp_dir = config('temp-dir', path('storage/tmp')).'/feed/voice';
            $file_name = 'voice_'.get_userid().'_'.time();
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }
            $temp_path = $temp_dir.'/'.$file_name.'.'.$extension;
            file_put_contents($temp_path, $voice);
            $uploader = new Uploader($temp_path, 'audio', false, true);
            if ($uploader->passed()) {
                $path = get_userid().'/'.date('Y').'/voices/feeds/';
                $uploader->setPath($path);
                $voice = $uploader->uploadFile()->toDB('posts')->result();
            } else {
                $result['status'] = 0;
                $result['message'] = $uploader->getError();
                return json_encode($result);
            }
        }

        $f = input_file('file');
        if ($f and user_has_permission('can-share-file-feed')) {
            $uploaded_files = array();
            $validate = new Uploader(null, 'file', $f);
            if ($validate->passed()) {
                foreach ($f as $file) {
                    $uploader = new Uploader($file, 'file');
                    $path = get_userid().'/'.date('Y').'/files/posts/';
                    $uploader->setPath($path);
                    if ($uploader->passed()) {
                        $file = $uploader->uploadFile()->toDB($entity_type.'-posts-files', $entity_id, $privacy, null, null, $entity_type, $entity_id)->result();
                        $uploaded_files[$uploader->insertedId] = array(
                            'path' => $file,
                            'name' => $uploader->sourceName,
                            'extension' => $uploader->extension
                        );
                    } else {
                        $result['status'] = 0;
                        $result['message'] = $uploader->getError();
                        return json_encode($result);
                    }
                }
            } else {
                $result['status'] = 0;
                $result['message'] = $validate->getError();
                return json_encode($result);
            }
            if ($uploaded_files) {
                $files = perfectSerialize($uploaded_files);
            }
        }
        $background_image = input_file('background_image');
        if ($background_image) {
            $uploader = new Uploader($background_image);
            $path = get_userid().'/'.date('Y').'/background/posts/';
            $uploader->setPath($path);
            if ($uploader->passed()) {
                $image = $uploader->noThumbnails()->resize()->toDB($entity_type.'-post-background', $entity_id, $privacy, null, null, $entity_type, $entity_id)->result();
                $background_image = $image;
            } else {
                $result['status'] = 0;
                $result['message'] = $uploader->getError();
                return json_encode($result);
            }
        }

        if (empty($content) and empty($images) and empty($files) and empty($music) and empty($gif) and empty($list) and empty($video) and empty($voice) and empty($type_data) and empty($feeling_text) and empty($feeling_data) and empty($location) and empty($feed_embed_code) and empty($feed_embed_type)) return ($api) ? false : json_encode($result);


        $new_poll_options = array();
        if (config('enable-feed-poll', true) and $poll and user_has_permission('can-create-poll')) {
            foreach ($poll_options as $option) {
                if ($option) {
                    $new_poll_options[] = $option;
                }
            }

            if (count($new_poll_options) < 2) {
                $result['status'] = 0;
                $result['message'] = lang('feed::feed-poll-options-error')."\n";
                return json_encode($result);
            }

            if ($poll_multiple) {
                $poll = 2;
            }
        }

        if (config('enable-feed-list', true) and !empty($list) and user_has_permission('can-create-list')) {
            if (!empty(array_filter($list_options)) and $list_type and $list_title) {
                $list_data = array();
                $list_data['items'] = $list_options;
                $list_data['title'] = $list_title;
                $list_data['background'] = $list_background;
                $list_data['icon'] = $list_icon;
                $list_data['list_type'] = $list_type;

                $list = perfectSerialize($list_data);
            } else {
                $list = null;
            }
        } else {
            $list = null;
        }
        if ($feed_embed_code) {
            if (!$feed_embed_type) {
                $result['message'] = lang("feed::embed-type-needed");
                return json_encode($result);
            }
        }

        $time = time();
        $tags_data = serialize($tags);
		if(isset($custom_friends)) {
		$custom_friends = serialize($custom_friends);
		}else{$custom_friends = '';}
        $userid = get_userid();
        $content = config('enable-feed-text-limit', false) ? substr($content, 0, config('maximum-feed-text-limit', 150)) : $content;
        $content = sanitizeText($content);
        $location = sanitizeText($location);
        $entity_id = sanitizeText($entity_id);
        $entity_type = sanitizeText($entity_type);
        $feeling_text = sanitizeText($feeling_text);
        $feeling = "";
        if ($feeling_text or $feeling_data) {
            if ($feeling_text) {
                $feeling = array(
                    'type' => $feeling_type,
                    'text' => $feeling_text,
                    'data' => $feeling_data
                );

                $feeling = perfectSerialize($feeling);
            }
        }
        $music_colum_sql = empty($music) ? '' : ',music';
        $music_value_sql = empty($music) ? '' : ",'".$music."'";
        $custom_column_sql = '';
        $custom_value_sql = '';
        $custom_column_sql = fire_hook('feed.custom.column.sql', array($custom_column_sql), array($val))[0];

        $custom_value_sql = fire_hook('feed.custom.value.sql', array($custom_value_sql), array($val))[0];
        $userId = get_userid();
       
	   //ranker starts//
		if(plugin_loaded('ranker')) {
			$point = get_post_point();
		}else {$point['point'] = 0;}
        $feed = $db->query("INSERT INTO `feeds` (is_poll,feeling_data,to_user_id,link_details,can_share,user_id,files,tags,custom_friends,entity_id,entity_type,type,type_id,type_data,photos".$music_colum_sql.$custom_column_sql.",video,gif,voice,feed_content,privacy,location,time,background,list,background_image,background_color,point) VALUES('".$poll."', '".$feeling."', '".$to_user_id."', '".$link_details."', '".$can_share."', '".$userid."', '".$files."', '".$tags_data."','".$custom_friends."', '".$entity_id."', '".$entity_type."', '".$type."', '".$type_id."', '".$type_data."', '".$images."'".$music_value_sql.$custom_value_sql.",'".$video."', '".$gif_id."', '".$voice."', '".$content."', '".$privacy."', '".$location."', '".$time."', '".$background."', '".$list."', '".$background_image."' , '".$background_color."','".$point['point']."')");
        if ($feed) {
            session_put(md5($type.$type_id), time());
            if (!$auto_post) {
                session_put(md5('feed'), time());
                session_put(md5('timeline'.get_userid()), time());
            }
            $feed_id = $db->insert_id;
            if ($feed_embed_code && $feed_embed_type) {
                $code = false;
                if ($feed_embed_type == 'twitter') {
                    $code = feed_process_twitter_embed($feed_embed_code, $feed_id);
                } else if ($feed_embed_type == 'tiktok') {
                    $code = feed_process_tiktok_embed($feed_embed_code);
                } else if ($feed_embed_type == 'video') {
                    $code = feed_process_video_embed($feed_embed_code);
                }
                if ($code) {
                    $embedded = $db->query("INSERT INTO `feed_embeds`(`feed_id`, `embed_code`, `embed_type`) VALUES($feed_id, '{$code}', '$feed_embed_type')");
                    if (!$embedded) {
                        echo $db->error;
                        die;
                        exit;
                    }
                }
            }
            
            if ($images) {
                $images = perfectUnserialize($images);
                foreach ($images as $img_id => $path) {
                    $db->query("UPDATE medias SET ref_id = '".$feed_id."', ref_name = 'feed' WHERE id = '".$img_id."'");
                }
            }
            if ($gif) {
                $gif_id = perfectUnserialize($gif_id);
                $db->query("UPDATE medias SET ref_id = '".$feed_id."', ref_name = 'feed' WHERE id = '".$gif_id."'");
            }
            if (isset($video_media_id)) {
                $db->query("UPDATE medias SET ref_id = '".$feed_id."', ref_name = 'feed' WHERE id = '".$video_media_id."'");
            }
            if ($files) {
                $files = perfectUnserialize($files);
                foreach ($files as $file_id => $path) {
                    $db->query("UPDATE medias SET ref_id = '".$feed_id."', ref_name = 'feed' WHERE id = '".$file_id."'");
                }
            }

            //lets add the poll options
            if ($poll) {
                $qs = "INSERT INTO poll_answers (poll_id,answer_text) VALUES ";
                $a = "";
                foreach ($new_poll_options as $option) {
                    $a .= ($a) ? ",('".$feed_id."', '".$option."')" : "('".$feed_id."', '".$option."')";
                }
                $qs .= $a;
                $db->query($qs);
            }

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

function can_post_to_feed($entity_type, $entity_id, $to_user_id) {
    //if ($to_user_id and $to_user_id == get_userid()) return false;
    $result = array('result' => true);

    $result = fire_hook('can.post.feed', $result, array($entity_type, $entity_id, $to_user_id));
    return $result['result'];
}

function get_feed_fields() {
    $sql_fields = "status,is_poll,poll_voters,feeling_data,user_id,feed_id,entity_id,entity_type,type_id,type,type_data,to_user_id,photos,video,voice,files,feed_content,background,privacy, gif,link_details,tags,custom_friends,location,can_share,shared,shared_id,shared_count,edited,time,list,hide_comment,background_image,background_color,hide_reaction";
    $sql_fields = fire_hook("feeds.query.fields", $sql_fields, array($sql_fields));
    return $sql_fields;
}

/**
 * Method to get feeds
 * @param string $type
 * @param string $type_id
 * @param int $limit
 * @param int $offset
 * @param int|bool $update_time
 * @return array
 */
function get_feeds($type = "feed", $type_id = null, $limit = null, $offset = 0, $update_time = false) {
    $limit = ($limit) ? $limit : config('feed-limit', 10);

    $sql = fire_hook("feeds.query", '', array($type, $type_id, $limit, $offset));
    $sql_fields = get_feed_fields();

    if ($type == 'feed') {
        $sql = "SELECT ".$sql_fields." FROM `feeds` WHERE";
        $where_clause = " ((`type` = '".$type."'";
        $where_clause = fire_hook('join.feed.query', $where_clause, array($sql_fields));
        $userid = get_userid();
        $where_clause .= " AND ( (`entity_id` = '".$userid."' AND `entity_type` = 'user') ";

        $allow_relationship = fire_hook("allow.relationhsip.feed", true, array());
        if (plugin_loaded('relationship') && $allow_relationship) {
            $where_clause = fire_hook('system.relationship.method', $where_clause, array('feed', $userid));
        }
        $where_clause .= " ))";
        $where_clause = fire_hook("user.feeds.query", $where_clause, array($type, $type_id, $limit, $offset));
        $where_clause .= ") AND (privacy !='4' AND entity_type !='group' )";
        $where_clause = fire_hook("user.feeds.query.after", $where_clause, array($type, $type_id, $limit, $offset));
    } elseif ($type == 'saved') {
        $saved = get_user_saved('feed');
        $saved[] = 0;
        $saved = implode(', ', $saved);
        $sql = "SELECT ".$sql_fields." FROM `feeds` WHERE feed_id IN (".$saved.")";
    } elseif ($type == 'timeline') {
        $userid = $type_id;
        $privacy_sql = fire_hook('privacy.sql', ' ', array($type));
        $result = fire_hook("feed.exclude.type", array(NULL));
        unset($result[0]);
        $where_clause = !empty($result) ? ' AND type != \''.implode(' AND type != \'', $result).'\'' : '';
        $sql = "SELECT ".$sql_fields." FROM `feeds` WHERE 1 = 1 ".$where_clause." AND (((`entity_id` = '".$userid."' AND `entity_type` = 'user' AND (".$privacy_sql.")) OR to_user_id = '".$userid."')";
        $tag_id = array();
        $q = db()->query("SELECT tag_id FROM user_tags WHERE tagged_id = '".$userid."' AND tag_type = 'post'");
        while ($f = $q->fetch_assoc()) {
            $tag_id[] = $f['tag_id'];
        }
        if ($tag_id) {
            $tag_id = implode(', ', $tag_id);
            $sql .= " OR feed_id IN (".$tag_id.") ";
        }
        $sql .= ')';

        $pinned_posts = get_pinned_feeds();
        $pinned_posts[] = 0;

        $pinned_posts = implode(', ', $pinned_posts);
        $sql .= " AND feed_id NOT IN (".$pinned_posts.")";
    } elseif ($type == 'public') {
        $sql = "SELECT ".$sql_fields." FROM `feeds` WHERE privacy = '1' ";
    }

    $where_clause = isset($where_clause) ? $where_clause : '';
    if (is_loggedIn()) {
        $hide_feeds = implode(', ', get_privacy('hide-feeds', array()));

        if ($hide_feeds) {
            $where_clause .= " AND feed_id NOT IN (".$hide_feeds.") ";
        }

        $most_ignore_users = implode(', ', mostIgnoredUsers());
        if ($most_ignore_users) {
            $where_clause .= " AND (entity_type != 'user' OR (entity_type = 'user' AND entity_id NOT IN (".$most_ignore_users.")))";
        }
    }

    $where_clause = fire_hook('feed.get.sql.where.extend', ($where_clause ? $where_clause : ' '));
    if ($update_time) {
        $where_clause .= " AND time > ".$update_time;
    }

    $order_by = fire_hook('feed.get.sql.order', " GROUP BY feed_id ORDER BY `time` DESC", array($update_time));
    if (!$update_time) {
        $order_by .= " LIMIT ".$offset.", ".$limit;
    }
    $sql .= $where_clause.$order_by;
    $sql = fire_hook('feeds.get.sql', $sql, array($type, $type_id, $limit, $offset, $update_time));
    $query = db()->query($sql);
    if ($query) {
        $results = array();
        while ($fetch = $query->fetch_assoc()) {
            $feed = get_arranged_feed($fetch);
            if ($feed and $feed['status'] == 1) {
                //custom starts
				$uid = get_userid();
				$custom_friends = @unserialize($feed['custom_friends']);
				if($custom_friends) {
					$custom_friends[] .= $feed['user_id'];
					if (in_array($uid, $custom_friends)) $results[] = $feed;
				}else {$results[] = $feed;}
                //custom ends
            } else {
                //think we should delete this
            }
        }

        return $results;
    }
    return array();
}

function get_all_feeds() {
    return paginate("SELECT * FROM feeds ORDER BY time DESC");
}

function get_arranged_feed($fetch) {
    $feed = $fetch;
    $feed['editor'] = array(
        'avatar' => get_avatar(75),
        'id' => get_userid(),
        'type' => 'user'
    );
    if ($fetch['entity_type'] == 'user') {
        $user = find_user($fetch['entity_id'], false);
        if ($user) {
            if (config('feed-user-title', 2) == 1) $user['name'] = $user['username'];
            $feed['publisher'] = $user;

            $feed['publisher']['avatar'] = get_avatar(75, $user);
            $feed['publisher']['url'] = profile_url(null, $user);
        }
    } else {
        $feed['publisher'] = fire_hook('feed.get.publisher', null, array($feed));
    }
    if (!isset($feed['publisher']) or !$feed['publisher']) return false;

    if ($feed['to_user_id']) {
        $user = find_user($fetch['to_user_id'], false);
        $feed['targetUser'] = $user;
    }
    $tags = @unserialize($feed['tags']);
    if ($tags) {
        $tags = array_filter($tags);
        $tag_users = array();
        $tags = implode(', ', $tags);
        $query = db()->query("SELECT id,`username`,`first_name`,`last_name`,avatar FROM `users` WHERE `id` IN(".$tags.")");
        if ($query) {
            while ($fetch = $query->fetch_assoc()) {
                $tag_users[] = $fetch;
            }
        }
        if ($tag_users) {
            $feed['tags-users'] = $tag_users;
            $feed['tagsCount'] = count($tag_users);
        }
    }
    if ($feed['photos']) {
        $photos = @perfectUnserialize($feed['photos']);
        $images = array();
        if ($photos) {
            foreach ($photos as $id => $pPath) {
                $images[$id] = $pPath;
            }
            $feed['images'] = $images;
            if (empty($feed['link_details']) && empty($feed['feed_content']) && empty($feed['images']) && empty($feed['video']) && empty($feed['files'])) $feed['empty'] = true;
        }
    }

    if ($feed['files']) {
        $files = @perfectUnserialize($feed['files']);
        $feed['files'] = ($files) ? $files : '';
    }

    if ($feed['shared']) {
        $feed['shared-feed'] = find_feed($feed['shared_id']);
        if (!$feed['shared-feed']) {
            return false;
        }
        $feed['shared_title'] = lang('feed::shared-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        if ($feed['shared-feed']['photos']) {
            $feed['shared_title'] = lang('feed::shared-photo-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        }
        if ($feed['shared-feed']['video']) {
            $feed['shared_title'] = lang('feed::shared-video-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        }
        if ($feed['shared-feed']['files']) {
            $feed['shared_title'] = lang('feed::shared-file-post', array('name' => "<span data-type='".$feed['shared-feed']['entity_type']."' data-id='".$feed['shared-feed']['entity_id']."' class='preview-card'><a  ajax='true' href='".$feed['shared-feed']['publisher']['url']."'>".$feed['shared-feed']['publisher']['name']."</a></span>"));
        }
    }

    if ($feed['feeling_data']) {
        $feed['feeling_data'] = perfectUnserialize($feed['feeling_data']);
    }

    $feed = fire_hook("feed.arrange", $feed);
    return $feed;
}

function get_feed_publisher($id) {
    $fetch = find_feed($id, false);
    $feed = $fetch;

    if ($fetch['entity_type'] == 'user') {
        $user = find_user($fetch['entity_id'], false);
        if ($user) {
            $feed['publisher'] = $user;
        }
    } else {
        $feed['publisher'] = fire_hook('feed.get.publisher', null, array($feed));
    }
    if (!$feed['publisher']) {
        return false;
    }
    return $feed;
}

/**
 * function to find feed
 *
 * @param int $feed_id
 * @return array
 */
function find_feed($feed_id, $all = true) {
    $query = db()->query("SELECT * FROM  `feeds`  WHERE `feed_id`= ".$feed_id);
    if (!$query) {
        return false;
    }
    $fetch = $query->fetch_assoc();
    if (!$fetch) {
        return false;
    }
    $privacy = $fetch['privacy'];
    if ($fetch['type'] != 'page') {
        $my_blocked_users = array_merge(get_blockedIds(), get_blockerIds());
        if ($my_blocked_users and in_array($fetch['user_id'], $my_blocked_users)) return false;
        $owner_blocked_users = array_merge(get_blockedIds($fetch['user_id']), get_blockerIds($fetch['user_id']));
        if ($owner_blocked_users and in_array(get_userid(), $owner_blocked_users)) return false;
    }
    if ($privacy == 1) {
        return get_arranged_feed($fetch);
    } elseif ($privacy == 2) {
        if (!is_loggedIn()) return false;
        $userid = $fetch['user_id'];
        $users = array($userid);
        $followings = get_following($userid);
        $friends = get_friends($userid);
        $users = array_merge($users, $followings, $friends);
        if (in_array(get_userid(), $users)) return get_arranged_feed($fetch);
        return false;
    } elseif ($privacy == 3) {
        if (get_userid() != $fetch['user_id']) return false;
        return get_arranged_feed($fetch);
    } else {
        $result = array('status' => true, 'feed' => $fetch);
        $result = fire_hook("find.feed", $result);
        if ($result['status']) {
            return get_arranged_feed($fetch);
        }
        return false;
    }
}

function feed_update_privacy($id, $privacy) {
    $feed = find_feed($id, false);
    if (!can_edit_feed($feed)) return false;
    $db = db();
    $db->query("UPDATE feeds SET privacy = '".$privacy."' WHERE feed_id = '".$id."'");
    fire_hook('feed.privacy.update', $id, array($privacy));
	return true;
}

function hide_feed($feed) {
    $hide_feeds = get_privacy("hide-feeds", array());
    if (!in_array($feed, $hide_feeds)) $hide_feeds[] = $feed;
    remove_user_saving('feed', $feed);
    save_privacy_settings(array('hide-feeds' => $hide_feeds));
}

function unhide_feed($feed) {
    $hide_feeds = get_privacy("hide-feeds", array());
    if (in_array($feed, $hide_feeds)) {
        $a = array();
        foreach ($hide_feeds as $f) {
            if ($f != $feed) {
                $a[] = $f;
            }
        }

        save_privacy_settings(array('hide-feeds' => $a));
    };
}

/**
 * function to know if a user can edit feed
 * @param array $feed
 * @return boolean
 */
function can_edit_feed($feed) {
    $user = get_user();
    if (!is_loggedIn()) return false;
    if ($user['id'] == $feed['user_id']) {
        return true;
    }
    if (is_admin() or is_moderator()) return true;

    $result = array('edit' => false);
    $result = fire_hook('feed.edit.check', $result, array($feed));
    return $result['edit'];
}

/**
 * function to know if a user can edit feed
 * @param array $feed
 * @return boolean
 */
function can_delete_feed($feed) {
    $user = get_user();
    if (!is_loggedIn()) return false;
    if ($user['id'] == $feed['user_id'] or $user['id'] == $feed['to_user_id']) {
        return true;
    }
    if (is_admin() or is_moderator()) return true;

    $result = array('delete' => false);
    $result = fire_hook('feed.delete.check', $result, array($feed));
    return $result['delete'];
}

function can_edit_feed_privacy($feed) {
    $result = array('edit' => true);
    $result = fire_hook('feed.edit.privacy.check', $result, array($feed));
    return $result['edit'];
}

function feed_is_owner($feed) {
    if (!is_loggedIn()) return false;
    if (get_userid() != $feed['user_id']) return false;
    return true;
}

function can_share_feed($feed) {
    if (!$feed['can_share'] || !is_loggedIn()) return false;
    if ($feed['shared'] or $feed['privacy'] == 1 or $feed['privacy'] == 4) {
        return true;
    }
    return false;
}

function can_pin_post($feed) {
    $user = get_user();
    if (!is_loggedIn()) return false;

    if ($feed['type'] == 'feed' and $user['id'] == $feed['user_id']) {
        return true;
    }
    //if (is_admin() or is_moderator()) return true;

    $result = array('edit' => false);
    $result = fire_hook('feed.pin.check', $result, array($feed));
    return $result['edit'];
}

function feed_subscribed($feed, $userid = null) {
    return false;
}

function remove_feed($id, $feed = null, $admin = false) {
    $feed = $feed ? $feed : find_feed($id);

    if (!can_delete_feed($feed) && !$admin) {
        return false;
    }

    if (plugin_loaded('comment')) {
        delete_comments('feed', $id);
    }

    if (plugin_loaded('like')) {
        delete_likes('feed', $id);
    }

    $db = db();

    $db->query("DELETE FROM user_tags WHERE tag_id = '".$id."' AND tag_type = 'post'");

    $preserve_media = fire_hook('media.preserve.types', array('profile-avatar', 'profile-cover'));

    $query = $db->query("SELECT * FROM medias WHERE ref_name = 'feed' AND ref_id = '".$id."' AND type NOT IN ('".implode('\', \'', $preserve_media)."')");
    while ($row = $query->fetch_assoc()) {
        delete_file($row['path']);
    }
    $db->query("DELETE FROM medias WHERE ref_name = 'feed' AND ref_id = '".$id."' AND type NOT IN ('".implode('\', \'', $preserve_media)."')");

    fire_hook('feed.delete', null, array($id, $feed));

    $db->query("DELETE FROM `feeds` WHERE `feed_id` = '".$id."' OR shared_id = '".$id."'");

    if (is_numeric($feed['video'])) {
        delete_video($feed['video']);
    }

    $db->query("DELETE FROM feed_pinned WHERE feed_id = '".$id."'");
    forget_cache('feed-pinned');

    feed_delete_embed($id);

    return true;
}

function delete_posts($type, $id) {
    $db = db()->query("SELECT * FROM feeds WHERE type = '".$type."' AND type_id = '".$id."'");
    while ($feed = $db->fetch_assoc()) {
        remove_feed($feed['feed_id'], $feed);
    }
    return true;
}

function save_feed($id, $text) {
    $feed = find_feed($id);
    if (!can_edit_feed($feed)) return false;
    $text = config('enable-feed-text-limit', false) ? substr($text, 0, config('maximum-feed-text-limit', 150)) : $text;
    $text = sanitizeText($text);
    db()->query("UPDATE `feeds` SET `feed_content` = '".$text."', `edited` = '1' WHERE `feed_id` = '".$id."'");
    return true;
}

function share_feed($id, $toUser = false, $entityId = "", $entityType = "user") {
    $feed = find_feed($id, false);
    if (!$feed) {
        return 0;
    }
    $entityId = $entityId ? $entityId : get_userid();
    $count = $feed['shared_count'] + 1;
    if ($feed['shared']) {
        $id = $feed['shared_id'];
        $shared_feed = find_feed($id, false);
        $count = $shared_feed['shared_count'] + 1;
    }
    //update this feed shared count
    db()->query("UPDATE `feeds` SET `shared_count` = '".$count."' WHERE `feed_id` = '".$id."'");

    //insert new record
    $userid = get_userid();
    $time = time();
    db()->query("INSERT INTO `feeds` (user_id, entity_id, entity_type, type, shared, shared_id, time, privacy) VALUES ('".$userid."', '".$entityId."', '".$entityType."', 'feed', '1', '".$id."', '".$time."', '1')");
    $insert_id = db()->insert_id;
    if ($toUser) {
        db()->query("UPDATE `feeds` SET `to_user_id` = '".$toUser."' WHERE `feed_id` = '".$insert_id."'");
        if ($entityType == "user") {
            send_notification($toUser, 'post-on-timeline', $id);
        }
    }
    fire_hook('share.feed', null, array($feed));
    return ($feed['shared']) ? '' : $count;
}

function format_feed_content($content) {
    $length = config('maximum-feed-text-display', 500);
    $more = $length != 'all';
    return output_text($content, array('length' => config('maximum-feed-text-display', 500), 'more' => $more));
}

function feed_process_link($link) {
    $result = false;
    //first make use of embera
    require_once(path("includes/libraries/embed/1x/autoloader.php"));
    try {
        $headers = array(
            'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
        );
        if (is_loggedIn()) {
            $user = get_user();
            $cookie = 'sv_loggin_username='.$user['id'].';sv_loggin_password='.$user['password'];
        } else {
            $cookie = '';
        }
        $embed = Embed\Embed::create($link, array(
            'minImageWidth' => 50,
            'minImageHeight' => 50,
            "resolver" => array(
                "options" => array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_REFERER => url(),
                    CURLOPT_COOKIE => $cookie,
                    CURLOPT_COOKIESESSION => true,
                    CURLOPT_COOKIEJAR => 'tmp',
                    CURLOPT_COOKIEFILE => path('storage/tmp')
                )
            )
        ));
    } catch (Exception $e) {
        return false;
    }
    if ($embed) {
        $image_a = $embed->getImage();
        $path_info = pathinfo($image_a);
        if (!isset($path_info['extension']) || !in_array($path_info['extension'], array('jpg', 'jpeg', 'png'))) {
            $context = stream_context_create(array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ),
                    'http' => array(
                        'method' => 'GET',
                        'header' => 'Referer: '.url()."\r\n".'Origin: '.url()."\r\n".'Cookie:'.$cookie,
                        'ignore_errors' => true,
                    )
                )
            );
            $link_content = file_get_contents($link, false, $context);
            if($link_content) {
                //$link_content = preg_replace('/<(\w+):(\w+)/', '<$1 data-namespace = "$2"' , $link_content);
                libxml_use_internal_errors(true);
                $domdocument = new DOMDocument();
                $domdocument->loadHTML($link_content);
                $og_image = null;
                foreach ($domdocument->getElementsByTagName('meta') as $meta_tag) {
                    if ($meta_tag->getAttribute('property') == 'og:image') {
                        $og_image = $meta_tag->getAttribute('content');
                        break;
                    }
                }
                $first_img = null;
                foreach ($domdocument->getElementsByTagName('img') as $img_tag) {
                    $first_img = $img_tag->getAttribute('src');
                    break;
                }
                $image_a = $og_image ? $og_image : $first_img;
            }
        }
        $images = $embed->getImages();
        if ($images) {
            foreach ($images as $image) {
                $image = isset($image['url']) ? $image['url'] : $image;
                $path_info = pathinfo($image);
                if (isset($path_info['extension']) && in_array($path_info['extension'], array('jpg', 'jpeg', 'png'))) {
                    $image_b = $image;
                    break;
                }
            }
            foreach ($images as $image) {
                $image = isset($image['url']) ? $image['url'] : $image;
                $link_photos_dir = url().'storage/uploads/link/photos/';
                if (preg_match('/^'.preg_quote($link_photos_dir, '/').'/i', $image) && isset($path_info['extension']) && in_array($path_info['extension'], array('jpg', 'jpeg', 'png'))) {
                    $image_c = $image;
                    break;
                }
            }
            $image = isset($image_c) ? $image_c : (isset($image_b) ? $image_b : ($image_a ? $image_a : $images[0]));
        } else {
            $image = $image_a;
        }

        if ($image) {
            $uploader = new Uploader($image, 'image', false, true, true);
            if ($uploader->passed()) {
                $uploader->setPath('link/photos/');
                $image = $uploader->resize(600)->result();
                $image = url($image);
            } else {
                //exit($uploader->getError());
            }
        }
        $code = $embed->code;
        if (isSecure()) {
            $code = (preg_match("#https://#", $link)) ? $code : '';
        }
        $result = array(
            'type' => $embed->type,
            'title' => $embed->title,
            'description' => $embed->description,
            'link' => $embed->url,
            'image' => $image,
            'code' => $code,
            'provider' => $embed->providerName,
            'provider-url' => $embed->providerUrl,
            'imageWidth' => $embed->imageWidth
        );
    }
    if (preg_match('/^http(s?):\/\/(www\.)?tiktok\.com\//', $result['link'])) {
        $response = json_decode(fetch_api('https://www.tiktok.com/oembed?url=' . $result['link']));
        if (isset($response->type)) {
            $result['type'] = $response->type;
            $result['title'] = $response->title;
            $result['image'] = $response->thumbnail_url;
            $result['code'] = $response->html;
            $result['provider'] = $response->provider_name;
            $result['provider-url'] = $response->provider_url;
            $result['imageWidth'] = $response->thumbnail_width;
            $result['imageHeight'] = $response->thumbnail_height;
        }
    } elseif (preg_match('/^http(s?):\/\/(www\.)?twitter\.com\//', $result['link'])) {
        $response = json_decode(fetch_api('https://publish.twitter.com/oembed?url=' . $result['link']));
        if (isset($response->type)) {
            $result['type'] = $response->type;
            $result['title'] = $response->author_name;
            $result['code'] = $response->html;
            $result['provider'] = $response->provider_name;
            $result['provider-url'] = $response->provider_url;
        }
    } elseif ((!(($result['type'] == 'link' || (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') && preg_match('/^http:\/\//i', $result['link'])) && !preg_match('/^(https?\:\/\/)?([^\.]{1,63}\.)?(youtube\.com|youtu\.?be|vineo\.com|dailymotion\.com|'.preg_quote(getHost().getBase().'video', '/').')\/(.+)$/i', $result['link'])) && !$result['code']) || $result['type'] == 'video') {
        $result = video_url_details($result);
    }
    //$result['code'] = html_purifier_purify($result['code']);
    return $result;
}

function pin_feed($feed) {
    $type = $feed['type'];
    $type_id = $feed['type_id'];
    $feed_id = $feed['feed_id'];
    if ($type == 'feed') {
        $type = 'user';
        $type_id = $feed['entity_id'];
    }

    $check = db()->query("SELECT feed_id FROM feed_pinned WHERE type = '".$type."' AND type_id = '".$type_id."' AND feed_id = '".$feed_id."'");
    if ($check->num_rows) {
        //we are removing this pin
        db()->query("DELETE FROM feed_pinned WHERE type = '".$type."' AND type_id = '".$type_id."'");
    } else {
        db()->query("DELETE FROM feed_pinned WHERE type = '".$type."' AND type_id = '".$type_id."'");
        db()->query("INSERT INTO feed_pinned (type,type_id,feed_id)VALUES('".$type."', '".$type_id."', '".$feed_id."')");
    }
    forget_cache('feed-pinned');
    return true;
}

function is_feed_pinned($id) {
    $feeds = get_pinned_feeds();
    if (in_array($id, $feeds)) return true;
    return false;
}

function get_pinned_feed($type, $type_id) {
    $db = db()->query("SELECT feed_id FROM feed_pinned WHERE type = '".$type."' AND type_id = '".$type_id."' LIMIT 1");
    if ($db->num_rows) {
        $f = $db->fetch_assoc();
        return find_feed($f['feed_id']);
    }
    return false;
}

function get_pinned_feeds() {
    $cache_name = "feed-pinned";
    if (cache_exists($cache_name)) {
        return get_cache($cache_name);
    } else {
        $query = db()->query("SELECT feed_id FROM feed_pinned");
        $a = array();
        while ($fetch = $query->fetch_assoc()) {
            $a[] = $fetch['feed_id'];
        }
        set_cacheForever($cache_name, $a);
        return $a;
    }
}

function count_total_feeds() {
    $q = db()->query("SELECT feed_id FROM feeds");
    return $q->num_rows;
}

function count_posts_in_month($n, $year) {
    $q = db()->query("SELECT * FROM feeds WHERE YEAR(timestamp) = ".$year." AND MONTH(timestamp) = ".$n);
    return $q->num_rows;
}

function get_feelings_list() {
    return array(
        'listening-to',
        'watching',
        'feeling',
        'thinking-about',
        'reading',
        'eating',
        'drinking',
        'celebrating',
        'traveling-to',
        'exercising',
        'meeting',
        'playing',
        'looking-for',
    );
}

function get_poll_answers($id) {
    $cache_name = "poll-answers-".$id."";
    if (cache_exists($cache_name)) {
        return get_cache($cache_name);
    } else {
        $query = db()->query("SELECT answer_id,answer_text,voters FROM poll_answers WHERE poll_id = '".$id."'");
        $results = fetch_all($query);
        set_cacheForever($cache_name, $results);
        return $results;
    }
}

function has_submitted_poll($poll_id) {
    $userid = get_userid();
    $submits = poll_submitters($poll_id);
    if (in_array($userid, $submits)) return true;
    return false;
}

function poll_submitters($poll_id) {
    $cache_name = "poll-submit-".$poll_id;
    if (cache_exists($cache_name)) {
        return get_cache($cache_name);
    } else {
        $query = db()->query("SELECT user_id FROM poll_results WHERE poll_id = '".$poll_id."'");
        $a = array();
        while ($fetch = $query->fetch_assoc()) {
            $a[] = $fetch['user_id'];
        }
        set_cacheForever($cache_name, $a);
        return $a;
    }
}

function feed_submit_poll($val, $feed) {
    $userid = get_userid();
    /**
     * @var $poll_id
     * @var $answers
     * @var $answer
     */
    extract($val);

    if ($feed['is_poll'] == 1) {
        //single choice poll
        $option = $answer;

        $d = db()->query("SELECT * FROM poll_results WHERE user_id = '".$userid."' AND poll_id = '".$poll_id."' LIMIT 1");
        if ($d->num_rows < 1) {
            db()->query("INSERT INTO poll_results(user_id,poll_id,answer_id)VALUES('".$userid."', '".$poll_id."', '".$option."')");

            db()->query("UPDATE feeds SET poll_voters = poll_voters + 1 WHERE feed_id = '".$poll_id."'");
            db()->query("UPDATE poll_answers SET voters = voters + 1 WHERE answer_id = '".$option."'");

            forget_cache("poll-submit-".$poll_id);
            forget_cache("poll-answers-".$poll_id."");
            forget_cache("poll-answer-users-".$option."");
            in_poll_answer($option);//for quick refresh
        } else {
            $result = $d->fetch_assoc();
            $rId = $result['answer_id'];

            db()->query("UPDATE poll_answers SET voters = voters - 1 WHERE answer_id = '".$rId."'");
            db()->query("DELETE FROM poll_results WHERE user_id = '".$userid."' AND poll_id = '".$poll_id."'");

            db()->query("INSERT INTO poll_results(user_id,poll_id,answer_id)VALUES('".$userid."', '".$poll_id."', '".$option."')");
            db()->query("UPDATE poll_answers SET voters = voters + 1 WHERE answer_id = '".$option."'");

            forget_cache("poll-submit-".$poll_id);
            forget_cache("poll-answers-".$poll_id."");
            forget_cache("poll-answer-users-".$rId."");
            in_poll_answer($rId);
            forget_cache("poll-answer-users-".$option."");
            in_poll_answer($option);//for quick refresh
        }
    } else {
        //multiple choice poll
        $poll_answers = get_poll_answers($feed['feed_id']);
        foreach ($poll_answers as $poll_answer) {
            $option = $poll_answer['answer_id'];
            if (isset($answers[$poll_answer['answer_id']])) {
                //ok answer is selected
                if (!in_poll_answer($option)) {
                    //we act now to add the answer and the user
                    db()->query("INSERT INTO poll_results(user_id,poll_id,answer_id)VALUES('".$userid."', '".$poll_id."', '".$option."')");

                    db()->query("UPDATE feeds SET poll_voters = poll_voters + 1 WHERE feed_id = '".$poll_id."'");
                    db()->query("UPDATE poll_answers SET voters = voters + 1 WHERE answer_id = '".$option."'");

                    forget_cache("poll-submit-".$poll_id);
                    forget_cache("poll-answers-".$poll_id."");
                    forget_cache("poll-answer-users-".$option."");
                    in_poll_answer($option);//for quick refresh
                }
            } else {
                //its not selected
                if (in_poll_answer($option)) {
                    //we act now to remove user
                    db()->query("UPDATE feeds SET poll_voters = poll_voters - 1 WHERE feed_id = '".$poll_id."'");
                    db()->query("UPDATE poll_answers SET voters = voters - 1 WHERE answer_id = '".$option."'");
                    db()->query("DELETE FROM poll_results WHERE user_id = '".$userid."' AND poll_id = '".$poll_id."' AND answer_id = '".$option."'");
                    forget_cache("poll-answers-".$poll_id."");
                    forget_cache("poll-answer-users-".$option."");
                    in_poll_answer($option);//for quick refresh
                }
            }
        }
    }

    return true;
}

function get_num_poll_voter_pages($id, $limit) {
    $query = db()->query("SELECT COUNT(poll_id) FROM poll_results WHERE answer_id = ".$id);
    $row = $query->fetch_row();
    $total_records = $row[0];
    $total_pages = ceil($total_records / $limit);
    return $total_pages;
}

function get_poll_answers_user($id, $limit = 3, $page = 1) {
    $start_from = ($page - 1) * $limit;
    $voters = db()->query("SELECT id, first_name, last_name, username, avatar FROM poll_results INNER JOIN users ON poll_results.user_id = users.id WHERE answer_id = '".$id."' LIMIT ".$start_from.", ".$limit);
    return fetch_all($voters);
}

function get_answer_users($id) {
    $cache_name = "poll-answer-users-".$id."";
    if (cache_exists($cache_name)) {
        return get_cache($cache_name);
    } else {
        $a = array();
        $db = db()->query("SELECT user_id FROM poll_results WHERE answer_id = '".$id."'");
        while ($fetch = $db->fetch_assoc()) {
            $a[] = $fetch['user_id'];
        }
        set_cacheForever($cache_name, $a);
        return $a;
    }
}

function in_poll_answer($id) {
    $ids = get_answer_users($id);
    $userid = get_userid();
    if (in_array($userid, $ids)) return true;
    return false;
}

function get_page_title($type_id) {
    $query = db()->query("SELECT * FROM pages where page_id = $type_id");
    return fetch_all($query);
}

function video_url_details($result) {
    $id = '';
    $link = isset($result['link']) ? $result['link'] : '';
    $title = isset($result['title']) ? $result['title'] : '';
    $description = isset($result['description']) ? $result['description'] : '';
    $image = isset($result['image']) ? $result['image'] : img('images/logo.png');
    $provider_name = isset($result['provider_name']) ? $result['provider_name'] : '';
    $provider_url = isset($result['provider_url']) ? $result['provider_url'] : '';
    $embed_code = isset($result['code']) ? $result['code'] : '';
    $link_details = parse_url($link);
    $scheme = isset($link_details['scheme']) ? $link_details['scheme'].'://' : '';
    $host = isset($link_details['host']) ? $link_details['host'] : '';
    $path = isset($link_details['path']) ? $link_details['path'] : '';
    $query = isset($link_details['query']) ? $link_details['query'] : '';
    $context = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            ),
            'http' => array(
                'method' => 'GET',
                'header' => 'Referer: '.url()."\r\n".'Origin: '.url()."\r\n",
                'ignore_errors' => true,
            )
        )
    );
    if (preg_match('/youtube\.com$/i', $host) || preg_match('/youtu\.be$/i', $host)) {
        $result['type'] = 'video';
        if (preg_match('/youtube\.com$/i', $host)) {
            parse_str($query, $params);
            $id = isset($params['v']) ? $params['v'] : $id;
        }
        if (preg_match('/youtu\.be$/i', $host)) {
            $link = preg_replace('/www\.youtu\.be$/i', 'youtu.be', $link);
            $host = 'www.youtube.com';
            $id = trim($path, '/');
        }
        $api_url = 'https://www.googleapis.com/youtube/v3/videos?id='.$id.'&part=snippet&fields=items(snippet(title,description,thumbnails))&key='.config('google-api-key');
        $response = @file_get_contents($api_url, false, $context);
        if (strpos($http_response_header[0], '200 OK')) {
            $data = json_decode($response)->items[0]->snippet;
            $title = $data->title;
            $description = $data->description;
            $image = $data->thumbnails->high->url;
        }
        $provider_name = 'YouTube';
        $provider_url = 'https://www.youtube.com';
        $embed_url = 'https://www.youtube.com/embed/'.$id;
        $embed_code = '<iframe title="YouTube video player" class="youtube-player" type="text/html" src="'.$embed_url.'" frameborder="0" allowfullscreen></iframe>';
    } elseif (preg_match('/vimeo\.com$/i', $host)) {
        $result['type'] = 'video';
        $id = trim($path, '/');
        $api_url = 'https://vimeo.com/api/oembed.json?url=https%3A//vimeo.com/'.$id;
        $response = @file_get_contents($api_url, false, $context);
        if (strpos($http_response_header[0], '200 OK')) {
            $data = json_decode($response);
            $title = $data->title;
            $description = $data->description;
            $image = $data->thumbnail_url;
            $provider_name = $data->provider_name;
            $provider_url = $data->provider_url;
            $embed_url = 'https://player.vimeo.com/video/'.$id;
            $embed_code = $data->html;
        }
    } elseif (preg_match('/dailymotion\.com$/i', $host)) {
        $result['type'] = 'video';
        $params = explode('/', trim($path, '/'));
        $id = isset($params[1]) ? $params[1] : $id;
        $api_url = 'https://api.dailymotion.com/video/'.$id.'?fields=title,description,thumbnail_url,embed_html,embed_url';
        $response = @file_get_contents($api_url, false, $context);
        if (strpos($http_response_header[0], '200 OK')) {
            $data = json_decode($response);
            $title = $data->title;
            $description = $data->description;
            $image = $data->thumbnail_url;
            $provider_name = 'Dailymotion';
            $provider_url = 'https://www.dailymotion.com';
            $embed_url = $data->embed_url;
            $embed_code = $data->embed_html;
        }
    } elseif (preg_match('/xvideos\.com$/i', $host)) {
        $result['type'] = 'video';
        $params = explode('/', trim($path, '/'));
        $id = isset($params[0]) ? preg_replace('/[^0-9]/', '', $params[0]) : $id;
        $embed_url = 'https://www.xvideos.com/embedframe/'.$id;
        $embed_code = '<iframe src="'.$embed_url.'" frameborder=0 scrolling=no allowfullscreen=allowfullscreen></iframe>';
    } elseif (preg_match('/^(https?\:\/\/)?([^\.]{1,63}\.)?('.preg_quote(getHost().getBase(), '/').')(.+)/i', $link)) {
        if (plugin_loaded('video')) {
            $video = null;
            $parsed_link = parse_url($link);
            $params = explode('/', trim($parsed_link['path'], '/'));
            $type = isset($params[0]) ? $params[0] : '';
            if ($type == 'feed') {
                $feed_id = isset($params[1]) ? $params[1] : '';
                $feed = find_feed($feed_id);
                if ($feed && isset($feed['videoDetails'])) {
                    $id = $feed['videoDetails']['id'];
                    $video = get_video($id);
                }
            } elseif ($type == 'video') {
                $id = isset($params[1]) ? $params[1] : '';
                $video = get_video($id);
            }
            if ($video) {
                $result['type'] = 'video';
                $title = $video['title'];
                $description = $video['description'];
                $image = url_img($video['photo_path'], 920);
                $provider_name = config('site-title');
                $provider_url = url();
                $embed_url = url_to_pager('play-video').'?link='.$video['file_path'].'&id='.$video['id'].'&photo='.$video['photo_path'].'&height=430';
                $embed_code = isset($video['code']) && $video['code'] ? $video['code'] : '<iframe id="feed-video-embed-'.$video['id'].' class="player" src="'.$embed_url.'" frameborder="0" allowfullscreen></iframe>';
            }
        }
    }
    if (isset($image)) {
        $result['title'] = $title;
        $result['description'] = $description;
        $result['image'] = $image;
        $result['provider_name'] = $provider_name;
        $result['provider_url'] = $provider_url;
        $result['code'] = $embed_code;
    }
    return $result;
}

function get_feed($where_clause) {
    $query = db()->query("SELECT * FROM feeds WHERE $where_clause");
    $results = fetch_all($query);
    return $results;
}

function get_feed_list() {
    $query = db()->query("SELECT * FROM feed_lists");
    $results = fetch_all($query);
    return $results;
}

function find_list($id) {
    $query = db()->query("SELECT * FROM feed_lists WHERE id ='{$id}'");
    $result = $query->fetch_assoc();
    if ($result) {
        return $result;
    }
    return false;
}

function delete_list($id) {
    $query = db()->query("DELETE FROM feed_lists WHERE id ='{$id}'");
}

function list_add($val) {
    $expected = array(
        'title' => '',
        'icon' => ''
    );

    /**
     * @var $title
     * @var $icon
     */
    extract(array_merge($expected, $val));
    $titleSlug = "feed_list_".md5(time().serialize($val)).'_title';
    foreach ($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'feed');
    }
    db()->query("INSERT INTO `feed_lists`(`title`,`icon`) VALUES('".$titleSlug."','".$icon."')");
    return true;
}

function save_feed_list($val, $list) {
    $expected = array(
        'title' => '',
        'icon' => '',
    );

    /**
     * @var $title
     * @var $icon
     */
    extract(array_merge($expected, $val));
    $titleSlug = $list['title'];
    $id = $list['id'];

    foreach ($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, ' feed') : add_language_phrase($titleSlug, $t, $langId, 'feed');
    }
    db()->query("UPDATE `feed_lists` SET icon = '{$icon}' WHERE id = '{$id}'");
    return true;
}

function hideFeedComment($feedId, $action = 'enable') {
    if ($action == 'disable') {
        $status = 1;
        db()->query("UPDATE feeds SET hide_comment = '{$status}' WHERE feed_id ='{$feedId}'");
        return true;
    } else {
        $status = 0;
        db()->query("UPDATE feeds SET hide_comment = '{$status}' WHERE feed_id ='{$feedId}'");
        return true;
    }
    return false;
}

function count_user_posts($user_id) {
    $db = db();
    $sql = "SELECT COUNT(`feed_id`) FROM `feeds` WHERE `entity_id` = '".$user_id."' AND `entity_type` = 'user'";
    $query = $db->query($sql);
    $row = $query->fetch_row();
    $count = $row[0];
    return $count;
}
//hide reaction custom
function hideFeedReaction($feedId, $action = 'enable') {
    if ($action == 'disable') {
        $status = 1;
        db()->query("UPDATE feeds SET hide_reaction = '{$status}' WHERE feed_id ='{$feedId}'");
        return true;
    } else {
        $status = 0;
        db()->query("UPDATE feeds SET hide_reaction = '{$status}' WHERE feed_id ='{$feedId}'");
        return true;
    }
    return false;
}
//turn on post notification
function is_turnedOn($userid, $loggedinUser = null) {
    $loggedinUser = ($loggedinUser) ? $loggedinUser : get_userid();
    $postLists = get_postLists($loggedinUser);

    if(in_array($userid, $postLists)) return true;
    return false;
}
function get_postLists($userid = null) {
    $userid = ($userid) ? $userid : get_userid();
    $cacheName = "user-subscribed-".$userid;
    fire_hook('clear-user-subscribed-cache', null, array($cacheName));
    if(cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {

        $users = array();
        $sql = "SELECT DISTINCT `to_userid` FROM `notifypost`";
        $where_clause = " WHERE `type`='1' AND `from_userid`='".$userid."' AND `to_userid` IN (SELECT `id` FROM `users`)";
        $where_clause = fire_hook('users.category.filter', $where_clause, array($where_clause, true));
        $order_by = "";
        $query = db()->query("{$sql}"."{$where_clause}");
        if($query) {
            foreach(fetch_all($query) as $result) {
                $users[] = $result['to_userid'];
            }
            return $users;
        }
        set_cacheForever($cacheName, $users);
        return $users;
    }
}
function process_on($type, $userid,$fromUserid = null) {
    $fromUserid = ($fromUserid) ? $fromUserid : get_userid();
    if($type == 'off') {
        $sql = "DELETE FROM `notifypost` WHERE";
        $off_whereclause = " type='1' AND `from_userid`='{$fromUserid}' AND `to_userid`='{$userid}'";
        $off_whereclause = fire_hook('off.where.clause', $off_whereclause, array($off_whereclause));
        $sql .= $off_whereclause;
        db()->query($sql);
        fire_hook("user.off", null, array($fromUserid, $userid));
    } else {
        db()->query("INSERT INTO `notifypost` (from_userid,to_userid,type)VALUES(
            '{$fromUserid}','{$userid}','1'
        )");
        $notifyId = db()->insert_id;
        fire_hook("user.on", null, array($fromUserid, $userid));
        fire_hook('plugins.users.category.updater', null, array('notifypost', ' ', $notifyId, 'notify_id'));
    }
    forget_cache("user-on-".$fromUserid);
    forget_cache("user-notified-post-".$userid);
}
function get_subers() {
        $result = array();
        $userid = get_userid();
    //echo 'here';die;
    //print_r($userid);die;
        $query = db()->query("SELECT from_userid FROM `notifypost` WHERE  `to_userid`='{$userid}'");
    //print_r($query);die;
        if ($query and $query->num_rows > 0) {
            //echo 'here';die;
            while ($fetch = $query->fetch_assoc()) {
                $result[] = $fetch['from_userid'];
            }
        }
        return $result;

}

function feed_has_embed($id)
{
    $db = db();
    $query = $db->query("SELECT `id` FROM `feed_embeds` WHERE `feed_id` = $id");
    if ($query && $query->num_rows > 0) {
        return true;
    }
    return false;
}

function feed_delete_embed($id)
{
    $db = db();
    $db->query("DELETE FROM `feed_embeds` WHERE `feed_id` = $id");
    return true;
}

function feed_get_embed($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM `feed_embeds` WHERE `feed_id` = $id LIMIT 1");
    if ($query && $query->num_rows > 0) {
        return $query->fetch_assoc();
    }
    return false;
}

function feed_process_twitter_embed($feed_embed_code, $feed_id)
{
    $code = false;
    if (!strpos('blockquote', $feed_embed_code)) {
        $code = '<blockquote class="twitter-tweet"><p lang="en" dir="ltr"></p><a href="' . $feed_embed_code . '"></a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
    } elseif (strpos('twitter', $feed_embed_code) && strpos('blockquote', $feed_embed_code)) {
        $code = $feed_embed_code;
    }
    return $code;
}

function feed_process_tiktok_embed($feed_embed_code)
{
    $code = false;
    if (!strpos('blockquote', $feed_embed_code)) {
        $link = "https://www.tiktok.com/oembed?url=$feed_embed_code";
        $res = json_decode(fetch_api($link));
        if (isset($res->html)) {
            $code = $res->html;
        }
    } else if (strpos('tiktok', $feed_embed_code) && strpos('blockquote', $feed_embed_code)) {
        $code = $feed_embed_code;
    }
    return $code;
}

function feed_process_video_embed($feed_embed_code)
{
    $code = false;
    $video_embed = feed_process_link($feed_embed_code);
    if ($video_embed['type'] === 'video' && isset($video_embed['code']) && $video_embed['code']) {
        $code = $video_embed['code'];
    }
    return $code;
}