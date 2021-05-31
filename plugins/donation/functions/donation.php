<?php

function dCommissionEnabled(){
    if((int)str_replace('%','',config('admin-donation-commision',0)) > 0){
        return (int)str_replace('%','',config('admin-donation-commision',0));
    }
    return false;
}

class Donation extends App{
    private  $user_id ;
    private $paginate = true;
    private $limit= 10;
    private static $instance;
    private $table_name = "lh_donations";
    private $ftable_name = "lh_donations_followers";
    private $table_raised = "lh_donation_raised";
    private $table_settings = "lh_donations_settings";
    private $cTableName = "`donation_categories`"; //categoryTable Name
    private $pluginName = "donation";
    public function __constructor(){
       $this->user_id = get_userid();
    }


    public static function getInstance() {
        if(static::$instance == null) {
            static::$instance = new Donation();
        }
        return static::$instance;
    }

    public function canViewDonation($donation){
        $p = $donation['privacy'];
        if($p == 1) return true;
        if($p == 3 and $donation['user_id'] != get_userid()) return false;
        if($p == 2 and relationship_valid($donation['user_id'],1)) return true;
    }

    public function canViewComments($donation){
        $p = $donation['who_comment'];
        if($p == 1) return true;
        //if($p == 3 and $donation['user_id'] != get_userid()) return false;
        if($p == 2 and relationship_valid($donation['user_id'],1)) return true;
        return false;
    }

    public function canDonate($donation){
        $p = $donation['who_donate'];
        if($p == 1) return true;
        if($p == 2 and relationship_valid($donation['user_id'],1)) return true;
        return false;
    }

    /**
     * Returns the ID of a YouTube Video URL
     *
     * This works with both the short and full URL.
     *
     * @param  string $url The video URL
     * @return string  The video ID
     * @author TJ Miller http://tjay.co
     */
    public function getYoutubeCode($url)
    {
        $parts = [];
        $video_code = false;
        if (preg_match('/youtu.be/', $url)) {
            $video_code = preg_match_all('/youtu\.be\/(.*)\??/', $url, $matches);
            $video_code = $matches[1][0];
        } else {
            preg_match('/(\?v\=)+(.{11})/', $url, $parts);
            $video_code = substr($parts[0], -11);
        }
        return $video_code;
    }

    public function updateDonationView($id){
        if(session_get("donation_".$id)) return true;
        db()->query("UPDATE ".$this->table_name ." SET views = views + 1 WHERE id='{$id}'");
        session_put("donation_".$id,true);
        return true;
    }

    public function thankyouTemplate(){
        return array(
            'subject'=> 'Thank you for your Campaign Contribution',

            'message'=> "

             Hello [donor_name],

             Thank you for contibuting to [title]. Your contribution is very much appreciate and we assure you the
             money will be used for the main purpose to which it is contributed.

             For more information on how your donation is helping, you can visit our fundraising with the following link

             [donation_url]

             Thank you again and we look forward to your continued support.
            ",
        );
    }
    public function emailTemplateReplace($message,$donation, $t){
        switch($t){
            case 'iv':
                $user = find_user($donation['user_id']);
                $message = str_replace('[title]',$donation['title'], $message);
                $message = str_replace('[owner_name]',get_user_name($user), $message);
                $message = str_replace('[website_title]',get_setting("site_title", "crea8socialPRO"), $message);
                $message = str_replace('[donation_url]',url_to_pager("single_donation",array('id'=>$donation['id'])), $message);
                return $message;
                break;
        }
    }

    public function getFriendsLists($term){
         $friends = get_friends();
        $result = array();
         if($friends){
             $friends = implode(',',$friends);
         }else{
             $friends = implode(',',array(0));
         }
        $sql = "SELECT id,username,first_name,last_name,email_address,avatar FROM users
         WHERE username LIKE '{$term}%' OR first_name LIKE '{$term}%' OR last_name LIKE '{$term}%'
         OR email_address LIKE '{$term}%' AND id IN ({$friends}) GROUP BY id";
        //echo $sql;die();
         $q = db()->query($sql);
         if($q->num_rows > 0){
             while($r = $q->fetch_assoc()){
                 if($r['id'] == get_userid()) continue;
                $result[] = $r;
             }
         }
        return $result;
    }

