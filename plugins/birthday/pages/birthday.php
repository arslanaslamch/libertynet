<?php

function tasks_pager($app){
    $key = config('tasks-run-key');
    ini_set('max_execution_time', '0');
    if(input('key') == $key){
        //you get am for access
       //echo die("you are here");
        // check if there are celebrants today
        $users = Birthday::getTodaysBirthdays();
        if($users->total){
            //there are celebrants

            //check if user allow auto posting to user timeline
            if(config('enable-birthday-timeline-wish',1)){
                $template = Birthday::getTemplate('post');
                if($template){
                    foreach ($users->results() as $u){
                        $image = '';
                        if($image = $template['image']){
                            $arr = array(
                                0 => $image
                            );
                            $image = perfectSerialize($arr);
                            //$image = $template['image'];
                        }
                        //echo $template['image'];die();
                        //api_temporary_login_user($template['admin']);
                        //temporarily login user
                        $user = find_user($template['admin']);
                        app()->user = $user;
                        app()->userid = $user['id'];
                        //each celebrant
                        $content = Birthday::formatTemplates($template['content'],$u);
                        //print_r($content);die();
                        $fdata = array(
                            'entity_id' => $template['admin'],
                            'to_user_id'=> $u['id'],
                            'entity_type' => 'user',
                            'type' => 'feed',
                            'type_id' => 'birthday',
                            'type_data' => '',
                            'privacy' => 1,
                            'content'=>$content,
                            'images'=>$image,
                            'auto_post' => true,
                            'can_share' => 1
                        );
                        $r = add_feed($fdata);
                    }
                }
            }

            //check if allowed to send
            if(config('enable-birthday-auto-email',true)){
                //this is the mail sent to the celebrant
                $template = Birthday::getTemplate('email');
                if($template){
                    $image = null;
                    if($image = $template['image']){
                        $image = '<img style="height:100%; width :100%;margin:10px; max-width : 400px; max-height :400px ;display:block" src="'.url_img($image).'" />';
                    }
                    foreach ($users->results() as $u){
                        //print_r($u);die();
                        $content = Birthday::formatTemplates($template['content'],$u,$image);
                        $subject = $template['subject'];
                        $email_address = $u['email_address'];
                        //$email_address = 'oluwaseunyinka@gmail.com';
                        $mailer = mailer();
                        $mailer->setAddress($email_address, $u['first_name']);
                        $mailer->setSubject($subject);
                        //print_r($content);die();
                        $mailer->setMessage($content);
                        $mailer->send();
                    }
                }
            }

            //check reminder mail
            if(config('enable-birthday-reminder',true)){
                //this is the mail sent to the celebrant
                $template = Birthday::getTemplate('reminder');
                if($template){
                    $image = null;
                    if($image = $template['image']){
                        $image = '<img style="height:100%; width :100%;margin:10px; max-width : 400px; max-height :400px ;display:block" src="'.url_img($image).'" />';
                    }
                    foreach ($users->results() as $u){
                        //get this celebrants friends
                        $friends = get_friends($u['id']);
                        if($friends){
                            foreach($friends as $f){
                                //print_r($f);die();
                                $user = find_user($f);
                                $content = Birthday::formatTemplates($template['content'],$user,$image);
                                $str = '<a href="'.profile_url(null,$u).'">'.get_user_name($u).'</a>';
                                $content = str_replace('[friends_url]',$str,$content);
                                $subject = $template['subject'];

                                $email_address = $user['email_address'];
                                $mailer = mailer();
                                $mailer->setAddress($email_address, $user['first_name']);
                                $mailer->setSubject($subject);
                                $mailer->setMessage($content);
                                $mailer->send();
                            }
                        }
                    }
                }
            }
        }
    }
    echo "end";
}

function home_pager($app){
    $app->setTitle(lang("birthday::birthdays"));
    $month = input('m',null);
    $month = ($month) ? Birthday::getMonths($month) : date('F');
    $term = input('term',null);
    $users = Birthday::getCelebrants('month',$month, 'all',$term);
    $title = (!input('m',null)) ? lang('birthday::birthdays-in-this-month') : lang('birthday::birthdays-in',array('m'=>ucwords(lang('birthday::'.$month))));
    if($term) $title = lang("birthday::birthdays");
    return $app->render(view    ("birthday::home",array('users'=>$users,'title'=>$title)));
}

function ajax_pager($app){
    if(input('action') == 'congrats'){
        $msg = input('msg');
        $uid = input('uid');
        $file = input_file('image');
        if($file) {
            $uploader = new Uploader($file);
            if($uploader->passed()) {
                $uploader->setPath('birthday/images/');
                $image = $uploader->resize(700, 500)->result();
            }
        }
        add_feed(array(
            'entity_id' => get_userid(),
            'to_user_id'=> $uid,
            'entity_type' => 'user',
            'type' => 'feed',
            'type_id' => 'birthday',
            'type_data' => '',
            'privacy' => 1,
            'content'=>$msg,
            'images' => '',
            'auto_post' => true,
            'can_share' => 1
        ));
        return json_encode(array('status'=>1,'msg'=>lang("birthday::your-congratulations-was-sent")));
    }
    if(input('action') == 'friends-only'){
        session_put('b-filter-status',input('status',0));
        return true;
    }
    return json_encode(array('status'=>1,'message'=>'not found'));
}