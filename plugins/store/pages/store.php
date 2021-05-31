<?php

function store_index_pager($app){
    app()->leftMenu = false;
    $app->setTitle(lang('store::store'));
    //CSRFProtection::validate();
    //get the latest stores added to the database
    $term = input('term',null);
    $type = input('type',null);
    $products = lp_get_stores('featured',$term,$type);
  return $app->render(view('store::home',array('stores'=>$products)));
}

function add_seller_pager($app){
    $app->setTitle(lang('store::store'));
   // CSRFProtection::validate();
    //if there are already in the seller table, they can't add
    if(user_id_aleady_exists_in_seller_table() and !is_admin()){
        return redirect_to_pager("store-manager");
    }
    $message = '';
    $val = input('val');
    if($val){
        CSRFProtection::validate();
        $rules = array(
            'name'=>'required|unique:lp_stores',
            'email'=>'required|email',
            'address'=>'required',
            'phone'=>'required',
            'corporate_email'=>'required|email',
        );
        $validator = validator($val,$rules);
        if(validation_passes()){
            //slug


            $slug = toAscii($val['name']);
            if (empty($slug)) $slug = md5(time().get_userid());
            //check if slug exist
            if(storeSlugExist($slug)){
                $slug = md5(md5($slug));
            }
            $val['slug'] = $slug;

            $result = add_store_seller($val);
            //print_r('moo');die();
            if($result){
                return redirect_to_pager("store-manager");
            }else{
                $message = "Something went wrong adding new store";
                return $app->render(view('store::add-seller',array('message'=>$message)));
            }
        }else{
            $message = validation_first();
            return $app->render(view('store::add-seller',array('message'=>$message)));
        }

    }
    return $app->render(view('store::add-seller',array('message'=>$message)));
}

function store_manage_pager($app){
    $app->setTitle(lang('store::store-manager'));
    $seller = get_seller(get_userid());
    if(!$seller) return redirect_to_pager("add-seller");
    //echo "here";die();
    //return $app->render(view('store::manager-home',array('seller'=>$seller[0])));
    //manager home should return all stores
    return $app->render(view("store::customization/manager-home"));

}

function store_manage_edit_pager($app){
    $app->setTitle(lang('store::store-manager'));
    $message = "";
    $val = input('val');
   if($val){
       CSRFProtection::validate();
       $rules = array(
           'name'=>'required',
           'email'=>'required|email',
           'address'=>'required',
           'phone'=>'required|numeric',
           'corporate_email'=>'required|email',
       );
       $validator = validator($val,$rules);
       if(validation_passes()){
           $result = save_store_seller($val);
           if($result){
               return redirect_to_pager("store-manager");
           }
       }else{
           $message = validation_first();
           return $app->render(view('store::edit-seller',array('message'=>$message)));
       }

   }

    $seller = get_seller(get_userid());
    if(!is_store_owner($seller[0])) return redirect_to_pager("store_homepage");
    return $app->render(view('store::edit-seller',array('seller'=>$seller[0],'message'=>$message)));
}

function single_store_pager($app){
    app()->leftMenu = false;
    $slug = segment(1);
    $seller = get_seller($slug,'by_store_slug');
    if(!$seller) return redirect_to_pager("store_homepage");
    $store = $seller[0];
    $app->setTitle($store['name']);
    return $app->render(view('store::single_store',array('seller'=>$store)));
}

function store_follower_pager($app){
    $action = input('action');
    $storeid = input('store_id');
    $uid = get_userid();
    if($action=='follow'){
        //insert new or update
        if(hasUnfollowedStore($storeid)){
            //update
            db()->query("UPDATE lp_store_followers SET status=1 WHERE store_id='{$storeid}' AND user_id='{$uid}'");
        }else{
            //insert new
            db()->query("INSERT INTO lp_store_followers(user_id,store_id,status) VALUES ('{$uid}','{$storeid}',1)");
        }
    }

    if($action == "unfollow"){
        db()->query("UPDATE lp_store_followers SET status=0 WHERE store_id='{$storeid}' AND user_id='{$uid}'");
    }
}

