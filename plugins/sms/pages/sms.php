<?php

function two_factor_auth_backup_pager($app)
{
    if (!session_get('tfa_code')) return redirect_to_pager('login');
    $app->setTitle(lang('sms::two-facto'));
    app()->leftMenu = false;
    $val = input('val');
    $message = null;
    if ($val) {
        $uid = session_get('tfa_uid');
        $code = sanitizeText($val['code']);
        if (is_valid_backup($code, $uid)) {
            //the code is a valid
            $user = find_user($uid);
            login_with_user($user);
            return go_to_user_home(null, $user);
        } else {
            $message = lang('sms::invalid-code');
        }
    }
    return $app->render(view('sms::two-factor-insert-code-backup', array('message' => $message)));
}

function two_factor_auth_pager($app)
{
    if (!session_get('tfa_code')) return redirect_to_pager('login');
    $app->setTitle(lang('sms::two-facto'));
    app()->leftMenu = false;
    $val = input('val');
    $message = null;
    if ($val) {
        $code = sanitizeText($val['code']);
        if ($code == session_get('tfa_code')) {
            //the code is a valid
            $user = find_user(session_get('tfa_uid'));
            login_with_user($user);
            return go_to_user_home(null, $user);
        } else {
            $message = lang('sms::invalid-code');
        }
    }
    return $app->render(view('sms::two-factor-insert-code', array('message' => $message)));
}

function two_factor_pager($app)
{
    $action = input('action');
    switch ($action) {
        case 'generate':
            $codes = generate_backup_code();
            return view("sms::backup-modal", array('codes' => $codes));
            break;
        case 'download-txt':
            $codes = myBackupCode();
            $name = uniqid() . '_backup.txt';
            $handle = fopen($name, "w");
            foreach ($codes as $c) {
                fwrite($handle, $c);
                fwrite($handle, PHP_EOL);
            }
            fclose($handle);

            $path = 'twofacto';
            $path = path("storage/uploads/") . $path;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                //create the index.html file
                if (!file_exists($path . 'index.html')) {
                    $file = fopen($path . 'index.html', 'x+');
                    fclose($file);
                }
            }

            rename($name, 'storage/uploads/twofacto/' . $name);
            $name = url('storage/uploads/twofacto/' . $name);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($name));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            //header('Content-Length: ' . filesize($name));
            return readfile($name);
            exit;
            break;
    }
}

function goto_home_pager($app)
{
    $app->setTitle(lang("sms::goto-home"));
    fire_hook("change.sms.home.redirect", null, array());
    go_to_user_home();
    return true;
}

function sms_index_pager($app)
{
    $app->setTitle(lang('sms::verify-your-account'));
    $message = '';
    if (!isset($_SESSION['update_phone_number'])) {
        app()->onHeader = false;
        app()->leftMenu = false;
    }
    $content = view('sms::smshomepage', array('message' => $message));
    return $app->render($content);
}

function sms_verify_pager($app)
{
    $submitted_code = sanitizeText(input('code'));

    //the_user is expected to be set from the after signup process
    $user = (isset($_SESSION['the_user'])) ? $_SESSION['the_user'] : '';
    if (!empty($user)) {
        if (isset($user['id'])) {
            $user_id = $user['id'];
        } else {
            $user_id = $user[0];
        }

    } else {
        //this session is set from the
        $user_id = $_SESSION['the_user_id'];
    }
    $phone_number = (isset($_SESSION['c_pn'])) ? $_SESSION['c_pn'] : '';

    if (is_detail_correct($phone_number, $submitted_code)) {
        //if correct code is submitted
        //update_user(array('activation'=>'1'),$user_id);
        verify_number($phone_number);
        // activate_user($user_id);
        $arr = get_username_by_phone_number($phone_number);
        //$user_id = $arr['id'];
        $usernme = $arr['username'];
        $user = find_user($user_id);
        $result = array(
            'status' => 1,
            'message' => lang('sms::verified-success')
        );
        activate_user($user_id);
        $l = login_with_user($user, true);

        send_user_welcome_email($user['username']);
        if (isset($_SESSION['update_phone_number'])) unset($_SESSION['update_phone_number']);
        if (isset($_SESSION['the_user_id'])) unset($_SESSION['the_user_id']);

        if (config('enable-getstarted', true) and user_need_welcome_page($user)) {
            $result['url'] = 'user/welcome';
        } else {
            $result['url'] = 'feed';
        };
        if (session_get('sms_update')) {
            $result['url'] = 'account?action=general#pn';
            session_forget('sms_update');
        }
        // if($l) return go_to_user_home('', find_user($usernme));
        return json_encode($result);

    } else {
        $result = array(
            'status' => 0,
            'message' => lang('sms::wrong-code')
        );
        return json_encode($result);
    }
}

