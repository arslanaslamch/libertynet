<?php
function quiz_pager($app) {
	$app->setTitle(lang('quiz::quizes'));
	$type = input('type', 'all');
	$category = input('category');
	$term = input('term');
	$filter = input('filter', 'all');
    $list_type = cookie_get('quiz-list-type', 'list');
    $limit = config('quiz-list-limit', 12);
	return $app->render(view('quiz::lists', array('quizes' => get_quizes($type, $category, $term, null, $limit, $filter), 'list_type' => $list_type)));
}
function quiz_page_pager($app) {
	$slug = segment(1);
	$quiz = get_quiz($slug);
	if(!$quiz or (!$quiz['status'] and !is_quiz_owner($quiz))) return redirect(url('quizes'));
	$app->quiz = $quiz;
	if($quiz['status']) db()->query("UPDATE quizes SET views = views + 1 WHERE slug='{$slug}'");
	$app->setTitle($quiz['title'])->setKeywords($quiz['tags'])->setDescription(str_limit(strip_tags($quiz['content']), 100));
	set_meta_tags(array('name' => get_setting("site_title", "Crea8social"), 'title' => $quiz['title'], 'description' => str_limit(strip_tags($quiz['content']), 100), 'image' => $quiz['image'] ? url_img($quiz['image'], 200) : '', 'keywords' => $quiz['tags']));
	return $app->render(view('quiz::view', array('quiz' => $quiz)));
}
function add_quiz_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';
	$app->setTitle(lang('quiz::add-new-quiz'));
    $val = input('val', null, array('content'));
	if(user_has_permission('can-create-quiz') && config('allow-members-create-quiz', true)) {
		if($val) {
			CSRFProtection::validate();
			$validate = validator($val, array(
				'category' => 'required',
				'title' => 'required',
				'content' => 'required',
				'pass_mark' => 'required',
			));
			if(validation_passes()) {
				//print_r('here');die;
				$quiz_id = add_quiz($val);
				if($quiz_id) {
					$status = 1;
					$message = lang('quiz::quiz-add-success');
					$quiz = get_quiz($quiz_id);
					$redirect_url = url('quiz/'.$quiz['slug']);
					
				} else {
					$message = lang('quiz::quiz-add-error');
				}
			} else {
				$message = validation_first();
			}
		}
	} else {
		$message = lang('quiz::quiz-add-permission-denied');
		$redirect_url = url('quizes');
	}

	if(input('ajax')) {
		$result = array(
			'status' => (int) $status,
			'message' => (string) $message,
			'redirect_url' => (string) $redirect_url,
		);
		$response = json_encode($result);
		return $response;
	}

	if($redirect_url) {
		return redirect($redirect_url);
	}

	return $app->render(view("quiz::add", array('message' => $message)));
}
function manage_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$action = input('type');
	$app->setTitle(lang('quiz::manage-quizes'));
	$id = input('id');
	$quiz = get_quiz($id);
	if(is_quiz_owner($quiz)) {
		switch($action) {
			case 'delete':
				delete_quiz($id);
				return redirect(url('quizes?type=mine'));
			break;
			case 'edit':
				$id = input('id');
				$quiz = get_quiz($id);
				$val = input('val', null, array('content'));
				if($val) {
					CSRFProtection::validate();
					$validate = validator($val, array(
						'category' => 'required',
						'title' => 'required',
						'content' => 'required'
					));
					if(validation_passes()) {
						$save = save_quiz($val, $quiz);
						if($save) {
							$quiz = get_quiz($quiz['id']);
							$status = 1;
							$message = lang('quiz::quiz-edit-success');
							$redirect_url = url('quiz/'.$quiz['slug']);
						} else {
							$message = lang('quiz::quiz-save-error');
						}
					} else {
						$message = validation_first();
					}
					if(input('ajax')) {
						$result = array(
							'status' => (int) $status,
							'message' => (string) $message,
							'redirect_url' => (string) $redirect_url,
						);
						$response = json_encode($result);
						return $response;
					}
					if($redirect_url) {
						return redirect($redirect_url);
					}
				}
				return $app->render(view('quiz::edit', array('quiz' => $quiz, 'message' => $message)));
			break;
		}
	} else {
		$message = lang('quiz::quiz-edit-permission-denied');
		redirect(url('quizes'));
	}
}
function quiz_question_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$id = segment(1);
	$app->setTitle(lang('quiz::manage-questions'));
	$quiz = get_quiz($id);
	$question_limit = config('quiz-question-limit', 3);
	if(is_quiz_owner($quiz)) {
		return $app->render(view('quiz::question/details', array('quiz' => $quiz,'quiz_questions' => get_quiz_questions($id),'count' => count_quiz_user_questions($id),'question_limit' => $question_limit,'id' => $id, 'message' => $message)));
	} else {
		$message = lang('quiz::quiz-edit-permission-denied');
		redirect(url('quizes'));
	}
}
function quiz_create_question_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$id = segment(1);
	$app->setTitle(lang('quiz::create-questions'));
	$quiz = get_quiz($id);
	if(is_quiz_owner($quiz)) {
		return $app->render(view('quiz::question/create', array('quiz' => $quiz,'id' => $id, 'message' => $message)));
	} else {
		$message = lang('quiz::quiz-edit-permission-denied');
		redirect(url('quizes'));
	}
}
function add_quiz_question_pager($app) {
    $status = 0;
    $message = '';
    $redirect_url = '';
    $url = '';
    $app->setTitle(lang('quiz::add-quiz-question'));
    $val = input('val');

    if($val) {
        CSRFProtection::validate(); 
            $q_id = add_quiz_question($val);
            if($q_id) {
                $status = 1;
                $id = $val['quiz_id'];
                $message = lang('quiz::question-add-success');
                $redirect_url = url('quiz/'.$id.'/question');
            } else {
                $message = lang('quiz::question-add-error');
            }
    }
    if(input('ajax')) {
        $result = array(
            'status' => (int) $status,
            'message' => (string) $message,
            'redirect_url' => (string) $redirect_url,
        );
        $response = json_encode($result);
        return $response;
    }
    if($redirect_url) {
        return redirect($redirect_url);
    }
   
    return $app->render(view("quiz::question/create", array('message' => $message)));
}
function quiz_publish_pager($app) {
	$status = 0;
	$message = '';
    $app->setTitle(lang('quiz::publish-quiz'));
	$id = segment(1);
	$quiz = get_quiz($id);
	//$url = url_to_pager('quiz/'.$quiz['slug']);
	//print_r('here');die;
    if($quiz) {
        $publish = publish_quiz($id);
        if($publish) {
            $status = 1;
            $message = lang('quiz::quiz-publish-success');
            return redirect(url_to_pager('quiz/'.$quiz['slug']));
        } else {
            $message = lang('quiz::quiz-publish-error');
			return redirect(url_to_pager('quiz/'.$id.'/question'));
        }
    }
  
}
function process_publish_pager($app) {
    CSRFProtection::validate(false);
    publish_quizz(input('type'), input('id'));
}
function submit_quiz_answers_pager($app) {
    
    $status = 0;
    $message = '';
    $redirect_url = '';
    $url = '';
    $app->setTitle(lang('quiz::submit-quiz-answers'));
    $val = input('val');

    if($val) {
        CSRFProtection::validate();
        if(validation_passes()) {
			//echo 'here';die;
			$quiz_id = $val['quiz_id'];
			$quiz = get_quiz($quiz_id);
			$quiz_owner = $quiz['user_id'];
            $answer_id  = add_quiz_answers($val);
            if($answer_id) {
                $status = 1;
				$id = $val['quiz_id'];
                $message = lang('quiz::quiz-answered-success');
				send_notification($quiz_owner, 'answer.quiz.questions', $quiz_id);
				$redirect_url = url('quiz/'.$id.'/result');
            } else {
                $message = lang('quiz::quiz-answered-error');
            }
        } else {
            $message = validation_first();
        }
    }
    if(input('ajax')) {
        $result = array(
            'status' => (int) $status,
            'message' => (string) $message,
            'redirect_url' => (string) $redirect_url,
        );
        $response = json_encode($result);
        return $response;
    }
    if($redirect_url) {
        return redirect($redirect_url);
    }
    return $app->render(view("quiz::take-quiz", array('message' => $message)));
}
function quiz_result_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$id = segment(1);
	$app->setTitle(lang('quiz::manage-questions'));
	$quiz = get_quiz_answers($id);
	return $app->render(view('quiz::result', array('quiz' => $quiz,'quiz_id' => $id,'questions' => get_quiz_questions($id), 'message' => $message)));
}
function quiz_participants_pager($app) {
	$status = 0;
	$message = '';
	$redirect_url = '';

	$id = segment(1);
	$app->setTitle(lang('quiz::manage-quiz-participants'));
	$quiz = get_quiz($id);
	return $app->render(view('quiz::participants', array('quiz' => $quiz,'id' => $id,'participants' => get_quiz_participants($id), 'message' => $message)));
}