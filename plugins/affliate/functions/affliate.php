<?php

function aff_is_pending_approval($uid = null){
    $userid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM affliates WHERE user_id='{$userid}'");
    if($q->num_rows > 0){
        $r = $q->fetch_assoc();
        if(!$r['status']){
            return true;
        }
    }
    return false;
}

function count_stat_affliate(){
    $q = db()->query("SELECT * FROM affliates");
    return $q->num_rows;
}

function get_statistics_np($type){
    $id = get_userid();
    $result = array(
        'n'=>0,
        'p'=>0
    );
    //n is number, p is points
    switch($type){
        case 'overall':
            $q = db()->query("SELECT SUM(com_points) AS p,COUNT(id) AS n FROM aff_earnings WHERE user_id='{$id}'");
            break;
        case 'approved':
            $q = db()->query("SELECT SUM(com_points) AS p,COUNT(id) AS n FROM aff_earnings WHERE user_id='{$id}' AND status='3'");
            break;
        case 'waiting':
            $q = db()->query("SELECT SUM(com_points) AS p,COUNT(id) AS n FROM aff_earnings WHERE user_id='{$id}' AND status='1'");
            break;
        case 'denied':
            $q = db()->query("SELECT SUM(com_points) AS p,COUNT(id) AS n FROM aff_earnings WHERE user_id='{$id}' AND status='2'");
            break;
    }
    if($q->num_rows > 0){
        $r = $q->fetch_assoc();
        $result['n'] = $r['n'];
        $result['p'] = ($r['p']) ? $r['p'] : '0';
    }
    return $result;
}

function cancel_aff_request($id){
    $uid = get_userid();
    db()->query("DELETE FROM aff_requests WHERE id='{$id}' AND user_id='{$uid}'");
}

function update_aff_earning_from_admincp($id,$msg,$v){
    db()->query("UPDATE aff_earnings SET status='{$v}',reason='{$msg}' WHERE id='{$id}'");
}
function update_aff_request_from_admincp($id,$msg,$v){
    $time = time();
    db()->query("UPDATE aff_requests SET status='{$v}',response_message='{$msg}',response_time='{$time}' WHERE id='{$id}'");
}

function get_aff_commissions($t){
    switch($t){
        case 'any':
            return paginate("SELECT * FROM aff_earnings",'all');
            break;
    }
}

function get_all_affliates($from,$end,$name,$st){
    $fe = unix_aff_time($from,$end);
    $f = $fe['f'];
    $e = $fe['e'];
    $sql = "SELECT *  FROM affliates WHERE time >= '{$f}' AND time <= '{$e}'";
    if($name){
        $sql .=" AND name LIKE '%{$name}' ";
    }

    if($st != 'any'){
        $sql .=" AND status='{$st}' ";
    }
    //echo $sql;die();
    return paginate($sql,'all');

}

function get_single_aff_request($id){
    $arr = array();
    $q = db()->query("SELECT * FROM aff_requests WHERE id='{$id}'");
    if($q->num_rows > 0){
        return $q->fetch_assoc();
    }
    return $arr;
}