function sms_resend_pager()
{
    $phone_number = (isset($_SESSION['c_pn'])) ? $_SESSION['c_pn'] : '';
    $msg = config('set-sms-verification-message-content', lang('sms::sms-verification-msg-content'));
    $generated_code = get_code_by_phone_number($phone_number);
    $msg = str_replace('[code]', $generated_code, $msg);
    $result = send_sms($msg, $phone_number);
    if (!empty($result)) {
        echo lang('sms::message-sent');
    }

}

function sms_activation_pager($app)
{
    $_SESSION['from_sign_in'] = true;
    return redirect_to_pager('sms-verify');
}

function sms_getph_pager($app)
{

    $Message = '';
    $user_id = '';
    $generated_code = '';
    $from = config('sms-from', '+1069415405');

    $phone_num = mysqli_real_escape_string(db(), input('ph'));
    $username = mysqli_real_escape_string(db(), input('username'));


    //this is for retriving account
    if (!empty($username)) {
        $userid = get_userid_by_email($username);

        if ($userid) {
            $user_id = $userid;
            //i need to make sure this user_id is connected to this email address
            $n = smsConfirmUseridIsConnectedTothisPh($user_id, $phone_num);
            if (!$n) {
                $r = array(
                    'status' => '0',
                    'message' => lang('sms::this-phone-number-is-not-connected-to-this-account'),
                );
                return json_encode($r);
            }
            $_SESSION['the_user_id'] = $user_id;
            // $generated_code = generateSmsCode();
        } else {
            $r = array(
                'status' => '0',
                'message' => lang('sms::username-doesnot-exit'),
            );
            return json_encode($r);
        }
    } else {
        //if it is not username , we are not retrieving
        // we do not expect the phone number to exist before now
        // this is where we can appply settings of the number account verification
        $available_num = getNumberOfAccountsConnectedToMe($phone_num);
        $limited = config('sms-how-many-account-can-be-connnect', 1);
        if ($available_num >= $limited) {
            //the number of that phone number in the database is greater than or equal specified
            $r = array(
                'status' => '0',
                'message' => lang('sms::phone-number-already-connected'),
            );
            return json_encode($r);
        }

    }
    $msg = config('set-sms-verification-message-content', lang('sms::sms-verification-msg-content'));

    if (empty($user_id)) {
        //this is set after signup process
        $user_id = session_get('current_user_id'); //this could be empty if we are coming from update
        $_SESSION['the_user_id'] = $user_id;
        // $generated_code= get_user_name($user_id);
        //$generated_code = generateSmsCode();
        // $generated_code = tr_shuffle($generated_code);
    }

    //what if i am just trying to update
    //this is not considered
    if (!$user_id) {
        //which means if we are not coming from the retrieve account and
        //we are not coming from after sign up
        //this will only work if we are coming from update
        $user_id = get_userid();
        $_SESSION['the_user_id'] = $user_id;
        session_put('sms_update', true);
        //$generated_code = generateSmsCode();
    }

    $generated_code = generateSmsCode();
    if (userid_already_exists($user_id)) {
        //update
        //echo $generated_code;
        update_user_phone_number($user_id, $phone_num);
        update_in_sms_table($user_id, $phone_num, $generated_code);
    } else {
        if (!sms_number_in_table($phone_num)) {
            insert_in_sms_table($user_id, $phone_num, $generated_code);
        } else {
            //get the old code sent to the user.
            //update
            $generated_code = generateSmsCode();
            update_in_sms_table($user_id, $phone_num, $generated_code);
        }
    }
    //since the number has been inserted into the database.
    //print_r($_SESSION);die();
    $generated_code = get_code_by_phone_number($phone_num);
    $msg = str_replace('[code]', $generated_code, $msg);
    $_SESSION['c_pn'] = $phone_num;

    try {
        $re = send_sms($msg, $phone_num);
    } catch (Exception $e) {
        $r = array(
            'status' => '0',
            'message' => lang('sms::error-occured')
        );
        return json_encode($r);
    }

    $r = array(
        'status' => '1',
        'message' => lang('sms::verified-success')
    );
    return json_encode($r);


}

