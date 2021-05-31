<?php

function clearCartNow(){
    if(session_get('my_product_cart_'.get_userid())){
        session_forget('my_product_cart_'.get_userid());
    }
}
/**
 * @param $val
 * @return bool
 */
function add_billing_details($val){
    //Remove the old shipping and billing details
    $user_id = get_userid();
    /*db()->query("DELETE FROM lp_shipping_details WHERE user_id='{$user_id}'");
    db()->query("DELETE FROM lp_billing_details WHERE user_id='{$user_id}'");*/

    $expected = array(
        'billing_first_name'=>'',
        'billing_last_name'=>'',
        'billing_company'=>'',
        'billing_email_address'=>'',
        'billing_phone'=>'',
        'billing_country'=>'',
        'billing_address'=>'',
        'billing_zip'=>'',
        'billing_city'=>''
    );
    extract(array_merge($expected,$val));
    /**
     * @var $billing_first_name
     * @var $billing_last_name
     * @var $billing_company
     * @var $billing_email_address
     * @var $billing_phone
     * @var $billing_country
     * @var $billing_address
     * @var $billing_zip
     * @var $billing_city
     *
     */

    $billing_first_name = sanitizeText($billing_first_name);
    $billing_last_name = sanitizeText($billing_last_name);
    $billing_company = sanitizeText($billing_company);
    $billing_email_address = sanitizeText($billing_email_address);
    $billing_phone = sanitizeText($billing_phone);
    $billing_country = sanitizeText($billing_country);
    $billing_address = sanitizeText($billing_address);
    $billing_zip = sanitizeText($billing_zip);
    $billing_city = sanitizeText($billing_city);

    //insert into the billing table
    db()->query("INSERT into lp_billing_details(user_id,first_name,last_name,company,email_address,phone,country,city,zip,address)
    VALUES ('{$user_id}','{$billing_first_name}','{$billing_last_name}','{$billing_company}','{$billing_email_address}','{$billing_phone}','{$billing_country}','{$billing_city}','{$billing_zip}','{$billing_address}')");

    $bid =  db()->insert_id;
    session_put('bid'.get_userid(),$bid);
    fire_hook("billing.details.added",$bid,array($val));
    //now let us add shipping details
    add_shipping_details($val);
    return true;
}

/**
 * @param $val
 * @return bool
 */

function add_shipping_details($val){
    $expected = array(
        'shipping_first_name'=>'',
        'shipping_last_name'=>'',
        'shipping_company'=>'',
        'shipping_email_address'=>'',
        'shipping_phone'=>'',
        'shipping_country'=>'',
        'shipping_address'=>'',
        'shipping_zip'=>'',
        'shipping_city'=>''
    );
    extract(array_merge($expected,$val));
    /**
     * @var $shipping_first_name
     * @var $shipping_last_name
     * @var $shipping_company
     * @var $shipping_email_address
     * @var $shipping_phone
     * @var $shipping_country
     * @var $shipping_address
     * @var $shipping_zip
     * @var $shipping_city
     *
     */

    $shipping_first_name = sanitizeText($shipping_first_name);
    $shipping_last_name = sanitizeText($shipping_last_name);
    $shipping_company = sanitizeText($shipping_company);
    $shipping_email_address = sanitizeText($shipping_email_address);
    $shipping_phone = sanitizeText($shipping_phone);
    $shipping_country = sanitizeText($shipping_country);
    $shipping_address = sanitizeText($shipping_address);
    $shipping_zip = sanitizeText($shipping_zip);
    $shipping_city = sanitizeText($shipping_city);
    $user_id = get_userid();
//insert into the shipping table
    db()->query("INSERT into lp_shipping_details(user_id,first_name,last_name,company,email_address,phone,country,city,zip,address)
VALUES ('{$user_id}','{$shipping_first_name}','{$shipping_last_name}','{$shipping_company}','{$shipping_email_address}','{$shipping_phone}','{$shipping_country}','{$shipping_city}','{$shipping_zip}','{$shipping_address}')");
    $sid = db()->insert_id;

    fire_hook("shipping.details.added",$sid,array($val));
    session_put('sid'.get_userid(),$sid);

    return true;
}

/**
 * @param $order
 * @return bool|string
 */
function getOrderStatus($order){
    $status = $order['status'];
    switch($status){
        case 0:
            return lang('store::pending');
            break;
        case 1:
            return lang('store::successful');
        case 7:
            return lang("store::refunded");
            break;
    }
    return false;
}


function getBillingOrShippingDetails($type,$id,$uid=null){
    $sql = db()->query("SELECT * FROM $type WHERE b_id='{$id}' ORDER BY b_id DESC LIMIT 1");
    if($uid){
        $sql = db()->query("SELECT * FROM $type WHERE user_id='{$uid}' ORDER BY b_id DESC LIMIT 1");
    }
    $result =  $sql->fetch_assoc();
    return $result;
}

function getProductPurchasePrice($order_id,$product_id, $r_arr = false){
    $q = db()->query("SELECT * FROM lp_store_order WHERE order_id='{$order_id}' AND product_id='{$product_id}' ");
    if($r_arr){
        return $q->fetch_assoc();
    }
    return $q->fetch_assoc()['price'];
}
function addNewOrder($pq,$payment_type){
    $time = time();
    $status = 0;
    $user_id = get_userid();
    $actual = getCartTotalPrice($pq);
    $pq_serialize = perfectSerialize($pq);

    //Add billing and shipping of this order
    $bid = session_get('bid'.get_userid());
    $sid = session_get('sid'.get_userid());

    $store_ids = array();
    $products_arr = array();
    db()->query("INSERT INTO lp_order(user_id,product,time,status,payment_type,total_price,bid,sid)
    VALUES ('{$user_id}','{$pq_serialize}','{$time}','{$status}','{$payment_type}','{$actual}','{$bid}','{$sid}')");
     $overall_order_commision_Earned = 0;
    $order_id = db()->insert_id;
    //Add store order
    $attr = perfectSerialize(array());
    $cart = session_get('my_product_cart_'.get_userid());
    //print_r($pq);die();
    foreach($pq as $p=>$q){
        $product = getSingleProduct($p);
        //shipping
        $products_arr[] = $product;
        $product_store_id = $product['store_id'];


        if(!in_array($product_store_id,$store_ids)){
            $store_ids[] = $product_store_id;
        }
        //shipping end;
        if($cart[$p]['attr']){
            $attr = perfectSerialize($cart[$p]['attr']);
        }
        $price  = getProductPrice($product,'yes');
        $sub_total = $price * $q;
        $sub_total_bfc = $sub_total; //sub total before commission awon eyan ti emoney
        //if there Admin set percentage commision
        $commision_percentage = config('commission-per-sale',0);
        $each_product_commision = 0;

        if($commision_percentage){
            $each_product_commision = ((float)$commision_percentage / 100) * $sub_total;
            $each_product_commision = number_format((float)$each_product_commision,2,'.',',');
            $sub_total -= $each_product_commision;
            $overall_order_commision_Earned += $each_product_commision;
        }
        $store_id  = $product['store_id'];
        $store = lpGetStoreById($store_id);
        $store_email_address = $store['email'];
        $storeOwner = $store['user_id'];
        db()->query("INSERT INTO lp_store_order(store_owner,store_id,product_id,order_id,quantity,attr,time,status,sub_total,price,commission)
        VALUES ('{$storeOwner}','{$store_id}','{$p}','{$order_id}','{$q}','{$attr}','{$time}',0,'{$sub_total}','{$price}','{$each_product_commision}')");

        $lp_store_order_id = db()->insert_id;
        if(config('enable-emoney-payment-on-ecommerce',false) && function_exists('ecommerce_order_credit_user')){
            ecommerce_order_credit_user($storeOwner,$lp_store_order_id,$sub_total,$product,$sub_total_bfc);
        }
        /*echo db()->error;
        die();*/
        if(config('send_notification_on_new_order',true)){
            sendSellerNewOrderNotification($store_email_address,$product,$sub_total,$order_id,$q,$bid,$storeOwner,$price);
            //send sms notification
            $number = $store['phone'];
            $name = get_user_name();
            sendSellerSmsNotification($number,$name);
        }
    }

    //update this order commission Earned by admin
    db()->query("UPDATE lp_order SET overall_commision='{$overall_order_commision_Earned}' WHERE id='{$order_id}'");

    //shipping
    $shipping_price = getShippingCostFromThisStores($products_arr,true);
    if(!is_string($shipping_price)){
        db()->query("UPDATE lp_order SET shipping_price='{$shipping_price}' WHERE id='{$order_id}'");
        db()->query("UPDATE lp_store_order SET shipping_price='{$shipping_price}' WHERE order_id='{$order_id}'");
    }
    //send_notification_privacy('notify-site-store',$order['user_id'],'buyer-successful-order',$order_id);
    clearCartNow();
    fire_hook('order.added',null,array($order_id));
    return $order_id;

}

//update order
function getSingleOrder($orderid){
    $sql = db()->query("SELECT * FROM lp_order WHERE id='{$orderid}' OR invoice='{$orderid}'");
    if($sql->num_rows > 0){
        return $sql->fetch_assoc();
    }
    return false;
}

//update order status
function updateOrderStatus($order,$status){
    $order_id = $order['id'];
    //$user_id = $order['user_id']; //the person that ordered
    switch($status){
        case 'successful':
            db()->query("UPDATE lp_order SET status=1 WHERE id='{$order_id}'");
            db()->query("UPDATE lp_store_order SET status=1 WHERE order_id='{$order_id}'");

            //send email_to buyer here to download product
            fire_hook('order.successful',null,array($order_id));
            sendBuyerDownloadLink($order_id);
            break;
        case 'pending':
            db()->query("UPDATE lp_order SET status=0 WHERE id='{$order_id}'");
            db()->query("UPDATE lp_store_order SET status=0 WHERE order_id='{$order_id}'");
            break;
    }
    return true;
}

//update order
function  updateOrder($id,$value,$col){
    db()->query("UPDATE lp_order SET $col='{$value}' WHERE id='{$id}'");
    return true;
}

//function
function getProductPrice($product,$ac = 'no'){
   //ac = apply coupon
    $current_time = time();
    $discount_expire_time = $product['e_date'];
    $actual_price = ($discount_expire_time && ($discount_expire_time > $current_time))  ? $product['discount_price'] : $product['price'];
   if($ac != 'no' && session_get('coupon_code')){
       //we are applying coupon to price
       $actual_price = fire_hook('product.price',$actual_price,array($product));
   }
    return $actual_price;
}

function getProductDiscountedPrice($product){
    $current_time = time();
    $discount_expire_time = $product['e_date'];
    $actual_price = ($discount_expire_time && ($discount_expire_time > $current_time))  ? $product['price'] :  0;
    return $actual_price;
};

function getCartTotalPrice($pq,$shipping=false){
   /// $products_quantity = $pq;
    /*print_r($pq);
    exit;*/
    $store_ids = array();
    $products_arr = array();
    $t = array();
    foreach($pq as $p=>$q){
        $product = getSingleProduct($p);
        $products_arr[] = $product;
        if($shipping){
            $product_store_id = $product['store_id'];
            //add the store ID to an array
            if(!in_array($product_store_id,$store_ids)){
                $store_ids[] = $product_store_id;
            }
        }
        $actual_price = getProductPrice($product,'ac');
        $t[] = ($actual_price * $q);
    }

    if($store_ids){
        $shipping_price = getShippingCostFromThisStores($products_arr, true);
        if(!is_string($shipping_price)){
            $t[] = $shipping_price;
        }
    }
    return array_sum($t);
}

function getCartProductDescription($pq){
    $html = "";
    $i = 1;
    $total = count($pq);
    foreach($pq as $p=>$q){
        $product = getSingleProduct($p);
        $desc = html_entity_decode($product['description']);
        $html .= $desc;
        if($i != $total) $html .= lang("store::and");
        $i++;
    }
   return $html;
}

//get user orders
function getMyOrders($user_id=null,$limit=10){
    $uid = ($user_id) ? $user_id : get_userid();
    $sql = "SELECT * FROM lp_order WHERE user_id='{$uid}' ORDER BY id DESC";
    return paginate($sql,$limit);
}

function getAllOrders($limit=10){
    $sql = "SELECT * FROM lp_order ORDER BY time DESC";
    return paginate($sql,'all');
}

function getUserBillingDetails($user_id =null){
    $uid = ($user_id) ? $user_id : get_userid();
    $result = array();
    $query = db()->query("SELECT * FROM lp_billing_details WHERE user_id='{$uid}'");
    if($query->num_rows > 0){
        while($r = $query->fetch_assoc()){
            $result[] = $r;
        }
    }
    return $result;
}

