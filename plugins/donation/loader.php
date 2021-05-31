<?php
//icon
register_hook("shortcut.menu.images", function ($arr) {
    $arr['ion-social-usd'] = img("donation::images/icon.png");
    return $arr;
});


load_functions('donation::donation');

register_hook("system.started", function ($app) {
    register_asset('donation::css/jquery.datepick.css');
    //register_asset('donation::css/redmond.datepick.css');
    register_asset('donation::js/jquery.plugin.min.js');
    register_asset('donation::js/jquery.datepick.min.js');
    register_asset('donation::css/donation.css');
    register_asset('donation::js/donation.js');
    if ($app->themeType == 'frontend') {

        /*register_asset('donation::css/jquery.datepick.css');
        //register_asset('donation::css/redmond.datepick.css');
        register_asset('donation::js/jquery.plugin.min.js');
        register_asset('donation::js/jquery.datepick.min.js');
        register_asset('donation::css/donation.css');
        register_asset('donation::js/donation.js');*/
    }

    if ($app->themeType == 'backend') {
        register_asset('donation::js/admincp.js');
    }
});

register_hook("header",function(){
    $k = segment(2);
    if($k == 'donate'){
        echo '<script type="text/javascript" src="https://checkout.stripe.com/checkout.js"></script>';
    }
});


register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "donation") {
        $rid = $feed['type_data'];
        $don = Donation::getInstance();
        $raised = $don->getRaisedArr($rid);
        $did = $raised['did'];
        $donation = $don->getSingle($did);
        if($donation){
            $donation = $donation[0];
            $str =  lang('donation::donated').' '.lang("donation::to").' ';
            $str .= '<a ajax="true" href="'.url_to_pager('single_donation',array('id'=>$did)).'">';
            $str .= ' '.$donation['title'].'</a>';
            echo $str;
        }
    }
});

register_hook("feed.arrange", function($feed) {
    if ($feed['location'] == 'donation') {
        $feed['location'] = '';
        $rid = $feed['type_data'];
        $don = Donation::getInstance();
        $raised = $don->getRaisedArr($rid);
        $did = $raised['did'];
        $donation = $don->getSingle($did);
        if (!$donation) {
            $feedId = $feed['feed_id'];
            db()->query("DELETE FROM feeds WHERE feed_id='{$feedId}'");
            return false;
        };
        $feed['donation'] = $donation[0];
    }
    return $feed;
});

register_hook("feed.content", function($feed) {
    if ($feed['type_id'] == 'donation') {
        if (isset($feed['donation']) and $feed['donation']) {
            $donation = $feed['donation'];
            echo  view('donation::feed_view',array('donation' =>$donation));
        }
    }
});

register_hook("role.permissions", function ($roles) {
    $roles[] = array(
        'title' => lang('donation::donation-permissions'),
        'description' => '',
        'roles' => array(
            'can-create-new-donation' => array('title' => lang('books::can-create-new-donation'), 'value' => 1),
        )
    );
    return $roles;
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'donation.invite') {
        return view("donation::notifications/invite", array('notification' => $notification, 'type' => 'donation'));
    }
    if ($notification['type'] == 'donation.notify') {
        return view("donation::notifications/notify", array('notification' => $notification, 'type' => 'donation'));
    }
});



register_pager('donations', array(
    'use' => 'donation::donation@home_pager',
    'as' => 'donations-page'
));
register_pager('donation/ajax', array(
    'use' => 'donation::donation@ajax_pager',
    'filter' => 'auth',
    'as' => 'ajax-donation'
));

register_pager('donation/add', array(
    'use' => 'donation::donation@create_pager',
    'filter' => 'auth',
    'as' => 'create-donation'
));
register_pager('donation/donate/now', array(
    'use' => 'donation::donation@donate_now_pager',
    'as' => 'donate-now'
));
register_pager('donation/settings', array(
    'use' => 'donation::donation@settings_pager',
    'as' => 'donate-settings'
));
register_pager('donation/paypal/verify', array(
    'use' => 'donation::donation@paypal_verify_pager',
    'as' => 'donate-verify'
));
register_pager('donation/stripe/verify', array(
    'use' => 'donation::donation@verify_stripe_pager',
    'as' => 'donate-stripe-verify'
));

register_pager('donation/more-fields', array(
    'use' => 'donation::donation@more_fields_pager',
    'as' => 'more-fields',
    'filter'=>'auth'
));

register_pager('donation/promote', array(
    'use' => 'donation::donation@iframe_pager',
    'as' => 'donation-iframe'
));

register_pager('donation/{id}', array(
    'use' => 'donation::donation@single_pager',
    'as' => 'single_donation'
))->where(array('id' => '[0-9]+'));

register_pager('donation/{id}/donate', array(
    'use' => 'donation::donation@donate_me_pager',
    'as' => 'single_donation_donate'
))->where(array('id' => '[0-9]+'));



register_pager('donation/{id}/manage', array(
    'use' => 'donation::donation@manage_pager',
    'as' => 'manage_donation'
))->where(array('id' => '[0-9]+'));

add_available_menu('donation::donations', 'donations', 'ion-social-usd');

register_pager("admincp/donations", array('use' => "donation::admincp@list_pager", 'filter' => 'admin-auth', 'as' => 'admincp-donation-lists'));
register_pager("admincp/donation/add", array('use' => "donation::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-donation-add'));
register_pager("admincp/donation/settings", array('use' => "donation::admincp@admincp_settings", 'filter' => 'admin-auth', 'as' => 'admincp-settings-donation'));
register_pager("admincp/donation/manage", array('use' => "donation::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-manage-donation'));
register_pager("admincp/donation/categories", array('use' => "donation::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-donations-categories'));
register_pager("admincp/donation/categories/add", array('use' => "donation::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-donations-categories-add'));
register_pager("admincp/donation/category/manage", array('use' => "donation::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-donations-manage-category'));

register_hook("admin-started", function () {
    get_menu("admin-menu", "plugins")->addMenu(lang('donation::campaign-manager'), '#', 'admin-donations');
    get_menu("admin-menu", "plugins")->findMenu('admin-donations')->addMenu(lang('donation::lists   '), url_to_pager("admincp-donation-lists"), "manage-donations");
    //get_menu("admin-menu", "plugins")->findMenu('admin-donations')->addMenu(lang('donation::create-donation'), url_to_pager("admincp-donation-add"), "add-donation");
    get_menu("admin-menu", "plugins")->findMenu('admin-donations')->addMenu(lang('donation::manage-categories'), url_to_pager("admincp-donations-categories"), "categories");
});

register_hook('user.delete', function($userid) {
    $d = db()->query("SELECT * FROM lh_donations WHERE user_id='{$userid}'");
    while($r = $d->fetch_assoc()){
        $id = $r['id'];
        db()->query("DELETE FROM lh_donations WHERE id='{$id}'");
        db()->query("DELETE FROM lh_donation_raised WHERE did='{$id}'");
    }
    db()->query("DELETE FROM lh_donations_settings WHERE user_id='{$userid}'");
});