<?php

/** update for icon ***/
register_hook("shortcut.menu.images",function($arr){
    $arr['ion-cash'] = img("booster::images/boost.png");
    return $arr;
});

load_functions("booster::booster");
register_asset("booster::css/booster.css");
register_asset("booster::js/booster.js");

register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('booster::post-booster-permission'),
        'description' => '',
        'roles' => array(
            'can-boost-post' => array('title' => lang('booster::can-boost-post'), 'value' => 1),
        )
    );
    return $roles;
});

register_hook("feed.menu", function($feed) {
    if((is_admin() || ($feed['user_id'] == get_userid())) && ($feed['privacy'] == 1 || $feed['privacy']  == 0))  {
        if (user_has_permission('can-boost-post') && $feed['type_id'] != 'products'){
             echo  view('booster::boost_button',array('feed'=>$feed));
        }
    }
});

if (is_loggedIn()) {
    if (user_has_permission('can-boost-post')) {
        register_pager("boosts", array('use' => "booster::booster@get_my_boost_pager", 'filter' => 'user-auth', 'as' => 'manage-boost'));
        add_menu('header-account-menu', array('id' => 'manage-boost', 'title' => lang('booster::boosts'), 'link' => url_to_pager('manage-boost')));
   }
}

register_pager('admincp/booster/manage',array(
    'filter'=>'admin-auth',
    'as'=>'admin-manage-boost',
    'use'=>'booster::admincp@manage_boost_pager'
));

register_get_pager('admincp/booster/delete',array(
    'filter'=>'admin-auth',
    'as'=>'admin-delete-boost',
    'use'=>'booster::admincp@admin_delete_pb'
));

register_pager('admincp/booster/activate/{id}',array(
    'filter'=>'admin-auth',
    'as'=>'admin-activate-boost',
    'use'=>'booster::admincp@activate_pager'
))->where(array('id'=>'[0-9]+'));

register_pager("booster/clicked", array('use' => "booster::booster@booster_clicked_pager"));

register_hook('admin-started',function(){
    get_menu('admin-menu','plugins')->addMenu(lang("booster::booster"),'#','Boosted-Post');
    get_menu('admin-menu','plugins')->findMenu('Boosted-Post')->addMenu(lang('booster::boosts'),url_to_pager("admin-manage-boost"), "manage");
});


register_hook('feed.lists.inline', function($index) {
    if (config('enable-post-inline-ads', true)) {
        $index = $index + 1;
        if ($index == config('display-sponsored-post-after', 3)) {
            $array = array(1, 2);
            $key = array_rand($array, 1);
            $limit = 1;
            $boost = get_render_boosted_post('all', $limit);
            if(!empty($boost)){
                $type = $boost[0]['type'];
              //  print_r($boost); die();
                $id = $boost[0]['pb_id'];
                $type_id = $boost[0]['post_id'];
                if($type == "Post"){
                    $feed = find_feed($type_id);
                    if(empty($feed)){
                        $sql = "DELETE FROM `post_boost` WHERE `pb_id`='{$id}'";
                        db()->query($sql);
                    }else{
                        echo view('booster::block/post', array('feed' => $feed));
                    }
                }
                elseif($type == 'Listing')
                {
                    $listing = marketplace_get_listing($type_id);
                    //print_r($listing);die();
                    if(!empty($listing)){
                        echo view('booster::block/listing',array('listing'=>$listing,'id'=>$boost[0]['pb_id']));
                    }else{
                        $sql = "DELETE FROM `post_boost` WHERE `pb_id`='{$id}'";
                        db()->query($sql);
                    }
                }elseif($type == 'Product'){
                    $product = getSingleProduct($type_id);
                    if(empty($product)){
                        $sql = "DELETE FROM `post_boost` WHERE `pb_id`='{$id}'";
                        db()->query($sql);
                    }else{
                        echo view('booster::block/product', array('product' => $product,'id'=>$boost[0]['pb_id']));
                    }
                }elseif($type == 'property'){
                    $property = property_get_property($type_id);
                    if(empty($property)){
                        $sql = "DELETE FROM `post_boost` WHERE `pb_id`='{$id}'";
                        db()->query($sql);
                    }else{
                        echo view('booster::block/property', array('property' => $property,'id'=>$boost[0]['pb_id']));
                    }
                }

            }

        }
    }
});

register_hook('header', function(){

    echo view('booster::boost_modal');
});

register_pager("boost/activate/stripe/{id}", array('use' => "booster::booster@boost_stripe_process_pager", 'filter' => 'user-auth', 'as' => 'boost-stripe-process'))->where(array('id' => '[0-9]+'));
register_pager("boost/activate/paypal/{id}", array('use' => "booster::booster@boost_paypal_process_pager", 'filter' => 'user-auth', 'as' => 'boost-paypal-notify'))->where(array('id' => '[0-9]+'));
register_get_pager("boostgetpost", array('use' => 'booster::booster@post_boost_pager', 'filter' => 'auth'));
register_pager("boost/create", array('use' => 'booster::booster@post_boost_create_pager', 'as'=>'pb_create', 'filter' => 'auth'));
register_pager("boost/activate/{id}", array('use' => 'booster::booster@post_boost_activate_pager', 'as'=>'pb_activate','filter' => 'auth'))->where(array('id'=>'[0-9]+'));;
register_pager("boost/delete/{id}", array('use' => 'booster::booster@post_boost_delete_pager', 'as'=>'pb_delete','filter' => 'auth'))->where(array('id'=>'[0-9]+'));;

register_pager("booster/payment/success", array('use' => "booster::booster@booster_payment_success_pager", 'filter' => 'auth', 'as' => 'booster-payment-success'));
register_pager("booster/payment/cancel", array('use' => "booster::booster@booster_payment_cancel_pager", 'filter' => 'auth', 'as' => 'booster-payment-cancel'));

register_hook('admin.statistics', function($stats) {
    $stats['booster'] = array(
        'count' => count_all_boosted_posts(),
        'title' => lang('booster::Boosted Posts'),
        'icon' => 'ion-aperture',
        'link' => url_to_pager('admin-manage-boost'),
    );
    return $stats;
});

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM `post_boost` WHERE user_id='{$userid}'");
});

/**version 4.0 start **/
/*<?php fire_hook("booster.mkp",null, array($listing)); ?>*/
register_hook("booster.mkp",function($listing){
    echo view('booster::marketplacebtn',array('type'=>'mkp','listing'=>$listing));
});
