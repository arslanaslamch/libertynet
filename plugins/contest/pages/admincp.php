<?php

get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-contests')->setActive();
function ajax_pager($app){
    $action = input('action');
    switch ($action){
        case 'featured-contest':
            $cid = input('cid');
            $v = input('v');
            $contest = get_contest($cid);
            if($contest){
                db()->query("UPDATE contests SET featured='{$v}' WHERE id='{$cid}'");
                return true;
            }
            break;
    }
}

function announcement_pager($app){
    $app->setTitle(lang("contest::manage-annoucement"));
    $action = input('action');
    if ($action == 'delete') {
        $aid = input('aid');
        $cid = sanitizeText(input('cid'));
        deleteContestAnn($aid, $cid, true);
        //$contest = get_contest($cid);
        return redirect(url_to_pager("admincp-contests-ann-manage"));
    }

    $val = input('val');
    $message = null;
    if ($val) {
        CSRFProtection::validate();
        $validate = validator($val, array(
            'headline' => 'required',
            'content' => 'required'
        ));
        if (validation_passes()) {
            //check contest and confirm the person is the contest owner
            $cid = sanitizeText($val['contest_id']);
            $contest = get_contest($cid);
            $type = 'update';
            if ($contest) {
                $a_id = saveContestAnnouncement($val);
                $message = lang("contest::announcement-saved");
            }
        } else {
            $message = validation_first();
        }
    }
    $ann = getContestAnnouncements();
    return $app->render(view("contest::admincp/announcement/lists",array('ann'=>$ann,'message'=>$message)));
}
function manage_entries_pager($app) {
    $app->setTitle('Manage Entries');
    $id = input('id');
    $action = input('action');
    $contest = get_contest($id);
    if(!$contest) return redirect_back();
    switch ($action){
        case 'edit':

            break;
        case 'delete':
            $entry = getContestEntry($contest,input('eid'));
            deleteContestEntry($contest,$entry);
            return redirect(url_to_pager("admincp-contests-entries-manage").'?id='.input('id'));
            break;
        default:
            $entries = getContestEntries($contest,10,input('sort'),input('term'));
            return $app->render(view('contest::admincp/entries/lists', array('contest'=>$contest,'entries' => $entries)));
            break;
    }
    return redirect_to_pager('admincp-contests');

}

function lists_pager($app) {
    $app->setTitle('Manage contests');
    return $app->render(view('contest::admincp/lists', array('contests' => admin_get_contests(input('term')))));
}


function manage_pager($app) {
    $action = input('action', 'order');
    $app->setTitle(lang('contest::manage-contests'));
    $message = null;
    switch($action) {
        case 'delete':
            $id = input('id');
            delete_contest($id);
            return redirect_back();
            break;
        case 'edit':
            $id = input('id');
            $contest = get_contest($id);
            if(!$contest) return redirect_back();
            $val = input('val', null, array('description'));
            if ($val) {
                CSRFProtection::validate();
                $validate = validator($val, array(
                    'category' => 'required',
                    'name' => 'required',
                    'description' => 'required',
                    'type' => 'required',
                    'award' => 'required',
                    'terms' => 'required',
                    'contest_start' => 'required',
                    'contest_end' => 'required',
                    'entries_start' => 'required',
                    'entries_end' => 'required',
                    'voting_start' => 'required',
                    'voting_end' => 'required',
                    'auto_approve' => 'required',
                ));
                if (validation_passes()) {
                    $check = true;
                    $contest_start = convertContestTime($val['contest_start']);
                    $val['contest_start'] = $contest_start;
                    $contest_end = convertContestTime($val['contest_end']);
                    $val['contest_end'] = $contest_end;

                    $entries_start = convertContestTime($val['entries_start']);
                    $val['entries_start'] = $entries_start;
                    $entries_end = convertContestTime($val['entries_end']);
                    $val['entries_end'] = $entries_end;

                    $voting_start = convertContestTime($val['voting_start']);
                    $val['voting_start'] = $voting_start;
                    $voting_end = convertContestTime($val['voting_end']);
                    $val['voting_end'] = $voting_end;

                    $time = time();
                    if (($contest_start >= $contest_end)) {
                        $message = lang("contest::invalid-contest-duration");
                        $check = false;
                    }

                    if (($entries_start >= $entries_end) || ($entries_end > $contest_end) || ($entries_start < $contest_start)) {
                        $message = lang("contest::invalid-entries-duration");
                        $check = false;
                    }

                    if (($voting_start >= $voting_end) || ($voting_end > $contest_end) || ($voting_start < $contest_start)) {
                        $message = lang("contest::invalid-voting-duration");
                        $check = false;
                    }
                    if ($check) {
                        $contest_id = save_contest($val, 'update',$contest);
                        return redirect_to_pager('admincp-contests');
                    }
                } else {
                    $message = validation_first();
                }
            }
            return $app->render(view('contest::admincp/edit', array('message'=>$message, 'contest' => $contest)));
            break;
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_help_category_order($ids[$i], $i);
            }
            break;
    }
}