function store_manage_product_pager($app){
    //show the list of products table for this user store.
    $app->setTitle(lang('store::manage-product'));
    $type = input('t',0);
    $defaultStore = getCurrentUserStore(); //saf was here
    if($type){
        $store = lpGetStoreById($type); //saf was here
        if($store){
            if($store['user_id'] == get_userid()){
                $defaultStore  = $store;
            }
        }
    }
    $products = getMyStoreProducts($defaultStore['s_id'],'all');
    return $app->render(view("store::manage-products",array('products'=>$products)));
}

function store_manage_single_product_pager($app){
    $app->setTitle(lang('store::manage-products'));
    $id = input('pid');
    $product = getSingleProduct($id);
    $action = input('action');
   // print_r($product); die();
    if(!$product || !is_store_owner($product)) return redirect_to_pager("store-products-manager");
    $message= null;
    $val = input('val');
    if($action == 'delete'){
        deleteProduct($id);
        return redirect_to_pager("store-products-manager");
    }
    if($val){

      //  if($val){
        CSRFProtection::validate();
            $combined_array =  array();
            $arr_keys = $val['label'];
            $arr_val = $val['values'];
            if(!empty($arr_keys) && !empty($arr_val)){
                $combined_array = array_combine($arr_keys,$arr_val);
            }

            //print_r($combined_array);
            validator($val,array(
                'product_name'=>'required',
                'price'=>'required',
                'description'=>'required',
            ));

            if(validation_passes()){
                $val['label'] = $combined_array;

                if($val['type'] == "intangible" && $val['id']=="none"){
                    if(!input_file("product_file")){
                        $message = lang("store::attach-file");
                        return $app->render(view("store::products/edit",array('product'=>$product,'message'=>$message)));
                    }
                }


                $response = save_product($val);
                if($response){
                    return redirect_to_pager("store-products-manager");
                }
            }else{
                $message =  validation_first();
                return $app->render(view("store::products/edit",array('product'=>$product,'message'=>$message)));
            }
       // }
    }

    return $app->render(view("store::products/edit",array('product'=>$product,'message'=>$message)));

}

function store_manage_order_pager($app){
    $app->setTitle(lang("store::manage-order"));
    //saf here
    $storeId = input('t',0);
    $orders = getStoreOrders(10,'all',null,$storeId);
    /*if(!$orders->total){
        return redirect_to_pager('my_orders');
    }*/
    $store_order_id = input('id');
    if($store_order_id){
        $order = getSingleStoreOrder($store_order_id);
        if($order){
            return $app->render(view("store::orders/store_single_order",array('order'=>$order)));
        }
    }
    return $app->render(view("store::orders/store_order",array('orders'=>$orders)));
}

/*function store_manage_single_order_pager($app){

    $store_order_id = input('id');
    if($store_order_id){
        $order = getSingleStoreOrder($store_order_id);
        if($order){
            return $app->render(view("store::orders/single_store_order",array('order'=>$order)));
        }
    }
    return redirect_to_pager("store-order-manager");
}*/

/***
 * Shopping Cart
 */

function add_to_cart_pager($app){
    CSRFProtection::validate(false);
   $quantity = input('q');
    $attributes = input('attrib',null);
    $product_id = input('pid');
    $cart_key = 'my_product_cart_'.get_userid();
    if(session_get($cart_key)){
        $my_cart = session_get($cart_key);
        $my_cart[$product_id] = array('quantity'=>$quantity,'attr'=>$attributes);
        session_put($cart_key,$my_cart);
    }else{
        $my_cart[$product_id] = array('quantity'=>$quantity,'attr'=>$attributes);
        session_put($cart_key,$my_cart);
    }
   // print_r($my_cart);
    $view = view("store::products/cart-list",array('cart'=>$my_cart));
    //$total = getCartTotalPrice($my_cart);
    return json_encode(array('count'=>count($my_cart),'view'=>$view));
   //return count($my_cart);
    //return true;
}

function remove_from_cart_pager($app){
    $my_cart = session_get('my_product_cart_'.get_userid());
    $id = input('pid');
    if(isset($my_cart[$id])) unset($my_cart[$id]);
    session_put('my_product_cart_'.get_userid(),$my_cart);
    return count($my_cart);
}


function get_carts_pager($app){
    $app->setTitle(lang('store::your-cart'));
    $products_cart = session_get('my_product_cart_'.get_userid());
    return $app->render(view('store::cart',array('products'=>$products_cart)));
}

