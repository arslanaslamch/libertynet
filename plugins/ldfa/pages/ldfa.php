<?php
function ldfa_payment_pager($app) {
    $id = input('id');
    $price = input('price');
    $name = input('name');
    $description = input('description');
    $quantity = input('quantity', 1);
    $type = input('type_r');
    $plan = array('type' => '');

    $notify_url = url_to_pager('paypal-verification');
    $return_url = input('return_url');
    $cancel_url = input('cancel_url');
    if (input('webview')) {
        if (preg_match("#\?#", $return_url)) {
            $return_url .= "&webview=true&api_userid=" . get_userid();
        } else {
            $return_url .= "?webview=true&api_userid=" . get_userid();
        }

        if (preg_match("#\?#", $cancel_url)) {
            $cancel_url .= "&webview=true&api_userid=" . get_userid();
        } else {
            $cancel_url .= "?webview=true&api_userid=" . get_userid();
        }
    }
    if ($type == 'membership-plan') {
        $membership_plan = get_membership_plan($id);
        $plan = $membership_plan;
        if ($plan['type'] == 'recurring') {
            return ldfa_process_recurring($plan, ['return' => $return_url, 'cancel' => $cancel_url, 'notify' => $notify_url]);
        }
    }
    

    $p_type = input('type', 'request');
    require_once(path('includes/libraries/paypal_class.php'));
    $paypal = new \paypal_class();
    $paypal->sandbox = config('enable-paypal-sandbox');;
    switch($p_type) {
        case 'request':
            $paypal->admin_mail = config('paypal-notification-email');
            $paypal->add_field('business', config('paypal-corporate-email'));
            $paypal->add_field('rm', '2');
            $paypal->add_field('cmd', '_xclick');
            $paypal->add_field('item_amount', $price);
            //$csrf_token = CSRFProtection::getToken();
            $thisUrl = getFullUrl(true).'&type=success';

            $paypal->add_field('return', $thisUrl);
            $paypal->add_field('cancel_return', $cancel_url);
            $paypal->add_field('notify_url', $notify_url);
            $paypal->add_field('currency_code', config('default-currency'));
            $paypal->add_field('invoice', time().'-'.$id);
            $paypal->add_field('upload', 1);
            $paypal->add_field('item_name', lang($name));
            $paypal->add_field('item_description', lang($description));
            $paypal->add_field('item_number', $id);
            $paypal->add_field('item_quantity', $quantity);
            $paypal->add_field('item_type', $type);
            $paypal->add_field('amount', $price);
            $paypal->add_field('user_id', get_user_data('id'));
            $paypal->add_field('email', get_user_data('email_address'));
            $paypal->add_field('first_name', get_user_data('first_name'));
            $paypal->add_field('last_name', get_user_data('last_name'));
            $paypal->add_field('address1', '');
            $paypal->add_field('city', get_user_data('city'));
            $paypal->add_field('state', get_user_data('state'));
            $paypal->add_field('country', get_user_data('country'));
            $paypal->add_field('zip', 'null');
            $paypal->add_field('custom', get_userid().",".$type);
            $paypal->submit_paypal_post();
            //$paypal->dump_fields();
            break;
        case 'cancel':
            return redirect($cancel_url);
            break;
        case 'success':
            //CSRFProtection::validate();

            //fire_hook("payment.aff", null, array($type, $id));
            if(config('promotion-coupon', 0)) {
                fire_hook('payment.coupon.completed', input('coupon'));
            }
            redirect($return_url);
    }
}

function paypal_payment_verification_pager() {
    require_once(path('includes/libraries/paypal_class.php'));
    $paypal = new \paypal_class();
    $paypal->sandbox = config('enable-paypal-sandbox');
    $paypal->admin_mail = config('paypal-notification-email');
    $ipn_verification = $paypal->validate_ipn();
    $type = "";
    if($ipn_verification) {
        $custom = "";
        $id = "";
        $user_id = "";

        foreach($ipn_verification as $field => $value) {
            if($field == "item_number") $id = $value;
            if($field == "custom") $custom = $value;
        }
        if($custom){
            $custom = explode(',', $custom);
            $type = $custom[1];
            $user_id = $custom[0];
            fire_hook("payment.aff", null, array($type, $id, $user_id));
            $subject = $type.'Instant Payment Notification - Recieved Payment';
            $paypal->send_report ( $subject );
        }
    } else {
        $subject = $type.'Instant Payment Notification - Payment Fail';
        $paypal->send_report ( $subject );
    }
}

