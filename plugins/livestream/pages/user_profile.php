<?php

/**
 * Livestream Profile Listing Page
 * @param App $app
 * @return bool|string
 */
function list_page($app) {
	$profile_url = profile_url('livestreams', $app->profileUser);
	$page = input('page', 1);
	$livestreams = Livestream::getLivestreams(array('user_id' => $app->profileUser['id'], 'category_id' => input('c'), 'term' => input('s')));
	return $app->render(view('livestream::profile/list', array('livestreams' => $livestreams, 'profile_url' => $profile_url)));
}