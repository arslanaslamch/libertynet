<?php

function makeExpiryTime($time){
    $date_and_time = explode(' ',$time);
    $date = explode('/',$date_and_time[0]);
    $end_year = $date[0];
    $end_month = $date[1];
    $end_day = $date[2];
    $end_time = explode(':',$date_and_time[1]);
    $end_hour = $end_time[0];
    $end_minute = $end_time[0];
    $e_date = mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
    return $e_date;
}
function save_coupon($val){
    $expected = array(
        'code'=>'',
        'expiry_date'=>'',
        'coupon_type'=>'',
        'coupon_value'=>'',
        'save_type'=>'new_coupon',
        'products'=>serialize(array()),
    );
    /**
     * @var $code
     * @var $expiry_date
     * @var $coupon_type
     * @var $coupon_value
     * @var $save_type
     * @var $products
     */
    extract(array_merge($expected,$val));
    $code = sanitizeText($code);
    $expiry_date = sanitizeText($expiry_date);

    $coupon_type = sanitizeText($coupon_type);
    $coupon_value = sanitizeText($coupon_value);
    $save_type = sanitizeText($save_type);
    $user_id = get_userid();
    $store_id = $val['store_id'];
    $time = time();
    $status  =$val['status'];
    //print_r($val['products']); die();
    $products = serialize($val['products']);

    if($save_type == 'new_coupon' && !isset($val['update_coupon'])){
        $sql = db()->query("INSERT INTO lp_coupons(store_id,user_id,code,expiry_date,coupon_type,coupon_value,products,time,status)
        VALUES ('{$store_id}','{$user_id}','{$code}','{$expiry_date}','{$coupon_type}','{$coupon_value}','{$products}','{$time}','{$status}')  ");
      // echo db()->error;die();
        $inserted_id = db()->insert_id;
        fire_hook('coupon.added',null,array($inserted_id,$val));
    }
    if($save_type == 'update_coupon'){
        $coupon_id = $val['coupon_id'];
        db()->query("UPDATE lp_coupons SET code='{$code}',expiry_date='{$expiry_date}',
        coupon_type='{$coupon_type}',coupon_value='{$coupon_value}',products='{$products}',
        status='1' WHERE id='{$coupon_id}'");
        fire_hook('coupon.updated',null,array($coupon_id,$val));
    }
    return true;
}

function get_store_coupons($store_id,$type='single'){
    $result =  array();
    $sql = db()->query("SELECT * FROM lp_coupons WHERE store_id='{$store_id}'");
    if($type='all'){
        $sql = db()->query("SELECT * FROM lp_coupons");
    }
    if($sql->num_rows > 0){
        while($r = $sql->fetch_assoc()){
            $result[] = $r;
        }
    }
    return $result;
}

function isCouponOwner($coupon){
    if($coupon['user_id'] == get_userid()) return true;
    return false;
}
function get_coupon_expired_status($coupon){
    if(!$coupon) return false;
    $time = time();
    if($coupon['expiry_date'] > $time){
        return ucwords(lang('no'));
    }
    return ucwords(lang('yes'));
}
function get_coupon_active_status($coupon){
    if(!$coupon) return false;
    $time = time();
    if($coupon['expiry_date'] < $time){
        //coupon has expired
        return '<i class="text-danger ion-android-cancel"></i>  '.lang('store::not-active');
    }
    if($coupon['status']){
        return '<i class="text-success ion-checkmark-circled"></i>  '.lang('active');
    }
    return '<i class="text-danger ion-android-cancel"></i>  '.lang('store::not-active');
}

function getSingleCoupon($id){
    $sql = db()->query("SELECT * FROM lp_coupons WHERE id='{$id}'");
    if($sql->num_rows > 0){
        return $sql->fetch_assoc();
    }
    return false;
}

function deleteCoupon($id){
    db()->query("DELETE FROM lp_coupons WHERE id='{$id}'");
    return true;
}

function getCouponStatusCode($status_code){
    $code = array(
        101 => lang('store::coupon-code-does-not-exist'),
        102 => lang("store::coupon-code-has-expired"),
        103 => lang("store::coupon-code-is-not-active"),
        200 => 'ok'
    );
    return $code[$status_code];
}

function getProductCart(){
  $product =  session_get('my_product_cart_'.get_userid());
    return $product;
}
function is_coupon_code_useable($code){
    $product_in_cart = getProductCart();
    $time = time();
    $coupon_appllied_products = array();
    $db = db();
    $q = $db->query("SELECT * FROM lp_product_coupon WHERE code='{$code}'");
    if($q->num_rows > 0){
        //while($q)
        foreach($product_in_cart as $pc=>$va){
            //$a in a
           $a =  $db->query("SELECT * FROM lp_product_coupon lpc LEFT JOIN lp_coupons lc ON lpc.coupon_id=lc.id WHERE lpc.code='{$code}' AND product_id='{$pc}'");
            if($a->num_rows > 0){
                //there is a match on one.
                $result = $a->fetch_assoc();
                if($result['expiry_date'] < $time){
                    //it has expired
                    return 102;
                }
                if(!$result['status']){
                    //not active
                    return 103;
                }
                 $coupon_appllied_products[] = $pc;
                  return 200;
            }
        }
        //coupon code exist
    }
    return 101; //
}