function add_new_request_payout($amt,$points,$msg){
    $userid = get_userid();
    $time = time();
    db()->query("INSERT INTO aff_requests (user_id,status,request_time,request_message,amount,points)
    VALUES('{$userid}','1','{$time}','{$msg}','{$amt}','{$points}')");
    return db()->insert_id;
}

function aff_update_requests($rid,$t){
    switch($t){
        case 'approved':
            db()->query("UPDATE aff_requests SET status=3 WHERE id='{$rid}'");
            break;
    }
}

function get_aff_earnings($type,$uid= null){
    $id = ($uid) ? $uid : get_userid();
    switch($type){
        case 'all':
            //status 3 means the earned amount has been approved
            $q = db()->query("SELECT SUM(com_points) AS p FROM aff_earnings WHERE user_id='{$id}' AND status='3'");
            if($q->num_rows > 0){
                return $q->fetch_assoc()['p'];
            }
            return 0;
            break;
        case 'pending':
            //request that has nott been approved
            //1 = pending,2=denied,3 =  approved
            $q = db()->query("SELECT SUM(points) AS p FROM aff_requests WHERE user_id='{$id}' AND status='1'");
            if($q->num_rows > 0){
                return $q->fetch_assoc()['p'];
            }
            return 0;
            break;
        case 'received':
            //request that has nott been approved
            //1 = pending,2=denied,3 =  approved
            $q = db()->query("SELECT SUM(points) AS p FROM aff_requests WHERE user_id='{$id}' AND status='3'");
            if($q->num_rows > 0){
                return $q->fetch_assoc()['p'];
            }
            return 0;
            break;
        case 'denied':
            //request that has nott been approved
            //1 = pending,2=denied,3 =  approved
            $q = db()->query("SELECT SUM(points) AS p FROM aff_requests WHERE user_id='{$id}' AND status='2'");
            if($q->num_rows > 0){
                return $q->fetch_assoc()['p'];
            }
            return 0;
            break;
        case 'available':
            //available points consist of all the points earned minus pending minus recieved - minus denied
            $total = get_aff_earnings('all',null);
            $pending = get_aff_earnings('pending',null);
            //$denied = get_aff_earnings('denied',null); //denied should be ignored
            //because it is not recieved
            $r = get_aff_earnings('received',null);
            $ans = ($total) - ($pending + $r);
            return $ans;
            break;
    }
}

function get_aff_requests($t,$status='any', $f, $e){
    $arr = array();
    switch($t){
        case 'mine':
            $uid = get_userid();
            $q = "SELECT * FROM aff_requests WHERE request_time >= '{$f}' AND request_time <= '{$e}' AND user_id='{$uid}'";
            return paginate($q,'all');
            break;
        case 'admin':
            $q = "SELECT * FROM aff_requests WHERE request_time >= '{$f}' AND request_time <= '{$e}'";
            if($status != 'any'){
                $q = $q." AND status='{$status}'";
            }
            return paginate($q,'all');
            break;
    }
    return $arr;
}

function get_color_encoded(){
    $arr = array();
    $index = 0;
    foreach(get_commissions_list() as $k=>$v){
        $arr[] = aff_colors($index);
        $index++;
    }
    return json_encode($arr);
}

function get_pie_data_encoded($arr){
    $pd = array();
    foreach(get_commissions_list() as $k=>$v){
        $v_i = toAscii($v);
        $v =  (isset($arr[$v_i])) ? $arr[$v_i] : 0;
        $pd[] = $v;
    }
    return json_encode($pd);
}

function get_lable_encoded(){
    $arr = array();
    foreach(get_commissions_list() as $k=>$v){
        $arr[] = $v;
    }
    return json_encode($arr);

}

function aff_colors($index){
    $arr =  array(
    '0' => 'rgb(255, 99, 132)',
    '1'=> 'rgb(255, 159, 64)',
    '2'=> 'rgb(255, 205, 86)',
    '3'=> 'rgb(75, 192, 192)',
    '4'=> 'rgb(54, 162, 235)',
    '5'=> 'rgb(153, 102, 255)',
    '6'=> 'rgb(231,233,237)'
    );
    return $arr[$index];
}

function format_stat_data($result,$period){
    $apt =  get_commissions_list(); //all payment type
    //print_r($result);die();
    $arr = array();
    $index = 0;
    foreach($apt as $pt){
        $pt_asic = toAscii($pt);
        //if the key is in athe array
        $data = array();
        if(isset($result[$pt_asic])){
            $dp = $result[$pt_asic];  //date and points
            //print_r($dp);die();
            foreach($period as $per=>$k){
                $data[] = isset($dp[$k]) ? ($dp[$k]) : 0;
            }
        }else{
            //for values not yet earned
            foreach($period as $per=>$k){
                $data[] =  0;
            }
        }
        $arr[] = array(
            'label' => $pt,
            'backgroundColor' => aff_colors($index),
            'borderColor' =>aff_colors($index),
            'data' => $data
        );
        $index++;
    }
    return $arr;
    //echo '<pre>', print_r($arr), '</pre>';die();
}

function get_array_stat($from,$end,$status= 'any',$type = 'line'){
    $arr = array();
    $fe = unix_aff_time($from,$end);
    $f = $fe['f'];
    $e = $fe['e'];
    if($e < $f) return false;
    $uid = get_userid();
    $sql = "SELECT * FROM aff_earnings afe WHERE user_id='{$uid}' AND time >= '{$f}' AND time <= '{$e}'";
    if($status != 'any'){
        $sql .= " AND status='{$status}'";
    }
    $q = db()->query($sql);

    if($q->num_rows > 0){
         while($r = $q->fetch_assoc()){
             if($type == 'pie'){
                 if(isset($arr[$r['ptype']])){
                     $exv = $arr[$r['ptype']];
                     $arr[$r['ptype']] = ($exv + $r['com_points']);
                 }else{
                     $arr[$r['ptype']] = $r['com_points'];
                 }
                 continue;
             }
             $d = date('m/d/Y',$r['time']);
             if(isset($arr[$r['ptype']][$d])){
                 $exv = $arr[$r['ptype']][$d];
                 $arr[$r['ptype']][$d] = ($exv + $r['com_points']);
                 continue;
             }
             $arr[$r['ptype']][$d] = $r['com_points'];
         }
    }
    if($type == 'pie'){
        return array('period'=>0,'arr'=>$arr);
    }

   // echo '<pre>',print_r($arr),'</pre>';die();
    //forach time set
    // get each payment type for each time and the value in points earned
    $period = getDateRange($from, $end);
    if(!$period) return false;
    return array('period'=>$period,'arr'=>$arr);
    //echo '<pre>',print_r($arr),'</pre>';;

    //foreach
}

function getDateRange($startDate, $endDate, $format="m/d/Y")
{
    //Create output variable
    $datesArray = array();
    //Calculate number of days in the range
    $total_days = round(abs(strtotime($endDate) - strtotime($startDate)) / 86400, 0) + 1;
    if($total_days<0) { return false; }
    //Populate array of weekdays and counts
    for($day=0; $day<$total_days; $day++)
    {
        $datesArray[] = date($format, strtotime("{$startDate} + {$day} days"));
    }
    //Return results array
    return $datesArray;
}


function get_commission_tracking($f, $e, $name= null, $ptype = 'any', $status = 'any' ,$mtype = 'mine'){
    $sql = "SELECT * FROM aff_earnings afe WHERE afe.time >= '{$f}' AND afe.time <= '{$e}'";
    if($name){
        $sql = "SELECT afe.id,afe.user_id,afe.earned_from,afe.time,afe.ptype,afe.amount,afe.percent,afe.com_amt,
        afe.com_points,afe.reason,afe.status FROM aff_earnings afe LEFT JOIN users u ON afe.earned_from = u.id ";
        $sql .= " WHERE u.first_name  LIKE '%{$name}%' OR u.last_name LIKE '%{$name}%'";
    }
    if($ptype != "any"){
        $sql .= " AND afe.ptype = '{$ptype}'";
    }
    if($status != 'any'){
        $sql .=" AND afe.status = '{$status}'";
    }
    if($mtype =='mine'){
        $uid = get_userid();
        $sql .= " AND user_id='{$uid}'";
    }
    //echo $sql; die();
    return paginate($sql,'all');
}

function get_gainers_on_me($uid = null, $ptype)
{
    $arr = array();
    $me = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM aff_gainers WHERE reffered='{$me}' AND ptype = '{$ptype}'");
    while ($r = $q->fetch_assoc()) {
        $arr[] = $r;
    }
    return $arr;
}

function aff_credit($amt,$tp)
{
    $uid = get_userid();
    $gainers = get_gainers_on_me(get_userid(),$tp);
    $time = time();
    foreach ($gainers as $k) {
        $rate = config('aff-conversion-rate', 1);
        $benefiter = $k['user_id'];
        $p = $k['percent']; //each level percentage earning
        $com_points = ($p / 100) * $amt; //points earned on each sale
        $com_amt = aff_percent(($com_points / $rate), false); //convert to real cash
        $status = 1;
        if(config('auto-approved-commission-earnings',0)){
            $status = 3;
        }
        db()->query("INSERT INTO aff_earnings(earned_from,user_id,time,ptype,amount,percent,com_amt,com_points,status)
        VALUES('{$uid}','{$benefiter}','{$time}','{$tp}','{$amt}','{$p}','{$com_amt}','{$com_points}','1')");
    }
}

function aff_earning_insert($type, $others = array())
{
    switch ($type) {
        case 'membership-subscription':
            $id = $others['id']; //this is set from the loader
            $plan = get_membership_plan($id);
            $amt = $plan['price'];
            aff_credit($amt,$type);
            return true;
            break;
        case 'ads-subscription':
            $id = $others['id']; //this is set from the loader
            $ads = find_ads($id);
            $plan = get_plan($ads['plan_id']);
            $amt = $plan['price'];
            aff_credit($amt,$type);
            break;
        case 'booster-subscription':
            $id = $others['id']; //this is set from the loader
            $bp = find_pb($id);
            $plan = get_plan($bp['plan_id']);
            $amt = $plan['price'];
            aff_credit($amt,$type);
            break;
        case 'spotlight':
            $id = $others['id']; //this is set from the loader
            $plan = get_spotlight_plan($id);
            $amt = $plan['price'];
            aff_credit($amt,$type);
            break;
        case 'product-purchase':
            $order_id = $others['id']; //this is set from the loader
            $order = getSingleOrder($order_id);
            $amt = ($order['total_price'] + $order['shipping_price']);
            aff_credit($amt,$type);
            break;
        case 'property-activation':
            $id = $others['id']; //this is set from the loader
            $plan = get_property_plan($id);
            $amt = $plan['price'];
            aff_credit($amt,$type);
            break;
    }
}

function unix_aff_time($from, $end)
{
    $from = explode('/', trim($from));
    $f = @mktime(0, 0, 0, $from[0], $from[1], $from[2]);
    $end = explode('/', trim($end));
    $e = @mktime(23, 59, 0, $end[0], $end[1], $end[2]);
    return array('f' => $f, 'e' => $e);
}

function get_link_tracking($f, $e)
{
    $uid = get_userid();
    return paginate("SELECT * FROM aff_network WHERE parent_id='{$uid}' AND time >= '{$f}' AND time <='{$e}'", 10);
}

function get_aff_banner_img()
{
    $tc = get_affliate_tc(true);
    if ($tc['image']) {
        return url_img($tc['image']);
    }
    return img("affliate::images/bn.png");
}

function get_my_children($pid)
{
    $arr = array();
    $q = db()->query("SELECT * FROM aff_network WHERE parent_id='{$pid}'");
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            $arr[] = $r['ref_id'];
        }
    }
    return $arr;
}

