<?php
load_functions("feed::feed");

function feed_pager($app) {
    $app->setTitle(lang("news-feed"));
    $app->topMenu = lang('news-feed');
    $type = input('type', 'all');
    session_put(md5($type.'all'), time());

    if (is_loggedIn()) get_menu("dashboard-main-menu", 'news-feed')->setActive();
    $content = view("feed::content", array('feeds' => get_feeds('feed', $type), 'type' => $type));
    $content = fire_hook('feed.type', $content, array($type));
    return $app->render($content);
}

function add_feed_pager($app) {
    CSRFProtection::validate(false);
    $val = input('val');
    if ($val) {
        CSRFProtection::validate();
        $feed = add_feed($val);
        $feedAlt = json_decode($feed, true);
        if (isset($val['messenger']) && $feedAlt['status'] != 1) {
            $afterFeed = fire_hook("after.add.feed", $val);
            if ($afterFeed) {
                return json_encode(
                    $result = array(
                        'status' => $afterFeed['status'],
                        'message' => $afterFeed['message'],
                        'feed' => ''
                    )
                );
            }
        } else if (isset($val['messenger']) && $feedAlt['status'] == 1) {
            $afterFeed = fire_hook("after.add.feed", $val);
            if ($afterFeed) {
                return json_encode(
                    $result = array(
                        'status' => $afterFeed['status'],
                        'message' => $afterFeed['message']." ".lang('feed::feed-successfully-posted'),
                        'feed' => $feedAlt['feed']
                    )
                );
            }
        } else {
            return $feed;
        }
    } else {
        return json_encode(array(
            'status' => 0,
            'message' => lang("failed-to-upload-file")
        ));
    }
}

function save_feed_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $text = input('text');
    $save = save_feed($id, $text);
    $feed = find_feed($id);
    if ($save) {
        return format_feed_content($feed['feed_content']);
    } else {
        return '0';
    }
}

function remove_feed_pager($app) {
    CSRFProtection::validate(false);
    $removed = remove_feed(input('id'));
    if (is_ajax()) {
        if ($removed) return 1;
        return '0';
    } else {
        return redirect_back();
    }
}

function feed_more_pager($app) {
    CSRFProtection::validate(false);
    $limit = config('feed-limit', 10);
    $offset = input('offset');
    $offset = ($offset) ? $offset : $limit;
    $type = input('type');
    $typeId = input('type_id');
    $newOffset = (int)$offset + $limit;
    $feeds = get_feeds($type, $typeId, $limit, $offset);
    $content = view('feed::paginate', array('feeds' => $feeds));
    $content = fire_hook('feed.type', $content, array($type));

    return json_encode(array(
        'offset' => $newOffset,
        'feeds' => $content,
    ));
}

function check_new_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $typeId = input('type_id');
    $update_time = input('update_time');
    $feeds = get_feeds($type, $typeId, null, 0, $update_time);
    $update_time = time();
    $content = '';
    foreach ($feeds as $feed) {
        $content .= view('feed::feed', array('feed' => $feed));
    }

    return json_encode(array(
        'count' => count($feeds),
        'update_time' => $update_time,
        'feeds' => $content,
    ));

}

function share_feed_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $count = share_feed($id);
    fire_hook("creditgift.addcredit.share", null, array(get_userid()));
    return json_encode(array(
        'count' => $count,
        'message' => lang('feed::feed-success-shared')
    ));
}

function feed_notification_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $id = input('id');
    if ($type == 1) {
        add_subscriber(get_userid(), 'feed', $id);
    } else {
        remove_subscriber(get_userid(), 'feed', $id);
    }
}

function feed_page_pager($app) {
    $feed = find_feed(segment(1));
    if (!$feed) return redirect_to_pager('feed');
    if ($feed['shared']) {
        $title = isset($feed['shared-feed']['publisher']['page_title']) ? $feed['shared-feed']['publisher']['page_title'] : $feed['shared-feed']['publisher']['name'];
    } else {
        $title = isset($feed['publisher']['page_title']) ? $feed['publisher']['page_title'] : $feed['publisher']['name'];
    };
    $app->setTitle($title);
    $description = $feed['feed_content'];
    $image = str_replace('_75_', '_920_', $feed['publisher']['avatar']);
    if ($feed['link_details']) {
        $details = perfectUnserialize($feed['link_details']);
        if ($details['image']) {
            $image = $details['image'];
        }
        if ($details['title'] && !$title) {
            $title = $details['title'];
        }
        if ($details['description'] && !$description) {
            $description = $details['description'];
        }
    }
    if ($feed['video']) {
        $video_path = $feed['video'];
    }
    if (isset($feed['videoDetails'])) {
        $details = $feed['videoDetails'];
        if ($details['photo_path']) {
            $image = $details['source'] == 'external' ? $details['photo_path'] : url($details['photo_path']);
        }
        if ($details['title'] && !$title) {
            $title = $details['title'];
        }
        if ($details['description'] && !$description) {
            $description = $details['description'];
        }
        if ($details['file_path']) {
            $video_path = $details['file_path'];
        }
    }
    if ($feed['photos']) {
        $photos = perfectUnserialize($feed['photos']);
        foreach ($photos as $photId => $path) {
            $image = url_img($path, 920);
            break;
        }
    }
    $meta_tags = array('name' => get_setting("site_title", "Crea8social"), 'title' => $title, 'description' => $description, 'image' => $image);
    if (isset($video_path)) {
        $meta_tags['video'] = url($video_path);
    }
    //increase feed
    fire_hook('feed.counts', null, array(segment(1)));
    set_meta_tags($meta_tags);
    if ($feed['entity_type'] == 'user') {
        $design = get_user_design_details($feed['publisher']);
        if ($design) app()->design = $design;
    }
    return $app->render(view('feed::page', array('feed' => $feed)));
}

