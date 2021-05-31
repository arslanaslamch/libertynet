<?php
function login_pager($app) {
    $status = 0;
    $message = '';
    $reload = 1;
    $redirect_url = '';

    $app->leftMenu = false;
    $redirect_to = input('redirect_to', url_to_pager(config('user-home', 'feed')));
    $redirect_to = filter_var($redirect_to, FILTER_VALIDATE_URL) ? $redirect_to : url('feed');
    if (is_loggedIn()) {
        $redirect_url = get_user_home_url();
    } else {
        $val = input("val");
        $message = null;
        $app->setTitle(lang('login'));
        if ($val) {
            CSRFProtection::validate();
            /**
             * @var $username
             * @var $password
             */
            extract($val);
            if ($username && $password) {
                $login = login_user($username, $password, input('val.remember'), false, (bool)input('ajax'));
                if ($login) {
                    $status = 1;
                    $reload = 1;
                    $redirect_url = get_user_home_url($redirect_to, find_user($username));
                } else {
                    $user = find_user($username);
                    if($user && $user['bannned']) {
                        $message = lang('ban-message', array('name' => get_user_name($user), 'site-title' => config('site_title')));
                    } else {
                        $message = lang('login-error');
                    }
                }
            } else {
                $message = lang('login-error');
            }
        }
    }

    if(input('ajax')) {
        $result = array(
            'status' => $status,
            'message' => $message,
            'reload' => $reload,
            'redirect_url' => (string) $redirect_url,
        );
        $response = json_encode($result);
        return $response;
    }
    if($redirect_url) {
        return redirect($redirect_url);
    }

    return $app->render(view('login/content', array('message' => $message)));
}

function forgot_password_pager($app) {
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

function reset_password_pager($app) {
    $app->leftMenu = false;
    $app->setTitle(lang('reset-password'));
    //$app->setLayout("layouts/blank");

    $message = null;
    $hash = input('code');
    $verifyHash = mail_hash_valid($hash);
    if (!$verifyHash) {
        return $app->render(view("login/reset-password-invalid"));
    }
    $val = input('val');
    if ($val) {
        CSRFProtection::validate();
        /**
         * @var $password
         * @var $confirm_password
         */
        extract($val);
        if (!$password or !$confirm_password or ($password != $confirm_password)) {
            $message = lang('password-match-error');
        } else {
            $user = find_user($verifyHash);
            $newPassword = hash_make($password);
            update_user(array('password' => $newPassword), $user['id']);
            delete_mail_hash($hash);
            $login = login_user($user['username'], $password, 0);
            if ($login) return go_to_user_home();
        }
    }

    return $app->render(view("login/reset-password", array('message' => $message)));
}

function login_verify($app) {
    $app->leftMenu = false;
    $login_verify = perfectUnserialize(session_get('login.verify'));
    if($login_verify && time() - $login_verify['time'] < config('login-verify-timeout', 1800)) {
        if (is_loggedIn()) {
            session_forget('login.verify');
            return go_to_user_home();
        } else {
            $redirect_url = input('redirect_to', url_to_pager(config('user-home', 'feed')));
            $redirect_url = filter_var($redirect_url, FILTER_VALIDATE_URL) ? $redirect_url : url('feed');
            $user = find_user($login_verify['id']);
            $security_settings = get_security_settings($user['id']);
            if(in_array($login_verify['type'], array('tfa', 'activate'))) {
                $medium = $login_verify['type'] == 'activate' ? (is_numeric(trim($login_verify['username'], '+')) ? 'phone' : 'email') : $security_settings['preferred-tfa-medium'];
                $type = $login_verify['type'] == 'activate' ? (is_numeric(trim($login_verify['username'], '+')) ? 'phone_no' : 'email') : ($medium == 'phone' ? ($user['phone_no_verified'] ? 'phone_no' : 'email') : 'email');
                $id = $medium == 'phone' ? $user['phone_no'] : $user['email_address'];
                $id = $medium == 'phone' ? preg_replace('/(^[^\+])/', '+$1', $id) : $id;
                $var = $medium == 'phone' ? 'phone-no' : 'email';
                $val = input('val');
                $app->setTitle(lang('login-verification'));
                if ($val) {
                    $verify = $medium == 'phone' ? phone_no_verify($val['code'], $user['id']) : email_verify($val['code'], $user['id']);
                    if($verify) {
						activate_user($user['id']);
                        $login = login_user($login_verify['username'], $login_verify['password'], $login_verify['remember'], true);
                        if ($login) {
                            return go_to_user_home($redirect_url, find_user($login_verify['username']));
                        } else {
                            return redirect(url('login'));
                        }
                    } else {
                        $status = 0;
                        $message = lang($var.'-verification-error');
                    }
                } else {
                    if(time() - session_get($var.'-verification-resend-time', 0) > config($var.'-verification-resend-timeout', 60)) {
                        $verify_send = $medium == 'phone' ? phone_no_verify_send($id, $user['id']) : email_verify_send($id, $user['id']);
                        if($verify_send) {
                            $status = 1;
                            $message = lang($var.'-verification-code-message-success', array($var => $id));
                        } else {
                            $status = 0;
                            $message = lang($var.'-verification-code-message-error', array($var => $id));
                        }
                    } else {
                        $status = 0;
                        $message = lang($var.'-verification-code-message-error', array($var => $id));
                    }
                }
            } else {
                $type = 'suspicion';
                $security_questions = get_security_questions();
                $id = isset($security_questions[$security_settings['security-question']['question']]) ? $security_questions[$security_settings['security-question']['question']] : $security_settings['security-question']['question'];
                $status = 1;
                $message = lang('answer-security-question');
                $val = input('val');
                if ($val) {
                    if($security_settings['security-question']['answer'] == $val['answer']) {
                        $login = login_user($login_verify['username'], $login_verify['password'], $login_verify['remember'], true);
                        if ($login) {
                            return go_to_user_home($redirect_url, find_user($login_verify['username']));
                        } else {
                            return redirect(url('login'));
                        }
                    }
                } else {
                    $status = 0;
                    $message = lang('answer-security-question-error');
                }
            }
            return $app->render(view('login/verify', array('user' => $user, 'type' => $type, 'id' => $id, 'mode' => $login_verify['type'], 'status' => $status, 'message' => $message)));
        }
    }
    return redirect(url('login'));
}