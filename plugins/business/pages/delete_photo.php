<?php
function delete_photo_pager($app) {
	$app->setTitle(lang("business::delete-photo"));
	$message = null;
	$val = input('val');
	$business_id = input('id');
	if($val) {
		CSRFProtection::validate();
		property_execute_form($val);
		return true ? json_encode(array('status' => true)) : redirect_to_pager('business-page', array('slug' => business_get_slug($business_id, 'business')));
	}
	$business_image = business_get_business_images($business_id);
	return $app->render(view('business::delete_photo', array('business_id' => $business_id, 'business_image' => $business_image, 'message' => $message)));
}