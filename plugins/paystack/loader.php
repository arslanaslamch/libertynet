<?php
register_pager('paystack/success/callback', array('filter'=> 'auth', 'as' => 'paystack-response', 'use' => 'paystack::paystack@paystack_response_pager'));
if( config('enable-paystack')) {
	register_hook('payment.buttons.extend', function($type, $id, $name, $description, $price, $return_url, $cancel_url, $coupon = null) {
		$key_secret = config('paystack-public-key');
		$display_currency = config('default-currency');
		if ($display_currency != "NGN"){
		    $exchangeUrl = 'https://openexchangerates.org/api/latest.json?app_id=64de858e280e40b4bc2d62c8429bc8ec';
		    $result = json_decode(file_get_contents($exchangeUrl), true);
		    $price = $price *($result['rates']["NGN"]/$result['rates'][$display_currency]);
        }

		$price = round($price * 100);
		$data = array(
		    'price' => $price,
            'id' => $id,
            'type' => $type,
            'name' => $name,
            'description' => $description,
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
            'key' => $key_secret
        );
        echo '<div class="payment-method paystack">'.view("paystack::button", array('data' => $data, 'display_currency' => $display_currency)).'</div>';
	});
}