<?php

if(!function_exists('random_text')){
    function random_text($type = 'alnum', $length = 8)
    {
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                //$pool = '0123456789';
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


function send_sms($msg,$phone_number){
    //send this user message here
    /**
     * This is for Twilo
     */
    if(config('sms-gateway',1) == 1){
        //require_once path('plugins/sms/pages/Twilio.php');
        $sid = config('twilio-account-id',''); // Your Account SID from www.twilio.com/user/account
        $token = config('twilio-token-id',''); // Your Auth Token from www.twilio.com/user/account

        // Get the PHP helper library from twilio.com/docs/php/install
        //$client = new Services_Twilio($sid, $token);
        //$sms = $client->account->sms_messages->create(config('from-twilio-number',''),$phone_number, $msg, array());

        //using curl
        $ch = curl_init('https://api.twilio.com/2010-04-01/Accounts/'.$sid.'/Messages.json');
        $data = array(
            'To' => $phone_number,
            'From' => config('from-twilio-number',''),
            'Body' => $msg,
        );
        $auth = $sid .":". $token;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
        curl_setopt($ch, CURLOPT_USERAGENT , 'Mozilla 5.0');
        $response = curl_exec($ch);
        curl_close($ch);
    }
    elseif(config('sms-gateway',1) == 2){
        //46elks
        $url = 'https://api.46elks.com/a1/SMS';
        $username = config('46elks_app_id');
        $password = config('46elks_api_password');
        $sms = array('from' => config('sms-from','Lightedphp'),
            'to' => $phone_number,
            'message' => $msg);

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header'  => "Authorization: Basic ".
                    base64_encode($username.':'.$password). "\r\n".
                    "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($sms),
                'timeout' => 10
            )
        ));
        $response = file_get_contents($url, false, $context);
    }
    elseif(config('sms-gateway',1) == 3){
        $url = 'http://api.clickatell.com/http/sendmsg';
        $user = config('clickatell-username','');
        $password = config('clickatell-password','');
        $api_id = config('clickatell-app_id');
        $to = $phone_number;
        $text = $msg;
        $data = array(
            'user' => $user,
            'password'=>$password,
            'api_id'=>$api_id,
            'to'=>$to,
            'text' => $text
        );

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) { /* Handle error */ }
        return $result;
        // return var_dump($result);
    }
    elseif(config('sms-gateway',4) == 4){
        //echo "Quick sms";
        $url = 'http://www.quicksms1.com/api/sendsms.php';
        $user = config('quicksms_username','');
        $password = config('quicksms_password','');
        $to = $phone_number;
        $text = $msg;
        $data = array(
            'username' => $user,
            'password'=>$password,
            'sender'=>config('sms-from','Betayan'),
            'message'=>$text,
            'recipient'=>$to,
            'convert'=> 1
        );

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
        // print_r($result);
        //  exit;
    }elseif(config('sms-gateway',1) == 10){
        //Text local
        $apiKey = config('text-local-api-key','');
        $apiKey = urlencode($apiKey);

        // Message details
        $numbers = array($phone_number);
        $sender = urlencode(config('sms-from','LightedPHP'));
        $message = rawurlencode($msg);

        $numbers = implode(',', $numbers);

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Process your response here
        print_r($response);die();

    }
    return true;
}

//insert into the database table
function insert_in_sms_table($user_id,$phone_num,$generated_code){
    $sql = "INSERT INTO `sms_verification` (user_id,phone_num,scode) VALUES ('{$user_id}','{$phone_num}','{$generated_code}')";
    db()->query($sql);
}

function getNumberOfAccountsConnectedToMe($phone_num){
    $q = db()->query("SELECT * FROM `sms_verification` WHERE phone_num='{$phone_num}'");
    return $q->num_rows;
}

function smsConfirmUseridIsConnectedTothisPh($user_id,$phone_num){
    $q = db()->query("SELECT * FROM `sms_verification` WHERE user_id='{$user_id}' AND phone_num='{$phone_num}'");
    $r = ($q->num_rows > 0) ? true : false;
    return true;
}
function  update_in_sms_table($user_id,$phone_num,$generated_code){
    $sql = "UPDATE sms_verification SET scode='{$generated_code}' WHERE phone_num='{$phone_num}'";
    db()->query($sql);
}

function userid_already_exists($user_id){
    $sql = "SELECT `s_id` FROM `sms_verification` WHERE `user_id`='{$user_id}'";
    $result = db()->query($sql);
    $r = $result->num_rows;
    return ($r) ? true: false;
}
function update_user_phone_number($user_id,$phone_num){
    $sql = "UPDATE `sms_verification` SET `phone_num`='{$phone_num}' WHERE `user_id`='{$user_id}'";
    db()->query($sql);
    return true;
}