function get_my_downline()
{
    $uid = get_userid();
    $arr = array();
    $q = db()->query("SELECT * FROM aff_network WHERE parent_id='{$uid}'");
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            //step 1
            $reffered = $r['ref_id'];
            $arr[] = $reffered;
        }
    }
    return $arr;

}

function count_my_downline($id = null)
{
    $uid = ($id) ? $id : get_userid();
    $count = db()->query("SELECT * FROM aff_gainers WHERE user_id='{$uid}' GROUP BY reffered");
    return $count->num_rows;
}

function has_aff_parent($user_id = null)
{
    $uid = ($user_id) ? $user_id : get_userid();
    $q = db()->query("SELECT * FROM aff_network WHERE ref_id='{$uid}'");
    if ($q->num_rows > 0) {
        $r = $q->fetch_assoc();
        return $r['parent_id'];
    }
    return 0;
}

function get_ancestors_arr($parent)
{
    $max_level = config('number-of-commision-levels', 5);
    $arr = array();
    $arr['user_1'] = $parent;
    for ($i = 2; $i <= $max_level; $i++) {
        $lp = ($i - 1); //this is the last parent
        //check if the last parent has a parent;
        $parent = has_aff_parent($arr['user_' . $lp]);
        $arr['user_' . $i] = $parent;
    }
    return $arr;
}

