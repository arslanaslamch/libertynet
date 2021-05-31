<?php
function add_images_pager($app) {
	$app->setTitle(lang("business::add-photos"));
	$message = null;
	$business_id = input('id');
	$business_images = business_get_business_images($business_id);
	$num_business_images = count($business_images);
	$val = input('val');
	if($val) {
		$images = input_file('images');
		$images_paths = array();
		$images_max = config('max-num-business-photos', 5);
		if($images) {
			if($images_max >= ($num_business_images) + count($images)) {
				$uploader = new Uploader(null, 'image', $images);
				if($uploader->passed()) {
					foreach($images as $image) {
						$uploader = new Uploader($image);
						if($uploader->passed()) {
							$uploader->setPath('business/properties/images/');
							$image_path = $uploader->resize()->result();
							$images_paths[] = $image_path;
						} else {
							$message = $uploader->getError();
							break;
						}
					}
				} else {
					$message = $uploader->getError();
				}
			} else {
				$message = lang('business::images-max-error', array('max' => $images_max));
			}
		}
		if(!$message) {
			$val['images_paths'] = $images_paths;
			business_execute_form($val);
			return redirect_to_pager('business-page', array('slug' => business_get_slug($business_id, 'business')));
		}
	}
	return $app->render(view('business::add_images', array('business_id' => $business_id, 'business_images' => $business_images, 'num_business_images' => $num_business_images, 'message' => $message)));
}