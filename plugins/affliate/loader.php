<?php

load_functions('affliate::affliate');

register_asset('affliate::css/affliate.css');
register_asset('affliate::css/daterangepicker.css');
register_asset('affliate::js/moment.js');
register_asset('affliate::js/Chart.bundle.min.js');
register_asset('affliate::js/daterangepicker.js');
register_asset('affliate::js/affliate.js');
register_hook('system.started', function($app) {
    if ($app->themeType == 'backend') {
        register_asset('affliate::js/admincp.js');
    }
});

register_pager('affiliate', array(
    'use' => 'affliate::affliate@home_pager',
    'as' => 'affliate-home',
    'filter'=>'auth'
));

register_pager('affiliate/links', array(
    'use' => 'affliate::affliate@links_pager',
    'as' => 'affliate-links',
    'filter'=>'auth'
));

register_pager('affiliate/network', array(
    'use' => 'affliate::affliate@network_pager',
    'as' => 'affliate-network',
    'filter'=>'auth'
));
register_pager('affiliate/codes', array(
    'use' => 'affliate::affliate@code_pager',
    'as' => 'affliate-code',
    'filter'=>'auth'
));

register_pager('affiliate/link-tracking', array(
    'use' => 'affliate::affliate@link_tracking_pager',
    'as' => 'affliate-link-tracking',
    'filter'=>'auth'
));

register_pager('affiliate/commission-tracking', array(
    'use' => 'affliate::affliate@commission_tracking_pager',
    'as' => 'affliate-commission-tracking',
    'filter'=>'auth'
));
register_pager('affiliate/statistics', array(
    'use' => 'affliate::affliate@statistics_pager',
    'as' => 'affliate-stats',
    'filter'=>'auth'
));

register_pager('affiliate/commission', array(
    'use' => 'affliate::affliate@commsion_pager',
    'as' => 'commision-rules',
    'filter'=>'auth'
));
register_pager('affiliate/my-requests', array(
    'use' => 'affliate::affliate@my_requests_pager',
    'as' => 'aff-requests',
    'filter'=>'auth'
));

register_pager('af/{id}/{slug}', array(
    'use' => 'affliate::affliate@shared_link_redirect_pager',
    'as' => 'redirect-shared-link'
))->where(array('id' => '[0-9]+','slug'=>'[a-zA-Z0-9\_\-]+'));

register_pager('admincp/affiliates', array(
    'use' => 'affliate::admincp@affiliates_list_pager',
    'as' => 'admincp-affiliates',
    'filter'=>'admin-auth'
));

register_pager('admincp/ajax', array(
    'use' => 'affliate::admincp@ajax_pager',
    'as' => 'admincp-affiliates-ajax',
    'filter'=>'admin-auth'
));

register_pager('admincp/commission-earnings', array(
    'use' => 'affliate::admincp@commission_earing_pager',
    'as' => 'admincp-affiliates-com-earnings',
    'filter'=>'admin-auth'
));

register_pager('admincp/affiliate/commision-rules', array(
    'use' => 'affliate::admincp@commision_rules_pager',
    'as' => 'admincp-commission-rules',
    'filter'=>'admin-auth'
));

register_pager('admincp/affiliate/terms-condition', array(
    'use' => 'affliate::admincp@tc_pager',
    'as' => 'aff-terms-condition',
    'filter'=>'admin-auth'
));
register_pager('admincp/affiliate/requests', array(
    'use' => 'affliate::admincp@requests_pager',
    'as' => 'admincp-aff-requests',
    'filter'=>'admin-auth'
));

register_pager('affliate/ajax', array(
    'use' => 'affliate::affliate@ajax_pager',
    'as' => 'affliate-ajax',
    'filter'=>'auth'
));

