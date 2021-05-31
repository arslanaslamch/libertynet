<?php
/**
 *  ... Please MODIFY this file ...
 *
 *
 *  YOUR MYSQL DATABASE DETAILS
 *
 */

define('DB_HOST', config('mysql_host'));
define('DB_USER', config('mysql_user'));
define('DB_PASSWORD', config('mysql_password'));
define('DB_NAME', config('mysql_db_name'));


/**
 *  ARRAY OF ALL YOUR CRYPTOBOX PRIVATE KEYS
 *  Place values from your gourl.io signup page
 *  array('your_privatekey_for_box1', 'your_privatekey_for_box2 (optional)', 'etc...');
 */

$cryptobox_private_keys = CryptoPay::getPrivateKeys();

define('CRYPTOBOX_PRIVATE_KEYS', implode('^', $cryptobox_private_keys));
unset($cryptobox_private_keys);
