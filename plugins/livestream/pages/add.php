<?php
/**
 * Livestream Add Page
 * @param App $app
 * @return bool|string
 */
function add_page($app) {
    pusher()->reset('livestream.keep.alive');
    pusher()->reset('livestream.session.description');
    pusher()->reset('livestream.ice.candidate');
    pusher()->reset('livestream.member.count');
    pusher()->reset('livestream.comment');
    pusher()->reset('livestream.ended');
    $app->leftMenu = false;
    $title = lang('livestream::start-livestream');
    $description = lang('livestream::livestream-start-desc');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'data' => array(),
        'livestream' => array(),
        'ice_servers' => array(),
        'ice_transport_policy' => array(),
        'html' => '',
        'redirect_url' => ''
    );

    $livestream = input('livestream', array());
    $categories = Livestream::getCategories();
    $result['html'] = view('livestream::livestream/add', array('livestream' => $livestream, 'categories' => $categories, 'message' => $result['message']));
    $ice_servers = Livestream::getICEServers(null, 1, true);
    $ice_transport_policy = config('livestream-ice-transport-policy', 'all');
    $result['ice_servers'] = $ice_servers;
    $result['ice_transport_policy'] = $ice_transport_policy;
    if($livestream) {
        $val = input('val', array());
        $livestream = array_merge($livestream, $val);
        validator($livestream, array(
            'title' => 'required',
            'type' => 'required',
            'categories' => 'required'
        ));
        if(validation_passes()) {
            CSRFProtection::validate();
            if(input('image_data')) {
                list($header, $image) = array_pad(explode(',', input('image_data')), 2, '');
                if($image) {
                    preg_match('/data\:image\/(.*?);base64/i', $header, $matches);
                    $default_extension = 'webp';
                    $image_file_extensions = explode(',', config('image-file-types', 'jpeg,jpg,png,gif,webp'));
                    if(!in_array($default_extension, $image_file_extensions)) {
                        $image_file_extensions[] = $default_extension;
                    }
                    $extension = isset($matches[1]) ? $matches[1] : $default_extension;
                    if(in_array($extension, $image_file_extensions)) {
                        $image = base64_decode(str_replace(' ', '+', $image));
                        $temp_dir = config('temp-dir', path('storage/tmp')).'/livestream/images';
                        $file_name = 'image_'.get_userid().'_'.time();
                        if(!is_dir($temp_dir)) {
                            mkdir($temp_dir, 0777, true);
                        }
                        $temp_path = $temp_dir.'/'.$file_name.'.'.$extension;
                        file_put_contents($temp_path, $image);
                        if($extension == 'webp' && function_exists('imagecreatefromwebp')) {
                            $image = imagecreatefromwebp($temp_path);
                            $temp_path = $temp_dir.'/'.$file_name.'.jpg';
                            imagejpeg($image, $temp_path, 100);
                        }
                        $uploader = new Uploader($temp_path, 'image', true, true);
                        if($uploader->passed()) {
                            $path = get_userid().'/'.date('Y').'/livestream/images/';
                            $uploader->setPath($path);
                            $livestream['image'] = $uploader->resize()->result();
                        } else {
                            $result['message'] = $uploader->getError();
                        }
                    } else {
                        $result['message'] = lang('livestream::invalid-image');
                        $livestream['image'] = '';
                    }
                } else {
                    $result['message'] = lang('livestream::invalid-image');
                    $livestream['image'] = '';
                }
            } elseif(input_file('image')) {
                $image = input_file('image');
                $uploader = new Uploader($image, 'image');
                $uploader->setPath('livestream/images/');
                if($uploader->passed()) {
                    $livestream['image'] = $uploader->resize()->result();
                } else {
                    $result['message'] = $uploader->getError();
                }
            } else {
                $livestream['image'] = '';
            }
            if(isset($livestream['image'])) {
                $user_id = get_userid();
                $livestream['token'] = Livestream::generateToken();
                $added = Livestream::add($livestream);
                if($added) {
                    $livestream = Livestream::get($added);
                    if($livestream['privacy'] < 3) {
                        $friends = get_friends();
                        $followers = get_followers();
                        $subscribers = array_unique(array_merge($friends, $followers), SORT_REGULAR);
                        foreach ($subscribers as $subscriber_id) {
                            if ($subscriber_id != $user_id) {
                                send_notification($subscriber_id, 'livestream.started', $livestream['id']);
                            }
                        }
                    }
                    $result['status'] = 1;
                    $result['title'] = $livestream['title'];
                    $result['description'] = $livestream['description'];
                    $result['message'] = lang('livestream::livestream-started');
                    $result['html'] = view('livestream::livestream/list_item', array('livestream' => $livestream, 'categories' => $categories));
                    $livestream['id'] = (integer) $livestream['id'];
                    $result['livestream'] = $livestream;
                    $result['view'] = view('livestream::livestream/view', array('livestream' => $livestream, 'user_id' => $user_id, 'token' => $livestream['token'], 'is_owner' => true, 'ice_servers' => json_encode($ice_servers), 'ice_transport_policy' => $ice_transport_policy));
                } else {
                    $result['message'] = lang('livestream::livestream-add-error');
                }
            }
        } else {
            $result['message'] = validation_first();
        }
    }
    if(input('ajax')) {
        $response = json_encode($result, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(array(
            'title' => lang('livestream::livestreams'),
            'url' => url_to_pager('livestream-list'),
            'type' => 'primary',
            'confirm' => false,
            'modal' => false,
            'ajax' => true
        ));
        $result['html'] = view('livestream::layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}