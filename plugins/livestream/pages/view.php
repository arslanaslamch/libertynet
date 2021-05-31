<?php
/**
 * Livestream Page
 * @param App $app
 * @return bool|string
 */
function view_page($app) {
    $app->leftMenu = false;
    $slug = segment(1);
	$id = input('id', $slug);
	$livestream = Livestream::get($id);
	if(!$livestream) {
		return MyError::error404();
	}
	$app->setTitle($livestream['title']);
    set_meta_tags(array('name' => get_setting('site_title', 'Crea8social'), 'title' => $livestream['title'], 'description' => $livestream['description'], 'image' => url_img($livestream['image'], 920), 'keywords' => $livestream['description']));
	$site_name = get_setting('site_title', 'Crea8social');
	$title = $site_name.' - '.$livestream['title'];
	$image = $livestream['image'] ? url_img($livestream['image'], 920) : img('livestream::images/no_image.png', 920);
	$description = $site_name.' > livestream > '.$livestream['title'];
    $message = null;
    $user_id = get_userid();
    $result = array(
        'status' => 1,
        'title' => $livestream['title'],
        'description' => $livestream['description'],
        'message' => '',
        'data' => $livestream,
        'livestream' => array(),
        'ice_servers' => array(),
        'ice_transport_policy' => array(),
        'html' => '',
        'redirect_url' => ''
    );
    $is_owner = $livestream['user_id'] == $user_id;
    $token = $is_owner ? $livestream['token'] : Livestream::generateToken();
    $ice_servers = Livestream::getICEServers(null, 1, true);
    $ice_transport_policy = config('livestream-ice-transport-policy', 'all');
    $result['ice_servers'] = $ice_servers;
    $result['ice_transport_policy'] = $ice_transport_policy;
    $result['html'] = view('livestream::livestream/view', array('livestream' => $livestream, 'user_id' => $user_id, 'token' => $token, 'is_owner' => $is_owner, 'ice_servers' => json_encode($ice_servers), 'ice_transport_policy' => $ice_transport_policy));
    set_meta_tags(array('name' => $site_name, 'title' => $title, 'description' => $description, 'image' => $image));
    Livestream::viewed($livestream['id']);
    add_subscriber($user_id, 'livestream', $livestream['id']);
    $livestream['livestream'] = $livestream;
    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array();
        if($livestream['user_id'] == get_userid() && $livestream['status'] > 1) {
            $links[] =  array(
                'title' => lang('livestream::delete-livestream'),
                'url' => url_to_pager('livestream-delete').'?id='.$livestream['id'],
                'type' => 'danger',
                'confirm' => true,
                'modal' => false,
                'ajax' => false
            );
            $links[] = array(
                'title' => lang('livestream::edit-livestream'),
                'url' => url_to_pager('livestream-edit').'?id='.$livestream['id'],
                'type' => 'secondary',
                'confirm' => false,
                'modal' => false,
                'ajax' => true
            );
        }
        $links[] = array(
            'title' => lang('livestream::livestreams'),
            'url' => url_to_pager('livestream-list'),
            'type' => 'primary',
            'confirm' => false,
            'modal' => false,
            'ajax' => true
        );
        $result['html'] = view('livestream::layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}