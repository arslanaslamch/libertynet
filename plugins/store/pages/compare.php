<?php

function compare_pager($app){
    $app->setTitle(lang("store::compare-products"));
    $products = array_filter(session_get("compared-products",array()));
    return $app->render(view("store::products/compare",array("products"=>$products)));
}

function compare_ajax_pager($app){
    $pid = input('p');
    $action = input('action');
    $status = input('status',0);
    switch($action){
        case 'add':
            $ccp = session_get("compared-products",array());
            //current compared products
            if($status == 0){
                //count the number of products 3  already available
                if(count($ccp) > 2) return json_encode(array('status'=>403,'message'=>lang('store::only-3-products-can-compared-at-a-time')));
                //we are adding it
                if($pid && !in_array($pid,$ccp)) $ccp[] = $pid;
                session_put("compared-products",$ccp);
                $item = getSingleProduct($pid);
                $view = view("store::products/top-compare",array('item'=>$item));
                return json_encode(array('count'=>count($ccp), 'status'=>1,'message'=>lang('store::product-added-successfully'),'view'=>$view));
            }else{
                //we are removing the already added
                    $key = array_search($pid,$ccp);
                    if($key!==false){
                        unset($ccp[$key]);
                    }
                session_put("compared-products",$ccp);
                return json_encode(array('count'=>count($ccp), 'status'=>0,'message'=>lang('store::product-removed-from-compare')));
            }
            break;
        case 'clear':
            session_put("compared-products",array());
            return json_encode(array('status'=>0,'message'=>lang('store::cleared-successfully')));
            break;
    }
    return 404;

}