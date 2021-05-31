<?php

class Birthday{
    protected static $fields = 'birth_day,birth_month,birth_year,avatar,cover,first_name,last_name,username,id,email_address';
    public static function getCelebrants($filter,$key, $limit='all', $term = null){
        switch ($filter){
            case 'month':
                $key = strtolower($key);
                $f = self::$fields;
                $sql = "SELECT  {$f} FROM users WHERE birth_month='{$key}' ";
                if($term){
                    $sql ="SELECT  {$f} FROM users WHERE username LIKE '%{$term}' OR last_name LIKE '%{$term}' OR first_name LIKE '%{$term}'";
                }
                if(session_get('b-filter-status',0)){
                    $friends = implode(',',get_friends());
                   $sql .=" AND id IN ({$friends}) ";
                }
                $sql .=" ORDER BY ABS(birth_day) ASC";
                return  paginate($sql,'all');
                break;
        }
        return paginate("SELECT * FROM users");
    }

    public static function getMonths($key = null){
          $month =  array(
            '1' => 'january',
            '2' => 'february',
            '3' => 'march',
            '4' => 'april',
            '5' => 'may',
            '6' => 'june',
            '7' => 'july',
            '8' => 'august',
            '9' => 'september',
            '10' => 'october',
            '11' => 'november',
            '12' => 'december'
        );

          if($key) return $month[$key];

          return $month;
    }

    public static function getTodaysBirthdays($friendsonly = false){
        $todayMonth = strtolower(date('F'));
        $todayDay = date('d');
        $todayDayExtra = '0'.date('d');
        $f = self::$fields;
        if(input('x')){
            $q =  db()->query("SELECT {$f} FROM users WHERE (birth_day={$todayDay} OR birth_day='{$todayDayExtra}') AND birth_month='{$todayMonth}' ORDER BY ABS(birth_day) ASC");
           echo  get_user_data('birth_month');
            echo db()->error.'<br/>';
            echo $q->num_rows;die();
        }
        if(session_get('b-filter-status',0)){
            $friends = implode(',',get_friends());
            return paginate("SELECT {$f} FROM users WHERE (birth_day={$todayDay} OR birth_day='{$todayDayExtra}') AND birth_month='{$todayMonth}' AND id IN ({$friends}) ORDER BY ABS(birth_day) ASC",'all');
        }
        return paginate("SELECT {$f} FROM users WHERE (birth_day={$todayDay} OR birth_day='{$todayDayExtra}') AND birth_month='{$todayMonth}' ORDER BY ABS(birth_day) ASC",'all');
    }


    public static function hasBirthdayToday($uid){
        $todayMonth = strtolower(date('F'));
        $todayDay = date('d');
        $todayDayExtra = '0'.date('d');
        $f = self::$fields;
        $sql = "SELECT {$f} FROM users WHERE (birth_day={$todayDay} OR birth_day='{$todayDayExtra}') AND birth_month='{$todayMonth}' AND id='{$uid}'";
        $q = db()->query($sql);
        if($q->num_rows > 0) return true;
        return false;
    }

    public static function getAdminAccounts(){
        $f = self::$fields;
        return paginate("SELECT {$f} FROM users WHERE role='1'",'all');

    }

    protected static function removeOldTemplate($type){
        db()->query("DELETE FROM birthday_templates WHERE `type`='{$type}'");
    }

    /**
     * @param $type
     * @return array|null
     */
    public static function getTemplate($type){
        $q = db()->query("SELECT * FROM birthday_templates WHERE `type`='{$type}'");
        if($q->num_rows > 0){
            return $q->fetch_assoc();
        }
        return null;
    }

    /**
     * @param $type
     * @param $content
     * @param $image
     * @param $admin
     * @param null $subject
     */
    public static function addTemplate($type,$content,$image,$admin,$subject = null){
        self::removeOldTemplate($type);
        db()->query("INSERT INTO birthday_templates(`type`,content,image,admin,subject) VALUES ('{$type}','{$content}','{$image}','{$admin}','{$subject}')");
    }


    public static function formatTemplates($template,$user,$img = null){
        $template = str_replace('[first_name]',$user['first_name'],$template);
        $template = str_replace('[last_name]',$user['last_name'],$template);
        $template = str_replace('[username]',$user['username'],$template);
        if($img){
            $template = str_replace('[img]',$img,$template);
        }
        return $template;
    }

}