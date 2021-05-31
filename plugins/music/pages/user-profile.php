<?php
function musics_pager($app) {
	$profile_url = profile_url('musics', $app->profileUser);
    $limit = config('music-list-limit', 12);
	$musics = get_musics('user-profile', input('category', 'all'), input('term'), $app->profileUser['id'], $limit, input('filter'));
	$playlist = array();
	foreach($musics->results() as $music) {
		$music['file_path'] = fire_hook('filter.url', url($music['file_path'], false));
		$playlist[$music['slug']] = $music;
	}
	return $app->render(view('music::profile/list', array('musics' => $musics, 'playlist' => $playlist, 'profile_url' => $profile_url)));
}