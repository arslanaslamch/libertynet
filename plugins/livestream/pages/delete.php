<?php
/**
 * Livestream Delete Page
 * @param App $app
 * @return bool|string
 */
function delete_livestream_page($app) {
	$id = input('id');
	Livestream::delete($id);
	return redirect_to_pager('livestream-list');
}