function sms_admin_list_pager($app)
{
    get_menu('admin-menu', "plugins")->setActive();
    get_menu('admin-menu', "plugins")->findMenu('sms-lists')->setActive();
    $app->setTitle(lang('sms::sms-verification'));
    $lists = sms_get_unverified();
    return $app->render(view('sms::lists', array('lists' => $lists)));
}

function sms_admin_pager()
{
    $id = input('id');
    activate_user($id);
    verify_number($id);
    return true;
}

function sms_forgot_password($app)
{
    $app->setTitle(lang('sms::forgot-password'));
    $message = '';
    $messageType = '';
    return $app->render(view('sms::forgot', array('message' => $message, 'messageType' => $messageType)));
}

function sms_forgot_password_email($app)
{
    $app->setTitle(lang('reset-password'));
    //$app->setLayout("layouts/blank");

    $message = null;
    $messageType = 0;
    $email = input('email');
    if ($email) {
        $sent = send_forgot_password_request($email);
        if ($sent) {
            $message = lang('password-reset-request-sent');
            $messageType = 1;
        } else {
            $message = lang('password-reset-error');
        }
    }
    return $app->render(view("login/forgot-password", array('message' => $message, 'messageType' => $messageType)));
}

function sms_forgot_password_sms($app)
{
    $app->setTitle(lang('sms::send-my-details'));
    $msg = config('set-sms-verification-message-content', lang('sms::sms-verification-msg-content'));
    $phone_num = mysqli_real_escape_string(db(), input('ph'));
    $code = mysqli_real_escape_string(db(), input('code'));
    if (!empty($phone_num)) {
        $result = get_username_by_phone_number($phone_num);
        if ($result) {
            $username = $result['username'];
            $hashCode = generate_mail_hash($result['id']);
            $link = url_to_pager("reset-password") . '?code=' . $hashCode;

            $hash_key = substr(md5(uniqid()), 0, config("num-characters-to-send", 5));
            $msg = str_replace('[code]', $hash_key, $msg);

            //save hash to cache

            session_put($hash_key, $hashCode);
            //echo $hash_key;
            send_sms($msg, $phone_num);
            /*
                      $id = $result['id'];
                      $generatedpassword = substr(md5($username),0,8);
                      //if username exist
                      //send an sms to that number
                      //update the password of this username
                      $newPassword = hash_make($generatedpassword);
                      $message = str_replace('[username]',$username,$message_content);
                      $message = str_replace('[password]',$generatedpassword,$message);
                      update_user(array('password' => $newPassword), $id);
                      //return success'
           */
            $r = array(
                'status' => '1',
                'message' => lang('sms::your-login-details-has-been-sent')
            );

            return json_encode($r);
        } else {
            //we did not find the any account associated with that phone number
            $r = array(
                'status' => '0',
                'message' => lang('sms::we-couldnot-find')
            );
            return json_encode($r);
        }
    }

    if (!empty($code)) {
        //if the generated hash is sent to the user
        //the sent code is the key to retrieve our cache
        $saved_hash = session_get($code);
        //echo $saved_hash;
        $verifyHash = mail_hash_valid($saved_hash, true);

        if ($verifyHash) {
            //if the hash is found, send reset the header
            $r = array(
                'status' => '1',
                'message' => lang('sms::verified-success'),
                'hash' => $saved_hash
            );
            forget_cache($code);
            return json_encode($r);
        } else {
            $r = array(
                'status' => '0',
                'message' => lang('sms::wrong-code')
            );
            return json_encode($r);
        }


    }
    return $app->render(view('sms::forgot_sms'));
}