function generateSmsCode() {
    $length  = config('num-characters-to-send',5);
    return random_text('numeric',$length);
    $chars = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return strtoupper($result);
}

function smsGetAllUsers($term = null){

    $sql = "SELECT `username`,`avatar`,`first_name`,`last_name`, `id`,`phone_num`,join_date,online_time FROM `users` u LEFT JOIN `sms_verification` s ON u.id=s.user_id ";
    $where = "WHERE phone_num !='' ";
    if($term){
        $term = sanitizeText($term);
        $where .= " AND username LIKE '%{$term}' OR first_name LIKE '%{$term}'
        OR last_name LIKE '%{$term}' OR email_address LIKE '%{$term}' OR username LIKE '%{$term}'";
    }
    $sql = $sql.$where." ORDER BY id DESC";
    return paginate($sql,12);
}
function getMyPhoneNumber($uid = null){
    $uid = ($uid) ? $uid : get_userid();
    $sql = "SELECT `phone_num` FROM `sms_verification` WHERE `user_id`='{$uid}'";
    $q = db()->query($sql);
    if($q->num_rows > 0){
        $r = $q->fetch_assoc();
        return $r['phone_num'];
    }
    return null;
}
function sms_number_in_table($number){
    $sql = "SELECT `s_id` FROM `sms_verification` WHERE `phone_num`='{$number}'";
    $result = db()->query($sql);
    $r = $result->num_rows;

   return ($r) ? true: false;
}

function get_userid_by_email($username){
    $sql = "SELECT `id` FROM `users` WHERE `username`='{$username}' OR `email_address`='{$username}'";
    $result = db()->query($sql);
    if($result->num_rows > 0){
        $r = $result->fetch_assoc();
        return $r['id'];
    }else{
        return false;
    }

}
function is_detail_correct($number,$code){
    $sql = "SELECT * FROM `sms_verification` WHERE `phone_num`='{$number}' AND `scode`='{$code}'";
    $result = db()->query($sql);

    $r = $result->num_rows;
    return ($r) ? true: false;
}
function is_verified($number){
    $sql = "SELECT * FROM `sms_verification` WHERE `phone_num`='{$number}' AND `verified`=1";
    $result = db()->query($sql);
    return ($result) ? true: false;
}
function get_code_by_phone_number($number){
    $sql = "SELECT `scode` FROM `sms_verification` WHERE `phone_num`='{$number}'";
    $result = db()->query($sql);
    $scode = $result->fetch_assoc();
    return $scode['scode'];
}
function verify_number($number){
    $sql = "UPDATE `sms_verification` SET `verified`=1 WHERE `phone_number`='{$number}' OR `user_id`='{$number}'";
    db()->query($sql);
}

function sms_get_unverified($limit=10){
    $sql = "SELECT * FROM `users` WHERE `activated`=0 ORDER BY `id` DESC";
    return paginate($sql,$limit);
}

function get_username_by_phone_number($phone_number){
    $sql = "SELECT `username`,id from `users` u LEFT JOIN `sms_verification` s ON u.id=s.user_id WHERE `phone_num`='{$phone_number}'";
    $result = db()->query($sql);
    if($result->num_rows > 0){
         $r =  $result->fetch_assoc();
        return $r;
    } else{
        return false;
    }
}

//2fa start
function backupCodeLeft($uid = null){
    $uid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM sms_backup WHERE user_id='{$uid}' AND status=0");
    return $q->num_rows;
}

function generate_backup_code(){
    $arr = array();
    $uid = get_userid();
    db()->query("DELETE FROM sms_backup WHERE user_id='{$uid}'");//delete all old codes
    $limit = config('backup-code-limit',20);
    for($i = 1; $i <= $limit; $i++){
        $time = time();
        $code = random_text();
        db()->query("INSERT INTO sms_backup(user_id,`code`,time) VALUES('{$uid}','{$code}','{$time}')");
        $arr[] = $code;
    }
    return $arr;
}

function myBackupCode($uid = null){
    $uid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT `code` FROM sms_backup WHERE user_id='{$uid}' AND status=0");
    $arr = array();
    if($q->num_rows > 0){
        while($r = $q->fetch_assoc()){
            $arr[] = $r['code'];
        }
    }
    return $arr;
}

function is_valid_backup($code,$uid){
    $q = db()->query("SELECT * FROM sms_backup WHERE user_id='{$uid}' AND `code`='{$code}'");
    if($q->num_rows > 0) return true;
    return false;
}

//2fa end
