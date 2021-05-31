<?php

/**
 * @param $app
 */
function checkout_pager($app){
    $app->setTitle(lang("store::billing-and-shipping-details"));
    $val = input('val');
  if($val){
    //  CSRFProtection::validate();
     // print_r($val);
      CSRFProtection::validate();
    $product_ids = $val['pid']; //this is an array;
    $product_quantities = $val['quantity'];
     $productids_plus_quantity = array_combine($product_ids,$product_quantities);
      //save the pq in a session
      session_put('final_product_quantity_'.get_userid(),$productids_plus_quantity);
         //$p is product and $q=quantity
      return $app->render(view('store::orders/checkout',array('pq'=>$productids_plus_quantity)));
  }
   return redirect_to_pager("product-cart");
}

/**
 * @param $app
 * @return string
 */

function submit_checkout_order($app){
    //this pager will validate their billing and shipping details
    $error_message = null;
    $val = input('val');
    CSRFProtection::validate();
   // print_r($val);
    //return false;
    validator($val,array(
       'billing_first_name'=>'required',
        'billing_last_name'=>'required',
        'billing_email_address'=>'required|email',
        'billing_phone'=>'required',
        'billing_country'=>'required',
        'billing_address'=>'required',
        'billing_zip'=>'required',
        'billing_city'=>'required',
        'shipping_first_name'=>'required',
        'shipping_last_name'=>'required',
        'shipping_email_address'=>'required|email',
        'shipping_phone'=>'required',
        'shipping_country'=>'required',
        'shipping_address'=>'required',
        'shipping_zip'=>'required',
        'shipping_city'=>'required',
    ));

    if(validation_passes()){

    }else{
        //validation does not pass.
        $error_message = validation_first();
    }
    if($error_message){
        $response = array('status'=>0,'msg'=>$error_message);
        return json_encode($response);
    }else{
        // when there is no error at all
      $r =   add_billing_details($val);

        //it will add a new order row

        if($r){
            //redirect to process payment pager
            if(is_ajax()){
                $response = array('status'=>1,'msg'=>url_to_pager("orders-checkout-payment"));
                return json_encode($response);
            }else{
                return redirect_to_pager('orders-checkout-payment');
            }
        }
    }

  return redirect_to_pager("product-cart");
    //add_both_shipping and billing accounts :: in case of next shopping
    //redirect to payment gateway
    //if payment is successful
    //notify Admin/notify store owner
    //enable user to download product or send download link
    //if payment is unsuccessful delete the user billing,shipping and order ID

}

