<?php
function editor_list_pager($app){
	$action = input('action','list');
	$id = input('id');
	if ($action == "list") {
		$lists = get_feed_list();
		return $app->render(view("feed::list/lists", array('lists' => $lists)));
	} elseif ($action =="edit") {
		$message = "";
		$list = find_list($id);
		if(!$list) return false;
		$val = input('val');
		if ($val) {
			$saved = save_feed_list($val, $list);
			if ($saved) {
				return redirect_back();
			}
		}
	return $app->render(view("feed::list/edit", array('list' => $list, 'message' => $message)));
	} elseif ($action == "delete") {
		$list = find_list($id);
		if(!$list) return false;
		list_delete($id);
		return redirect_back();
	}
}

function editor_add_list_pager($app){
	$val = input("val");
	$message = "";
	if ($val) {
		$added = list_add($val);
		if ($added) {
			return redirect_back();
		}
	}
	return $app->render(view("feed::list/add", array('message' => $message)));
}