<?php
function ads_pager($app) {
    $app->setTitle(lang('ads::manage-ads'));
    return $app->render(view('ads::manage'));
}

function create_ads_pager($app) {
    $app->setTitle(lang('ads::create-ads'));
    return $app->render(view('ads::create'));
}

function process_create_ads_pager($app) {
    CSRFProtection::validate(false);
    $result = array(
        'status' => 0,
        'message' => lang('ads::ads-create-default-error')
    );
    $val = input('val');
    if (!$val) {
        return json_encode($result);
    }

    return ads_create($val, $result);
}

function edit_ads_pager($app) {
    $id = segment(2);
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) {
        return redirect_to_pager('ads-manage');
    }

    $app->setTitle(lang('ads::edit-ads'));
    return $app->render(view('ads::edit', array('ads' => $ads)));
}

function process_save_ads_pager($app) {
    $id = input('id');
    $result = array(
        'status' => 0,
        'message' => lang('ads::ads-create-default-error')
    );
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) {
        return json_encode($result);
    }

    $val = input('val');
    if (!$val) {
        return json_encode($result);
    }

    return ads_save($val, $result, $ads);
}


function activate_ads_pager($app) {
    $id = segment(2);
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) {
        return redirect_to_pager('ads-manage');
    }
    $app->setTitle(lang('ads::activate-ads'));
    $plan = get_plan($ads['plan_id']);
    if ($ads['paid'] == 0 && $plan['price'] == 0) {
        activate_ads($ads);
        return $app->render(view('ads::payment/success'));
    }
    return $app->render(view('ads::activate', array('ads' => $ads)));

}

function delete_ads_pager($app) {
    $id = segment(2);
    $ads = find_ads($id);

    //$plan = get_plan($ads['plan_id']);
    if ((!$ads or $ads['user_id'] != get_userid()) and !is_admin()) {
        return redirect_to_pager('ads-manage');
    }
    delete_ads($id);

    if (input('admin')) {
        return redirect_back();
    }
    return redirect_to_pager('ads-manage');
}

function ads_clicked_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $ads = find_ads($id);
    if ($ads and is_loggedIn()) {
        $userClicks = get_privacy('ads-clicks', array());
        if (!in_array($ads['ads_id'], $userClicks)) {
            $clicks = $ads['clicks_stats'] + 1;
            $adsId = $ads['ads_id'];
            $quantity = $ads['quantity'];
            if ($ads['plan_type'] == 1) {
                $quantity -= config('ads-quantity-deduction-per-click', 5);
            }
            db()->query("UPDATE ads SET clicks_stats='{$clicks}',quantity='{$quantity}' WHERE ads_id='{$adsId}'");

            $userClicks[] = $ads['ads_id'];
            save_privacy_settings(array('ads-clicks' => $userClicks));
        }
    }
}

function load_plans_pager($app) {
    CSRFProtection::validate(false);
    $type = input('type');
    $result = array(
        'content' => '',
        'description' => ''
    );

    $plans = get_ads_plans($type);
    $content = '';
    foreach ($plans as $plan) {
        if (!$result['description']) {
            $result['description'] = lang($plan['description']);
        }
        $content .= '<option value="'.$plan['id'].'">'.lang($plan['name']).'</option>';
    }
    $result['content'] = $content;

    return json_encode($result);
}

function get_video_ads_pager() {
    $sql = "SELECT video FROM ads WHERE ads_class = 'video' ORDER BY rand() limit 1";
    $q = db()->query($sql);
    $result = fetch_all($q);
    return json_encode($result);
}

function load_plans_description_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $plan = get_plan($id);
    if ($plan) return lang($plan['description']);
}

function load_plans_page_pager($app) {
    CSRFProtection::validate(false);
    $id = input('id');
    $page = find_page($id);
    $result = array('title' => '', 'description' => '', 'link' => '', 'avatar' => '');
    if ($page) {
        $result['title'] = $page['page_title'];
        $result['description'] = $page['page_desc'];
        $result['link'] = page_url(null, $page);
        $result['avatar'] = get_page_logo(600, $page);
    }

    return json_encode($result);
}

function ads_payment_success_pager($app) {
    //CSRFProtection::validate();
    $id = input('id');
    $ads = find_ads($id);
    if (!$ads or $ads['user_id'] != get_userid()) {
        return redirect_to_pager('ads-manage');
    }
    activate_ads($ads);
    return $app->render(view('ads::payment/success', array('id' => $id)));
}

function ads_payment_cancel_pager($app) {
    $app->setTitle(lang('ads::ads-canceled'));
    return $app->render(view('ads::payment/cancel'));
}

function get_overlay_ads_pager() {
    $overlay = get_render_ads($type = 'all', $limit = '1', $class = 'overlay');
    return json_encode($overlay);
}