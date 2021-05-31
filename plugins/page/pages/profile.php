<?php
function page_profile_pager($app) {
	get_menu("page-profile", 'timeline')->setActive();
	set_meta_tags(array('name' => get_setting("site_title", "Crea8social"), 'title' => get_page_details('page_title'), 'description' => get_page_details('page_desc'), 'image' => get_page_details('page_logo') ? url_img(get_page_details('page_logo'), 920) : '', 'keywords' => ''));
	return $app->render(view('page::profile/timeline', array('feeds' => get_feeds('page', $app->profilePage['page_id']))));
}

function page_about_profile_pager($app) {
	get_menu("page-profile", 'about')->setActive();
	$type = input('type', 'general');

	//register the about menus
	add_menu('page-profile-about', array('title' => lang('general'), 'link' => page_url('about', $app->profilePage), 'id' => 'general'));
	foreach(get_custom_field_categories('page') as $category) {
		add_menu('page-profile-about', array('title' => lang($category['title']), 'link' => page_url('about?id='.$category['id'].'&type=custom', $app->profilePage), 'id' => 'field-'.$category['id']));
	}

	//allow plugins to hook in
	fire_hook('page-profile-about', null, array($app));
	switch($type) {
		case 'general' :
			get_menu("page-profile-about", "general")->setActive();
			$content = view('page::profile/about/general');
		break;
		case 'custom':
			$id = input('id');
			$category = get_custom_field_category($id);
			if(!$category) return redirect(page_url("about"));
			get_menu("page-profile-about", "field-".$id)->setActive();
			$content = view("page::profile/about/field", array('id' => $id));
		break;
		default:
			$content = fire_hook('page-profile-about', '', array($type, $app));
		break;
	}
	return $app->render(view('page::profile/about/layout', array('content' => $content)));
}

function page_photos_profile_pager($app) {
	get_menu("page-profile", 'photos')->setActive();
	return $app->render(view('page::profile/photos', array('photos' => get_photos($app->profilePage['page_id'], 'page'))));
}

function page_events_profile_pager($app) {
	get_menu("page-profile", 'events')->setActive();
	$type = input('type', 'upcoming');
	return $app->render(view('event::profile/list', array('events' => get_events($type, input('term'), null, false, input('category'), 'page', $app->profilePage['page_id']))));
}

function page_blogs_profile_pager($app) {
	get_menu("page-profile", 'blogs')->setActive();
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
	$blogs = get_blogs($type, $category, $term, null, null, $filter, null, 'page', $app->profilePage['page_id']);
	return $app->render(view('blog::profile/list', array('blogs' => $blogs)));
}
function page_messages_profile_pager($app) {
    $app->sideChat = false;
    $app->fullContainer = true;
    get_menu("page-profile", 'messages')->setActive();

	$entityType = 'page';
	$entityId = $app->profilePage['page_id'];

	$app->setTitle(lang('chat::messages'));
    $app->sideChat = false;
    $cid = input('cid', 'last');
    $userid = input('userid');
    $content = "";
    $conversation = null;
    $messageContent = null;
    if ($userid) {
        $theirCid = get_conversation_id(array($userid), true, $entityType, $entityId);
        $cid = ($theirCid) ? $theirCid : 'new';
    }
    if ($cid == 'last') {
        $conversation = get_last_conversation($entityType, $entityId);
        $cid = ($conversation) ? $conversation['cid'] : 'new';
    }
    if ($cid != 'new' and $cid != 'mobile') {
        $conversation = ($conversation) ? $conversation : get_conversation($cid);
        if (!$conversation or !is_conversation_member($cid)) return redirect(page_url('messages', $app->profilePage).'?cid=new');
        $messages = get_chat_messages($cid);
        $app->setTitle($conversation['title']);
        $messageContent = '';
        foreach ($messages as $message) {
            $messageContent .= view('page::profile/messenger/message', array('message' => $message, 'conversation' => $conversation));
        }
    } else {
        $app->setTitle(lang('chat::new-message'));
    }

    $limit = input('limit', config('chat-conversation-list-limit', 10));
    $conversations = get_user_conversations($limit, false,0, null, $entityType, $entityId);
    $content = view('page::profile/messenger/index', array(
        'cid' => $cid,
        'userid' => $userid,
        'conversations' => $conversations,
        'conversations_limit' => $limit,
        'conversate' => $conversation,
        'messageContent' => $messageContent
    ));
    return $app->render($content);
}

function page_musics_profile_pager($app) {
	get_menu("page-profile", 'musics')->setActive();
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
	$musics = get_musics($type, $category, $term, null, null, $filter, false, 'page', $entity_id = $app->profilePage['page_id']);
	$playlist = array();
	foreach($musics->results() as $music) {
		$music['file_path'] = fire_hook('filter.url', url($music['file_path'], false));
		$playlist[$music['slug']] = $music;
	}
	return $app->render(view('music::profile/list', array('musics' => $musics, 'playlist' => $playlist)));
}

function page_videos_profile_pager($app) {
	get_menu("page-profile", 'videos')->setActive();
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
	$videos = get_videos($type, $category, $term, null, null, $filter, false, 'page', $entity_id = $app->profilePage['page_id']);
	return $app->render(view('video::profile/list', array('videos' => $videos)));
}

function page_livestreams_profile_pager($app) {
	get_menu("page-profile", 'livestreams')->setActive();
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
	$livestreams = get_livestreams($type, $category, $term, null, null, $filter, false, 'page', $entity_id = $app->profilePage['page_id']);
	return $app->render(view('livestreaming::profile/list', array('livestreams' => $livestreams)));
}

