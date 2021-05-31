<?php

//icon
register_hook("shortcut.menu.images",function($arr){
    $arr['ion-ios-cart'] = img("store::images/store.png");
    return $arr;
});

register_hook("add.more.mobile.menu",function(){
    echo '<style>#explore-menu .container > ul > li > a{ margin-right: 12px}</style>';
   echo '<li id="strore-dropdown-container"  class="">
      <a ajax="true" href="'.url_to_pager("product-cart").'"><i class="ion-android-cart"></i> <span class="ProductCount"></span> </a></li>';
});

//version 7zero
/*register_hook("mobile.product.cart",function(){
    $mpc = session_get('my_product_cart_'.get_userid());
    if(!$mpc) $mpc = array();
    $mpc =  ($mpc) ? count($mpc) : '0';
    echo '<li id="strore-dropdown-container"  class="">
      <a ajax="true" href="'.url_to_pager("product-cart").'"><i style="font-size: 20px;vertical-align: sub;" class="ion-android-cart"></i>
      <span class="ProductCount">'.$mpc.'</span> </a></li>';
});*/

load_functions("store::helper");
load_functions("store::rating");
load_functions("store::coupons");
load_functions("store::shipping");
load_functions("store::store.one");
load_functions("store::billing");
load_functions("store::cart");
load_functions("store::compare");
load_functions("store::store");
load_functions("store::customized"); //saf mod
register_asset("store::css/lp_store.css");
register_asset("store::css/chosen.css");
register_asset("store::css/jcarousel.css");
register_asset("store::css/chosen-boostrap.css");
register_asset("store::css/jquery.datetimepicker.css");
register_asset("store::css/store_custom_one.css"); //april customization start

//register_asset("store::css/jquery.dataTables.css");
register_asset("store::css/dataTables.bootstrap.min.css");
//register_asset("store::css/dataTables.foundation.min.css");
register_asset("store::css/dataTables.bootstrap4.min.css");

register_asset("store::css/store.css");
register_asset("store::css/prod.css");//saf css styling
register_asset("store::css/customized.css");//saf customized

//register_asset("store::js/datatables/dataTables.bootstrap.js");
register_asset("store::js/jquery.dataTables.min.js");
register_asset("store::js/dataTables.bootstrap.js");
//register_asset("store::js/dataTables.responsive.min.js");
//register_asset("store::js/dataTables.foundation.min.js");
register_asset("store::js/dataTables.bootstrap4.js");
register_asset("store::js/jcarousel.js");
register_asset("store::js/jcarousel_init.js");
register_asset("store::js/store.js");
register_asset("store::js/prod.js"); //saf carousel styling
register_asset("store::js/store.one.js");
register_asset("store::js/coupon.js");
register_asset("store::js/customized.js");
register_hook('system.started', function($app) {
    if ($app->themeType == 'backend') {
        register_hook('after-render-js',function($html){
                 $html .= '<div id="alertModal" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body">
              <input type="hidden" id="AdminMessage" value="'.lang("store::success-message").'" />
            </div>
            <div class="modal-footer">

                <button type="button"  data-dismiss="modal" aria-label="Close" class="btn btn-primary btn-sm">'.lang("ok").'</button>
            </div>
        </div>
    </div>
</div>';

            return $html;
        });

        register_hook('admin.modals',function(){
            $html = '<script>
            window.pageLoadHooks = [];
            function addPageHook(hook) {
                window.pageLoadHooks.push(hook);
            };
</script>';
            echo  $html;
        });
    }
});


register_hook("role.permissions", function ($roles) {
    $roles[] = array(
        'title' => lang('store::store-permissions'),
        'description' => '',
        'roles' => array(
            'can-create-store' => array('title' => lang('store::can-create-store'), 'value' => 1),

        )
    );
    return $roles;
});
add_available_menu('store', 'stores', 'ion-ios-cart');
//rating pager
register_pager("rating", array(
    'filter' => 'user-auth',
    'as' => 'rate-product',
    'use' => 'store::rating@rating_index_pager'
));

register_pager("stores", array(
    'as' => 'store_homepage',
    'use' => 'store::store@store_index_pager'
));

register_pager('store/earnings', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'store-transactions',
    'use' => 'store::store@transaction_pager'
));

register_pager('store/account-settings', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'manager-settings',
    'use' => 'store::store@manager_settings_pager'
));

register_pager('store/account-settings/wired-transfer', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'wired-transfer',
    'use' => 'store::store@wired_transfer_pager'
));
register_pager('store/account-settings/skrill', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'skrill_manager_settings',
    'use' => 'store::store@skrill_transfer_pager'
));
register_pager('store/account-settings/paypal', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'manager_paypal_transfer',
    'use' => 'store::store@manager_paypal_transfer_pager'
));

