<?php

function my_entries_pager($app){
    $app->setTitle(lang("contest::my-entries"));
    $term = input('term');
    $type = input('ctype','blog');
    $method = input('method','mine');
    $title = lang('contest::my-entries');
    if($method == 'recent'){
        $title = lang("contest::recent-entries");
    }
    $entries = getMyContests($type, $method, null,null,10);
    return $app->render(view("contest::my-entries",array('entries'=>$entries,'title'=>$title)));
}

function promote_pager($app)
{
    $contest_id = sanitizeText(input('contest_id'));
    $img = sanitizeText(input('img', 1));
    $desc = sanitizeText(input('desc', 1));
    $contest = get_contest($contest_id);
    if (!$contest) return false;
    return view("contest::promote-view-ext", array('contest' => $contest, 'desc' => $desc, 'img' => $img));
}

function announcement_pager($app)
{

    $status = 0;
    $message = '';
    $redirect_url = '';

    $val = input('val', null, array('content'));

    $action = input('action');
    if ($action == 'del') {
        $aid = input('aid');
        $cid = sanitizeText(input('cid'));
        deleteContestAnn($aid, $cid);
        //$contest = get_contest($cid);
        return redirect_back();
    }
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
            $type = sanitizeText($val['atype']);
            //print_r($contest);die();
            if ($contest and is_contest_owner($contest)) {
                $a_id = saveContestAnnouncement($val);
                if ($a_id) {
                    $status = 1;
                    $message = ($type == 'new') ? lang('contest::announcement-add-successfully') : lang("contest::announcement-saved");
                    $redirect_url = contestUrl($contest);
                } else {
                    $message = lang('contest::announce-error');
                }
            }
        } else {
            $message = validation_first();
        }
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

    return redirect(url('contests'));
}

function entries_pager($app)
{
    $contest_slug = segment(1);
    $contest = get_contest($contest_slug);
    if (!$contest) return redirect(url('contests'));
    app()->leftMenu = false;
    $app->setTitle($contest['name']);
    $limit = 10;
    $filter = input('filter', 'latest');
    $term = input('term', null);
    $entries = getContestEntries($contest, $limit, $filter, $term);
    $view = getContestEntryView($contest, 'display', $entries, true);
    $content = view("contest::layout", array('contest' => $contest, 'content' => $view));
    return $app->render($content);
}

