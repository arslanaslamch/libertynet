<?php
function store_profile_pager($app) {
    get_menu('store-profile', 'products')->setActive();
    $store = $app->profileStore;
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => get_store_details('name'), 'description' => get_store_details('description'),
        'image' => get_store_details('image') ? url_img(get_store_details('image'), 200) : '', 'keywords' => ''));
    $app->setTitle($store['name']);
    return $app->render(view('store::profile/products',array('seller'=>$store)));
    //return $app->render(view('store::single_store',array('seller'=>$store)));
    //return $app->render(view('group::profile/posts', array('feeds' => get_feeds('group', $app->profileGroup['s_id']))));
}

function store_profile_info_pager($app) {
    get_menu('store-profile', 'posts')->setActive();
    $store = $app->profileStore;
    $app->setTitle($store['name']);
    return $app->render(view('store::single_store',array('seller'=>$store)));
}

function store_profile_location_pager($app) {
    get_menu('store-profile', 'location')->setActive();
    $store = $app->profileStore;
    $app->setTitle($store['name']);
    return $app->render(view('store::profile/location',array('seller'=>$store)));
}

function store_profile_products_pager($app) {
    get_menu('store-profile', 'products')->setActive();
    $store = $app->profileStore;
    $app->setTitle($store['name']);
    return $app->render(view('store::profile/products',array('seller'=>$store)));
}
function store_profile_followers_pager($app) {
    get_menu('store-profile', 'followers')->setActive();
    $store = $app->profileStore;
    $app->setTitle($store['name']);
    $users = get_store_followers($store['s_id']);
    return $app->render(view('store::profile/followers',array('seller'=>$store,'users'=>$users)));
}

function store_profile_edit_pager($app){
    $app->setTitle(lang('store::store-manager'));
    $message = "";
    //echo "here";die();
    $val = input('val');
    if($val){
        CSRFProtection::validate();
        $rules = array(
            'name'=>'required',
            'email'=>'required|email',
            'address'=>'required',
            'phone'=>'required',
        );
        //print_r($val);die();
        $validator = validator($val,$rules);
        if(validation_passes()){
            $result = save_store_seller($val);
            if($result){
                return redirect_to_pager("store-manager");
            }
        }else{
            $message = validation_first();
            return $app->render(view('store::edit-seller',array('message'=>$message)));
        }

    }

    $seller = lpGetStoreById(segment(1));
    //$seller = get_seller(get_userid());
    if(!is_store_owner($seller)) return redirect_to_pager("store_homepage");
    return $app->render(view('store::edit-seller',array('seller'=>$seller,'message'=>$message)));
}

function store_blogs_profile_pager($app) {
    get_menu('store-profile', 'blogs')->setActive();
    $type = input('type', 'all');
    $category = input('category');
    $term = input('term');
    $filter = input('filter', 'all');
    $blogs = get_blogs($type, $category, $term, null, null, $filter, null, 'store', $app->profileStore['s_id'], null, $filter);
    return $app->render(view('store::profile/blog', array('blogs' => $blogs)));
}

function group_profile_members_pager($app) {
    get_menu('group-profile', 'members')->setActive();
    return $app->render(view('group::profile/members', array('users' => get_group_members($app->profileGroup['s_id']))));
}

function upload_cover_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $storeId = input('id');
    $store = lpGetStoreById($storeId);
    if(!$store) return json_encode($result);
    if(!is_store_owner($store)) return json_encode($result);

    if(input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath($store['s_id'].'/'.date('Y').'/photos/cover/');
        if($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->toDB("store", $store['s_id'])->result();


            //delete the old resized cover
            if($store['store_cover_resized']) {
                delete_file(path($store['store_cover_resized']));
            }

            //lets now crop this image for the resized cover
            $uploader->setPath($store['s_id'].'/'.date('Y').'/photos/cover/resized/');
            $cover = $uploader->crop(0, 0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['image'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_store_details(array('store_cover' => $original, 'store_cover_resized' => $cover), $store['s_id']);
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
    $storeId = input('id');
    $store = lpGetStoreById($storeId);
    if(!$store) return false;
    if(!is_store_owner($store)) return false;

    $cover = path($store['store_cover']);
    $uploader = new Uploader($cover, 'image', false, true);
    $uploader->setPath($store['s_id'].'/'.date('Y').'/photos/cover/resized/');
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
    if($store['store_cover_resized']) {
        delete_file(path($store['store_cover_resized']));
    }
   update_store_details(array('store_cover_resized' => $cover), $store['s_id']);
    return url_img($cover);
}

function remove_cover_pager($app) {
    CSRFProtection::validate(false);
    $storeId = input('id');
    $store = lpGetStoreById($storeId);
    if(!$store) return false;
    if(!is_store_owner($store)) return false;
    delete_file(path($store['store_cover_resized']));

   update_store_details(array('store_cover' => '', 'store_cover_resized' => ''), $store['s_id']);
}

function change_logo_pager($app) {
    CSRFProtection::validate(false);
    $storeId = input('id');
    $store = lpGetStoreById($storeId);
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    if(!$store) return json_encode($result);
    if(!is_store_owner($store)) return json_encode($result);

    $src = input('avatar_src');
    $data = input('avatar_data');
    $file = input_file('avatar_file');
    if($file) {
        $avatar_sizes = array(75, 200, 600, 920);
        $old_avatars = array();
        $old_avatar = $store['image'];
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
        $uploader->setPath($store['s_id'].'/'.date('Y').'/photos/logo/');
        if($uploader->passed()) {
            $image = $uploader->resize()->toDB("store-logo", $store['s_id'])->result();

           update_store_details(array('image' => $image), $store['s_id']);
            fire_hook('group.logo.updated', null, array($store['s_id'], $uploader->insertedId, $image));
            $new_avatars = array();
            $store = lpGetStoreById($storeId);
            $new_avatar = $store['image'];
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
