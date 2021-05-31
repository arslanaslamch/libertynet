<?php
//get_menu('user-profile', 'connections')->setActive();
function quizes_pager($app) {
    $limit = config('quiz-list-limit', 12);
    $quizes = get_quizes('mine', null, null, $app->profileUser['id'], $limit);
	return $app->render(view('quiz::profile/list', array('quizes' => $quizes)));
}