<?php
function my_requests_pager($app){
    $app->setTitle(lang("affliate::my-requests"));
    $info = is_affliate();
    if(!$info) return redirect_to_pager("affiliate");
    $val = input('val');
    $message = null;
    if($val){
        CSRFProtection::validate();
        validator($val, array(
            'name' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'phone' => 'required',
        ));
        if(validation_passes()){
            aff_form_handler($val,'update-info');
            $info = is_affliate();
            $message = lang('affliate::changes-saved-successfully');
        }else{
            $message = validation_first();
        }
    }
    return  $app->render(view("affliate::request",array('info'=>$info,'message'=>$message)));
}

function statistics_pager($app){
    $app->setTitle(lang("affliate::statistics"));
    return $app->render(view("affliate::statistics"));
}


function commission_tracking_pager($app){
    $app->setTitle(lang("affliate::commsion-tracking"));
    return $app->render(view("affliate::commission-tracking"));
}
function link_tracking_pager($app){
    $app->setTitle(lang("affliate::link-tracking"));
    return $app->render(view("affliate::link-tracking"));
}

function code_pager($app){
    $app->setTitle(lang("affliate::code"));
    return $app->render(view("affliate::code"));
}

function network_pager($app){
    $app->setTitle(lang("affliate::network-clients"));
    return $app->render(view("affliate::network"));
}

function commsion_pager($app){
    $app->setTitle(lang("affliate::commission-rules"));
    return $app->render(view("affliate::commision-rules"));
}

function shared_link_redirect_pager($app){
    $arr = array();
    $fullUrl = getFullUrl();
    $uri = str_replace(url(), "", $fullUrl);
    if (!empty($uri)) $arr = explode('/', $uri);

    $uid = (isset($arr[1])) ? $arr[1] : '';
    $slug = (isset($arr[2])) ? $arr[2] : '';

    if($uid && $slug){
        $secret = substr($slug,0,30);
        $url_key = str_replace($secret, "", $slug);
        //uk is url key
        $uk_arr =  get_url_with_url_key(sanitizeText($url_key));
        //homepage
        $hp = url();
        if($uk_arr){
            //let us get the url with this key
            $hp = urldecode($uk_arr['link']);
        }
        //session we wil catch after signup
        session_put('af_uid',$uid);
        session_put('af_link_come',$hp);

        return redirect(url('signup').'?redirect_to='.$hp);
    }
    return  redirect(url('signup'));
}

function links_pager($app){
    $app->setTitle(lang("affliate::links"));
    $val = input('val');
    if($val){
        $surl = $val['dl'];
        $url = url();
        if(stripos($surl, $url) !== false) {
            $str = get_aff_url_key($val['dl'],'url');
            $aff = find_af();
            $str = $aff['secret'].$str;
            $link = url().'af/'.get_userid().'/'.$str;
          return  json_encode(array('status'=>1,'str'=>$link));
        } else {
           return json_encode(array('status'=>0,'msg'=>lang("affliate::invalid-link")));
        }
    }
    return $app->render(view("affliate::links"));
}

