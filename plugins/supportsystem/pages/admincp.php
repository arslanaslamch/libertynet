<?php
get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->setActive();
function departements_pager($app) {
    $app->setTitle(lang('supportsystem::manage-departments'));
    $type = input('type','ticket');
    $categories = (input('id')) ? get_ss_parent_categories(input('id'),$type) : get_ss_categories($type);
    $title = ($type == 'ticket') ? lang("supportsystem::manage-ticket-departments") : lang("supportsystem::manage-faq-categories");
    return $app->render(view('supportsystem::admincp/departments/list', array('categories' => $categories,'title'=>$title,'type'=>$type)));
}

function departments_add_pager($app) {
    $app->setTitle(lang('add'));
    $message = null;
    $val = input('val');
    $type = input('type','ticket');
    if($val) {
        CSRFProtection::validate();
        $val['type'] = $type;
        ss_add_category($val);
        return redirect(url_to_pager('admincp-list-tickets-departments').'?type='.$type);
    }
    $title = ($type == 'ticket') ? lang("supportsystem::add-ticket-department") :  lang("supportsystem::add-faq-categories");
    return $app->render(view('supportsystem::admincp/departments/add', array('message' => $message,'type'=>$type,'title'=>$title)));
}

function manage_departments_pager($app) {
    $action = input('action', 'order');
    $id = input('id');
    $type = input('type','ticket');
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_ss_category_order($ids[$i], $i,$type);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('supportsystem::manage'));
            $category = get_ss_category($id);
            if(!$category) return redirect(url_to_pager('admincp-list-tickets-departments').'?type='.$type);
            if($val) {
                CSRFProtection::validate();
                save_ss_category($val, $category);
                return redirect(url_to_pager('admincp-list-tickets-departments').'?type='.$type);
            }
            return $app->render(view('supportsystem::admincp/departments/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = get_ss_category($id);
            if(!$category) return redirect(url_to_pager('admincp-list-tickets-departments').'?type='.$type);
            delete_ss_category($id, $category);
            return redirect(url_to_pager('admincp-list-tickets-departments').'?type='.$type);
            break;
    }
    return $app->render();
}

//articles
function articles_pager($app){
    $app->setTitle(lang("supportsystem::manage-articles"));
    $type = input("type","list");
    $val = input('val',null, array('content'));
    $content = "";
    $message = "";
    if($val){
        //we are either editing or creating a new one
        CSRFProtection::validate();
        $validate = validator($val, array(
            'title' => 'required',
            'content' => 'required'
        ));
        if(validation_passes()) {
            $val['id'] = input('id',0);
            ss_save_article($val,$type);
            return redirect(url_to_pager("admincp-support-system-articles"));
        } else {
            $message = validation_first();
        }
    }
    if($type == 'list'){
        $articles = ss_get_articles('admin');
        $content = view("supportsystem::admincp/articles/lists",array('articles'=>$articles));
    }
    if($type == 'add') $content = view("supportsystem::admincp/articles/add",array("message"=>$message));
    if($type == 'manage'){
        $id = input('id');
        if(input('action') == 'delete'){
            ss_article_delete($id);
            return redirect(url_to_pager("admincp-support-system-articles"));
        }
        $article = ss_get_articles('single',$id);
        $content = view("supportsystem::admincp/articles/edit",array('message'=>$message,'article'=>$article));
    }
    return $app->render($content);
}

//moderators
function moderators_pager($app){
    $app->setTitle(lang("supportsystem::ticket-moderators"));
    $moderators = ss_get_ticket_moderators('admin');
    $val = input('val');
    if($val){
       if(input('action') == 'delete'){
           $uid = input('id');
           ss_remove_moderator($uid);
           return redirect(url_to_pager("admincp-support-system-moderator"));
       }
       //we are adding
        if($val['selected']){
            ss_add_moderator($val);
            return redirect(url_to_pager("admincp-support-system-moderator"));
        }
    }
    return $app->render(view("supportsystem::admincp/moderators/index",array('moderators'=>$moderators)));
}

//tickets
function tickets_pager($app){
    $app->setTitle(lang("supportsystem::manage-tickets"));
    $ticket_id = input('id');
    if($ticket_id){
        //we are viewing a single ticket
        $ticket = ss_get_ticket($ticket_id);
        if (!$ticket) return redirect_back();
        if (!ss_can_view_ticket($ticket)) return redirect_back();
        $app->setTitle($ticket['subject']);
        $replies = ss_ticket_replies($ticket_id);
        return $app->render(view("supportsystem::admincp/tickets/view", array('ticket' => $ticket, 'replies' => $replies)));
    }
    $term = input('term');
    $type = input('type','all');
    $limit = 10;
    $tickets = get_support_tickets('admincp', null, $term, $type, $limit);
    return $app->render(view("supportsystem::admincp/tickets/lists",array('tickets'=>$tickets)));
}

//dashboard
function dashboard_pager($app){
    $app->setTitle(lang("supportsystem::dashboard") );
    return $app->render(view("supportsystem::admincp/tickets/dashboard"));
}