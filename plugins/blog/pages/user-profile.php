<?php
//get_menu('user-profile', 'connections')->setActive();
function blogs_pager($app) {
    $limit = config('blog-list-limit', 12);
    $blogs = get_blogs('mine', null, null, $app->profileUser['id'], $limit);
	return $app->render(view('blog::profile/list', array('blogs' => $blogs)));
}