register_pager('store/earnings/withdraw', array(
    //'filter' => 'store-profile',
    'filter' => 'auth', //saf
    'as' => 'store-transactions-withdraw',
    'use' => 'store::store@transaction_withdraw_pager'
));

register_pager('store/add_to_wishlist', array(
    'filter' => 'user-auth',
    'as' => 'add-to-wishlist',
    'use' => 'store::product@add_to_wishlist_pager'
));
register_pager('store/my_wishlist', array(
    'filter' => 'user-auth',
    'as' => 'my-wish-list',
    'use' => 'store::product@my_wish_list_pager'
));

register_pager('store/remove_wishlist', array(
    'filter' => 'user-auth',
    'as' => 'my-wish-list-remove',
    'use' => 'store::product@my_remove_wishlist_pager'
));

register_pager('store/create', array(
    'filter' => 'user-auth',
    'as' => 'add-seller',
    'use' => 'store::store@add_seller_pager'
));


register_pager('store/manager', array(
    'as' => 'store-manager',
    'filter' => 'auth', //saf mod
    'use' => 'store::store@store_manage_pager'
));

/*register_pager('store/manager/edit', array(
    'filter' => 'store-profile',
    'as' => 'store-manager-edit',
    'use' => 'store::store@store_manage_edit_pager'
));*/

register_pager('store/manager/products', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'store-products-manager',
    'use' => 'store::store@store_manage_product_pager'
));


register_pager('store/manager/product/manage', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'store-manage-single-product',
    'use' => 'store::store@store_manage_single_product_pager'
));

register_pager('store/manager/order', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'store-order-manager',
    'use' => 'store::store@store_manage_order_pager'
));

register_pager('store/manager/order/manage', array(
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'store-single-order-manager',
    'use' => 'store::store@store_manage_single_order_pager'
));


/*
 * Producer Start
 *
 */
register_pager('store/manager/producer/add', array(
    'as' => 'create-producer',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'use' => 'store::producer@producer_create_pager'
));

register_pager('store/manager/producer/edit', array(
    'as' => 'edit-producer',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'use' => 'store::producer@producer_edit_pager'
));

register_pager('store/manager/producer', array(
    'as' => 'producer-home',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'use' => 'store::producer@producer_index_pager'
));

register_pager('store/remove_producer', array(
    'as' => 'producer-remove',
    'filter' => 'store-profile',
    'use' => 'store::producer@producer_remove_pager'
));

register_pager("store/follower", array(
    'use' => 'store::store@store_follower_pager',
    'filter' => 'user-auth',
    'as' => 'company-follower'
));

/**
 * Cart start
 */

function check_guest_puchase($param){
    if(config('enable-guest-product-purchase',1)){
        unset($param['filter']);
        return $param;
    }else{
        return $param;
    }
}

register_pager('store/cart', check_guest_puchase(array(
    'filter'=>'user-auth',
    'as' => 'product-cart',
    'use' => 'store::store@get_carts_pager'
)));

register_pager('store/cart/remove', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'product-remove-from-cart',
    'use' => 'store::store@remove_from_cart_pager'
)));

register_pager('store/add_to_cart',check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'product-add-new-to',
    'use' => 'store::store@add_to_cart_pager'
)));


register_pager('store/clear_cart', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'clear-cart',
    'use' => 'store::store@clear_cart_pager'
)));

register_hook('header', function () {
    echo view("store::sidebar");
});

register_hook('header', function () {
    echo view("store::store-modal");
});
/**
 * cart end
 */

/**
 * Orders start
 */
register_pager('store/orders/checkout', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-checkout',
    'use' => 'store::orders@checkout_pager'
)));

register_pager('store/orders/checkout/payment', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-checkout-payment',
    'use' => 'store::orders@checkout_payment_pager'
)));

register_pager("store/checkout/payment/paypal",check_guest_puchase(array(
    'use' => "store::orders@product_paypal_process_pager",
    'as' => 'product-paypal-notify'
)));

register_pager('store/checkout/payment/stripe', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-payment-stripe',
    'use' => 'store::orders@checkout_payment_stripe_pager'
)));

register_pager('store/payment/ondelivery', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-payment-delivery',
    'use' => 'store::orders@payment_ondelivery_pager'
)));

register_pager('store/checkout/payment/2checkout', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-payment-2checkout',
    'use' => 'store::orders@checkout_payment_TwoCheckout_pager'
)));