    public function emailTemplateInvite(){
        return array(
            'subject'=> 'Invitation to Fundraising/Donation Campaign',
            'message'=> "
            Hello Friends, I am using this opportunity to invite you to support [title] created by [owner_name] on [website_title].

            Follow the link below to Read the description.

             [donation_url].

            Thank you.
            ",
        );
    }

    public function updateDonateMore($donation,$val,$t){
        $id = $donation['id'];
        switch($t){
            case 'pm':
                $pe = sanitizeText($val['pe']);
                $sk = (isset($val['sk'])) ? sanitizeText($val['sk']) : '';
                $pk = (isset($val['sk'])) ? sanitizeText($val['pk']) : '';
                db()->query("UPDATE ".$this->table_name. " SET paypal_email='{$pe}',publishable_key='{$pk}',secret_key='{$sk}' WHERE id='{$id}'");
                return true;
                break;
            case 'ec':
                $terms = sanitizeText($val['terms']);
                $subject = sanitizeText($val['subject']);
                $msg = lawedContent(stripslashes($val['message']));
                db()->query("UPDATE ".$this->table_name. " SET terms='{$terms}', t_subject='{$subject}', t_message='{$msg}' WHERE id='{$id}'");
                break;
            case 'ga':
                $images = $val['images'];
                $ytube = sanitizeText($val['ytube']);
                db()->query("UPDATE ".$this->table_name. " SET gallery='{$images}',ytube='{$ytube}' WHERE id='{$id}'");
                return true;
                break;
        }
    }

    public function getCurrency(){
        return array(
            'USD' => 'US Dollar',
            'AUD' => 'Australian Dollar',
            'BRL' => 'Brazilian Real',
            'CAD' => 'Canadian Dollar',
            'DKK' => 'Danish Krone',
            'EUR' => 'Euro',
            'GBP' => 'British Pound Sterling',
            'HKD' => 'Hong Kong Dollar',
            'INR' => 'Indian Rupees',
            'JPY' => 'Japanese Yen',
            'MXN' => 'Mexican Peso',
            'MYR' => 'Malaysian Ringgit',
            'NGN' => 'Nigerian Naira',
            'NOK' => 'Norwegian Krone',
            'NZD' => 'New Zealand Dollar',
            'RUB' => 'Russian Ruble',
            'SEK' => 'Swedish Krona',
            'CHF' => 'Swiss Franc',
            'TRY' => 'Turkish Lira',
            'INR'=>'Indian rupee',
            'PKR'=>'Pakistan Rupee'
        );
    }

    public function sendThankyouMail($val,$donation){
        $email = isset($val['email']) ? sanitizeText($val['email']) : $val['sp_email'];
        $email = ($email) ? $email : get_user_data('email_address');
        $full_name = isset($val['full_name']) ? sanitizeText($val['full_name']) : get_user_name();
        //anonymous
        if(isset($val['anonymous'])){
            //this is from stripe
           $full_name = ($val['anonymous']) ? lang("donation::anonymous") : '';
        }

        $template = $this->thankyouTemplate();

        $subject = ($donation['t_subject']) ? $donation['t_subject'] : $template['subject'] ;
        $message = ($donation['t_message']) ? $donation['t_message'] : $template['message'];


        $user = find_user($donation['user_id']);


        $message = str_replace('[title]',$donation['title'], $message);
        $message = str_replace('[owner_name]',get_user_name($user), $message);
        $message = str_replace('[website_title]',get_setting("site_title", "crea8socialPRO"), $message);
        $message = str_replace('[donor_name]',$full_name, $message);
        $message = str_replace('[donation_url]',url_to_pager("single_donation",array('id'=>$donation['id'])), $message);

        mailer()->setAddress($email)->setSubject($subject)->setMessage($message)->send();

        //send real time notification to campaign followers, campaign owner
        //and previous donors when campaign receive a new donation
        $followers = $this->getDonationFollowers($donation['id']);
        if($followers){
            $needed = array(
                'full_name'=> $full_name,
                'amount'=> $val['amount'],
                'cur'=>$donation['cur'],
                'title'=>$donation['title'],
                'id'=>$donation['id']
            );
            foreach($followers as $f){
                $uid = $f['user_id'];
                if($uid == get_userid()) continue;
                //send_notification($userid,$type, $type_id, $data, $title, $content, $fromUserid);
                send_notification($uid,'donation.notify',$donation['id'],$needed);
            }
        }

        if(is_loggedIn()){
            if(config("donor-automatically-follow-campaign",true)){
                $this->deleteMyOldFollowing($donation['id']);
                $this->newDonationFollower($donation['id']);
            }
        }

        return true;
    }

