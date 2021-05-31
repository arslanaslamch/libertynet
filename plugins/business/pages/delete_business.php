<?php
function delete_business_pager($app) {
	$app->setTitle(lang("business::delete-business"));
	$message = null;
	$categories = business_get_categories();
	$business_id = input('id');
	$val = input('val');
	if($business_id) {
		if($val) {
			CSRFProtection::validate();
			business_execute_form($val);
			return redirect(url("businesses"));
		}
		$business = business_get_business($business_id);
		return $app->render(view('business::delete_business', array('categories' => $categories, 'business' => $business, 'message' => $message)));
	}
}