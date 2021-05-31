<?php
load_functions("quiz::quiz");
register_asset("quiz::css/quiz.css");
register_asset("quiz::js/quiz.js");
register_hook("role.permissions", function($roles) {
	$roles[] = array(
		'title' => lang('quiz::quiz-permissions'),
		'description' => '',
		'roles' => array(
			'can-create-quiz' => array('title' => lang('quiz::can-create-quiz'), 'value' => 1),

		)
	);
	return $roles;
});
register_pager("quizes", array('use' => 'quiz::quiz@quiz_pager', 'as' => 'quizes'));
register_pager("quiz/add", array('use' => 'quiz::quiz@add_quiz_pager', 'filter' => 'auth', 'as' => 'quiz-add'));
register_pager("quiz/manage", array('use' => 'quiz::quiz@manage_pager', 'as' => 'quiz-manage', 'filter' => 'auth'));
register_pager("quizes/api", array('use' => 'blog::blog@blog_api_pager'));

register_hook('system.started', function() {
	register_pager("quiz/{slug}", array('use' => 'quiz::quiz@quiz_page_pager', 'as' => 'quiz-page'))->where(array('slug' => '[a-zA-Z0-9\-\_\.]+'));
	register_pager("quiz/{slug}/question", array('use' => "quiz::quiz@quiz_question_pager", 'as' => 'quiz-question'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
	register_pager("quiz/{slug}/create", array('use' => "quiz::quiz@quiz_create_question_pager", 'as' => 'quiz-question-create'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
	register_pager("quiz/{slug}/publish", array('use' => "quiz::quiz@quiz_publish_pager", 'as' => 'quiz-question-publish'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
	register_pager("quiz/{slug}/result", array('use' => "quiz::quiz@quiz_result_pager", 'as' => 'quiz-result'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
	register_pager("quiz/{slug}/participants", array('use' => "quiz::quiz@quiz_participants_pager", 'as' => 'quiz-participants'))->where(array('slug' => '[a-zA-Z0-9\_\-]+'));
});
register_get_pager("quiz/publish", array("use" => "quiz::quiz@process_publish_pager", 'filter' => 'auth'));
register_pager("quiz/question/add", array('use' => 'quiz::quiz@add_quiz_question_pager', 'filter' => 'auth', 'as' => 'quiz-question-add'));
register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		register_asset("help::css/help.css");
		register_asset("help::js/help.js");
	}
});
register_pager("admincp/quizes", array('use' => "quiz::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quizes'));
register_pager("admincp/quiz/add", array('use' => "quiz::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quiz-add'));
register_pager("admincp/quiz/manage", array('use' => "quiz::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quiz-manage'));
register_pager("admincp/quiz/manage/question", array('use' => "quiz::admincp@manage_question_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quiz-manage-question'));
register_pager("admincp/quiz/categories", array('use' => "quiz::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quiz-categories'));
register_pager("admincp/quiz/categories/add", array('use' => "quiz::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quiz-categories-add'));
register_pager("admincp/quiz/category/manage", array('use' => "quiz::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-quiz-manage-category'));

register_hook("admin-started", function() {

	add_menu("admin-menu", array('icon' => 'ion-document-text', "id" => "admin-quizes", "title" => lang('quiz::manage-quizes'), "link" => '#'));
	get_menu("admin-menu", "plugins")->addMenu(lang('quiz::quizes-manager'), '#', 'admin-quizes');
	get_menu("admin-menu", "plugins")->findMenu('admin-quizes')->addMenu(lang('quiz::lists'), url_to_pager("admincp-quizes"), "manage");
	get_menu("admin-menu", "plugins")->findMenu('admin-quizes')->addMenu(lang('quiz::add-new-quiz'), url_to_pager("admincp-quiz-add"), "add");
	get_menu("admin-menu", "plugins")->findMenu('admin-quizes')->addMenu(lang('quiz::manage-categories'), url_to_pager("admincp-quiz-categories"), "categories");

});
register_hook('admin.statistics', function($stats) {
	$stats['quizes'] = array(
		'count' => count_total_quizes(),
		'title' => lang('quiz::quizes'),
		'icon' => 'fa fa-quiz',
		'link' => url_to_pager('quizes'),
	);
	return $stats;
});

register_hook("comment.add", function($type, $typeId, $text) {
	if($type == 'quiz') {
		$quiz = get_quiz($typeId);
		$subscribers = get_subscribers($type, $typeId);
		foreach($subscribers as $userid) {
			if($userid != get_userid()) {
				send_notification_privacy('notify-site-comment', $userid, 'quiz.comment', $typeId, $quiz, null, $text);
			}
		}

	}
});

register_hook("like.item", function($type, $typeId, $userid) {
	if($type == 'quiz') {
		$quiz = get_quiz($typeId);
		if($quiz['user_id'] and $quiz['user_id'] != get_userid()) {
			send_notification_privacy('notify-site-like', $quiz['user_id'], 'quiz.like', $typeId, $quiz);
		}
	} elseif($type == 'comment') {
		$comment = find_comment($typeId, false);
		if($comment and $comment['user_id'] != get_userid()) {
			if($comment['type'] == 'quiz') {
				$quiz = get_quiz($comment['type_id']);
				send_notification_privacy('notify-site-like', $comment['user_id'], 'quiz.like.comment', $comment['type_id'], $quiz);
			}
		}
	}
});

register_hook("comment.add", function($type, $typeId, $text) {
	if($type == 'quiz') {
		$quiz = get_quiz($typeId);
		$subscribers = get_subscribers($type, $typeId);
		if(!in_array($quiz['user_id'], $subscribers)) {
			$subscribers[] = $quiz['user_id'];
		}
		foreach($subscribers as $userid) {
			if($userid != get_userid()) {
				send_notification_privacy('notify-site-comment', $userid, 'quiz.comment', $typeId, $quiz, null, $text);
			}
		}

	}
});

register_hook("display.notification", function($notification) {
	if($notification['type'] == 'quiz.like') {
		return view("quiz::notifications/like", array('notification' => $notification, 'quiz' => unserialize($notification['data'])));
		delete_notification($notification['notification_id']);
	} elseif($notification['type'] == 'quiz.like.comment') {
		return view("quiz::notifications/like-comment", array('notification' => $notification, 'quiz' => unserialize($notification['data'])));
		delete_notification($notification['notification_id']);
	} elseif($notification['type'] == 'quiz.comment') {
		return view("quiz::notifications/comment", array('notification' => $notification, 'quiz' => unserialize($notification['data'])));
		delete_notification($notification['notification_id']);
	} elseif($notification['type'] == 'answer.quiz.questions') {
		$quiz = get_quiz($notification['type_id']);
		return view("quiz::notifications/answerd", array('notification' => $notification, 'quiz' => $quiz));
		delete_notification($notification['notification_id']);
	}
});

add_menu_location('quizes-menu', lang('quiz::quizes-menu'));
add_available_menu('quiz::quizes', 'quizes', 'fa fa-quiz');

register_pager("{id}/quizes", array("use" => "quiz::user-profile@quizes_pager", "as" => "profile-quizes", 'filter' => 'profile'))
	->where(array('id' => '[a-zA-Z0-9\_\-\.]+'));


register_hook('profile.started', function($user) {
	add_menu('user-profile-more', array('title' => lang('quiz::quizes'), 'as' => 'quizes', 'link' => profile_url('quizes', $user)));
});

register_block("quiz::block/profile-recent", lang('quiz::user-profile-recent-quizes'), null, array(
		'limit' => array(
			'title' => lang('list-limit'),
			'description' => lang('list-limit-desc'),
			'type' => 'text',
			'value' => 6
		),)
);

register_block("quiz::block/latest", lang('quiz::latest-quizes'), null, array(
		'limit' => array(
			'title' => lang('list-limit'),
			'description' => lang('list-limit-desc'),
			'type' => 'text',
			'value' => 6
		),)
);


//page blocks
register_hook('admin-started', function() {
	register_block_page('quizes', lang('quiz::quizes'));

});

register_hook('user.delete', function($userid) {
	$d = db()->query("SELECT * FROM quizes WHERE user_id='{$userid}'");
	while($quiz = $d->fetch_assoc()) {
		delete_quiz($quiz['id']);
	}
});

register_hook('uid.check', function($result, $value, $type = null, $type_id = null) {
	if(!$type || $type == 'quiz') {
		$quiz = get_quiz($value);
		if($quiz) {
			if(!$type_id || ($type_id && $type_id != $quiz['id'])) {
				$result[0] = false;
			}
		}
	}
	return $result;
});

register_hook('quiz.added', function($quizId, $quiz) {
    if($quiz['entity_type'] == 'user') {
        add_activity($quiz['slug'], null, 'quiz', $quizId, $quiz['privacy']);
        add_quiz_feed(array(
            'entity_id' => $quiz['entity_id'],
            'entity_type' => $quiz['entity_type'],
            'type' => 'feed',
            'type_id' => 'quiz-added',
            'type_data' => $quiz['id'],
            'quiz' => $quizId,
            'privacy' => $quiz['privacy'],
            'images' => '',
            'auto_post' => true,
            'can_share' => 1,
			'status' => 0

        ));
    }
});

register_hook('feed.arrange', function($feed) {
    if(is_numeric($feed['quiz'])) {
        $quiz = get_quiz($feed['quiz']);

        if($quiz) {
            if($quiz['status'] == 0 and ($quiz['user_id'] != get_userid())) $feed['status'] = 0;
            $feed['quizDetails'] = $quiz;
        }
    }
    return $feed;
});
register_hook("activity.title", function($title, $activity, $user) {
    switch($activity['type']) {
        case 'quiz':
            $quizId = $activity['type_id'];
            $quiz = get_quiz($quizId);
            if(!$quiz) return "invalid";
            $link = $quiz['slug'];
            $owner = get_quiz_publisher($quiz);
            $owner['link'] = url($owner['id']);
            return activity_form_link($owner['link'], $owner['name'], true)." ".lang("activity::added-new")." ".activity_form_link($activity['link'], lang('quiz::quiz'), true, true);
            break;
    }
    return $title;
});

register_hook('feed-title', function($feed) {
    if($feed['type_id'] == "quiz-added") {
        echo lang('quiz::added-quiz');
    }
});

register_hook('feed.custom.column.sql', function ($columns, $val){
    if (isset($val['quiz'])){
        $columns[0] = !($columns) ? '' : ',quiz';
    }
    return $columns;
});

register_hook('feed.custom.value.sql', function ($value, $val){
    if (isset($val['quiz'])){
        $value[0] = !($value) ? '' : ",'{$val['quiz']}'";
    }
    return $value;
});

register_hook('feeds.query.fields', function ($fields, $more = null){
    $fields .=",quiz";
   return $fields;
});
register_hook('feed.post.plugins.hook', function($feed) {
    if ($feed['quiz']){
        $quiz = get_quiz($feed['quiz']);
        if ($quiz) echo view("quiz::feed-content", array('quiz'=> $quiz));
    }
});
register_hook('start.quiz.load', function ($quiz) {
    echo view('quiz::take-quiz', array('quiz' => $quiz));
});
register_hook('view.result.load', function ($quiz,$user) {
    echo view('quiz::view-result', array('quiz' => $quiz,'user' => $user));
});
register_pager("quiz/submit/answers", array('use' => 'quiz::quiz@submit_quiz_answers_pager', 'filter' => 'auth', 'as' => 'quiz-submit-answers'));
register_hook('shortcut.menu.images', function ($images){
    $images['fa fa-quiz'] = img('quiz::images/quiz.png');
    return $images;
});