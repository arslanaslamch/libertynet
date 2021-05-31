<?php

Class CryptoPay {

    public static $defCoin = 'bitcoin';

    public static function getCoins() {
        $coins = array('bitcoin', 'bitcoincash', 'bitcoinsv', 'litecoin', 'dash', 'dogecoin', 'speedcoin', 'reddcoin', 'potcoin', 'feathercoin', 'vertcoin', 'peercoin', 'monetaryunit', 'universalcurrency');
        return $coins;
    }

    public static function getKeys() {
        $keys = array();
        $coins = self::getCoins();
        foreach ($coins as $coin) {
            $keys[$coin]['public_key'] = config($coin.'-public-key');
            $keys[$coin]['private_key'] = config($coin.'-private-key');
        }

        return $keys;
    }

    public static function getAvailableCoins() {
        $coins = array();
        $keys = CryptoPay::getKeys();
        foreach ($keys as $coin_name => $key) {
            if ($key['public_key'] && $key['private_key']) {
                $coins[] = $coin_name;
            }
        }
        return $coins;
    }

    public static function getAvailableKeys() {
        $available_keys = array();
        $keys = CryptoPay::getKeys();
        foreach ($keys as $coin_name => $key) {
            if ($key['public_key'] && $key['private_key']) {
                $available_keys[$coin_name] = $key;
            }
        }

        return $available_keys;
    }

    public static function getPrivateKeys() {
        $private_keys = array();
        $available_keys = CryptoPay::getAvailableKeys();
        foreach ($available_keys as $coin_name => $key) {
            if ($key['public_key'] && $key['private_key']) {
                $private_keys[] = $key['private_key'];
            }
        }

        return $private_keys;
    }

    public static function getOptions($type, $type_id, $price) {
        $user_id = get_userid();
        $user_format = 'COOKIE';
        $order_id = $type.'-'.$type_id.'-'.$user_id;
        $amount_usd = convert_currency_live(config('default-currency'), 'USD', $price);
        $period = 'NOEXPIRY';
        $def_language = get_lang_id();
        $def_coin = self::$defCoin;
        $coins = self::getAvailableCoins();
        $all_keys = self::getAvailableKeys();


        $def_coin = strtolower($def_coin);
        if (!in_array($def_coin, $coins)) $coins[] = $def_coin;
        foreach ($coins as $v) {
            if (!isset($all_keys[$v]['public_key']) || !isset($all_keys[$v]['private_key'])) {
                die('Please add your public/private keys for '.$v.' in $all_keys variable');
            } elseif (!strpos($all_keys[$v]['public_key'], 'PUB')) {
                die('Invalid public key for '.$v.' in $all_keys variable');
            } elseif (!strpos($all_keys[$v]['private_key'], 'PRV')) {
                die('Invalid private key for '.$v.' in \$all_keys variable');
            } elseif (strpos(CRYPTOBOX_PRIVATE_KEYS, $all_keys[$v]['private_key']) === false) {
                die('Please add your private key for '.$v.' in variable $cryptobox_private_keys, file /lib/cryptobox.config.php.');
            }
        }

        $coin_name = cryptobox_selcoin($coins, $def_coin);

        $public_key = $all_keys[$coin_name]['public_key'];
        $private_key = $all_keys[$coin_name]['private_key'];

        $options = array(
            'public_key' => $public_key,
            'private_key' => $private_key,
            'webdev_key' => '',
            'orderID' => $order_id,
            'userID' => $user_id,
            'user_format' => $user_format,
            'amount' => 0,
            'amountUSD' => $amount_usd,
            'period' => $period,
            'language' => $def_language
        );

        return $options;
    }
}