    public function isFollowing($id,$uid = null){
        $uid = ($uid) ? $uid : get_userid();
        $q = db()->query("SELECT * FROM ".$this->ftable_name ." WHERE did='{$id}' AND user_id='{$uid}'");
        if($q->num_rows > 0) return true;
        return false;
    }
    public function deleteMyOldFollowing($id, $uid = null){
        $uid = ($uid) ? $uid : get_userid();
        db()->query("DELETE FROM ".$this->ftable_name." WHERE user_id='{$uid}' AND did='{$id}'");
        return true;
    }

    public function newDonationFollower($id,$uid = null){
        $uid = ($uid) ? $uid : get_userid();
        db()->query("INSERT INTO ".$this->ftable_name." (did,user_id) VALUES ('{$id}','{$uid}')");
        return db()->insert_id;
    }

    public function getDonationFollowers($id){
         $q = db()->query("SELECT * FROM ".$this->ftable_name." WHERE did='{$id}'");
         $arr = array();
        if($q->num_rows > 0){
            while($r = $q->fetch_assoc()){
                $arr[] = $r;
            }
        }
        return $arr;
    }

    public function getRaisedArr($id){
        $q = db()->query("SELECT * FROM ".$this->table_raised ." WHERE id='{$id}'");
        return $q->fetch_assoc();
    }
    public function raisedActive($id){
        db()->query("UPDATE ".$this->table_raised ." SET status='1' WHERE id='{$id}'");
        return true;
    }
    public function amountRaised($id){
        $q = db()->query("SELECT sum(amount) AS c FROM ".$this->table_raised." WHERE did='{$id}' AND status='1'");
        $r = $q->fetch_assoc();
        return ($r['c']) ? $r['c'] : 0;
    }
    public function donors($id){
        $q = db()->query("SELECT * FROM ".$this->table_raised." WHERE did='{$id}' AND status='1' ORDER BY time DESC");
        $result = array();
        if($q->num_rows > 0){
            while($r = $q->fetch_assoc()){
                $result[] = $r;
            }
        }
        return $result;
    }

