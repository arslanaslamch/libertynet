<?php

register_asset('business::css/business.css');

register_asset('business::js/business.js');

load_functions('business::business');

// admin loaders
register_pager('admincp/business/business/pending', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-pending',
        'use' => 'business::admincp@pending_business_pager')
);

register_pager('admincp/business/business/active', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-active',
        'use' => 'business::admincp@active_business_pager')
);
register_pager("admincp/business/action/batch", array('use' => "business::admincp@business_action_batch_pager", 'filter' => 'admin-auth', 'as' => 'admin-business-batch-action'));


register_pager('admincp/business/business/claimable', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-claimable',
        'use' => 'business::admincp@claimable_business_pager')
);
register_pager('admincp/business/approve/claimable', array(
        'filter' => 'admin-auth',
        'as' => 'admin-biz-claim-approve',
        'use' => 'business::admincp@claimable_business_approve_pager')
);

register_pager('admincp/business/business/claimed', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-claimed',
        'use' => 'business::admincp@claimed_business_pager')
);

register_pager('admincp/business/business/add', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-add',
        'use' => 'business::admincp@add_admin_business_pager')
);

register_pager('admincp/business/categories', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-categories-list',
        'use' => 'business::admincp@categories_pager')
);

register_pager('admincp/business/category/add', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-category-add',
        'use' => 'business::admincp@add_category_pager')
);

register_pager('admincp/business/category/edit', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-category-edit',
        'use' => 'business::admincp@edit_category_pager')
);

register_pager('admincp/business/category/delete', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-category-delete',
        'use' => 'business::admincp@delete_category_pager')
);

register_pager('admincp/business/business/list', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-businesses-list',
        'use' => 'business::admincp@businesses_pager')
);

register_pager('admincp/business/business/list?t=p', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-businesses-list-pending',
        'use' => 'business::admincp@businesses_pager')
);

register_pager('admincp/business/business/edit', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-edit',
        'use' => 'business::admincp@admin_edit_business_pager')
);

register_pager('admincp/business/business/delete', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-delete',
        'use' => 'business::admincp@delete_business_pager')
);

register_pager('admincp/business/business/approve', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-approve',
        'use' => 'business::admincp@approve_business_pager')
);

register_pager('admincp/business/business/disapprove', array(
        'filter' => 'admin-auth',
        'as' => 'admin-business-disapprove',
        'use' => 'business::admincp@disapprove_business_pager')
);

register_pager('admincp/business/plans', array(
        'use' => 'business::admincp@plans_pager',
        'filter' => 'admin-auth',
        'as' => 'admin-business-plans')
);

register_pager('admincp/business/plan/add', array(
        'use' => 'business::admincp@add_plan_pager',
        'filter' => 'admin-auth',
        'as' => 'admin-business-add-plan')
);

register_pager('admincp/business/plan/edit', array(
        'use' => 'business::admincp@edit_plan_pager',
        'filter' => 'admin-auth',
        'as' => 'admin-business-edit-plan')
);

register_pager('admincp/business/plan/delete', array(
        'use' => 'business::admincp@delete_plan_pager',
        'filter' => 'admin-auth',
        'as' => 'admin-business-delete-plan')
);

register_hook('admin-started', function () {
    get_menu('admin-menu', 'plugins')->addMenu(lang('business::business-manager'), '#', 'admin-business-manager');
    get_menu('admin-menu', 'plugins')->findMenu('admin-business-manager')->addMenu(lang('business::categories'), url_to_pager('admin-business-categories-list'), 'categories');
    get_menu('admin-menu', 'plugins')->findMenu('admin-business-manager')->addMenu(lang('business::add-category'), url_to_pager('admin-business-category-add'), 'add-category');
    get_menu('admin-menu', 'plugins')->findMenu('admin-business-manager')->addMenu(lang('business::business'), url_to_pager('admin-business-businesses-list'), 'businesses');
    get_menu('admin-menu', 'plugins')->findMenu('admin-business-manager')->addMenu(lang('business::plans'), url_to_pager('admin-business-plans'), 'plans');
    get_menu('admin-menu', 'plugins')->findMenu('admin-business-manager')->addMenu(lang('business::add-plan'), url_to_pager('admin-business-add-plan'), 'add-plan');
});