register_pager('store/checkout/payment/2checkout/status', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-payment-2checkout-status',
    'use' => 'store::orders@Two_checkout_check_status_pager'
)));

register_pager('store/my_orders', array(
    'filter' => 'user-auth',
    'as' => 'my_orders',
    'use' => 'store::orders@my_order_pager'
));

register_pager('quickview/product', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'product-quick-view',
    'use' => 'store::product@product_quick_view_pager'
)));


register_pager('store/orders/checkout/submit', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-checkout-submit',
    'use' => 'store::orders@submit_checkout_order'
)));

register_pager('store/orders/checkout_complete', check_guest_puchase(array(
    'filter' => 'user-auth',
    'as' => 'orders-checkout-complete',
    'use' => 'store::orders@checkout_complete_pager'
)));

/***
 * Compare proedcuts
 */

register_pager("products/compare",check_guest_puchase(array(
    'use'=>'store::compare@compare_pager',
    'as'=>'compare-products',
    'filter'=>'auth'
)));

register_pager("products/ajax/compare",check_guest_puchase(array(
    'use'=>'store::compare@compare_ajax_pager',
    'as'=>'compare-ajax',
    'filter'=>'auth'
)));
/**
 * Orders End
 */




/*
 * Producer end
 */

/*
 * Product
 *
 */

register_pager("store/product/add", array(
    'use' => 'store::product@product_add_pager',
    'filter' => 'user-auth',
    'as' => 'add-product'
));


register_pager("store/products", array(
    'use' => 'store::product@products_home_pager',
    'as' => 'products-home'
));
register_pager("store/categories", array(
    'use' => 'store::product@categories_list_pager',
    'as' => 'products-categories-list'
));

register_pager("store/products/categories/{slugs}", array(
    'use' => 'store::product@products_categories_home_pager',
    'as' => 'products-categories-home'
))->where(array('slugs' => '[a-zA-Z0-9\-\_]+'));


register_pager("store/product/{slugs}", array('use' => 'store::product@product_single_pager', 'as' => 'single-product'))->where(array('slugs' => '[a-zA-Z0-9\-\_]+'));

/*
 * Product end
 */



/**
 *coupon pager
 *
 */

register_pager("store/coupons", array(
    'use' => 'store::coupon@stores_coupon_pager',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'coupons'
));

register_pager("store/coupon/add", array(
    'use' => 'store::coupon@store_coupon_add_pager',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'add-coupon'
));
register_pager("store/coupons/manage", array(
    'use' => 'store::coupon@coupons_manage_pager',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'manage_coupon'
));
register_pager("store/shipping", array(
    'use' => 'store::shipping@stores_shipping_pager',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'shipping'
));

register_pager("store/shipping/add", array(
    'use' => 'store::shipping@store_shipping_add_pager',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'add-shipping'
));
register_pager("store/shipping/manage", array(
    'use' => 'store::shipping@shipping_manage_pager',
    //'filter' => 'store-profile',
    'filter' => 'auth',
    'as' => 'manage_shipping'
));

/*register_pager("store/{slug}", array(
    'use' => 'store::store@single_store_pager',
    'as' => 'single-store',
    'filter'=>'store-filter'
))->where(array('slug' => '[a-zA-Z0-9\-\_]+'));*/

//we are setting up store profile here
register_post_pager("store/change/cover", array('use' => 'store::profile@upload_cover_pager', 'filter' => 'user-auth'));
register_pager("store/cover/reposition", array('use' => 'store::profile@reposition_cover_pager', 'filter' => 'user-auth'));
register_pager("store/cover/remove", array('use' => 'store::profile@remove_cover_pager', 'filter' => 'user-auth'));
register_pager("store/change/logo", array('use' => 'store::profile@change_logo_pager', 'filter' => 'user-auth'));