function update_editor_privacy_pager($app) {
    CSRFProtection::validate(false);
    save_privacy_settings(array('feed-editor-privacy' => sanitizeText(input('v'))));
}

function get_link_pager($app) {
    CSRFProtection::validate(false);
    $linkDetails = feed_process_link(perfect_url(input('link')));
    if (!$linkDetails) return false;
    return view('feed::link', array('details' => $linkDetails, 'editor' => true));
}

function update_privacy_pager($app) {
    CSRFProtection::validate(false);
    $feed = input('id');
    $privacy = input('privacy');
    feed_update_privacy($feed, $privacy);
}

function hide_feed_pager($app) {
    CSRFProtection::validate(false);
    $feed = input('id');
    hide_feed($feed);
}

function pin_feed_pager($app) {
    CSRFProtection::validate(false);
    $id = segment(2);
    $feed = find_feed($id);

    if (!can_pin_post($feed)) redirect_back();
    pin_feed($feed);
    redirect_back();
}

function unhide_feed_pager($app) {
    $feed = input('id');
    unhide_feed($feed);
}

function feed_download_pager($app) {
    $feed_id = input('feed_id');
    if ($feed_id) {
        $file_id = input('file_id');
        $feed = find_feed($feed_id);
        $file = $feed['files'][$file_id];
        return download_file($file['path'], $file['name']);
    }
//	$path = input('file');
//	$name = input('name');
//	return download_file($path, $name);
}

function search_media_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $term = input('term');

    if ($type == 'listening-to') {
        $soundcloud = 'http://api.soundcloud.com/tracks.json?client_id=e8d2797b62ce47938f3baa699a725864&limit=5&q='.urlencode($term);

        $soundcloud = @file_get_contents($soundcloud);


        $results = json_decode($soundcloud, true);


        $a = array();
        if (is_array($results) and count($results) > 1) {
            foreach ($results as $s) {

                if ($s['kind'] == 'track') {
                    $a[] = array(
                        'title' => $s['title'],
                        'description' => $s['description'],
                        'link' => $s['uri'],
                        'image' => $s['artwork_url']
                    );
                }
            }

            return view('feed::media-search', array('medias' => $a));
        }
    } else {
        $libPath = path('includes/libraries/Google/src/Google/');

        require_once $libPath.'Client.php';
        require_once $libPath.'Service/YouTube.php';

        $client = new \Google_Client();
        $client->setDeveloperKey(config('google-api-key'));

        $youtube = new \Google_Service_YouTube($client);

        $maxLimit = 5;

        try {
            $searchResult = $youtube->search->listSearch('id,snippet', array(
                'q' => $term,
                'maxResults' => $maxLimit
            ));

            $a = array();
            foreach ($searchResult['items'] as $result) {
                switch ($result['id']['kind']) {
                    case 'youtube#video':
                        $l = "https://www.youtube.com/embed/".$result['id']['videoId'];
                        $a[] = array(
                            'title' => $result['snippet']['title'],
                            'link' => (String)$l,
                            'image' => $result['snippet']['thumbnails']['medium']['url']
                        );

                        break;
                }
            }

            return view('feed::media-search', array('medias' => $a));
        } catch (\Exception $e) {
        }
    }
}

function submit_poll_pager($app) {
    CSRFProtection::validate(false);
    $pollId = input('val.poll_id');
    $feed = find_feed($pollId);
    feed_submit_poll(input('val'), $feed);
    return view("feed::poll-result", array('feed' => find_feed($pollId)));
}

function poll_voters_pager($app) {
    CSRFProtection::validate(false);
    $id = input('answer_id');
    $limit = input('limit', 4);
    $page = input('page', 1);
    $voters = get_poll_answers_user($id, $limit, $page);
    $total_pages = get_num_poll_voter_pages($id, $limit);
    return view("feed::poll-voters", array('voters' => $voters, 'answer_id' => $id, 'total_pages' => $total_pages, 'page' => $page));
}

