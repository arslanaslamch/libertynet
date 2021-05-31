<?php

get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->setActive();

function import_demos_pager($app){
    $app->setTitle("Import Demo");
    $action = input('action');
    $message = "";
    $type = input('type');
    switch($action){
        case 'categories':
            if($type == 'add'){
                importStoreCategories();
                $message = lang("store::categories-imported-successfully");
            }
            if($type == 'delete'){
                deleteImportedStoreCategories();
                $message = lang("store::categories-imported-deleted");
            }
            break;
        case 'slides':
            if($type == 'add'){
                addImportSlides();
                $message = lang("store::slides-imported-successfully");
            }
            if($type == 'delete'){
                deleteImportedSlides();
                $message = lang("store::slides-deleted-successfully");
            }
            break;
        case 'products':

            break;
    }
    return $app->render(view("store::admin/demo/import",array('message'=>$message)));
}
function categories_pager($app){
    $app->setTitle(lang('store::categories-manager'));
    $type = input('type','category');
    $categories = lp_store_categories('get_categories',10,$type);
    //   print_r($categories); die();
    if($type == 'slides'){
        return $app->render(view('store::admin/slides/lists',array('categories'=>$categories)));
    }
    return $app->render(view('store::admin/categories/lists',array('categories'=>$categories)));
}

function categories_manager_pager($app){
    $app->setTitle(lang('store::categories-manager'));
    $message = '';
    $val  = input('val');
    $slug = input('slug');
    $action  = input('action');
    $type = input('type','category');
    if($action== 'order'){
        $ids = input('data');
        for($i = 0; $i < count($ids); $i++) {
            update_store_category_order($ids[$i], $i,$type);
        };
    }
    $category = store_get_category_by_slug($slug);
    if(empty($category)){
        return redirect_to_pager("admin-store-category");
    }
    switch($action){
        case 'edit':
            if($val){
                CSRFProtection::validate();
                save_store_category($val,$category[0],$type);
                //return redirect_to_pager("admin-store-category").'?type=';
                return redirect(url_to_pager("admin-store-category").'?type='.$type);
            }
            if($type == 'slides'){
                return $app->render(view('store::admin/slides/edit',array('cat'=>$category[0],'message'=>$message)));
            }
            return $app->render(view('store::admin/categories/edit',array('cat'=>$category[0],'message'=>$message)));
            break;
        case 'delete':
            $c_id = $category[0]['id'];
            $sql = "DELETE FROM `lp_store_categories` WHERE `id`='{$c_id}'";
            if($category[0]['image']){
                delete_file(path($category[0]['image']));
            }
            db()->query($sql);
            return redirect(url_to_pager("admin-store-category").'?type='.$type);
            break;
        default:
            return $app->render(view('store::admin/categories/manage_sub',array('category'=>$category[0])));
    }
}

function categories_add_pager($app){
    $app->setTitle(lang('store::categories-manager'));
    $message = '';
    $val = input('val');
    $type = input('type','category');
    if($val){
        CSRFProtection::validate();
        $result =  add_store_category($val,$type);
        if($result){
            $message = lang('store::category-successfully-added');
        }else{
            $message = "something went wrong";
        }
        return redirect(url_to_pager("admin-store-category").'?type='.$type);
    }
    if($type == 'slides'){
        return $app->render(view('store::admin/slides/add',array('message'=>$message)));
    }
    return $app->render(view('store::admin/categories/add',array('message'=>$message)));
}


function sellers_pager($app){
    $app->setTitle(lang("store::manage-sellers"));
    $sellers = lp_get_stores();
    return $app->render(view('store::admin/seller/list',array('sellers'=>$sellers)));
}

function seller_details_pager($app){
    $app->setTitle(lang("store::seller-details"));
    $id = input('id');
    $seller = lpGetStoreById($id);
    if(!$seller) return redirect_to_pager("admin-manage-sellers");
    return $app->render(view("store::admin/seller/details",array('seller'=>$seller)));
}
function sellers_manage_pager($app){
    $app->setTitle(lang("store::manage-sellers"));
    $action = input('action');
    $id = input('id');
    $message = '';
    $val = input('val');
    $seller = lpGetStoreById($id);
    if(!$seller) return redirect_to_pager("admin-manage-sellers");
    switch($action){
        case 'edit':
            if($val){
                CSRFProtection::validate();
                save_store_seller($val,true);
                return redirect_to_pager("admin-manage-sellers");
            }
            return $app->render(view("store::admin/seller/edit",array('seller'=>$seller,'message'=>$message)));
            break;
        case 'delete':
            removeStore($id);
            return redirect_to_pager("admin-manage-sellers");
            break;
    }
}

