<?php

get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-mailautomation')->setActive();

/*function cron_pager($app){
    $app->setTitle(lang("mailautomation::cron-job-settings"));
    return $app->render(view("mailautomation::cronjob"));
}*/

function ajax_pager($app)
{
   $action = input('action');
   switch($action){
       case 'filter-map':
           $from = input('from_date');
           $end = input('end_date');
           $data = getMailAutomationGraphData($from, $end);
           $status = 1;
           if(!$data){
               $status = 0;
           }
           return json_encode(array('status'=>$status,'d'=>$data));
           break;
   }
}

function mail_logs_pager($app){
    $app->setTitle(lang("mailautomation::mail-logs"));
    $limit = 10;
    $stats = getMailAutomationStats($limit);
    return $app->render(view("mailautomation::logs",array('stats'=>$stats)));
}


function stats_pager($app)
{
    $app->setTitle(lang("mailautomation::statistics"));
    $limit = 10;
    $stats = getMailAutomationStats($limit);
    $from = (input('from_date')) ? input('val.from_date') : date('m/d/Y', (strtotime('-6 days')));
    $end = (input('end_date')) ? input('val.end_date') : date('m/d/Y', time());
    $data = getMailAutomationGraphData($from, $end);
    return $app->render(view("mailautomation::statistics", array('stats' => $stats, 'data' => $data)));
}

function lists_pager($app)
{
    $app->setTitle(lang("mailautomation::all-automations"));
    return $app->render(view('mailautomation::lists', array('autos' => admin_get_mailautomations(input('term')))));
}


function manage_pager($app)
{
    $action = input('action', 'order');
    $app->setTitle(lang('mailautomation::manage-all-automations'));
    $message = null;
    switch ($action) {
        case 'send-high':
            $email_address = get_user_data('email_address');
            //lets
            $user = get_user();
            $feeds = hightlights_feeds($user['id']);
            $subject = get_setting("site_title", "Crea8social").' '.lang("mailautomation::post-hightlights").' '.date('j.n');
            $mail_body = view("mailautomation::highlights-feeds",array('feeds'=>$feeds,'user'=>$user));
            $footerTemplate = get_email_template('footer');
            $footerContent = lang($footerTemplate['body_content']);
            $mail_body = $mail_body.$footerContent;

            $email_address = get_user_data('email_address', $user);
            $id = addNewAutomationStats($user['id'], $subject, $email_address);
            $mail_body .= "<img style='height:0;width:0' src='" . url_to_pager("automation-track") . '?sid=' . $id . "' />";
            $mailer = mailer();
            $mailer->setAddress($email_address, $user['first_name']);
            $mailer->setSubject($subject);
            $mailer->setMessage($mail_body);
            $mailer->send();
            return redirect(url_to_pager('admincp-mail-automations') . '?type=hight-success');
            break;
        case 'send-preview':
            $id = input('id');
            $email_address = get_user_data('email_address');
            $mailautomation = get_mailautomation($id);
            if ($mailautomation) {
                //lets
                $user = get_user();
                $active_lang = get_active_language();
                $subject = replaceMailAutomationStrings(get_phrase($active_lang, $mailautomation['subject']), $user, false);
                $mail_body = replaceMailAutomationStrings(get_phrase($active_lang, $mailautomation['body_content']), $user, true);
                $email_address = get_user_data('email_address', $user);
                $id = addNewAutomationStats($user['id'], $subject, $email_address);
                $mail_body .= "<img style='height:0;width:0' src='" . url_to_pager("automation-track") . '?sid=' . $id . "' />";
                $mailer = mailer();
                $mailer->setAddress($email_address, $user['first_name']);
                $mailer->setSubject($subject);
                $mailer->setMessage($mail_body);
                $mailer->send();
                //var_dump($fd);die();
                return redirect(url_to_pager('admincp-mail-automations') . '?type=preview-success');
            }
            break;
        case 'delete':
            $id = input('id');
            delete_mailautomation($id);
            return redirect_back();
            break;
        case 'edit':
            $id = input('id');
            $mailautomation = get_mailautomation($id);
            if (!$mailautomation) return redirect_back();
            $val = input('val', null, array('content'));
            if ($val) {
                CSRFProtection::validate();
                save_mailautomation($val, $mailautomation, true);
                return redirect_to_pager('admincp-mail-automations');

            }
            return $app->render(view('mailautomation::edit', array('a' => $mailautomation, 'message' => $message)));
            break;
        default:
            $ids = input('data');
            for ($i = 0; $i < count($ids); $i++) {
                update_help_category_order($ids[$i], $i);
            }
            break;
    }
}

function add_pager($app)
{
    $app->setTitle(lang('mailautomation::add-new-automation'));
    $message = null;
    $val = input('val', null, array('content'));
    if ($val) {
        CSRFProtection::validate();
        $validate = validator($val, array(
            'title' => 'required',
            'na_count' => 'required',
            'subject' => 'required',
            'mail_body' => 'required',
        ));
        //we are using default days

        if (validation_passes()) {
            add_mailautomation($val);
            return redirect_to_pager('admincp-mail-automations');
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view('mailautomation::add', array('message' => $message)));
}