register_pager('business/add-photo', array(
        'filter' => 'user-auth',
        'as' => 'business-add-images',
        'use' => 'business::add_images@add_images_pager')
);
// business favorite
register_pager('business/favourite', array(
        'filter' => 'user-auth',
        'as' => 'business-add-favorite',
        'use' => 'business::business@add_favorite_pager')
);

// follow business
register_pager('business/follow', array(
        'filter' => 'user-auth',
        'as' => 'business-add-follow',
        'use' => 'business::business@add_follow_pager')
);
// business show contact
register_pager('business/contact/show', array(
        'filter' => 'user-auth',
        'as' => 'business-contact-show',
        'use' => 'business::business@showcontact_pager')
);
// compare business
register_pager('business/compare', array(
        'filter' => 'user-auth',
        'as' => 'business-compare',
        'use' => 'business::business@add_compare_pager')
);

// business admin management
register_pager('business/admin/business', array(
        'filter' => 'user-auth',
        'as' => 'business-admin-management',
        'use' => 'business::business@business_admin_management_pager')
);
// business show contact
register_pager('business/contact/show', array(
        'filter' => 'user-auth',
        'as' => 'business-contact-show',
        'use' => 'business::business@showcontact_pager')
);
// compare business
register_pager('business/compare', array(
        'filter' => 'user-auth',
        'as' => 'business-compare',
        'use' => 'business::business@add_compare_pager')
);

// edit business
register_pager('business/edit-business', array(
        'filter' => 'user-auth',
        'as' => 'business-edit-business',
        'use' => 'business::edit_business@edit_business_pager')
);

register_pager('business/delete-photo', array(
        'filter' => 'user-auth',
        'as' => 'business-delete-photo',
        'use' => 'business::delete_photo@delete_photo_pager')
);

register_pager('business/payment', array(
    'use' => 'business::payment@payment_pager',
    'filter' => 'auth',
    'as' => 'business-payment'
));

register_pager('business/review/create', array(
    'use' => 'business::business@business_review_pager',
    'filter' => 'user-auth',
    'as' => 'business-reviews'
));

register_pager('business/delete-business', array(
        'filter' => 'user-auth',
        'as' => 'business-delete-business',
        'use' => 'business::delete_business@delete_business_pager')
);

register_pager("business/payment/success", array(
        'use' => 'business::payment@business_payment_success_pager',
        'filter' => 'auth',
        'as' => 'business-payment-success')
);

register_pager('business/payment/cancel', array(
        'use' => 'business::payment@business_payment_cancel_pager',
        'filter' => 'auth',
        'as' => 'business-payment-cancel')
);

register_pager('business/ajax', array(
        'as' => 'business-ajax',
        'use' => 'business::ajax@ajax_pager')
);

register_pager('business/contact/', array(
    'use' => 'business::business@business_contact_pager',
    'as' => 'business-contact',
    'filter' => 'auth'
));

// claim page loaders
register_pager('business/claim/business', array(
    'use' => 'business::business@business_claim_pager',
    'filter' => 'auth',
    'as' => 'business-claim'
));
// following business
register_pager('business/following/business', array(
    'use' => 'business::business@business_followed_pager',
    'filter' => 'auth',
    'as' => 'business-followed'
));
// favourite business
register_pager('business/favourite/business', array(
    'use' => 'business::business@business_favoured_pager',
    'filter' => 'auth',
    'as' => 'business-favoured'
));

register_pager('business/claim/business/page', array(
    'use' => 'business::business@business_claim_business_pager',
    'filter' => 'auth',
    'as' => 'business-claim-page'
));