function products_list_pager($app){
    $app->setTitle(lang("store::manage-products"));
    $products = lp_get_products('any','all',null,null,null,null,null);
    return $app->render(view('store::admin/products/list',array('products'=>$products)));
}

function product_manage_pager($app){
    $app->setTitle(lang('store::manage-products'));
    $id = input('pid');
    $action = input('action');
    // echo $id;die();
    $product = getSingleProduct($id);
    if(!$product) return redirect_to_pager("admin-manage-products");;
    if(!is_store_owner($product) and !is_admin()) return redirect_to_pager("admin-manage-products");
    $message= null;

    switch($action){
        case 'edit':
            $val = input('val');
            // if($val){
            if($val){
                CSRFProtection::validate();
                $combined_array =  array();
                if(isset($val['label']) && isset($val['values'])){
                    $arr_keys = array_filter($val['label']);
                    $arr_val = array_filter($val['values']);
                    if(!empty($arr_keys) && !empty($arr_val)){
                        $combined_array = array_combine($arr_keys,$arr_val);
                    }
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
                            return $app->render(view("store::admin/products/edit",array('product'=>$product,'message'=>$message)));
                        }
                    }
                    $response = save_product($val,true);
                    if($response){
                        return redirect_to_pager("admin-manage-products");
                    }
                }else{
                    $message =  validation_first();
                    return $app->render(view("store::admin/products/edit",array('product'=>$product,'message'=>$message)));
                }
                //  }
            }
            break;
        case 'delete':

            deleteProduct($id);
            return redirect_to_pager("admin-manage-products");
            break;
    }

    return $app->render(view("store::admin/products/edit",array('product'=>$product,'message'=>$message)));

}

function orders_list_pager($app){
    $app->setTitle(lang("store::orders"));
    $orders = getAllOrders();
    $id = input('id',null);
    if($id){
        $order =  getSingleOrder($id);
        return $app->render(view("store::admin/orders/single-view",array("order"=>$order)));
    }
    return $app->render(view("store::admin/orders/list",array('orders'=>$orders)));
}

function orders_update_pager($app){
    $id = input('pid');
    $status = input('s');
    $order = getSingleOrder($id);
    if($status == 1){
        //make it successful
        updateOrderStatus($order,'successful');
    }else{
        //make it pending
        updateOrderStatus($order,'pending');
    }
    return true;
}

