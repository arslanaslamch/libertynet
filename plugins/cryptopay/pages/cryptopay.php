<?php

function payment($app) {
    $app->setTitle(lang('cryptopay::cryptopay-payment'));
    $type = input('type');
    $type_id = input('type_id');
    $price = input('price');
    $coupon = input('coupon');
    $name = input('name');
    $description = input('description');
    $return_url = input('return_url');
    $cancel_url = input('cancel_url');

    if (input('webview')) {
        if (preg_match('#\?#', $return_url)) {
            $return_url .= '&webview=true&api_userid='.get_userid();
        } else {
            $return_url .= '?webview=true&api_userid='.get_userid();
        }

        if(preg_match('#\?#', $cancel_url)) {
            $cancel_url .= '&webview=true&api_userid='.get_userid();
        } else {
            $cancel_url .= '?webview=true&api_userid='.get_userid();
        }
    }

    $options = CryptoPay::getOptions($type, $type_id, $price);
    $box = new Cryptobox($options);
    $def_coin = CryptoPay::$defCoin;
    $coins = CryptoPay::getAvailableCoins();
    $def_language = get_lang_id();
    $custom_text = '<p class="lead">'.$name.'</p>';
    $custom_text .= '<p class="lead">'.$description.'</p>';

    $cryptobox = $box->display_cryptobox_bootstrap($coins, $def_coin, $def_language, $custom_text, 70, 200, true, img('cryptopay::images/your_logo.png'), 'default', 250, '', 'curl', false);

    return $app->render(view('cryptopay::payment', array('type' => $type, 'type_id' => $type_id, 'price' => $price, 'coupon' => $coupon, 'name' => $name, 'description' => $description, 'return_url' => $return_url, 'cancel_url' => $cancel_url, 'cryptobox' => $cryptobox)));
}

function payment_callback($app) {
    include_once CRYPTOBOX_PHP_FILES_PATH.'cryptobox.callback.php';
}