function display_entry_pager($app)
{
    $contest_slug = segment(1);
    $entry_slug = segment(3);
    app()->leftMenu = false;
    $contest = get_contest($contest_slug);
    if (!$contest) return redirect(url('contests'));
    $entry = getContestEntry($contest, $entry_slug);
    if (!$entry) return redirect(contestUrl($contest));
    $app->setTitle($contest['name']);
    //let us handle edit and delelete of entry
    if (is_contest_owner($entry)) {
        $action = input('action');
        switch ($action) {
            case 'edit':
                $status = 0;
                $message = '';
                $redirect_url = '';
                switch ($contest['type']) {
                    case 'music':
                        $val = input("val", null, array('code'));
                        if ($val) {
                            $val['id'] = $entry['id'];
                            $val['file_path'] = $entry['file_path'];
                            $musicFile = input_file('music_file');
                            if ($musicFile) {
                                $validator = validator($val, array(
                                    'title' => 'required',
                                ));
                                if (validation_fails()) {
                                    $message = validation_first();
                                } else {
                                    $uploader = new Uploader($musicFile, 'audio');
                                    if ($uploader->passed()) {
                                        $added = save_music_contest($val, 'update');
                                        if ($added) {
                                            $status = 1;
                                            $message = lang('contest::entry-saved-successfully');
                                            $entry = get_contest_music($added);
                                            $redirect_url = getEntryContestUrl($contest, $entry);
                                        } else {
                                            $message = lang('contest::error-occurred');
                                        }
                                    } else {
                                        $message = $uploader->getError();
                                    }
                                }
                            } else {
                                //it is iframe code
                                //let us check
                                if ($val['code'] == '') {
                                    //let us now check if file_path is not empty
                                    if ($val['file_path']) {
                                        //he is just updating maybe title or description of music
                                        $validator = validator($val, array(
                                            'title' => 'required',
                                        ));
                                        if (validation_fails()) {
                                            $message = validation_first();
                                        } else {
                                            $val['source'] = 'upload';
                                            $added = save_music_contest($val, 'update');
                                            $status = 1;
                                            $message = lang('contest::entry-saved-successfully');
                                            $entry = get_contest_music($added);
                                            $redirect_url = getEntryContestUrl($contest, $entry);
                                        }
                                    }
                                    $message = lang("contest::music-file-and-music-external-source-cannot-be-empty");
                                } else {
                                    //the iframe code is not empty niye
                                    $validator = validator($val, array(
                                        'title' => 'required',
                                    ));
                                    if (validation_fails()) {
                                        $message = validation_first();
                                    } else {
                                        $added = save_music_contest($val, 'update');
                                        if ($added) {
                                            $status = 1;
                                            $message = lang('contest::entry-saved-successfully');
                                            $entry = get_contest_music($added);
                                            $redirect_url = getEntryContestUrl($contest, $entry);
                                        } else {
                                            $message = lang('contest::error-occurred');
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'video':
                        $val = input("val");

                        if ($val) {
                            CSRFProtection::validate();
                            $val['file_path'] = $entry['file_path'];
                            $val['id'] = $entry['id'];
                            //check for video files
                            $video_file = input_file('video_file');
                            if ($video_file) {
                                $validator = validator($val, array(
                                    'title' => 'required',
                                ));
                                if (validation_fails()) {
                                    $message = validation_first();
                                } else {
                                    $uploader = new Uploader($video_file, 'video');
                                    if ($uploader->passed()) {
                                        $val['contest_id'] = $contest['id'];
                                        $added = save_video_contest($val, 'update');
                                        if ($added) {
                                            $status = 1;
                                            $message = lang('contest::entry-saved-successfully');
                                            $entry = get_contest_video($added);
                                            $redirect_url = getEntryContestUrl($contest, $entry);
                                        } else {
                                            $message = lang('video::video-add-error-message');
                                        }
                                    } else {
                                        $message = $uploader->getError();
                                    }
                                }
                            } else {
                                /**
                                 * @var $link
                                 */

                                $link = input('val.link');
                                if ($link) {
                                    $link_details = feed_process_link($link);
                                    if (($link_details && ($link_details['type'] == 'video') || is_youtube_video($link))) {
                                        $val['title'] = ($val['title']) ? $val['title'] : mysqli_real_escape_string(db(), sanitizeText($link_details['title']));
                                        $val['description'] = ($val['description']) ? $val['description'] : mysqli_real_escape_string(db(), sanitizeText($link_details['description']));
                                        $val['code'] = $link_details['code'];
                                        $val['photo_path'] = $link_details['image'];
                                        $val['contest_id'] = $contest['id'];
                                        $added = save_video_contest($val, 'update');
                                        if ($added) {
                                            $status = 1;
                                            $message = lang('contest::entry-saved-successfully');
                                            $entry = get_contest_video($added);
                                            $redirect_url = getEntryContestUrl($contest, $entry);
                                        } else {
                                            $message = lang('contest::video-add-error-message');
                                        }
                                    } else {
                                        $message = lang('video::video-not-found-in-link');
                                    }
                                } else {
                                    $message = lang("contest::video-not-detected-check-settings");
                                }
                            }

                            //save if file path is not empty, file was already uploaded,
                            //only title or description changes
                            if ($entry['file_path']) {
                                $added = save_video_contest($val, 'update');
                                if ($added) {
                                    $status = 1;
                                    $message = lang('contest::entry-saved-successfully');
                                    $entry = get_contest_video($added);
                                    $redirect_url = getEntryContestUrl($contest, $entry);
                                } else {
                                    $message = lang('contest::video-add-error-message');
                                }
                            }

                        }
                        break;
                    case 'blog':
                        $val = input('val');

                        if ($val) {
                            CSRFProtection::validate();
                            $validate = validator($val, array(
                                'title' => 'required',
                                'content' => 'required'
                            ));
                            if (validation_passes()) {
                                $val['contest_id'] = $contest['id'];
                                $blog_id = save_contest_blog($val, 'edit', $entry);
                                if ($blog_id) {
                                    $status = 1;
                                    $message = lang('contest::entry-updated-successfully');
                                    $blog = get_contest_blog($blog_id);
                                    $redirect_url = getEntryContestUrl($contest, $blog);
                                } else {
                                    $message = lang('blog::blog-add-error');
                                }
                            } else {
                                $message = validation_first();
                            }
                        }
                        break;
                    case 'photo':
                        $val = input('val', null, array('photo_url'));
                        if ($val) {
                            CSRFProtection::validate();
                            $photo_url = $val['photo_url'];
                            $url_error = false;
                            $image = $entry['image'];
                            if ($photo_url) {
                                //he wants to upload from url
                                $check = url_is_image($photo_url);
                                if ($check) {
                                    $random = md5(get_user_data('username') . time() . rand(50, 100));
                                    $extension = $match = preg_replace("#(.+)?\.(\w+)(\?.+)?#", "$2", $photo_url);
                                    $temp_dir = config('temp-dir', path('storage/tmp'));
                                    if (!is_dir($temp_dir)) {
                                        @mkdir($temp_dir, 0777, true);
                                    }
                                    $file_path = $temp_dir . '/' . $random . '.' . $extension;
                                    file_put_contents($file_path, file_get_contents($photo_url));
                                    $user_id = get_userid();
                                    $uploader = new Uploader($file_path, 'image', false, true);
                                    $privacy = 1;
                                    $uploader->setPath($user_id . '/' . date('Y') . '/photos/contests/');
                                    if ($uploader->passed()) {
                                        $image = $uploader->resize()->toDB("user-posts", get_userid(), 1)->result();
                                    } else {
                                        $message = lang("contest::image-upload-error");
                                        $url_error = true;
                                    }
                                } else {
                                    $message = lang("contest::invalid-image-link");
                                    $url_error = true;
                                }
                            }

                            if (!$photo_url) {
                                //we have uploaded not upload from link, let us check file from computer
                                $file = input_file('photos');
                                if ($file) {
                                    $uploader = new Uploader($file);
                                    if ($uploader->passed()) {
                                        $uploader->setPath('blogs/preview/');
                                        $image = $uploader->resize(700, 500)->result();
                                    } else {
                                        $message = lang("contest::image-upload-error");
                                    }
                                } else {
                                    $message = lang("contest::no-photo-selected") . $photo_url;
                                }
                            }

                            if ($image) {
                                $message = lang("contest::photo-update-successful");
                                $val['image'] = $image;
                                $val['contest_id'] = $contest['id'];
                                $val['id'] = $entry['id'];
                                $photoid = saveContestPhoto($val, 'update');
                                $entry = get_contest_photo($photoid);
                                $redirect_url = getEntryContestUrl($contest, $entry);
                                $status = 1;
                            }

                        }
                        break;
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
                $view = getContestEntryView($contest, 'add', $entry);
                $view = view("contest::entry/index", array('view' => $view, 'contest' => $contest));
                $content = view("contest::layout", array('contest' => $contest, 'content' => $view));
                return $app->render($content);

                break;
            case 'del' :
                //delete again
                deleteContestEntry($contest, $entry);
                return redirect(contestUrl($contest));
                break;
        }
    }
    //let us display now
    $app->setTitle($contest['name']);
    //update views
    entryViewsUpdate($contest, $entry);
    $view = getContestEntryView($contest, 'display', $entry);
    $view = view("contest::entry/display/index", array('view' => $view, 'contest' => $contest, 'entry' => $entry));
    $content = view("contest::layout", array('contest' => $contest, 'content' => $view));
    return $app->render($content);

}

function submit_entry_pager($app)
{
    $app->setTitle(lang("contest::submit-entry"));
    app()->leftMenu = false;
    $slug = segment(1);
    $contest = get_contest($slug);
    if (!$contest) return redirect(url('contests'));
    if (!canJoinContest($contest)) return redirect(url('contests'));
    if (!isContestParticipant($contest)) return redirect(url('contests'));

    //check if submission has commenced
    if($contest['contest_start'] > time() or $contest['entries_start'] > time()){
        return $app->render(view("contest::contest-time-end",array('start'=>true,'contest'=>$contest)));
    }
    //check if entry is closed or open
    $nowTime = time();
    if($nowTime > $contest['contest_end'] || $nowTime > $contest['entries_end']){
        return $app->render(view("contest::contest-time-end",array('c_end'=>true,'contest'=>$contest)));
    }

    //check the number of entries submission allowed foreach user
    $current_count = contestCountMyExistingEntries($contest);
    if($current_count >= $contest['max_entries']){
        return $app->render(view("contest::max-entries",array('max'=>$contest['max_entries'],'contest'=>$contest)));
    }
    $status = 0;
    $message = '';
    $redirect_url = '';
    switch ($contest['type']) {
        case 'music':
            $val = input("val", null, array('code'));
            if ($val) {
                $musicFile = input_file('music_file');
                $val['contest_id'] = $contest['id'];
                if ($musicFile) {
                    $validator = validator($val, array(
                        'title' => 'required',
                    ));
                    if (validation_fails()) {
                        $message = validation_first();
                    } else {
                        $uploader = new Uploader($musicFile, 'audio');
                        if ($uploader->passed()) {
                            $added = save_music_contest($val, 'fresh');
                            if ($added) {
                                $status = 1;
                                $message = lang('contest::entry-saved-successfully');
                                $entry = get_contest_music($added);
                                $redirect_url = getEntryContestUrl($contest, $entry);
                            } else {
                                $message = lang('contest::error-occurred');
                            }
                        } else {
                            $message = $uploader->getError();
                        }
                    }
                } else {
                    //it is iframe code
                    //let us check
                    if ($val['code'] == '') {
                        $message = lang("contest::music-file-and-music-external-source-cannot-be-empty");
                    } else {
                        //the iframe code is not empty niye
                        $validator = validator($val, array(
                            'title' => 'required',
                        ));
                        if (validation_fails()) {
                            $message = validation_first();
                        } else {
                            $added = save_music_contest($val, 'fresh');
                            if ($added) {
                                $status = 1;
                                $message = lang('contest::entry-saved-successfully');
                                $entry = get_contest_music($added);
                                $redirect_url = getEntryContestUrl($contest, $entry);
                            } else {
                                $message = lang('contest::error-occurred');
                            }
                        }
                    }
                }
            }
            break;
        case 'video':
            $val = input("val");
            if ($val) {
                CSRFProtection::validate();
                //check for video files
                $val['contest_id'] = $contest['id'];
                $video_file = input_file('video_file');
                if ($video_file) {
                    $validator = validator($val, array(
                        'title' => 'required',
                    ));
                    if (validation_fails()) {
                        $message = validation_first();
                    } else {
                        $uploader = new Uploader($video_file, 'video');
                        if ($uploader->passed()) {
                            $val['contest_id'] = $contest['id'];
                            $added = save_video_contest($val, 'fresh');
                            if ($added) {
                                $status = 1;
                                $message = lang('contest::entry-saved-successfully');
                                $entry = get_contest_video($added);
                                $redirect_url = getEntryContestUrl($contest, $entry);
                            } else {
                                $message = lang('video::video-add-error-message');
                            }
                        } else {
                            $message = $uploader->getError();
                        }
                    }
                } else {
                    /**
                     * @var $link
                     */
                    $link = input('val.link');
                    if ($link) {


                        $link_details = feed_process_link($link);
                        if (($link_details && ($link_details['type'] == 'video') || is_youtube_video($link))) {
                            $val['title'] = ($val['title']) ? $val['title'] : mysqli_real_escape_string(db(), sanitizeText($link_details['title']));
                            $val['description'] = ($val['description']) ? $val['description'] : mysqli_real_escape_string(db(), sanitizeText($link_details['description']));
                            $val['code'] = $link_details['code'];
                            $val['photo_path'] = $link_details['image'];
                            $val['contest_id'] = $contest['id'];
                            $added = save_video_contest($val, 'fresh');
                            if ($added) {
                                $status = 1;
                                $message = lang('contest::entry-saved-successfully');
                                $entry = get_contest_video($added);
                                $redirect_url = getEntryContestUrl($contest, $entry);
                            } else {
                                $message = lang('contest::video-add-error-message');
                            }
                        } else {
                            $message = lang('video::video-not-found-in-link');
                        }
                    } else {
                        $message = lang("contest::video-not-detected-check-settings");
                    }
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
            }
            break;
        case 'blog':
            $val = input('val', null, array('content'));
            if ($val) {
                CSRFProtection::validate();
                $validate = validator($val, array(
                    'title' => 'required',
                    'content' => 'required'
                ));
                if (validation_passes()) {
                    $val['contest_id'] = $contest['id'];
                    $blog_id = save_contest_blog($val);
                    if ($blog_id) {
                        $status = 1;
                        $message = lang('contest::entry-submitted-successfully');
                        $blog = get_contest_blog($blog_id);
                        $redirect_url = getEntryContestUrl($contest, $blog);
                    } else {
                        $message = lang('contest::entry-add-error');
                    }
                } else {
                    $message = validation_first();
                }
            }
            break;
        case 'photo':
            $val = input('val', null, array('photo_url'));
            if ($val) {
                CSRFProtection::validate();
                $photo_url = $val['photo_url'];
                $url_error = false;
                $image = '';
                if ($photo_url) {
                    //he wants to upload from url
                    $check = url_is_image($photo_url);
                    if ($check) {
                        $random = md5(get_user_data('username') . time() . rand(50, 100));
                        $extension = $match = preg_replace("#(.+)?\.(\w+)(\?.+)?#", "$2", $photo_url);
                        $temp_dir = config('temp-dir', path('storage/tmp'));
                        if (!is_dir($temp_dir)) {
                            @mkdir($temp_dir, 0777, true);
                        }
                        $file_path = $temp_dir . '/' . $random . '.' . $extension;
                        file_put_contents($file_path, file_get_contents($photo_url));
                        $user_id = get_userid();
                        $uploader = new Uploader($file_path, 'image', false, true);
                        $privacy = 1;
                        $uploader->setPath($user_id . '/' . date('Y') . '/photos/contests/');
                        if ($uploader->passed()) {
                            $image = $uploader->resize()->toDB("user-posts", get_userid(), 1)->result();
                        } else {
                            $message = lang("contest::image-upload-error");
                            $url_error = true;
                        }
                    } else {
                        $message = lang("contest::invalid-image-link");
                        $url_error = true;
                    }
                }

                if (!$photo_url) {
                    //we have uploaded not upload from link, let us check file from computer
                    $file = input_file('photos');
                    if ($file) {
                        $uploader = new Uploader($file);
                        if ($uploader->passed()) {
                            $uploader->setPath('blogs/preview/');
                            $image = $uploader->resize(700, 500)->result();
                        } else {
                            $message = lang("contest::image-upload-error");
                        }
                    } else {
                        $message = lang("contest::no-photo-selected") . $photo_url;
                    }
                }

                if ($image) {
                    $val['image'] = $image;
                    $val['contest_id'] = $contest['id'];
                    $photoid = saveContestPhoto($val, 'fresh');
                    $entry = get_contest_photo($photoid);
                    $redirect_url = getEntryContestUrl($contest, $entry);
                    $status = 1;
                }

            }
            break;
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


    $view = getContestEntryView($contest);
    $view = view("contest::entry/index", array('view' => $view, 'contest' => $contest));
    $content = view("contest::layout", array('contest' => $contest, 'content' => $view));
    return $app->render($content);
}

function ajax_pager($app)
{
    $action = input('action');
    switch ($action) {
        case 'promote-contest':
            $id = sanitizeText(input('cid'));
            $contest = get_contest($id);
            if (!$contest) return false;
            $view = view("contest::promote-view", array('contest' => $contest));
            return json_encode(array('view' => $view));
            break;
        case 'vote-entry':
            if (!is_loggedIn()) return false;
            $contest_id = sanitizeText(input('cid'));
            $contest = get_contest($contest_id);
            if(!$contest) return false;
            if(time() > $contest['voting_end']) return false;
            $voter = get_userid();
            $entry_type = sanitizeText(input('etype'));
            $entry_id = sanitizeText(input('eid'));
            $status = input('status');
            if ($status == 0) {
                contestVoterRemove($entry_type, $entry_id, $voter);
                contestVoterRegister($contest_id, $entry_type, $entry_id, $voter);
                $msg = lang("contest::entry-voted-successfully");
            }

            if ($status == 1) {
                contestVoterRemove($entry_type, $entry_id, $voter);
                $msg = lang("contest::you-have-unvoted-this-entry");
            }

            $votes = contestEntryVoteCount($entry_type, $entry_id);
            return json_encode(array('votes' => $votes, 'msg' => $msg));
            break;
        case 'join-contest':
            if (!is_loggedIn()) return false;
            $id = sanitizeText(input('id'));
            $contest = get_contest($id);
            if (!$contest) return false;
            $url = contestUrl($contest);
            joinContest($contest);
            return json_encode(array('url' => $url, 'msg' => lang('contest::you-have-joined-contest-successfully')));
            break;
        case 'invite':
            $val = input('val');
            $id = input('id');

            $status = 0;
            $message = '';
            $redirect_url = '';
            $contest = get_contest($id);
            if ($val) {
                //let us check if there are friends and send them notification
                if (is_loggedIn()) {
                    if ($val['friends']) {
                        $friends = $val['friends'];
                        //$friends[] = get_userid();
                        foreach ($friends as $k => $uid) {
                            //send notification to reach users
                            send_notification_privacy('notify-site-tag-you', $uid, 'contest.invite', $id);
                        }
                        $message = lang("contest::notification-sent-successfully");
                        $status = 1;
                    }
                }
                //let us check emails
                if ($val['emails']) {

                    $subject = sanitizeText($val['subject']);
                    $content = contestEmailTemplateReplace($val['message'], $contest);
                    $emails = trim($val['emails']);
                    $scm = "";
                    $emails = explode(',', $emails);
                    foreach ($emails as $em) {
                        $em = trim($em);
                        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            //print_r("here");die();
                            mailer()->setAddress($em)->setSubject($subject)->setMessage($content)->send();
                            $scm = lang("contest::message-sent-successfully");
                        }
                    }
                    $message .= $scm;
                    $status = 1;
                }
                if ($message == '') {
                    $status = 0;
                    $message = lang("contest::no-invitation-sent");
                } else {
                    $redirect_url = contestUrl($contest);
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
            }
            $contest = get_contest($id);
            $view = view("contest::invite-view", array('contest' => $contest));
            return json_encode(array('view' => $view));
            break;
        case 'follow':
            if (!is_loggedIn()) return false;
            $s = input('status');
            $id = input('id');
            if ($s) {
                //na hin be say, we want to unfollow
                contestDeleteMyOldFollowing($id);
            } else {
                //zero, we want to follow
                contestDeleteMyOldFollowing($id);
                newContestFollower($id);
            }
            break;
        case 'search-friends':
            if (!is_loggedIn()) return false;
            $term = sanitizeText(input('term'));
            $lists = contestGetFriendsLists($term);
            $view = view("contest::friends-lists", array('lists' => $lists));
            return json_encode(array('v' => $view));
            break;
        case 'all-friends':
            if (!is_loggedIn()) return false;
            $friends = get_friends();
            $html = "";
            foreach ($friends as $u) {
                $user = find_user($u);
                if (!$user) continue;
                $uid = 'uid' . $u;
                $name = get_user_name($user);
                $html .= '<p class="cuser-line" id="' . $uid . '" ><input checked type="checkbox" name="val[friends][]" value="' . $u . '" />  ' . $name . '</p>';
            }
            $status = ($html) ? 1 : 0;
            $view = ($html) ? $html : lang("contest::no-friend-found");
            return json_encode(array('status' => $status, 'v' => $view));
            break;
    };

}

function contest_pager($app)
{
    $app->setTitle(lang('contest::contests'));
    $type = input('type', 'all');
    $category = input('category');
    $term = input('term');
    $filter = input('filter', 'all');
    $title = lang("contest::contests");
    switch ($type) {
        case 'friends':
            $title = lang("contest::friends-contests");
            break;
        case 'featured':
            $title = lang("contest::featured-contests");
            break;
        case 'premium':
            $title = lang("contest::premium-contests");
            break;
        case 'closed':
            $title = lang("contest::closed-contests");
            break;
        case 'following':
            $title = lang("contest::following-contests");
            break;
        case 'favorite':
            $title = lang("contest::my-favorite-contests");
            break;
            case 'mine':
            $title = lang("contest::my-contests");
            break;
    }
    return $app->render(view('contest::home', array('title' => $title, 'contests' => get_contests($type, $category, $term, null, 10, $filter))));
}

function contest_page_pager($app)
{
    $slug = segment(1);
    $contest = get_contest($slug);
    if (!$contest or (!$contest['status'] and !is_contest_owner($contest))) return redirect(url('contests'));
    $app->contest = $contest;
    app()->leftMenu = false;
    if ($contest['status'] && session_get('contest_view', 0) != $contest['id']) {
        db()->query("UPDATE contests SET views = views + 1 WHERE slug='{$slug}'");
        session_put('contest_view', $contest['id']);
    }
    $app->setTitle($contest['name'])->setDescription(str_limit(strip_tags($contest['description']), 100));
    set_meta_tags(array('name' => get_setting("site_title", "Crea8social"), 'title' => $contest['name'], 'description' => str_limit(strip_tags($contest['description']), 100), 'image' => $contest['image'] ? url_img($contest['image'], 200) : ''));
    return $app->render(view('contest::view', array('contest' => $contest)));
}

function manage_pager($app)
{
    $status = 0;
    $message = '';
    $redirect_url = '';

    $action = input('type');
    $app->setTitle(lang('contest::manage-contests'));
    $id = input('id');
    $contest = get_contest($id);
    if (is_contest_owner($contest)) {
        switch ($action) {
            case 'delete':
                delete_contest($id);
                return redirect(url('contests?type=mine'));
                break;
            case 'edit':
                $id = input('id');
                $contest = get_contest($id);
                $val = input('val', null, array('description'));
                $message = null;
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
                            if ($contest_id) {
                                $status = 1;
                                $message = lang('contest::contest-updated-successfully');
                                $contest = get_contest($contest_id);
                                if ($contest['status']) {
                                    $redirect_url = url('contest/' . $contest['slug']);
                                } else {
                                    $redirect_url = url('contests?type=mine');
                                }
                            } else {
                                $message = lang('contest::contest-add-error');
                            }
                        }
                    } else {
                        $message = validation_first();
                    }
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
                return $app->render(view('contest::edit', array('contest' => $contest, 'message' => $message)));
                break;
        }
    } else {
        $message = lang('contest::contest-edit-permission-denied');
        redirect(url('contests'));
    }
}

function add_contest_pager($app)
{
    $status = 0;
    $message = '';
    $redirect_url = '';

    $app->setTitle(lang('contest::add-new-contest'));
    $val = input('val', null, array('description'));
    if (user_has_permission('can-create-contest') && config('allow-members-create-contest', true)) {
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
                    $contest_id = save_contest($val, 'new');
                    if ($contest_id) {
                        $status = 1;
                        $message = lang('contest::contest-add-success');
                        $contest = get_contest($contest_id);
                        if ($contest['status']) {
                            $redirect_url = url('contest/' . $contest['slug']);
                        } else {
                            $redirect_url = url('contests?type=mine');
                        }
                    } else {
                        $message = lang('contest::contest-add-error');
                    }
                }
            } else {
                $message = validation_first();
            }
        }
    } else {
        $message = lang('contest::contest-add-permission-denied');
        $redirect_url = url('contests');
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

    return $app->render(view("contest::add", array('message' => $message)));
}