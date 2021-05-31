<?php

function categories_list_pager($app){
    $app->setTitle(lang("store::categories"));
    app()->leftMenu = false;
    return $app->render(view("store::categories-list"));
}

function product_add_pager($app){
    $app->setTitle(lang("store::add-product"));
    $status = 0;
    $message = '';
    $redirect_url = '';

    if(getCurrentUserStore()){
        //check if the store is verified
        $store = getCurrentUserStore();
        if(!$store['status']){
            return $app->render(view("store::store-not-verified"));
        }
    }else{
        return $app->render(view("store::add-seller-before-product"));
    }
    $val = input("val");

    if($val){
        //print_r($val);die();
        CSRFProtection::validate();
        $combined_array =  array();
        $arr_keys = array_filter($val['label']);
        $arr_val = array_filter($val['values']);
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
            $slug = toAscii($val['product_name']);
            if (empty($slug)) $slug = md5(time().get_userid());
            //check if slug exist
            if(productSlugExist($slug)){
                $slug = md5(md5($slug.time()));
            }
            $val['slug'] = $slug;
            if($val['type'] == "intangible" && $val['id']=="none"){
                if(!input_file("product_file")){
                    $message = lang("store::attach-file");
                    //return $app->render(view('store::products/add',array("message"=>$message)));
                }
            }

            $response = save_product($val);
            if($response){
                $status = 1;
                $message = lang("store::product-saved-successfully");
                $redirect_url = url_to_pager("products-home");
            }
        }else{
            $message =  validation_first();
        }
        if(input('ajax')) {
            $result = array(
                'status' => (int) $status,
                'message' => (string) $message,
                'redirect_url' => (string) $redirect_url,
            );
            $response = json_encode($result);
            return $response;
        }
    }
    return $app->render(view('store::products/add',array("message"=>$message)));
}

function products_home_pager($app){
    $app->setTitle(lang("store::products"));
    $filter = input('filter','all');
    $sort = input('sort','newest');
    $term = input('term',null);
    $tag = input('tag',null);
    $category = input('category',null);
    $type = input('type','browse'); //browse is working as "approved"
    $products = lp_get_products($type,10,$filter,$sort,$term,$category,$tag,null);
    return $app->render(view('store::products/home',array('products'=>$products,'ps'=>null,'cid'=>0)));
}

function products_categories_home_pager($app){
    $category_slug = segment(3);
    $app->setTitle(lang('store::categories'));
    $category_arr = explode('_',$category_slug);
    $filter = input('filter','all');
    $sort = input('sort','newest');
    $term = input('term',null);
    $tag = input('tag',null);
    $type = input('type','browse');

    if(count($category_arr) > 2){
        //this contain category and subcategory
        $category_id = $category_arr[0];
        $ps = $category_arr[1];
        //category_id, get parent
        $category = store_get_category_by_slug($category_id);
        if($category){
            $child_category = lang($category[0]['title']);
            $parent_category = store_get_category_by_slug($category[0]['parent_id']);
            $both = $category_id.'::'.lang($parent_category[0]['title']).'::'.$child_category;
            $both_id = $category_id.'::'.$category[0]['parent_id'];
            $products = lp_get_products($type,10,$filter,$sort,$term,$both,$tag,$both_id);
            $append = ' > '.ucwords(lang($parent_category[0]['title'])).' > '.ucwords($child_category);
            return $app->render(view('store::products/home',array('ps'=>$parent_category[0]['id'],'products'=>$products,'append'=>$append,'cid'=>$category_id)));
        }
    }else{
        //it is a category - single
        $category_id = $category_arr[0];
        //category_id, get parent
        $category = store_get_category_by_slug($category_id);
        //print_r($category);die();
        $child_category = lang($category[0]['title']);
        if($category){
            $products = lp_get_products($type,10,$filter,$sort,$term,$child_category,$tag,$category_id);
            $append = ' > '.ucwords($child_category);
            return $app->render(view('store::products/home',array('ps'=>null,'products'=>$products,'append'=>$append,'cid'=>$category_id)));
        }
    }


}

function product_single_pager($app){
    $slug = segment(2);
    $product = getSingleProduct($slug);
    if(!$product) return redirect_to_pager("products-home");
    if(config('enable-admin-approve-product',false)){

        if(!$product['status'])
            return $app->render(view("store::products/product-not-yet-verified"));
    }
    db()->query("UPDATE lp_products SET views = views + 1 WHERE slug='{$slug}'");
    $app->setTitle($product['name'])
        ->setKeywords($product['tags'])
        ->setDescription(str_limit(strip_tags($product['description']), 100));
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $product['name'], 'description' => str_limit(strip_tags($product['description']), 100), 'image' => $product['main_photo'] ? url_img($product['main_photo'], 200) : '', 'keywords' => $product['tags']));

    return $app->render(view("store::products/single-product",array('product'=>$product)));
}

function add_to_wishlist_pager($app){
    $pid = input('pid');
    $attr = input('attrib',array());
    addToWishlist($pid,$attr);
    return lang("store::product-successfully-added-to-wishlist");
}

function product_quick_view_pager($app){
    $pid = input('pid');
    $product = getSingleProduct($pid);
    return view("store::products/quickview",array('product'=>$product));
    //return json_encode(array('v'=>view("store::products/quickview",array('product'=>$product))));
}

function my_wish_list_pager($app){
    $userid = get_userid();
    $wishes = getMyWishList($userid);
    return $app->render(view("store::my_wish_list",array('wishes'=>$wishes)));
}

function my_remove_wishlist_pager($app){
    $userid = get_userid();
    $pid = input('pid');
    removeWishes($pid,$userid);
    return true;

}