function hide_comment_pager() {
    $result = array('status' => 0, 'html' => '', 'content' => '', 'type' => 'enable');
    $html = "<i class='ion-eye-disabled'></i>".lang("feed::enable-comment");
    $feedId = input('id');
    $type = input('type');
    if ($feedId && $type) {
        $feed = find_feed($feedId);
        if ($feed && $feed['user_id'] = get_userid()) {
            $hide = hideFeedComment($feedId, $type);
            if ($hide) $result['status'] = 1;
            if ($type == "enable") {
                $html = "<i class='ion-eye-disabled'></i>".lang("feed::disable-comment");
                $result['content'] = view("feed::feed-comment", array('feed' => find_feed($feedId)));
                $result['type'] = 'disable';
            }
            $result['html'] = $html;
        }
    }
    return json_encode($result);
}

function send_message_load_pager() {
    $result = array('status' => 0, 'feed' => '');
    $feedId = input('id');
    $feed = find_feed($feedId);
    if ($feed) {
        $result['status'] = 1;
        $result['feed'] = view("feed::share/message", array('feed' => $feed));
    }
    return json_encode($result);
}

function share_to_friend_load_pager() {
    $result = array('status' => 0, 'feed' => '');
    $feedId = input('id');
    $feed = find_feed($feedId);
    if ($feed) {
        $result['status'] = 1;
        $result['feed'] = view("feed::share/timelines", array('feed' => $feed));
    }
    return json_encode($result);
}

function share_to_page_load_pager() {
    $result = array('status' => 0, 'feed' => '');
    $feedId = input('id');
    $feed = find_feed($feedId);
    if ($feed) {
        $result['status'] = 1;
        $pages = get_pages('mine');
        $result['feed'] = view("feed::share/load-pages", array('pages' => $pages, 'feed' => $feed));
    }
    return json_encode($result);
}

function send_message_pager() {
    $result = array('status' => 0, 'message' => 'Oops! Something went wrong');
    $feedId = input('id');
    $toUser = input('user');
    $feed = find_feed($feedId);
    if ($feed and $toUser) {
        $link = url_to_pager('view-post', array('id' => $feed['feed_id']));
        $user = array($toUser);
        $conversationId = get_conversation_id($user);
        send_chat_message($conversationId, $link);
        $result['status'] = 1;
        $result['message'] = "Sent";
    }
    return json_encode($result);
}

function search_friends_pager() {
    $feedId = input('id');
    $type = input('type');
    $term = input('term');
    $content = "";
    $feed = find_feed($feedId);
    if ($feed and $type) {
        if ($type == "message-to-friends" || $type == "share-to-timeline") {
            $paginator = search_users($term, '10', true);
            $users = $paginator->results();
            if ($type == "share-to-timeline") $content = view("feed::share/friends-timeline", array('users' => $users, 'feed' => $feed));
            if ($type == "message-to-friends") $content = view("feed::share/friends-message", array('users' => $users, 'feed' => $feed));
        } elseif ($type == "share-to-page") {
            $pages = get_pages('browse', $term);
            $content = view("feed::share/page-lists", array('pages' => $pages, 'feed' => $feed));
        } else {
            return false;
        }
    }
    return json_encode(array('content' => $content));
}

function share_to_friend_pager() {
    $result = array('status' => 0, 'message' => 'Oops! Something went wrong');
    $feedId = input('id');
    $toUser = input('user');
    $feed = find_feed($feedId);
    if ($feed and $toUser) {
        share_feed($feedId, $toUser);
        $result['status'] = 1;
        $result['message'] = "Shared";
    }
    return json_encode($result);
}

function share_to_page_pager() {
    $result = array('status' => 0, 'message' => 'Oops! Something went wrong');
    $feedId = input('id');
    $toPage = input('page');
    $page = find_page($toPage);
    $feed = find_feed($feedId);
    if ($feed and $page) {
        share_feed($feedId, $toPage, $toPage, 'page');
        $result['status'] = 1;
        $result['message'] = "Shared";
    }
    return json_encode($result);
}
//hide reaction custom
function hide_reaction_pager() {
    $result = array('status' => 0, 'html' => '', 'content' => '', 'type' => 'enable');
    $html = "<i class='ion-eye-disabled'></i>".lang("feed::enable-reaction");
    $feedId = input('id');
    $type = input('type');
    if ($feedId && $type) {
        $feed = find_feed($feedId);
        if ($feed && $feed['user_id'] = get_userid()) {
            $hide = hideFeedReaction($feedId, $type);
            if ($hide) $result['status'] = 1;
            if ($type == "enable") {
                $html = "<i class='ion-eye-disabled'></i>".lang("feed::disable-reaction");
                $result['content'] = view("feed::feed-reaction", array('feed' => find_feed($feedId)));
                $result['type'] = 'disable';
            }
            $result['html'] = $html;
        }
    }
    return json_encode($result);
}
//turn notification post
function process_on_pager($app) {
    CSRFProtection::validate(false);
    process_on(input('type'), input('userid'));
}