<?php
/**
 * Livestream List Page
 * @param App $app
 * @return bool|string
 */
function list_page($app) {
	$app->setTitle(lang('livestream::livestream'));
	$category_id = input('c', null);
    $category_id = $category_id ? $category_id : null;
	$term = input('s', null);
	$term = $term ? $term : null;
	$user_id = input('u', null);
    $user_id = $user_id ? $user_id : null;
    $type = input('t', null);
	$featured = $type == 'f' ? 1 : null;
	$status = $type == 'o' ? 1 : 3;
	$filter = array('category_id' => $category_id, 'term' => $term, 'user_id' => $user_id, 'featured' => $featured, 'status' => $status);
	$categories = Livestream::getCategories();
	$category = is_numeric($category_id) ? lang(Livestream::getCategory($category_id)['title']) : lang('livestream::all-categories');
	$livestreams = Livestream::getLivestreams($filter);
	$message = null;
	return $app->render(view('livestream::livestream/list', array('categories' => $categories, 'category' => $category, 'category_id' => $category_id, 'livestreams' => $livestreams, 'search' => $term,'message' => $message)));
}