function ajax_pager($app){
    $action = input('action');
    switch($action){
        case 'admincp-commission':
            $ug = input('k');
            $pt = input('pt');
            $ugc = get_user_group_commission($ug,$pt); //user group commsion
            //print_r($ugc);die();
            return json_encode(array('view'=>view("affliate::admincp/ajax/commission",array('ugc'=>$ugc))));
            break;
        case 'front-end':
            $v = sanitizeText(input('v'));
            return json_encode(array('view'=>view("affliate::commission-row",array('ur'=>$v))));
            break;
        case 'ajax-search-date-link':
            $from = sanitizeText(input('from_date'));
            $end = sanitizeText(input('end_date'));
            $view = view("affliate::link-tracking-row",array('from'=>$from,'end'=>$end));
            return json_encode(array('view'=>$view));
            break;
        case 'ajax-commission-tracking':
            $from = sanitizeText(input('from_date'));
            $end = sanitizeText(input('end_date'));
            $name = sanitizeText(input('name',null));
            $ptype = sanitizeText(input('ptype','any'));
            $status = sanitizeText(input('status','any'));
            $ad = sanitizeText(input('ad','n'));
            $admin = ($ad == 'y') ? 'yes' : 'no';
            $view = view("affliate::commission-tracking-row",array('from'=>$from,'end'=>$end,'name'=>$name,'status'=>$status,'ptype'=>$ptype,'admin'=>$admin));
            return json_encode(array('view'=>$view));
            break;
        case 'statistics':
            $from = sanitizeText(input('from_date'));
            $end = sanitizeText(input('end_date'));
            $status = sanitizeText(input('status','any'));
            $arr_stat = get_array_stat($from,$end,$status);
            if(!$arr_stat) return json_encode(array('status'=>0));
            $period = $arr_stat['period'];
            $r = $arr_stat['arr'];
            $f = format_stat_data($r,$period); //f is the data

            //pie
            $arr_stat = get_array_stat($from,$end,$status,'pie');
            $pd = get_pie_data_encoded($arr_stat['arr']); //pie result data
            return json_encode(array('status'=>1,'l'=>$period,'d'=>$f,'pd'=>$pd));
            break;
        case 'request-payout':
            $points = sanitizeText(input('pnt'));
            $points = (float)$points;

            $minimum = config("aff-minimum-points-request",1);
            $max = config("aff-maximum-points-request",1000);
            $available = get_aff_earnings('available',null);
            //check if points requested is greater than available, throw error
            if($points > $available){
                return json_encode(array('status'=>0,'msg'=>lang("affliate::insufficient-fund")));
            }
            //asking for small amount below minimum
            //or asking for too large value
            if($points < $minimum || $points > $max){
                return json_encode(array('status'=>0,'msg'=>lang("affliate::points-requested-range-is-").' '.$minimum.' - '.$max));
            }
            $msg = sanitizeText(input('msg'));
            $rate = config('aff-conversion-rate',1);
            $amt = aff_percent(($points / $rate), false);
            $rid = add_new_request_payout($amt,$points,$msg);
            fire_hook("affiliate.payment.request.now",$rid,array());
            $view = view("affliate::request-row",array('single'=>$rid));
            return json_encode(array('status'=>1,'msg'=>lang("affliate::request-submitted-successfully"),'view'=>$view));
            break;
        case 'cancel-request':
            $id = input('rid');
            cancel_aff_request($id);
            return json_encode(array('txt'=>lang("affliate::request-canceled-successfully")));
        case 'request-duration':
            $from = sanitizeText(input('from_date'));
            $end = sanitizeText(input('end_date'));
            $status = sanitizeText(input('status','any'));
            $admin = sanitizeText(input('ad','no'));
            $view = view("affliate::request-row", array('from' => $from, 'end' => $end,'admin'=>$admin,'status'=>$status));
            return json_encode(array('view'=>$view));
    }
}

function home_pager($app){
    $app->setTitle(lang("affliate::home"));
    $val = input('val');
    $message = null;
    if(is_affliate()){
        return $app->render(view("affliate::commision-rules"));
    }else{
        //let him register
        if($val){
            CSRFProtection::validate();
            validator($val, array(
                'name' => 'required',
                'email' => 'required|email|unique:affliates',
                'address' => 'required',
                'phone' => 'required',
            ));
            if(validation_passes()){
               if(!isset($val['terms'])){
                   $message = lang("affliate::terms-and-condition-not-accepted");
               }else{
                   aff_form_handler($val,'add-new');
                   return redirect(url_to_pager("affliate-home"));
               }
            }else{
                $message = validation_first();
            }
        }
        return $app->render(view("affliate::register",array('message'=>$message)));
    }
}