//show payment pager
function checkout_payment_pager($app){
    $app->setTitle(lang('store::select-payment-options'));
    $pq = session_get('final_product_quantity_'.get_userid());
    $bid = session_get('bid'.get_userid());
    $sid = session_get('sid'.get_userid());
    $billing_address = getBillingOrShippingDetails('lp_billing_details','mumu',get_userid());
    if(!$pq) return redirect_to_pager("product-cart");
    if(!$bid || !$sid) return redirect_to_pager("orders-checkout");
    $action = input('action');
    $type = input('type','request');
    switch($action){
        case 'paypal':
            //check for multiple accounts
            if(config("allow-buyers-to-pay-directly-to-seller",0)){
                switch($type){
                    case 'request':
                        require_once(path('plugins/store/lib/paypalmasspay.php'));
                        break;
                    case 'cancel':
                        return $app->render(view('store::orders/paypal/cancel'));
                        break;
                    case 'success':
                        return $app->render(view('store::orders/checkout_complete'));
                        break;
                }
            }else{
                switch($type){
                    case 'request':
                        $lastInsertedOrder = addNewOrder($pq,'PAYPAL');
                        $invoice = time()."-".$lastInsertedOrder;


                        //MO SAF START
                        //now we need to log this payment for us to have copy payment
                        $extra_data = array(
                            'invoice' => $invoice,
                            'buyer_id'=>get_userid()
                        );
                        $payment_id = log_payment(array(
                            'type' => 'store',
                            'type_id' => $lastInsertedOrder,
                            'payment_data' => perfectSerialize($pq),
                            'payment_method' => 'paypal',
                            'extra_data' => perfectSerialize($extra_data),
                        ));
                        $notify_url = url('paypal/payment/verification');
                        $invoice = $invoice.'-'.$payment_id;
                        //payment log finish
                        //MO SAF END




                        updateOrder($lastInsertedOrder,$invoice,'invoice');
                        $i = 1;
                        //require_once(path('includes/libraries/paypal_class.php'));
                        require_once(path('plugins/store/lib/StorePaypal.php'));
                        $paypal = new \StorePaypal();
                        $paypal->admin_mail = config('paypal-notification-email');  //store email or Admin Email
                        $paypal->add_field('business', config('paypal-corporate-email')); //live

                        //$paypal->sandbox = true; //demo add
                        //$paypal->add_field('business', 'jbellmma1-facilitator@gmail.com'); //demo add

                        $paypal->add_field('cmd', '_cart');
                        $paypal->add_field('rm', '2');
                        //$paypal->add_field('display', '1');
                        $paypal->add_field('return', url_to_pager('orders-checkout-payment').'?action=paypal&type=success');
                        $paypal->add_field('cancel_return', url_to_pager('orders-checkout-payment').'?action=paypal&type=cancel');
                        //$paypal->add_field('notify_url', url_to_pager('product-paypal-notify'));
                        $paypal->add_field('notify_url', $notify_url); //SAF MODIFY
                        $paypal->add_field('currency_code', config('default-currency'));
                        $paypal->add_field('invoice', $invoice);
                        //$paypal->add_field('address_override', '1');
                        $paypal->add_field('upload',  1);

                        $store_ids = array();
                        $products_arr = array();
                        foreach($pq as $product=>$quantity){
                            $productGanGan = getSingleProduct($product);
                            $products_arr[] = $productGanGan;
                            $product_store_id = $productGanGan['store_id'];
                            if(!in_array($product_store_id,$store_ids)){
                                $store_ids[] = $product_store_id;
                            }

                            $amount = getProductPurchasePrice($lastInsertedOrder,$product);
                            $paypal->add_field('item_name_'.$i, $productGanGan['name']);
                            //$paypal->add_field('item_number_'.$i, $productGanGan['id']);
                            $paypal->add_field('quantity_'.$i, $quantity);
                            $paypal->add_field('amount_'.$i,$amount);
                            $i++;
                            ///continue;
                         }

                        $shipping_price = getShippingCostFromThisStores($products_arr,true);
                        if(!is_string($shipping_price)){
                            $j = $i;
                            $paypal->add_field('item_name_'.$j, lang("store::shipping-cost"));
                            $paypal->add_field('quantity_'.$j, 1);
                            $paypal->add_field('amount_'.$j,$shipping_price);
                        }
                        $paypal->add_field('email', $billing_address['email_address']);
                        $paypal->add_field('first_name',$billing_address['first_name']);
                        $paypal->add_field('last_name', $billing_address['last_name']);
                        $paypal->add_field('address1', $billing_address['address']);
                        $paypal->add_field('city', $billing_address['city']);
                        $paypal->add_field('state', 'nspc');
                        $paypal->add_field('country', $billing_address['country']);
                       // $paypal->add_field('country_code', $billing_address['country']);
                        $paypal->add_field('zip', $billing_address['zip']);
                        /*echo '<pre>',print_r($paypal),'</pre>';
                         die();*/
                        $paypal->submit_paypal_post();
                        // $paypal->dump_fields();die();

                        break;
                    case 'cancel':
                        return $app->render(view('store::orders/paypal/cancel'));
                        break;
                    case 'success':
                        clearCartNow();
                        return $app->render(view('store::orders/checkout_complete'));
                        break;
                }
            }

            break;

        case '2CO':
            $lastInsertedOrder = addNewOrder($pq,'2CO');
            $invoice = time()."-".$lastInsertedOrder;
            updateOrder($lastInsertedOrder,$invoice,'invoice');
            return $lastInsertedOrder;
            break;
        case 'razor':

            if($type == 'cancel'){
                return $app->render(view('store::orders/paypal/cancel'));
            }
            elseif($type == 'success'){
                $lastInsertedOrder = addNewOrder($pq,'RAZOR PAY');
                $invoice = time()."-".$lastInsertedOrder;
                updateOrder($lastInsertedOrder,$invoice,'invoice');
                $order = getSingleOrder($invoice);
                updateOrderStatus($order,'successful');
                fire_hook("payment.aff",null,array('store', $invoice));
                return $app->render(view('store::orders/checkout_complete'));
            }

            break;
    }
    return $app->render(view('store::orders/payment',array('pq'=>$pq)));
}

function product_paypal_process_pager($app){
    //$order_id = segment(4);
    //db()->query("UPDATE lp_order SET status=1");
    if(isset($_POST['invoice'])){
        $invoice = $_POST['invoice'];
        $order = getSingleOrder($invoice);
    require_once(path('includes/libraries/paypal_class.php'));

    $paypal = new \paypal_class();

    if($paypal->validate_ipn()) {
        //that means user has successfully paid
        //Change their order status
        fire_hook("payment.aff",null,array('store', $invoice));
        return updateOrderStatus($order,'successful');
       }
    }
    return false;
}

