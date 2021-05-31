<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-quizes')->setActive();
function lists_pager($app) {
	$app->setTitle('Manage Quizes');
	return $app->render(view('quiz::admincp/lists', array('quizes' => admin_get_quizes(input('term')))));
}


function manage_pager($app) {
	$action = input('action', 'order');
	$app->setTitle(lang('quiz::manage-quizes'));
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_quiz($id);
			return redirect_back();
		break;
		case 'edit':
			$id = input('id');
			$quiz = get_quiz($id);
			if(!$quiz) return redirect_back();
            $val = input('val', null, array('content'));
			if($val) {
				CSRFProtection::validate();
				save_quiz($val, $quiz, true);
				return redirect_to_pager('admincp-quizes');

			}
			return $app->render(view('quiz::admincp/edit', array('quiz' => $quiz)));
		break;
		default:
			$ids = input('data');
			for($i = 0; $i < count($ids); $i++) {
				update_help_category_order($ids[$i], $i);
			}
		break;
	}
}

function add_pager($app) {
	$app->setTitle(lang('quiz::add-new-quiz'));
	$message = null;
    $val = input('val', null, array('content'));
	if($val) {
		CSRFProtection::validate();
		$validate = validator($val, array(
			'category' => 'required',
				'title' => 'required',
				'content' => 'required',
				'pass_mark' => 'required',
		));

		if(validation_passes()) {
			$quiz_id = add_quiz($val);
			return redirect_to_pager('admincp-quizes');
		} else {
			$message = validation_first();
		}
	}
	return $app->render(view('quiz::admincp/add', array('message' => $message)));
}

function categories_pager($app) {
	$app->setTitle(lang('quiz::manage-categories'));
    $categories = (input('id')) ? get_quiz_parent_categories(input('id')) : get_quiz_categories();
	return $app->render(view('quiz::admincp/categories/lists', array('categories' => $categories)));
}

function categories_add_pager($app) {
	$app->setTitle(lang('quiz::add-category'));
	$message = null;

	$val = input('val');
    if($val) {
        CSRFProtection::validate();
		quiz_add_category($val);
		return redirect_to_pager('admincp-quiz-categories');
		//redirect to category lists
	}

	return $app->render(view('quiz::admincp/categories/add', array('message' => $message)));
}

function manage_category_pager($app) {
	$action = input('action', 'order');
	$id = input('id');
	switch($action) {
		default:
			$ids = input('data');
			for($i = 0; $i < count($ids); $i++) {
				update_quiz_category_order($ids[$i], $i);
			}
		break;
		case 'edit':
			$message = null;
			$image = null;
			$val = input('val');
			$app->setTitle(lang('quiz::edit-category'));
			$category = get_quiz_category($id);
			if(!$category) return redirect_to_pager('admincp-quiz-categories');
			if($val) {
				CSRFProtection::validate();
				$file = input_file('file');

				save_quiz_category($val, $category);
				return redirect_to_pager('admincp-quiz-categories');
				//redirect to category lists
			}
			return $app->render(view('quiz::admincp/categories/edit', array('message' => $message, 'category' => $category)));
		break;
		case 'delete':
			$category = get_quiz_category($id);
			if(!$category) return redirect_to_pager('admincp-quiz-categories');
			delete_quiz_category($id, $category);
			return redirect_to_pager('admincp-quiz-categories');
		break;
	}
	return $app->render();
}
function manage_question_pager($app) {
	$action = input('action', 'order');
	$app->setTitle(lang('quiz::manage-quiz'));
	switch($action) {
		case 'delete':
			$id = input('id');
			delete_quiz($id);
			return redirect_back();
		break;
		case 'view':
			$id = input('id');
			$quiz = get_quiz($id);
			if(!$quiz) return redirect_back();
            
			return $app->render(view('quiz::admincp/view', array('quiz' => $quiz)));
		break;
		case 'add':
			$id = input('id');
			$quiz = get_quiz($id);
			if(!$quiz) return redirect_back();
            $question_limit = config('quiz-question-limit', 3);
			return $app->render(view('quiz::admincp/add-question', array('quiz' => $quiz,'quiz_questions' => get_quiz_questions($id),'count' => count_quiz_user_questions($id),'question_limit' => $question_limit,'id' => $id,)));
		break;
		case 'create':
			$id = input('id');
			$quiz = get_quiz($id);
			if(!$quiz) return redirect_back();
			return $app->render(view('quiz::admincp/create', array('quiz' => $quiz,'id' => $id,)));
		break;
		case 'add-q':
			$id = input('id');
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
						redirect(url_to_pager('admincp-quiz-manage-question').'?action=add&id='.$id);
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
		   
			return $app->render(view("quiz::admincp/create", array('message' => $message)));
		break;
		default:
			$ids = input('data');
			for($i = 0; $i < count($ids); $i++) {
				update_help_category_order($ids[$i], $i);
			}
		break;
	}
}