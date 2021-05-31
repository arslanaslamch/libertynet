<?php

function track_pager($app)
{
    $id = sanitizeText(input('sid'));
    if ($id) {
        db()->query("UPDATE mailautomation_stats SET `status`=2 WHERE id='{$id}'");
    }
    return true;
}

function service_auto_pager($app){
    //get all the automation
    //foreach days, check if we have users for that day who were not active
    //
    //echo $style;die();
    ini_set('max_execution_time', '0');
    $count = 0;
    $q = db()->query("SELECT * FROM mailautomations");
    if($q->num_rows > 0){
        while($r = $q->fetch_assoc()){
            $days = $r['na_count'];
            $days_str = '-'.$days.' days';
            $tms = date('Y-m-d',strtotime($days_str));
            $start = strtotime($tms." 00:00:00"); //12:00am
            $end = strtotime($tms." 23:59:59"); //11:59pm
            $sql = "SELECT * FROM users WHERE online_time <= '{$end}' AND online_time >= '{$start}'";
            //$sql = "SELECT * FROM users WHERE online_time <= '1545782399' AND online_time >= '1545696000'";
            $qq = db()->query($sql);
            //echo $qq->num_rows;die();
            if($qq->num_rows > 0){
                //we have users here
                while($user = $qq->fetch_assoc()){
                    //get the template
                    $active_lang = get_active_language();

                    //get the mail template of this active language

                    $subject = replaceMailAutomationStrings(get_phrase($active_lang, $r['subject']), $user,false);
                    $mail_body = replaceMailAutomationStrings(get_phrase($active_lang, $r['body_content']),$user,true);
                    $email_address = $user['email_address'];
                    //echo $mail_body;die();
                    $id = addNewAutomationStats($user['id'],$subject,$email_address);
                    $mail_body .="<img style='height:0;width:0' src='".url_to_pager("automation-track").'?sid='.$id."' />";
                    $mailer = mailer();
                    $mailer->setAddress($email_address, $user['first_name']);
                    $mailer->setSubject($subject);
                    $mailer->setMessage($mail_body);
                    $mailer->send();
                    //automation starts
                    $count ++;
                }
            }
        }
    }
    echo $count." Mails Sent";
}

function service_highlights_pager($app){
     //get website random posts
    ini_set('max_execution_time', '0');

    //$random_users = get_random_users_ma();
    $limit = config("number-of-users-to-send-hightlights",50);
    $users = db()->query("SELECT id,email_address,first_name,avatar,gender FROM users ORDER BY RAND() LIMIT {$limit}");

    //echo $mail_body;die();
    $subject = get_setting("site_title", "Crea8social").' '.lang("mailautomation::post-hightlights").' '.date('j.n');
    while($user = $users->fetch_assoc()){
        $feeds = hightlights_feeds($user['id']);
        $mailer = mailer();
        $mail_body = view("mailautomation::highlights-feeds",array('feeds'=>$feeds,'user'=>$user));
        $footerTemplate = get_email_template('footer');
        $footerContent = lang($footerTemplate['body_content']);
        $mail_body = $mail_body.$footerContent;
        $email_address = $user['email_address'];
        $id = addNewAutomationStats($user['id'],$subject,$email_address,'highlights');
        $mail_body .="<img style='height:0;width:0' src='".url_to_pager("automation-track").'?sid='.$id."' />";

        //echo $mail_body;die();
        $mailer->setAddress($email_address, $user['first_name']);
        $mailer->setSubject($subject);
        $mailer->setMessage($mail_body);
        $mailer->send();
    }
}