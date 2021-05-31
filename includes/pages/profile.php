<?php
function profile_pager($app) {
    fire_hook('auth.profile.permission');
	get_menu("user-profile", "timeline")->setActive();
	set_meta_tags(array('name' => get_setting('site_title', 'Crea8social'), 'title' => get_user_name(app()->profileUser), 'description' => str_limit(app()->profileUser['bio'], 100), 'image' => get_avatar(920, app()->profileUser), 'keywords' => app()->profileUser['username']));
	load_functions("feed::feed");
	return $app->render(view('profile/timeline', array('feeds' => get_feeds('timeline', app()->profileUser['id']))));
}

function profile_about_pager($app) {
	get_menu("user-profile", "about")->setActive();
	$type = input('type', 'overview');

	//register the about menus
	add_menu('user-profile-about', array('title' => lang('overview'), 'link' => profile_url('about', $app->profileUser), 'id' => 'overview'));
	foreach(get_custom_field_categories('user') as $category) {
		add_menu('user-profile-about', array('title' => lang($category['title']), 'link' => profile_url('about?id='.$category['id'].'&type=custom', $app->profileUser), 'id' => 'field-'.$category['id']));
	}

	//allow plugins to hook in
	fire_hook('user-profile-about', null, array($type, $app));
	switch($type) {
		case 'overview' :
			get_menu("user-profile-about", "overview")->setActive();
			$content = view('profile/about/overview');
		break;
		case 'custom':
			$id = input('id');
			$category = get_custom_field_category($id);
			if(!$category) return redirect(profile_url("about"));
			get_menu("user-profile-about", "field-".$id)->setActive();
			$content = view("profile/about/field", array('id' => $id));
		break;
		default:
			$content = fire_hook('user-profile-about-content', '', array($type, $app));
		break;
	}
	return $app->render(view('profile/about/layout', array('content' => $content)));
}