function clear_cart_pager(){
    clearCartNow();
    return true;
}

function transaction_pager($app){
    $app->setTitle(lang("store::transactions"));
    return $app->render(view("store::transactions"));
}

function transaction_withdraw_pager($app){
    $app->setTitle(lang("store::transaction-withdrawal"));
    $val = input('val');
    $message = null;
    if($val){
        CSRFProtection::validate();
        validator($val,array(
            'amount'=>'required|numeric'
        ));

        if(validation_passes()){
            //check if the request balance is greater than their request
            $availableBalance = getUserAvailableBalance();
            if($val['amount'] > $availableBalance){
                $message = lang("store::the-request-is-more-than-your-bal");
                return $app->render(view("store::widthraw",array('message'=>$message,'type'=>'error')));
            }

            //insert new request
            insertNewWithdrawRequest($val);
            $message = lang("store::your-request-has-been-submitted-successfully");
            return $app->render(view("store::widthraw",array('message'=>$message,'type'=>'success')));
        }else{
            $message = validation_first();
            return $app->render(view("store::widthraw",array('message'=>$message,'type'=>'error')));
        }
    }
    return $app->render(view("store::widthraw",array('message'=>$message,'type'=>'error')));
}

function manager_settings_pager($app){
    $app->setTitle(lang("store::account-settings"));
    $settings = getMyStoreAccountSettings();
   return  $app->render(view("store::account-setting",array('settings'=>$settings)));
}

function wired_transfer_pager($app){
    $app->setTitle(lang("store::wired-transfer-set-up"));
    $settings = getMyStoreAccountSettings();
    $action = input('type');
    $val = input('val');
    if($action == 'success'){
        return $app->render(view("store::wired-success"));
    }
    $message = null;
    if($val){
        validator($val,array(
            'swift_code'=>'required',
            'bank_name'=>'required',
            'bank_address'=>'required',
            'account_number'=>'required',
            'account_name'=>'required',
            'address'=>'required',
            'phone'=>'required',
            'city_state'=>'required',
            'country'=>'required',
            'add_info'=>'required'
        ));

        if(validation_passes()){
           $response =  saveWiredAccountDetails($val,'wire-transfer');
            if($response){
                return redirect_to_pager("manager-settings");
            }
        }else{
            $message = validation_first();
            return  $app->render(view("store::wired-transfer",array('settings'=>$settings,'message'=>$message)));
        }
    }
    return  $app->render(view("store::wired-transfer",array('settings'=>$settings,'message'=>$message)));
}

function skrill_transfer_pager($app){
    $app->render(lang("store::skrill-account-setup"));
    $settings = getMyStoreAccountSettings();
    $val = input('val');
    $message = null;
    if($val){
        validator($val,array(
           'skrill_email'=>'required|email'
        ));
        if(validation_passes()){
            $response =  saveWiredAccountDetails($val,'skrill');
            if($response){
                return redirect_to_pager("manager-settings");
            }
        }else{
            $message = validation_first();
            return  $app->render(view("store::skrill_setup",array('settings'=>$settings,'message'=>$message)));
        }

    }
    return $app->render(view("store::skrill_setup",array('settings'=>$settings,'message'=>$message)));
}

function manager_paypal_transfer_pager($app){
    $app->render(lang("store::paypal-account-setup"));
    $store = getCurrentUserStore();
    if(!$store) return redirect_to_pager('add-seller');
    $settings = getMyStoreAccountSettings();
    $val = input('val');
    $message = null;
    if($val){
        validator($val,array(
            'paypal_email'=>'required|email'
        ));
        if(validation_passes()){
            $response =  saveWiredAccountDetails($val,'paypal');
            if($response){
                return redirect_to_pager("manager-settings");
            }
        }else{
            $message = validation_first();
            return  $app->render(view("store::paypal_setup",array('settings'=>$settings,'message'=>$message)));
        }

    }
    return $app->render(view("store::paypal_setup",array('settings'=>$settings,'message'=>$message)));
}

function product_coupon_pager($app){
    $app->render(lang("store::coupons"));
    $store = getCurrentUserStore();
    if(!$store) return redirect_to_pager('add-seller');


}