<?php

function stores_shipping_pager($app){
    $app->setTitle(lang("store::shipping"));
    $store = getCurrentUserStore();
    if(!$store) return redirect_to_pager('store_homepage');
    $store_id = $store['s_id'];
    $shipping = get_store_shipping_settings($store_id);
   return  $app->render(view("store::shipping/list",array('shipping'=>$shipping)));
}

function store_shipping_add_pager($app){
    $app->setTitle(lang("store::add-shipping-settings"));
    $store = getCurrentUserStore();
    $message = null;
    if(!$store) return redirect_to_pager('store_homepage');
    $val = input('val');
    if($val){
        validator($val,array(
            'zone'=>'required',
            'shipping_method'=>'required',

        ));
        if(validation_passes()){
            if(empty($val['regions'])){
                $message = lang("store::select-regions");
                return $app->render(view('store::shipping/add',array('message'=>$message)));
            }
            //expired time
            $val['store_id'] = $store['s_id'];
            save_shipping_settings($val);
           // echo 'waka';die();
            return redirect_to_pager('shipping');
        }
        else{
            $message = validation_first();
        }
    }
    return $app->render(view('store::shipping/add',array('message'=>$message)));
}

function shipping_manage_pager($app){
    $app->setTitle(lang("store::shipping"));
    $store = getCurrentUserStore();
    if(!$store) return redirect_to_pager('store_homepage');
    $shipping_id = sanitizeText(input('id'));
    $message = null;
    $action = input('action');
    $shipping = getSingleShipping($shipping_id);
   // print_r($shipping);die();
    if($shipping){
        if(!isShippingSettingsOwner($shipping)) return redirect_to_pager('store_homepage');
        if($action == 'delete'){
            deleteShipping($shipping_id);
            return redirect_to_pager('shipping');
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
                        return $app->render(view('store::shipping/edit',array('message'=>$message)));
                    }
                    //expired time
                    $val['shipping_id'] = $shipping_id;
                    $val['store_id'] = $store['s_id'];
                    save_shipping_settings($val);
                    return redirect_to_pager('shipping');
                }
            }
            return $app->render(view("store::shipping/edit",array('shipping'=>$shipping,'message'=>$message)));
        }
    }
}


