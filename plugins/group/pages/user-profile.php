<?php
//get_menu('user-profile', 'connections')->setActive();
function groups_pager($app) {
    $limit = config('group-list-limit', 12);
	return $app->render(view('group::user-profile/groups', array('groups' => get_groups('profile', $app->profileUser['id'], $limit))));
}