function insert_gainers($p_id, $uid)
{
    $max_level = config('number-of-commision-levels', 5);
    //$max_users = config("number-of-users-in-each-level",4);
    $ancestors = get_ancestors_arr($p_id);
    for ($i = 1; $i <= $max_level; $i++) {
        //level 1 earner is the person that reffered you.
        //level 2 earner is the person that reffered your parent
        //level 3 earner is the person that reffered your grand parent
        //level 4 earner is the person that reffered your great grand parent e.t.c
        //note some people will have all of the above or either of the above
        //also note that percentage earning on payment-type is different for each level.
        //make a note of that.
        $level = $i;
        $user_id = $ancestors['user_' . $i];

        //let us skip ancestors of zero
        if (!$user_id) continue;
        $time = time();
        if ($level == 1) {
            //note that the percentage earned depend on the role of the earner
            $user = find_user($user_id);
            $role = $user['role'];
            $cls = get_commissions_list();
            foreach ($cls as $k => $txt) {
                $ptype = toAscii($txt);
                $commsion = get_user_group_commission($role, $ptype);
                $amt = (isset($commsion['level_' . $i])) ? $commsion['level_' . $i] : 0;
                if ($user_id == 0) {
                    $amt = 0;
                }
                db()->query("INSERT INTO aff_gainers(user_id,level,percent,time,reffered,ptype)
                 VALUES('{$user_id}','{$level}','{$amt}','{$time}','{$uid}','{$ptype}')");
            }

        }

    }
}

/**
 * @param $ac the account status
 * @param $link the link he signed up through
 * @param $p_id is the person we bring into the newtwork
 */
function add_new_network_client($ac, $link, $p_id, $userid)
{
    $link = urlencode($link);
    $time = time();
    db()->query("INSERT INTO aff_network (ref_id,parent_id,time,status,link) VALUES('{$userid}','{$p_id}','{$time}','{$ac}','{$link}')");
    fire_hook("new.affliate.client.added",null,array($p_id,$userid,$ac,$link));
    insert_gainers($p_id, $userid);
}

if (!function_exists('random_text')) {
    function random_text($type = 'alnum', $length = 8)
    {
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string)$type;
                break;
        }


        $crypto_rand_secure = function ($min, $max) {
            $range = $max - $min;
            if ($range < 0) return $min; // not so random...
            $log = log($range, 2);
            $bytes = (int)($log / 8) + 1; // length in bytes
            $bits = (int)$log + 1; // length in bits
            $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);
            return $min + $rnd;
        };

        $token = "";
        $max = strlen($pool);
        for ($i = 0; $i < $length; $i++) {
            $token .= $pool[$crypto_rand_secure(0, $max)];
        }
        return $token;
    }
}

