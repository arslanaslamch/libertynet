<?php

function s_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$id = segment(1);
	$app->setTitle(lang('quiz::manage-questions'));
	print_r($id);die;
	$quiz = get_quiz($id);
	if(is_quiz_owner($quiz)) {
		print_r('here');die;
	} else {
		$message = lang('quiz::quiz-edit-permission-denied');
		redirect(url('quizes'));
	}
}
