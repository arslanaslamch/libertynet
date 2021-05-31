<?php
function contests_pager($app) {
	$contests = get_contests('mine', null, null, $app->profileUser['id']);
	return $app->render(view('contest::profile/list', array('contests' => $contests)));
}