function paypal_process_recurring($plan, $urls)
{
    require_once(path('includes/libraries/paypal_class.php'));
    $paypal = new \paypal_class();
    $paypal->sandbox = config('enable-paypal-sandbox');
    $paypal->admin_mail = config('paypal-notification-email');
    $paypal->add_field('business', config('paypal-corporate-email'));
    $paypal->add_field('cmd', '_xclick-subscriptions');
    $paypal->add_field('item_name', lang($plan['title']));
    $paypal->add_field('item_number', $plan['id']);

    $paypal->add_field('upload', 1);
    $paypal->add_field('rm', 2);
    $paypal->add_field('src', 1);
    $paypal->add_field('currency_code', config('default-currency'));
    $paypal->add_field('a3', $plan['price']);
    $paypal->add_field('p3', $plan['expire_no']);
    if ($plan['expire_type'] == 'day') {
        $t3 = 'D';
    } elseif ($plan['expire_type'] == 'week') {
        $t3 = 'W';
    } elseif ($plan['expire_type'] == 'month') {
        $t3 = 'M';
    } elseif ($plan['expire_type'] == 'year') {
        $t3 = 'Y';
    } else {
        $t3 = 'M';
    }
    $paypal->add_field('t3', $t3);
    $paypal->add_field('return', $urls['return']);
    $paypal->add_field('cancel_return', $urls['cancel']);
    $paypal->add_field('notify_url', url_to_pager("paypal-recurring-verification"));
    $paypal->add_field('custom', $plan['id']);
    $paypal->submit_paypal_post();
}


function paypal_recurring_verification()
{
    require_once(path('includes/libraries/paypal_class.php'));
    $paypal = new \paypal_class();
    $paypal->sandbox = config('enable-paypal-sandbox');
    $paypal->admin_mail = config('paypal-notification-email');
    $verified = $paypal->validate_ipn();
    if ($verified) {
        $plan = get_membership_plan($verified['item_number']);
        $valid_from = !empty($verified['subscr_date']) ? $verified['subscr_date'] : date("Y-m-d H:i:s");
        $dt = new DateTime($valid_from);
        $valid_from = $dt->format("Y-m-d H:i:s");
        $expiry = $plan['expire_no'] . " " . $plan['expire_type'];
        $valid_to = date("Y-m-d H:i:s", strtotime(" + $expiry", strtotime($valid_from)));
        $data = [
            'payment_method' => 'paypal',
            'subscr_id' => $verified['subscr_id'],
            'payer_email' => $verified['payer_email'],
            'plan_id' => $verified['item_number'],
            'txn_id' => !empty($verified['txn_id']) ? $verified['txn_id'] : '',
            'amount' => $verified['mc_gross'],
            'currency' => $verified['currency_code'],
            'interval' => $plan['expire_type'],
            'interval_count' => !empty($verified['period3']) ? $verified['period3'] : $plan['expire_no'],
            'status' => $verified['status'],
            'valid_from' => $valid_from,
            'valid_to' => $valid_to,
            'user_id' => get_userid(),
            'cust_id' => "",
        ];
        if ($data['txn_id'] == "" && check_membership_txn($data['txn_id'])) {
            exit();
        } else {
            save_membership_subscription($data);
            fire_hook("payment.aff", null, array('membership-plan', $data['item_number'], get_userid()));
            $subject =  'membership-plan Instant Payment Notification - Received Payment';
            $paypal->send_report($subject);
        }
    } else {
        $subject = 'Membership Plan: Instant Payment Notification - Payment Fail';
        $paypal->send_report($subject);
    }
    die;
}