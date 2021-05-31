<?php
function payment_pager($app) {
	$message = null;
	$id = input('id');
	if($id) {
		$business = get_business_details($id);
		if($business['paid'] == 1) {
			return url_to_pager('all-business');
		}
		$plan = get_business_plan($business['plan_id']);
		if($business['paid'] == 0) {
			if($business['user_id'] == get_userid()) {
				$app->setLayout('layouts/blank')->setTitle(lang('business::select-payment-method'))->onHeaderContent = false;
				return $app->render(view('business::payment', array('plan' => $plan, 'id' => $id)));
			}
		}
	}
	return MyError::error404();
}