function get_url_with_url_key($key)
{
    $q = db()->query("SELECT * FROM aff_url_links WHERE key_slug='{$key}'");
    if ($q->num_rows > 0) return $q->fetch_assoc();
    return false;
}

/**
 * function that insert new link
 * @param $url
 * @return string
 */
function insert_new_aff_link($url)
{
    $url = urlencode($url);
    $secret = getUniqueSecret(10);
    $q = db()->query("INSERT INTO aff_url_links(key_slug,link) VALUES('{$secret}','{$url}')");
    return $secret;
}

/**
 * check if the url already existed
 * @param $url
 * @return bool
 */
function url_af_exists($url)
{
    $url = urlencode($url);
    $q = db()->query("SELECT * FROM aff_url_links WHERE link='{$url}'");
    if ($q->num_rows > 0) {
        return $q->fetch_assoc();
    }
    return false;
}

/**
 * @param $key
 * @return string
 */
function get_aff_url_key($key, $by = 'key')
{
    if ($by == 'key') {
        $url = url($key);
    } else {
        //if it is not equal to key
        $url = $key;
    }
    $exists = url_af_exists($url);
    if ($exists) {
        return $exists['key_slug'];
    }
    $result = insert_new_aff_link($url);
    return $result;
}

function find_af($uid = null)
{
    $id = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM affliates WHERE user_id='{$id}'");
    if ($q->num_rows > 0) {
        return $q->fetch_assoc();
    }
    return false;
}

function get_url_affliate_link($append = null)
{
    $aff = find_af();
    $secret = $aff['secret'];
    $link = url() . 'af/' . get_userid() . '/' . $secret;
    switch ($append) {
        case 'homepage':
            return $link;
            break;
        case 'people':
            $str = get_aff_url_key('people');
            return $link . $str;
            break;
        default:
            return $link;
            break;
    }
}

function suggested_links()
{
    return array(
        lang('affliate::home-page') => get_url_affliate_link('hompage'),
        lang('affliate::people') => get_url_affliate_link('people')
    );
}

function aff_percent($numb, $append = true)
{
    $f = number_format((float)$numb, 2, '.', '');

    if ($append) {
        $f = $f . '%';
    }
    return $f;
}

function get_affliate_tc($all = false)
{
    $q = db()->query("SELECT * FROM aff_tc WHERE id='1'");
    if ($all) {
        return $q->fetch_assoc();
    }
    return $q->fetch_assoc()['content'];
}

function getUniqueSecret($length = 30)
{
    $str = random_text('alnum', $length);
    return $str;
}

