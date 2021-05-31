<?php
function edit_business_pager($app) {
	$app->setTitle(lang("business::edit-business"));
	$message = null;
	$categories = business_get_categories();
	$val = input('val');
	$business_id = input('id');
	if($business_id) {
		if($val) {
			CSRFProtection::validate();
			$image = input_file('image');
			$image_path = '';
			if($image) {
				$uploader = new Uploader($image);
				if($uploader->passed()) {
					$uploader->setPath('business/businesses/images/');
					$image_path = $uploader->resize()->result();
				} else {
					$message = $uploader->getError();
				}
				$val['image_path'] = $image_path;
			}
			if(!$message) {
				business_execute_form($val);
				return redirect_to_pager('business-page', array('slug' => business_get_slug($business_id, 'business')));
			}
		}
		$business = get_business_details($business_id);
		return $app->render(view('business::edit_business', array('categories' => $categories, 'business' => $business, 'business_id' => $business_id, 'message' => $message)));
	}
}