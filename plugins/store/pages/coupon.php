<?php

function stores_coupon_pager($app){
    $app->setTitle(lang("store::coupons"));
    $store = getCurrentUserStore();
    if(!$store) return redirect_to_pager('store_homepage');
    $store_id = $store['s_id'];
    $coupons = get_store_coupons($store_id);
   return  $app->render(view("store::coupons/list",array('coupons'=>$coupons)));
}

function store_coupon_add_pager($app){
    $app->setTitle(lang("store::add-coupons"));
    $store = getCurrentUserStore();
    $message = null;
    if(!$store) return redirect_to_pager('store_homepage');
    $val = input('val');
    if($val){
        validator($val,array(
            'code'=>'required|min:4',
            'expiry_date'=>'required',
            'type'=>'required',
            'coupon_type'=>'required',
            'coupon_value'=>'required'

        ));
        if(validation_passes()){
            if(empty($val['products'])){
                $message = lang("store::select-products-for-this-coupons");
                return $app->render(view('store::coupons/add',array('message'=>$message)));
            }
            //expired time
            $expiry_date = makeExpiryTime($val['expiry_date']);
            if(time() > $expiry_date){
                $message = lang("store::invalid-discount-date");
                return $app->render(view('store::coupons/add',array('message'=>$message)));
            }
            $val['expiry_date'] = $expiry_date;
            $val['store_id'] = $store['s_id'];
            save_coupon($val);
            return redirect_to_pager('coupons');
        }
        else{
            $message = validation_first();
        }
    }
    return $app->render(view('store::coupons/add',array('message'=>$message)));
}

function coupons_manage_pager($app){
    $app->setTitle(lang("store::coupons"));
    $store = getCurrentUserStore();
    if(!$store) return redirect_to_pager('store_homepage');
    $coupon_id = sanitizeText(input('id'));
    $message = null;
    $action = input('action');
    $coupon = getSingleCoupon($coupon_id);
    if($coupon){
        if(!isCouponOwner($coupon)) return redirect_to_pager('store_homepage');
        if($action == 'delete'){
            deleteCoupon($coupon_id);
            return redirect_to_pager('coupons');
        }
        if($action == 'edit'){
            $val = input('val');
            if($val){
                validator($val,array(
                    'code'=>'required|min:4',
                    'expiry_date'=>'required',
                    'type'=>'required',
                    'coupon_type'=>'required',
                    'coupon_value'=>'required'

                ));
                if(validation_passes()){
                    if(empty($val['products'])){
                        $message = lang("store::select-products-for-this-coupons");
                        return $app->render(view('store::coupons/edit',array('coupon'=>$coupon,'message'=>$message)));
                    }
                    //expired time
                    $expiry_date = makeExpiryTime($val['expiry_date']);
                    if(time() > $expiry_date){
                        $message = lang("store::invalid-discount-date");
                        return $app->render(view('store::coupons/edit',array('coupon'=>$coupon,'message'=>$message)));
                    }
                    $val['expiry_date'] = $expiry_date;
                    $val['coupon_id'] = $coupon_id;
                    $val['store_id'] = $store['s_id'];
                    save_coupon($val);
                    return redirect_to_pager('coupons');
                }
            }
            return $app->render(view("store::coupons/edit",array('coupon'=>$coupon,'message'=>$message)));
        }
    }
}

function coupon_verify_pager($app){
    $code = input('code');
    if($code){
       $code = sanitizeText($code);
        $reponse = is_coupon_code_useable($code);
        $response_message = getCouponStatusCode($reponse);
        if($response_message == 'ok'){
           //apply the coupon to products
           //coupon_in_a_session
            //retrieve
           $used =  session_get('coupon_code',array());
            $used[] = $code;
            session_put('coupon_code',$used);
            $url = url_to_pager('product-cart');
            return json_encode(array('status'=>1,'url'=>$url));
        }else{

            return json_encode(array('status'=>0,'message'=>$response_message));
        }
    }
}

