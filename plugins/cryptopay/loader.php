<?php

define('CRYPTOBOX_PHP_FILES_PATH', path('plugins/cryptopay/lib/'));
define('CRYPTOBOX_IMG_FILES_PATH', img('cryptopay::images/'));
define('CRYPTOBOX_JS_FILES_PATH', url('plugins/cryptopay/js/'));
define('CRYPTOBOX_LANGUAGE_HTMLID', 'cryptopay-lang');
define('CRYPTOBOX_COINS_HTMLID', 'cryptopay-coin');
define('CRYPTOBOX_PREFIX_HTMLID', 'cryptopay_');

load_functions('cryptopay::cryptopay');

if (config('enable-cryptopay', false)) {
    require_once(CRYPTOBOX_PHP_FILES_PATH.'cryptobox.class.php');
}

register_asset('cryptopay::css/cryptopay.css');
register_asset('cryptopay::js/support.min.js');

register_pager('cryptopay/payment', array(
    'use' => 'cryptopay::cryptopay@payment',
    'as' => 'cryptopay-payment')
);

register_pager('cryptopay/payment/callback', array(
    'use' => 'cryptopay::cryptopay@payment_callback',
    'as' => 'cryptopay-payment-callback')
);

if (config('enable-cryptopay', false)) {
    register_hook('payment.buttons.extend', function ($type, $type_id, $name, $description, $price, $return_url, $cancel_url, $coupon = null) {
        echo '<div class="payment-method cryptopay">'.view('cryptopay::button', array('type' => $type, 'type_id' => $type_id, 'price' => $price, 'coupon' => $coupon, 'name' => $name, 'description' => $description, 'return_url' => $return_url, 'cancel_url' => $cancel_url)).'</div>';
    });
}

register_hook('membership.segment.allowed', function ($allowed_segments) {
    $allowed_segments[] = 'cryptopay';
    return $allowed_segments;
});

register_hook('user.welcome.segment.allowed', function ($allowed_segments) {
    $allowed_segments[] = 'cryptopay';
    return $allowed_segments;
});