//add_available_menu('affliate::affiliates','affiliate','ion-android-add-circle');
register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang('affliate::affliate-manager'), '#', 'affliate-menu');
    get_menu("admin-menu", "plugins")->findMenu('affliate-menu')->addMenu(lang('affliate::commission-rules'), url_to_pager("admincp-commission-rules"), "manage");
    get_menu("admin-menu", "plugins")->findMenu('affliate-menu')->addMenu(lang('affliate::affiliates'), url_to_pager("admincp-affiliates"), "lists");
    get_menu("admin-menu", "plugins")->findMenu('affliate-menu')->addMenu(lang('affliate::commission-earnings'), url_to_pager("admincp-affiliates-com-earnings"), "cm");
    get_menu("admin-menu", "plugins")->findMenu('affliate-menu')->addMenu(lang('affliate::manage-requests'), url_to_pager("admincp-aff-requests"), "mr");
    get_menu("admin-menu", "plugins")->findMenu('affliate-menu')->addMenu(lang('affliate::aff-terms-condition'), url_to_pager("aff-terms-condition"), "tcd");
    //get_menu("admin-menu", "plugins")->findMenu('affliate-menu')->addMenu(lang('blog::manage-categories'), url_to_pager("admincp-blog-categories"), "categories");
});


register_hook("creditgift.addcredit.signup",function($userid){
    if(is_array($userid)){
        $userid = $userid[0];
    }
    $p_id = session_get('af_uid');
    $link = session_get('af_link_come');
    if($p_id && $link){
        //this guy was sent here
        $user = find_user($userid);
        $ac = 0; //activation
        if(($user['active'] == 1) && ($user['activated'] == 1)){
            $ac = 1;
        }
        add_new_network_client($ac,$link,$p_id,$userid);
    }
    return $userid;
});

register_hook("after-render-js",function($html){
   $html .="<script>
   function render_aff_js(){
    $('#startDate').daterangepicker({
      singleDatePicker: true,
      startDate: moment().subtract(6, 'days')
    });

    $('#endDate').daterangepicker({
      singleDatePicker: true,
      startDate: moment()
    });
   }
   $(function(){
       render_aff_js();
   });
   addPageHook('render_aff_js')
   </script>";
   return $html;
});

register_hook("user.activated",function($uid){
   db()->query("UPDATE aff_network SET status='1' WHERE ref_id='{$uid}'");
});

//membership subscription and ads
register_hook("payment.aff",function($t,$id){
    if(has_aff_parent(get_userid())){
        if($t == 'ads'){
            //ads sent here is ads id
            aff_earning_insert('ads-subscription',array('id'=>$id)) ;
        }
        if($t == 'membership'){
            //the one sent here is plan id
            aff_earning_insert('membership-subscription',array('id'=>$id)) ;
        }
        if($t == 'booster'){
            //the one sent here is plan id
            aff_earning_insert('booster-subscription',array('id'=>$id)) ;
        }
        if($t == 'spotlight'){
            //the one sent here is the plan id
            aff_earning_insert('spotlight',array('id'=>$id)) ;
        }

        if($t == 'store'){
            //the one sent here is the order_id
            aff_earning_insert('product-purchase',array('id'=>$id)) ;
        }
        if($t == 'property'){
            //the one sent here is the plan_id
            aff_earning_insert('property-activation',array('id'=>$id)) ;
        }

    }
});

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM affliates WHERE user_id='{$userid}'");
    db()->query("DELETE FROM aff_earnings WHERE user_id='{$userid}'");
    db()->query("DELETE FROM aff_gainers WHERE user_id='{$userid}'");
    db()->query("DELETE FROM aff_requests WHERE user_id='{$userid}'");
    db()->query("DELETE FROM aff_network WHERE ref_id='{$userid}' OR parent_id='{$userid}'");
});

add_available_menu('affliate::affiliate', 'affiliate', 'ion-ios-people');

register_hook('admin.statistics', function($stats) {
    $stats['affliate'] = array(
        'count' => count_stat_affliate(),
        'title' => lang('affliate::affiliates'),
        'icon' => 'ion-ios-people',
        'link' => url_to_pager('admincp-affiliates'),
    );
    return $stats;
});