function aff_form_handler($val, $action)
{
    switch ($action) {
        case 'add-new':
            $name = sanitizeText($val['name']);
            $address = sanitizeText($val['address']);
            $email = sanitizeText($val['email']);
            $phone = sanitizeText($val['phone']);
            $userid = get_userid();
            $time = time();
            $secret = getUniqueSecret();
            $status = 0;
            if(config("auto-approved-affliate-account",1)){
                $status = 1;
            }
            db()->query("INSERT INTO affliates (name,email,address,phone,user_id,time,secret,status)
            VALUES ('{$name}','{$email}','{$address}','{$phone}','{$userid}','{$time}','{$secret}','{$status}')");
            $id = db()->insert_id;
            //update to prevent collision
            return $id;
            break;
        case 'update-info':
            $name = sanitizeText($val['name']);
            $address = sanitizeText($val['address']);
            $email = sanitizeText($val['email']);
            $phone = sanitizeText($val['phone']);
            $ai = sanitizeText($val['add_info']);
            $userid = get_userid();
            db()->query("UPDATE affliates SET name='{$name}',email='{$email}',phone='{$phone}',address='{$address}',add_info='{$ai}' WHERE user_id='{$userid}'");
            break;
    }
}

function get_user_group_commission($ug = 2, $pt)
{
    $q = db()->query("SELECT * FROM aff_commision_rules WHERE user_group='{$ug}' AND payment_type='{$pt}'");
    $array = array();
    $i = 1;
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            $array['level_' . $i] = $r['val'];
            $i++;
        }
    }
    return $array;
}

function delete_commission_rules($ug, $pt)
{
    db()->query("DELETE FROM aff_commision_rules WHERE user_group='{$ug}' AND payment_type='{$pt}'");
}

function add_commission_rules($val)
{
    $user_group = $val['user_group'];
    $pt = $val['payment_type'];
    $limit = config('number-of-commision-levels', 5);
    for ($i = 1; $i <= $limit; $i++) {
        $level = 'level_' . $i;
        $value = ($val[$level]) ? $val[$level] : 0;
        $time = time();
        db()->query("INSERT INTO aff_commision_rules(payment_type,level,val,user_group,time)
        VALUES ('{$pt}','{$level}','{$value}','{$user_group}','{$time}')");
    }
}

function getCommisionLevels()
{
    $q = db()->query("SELECT * FROM aff_commision_rules");
    $arr = array();
    if ($q->num_rows > 0) {
        while ($r = $q->fetch_assoc()) {
            $arr[] = $r;
        }
    }
    return $arr;
}

function get_points_array($slug)
{
    //get from database first
    $all = getCommisionLevels();
    if (!$all) {
        //default
        $all = aff_levels_default();
    }
    if (isset($all[$slug])) {
        return $all[$slug];
    }
    return array();
}

function aff_levels_default()
{
    $arr = array(
        'membership-subscription' => array(
            'level_1' => '10',
            'level_2' => '5',
            'level_3' => '3',
            'level_4' => '1',
            'level_5' => '1'
        ),
        'ads-subscription' => array(
            'level_1' => '10',
            'level_2' => '5',
            'level_3' => '3',
            'level_4' => '1',
            'level_5' => '1'
        ),
        'booster-subscription' => array(
            'level_1' => '10',
            'level_2' => '5',
            'level_3' => '3',
            'level_4' => '1',
            'level_5' => '1'
        ),
        'product-purchase' => array(
            'level_1' => '10',
            'level_2' => '5',
            'level_3' => '3',
            'level_4' => '1',
            'level_5' => '1'
        ),
        'spotlight' => array(
            'level_1' => '10',
            'level_2' => '5',
            'level_3' => '3',
            'level_4' => '1',
            'level_5' => '1'
        )
    );
    return $arr;
}

function get_commissions_list()
{
    $arr = array(
        '1' => 'Membership Subscription',
        '2' => 'Ads Subscription',
        '3' => 'Booster Subscription',
        '4' => 'Product Purchase',
        '5' => 'Spotlight',
        '6' => 'Property Activation',
    );
    if(!config('enable-aff-property',1)) unset($arr['6']);
    if(!config('enable-aff-spotlight',1)) unset($arr['5']);
    if(!config('enable-aff-product-purchase',1)) unset($arr['4']);
    if(!config('enable-aff-booster',1)) unset($arr['3']);
    if(!config('enable-aff-ads',1)) unset($arr['2']);
    if(!config('enable-aff-membership',1)) unset($arr['1']);
    return $arr;
}
function get_aff_commission_status($key = 'all',$t = 'commission')
{
    $arr = array(
        '1' => lang('affliate::waiting'),
        '2' => lang('affliate::denied'),
        '3' => lang('affliate::approved'),
    );
    if($t == 'request'){
        $arr = array(
            '1' => lang('affliate::pending'),
            '2' => lang('affliate::denied'),
            '3' => lang('affliate::approved'),
        );
    }
    if($key == 'all'){
        return $arr;
    }
    return $arr[$key];
}

function is_affliate($uid = null)
{
    $userid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM affliates WHERE user_id='{$userid}'");
    if ($q->num_rows > 0) {
        return $q->fetch_assoc();
    }
    return false;
}
