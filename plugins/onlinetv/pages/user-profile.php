<?php
//get_menu('user-profile', 'connections')->setActive();
function onlinetvs_pager($app) {
	$onlinetvs = get_onlinetvs('mine', null, null, $app->profileUser['id']);
	return $app->render(view('onlinetv::profile/list', array('onlinetvs' => $onlinetvs)));
}