//stripe payment
function checkout_payment_stripe_pager($app){

    $pq = session_get('final_product_quantity_'.get_userid());
    //$product_and_id = perfectSerialize($pq);
    $lastInsertedOrder = addNewOrder($pq,'STRIPE');
    $invoice = time()."-".$lastInsertedOrder;
    updateOrder($lastInsertedOrder,$invoice,'invoice');

    $amount = getCartTotalPrice($pq,true);


    $token = input('stripeToken');
    if(!$token) return redirect_back();

    $desription = getCartProductDescription($pq);
    require_once(path('includes/libraries/stripe/lib/Stripe.php'));

    try {

        \Stripe::setApiKey(config('stripe-secret-key'));
        \Stripe_Charge::create(array(
            'amount' => $amount * 100, // this is in cents: $20
            'currency' => config('default-currency'),
            'card' => $token,
            'description' => $desription
        ));

        $order = getSingleOrder($lastInsertedOrder);
        fire_hook("payment.aff",null,array('store',$lastInsertedOrder));
        updateOrderStatus($order,'successful');
        //redirect to my orders and
        return redirect_to_pager('my_orders');
    } catch (\Exception $e) {
        // Declined. Don't process their purchase.
        // Go back, and tell the user to try a new card
        echo $e->getMessage();
        die();
        return redirect_back();
    }
}

function my_order_pager($app){
    $app->setTitle(lang("store::my-order"));
    $orders = getMyOrders(get_userid(),'all');
    $id = input('id');
    if($id){
      $order =  getSingleOrder($id);
        if($order['user_id'] != get_userid()) return $app->render(view("store::orders/my_order",array("orders"=>$orders)));
        return $app->render(view("store::orders/my-single-order",array("order"=>$order)));
    }
    return $app->render(view("store::orders/my_order",array("orders"=>$orders)));
}

//Two checkout
function checkout_payment_TwoCheckout_pager($app){
    if ($_REQUEST['demo'] == 'Y')
    {
        $order_number = 1;
    }
    else
    {
        $order_number = $_REQUEST['order_number'];
    }
    $hashSecretWord = config('2checkout-secret',null);
    $compare_string = $hashSecretWord . $_REQUEST['sid'] . $order_number . $_REQUEST['total'];
    $compare_hash1 = strtoupper(md5($compare_string));
    $compare_hash2 = $_REQUEST['key'];

    if ($compare_hash1 != $compare_hash2)
    {
        echo "Hash Mismatch";
    }
    else
    {
        //let us now check the request
        //it match now
        $my_tracking_oid = $_REQUEST['to_id'];
        $order = getSingleOrder($lastInsertedOrder);
        //tracking link https://www.2checkout.com/documentation/checkout/parameters
        if($_REQUEST['credit_card_processed'] == 'Y' && $_REQUEST['total'] >= $order['total_price']){
            //update
            $order = getSingleOrder($my_tracking_oid);
            fire_hook("payment.aff",null,array('store',$my_tracking_oid));
            updateOrderStatus($order,'successful');
        }
    }
}

function Two_checkout_check_status_pager($app){
    if(isset($_POST['message_type'])){
     session_put("twocheckout",$_POST);
    }
    if(session_get("twocheckout")) print_r(session_get("twocheckout"));
}

function checkout_complete_pager($app){
    $app->setTitle(lang("store::checkout-complete"));
    return $app->render(view("store::orders/checkout_complete"));
}

function payment_ondelivery_pager($app){
//$product_and_id = perfectSerialize($pq);
    $dont_pay_me_on_delivery = null;
    $pq = session_get('final_product_quantity_'.get_userid());
    foreach($pq as $p=>$q){
        $product = getSingleProduct($p);
        if(!$product['pay_on_delivery']){
            $dont_pay_me_on_delivery = $product['name'] ;
            break;
        }
    }
    if($dont_pay_me_on_delivery){
       // return json_encode(array('status'=>0,'msg'=>$dont_pay_me_on_delivery.' '.lang("store::is_not_to_be_paid_on_delivery")));
        $em = ucfirst(lang('store::product')).' <b>'.$dont_pay_me_on_delivery.'</b> '.lang("store::is_not_to_be_paid_on_delivery");
        session_put('em',$em);
        return redirect(url_to_pager("orders-checkout-complete").'?t=info');
    }
    $lastInsertedOrder = addNewOrder($pq,'Pay On Delivery');
    $invoice = time()."-".$lastInsertedOrder;
    updateOrder($lastInsertedOrder,$invoice,'invoice');
    session_put('em',null);
    //return json_encode(array('status'=>1,'url'=>url_to_pager("orders-checkout-complete")));
    return redirect(url_to_pager("orders-checkout-complete").'?t=success');
}