register_hook('system.started', function() {
    register_pager("store/{slug}", array('use' => "store::profile@store_profile_pager", 'filter' => 'store-profile', 'as' => 'store-profile'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    register_pager("store/{slug}/edit", array('use' => "store::profile@store_profile_edit_pager", 'filter' => 'store-profile', 'as' => 'store-profile-edit'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    register_pager("store/{slug}/location", array('use' => "store::profile@store_profile_location_pager", 'filter' => 'store-profile', 'as' => 'store-profile-location'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    register_pager("store/{slug}/products", array('use' => "store::profile@store_profile_products_pager", 'filter' => 'store-profile', 'as' => 'store-profile-products'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    register_pager("store/{slug}/followers", array('use' => "store::profile@store_profile_followers_pager", 'filter' => 'store-profile', 'as' => 'store-profile-followers'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    register_pager("store/{slug}/info", array('use' => "store::profile@store_profile_info_pager", 'filter' => 'store-profile', 'as' => 'store-profile-info'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    if(plugin_loaded('blog')) {
        register_pager("store/{slug}/blogs", array('use' => "store::profile@store_blogs_profile_pager", 'filter' => 'store-profile', 'as' => 'store-profile-blogs'))->where(array('slug' => '[a-zA-Z0-9\_\-\.]+'));
    }
});

register_filter("store-profile", function($app) {
    $slug = segment(1);
    $seller = get_seller($slug,'by_store_slug');
    if(!$seller){
        $seller = get_seller(get_userid());
        if(!$seller){
            return redirect_to_pager("store_homepage");
        }
    }
    $seller = $seller[0];
    $store = $seller;
    $app->profileStore = $seller;
    $app->storeUser = find_user($seller['user_id']);
    //$app->setTitle($seller['name'])->setLayout("store::profile/layout");
    //saf modification
    $app->setTitle($seller['name'])->setLayout("store::profile/layout" , array('seller' => $seller));

    //register page profile menu
    add_menu("store-profile", array('id' => 'all-stores', 'title' => 'All Stores', 'link' => url('stores').'?lt=list'));
    add_menu("store-profile", array('id' => 'products', 'title' => lang('store::products'), 'link' => store_url('', $store)));
    add_menu("store-profile", array('id' => 'posts', 'title' => lang('store::info'), 'link' => store_url('info', $store)));
    if(plugin_loaded('blog')) {
        add_menu("store-profile", array('id' => 'blogs', 'title' => lang('store::blogs'), 'link' => store_url('blogs', $store)));
    }
    add_menu("store-profile", array('id' => 'location', 'title' => lang('store::location'), 'link' => store_url('location', $store)));
    add_menu("store-profile", array('id' => 'followers', 'title' => lang('store::followers'), 'link' => store_url('followers', $store)));

    fire_hook('store.profile.started', null, array($store));

    return true;
});

register_hook('entity.select.list', function() {
    echo view('store::select/list');
});

register_hook('entity.data', function($entity, $type, $type_id) {
    if($type == 'store') {
        $store = lpGetStoreById($type_id);
        $entity = array(
            'id' => store_url('blogs',$store),
            'name' => $store['name'],
            'avatar' => get_store_logo(200, $store)
        );
    }
    return $entity;
});





register_pager('admincp/store/imports-demos', array(
    'filter' => 'admin-auth',
    'as' => 'imports-demos',
    'use' => 'store::admincp@import_demos_pager'
));
register_pager('admincp/store/category', array(
    'filter' => 'admin-auth',
    'as' => 'admin-store-category',
    'use' => 'store::admincp@categories_pager'
));

register_pager('admincp/store/category/manage', array(
    'filter' => 'admin-auth',
    'as' => 'admin-store-categories-manager',
    'use' => 'store::admincp@categories_manager_pager'
));

register_pager('admincp/store/category/add', array(
    'filter' => 'admin-auth',
    'as' => 'admin-store-add-category',
    'use' => 'store::admincp@categories_add_pager'
));

register_pager('admincp/store/sellers', array(
    'filter' => 'admin-auth',
    'as' => 'admin-manage-sellers',
    'use' => 'store::admincp@sellers_pager'
));

register_pager('admincp/store/orders', array(
    'filter' => 'admin-auth',
    'as' => 'admin-manage-orders',
    'use' => 'store::admincp@orders_list_pager'
));

register_pager('admincp/store/order/update', array(
    'filter' => 'admin-auth',
    'as' => 'admin-update-orders',
    'use' => 'store::admincp@orders_update_pager'
));

register_pager('admincp/store/sellers/manage', array(
    'filter' => 'admin-auth',
    'as' => 'admincp-seller-manage-single',
    'use' => 'store::admincp@sellers_manage_pager'
));

register_pager('admincp/store/seller/details', array(
    'filter' => 'admin-auth',
    'as' => 'admincp-seller-details',
    'use' => 'store::admincp@seller_details_pager'
));

register_pager('admincp/store/products', array(
    'filter' => 'admin-auth',
    'as' => 'admin-manage-products',
    'use' => 'store::admincp@products_list_pager'
));

register_pager('admincp/store/withdraw/details', array(
    'filter' => 'admin-auth',
    'as' => 'admincp-seller-setup-account',
    'use' => 'store::admincp@admincp_seller_setup_account_details_pager'
));


register_pager('admincp/store/payouts', array(
    'filter' => 'admin-auth',
    'as' => 'admin-manage-payouts-request',
    'use' => 'store::admincp@payout_request_list_pager'
));

register_pager('admincp/store/withdrawal_requests/update', array(
    'filter' => 'admin-auth',
    'as' => 'admin-update-withdrawal-status',
    'use' => 'store::admincp@update_withdrawal_status_pager'
));

register_pager('admincp/store/product/manage', array(
    'filter' => 'admin-auth',
    'as' => 'admincp-product-manage-single',
    'use' => 'store::admincp@product_manage_pager'
));

register_pager('admincp/store/successful_payouts', array(
    'filter' => 'admin-auth',
    'as' => 'successful-payouts-list',
    'use' => 'store::admincp@successful_payouts_list_pager'
));

register_pager('admincp/store/coupons', array(
    'filter' => 'admin-auth',
    'as' => 'admin-coupon-list',
    'use' => 'store::admincp@coupon_list_pager'
));

register_pager('admincp/store/shipping', array(
    'filter' => 'admin-auth',
    'as' => 'admin-shipping-list',
    'use' => 'store::admincp@shipping_list_pager'
));
register_pager('admincp/store/shipping/manage', array(
    'filter' => 'admin-auth',
    'as' => 'admin-shipping-manage',
    'use' => 'store::admincp@admin_manage_shipping_pager'
));
register_pager('admincp/store/coupon/manage', array(
    'filter' => 'admin-auth',
    'as' => 'admin-coupon-manage',
    'use' => 'store::admincp@admin_manage_coupon_pager'
));

//awon eyan ti emoney
register_pager('admincp/store/charge-refund/orders', array(
    'filter' => 'admin-auth',
    'as' => 'refund-and-charge-orders',
    'use' => 'store::admincp@charge_and_refund_pager'
));


register_hook('feed-title', function ($feed) {
    if ($feed['type_id'] == "products") {
        $product_id = $feed['type_data'];
        $product = getSingleProduct($product_id);
        $store = lpGetStoreById($product['store_id']);
        if ($product)
            $str = lang('store::added-a-new');
        $str .= '<a ajax="true" href="' . url('store/product') . '/' . $product['slug'] . '">';
        $str .= ' ' . lang('store::product') . '</a>';
        $str .= ' ' . strtolower(lang("store::in")).' ';
        $str .= '<a ajax="true" href="' . url('store') . '/' . $store['slug'] . '">';
        $str .= ' ' . $store['name']. '</a>';
        echo $str;
    }
});

register_hook('order.successful',function($orderid){
    $order = getSingleOrder($orderid);
    if (!$order) return false;
    $pq = perfectUnserialize($order['product']);
    foreach($pq as $p=>$q){
        //$product = getSingleProduct($p);
        db()->query("UPDATE lp_products SET quantity = quantity - $q WHERE id='{$p}'");
        $product = getSingleProduct($p);
        $store_id  = $product['store_id'];
        $store = lpGetStoreById($store_id);
        $storeOwner = $store['user_id'];
        send_notification_privacy('notify-site-store',$storeOwner,
            'notify-seller.seller-successful-order',$orderid,array('product_name'=>$product['name'],'orderer'=>$order['user_id']));


        if(config('enable-sms-for-succesful-order-notification',1)){
            $number = $store['phone'];
            $name = get_user_name();
            $succfull = "Yes";
            sendSellerSmsNotification($number,$name,$succfull);
        }
    }

    if($order['user_id']){
        send_notification_privacy('notify-site-store',$order['user_id'],'buyer-successful-order',$orderid);
    }

   return $orderid;
});
register_hook("blog.added",function($blogId, $val){
    //print_r($val);die();
    $entity_type = "";
    $entity_id = "";
    if(isset($val['entity'])){
        $entity = $val['entity'];
        $entity = explode('-', $entity);
        if(count($entity) == 2) {
            $entity_type = $entity[0];
            $entity_id = $entity[1];

        }
    }
    if(isset($val['entity_type'])){
        $entity_type = $val['entity_type'];
        $entity_id = $val['entity_id'];
    }

    if($entity_type == 'store'){
        //then we can now send followers of this store notification
        $tags = get_store_followers($entity_id);
        foreach($tags as $tag) {
            send_notification_privacy('notify-site-tag-you', $tag, 'store.new.blog', $blogId);
        }
    }
});
register_hook("product.added", function($id, $val) {

    $product = getSingleProduct($id);
    $store_id = $product['store_id'];
    $tags = get_store_followers($store_id);
    if($product){
        $owner = $product['user_id'];
        if ($tags) {
            foreach($tags as $tag) {
              if($tag == $owner) continue;
            send_notification_privacy('notify-site-tag-you', $tag, 'store.new.product', $id);
            }
        }
    }

    //check if the edit or newly added product has parent
    //if yes update the product parent column
    if($val['category']){
        $arr = $val['category'];
        $explode_arr = explode('::',$arr);
        $category_id = $explode_arr[0];
        $category = store_get_category_by_slug($category_id);
        $parent_id = $category[0]['parent_id'];
        db()->query("UPDATE lp_products SET category_parent_id='{$parent_id}',category_id='{$category_id}' WHERE id='{$id}'");
    };

});

register_hook('product.updated',function($id,$val){
    if($val['category']){
    $arr = $val['category'];
    $explode_arr = explode('::',$arr);
    $category_id = $explode_arr[0];
    $category = store_get_category_by_slug($category_id);
        if($category){
            $parent_id = $category[0]['parent_id'];
            db()->query("UPDATE lp_products SET category_parent_id='{$parent_id}',category_id='{$category_id}' WHERE id='{$id}'");
        }else{
            db()->query("UPDATE lp_products SET category_id='{$category_id}' WHERE id='{$id}'");
        }
    }
});

register_hook("display.notification", function($notification) {

        if($notification['type'] == 'notify-seller.new-order'){
            $order = getSingleOrder($notification['type_id']);
            if($order){
                return view("store::notifications/notify_seller_new_order", array('notification' => $notification, 'order' => $order));
            }
        }elseif($notification['type'] == 'buyer-successful-order'){
            $order = getSingleOrder($notification['type_id']);
            if($order){
                return view("store::notifications/buyer-successful-order", array('notification' => $notification, 'order' => $order));
            }
        }
        elseif($notification['type'] == 'notify-seller.seller-successful-order'){
            $order = getSingleOrder($notification['type_id']);
            if($order){
                return view("store::notifications/seller-successful-order", array('notification' => $notification, 'order' => $order));
            }
        }
        else if ($notification['type'] == 'store.new.product') {
       // echo "yes";
        return view("store::notifications/subscribe", array('notification' => $notification, 'type' => 'store'));
    } else if ($notification['type'] == 'store.new.blog') {
       // echo "yes";
        return view("store::notifications/blog", array('notification' => $notification, 'type' => 'store'));
    }
});
register_hook('site-notifications-settings',function(){
    return view("store::notification-settings");
});
register_hook("feed.arrange", function ($feed) {
    if(isset($feedp['location'])){
        if ($feed['location'] == 'product') {
            $feed['location'] = '';
            $product = getSingleProduct($feed['type_data']);
            if (!$product) {
                $feedId = $feed['feed_id'];
                db()->query("DELETE FROM feeds WHERE feed_id='{$feedId}'");
                return false;
            };
            $feed['product'] = $product;
        }
    }
    return $feed;
});

register_hook("feed.content", function ($feed) {
    if ($feed['type_id'] == 'products') {
        if (isset($feed['product']) and $feed['product']) {
            $product = $feed['product'];
            echo view('store::feed_view', array('product' => $product));
        }
    }
});

register_hook('admin.statistics', function($stats) {
    $stats['sellers'] = array(
        'count' => countSellers(),
        'title' => lang('store::sellers'),
        'icon' => 'ion-android-cart',
        'link' => url_to_pager('admin-manage-sellers'),
    );
    $stats['products'] = array(
        'count' =>countProducts(),
        'title' => lang('store::products'),
        'icon' => 'ion-android-cart',
        'link' => url_to_pager("admin-manage-products"),
    );
    return $stats;
});

register_hook('product.delete', function ($id) {
    //delete products
    db()->query("DELETE FROM lp_ratings WHERE type_id='{$id}' AND type='product'");
    db()->query("DELETE FROM lp_store_order WHERE product_id='{$id}'");
    db()->query("DELETE FROM lp_wishlist WHERE product_id='{$id}'");
});

register_hook('store.delete', function ($id) {
    //delete products
    db()->query("DELETE FROM lp_products WHERE store_id='{$id}'");
    db()->query("DELETE FROM lp_categories WHERE store_id='{$id}'");
    db()->query("DELETE FROM lp_store_order WHERE store_id='{$id}'");
    db()->query("DELETE FROM lp_store_withdrawal WHERE store_id='{$id}'");
});
register_hook('admin-started', function () {
    get_menu("admin-menu", "plugins")->addMenu(lang('store::store'), "#", "admin-store-manager");
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::categories'), url_to_pager('admin-store-category'), 'categories');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::store-home-slides'), url_to_pager('admin-store-category').'?type=slides', 'categories-slides');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::sellers'), url_to_pager('admin-manage-sellers'), 'manage-sellers');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::products'), url_to_pager('admin-manage-products'), 'manage-products');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::orders'), url_to_pager('admin-manage-orders'), 'admin-orders');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::payout-eligibility'), url_to_pager('admin-manage-payouts-request'), 'admin-withdrawal-request');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::success_payouts'), url_to_pager('successful-payouts-list'), 'admin-successful-payout-list');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::coupons'), url_to_pager('admin-coupon-list'), 'admin-coupons-list');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::shipping'), url_to_pager('admin-shipping-list'), 'admin-shipping-list');
    get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('store::import'), url_to_pager('imports-demos'), 'admin-import-demo');
   //awon eyan ti emoney
   // if(config('enable-emoney-payment-on-ecommerce',false)){
        get_menu("admin-menu", "plugins")->findMenu("admin-store-manager")->addMenu(lang('emoney::refund-and-charge-order'), url_to_pager('refund-and-charge-orders'), 'charge-order');
   // }
});

register_hook("after-render-css",function($html){
    //$html .= "<link href='https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'/>";
    return $html;
});

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM lp_stores WHERE user_id='{$userid}'");
    db()->query("DELETE FROM lp_products WHERE user_id='{$userid}'");
    db()->query("DELETE FROM lp_store_order WHERE user_id='{$userid}'");
    db()->query("DELETE FROM lp_wishlist WHERE user_id='{$userid}'");
    db()->query("DELETE FROM lp_store_withdrawal WHERE user_id='{$userid}'");
});

register_hook('after-render-js', function ($html) {

    $html .= '<script src="' . img("store::js/jquery.elevatezoom.js") . '" type="text/javascript"></script>';
    //if(!plugin_loaded('schedule')){
        $html .= '<script src="' . img("store::js/jquery.datetimepicker.full.min.js") . '" type="text/javascript"></script>';
   // }
    $html .= "<script src='".img('store::js/chosen.jquery.min.js')."'></script>";
    $html .= '<script src="' . img("store::js/chosen.proto.js") . '" type="text/javascript"></script>';
    //$html .= '<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>';
    $html .="<script>
    
    function showZoom(){
         $('.chosen-select').chosen();
        if($('.zoomContainer').length){
             $('.zoomContainer').remove();
        }
    $('#main_image').elevateZoom({
          zoomType : 'lens',
          lensShape : 'round',
          lensSize: 180
    });
    
    $(document).on('click','.store_thumb_images',function(){
    var m = $('#main_image');
    var scr = $(this).attr('src');
    var ez = $('#main_image').data('elevateZoom');
    ez.swaptheimage(scr, scr);
    m.attr('src',scr)
    });
    }
    $(function(){
       showZoom();
    });
    addPageHook('showZoom');
    </script>";
    $html .= "<script type='text/javascript'>
     function showDatePicker(){
      $('#datetimepicker_dark').datetimepicker({theme:'dark'});
     }

    function showDataTable(){
        $('.storeTable').DataTable();
       }
      showDataTable();

  addPageHook('showDatePicker');
  addPageHook('showDataTable');
   $(function(){

       showDatePicker();
      showDataTable();
   });
    $.fn.digits = function(){
        return this.each(function(){
            $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,') );
        })
     }
     </script>";
    //$html .= "<script src='https://www.2checkout.com/checkout/api/2co.min.js'></script>";
    return $html;
});


/**
 * version 2.1
 */

register_hook('coupon.code.input',function(){
    echo view("store::coupons/input-code");
});

register_hook('coupon.updated',function($coupon_id,$val){
    $products = $val['products'];
    $code = $val['code'];
    db()->query("DELETE FROM lp_product_coupon WHERE coupon_id='{$coupon_id}'");
    foreach($products as $pid){
     db()->query("INSERT INTO lp_product_coupon (coupon_id,code,product_id) VALUES ('{$coupon_id}','{$code}','{$pid}')");
    }
});

register_hook('coupon.added',function($coupon_id,$val){
    $products = $val['products'];
    $code = $val['code'];
   // db()->query("DELETE FROM lp_product_coupon WHERE coupon_id='{$coupon_id}'");
    foreach($products as $pid){
        db()->query("INSERT INTO lp_product_coupon (coupon_id,code,product_id) VALUES ('{$coupon_id}','{$code}','{$pid}')");
    }
});

register_pager('coupon/verify',array(
   'use'=>'store::coupon@coupon_verify_pager',
    'as'=>'coupon_code_verify'
));

/*register_hook('order.added',function($orderId){
    getCartTotalPrice()
});*/
register_hook('product.price',function($price,$product){
    //get coupon code
    $codes = session_get('coupon_code');
    $time = time();

    if($codes){
        foreach($codes as $c){
            $q = db()->query("SELECT status,coupon_value,coupon_type,products,expiry_date FROM lp_coupons WHERE code='{$c}'");
            if($q->num_rows > 0){
                $q = $q->fetch_assoc();
                $expiry_date = $q['expiry_date'];
                if($time > $expiry_date ) return $price;
                if(!$q['status']) return $price;
                $c_type = $q['coupon_type'];
                $c_value = $q['coupon_value'];
                $products = unserialize($q['products']);
                if(in_array($product['id'],$products)){
                    if($c_type == 'percent_off'){
                       $to_deduct =  ($c_value/100)*$price;
                        return $price -= $to_deduct;
                    }else{
                        //value off
                       return $price -= $c_value;
                    }
                }
            }
        }
    }
    return $price;
});


register_hook('order.added',function($order_id){
    $order = getSingleOrder($order_id);
    $store_ids = array();
    $t = array(0);
    $product_quantity = perfectUnserialize($order['product']);
    if(is_array($product_quantity)){
        $html = '<table class="table table-bordered" style="min-width: 400px; padding: 5px; font-size: 16px; ">';
        $html .="<thead style='width:100%;'><tr><th>".lang('store::product')."</th><th>".lang("store::price")."</th><th>".lang('store::total')."</th></tr></thead>";
        foreach($product_quantity as $p=>$q){
             $product = getSingleProduct($p);
            $actual_price = getProductPrice($product,'yes');

             $html .="<tr>";
             $html .="<td>".$product['name']."</td>";
             $html .="<td>".formatPriceNumber($actual_price) .' x '.$q."</td>";
             $html .="<td>".formatPriceNumber($actual_price* $q)."</td>";
             $html .= "</tr>";
            $product_store_id = $product['store_id'];
            //add the store ID to an array
            if(!in_array($product_store_id,$store_ids)){
                $store_ids[] = $product_store_id;
            }
            $t[] = ($actual_price * $q);
        }
        $html .="<tr><td></td><td>".lang("store::sub-total")."</td>";
        $html .= "<td>".formatPriceNumber(array_sum($t))."</td>";
        $html .="</tr>";
        $html .="<tr><td></td><td>".lang("store::shipping-cost")."</td><td>".$order['shipping_price']."</td></tr>";
        $t[] = $order['shipping_price'];
        $html .="<tr><td></td><td>".lang("store::total")."</td>".formatPriceNumber(array_sum($t))."</tr>";
        $html .= "</table>";
        $billing_details = getBillingOrShippingDetails('lp_billing_details',$order['bid']);
        $mailer = mailer();
        $mailer->setAddress($billing_details['email_address'])->template("send-order-notification-to-buyer", array(
            'cart' => $html,
        ));
        $mailer->send();
    }
    return $order_id;

});

add_menu_location('store-menu', lang('store::store-menu'));
$categories = lp_store_categories('get_categories');
if ($categories){
     foreach ($categories as $c){
         $title = lang($c['title']);
         $url = url_to_pager('products-categories-home', array('slugs' => $c['id'] . '_' . toAscii(lang($c['title']))));
         $icon = null;
         $location = 'all';
        // add_available_menu($title, $url, $icon = null, $location = 'all');
     };
}

////////////////////////////////////
/// Customization start ///////////
/// //////////////////////////////

register_hook("affliate.link.field",function($link =  null){
    if(is_admin()){
        echo view("store::customization/aff-field",array('link'=>$link));
    }
});

function updateAffLink($id,$link,$store_id){
    db()->query("UPDATE lp_products SET aff_link='{$link}',store_id='{$store_id}' WHERE id='{$id}'");
}

register_hook("product.added", function($id, $val) {
    if(is_admin()) {
        $link = urlencode($val['aff_link']);
        $store_id = $val['store_id'];
        updateAffLink($id, $link, $store_id);
    }
});

register_hook('product.updated',function($id,$val){
    if(is_admin()) {
        $link = urlencode($val['aff_link']);
        $store_id = $val['store_id'];
        updateAffLink($id, $link, $store_id);
    }
});

register_pager("store/admincp/ajax/saf",array(
    'filter' => 'admin-auth',
    'as' => 'admincp-ajax-saf',
    'use' => 'store::customized@customized_ajax_pager'
));

register_hook("users.category.filter",function ($where){
    if(strpos($where,'feed') !== false){
        $where .=" AND type_id !='event-added'  AND type_id !='products'  AND type_id !='blog-added' AND type_id !='group-added' ";
    }
    //echo $where;die();
   return $where;
});
////////////////////////////////////
/// Customization End//////////////
/// //////////////////////////////






