<?php
//get_menu('user-profile', 'connections')->setActive();
function likes_pager($app) {
    $limit = config('page-list-limit', 12);
	return $app->render(view('page::user-profile/likes', array('pages' => get_pages('likes', $app->profileUser['id'], $limit))));
}