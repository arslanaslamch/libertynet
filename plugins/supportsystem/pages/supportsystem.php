<?php

function search_pager($app){
    $app->setTitle(lang("supportsystem::search"));
    $term = input('term');
    $articles = ss_get_articles('admin',0,'all',$term);
    return $app->render(view("supportsystem::search",array('articles'=>$articles)));
}

function view_article_pager($app){
    $article_id = segment(2);
    $article = ss_get_articles('single',$article_id);
    if(!$article) return redirect_back();
    $app->setTitle($article['subject']);
    return $app->render(view("supportsystem::article-view",array('article'=>$article)));
}

function categories_pager($app){
    $category_id = segment(2);
    if($category_id){
        $category = get_ss_category($category_id);
        if(!$category) return redirect_back();
        $articles = ss_get_articles('category',$category_id,'all');
        $app->setTitle(lang($category['title']));
        return $app->render(view("supportsystem::category",array('articles'=>$articles,'category'=>$category)));
    }
    return redirect_back();
}

function ajax_pager()
{
    $action = input("action");
    switch ($action) {
        case 'article-feedback':
            $type = input('type'); //1 is helpful and 0 is not helpful
            $aid = input('aid');
            $saved_name = 'ss_articles';
            $stored = session_get($saved_name,array());
            $stored[] = $aid;
            session_put($saved_name,$stored);
            ss_increase_faq_feedback($aid,$type);
            return true;
            break;
        case 'close-ticket':
            $from = input('from');
            $result = array(
                'msg' => lang("supportsystem::something-has-gone-wrong-on-our-part"),
                'url' => url(),
                'status' => 0
            );
            if (!is_loggedIn()) return json_encode($result);
            $tid = input('tid');
            $ticket = ss_get_ticket($tid);
            if (!$ticket) return json_encode($result);
            if (!ss_can_view_ticket($ticket)) return json_encode($result);
            ss_close_ticket($tid);
            $url = url_to_pager("supportsystem-my-ticket-page");
            if($ticket['user_id'] != get_userid()) $url = url_to_pager("supportsystem-moderate-ticket-page");
            if($from == 'admin') $url =  url_to_pager("admincp-support-system-tickets");
            $result['url'] = $url;
            $result['status'] = 1;
            return json_encode($result);
            break;
    }
}

function reply_pager($app)
{
    $result = array(
        'status' => 0,
        'msg' => '',
        'view' => '',
    );
    $val = input('val');
    if ($val) {
        CSRFProtection::validate();
        $validate = validator($val, array(
            'content' => 'required',
        ));
        $f = input_file('file');
        if ($val['content'] != "" || ($f && (isset($f[0]['name']) && $f[0]['name'] != ''))) {
            $rid = ss_save_ticket_reply($val);
            if ($rid) {
                $reply = ss_get_ticket_reply($rid);
                $result['status'] = 1;
                $result['msg'] = lang("supportsystem::reply-submitted-successfully");
                $result['view'] = view("supportsystem::ticket/reply/each", array('reply' => $reply));
            } else {
                $result['msg'] = lang('supportsystem::unable-to-process-reply');
            }
        } else {
            $result['msg'] = lang("supportsystem::file-or-content-required");
        }
    }
    return json_encode($result);
}

function download_pager($app)
{
    $item_id = input('rid');
    $ctype = input('ctype');
    if ($item_id) {
        $file_id = input('file_id');
        $files = array();
        if ($ctype == 'ticket') {
            $item = ss_get_ticket($item_id);
            if ($item) {
                if (!ss_can_view_ticket($item)) return false;
                $files = ($item['files']) ? perfectUnserialize($item['files']) : array();
            }
        }
        if ($ctype == 'reply') {
            $item = ss_get_ticket_reply($item_id);
            if ($item) {
                if (!ss_can_view_ticket($item)) return false;
                $files = ($item['files']) ? perfectUnserialize($item['files']) : array();
            }
        }
        //$feed = ss_get_ticket_reply($feed_id);
        $file = isset($files[$file_id]) ? $files[$file_id] : array();
        return download_file($file['path'], $file['name']);
    }

}

function view_pager($app)
{
    $ticket_id = segment(2);
    $ticket = ss_get_ticket($ticket_id);
    if (!$ticket) return redirect_back();
    if (!ss_can_view_ticket($ticket)) return redirect_back();
    $app->setTitle($ticket['subject']);
    $replies = ss_ticket_replies($ticket_id);
    return $app->render(view("supportsystem::ticket/view", array('ticket' => $ticket, 'replies' => $replies)));
}

function ticket_moderate_pager($app){
    $app->setTitle(lang("supportsystem::manage-members-tickets"));
    $term = input('term');
    $type = input('type','all');
    $limit = 10;
    $tickets = get_support_tickets('moderator', null, $term, $type, $limit);
    return $app->render(view("supportsystem::ticket/moderate", array('tickets' => $tickets)));
}

function my_tickets_pager($app)
{
    $app->setTitle(lang("supportsystem::my-tickets"));
    $term = input('term');
    $type = input('type');
    $limit = 10;
    $tickets = get_support_tickets('mine', null, $term, $type, $limit);
    return $app->render(view("supportsystem::ticket/mine", array('tickets' => $tickets)));
}

function dashboard_pager($app)
{
    $app->setTitle(lang("supportsystem::support-system"));
    return $app->render(view("supportsystem::index"));
}

function create_pager($app)
{
    $app->setTitle(lang("supportsystem::submit-a-ticket"));
    $status = 0;
    $message = '';
    $redirect_url = '';

    $val = input('val', null, array('content'));
    if (user_has_permission('can-add-support-ticket', true)) {
        if ($val) {
            CSRFProtection::validate();
            $validate = validator($val, array(
                'category' => 'required',
                'subject' => 'required',
                'content' => 'required'
            ));
            if (validation_passes()) {
                $ticket_response = save_support_ticket($val, 'new');
                if (!is_array($ticket_response)) {
                    $status = 1;
                    $message = lang('supportsystem::ticket-submitted-successfully');
                    $redirect_url = url_to_pager('supportsystem-my-ticket-page');
                } else {
                    $message = $ticket_response['message'];
                }
            } else {
                $message = validation_first();
            }
        }
    } else {
        $message = lang('supportsystem::supportsystem-add-permission-denied');
        $redirect_url = url_to_pager('supportsystem-dashboard-page');
    }

    if (input('ajax')) {
        $result = array(
            'status' => (int)$status,
            'message' => (string)$message,
            'redirect_url' => (string)$redirect_url,
        );
        $response = json_encode($result);
        return $response;
    }

    if ($redirect_url) {
        return redirect($redirect_url);
    }

    return $app->render(view("supportsystem::ticket/add", array('message' => $message)));
}