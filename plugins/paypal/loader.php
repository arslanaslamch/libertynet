<?php

register_pager("paypal/payment/verification", array('use' => "paypal::paypal@paypal_payment_verification_pager", 'as' => 'paypal-verification'));
register_pager("paypal/payment", array('use' => "paypal::paypal@paypal_payment_pager", 'filter' => 'user-auth', 'as' => 'paypal-process'));



register_pager("paypal/recurring/verification", array('use' => "paypal::paypal@paypal_recurring_verification", 'as' => 'paypal-recurring-verification'));

if(config('enable-paypal', false)) {
	register_hook('payment.buttons.extend', function($type, $id, $name, $description, $price, $return_url, $cancel_url, $coupon = null) {
		echo '  <div class="payment-method paypal">'.view("paypal::button", array('id' => $id, 'price' => $price, 'name' => $name, 'description' => $description, 'type' => $type, 'return_url' => $return_url, 'cancel_url' => $cancel_url, 'coupon' => $coupon)).'</div>';
	});
}

register_hook("membership.segment.allowed", function($allowed_segments) {
	$allowed_segments[] = 'paypal';
	return $allowed_segments;
});

register_hook('user.welcome.segment.allowed', function($allowed_segments) {
	$allowed_segments[] = 'paypal';
	return $allowed_segments;
});

register_hook('interest.segment.allowed', function($allowed_segments) {
    $allowed_segments[] = 'paypal';
    return $allowed_segments;
});