function update_withdrawal_status_pager(){
    $store_id = input('store_id');
    $store = lpGetStoreById($store_id);
    $user_id = $store['user_id'];
    $amount = input("amount");
    $time = time();
    $account = getMyStoreAccountSettings($user_id);
    if(!$account) return false;
    $payment_type = $account['payment_type'];
    //$statusCode = (int)input('s');
    db()->query("INSERT INTO lp_store_withdrawal(user_id,status,store_id,time,amount,method)
    VALUES ('{$user_id}',1,'{$store_id}','{$time}','{$amount}','{$payment_type}')");
    return true;
}

function payout_request_list_pager($app){
    $type = input('type',null);
    $app->setTitle("Payout Eligible List");
    $payout_requests = getStoresEligibleForWithdraw();
    // $payout_requests = getWithdrawalRequests($type);
    return  $app->render(view("store::admin/payout-request",array('request'=>$payout_requests)));

}

function admincp_seller_setup_account_details_pager($app){
    $app->setTitle("Account details");
    $store_id = input('id');
    $store = lpGetStoreById($store_id);
    $user_id = $store['user_id'];
    $account = getMyStoreAccountSettings($user_id);
    return $app->render(view("store::admin/account_setup_detail",array("settings"=>$account,'store'=>$store)));
}

function successful_payouts_list_pager($app){
    $app->setTitle("Successful Payouts");
    $payouts = getAllSuccessfulPayouts();
    return $app->render(view("store::admin/successful_payouts",array('payouts'=>$payouts)));
}

function shipping_list_pager($app){
    $app->setTitle("Manage Shipping");
    $shipping = get_store_shipping_settings('nothing','all');
    return  $app->render(view("store::admin/shipping/list",array('shipping'=>$shipping)));
}

function coupon_list_pager($app){
    $app->setTitle("Manage Coupons");
    $coupons = get_store_coupons('nothing','all');
    return  $app->render(view("store::admin/coupons/list",array('coupons'=>$coupons)));
}
function admin_manage_coupon_pager($app){
    $app->setTitle(lang("store::coupons"));
    $coupon_id = sanitizeText(input('id'));
    $message = null;
    $action = input('action');
    $coupon = getSingleCoupon($coupon_id);
    $store_id = $coupon['store_id'];
    if($coupon){
        if($action == 'delete'){
            deleteCoupon($coupon_id);
            return redirect_to_pager('admin-coupon-list');
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
                        return $app->render(view('store::admin/coupons/edit',array('coupon'=>$coupon,'message'=>$message)));
                    }
                    //expired time
                    $expiry_date = makeExpiryTime($val['expiry_date']);
                    if(time() > $expiry_date){
                        $message = lang("store::invalid-discount-date");
                        return $app->render(view('store::admin/coupons/edit',array('coupon'=>$coupon,'message'=>$message)));
                    }
                    $val['expiry_date'] = $expiry_date;
                    $val['coupon_id'] = $coupon_id;
                    $val['store_id'] = $store_id;
                    save_coupon($val);
                    return redirect_to_pager('admin-coupon-list');
                }
            }
            return $app->render(view("store::admin/coupons/edit",array('coupon'=>$coupon,'message'=>$message)));
        }
    }
}
function admin_manage_shipping_pager($app){
    $app->setTitle(lang("store::shipping"));
    $shipping_id = sanitizeText(input('id'));
    $message = null;
    $action = input('action');
    $shipping = getSingleShipping($shipping_id);
    $store_id = $shipping['store_id'];
    // print_r($shipping);die();
    if($shipping){
        if($action == 'delete'){
            deleteShipping($shipping_id);
            return redirect_to_pager('admin-shipping-list');
        }
        if($action == 'edit'){
            $val = input('val');
            if($val){
                validator($val,array(
                    'zone'=>'required',
                    'shipping_method'=>'required',
                ));
                if(validation_passes()){

                    if(empty($val['regions'])){
                        $message = lang("store::select-regions");
                        return $app->render(view('store::admin/shipping/edit',array('message'=>$message)));
                    }
                    //expired time
                    $val['shipping_id'] = $shipping_id;
                    $val['store_id'] = $store_id;
                    save_shipping_settings($val);
                    return redirect_to_pager('admin-shipping-list');
                }
            }
            return $app->render(view("store::admin/shipping/edit",array('shipping'=>$shipping,'message'=>$message)));
        }
    }
}

/** charge refund, awon eyan ti Emoney */
function charge_and_refund_pager($app){
    $app->setTitle(lang("emoney::refund-and-charge-order"));
    if(!plugin_loaded('emoney')){
        $view = "<div class='wrapper'><div class='wrapper-title'>This feature Requires Emoney Plugin https://lightedphp.com/plugins/emoney</div></div>";
        return $app->render($view);
    }
    $orders = get_stores_orders_by_emoney();
    $action = input('action');
    $id = input('id');
    if($id){
        $order_store = getEmoneyStoreOrder($id);

        $order_basket = getSingleOrder($order_store['order_id']);
        $buyer = $order_basket['user_id'];
        $price = $order_store['price'] * $order_store['quantity'];

        $product = getSingleProduct($order_store['product_id']);
        //seller
        $seller = $order_store['store_owner'];
        switch($action){
            case 'refund':
                //debit seller
                $action = lang("emoney::store"). ' :  '.$product['name']  .'  '. lang("emoney::refund");
                emoney_credit_user('debit', $action, $price, $seller);

                //credit the store person
                emoney_credit_user('credit', $action, $price, $buyer);

                db()->query("UPDATE lp_store_order SET emoney=1, status=7 WHERE id='{$id}'");
                break;
            case 'charge':
                //charge is synonymous successful, we are still one
                //return redirect(url_to_pager("refund-and-charge-order"));
                $action = lang("emoney::store"). ' :  '.$product['name']  .' '. lang("emoney::purchase");

                emoney_credit_user('debit', $action, $price, $buyer);

                //credit the store person
                emoney_credit_user('credit', $action, $price, $seller);

                db()->query("UPDATE lp_store_order SET emoney=1,status=1 WHERE id='{$id}'");
                //update parent
                break;
            default :
                //return $view
                break;
        }
    }
    return $app->render(view("store::admin/orders-charge/list",array('orders'=>$orders)));
}