register_pager('business/claim/business/membership', array(
    'use' => 'business::business@business_claim_membership_pager',
    'filter' => 'auth',
    'as' => 'business-claim-membership'
));

register_pager('business/members', array(
    'use' => 'business::business@business_members_pager',
    'filter' => 'auth',
    'as' => 'business-member'
));

register_pager('business/image/upload', array(
    'use' => 'business::business@business_image_upload_pager',
    'as' => 'business-image-upload',
    'filter' => 'user-auth'
));

// create business
register_pager('business/create{appends}', array(
    'use' => 'business::business@business_create_pager',
    'as' => 'business-create',
    'filter' => 'user-auth'
))->where(array('appends' => '.*'));

register_pager('businesses{appends}', array(
    'use' => 'business::business@busınesses_slug_pager',
    'as' => 'all-business',
    'filter' => 'user-auth'
))->where(array('appends' => '.*'));

register_pager('business/{slug}', array(
    'as' => 'business-page',
    'use' => 'business::business_each@business_slug_pager'))->where(array('slug' => '.*')
);

register_hook('admin.statistics', function ($stats) {
    $stats['business'] = array(
        'count' => business_num_businesses(),
        'title' => lang('business::business'),
        'icon' => 'ion-ios-briefcase',
        'link' => url_to_pager('admin-business-businesses-list'),
    );
    $stats['pendingbusinesses'] = array(
        'count' => business_num_pending_businesses(),
        'title' => lang('business::pending-businesses'),
        'icon' => 'ion-ios-briefcase',
        'link' => url('admincp/business/business/list?t=p'),
    );
    return $stats;
});

register_hook('site.statistics.excludes', function ($stats) {
    $stats[] = 'pendingbusinesses';
    return $stats;
});


// notification

register_hook('comment.add', function ($type, $typeId, $text) {
    if ($type == 'business') {
        $business = business_get_business($typeId);
        $db = db()->query("SELECT user_id FROM business_member WHERE business_id = '{$typeId}'");
        while ($rec = $db->fetch_assoc()) {
            send_notification($rec['user_id'], 'business.comment', $typeId, $business, '', $text);
        }
    }
});

register_hook('display.notification', function ($notification) {
    if ($notification['type'] == 'business.comment') {
        return view('business::notifications/business_comment', array('notification' => $notification, 'data' => unserialize($notification['data'])));
    }
});
register_hook("business.rate", function ($business_id, $rate, $rator_id) {
    $data = get_business_details($business_id);
    $data['rator_id'] = $rator_id;
    $db = db()->query("SELECT user_id FROM business_member WHERE business_id = '{$business_id}'");
    while ($rec = $db->fetch_assoc()) {
        send_notification($rec['user_id'], 'business.comment', $business_id, $data, '');
    }
});
register_hook('business_compare', function () {
    if (is_loggedIn()) echo view('business::compare');
});
add_menu_location('business-menu', 'business::business-menu');
add_available_menu('business::business', 'businesses', 'ion-ios-briefcase');

register_hook('payment.aff', function ($type, $id, $user_id = null) {
    if ($type == "business-plan") {
        business_activate($id);
    }
});

register_hook('user.delete', function ($user_id) {
    $db = db();
    $query = $db->query("SELECT id FROM business WHERE user_id = '".$user_id."'");
    while ($row = $query->fetch_row()) {
        business_delete($row[0]);
    }
    $db->query("DELETE FROM business_favourite WHERE user_id = '".$user_id."'");
    $db->query("DELETE FROM business_member WHERE user_id = '".$user_id."'");
    $db->query("DELETE FROM business_reviews WHERE user_id = '".$user_id."'");
    $db->query("DELETE FROM business_views WHERE user_id = '".$user_id."'");
    $db->query("DELETE FROM business_rating WHERE user_id = '".$user_id."'");
    return $user_id;
});