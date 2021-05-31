<?php
function videos_pager($app) {
	$app->setTitle(lang('video::videos'));
	$category = input('category', 'all');
	$term = input('term');
	$type = input('type', 'browse');
	$filter = input('filter', 'all');
    $limit = config('video-list-limit', 12);
	$videos = get_videos($type, $category, $term, null, $limit, $filter);
    $list_type = cookie_get('video-list-type', 'list');
	return $app->render(view('video::index', array('videos' => $videos, 'list_type' => $list_type)));
}

function video_page_pager($app) {
	$videoId = segment(1);
	$video = $app->video;
	$meta_tags = array('name' => get_setting("site_title", "Crea8social"), 'title' => $video['title'], 'description' => $video['description'], 'keywords' => '');
	if($video['photo_path']) {
		$meta_tags['image'] = url($video['photo_path']);
	}
	if($video['file_path']) {
		$meta_tags['video'] = url($video['file_path']);
	}
	set_meta_tags($meta_tags);
	return $app->render(view('video::page', array('video' => $video)));
}

function video_player_pager($app)
{
	$videoId = segment(1);
	$video = $app->video;
	$meta_tags = array('name' => get_setting("site_title", "Crea8social"), 'title' => $video['title'], 'description' => $video['description'], 'keywords' => '');
	if ($video['photo_path']) {
		$meta_tags['image'] = url($video['photo_path']);
	}
	if ($video['file_path']) {
		$meta_tags['video'] = url($video['file_path']);
	}
	set_meta_tags($meta_tags);
	$app->onHeader = false;
	$app->onFooter = false;
	$app->leftMenu = false;
	return $app->render(view('video::player_embed', array('video' => $video)));
}

function video_edit_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$video = get_video(input('id'));
	if(!$video or !is_video_owner($video)) redirect('videos');
	$app->setTitle(lang('video::edit-video'));
	$message = null;
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		$save = save_video($val, $video);
		if($save) {
			$status = 1;
			$message = lang('video::video-saved');
			$redirect_url = get_video_url($video);
		}
		if(input('ajax')) {
			$result = array(
				'status' => (int) $status,
				'message' => (string) $message,
				'redirect_url' => (string) $redirect_url,
			);
			$response = json_encode($result);
			return $response;
		}
		if($redirect_url) {
			return redirect($redirect_url);
		}
	}
	return $app->render(view('video::edit', array('video' => $video, 'message' => $message)));
}

function video_delete_pager($app) {
	$video = get_video(input('id'));
	if(!$video or !is_video_owner($video)) return redirect('videos');
	delete_video($video['id']);

	return redirect_to_pager('videos');
}

function create_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$app->setTitle(lang('video::add-new-video'));
	$val = input('val');
	if($val) {
		CSRFProtection::validate();
		//check for video files
		$video_file = input_file('video_file');
		if($video_file && config('video-upload') && config('video-encoder', 'none') !== 'none') {
			validator($val, array('title' => 'required'));
			if(validation_fails()) {
				$message = validation_first();
			} else {
				$uploader = new Uploader($video_file, 'video');
				if($uploader->passed()) {
					$added = add_video($val);
					if($added) {
						$status = 1;
						$message = lang('video::video-add-success-message');
						$redirect_url = get_video_url($added);
					} else {
						$message = lang('video::video-add-error-message');
					}
				} else {
					$message = $uploader->getError();
				}
			}
		} else {
			/**
			 * @var $link
			 */
			$link = input('val.link');
			$link_details = feed_process_link($link);
			if(($link_details && ($link_details['type'] === 'video')) || is_youtube_video($link)) {
				$val['title'] = mysqli_real_escape_string(db(), sanitizeText($link_details['title']));
				$val['description'] = mysqli_real_escape_string(db(), sanitizeText($link_details['description']));
				$val['code'] = $link_details['code'];
				$val['photo_path'] = $link_details['image'];
				$added = add_video($val);
				if($added) {
					$status = 1;
					$message = lang('video::video-add-success-message');
					$redirect_url = get_video_url($added);
				} else {
					$message = lang('video::video-add-error-message');
				}
			} else {
				$message = lang('video::video-not-found-in-link');
			}
		}
		if(input('ajax')) {
			$result = array(
				'status' => $status,
				'message' => $message,
				'redirect_url' => $redirect_url,
			);
			$response = json_encode($result);
			return $response;
		}
		if($redirect_url) {
			return redirect($redirect_url);
		}
	}
	return $app->render(view('video::create', array('message' => $message)));
}

function video_embed_pager($app) {
	$uri = input('uri');
	$width = input('width', '100%');
	$height = input('height', '100vh');
	return view('video::embed', array(
		'uri' => $uri,
		'width' => $width,
		'height' => $height
	));
}