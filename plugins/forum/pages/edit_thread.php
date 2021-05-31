<?php

function modify_thread_pager($app) {
	$app->setTitle(lang("forum::edit-thread"));
	$val = input('val');
	$messages = null;
	$id = input('id');
	$thread = forum_get_thread($id);
	$tag_ids = explode(' ', trim($thread['tags']));
    $thread['tags'] = array();
	foreach($tag_ids as $tag_id) {
	    $tag = forum_get_tag($tag_id);
		if($tag) {
            $tag['value'] = $tag['title'];
            $tag['slug'] = $tag['title'];
            $thread['tags'][] = $tag;
		}
	}
	$categories = forum_get_categories();
	if($val) {
		CSRFProtection::validate();
		$validate = validator($val, array(
			'subject' => 'required',
		));
		if(validation_passes()) {
			forum_execute_form($val);
			return redirect_to_pager('forum-thread-slug', array('appends' => '/'.$val['id'].'/'.forum_slugger($val['subject'])));
		} else {
			$messages = validation_first();
		}
	}
	return $app->render(view('forum::edit_thread', array('messages' => $messages, 'id' => $id, 'thread' => $thread, 'categories' => $categories)));
}