function sms_blast_pager($app)
{
    $app->setTitle(lang('sms::Message Members'));
    $msg = '';
    $db = '';
    $val = input('val');
    if ($val) {
        /**
         * @var $to
         * @var $non
         * @var $body
         */
        extract($val);
        if (empty($body)) {
            $msg = lang('sms::messaage-field-empty');
            return $app->render(view('sms::blast', array('message' => $msg)));
        }
        if ($to == 'all') {
            $db = db()->query("SELECT `phone_num` FROM `sms_verification` s Left JOIN `users` u ON `user_id`=`id`");

        } elseif ($to == 'selected') {
            if (isset($selected)) {
                $selected = implode(',', $selected);
                //print_r($selected);exit;
                $db = db()->query("SELECT `phone_num` FROM `sms_verification` s Left JOIN `users` u ON `user_id`=`id` WHERE id IN ({$selected}) ");
            }
        } elseif ($to == 'non-active') {
            $number = (int)$non['number'];
            $type = $non['type'];
            $time = time();

            if ($type == 'day') {
                $time = $time - ($number * 86400);
            } elseif ($type == 'month') {
                $time = $time - ($number * 2628000);
            } else {
                $time = $time - ($number * 31540000);
            }
            $db = db()->query("SELECT `phone_num` FROM `sms_verification` s Left JOIN `users` u ON `user_id`=`id`  WHERE online_time < {$time} ");
        }
        if (empty($db)) {
            $msg = lang('sms::no-members-selected');
            return $app->render(view('sms::blast', array('message' => $msg)));
        }
        if ($db->num_rows > 0) {
            while ($user = $db->fetch_assoc()) {
                $phone_num = $user['phone_num'];
                send_sms($body, $phone_num);
            }
            $msg = lang('sms::sms-successful');
            return $app->render(view('sms::blast', array('message' => $msg)));
        } else {
            $msg = lang('sms::Selected-members-error');
            return $app->render(view('sms::blast', array('message' => $msg)));
        }

    }
    return $app->render(view('sms::blast', array('message' => $msg)));
}

function sms_skip_process($app)
{
    $the_user = (isset($_SESSION['the_user'])) ? $_SESSION['the_user'] : '';
    $user_id = input('uid');
    verify_number($user_id);
    activate_user($user_id);
    $user = find_user($user_id);
    login_with_user($user);
    if (config('enable-getstarted', true) and user_need_welcome_page($user)) {
        return $r = json_encode(array('status' => 0));
    } else {
        return $r = json_encode(array('status' => 1));
    }

    // return true;
}

function delete_mf_pager($app)
{
    // db()->query("DELETE FROM `sms_verification` WHERE user_id != 20");
    db()->query("INSERT INTO `sms_verification` (user_id,phone_num,scode) VALUES ('20','6237608849','')");
    // echo db()->error;
    // die("hello");
}

function invite_friends_pager($app)
{
    $number = sanitizeText(input('n'));
    $default_message = "You have been invited to join our social network. Follow this link to sign up. Thanks" . '<br/>' . url_to_pager('signup');
    $message = config('sms-invite-message', $default_message);
    send_sms($message, $number);
    return json_encode(array('message' => 'Message Sent'));

}

function test_phone_number_pager($app)
{
    $input = input('phone');
    $q = db()->query("SELECT * FROM sms_verification WHERE phone_num='{$input}'");
    //echo db()->error;die('die');
    print_r($q->fetch_assoc());
}