function add_pager($app) {
    $app->setTitle(lang('contest::add-new-contest'));
    $message = null;
    $val = input('val', null, array('description'));
    if ($val) {
        CSRFProtection::validate();
        $validate = validator($val, array(
            'category' => 'required',
            'name' => 'required',
            'description' => 'required',
            'type' => 'required',
            'award' => 'required',
            'terms' => 'required',
            'contest_start' => 'required',
            'contest_end' => 'required',
            'entries_start' => 'required',
            'entries_end' => 'required',
            'voting_start' => 'required',
            'voting_end' => 'required',
            'auto_approve' => 'required',
        ));
        if (validation_passes()) {
            $check = true;
            $contest_start = convertContestTime($val['contest_start']);
            $val['contest_start'] = $contest_start;
            $contest_end = convertContestTime($val['contest_end']);
            $val['contest_end'] = $contest_end;

            $entries_start = convertContestTime($val['entries_start']);
            $val['entries_start'] = $entries_start;
            $entries_end = convertContestTime($val['entries_end']);
            $val['entries_end'] = $entries_end;

            $voting_start = convertContestTime($val['voting_start']);
            $val['voting_start'] = $voting_start;
            $voting_end = convertContestTime($val['voting_end']);
            $val['voting_end'] = $voting_end;

            $time = time();
            if (($contest_start >= $contest_end) || ($time > $contest_end)) {
                $message = lang("contest::invalid-contest-duration");
                $check = false;
            }

            if (($entries_start >= $entries_end) || ($time > $entries_end) || ($entries_end > $contest_end) || ($entries_start < $contest_start)) {
                $message = lang("contest::invalid-entries-duration");
                $check = false;
            }

            if (($voting_start >= $voting_end) || ($time > $voting_end) || ($voting_end > $contest_end) || ($voting_start < $contest_start)) {
                $message = lang("contest::invalid-voting-duration");
                $check = false;
            }
            if ($check) {
                save_contest($val, 'new');
                return redirect_to_pager('admincp-contests');
            }
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view('contest::admincp/add', array('message' => $message)));
}

function categories_pager($app) {
    $app->setTitle(lang('contest::manage-categories'));
    $categories = (input('id')) ? get_contest_parent_categories(input('id')) : get_contest_categories();
    return $app->render(view('contest::admincp/categories/lists', array('categories' => $categories)));
}

function categories_add_pager($app) {
    $app->setTitle(lang('contest::add-category'));
    $message = null;

    $val = input('val');
    if($val) {
        CSRFProtection::validate();
        contest_add_category($val);
        return redirect_to_pager('admincp-contest-categories');
        //redirect to category lists
    }

    return $app->render(view('contest::admincp/categories/add', array('message' => $message)));
}

function manage_category_pager($app) {
    $action = input('action', 'order');
    $id = input('id');
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                update_contest_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('contest::edit-category'));
            $category = get_contest_category($id);
            if(!$category) return redirect_to_pager('admincp-contest-categories');
            if($val) {
                CSRFProtection::validate();
                save_contest_category($val, $category);
                return redirect_to_pager('admincp-contest-categories');
                //redirect to category lists
            }
            return $app->render(view('contest::admincp/categories/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = get_contest_category($id);
            if(!$category) return redirect_to_pager('admincp-contest-categories');
            delete_contest_category($id, $category);
            return redirect_to_pager('admincp-contest-categories');
            break;
    }
    return $app->render();
}