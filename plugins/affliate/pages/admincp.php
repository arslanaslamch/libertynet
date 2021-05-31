<?php

function requests_pager($app){
    $app->setTitle(lang("affliate::manage-requests"));
    return $app->render(view("affliate::admincp/manage-requests"));
}

function commission_earing_pager($app){
    $app->setTitle(lang("affliate::commissions"));
    $commissions = get_aff_commissions('any');
    return $app->render(view("affliate::admincp/earnings",array('commissions'=>$commissions)));
}
function ajax_pager($app){
    $action = sanitizeText(input('action'));
    switch($action){
        case 'aff-details':
            $user_id = input('id');
            $user = find_user($user_id);
            $info = is_affliate($user_id);
            $view = view("affliate::admincp/details",array('uid'=>$user_id,'user'=>$user,'info'=>$info));
            return json_encode(array('view'=>$view));
            break;
        case 'approve-affliate':
            $id = input('id');
            db()->query("UPDATE affliates SET status='1' WHERE id='{$id}'");
            $view = view("affliate::alert",array('message'=>lang("affliate::account-approved-successfully")));
            return json_encode(array('view'=>$view));
            break;
        case 'delete-affliate':
            $id = input('id');
            db()->query("DELETE FROM affliates WHERE id='{$id}'");
            $view = view("affliate::alert",array('message'=>lang("affliate::account-deleted-successfully")));
            return json_encode(array('view'=>$view));
            break;
        case 'affliate-row':
            $from = input('from_date');
            $end = input('end_date');
            $name = input('name');
            $st = input('status');
            $view = view("affliate::admincp/ajax/affliates-row",array('from'=>$from,'end'=>$end,'st'=>$st,'name'=>$name));
            return json_encode(array('view'=>$view));
            break;
        case 'reponse-earning':
            $id = input('id');
            $v = input('v');
            $msg = input('msg');
            $txt = ($v == 2) ? lang("affliate::denied") : lang("affliate::approved");
            update_aff_earning_from_admincp($id,$msg,$v);
            $view = view("affliate::alert",array('message'=>lang("affliate::submitted-successfully")));
            return json_encode(array('txt'=>$txt,'view'=>$view));
            break;
        case 'request-respond':
            $id = input('id');
            $v = input('v');
            $msg = input('msg');
            $txt = ($v == 2) ? lang("affliate::denied") : lang("affliate::approved");
            update_aff_request_from_admincp($id,$msg,$v);
            $view = view("affliate::alert",array('message'=>lang("affliate::submitted-successfully")));
            $da = date('d - m - Y', time()); //respond date
            return json_encode(array('txt'=>$txt,'view'=>$view,'da'=>$da));
            break;
    }
}

function affiliates_list_pager($app){
    $app->setTitle(lang("affliate::affilites"));
    return $app->render(view("affliate::admincp/lists"));
}

function tc_pager($app){
    $app->setTitle(lang("affliate::terms-condition"));
    $val = input('val', null, array('content'));
    if($val){
        $content = $val['content'];
        $content = lawedContent(stripslashes($content));
        $image = $val['banner'];
        $file = input_file('banner');
        if ($file) {
            $uploader = new Uploader($file);
            if ($uploader->passed()) {
                $uploader->setPath('affliates/banner/');
                $image = $uploader->resize(700, 500)->result();
            }
        }
        db()->query("UPDATE aff_tc SET content='{$content}', image='{$image}'");
    }
    $tc = get_affliate_tc(1);
    $image = get_aff_banner_img();
   return  $app->render(view("affliate::admincp/tc",array('tc'=>$tc,'image'=>$image)));
}

function commision_rules_pager($app){
    $app->setTitle(lang("affliate::commision-rules"));
    $val = input('val');
    $message = null;
    if($val){
        $ug = $val['user_group'];
        $pt = $val['payment_type'];
        delete_commission_rules($val['user_group'],$pt);
        add_commission_rules($val);
        $message = lang("affliate::settings-saved-successfully");
    }
    $commisions = get_commissions_list();
    return $app->render(view("affliate::admincp/commission",array('commisions'=>$commisions,'message'=>$message)));
}
