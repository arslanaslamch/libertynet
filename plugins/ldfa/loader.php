<?php

register_pager("ldfa/payment/verification", array('use' => "ldfa::ldfa@ldfa_payment_verification_pager", 'as' => 'ldfa-verification'));
register_pager("ldfa/payment", array('use' => "ldfa::ldfa@ldfa_payment_pager", 'filter' => 'user-auth', 'as' => 'ldfa-process'));



register_pager("ldfa/recurring/verification", array('use' => "ldfa::ldfa@ldfa_recurring_verification", 'as' => 'ldfa-recurring-verification'));

if(config('enable-ldfa', false)) {
	register_hook('payment.buttons.extend', function($type, $id, $name, $description, $price, $return_url, $cancel_url, $coupon = null) {
		echo '  <div class="payment-method ldfa">'.view("ldfa::button", array('id' => $id, 'price' => $price, 'name' => $name, 'description' => $description, 'type' => $type, 'return_url' => $return_url, 'cancel_url' => $cancel_url, 'coupon' => $coupon)).'</div>';
	});  
}

register_hook("membership.segment.allowed", function($allowed_segments) {
	$allowed_segments[] = 'ldfa';
	return $allowed_segments;
});

register_hook('user.welcome.segment.allowed', function($allowed_segments) {
	$allowed_segments[] = 'ldfa';
	return $allowed_segments; 
});

register_hook('interest.segment.allowed', function($allowed_segments) {
    $allowed_segments[] = 'ldfa';
    return $allowed_segments;
});