function upload_cover_pager($app) {
	CSRFProtection::validate(false);
	$result = array(
		'status' => 0,
		'message' => lang('general-image-error'),
		'image' => ''
	);
	$pageId = input('id');
	$page = find_page($pageId);
	if(!$page) return json_encode($result);
	if(!is_page_admin($page) and !is_page_editor($page) and !is_page_moderator($page)) return json_encode($result);

	if(input_file('image')) {
		$uploader = new Uploader(input_file('image'), 'image');
		$uploader->setPath($page['page_id'].'/'.date('Y').'/photos/cover/');
		if($uploader->passed()) {
			$original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->toDB("page", $page['page_id'])->result();


			//delete the old resized cover
			if($page['page_cover_resized']) {
				delete_file(path($page['page_cover_resized']));
			}

			//lets now crop this image for the resized cover
			$uploader->setPath($page['page_id'].'/'.date('Y').'/photos/cover/resized/');
			$cover = $uploader->crop(0, 0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
			$result['image'] = url_img($cover);
			$result['original'] = url_img($original);
			$result['id'] = $uploader->insertedId;
			update_page_details(array('page_cover' => $original, 'page_cover_resized' => $cover), $page['page_id']);
			$result['status'] = 1;
		} else {
			$result['message'] = $uploader->getError();
		}
	}

	return json_encode($result);
}

function reposition_cover_pager($app) {
	CSRFProtection::validate(false);
	$pos = input('pos');
	$width = input('width', 623);
	$pageId = input('id');
	$page = find_page($pageId);
	if(!$page) return false;
	if(!is_page_admin($page) and !is_page_editor($page) and !is_page_moderator($page)) return false;

	$cover = path($page['page_cover']);
	$uploader = new Uploader($cover, 'image', false, true);
	$uploader->setPath($page['page_id'].'/'.date('Y').'/photos/cover/resized/');
	$pos = abs($pos);
	$pos = ($pos / $width);
	$yCordinate = 0;
	$srcWidth = $uploader->getWidth();
	$srcHeight = $srcWidth * 0.4;
	if(!empty($pos) & $pos < $srcWidth) {
		$yCordinate = $pos * $uploader->getWidth();
	}
	$cover = $uploader->crop(0, $yCordinate, $srcWidth, $srcHeight)->result();

	//delete old resized image if available
	if($page['page_cover_resized']) {
		delete_file(path($page['page_cover_resized']));
	}
	update_page_details(array('page_cover_resized' => $cover), $page['page_id']);
	return url_img($cover);
}

function remove_cover_pager($app) {
	CSRFProtection::validate(false);
	$pageId = input('id');
	$page = find_page($pageId);
	if(!$page) return false;
	if(!is_page_admin($page) and !is_page_editor($page) and !is_page_moderator($page)) return false;

	delete_file(path($page['page_cover_resized']));

	update_page_details(array('page_cover' => '', 'page_cover_resized' => ''), $page['page_id']);
}

function change_logo_pager($app) {
	CSRFProtection::validate(false);
	$pageId = input('id');
	$page = find_page($pageId);
	$result = array(
		'status' => 0,
		'message' => lang('page::page-permission-error'),
		'image' => ''
	);
	if(!$page) return json_encode($result);
	if(!is_page_admin($page) and !is_page_editor($page) and !is_page_moderator($page)) return json_encode($result);

	$src = input('avatar_src');
	$data = input('avatar_data');
	$file = input_file('avatar_file');
	if($file) {
		$avatar_sizes = array(75, 200, 600, 920);
		$old_avatars = array();
		$old_avatar = $page['page_logo'];
		$old_media_id = get_media_id($old_avatar);
		if(preg_match('/_%w_/', $old_avatar)) {
			foreach($avatar_sizes as $size) {
				$old_avatars[] = url(str_replace('%w', $size, $old_avatar));
			}
		} else {
			$old_avatars[] = url($old_avatar);
		}
		require path('includes/libraries/CropAvatar.php');
		$crop = new CropAvatar($src, $data, $file);
		$path = $crop->getResult();
		$uploader = new Uploader($path, 'image', false, true);
		$uploader->setPath($page['page_id'].'/'.date('Y').'/photos/logo/');
		if($uploader->passed()) {
			$image = $uploader->resize()->toDB("page-logo", $page['page_id'])->result();
			update_page_details(array('page_logo' => $image), $page['page_id']);
			fire_hook('page.logo.updated', null, array($page['page_id'], $uploader->insertedId, $image));
			$new_avatars = array();
			$page = find_page($pageId);
			$new_avatar = $page['page_logo'];
			$new_media_id = get_media_id($new_avatar);
			if(preg_match('/_%w_/', $new_avatar)) {
				foreach($avatar_sizes as $size) {
					$new_avatars[] = url(str_replace('%w', $size, $new_avatar));
				}
			} else {
				$new_avatars[] = url($new_avatar);
			}
			$avatars = array();
			for($i = 0; $i < count($old_avatars); $i++) {
				$avatars[$old_avatars[$i]] = $new_avatars[$i];
			}
			$result['avatars'] = $avatars;
			$result['oldMediaId'] = $old_media_id;
			$result['newMediaId'] = $new_media_id;
			$result['avatars'] = $avatars;
			$result['message'] = null;
			$result['result'] = url_img($image, 200);
			$result['state'] = 200;
			$result['status'] = 1;
			$result['image'] = url_img($image, 200);
			$result['id'] = $uploader->insertedId;
			$result['large'] = url_img($image, 920);
		} else {
			$result['message'] = $uploader->getError();
		}
	}

	return json_encode($result);
}