function profile_change_cover_pager($app) {
	CSRFProtection::validate(false);
	$result = array(
		'status' => 0,
		'message' => lang('general-image-error'),
		'image' => ''
	);

	if(input_file('cover')) {
		$uploader = new Uploader(input_file('cover'), 'image');
		$uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/');
		if($uploader->passed()) {
			$original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->toDB("profile-cover", get_userid())->result();

			$user = get_user();
			//delete the old resized cover
			if($user['resized_cover']) {
				delete_file(path($user['resized_cover']));
			}
			fire_hook("user.cover", null, array($original, $uploader->insertedId));
			$uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/resized/');
			$cover = $uploader->crop(0, 0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
			$result['image'] = url_img($cover);
			$result['original'] = url_img($original);
			$result['id'] = $uploader->insertedId;
			update_user(array('cover' => $original, 'resized_cover' => $cover));

			$result['status'] = 1;
		} else {
			$result['message'] = $uploader->getError();
		}
	}

	return json_encode($result);
}

function profile_position_cover_pager($app) {
	CSRFProtection::validate(false);
	$pos = input('pos');
	$width = input('width', 623);
	$user = get_user();
	$cover = path($user['cover']);
	$uploader = new Uploader($cover, 'image', false, true);
	$uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/cover/resized/');
	$pos = abs($pos);
	$pos = ($pos / $width);
	$yCordinate = 0;
	$srcWidth = $uploader->getWidth();
	$srcHeight = $srcWidth * 0.4;
	if(!empty($pos) & $pos < $srcWidth) {
		$yCordinate = $pos * $uploader->getWidth();
	}
	$cover = $uploader->crop(0, $yCordinate, $srcWidth, $srcHeight)->result();

	update_user(array('resized_cover' => $cover));
	return url_img($cover);
}

function remove_cover_pager($app) {
	CSRFProtection::validate(false);
	$user = get_user();
	if(!$user['resized_cover']) return false;
	delete_file(path($user['resized_cover']));

	return update_user(array('resized_cover' => '', 'cover' => ''));
}

function change_logo_pager($app) {
	CSRFProtection::validate(false);
	$result = array(
		'status' => 0,
		'message' => lang('general-image-error'),
		'image' => '',
		'id' => ''
	);
	$src = input('avatar_src');
	$data = input('avatar_data');
	$file = input_file('avatar_file');
	if($file) {
		$avatar_sizes = array(75, 200, 600, 920);
		$old_avatars = array();
		$user = get_user();
		$old_avatar = $user['avatar'];
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
		$uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
		if($uploader->passed()) {
			$avatar = $uploader->resize()->toDB("profile-avatar", get_userid(), 1)->result();

			update_user_avatar($avatar, null, $uploader->insertedId, false);
			$new_avatars = array();
			$user = find_user(get_userid());
			$new_avatar = $user['avatar'];
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
			$result['result'] = url_img($avatar, 200);
			$result['state'] = 200;
			$result['status'] = 1;
			$result['image'] = url_img($avatar, 200);
			$result['id'] = $uploader->insertedId;
			$result['large'] = url_img($avatar, 920);
		} else {
			$result['message'] = $uploader->getError();
		}
	}

	return json_encode($result);
}

function load_preview_pager($app) {
	CSRFProtection::validate(false);
	$type = input('type');
	$id = input('id');
	$content = fire_hook('preview.card', null, array($type, $id));
	if($type == 'user') {
		$content = view('account/profile-card', array('user' => find_user($id)));
	}

	echo $content;
}
function education_list_pager(){
    $status = 0;
    $message = '';
    $redirect_url = '';
    $return = array(
        'content' => '',
        'type' => '',
        'id' => '',
        'action' => '',
        'status' => 0
    );

    $val = input('val');
    $action = input('action');
    $return['action'] = $action;
    if ($action == 'add'){
        $val['user_id'] = get_userid();
        $addWorkExperience = addWorkExperience($val);
        if ($addWorkExperience){
            $status = 1;
            $type = $val['type'];
            $list = getWorkExperienceById($addWorkExperience);
            $return['content'] ='<li id="work-'.$addWorkExperience.'" class="experience work">'. view('user/education/load', array('type' => $type, 'list' => $list)).'</li>';
            $return['id'] = $addWorkExperience;
            $return['type'] = $type;
            if ($type == 'work'){
                $message = lang('work').' '.lang('added-successfully');
            } elseif ($type =='college'){
                $message = lang('college').' '.lang('added-successfully');
            }elseif ($type =='high'){
                $message = lang('high-school').' '.lang('added-successfully');
            }else{
                $message = lang('unknown-error-occurred');
            }
        }
    }elseif ($action == 'edit') {
        $id = input('id');
        $type = input('type');
        $load = input('load');
        if ($load){
            $list = getWorkExperienceById($id);
            if ($list){
                $status = 1;
                $return['content'] = view('user/education/edit', array('list' => $list, 'type' => $type));
                $return['type'] = $type;
                $return['id'] = $id;
            }
        } elseif ($val){
            $editWorkExperience = editWorkExperience($id, $val);
            if ($editWorkExperience){
                $status = 1;
                $type = $val['type'];
                $list = getWorkExperienceById($id);
                $return['content'] = view('user/education/load', array('type' => $type, 'list' => $list));
                $return['id'] = $id;
                $return['type'] = $type;
                $message = lang('edited-successfully');
            }
        }
    } elseif ($action == 'delete'){
        $id = input('id');
        $delete = deleteWorkExperience($id);
        if ($delete){
            $return['type'] = input('type');
            $return['id'] = $id;
            $return['status'] = 1;
            $status = 1;
            $message = lang('deleted').' '.lang('successfully');
        }
    }

    if(input('ajax')) {
        $result = array(
            'status' => (int) $status,
            'message' => (string) $message,
            'redirect_url' => (string) $redirect_url,
            'data' => $return,
        );
        $response = json_encode($result);
        return $response;
    }
    return json_encode($return);
}