    public function tofeed($rid){
        $userid = get_userid();
        $outcome =  add_feed(array(
            'user_id'=>$userid,
            'entity_id' => $userid,
            'entity_type' => 'user',
            'type' => 'feed',
            'type_id' => 'donation',
            'type_data' => $rid,
            'privacy' => 1,
            'auto_post' => true,
            'can_share' => false,
            'location' => 'donation'
        ));
    }
    public function addNewRaised($val,$donation,$t){
        $id = $donation['id'];
        $amount = $val['amount'];
        $status = $t;
        $ano = (isset($val['anonymous'])) ? 1 : 0;
        $to_feed = (isset($val['show_feed'])) ? 1 : 0;
        $msg = sanitizeText($val['msg']);
        $full_name = isset($val['full_name']) ? sanitizeText($val['full_name']) : get_user_name();
        $full_name = ($ano) ? lang("donation::anonymous") : $full_name;
        $email = isset($val['email']) ? sanitizeText($val['email']) : $val['sp_email'];
        $email = ($email) ? $email : get_user_data('email_address');
        $user_id = get_userid();
        $cur = $donation['cur'];
        $time = time();
        db()->query("INSERT INTO ".$this->table_raised." (did,amount,status,anonymous,user_id,full_name, cur, email_address,msg,time,to_feed)
        VALUES ('{$id}','{$amount}','{$status}','{$ano}','{$user_id}','{$full_name}','{$cur}','{$email}','{$msg}','{$time}','{$to_feed}')");
        return db()->insert_id;
    }

    public function removeOldSettings($id){
        db()->query("DELETE FROM ".$this->table_settings." WHERE user_id='{$id}'");
    }

    public function saveSettings($pe,$pk,$sk,$uid = null){
        $pe = sanitizeText($pe);
        $pk = sanitizeText($pk);
        $sk = sanitizeText($sk);
        $id = ($uid) ? $uid : get_userid();
        $this->removeOldSettings($id);
        db()->query("INSERT INTO ".$this->table_settings."
        (secret_key,publishable_key,paypal_email,user_id) VALUES ('{$sk}','{$pk}','{$pe}','{$id}')");
    }

    public function getSettings($id = null){
        $default = array(
            'paypal_email'=>'',
            'publishable_key'=>'',
            'secret_key'=>''
        );
        $id = ($id) ? $id : get_userid();
        $sql = "SELECT * FROM ".$this->table_settings ." WHERE user_id =".$id;
        $this->paginate = false;
        $result = $this->dbQuery($sql);
        if(!$result) return $default;
        return $result[0];
    }

    public function deleteDonation($id){
        //delete all the images related to donations
        //$don =  Donation::getInstance();
        $donation = $this->getSingle($id);
        $donation = $donation[0];
        $gallery = $donation['gallery'];
        if($gallery){
            $images = perfectUnserialize($donation['gallery']);
            foreach ($images as $k=>$image){
                delete_file(path($image));
            }
        }
        delete_file(path($donation['image']));
        db()->query("DELETE FROM ".$this->table_name." WHERE id='{$id}'");
        return true;
    }

    public function canManageDonation($donation){
        $uid = 0;
        if(isset($donation['user_id'])){
            $uid = $donation['user_id'];
        }

        if(isset($donation[0]['user_id'])){
            $uid = $donation[0]['user_id'];
        }
        if(!$uid) return false;
        if($uid == get_userid()) return true;
        return false;
    }

    public function timeLeft($tim){
        $now = new DateTime();
        $tim = date('M j , Y',$tim);
        $future_date = new DateTime($tim);

        $interval = $future_date->diff($now);

        $year = strtolower(lang("donation::year"));
        $month = strtolower(lang("donation::month"));
        $days = strtolower(lang("donation::days"));
        $hours = strtolower(lang("donation::hours"));
        $minutes = strtolower(lang("donation::minutes"));
        $seconds = strtolower(lang("donation::seconds"));
        //'%y Year %m Month %d Day %h Hours %i Minute %s Seconds'
        //return $interval->format("%a ".$days." ".strtolower(lang("donation::left")));
        //return $interval->format('%y '.$year.' %m '.$month.' %d '.$days.' %h  '.$hours.' '.lang("donation::left"));
        return $interval->format(' %d '.$days.' %h  '.$hours.' '.lang("donation::left"));
    }

    public function getStats($t){
        $tm = time();
        switch($t){
            case 'ongoing':
                $q = db()->query("SELECT * FROM ".$this->table_name." WHERE closed='0' AND published='1' AND (expire_time > '{$tm}' OR expire_time = '' ) ORDER BY time DESC");
                return $q->num_rows;
                break;
            case 'closed':
                $q = db()->query("SELECT * FROM ".$this->table_name." WHERE closed='1' AND published='1' ORDER BY time DESC");
                return $q->num_rows;
                break;
            case 'expired':
                $q = db()->query("SELECT * FROM ".$this->table_name." WHERE published='1' AND expire_time < '{$tm}' AND unlimited = '0' ORDER BY time DESC");
                return $q->num_rows;
                break;
        }
        return 0;
    }
    public function getSingle($id){
        $sql = "SELECT * FROM ".$this->table_name." WHERE id='{$id}'";
        $this->paginate = false;
        return $this->dbQuery($sql);
    }
    public function unixDonationTime($et){
        $arr = explode('/',$et);
        $m = $arr[0];
        $d = $arr[1];
        $y = $arr[2];
        return mktime(0,0,0,$m,$d,$y);
    }

    public function saveDonation($val, $t = 'new',$admin = false){
        $expected = array(
            'title'=>'',
            'description'=>'',
            'category'=>'',
            'target_amount'=>'',
            'expire_time'=>'',
            'photo'=>'',

            'unlimited'=>0,
            'full_description'=>'',
            'cur'=>'USD',
            'd_min'=>0,
            'pre'=>array('',''),
            'anonymous'=>1,
            'location'=>'',
            'privacy'=>1,
            'who_comment'=>1,
            'who_donate'=>1,
            'closed'=>0,
            'published'=>1,
        );
        extract(array_merge($expected,$val));
        /**
         * @var $title
         * @var $description
         * @var $category
         * @var $target_amount
         * @var $photo
         * @var $expire_time
         *
         * @var $unlimited
         * @var $full_description
         * @var $cur
         * @var $d_min
         * @var $pre
         * @var $anonymous
         * @var $location
         * @var $privacy
         * @var $who_comment
         * @var $who_donate
         * @var $closed
         * @var $published
         */
        $db = db();
        $title = mysqli_real_escape_string($db,sanitizeText($title));
        $category = mysqli_real_escape_string($db,sanitizeText($category));
        $description = mysqli_real_escape_string($db,sanitizeText($description));
        $target_amount = mysqli_real_escape_string($db,sanitizeText($target_amount));
        $expire_time = mysqli_real_escape_string($db,sanitizeText($expire_time));

        $pre = perfectSerialize($pre);
        $unlimited = mysqli_real_escape_string($db,sanitizeText($unlimited));
        $full_description = mysqli_real_escape_string($db,lawedContent(stripslashes($full_description)));
        $cur = mysqli_real_escape_string($db,sanitizeText($cur));
        $d_min = mysqli_real_escape_string($db,sanitizeText($d_min));
        $anonymous = mysqli_real_escape_string($db,sanitizeText($anonymous));
        $location = mysqli_real_escape_string($db,sanitizeText($location));
        $privacy = mysqli_real_escape_string($db,sanitizeText($privacy));
        $who_comment = mysqli_real_escape_string($db,sanitizeText($who_comment));
        $who_donate = mysqli_real_escape_string($db,sanitizeText($who_donate));
        $closed = mysqli_real_escape_string($db,sanitizeText($closed));
        $published = mysqli_real_escape_string($db,sanitizeText($published));

        if($t == 'new'){
            $time = time();
            $verified = config('automatic-approve-created-donations',1);
            $id = get_userid();
            $sql = "INSERT INTO ".$this->table_name." (title,description,category,target_amount,time,user_id,status,expire_time,verified,image,
            predefined,unlimited,full_description,cur,donation_min,anonymous,location,privacy,who_comment,who_donate,published)
            VALUES ('{$title}','{$description}','{$category}','{$target_amount}','{$time}','{$id}','1','{$expire_time}','{$verified}','{$photo}',
            '{$pre}','{$unlimited}','{$full_description}','{$cur}','{$d_min}','{$anonymous}','{$location}','{$privacy}','{$who_comment}',
            '{$who_donate}','{$published}')";
            $db->query($sql);
            ///echo db()->error;die();
            $insert_id = $db->insert_id;

            //thank you
            $thankyou = $this->thankyouTemplate();
            $ts = $thankyou['subject'];
            $tm = $thankyou['message'];
            db()->query("UPDATE ".$this->table_name." SET t_subject='{$ts}',t_message='{$tm}' WHERE id='{$insert_id}'");

            //creator is an automatic follower
            $this->newDonationFollower($insert_id);
            return $insert_id;
        }
        else{
            //$t is now the id
            $id = $t;
            $sql = "UPDATE ".$this->table_name." SET title='{$title}',description='{$description}', category='{$category}',
            target_amount='{$target_amount}',expire_time='{$expire_time}',image='{$photo}',predefined='{$pre}',unlimited='{$unlimited}',
            full_description='{$full_description}',cur='{$cur}',donation_min='{$d_min}',anonymous='{$anonymous}',location='{$location}',
            privacy='{$privacy}', who_comment='{$who_comment}',who_donate='{$who_donate}',closed='{$closed}',published='{$published}' ";

            if($admin){
                $f = $val['featured'];
                $sql .= ", featured='{$f}'";
            }

            $sql .=" WHERE id='{$id}'";

            $db->query($sql);
        }
    }

    public function getDonations($type = 'mine',$term = null,$category=null){
        $sql = "SELECT * FROM ".$this->getTableName()." WHERE time != '' ";
        $tm = time();
        if($term){
            $sql .= " AND title LIKE '%{$term}%'";
        }
        if($category and $category != 'all'){
            $sql .= " AND category='{$category}'";
        }
        $uid = get_userid();
        //published
        //unlimited
        //active
        //verified
        switch($type){
            case 'admincp':
                $sql .= " ORDER BY time DESC ";
                return $this->dbQuery($sql);
                break;
            case 'closed':
                $sql .= " AND published='1'"; //and published
                $sql .= " AND closed='1'";
                $sql .= " ORDER BY time DESC ";
                return $this->dbQuery($sql);
            case 'expired':
                $sql .= " AND published='1'"; //and published
                $sql .= " AND expire_time < '{$tm}' AND unlimited = '0'  ORDER BY time DESC";
                return $this->dbQuery($sql);
            case 'featured':
                $sql .= " AND published='1'"; //and published
                $sql .= " AND closed='0' "; //and published
                $sql .= " AND featured='1' ";
                $sql .= " AND (expire_time > '{$tm}' OR expire_time = '' ) ORDER BY RAND()";
               // echo $sql;die();
                return $this->dbQuery($sql);
                break;
            case 'donated':
                $sql = "SELECT did FROM ".$this->table_raised." WHERE user_id='{$uid}' GROUP BY did";
                return $this->dbQuery($sql);
                break;
            case 'friends':
                //friends
                $arr = implode(',',array(0));
                $friends = get_friends();
                if($friends){
                    $arr = implode(',',$friends);
                }
                $sql .= " AND published='1' "; //and published
                $sql .= " AND closed='0' "; //and published
                $sql .= " AND user_id IN ({$arr})";
                return $this->dbQuery($sql);
                break;
            case 'all':
                $sql .= " AND published='1'"; //and published
                $sql .= " AND closed='0'"; //and published
                $sql .= " AND (expire_time > '{$tm}' OR expire_time = '' ) ORDER BY time DESC";
                return $this->dbQuery($sql);
                break;
            case 'mine':
                $sql .= " AND user_id='{$uid}' ";
                $sql .= " ORDER BY time DESC";
                return $this->dbQuery($sql);
                break;
        }
        return '' ;
    }

    public function getTableName(){
        return $this->table_name;
    }

    public function setTableName($name){
        $this->table_name = $name;
        return $this->table_name;
    }


    public function getUserid(){
        return $this->user_id;
    }

    public function setUserid($id){
        $this->user_id = $id;
        return $this->user_id;
    }
    public function  dbQuery($sql){
        if($this->paginate){
            return paginate($sql,$this->limit);
        }
        $arr = array();
        $q = db()->query($sql);
        if($q->num_rows > 0){
            while($r = $q->fetch_assoc()){
                $arr[] = $r;
            }
        }
        return $arr;
    }

    /**
     * @param $val
     * @return bool
     * Blog Categories
     */
    public function add_category($val) {
        $expected = array(
            'title' => ''
        );

        /**
         * @var $title
         * @var $desc
         */
        extract(array_merge($expected, $val));
        $titleSlug = $this->pluginName."_category_".md5(time().serialize($val)).'_title';

        foreach($title as $langId => $t) {
            add_language_phrase($titleSlug, $t, $langId, $this->pluginName);
        }


        $time = time();
        $order = db()->query('SELECT id FROM '.$this->cTableName);
        $order = $order->num_rows;
        $query = db()->query("INSERT INTO ".$this->cTableName."(
            `title`,`category_order`) VALUES(
            '{$titleSlug}','{$order}'
            )
        ");

        return true;
    }

    public function save_category($val, $category) {
        $expected = array(
            'title' => ''
        );

        /**
         * @var $title
         */
        extract(array_merge($expected, $val));
        $titleSlug = $category['title'];

        foreach($title as $langId => $t) {
            (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, $this->pluginName) : add_language_phrase($titleSlug, $t, $langId, $this->pluginName);
        }

        return true;
    }

    public function get_categories() {
        $query = db()->query("SELECT * FROM ".$this->cTableName." ORDER BY `category_order` ASC");
        return fetch_all($query);
    }

    public function get_category($id) {
        $query = db()->query("SELECT * FROM ".$this->cTableName." WHERE `id`='{$id}'");
        return $query->fetch_assoc();
    }

    public function delete_category($id, $category) {
        delete_all_language_phrase($category['title']);

        db()->query("DELETE FROM ".$this->cTableName." WHERE `id`='{$id}'");

        return true;
    }

    public function update_category_order($id, $order) {
        db()->query("UPDATE ".$this->cTableName." SET `category_order`='{$order}' WHERE  `id`='{$id}'");
    }
}

