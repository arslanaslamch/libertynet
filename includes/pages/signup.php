<?php
function signup_pager($app) {
    $status = 0;
    $message = '';
    $reload = 0;
    $redirect_url = '';

    $app->leftMenu = false;
    $app->setTitle(lang("join-network"));

    $val = input('val');

    if (is_loggedIn()) {
        $redirect_url = url_to_pager('feed');
    } else {

        load_functions('country');

        extract(fire_hook("signup.form.check", array(), array('status' => true, 'message' => null)));

        if ($val) {
            CSRFProtection::validate();

            if (config('enable-birthdate', true)) {
                if (isset($val['dob_input']) && $val['dob_input']) {
                    $dob = explode('/', $val['dob_input']);
                    $val['birth_month'] = strtolower($dob[0]);
                    $val['birth_day'] = $dob[1];
                    $val['birth_year'] = $dob[2];
                }
            }

            if (config('signup-merge-email-phone') && config('enable-phone-no', false) && !config('signup-hide-phone-no') && !config('signup-hide-phone-no')) {
                if (isset($val['email_phone']) && $val['email_phone']) {
                    if (is_numeric($val['email_phone'])) {
                        $val['phone_no'] = preg_replace('/^\+/', '', $val['email_phone']);
                    } else {
                        $val['email_address'] = $val['email_phone'];
                    }
                }
            }

            if(isset($val['name'])) {
                $names = preg_split('/\s+/', trim($val['name']));
                if(!isset($val['first_name']) && isset($names[0])) {
                    $val['first_name'] = $names[0];
                }
                if(!isset($val['last_name']) && isset($names[1])) {
                    $val['last_name'] = $names[1];
                }
            }
            $val = fire_hook('signup.fields', $val);

            if (fire_hook('users.category.exist', false)) {
                $val['username'] = fire_hook('users.category.username', $val, array($val));
            }

            fire_hook('signup.submitted', $val, array());

            $tos = input('val.tos');
            if ($tos) {
                if (config('enable-captcha') == 2) {
                    require_once path('includes/libraries/recaptcha/autoload.php');
                    $recaptcha = new \ReCaptcha\ReCaptcha(config('recaptcha-secret'));
                    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                }

                if (!config("enable-captcha") || (config('enable-captcha') == 1 && strtolower(session_get("sv_captcha")) == strtolower(input('captcha'))) || (config('enable-captcha') == 2 && $resp->isSuccess())) {
                    $rules = array(
                        'first_name' => 'required|predefined',
                        'last_name' => 'required|predefined',
                        'username' => 'required|predefined|alphanum|min:3|username',
                        'password' => 'required|min:6',
                    );

                    if (config('enable-phone-no', true) && (isset($val['email_address']) && isset($val['phone_no']) || isset($val['email_phone'])) && (isset($val['email_address']) && !$val['email_address']) && (isset($val['phone_no']) && !$val['phone_no'])) {
                        $message = lang('either-email-phone-required');
                    } elseif (isset($val['email_address']) && !$val['email_address']) {
                        $message = lang('email-required');
                    } elseif (config('enable-phone-no', true) && isset($val['phone_no']) && !$val['phone_no']) {
                        $message = lang('phone-required');
                    } elseif (!isset($val['email_address']) && !isset($val['phone_no']) && !isset($val['email_phone'])) {
                        $message = lang('contact-required');
                    }

                    if (isset($val['email_address']) && $val['email_address']) {
                        $rules['email_address'] = 'email|unique:users';
                    }

                    if (config('enable-phone-no', true)) {
                        if (isset($val['phone_no']) && $val['phone_no']) {
                            $rules['phone_no'] = 'phoneno|unique:users';
                        }
                    } elseif (isset($val['phone_no'])) {
                        unset($val['phone_no']);
                    }

                    if (config('enable-birthdate', true) && isset($val['birth_day']) && $val['birth_day'] && isset($val['val.birth_month']) && $val['val.birth_month'] && isset($val['val.birth_year']) && $val['val.birth_year']) {
                        $rules['birth_day'] = 'required';
                        $rules['birth_month'] = 'required';
                        $rules['birth_year'] = 'required';
                    }

                    $rules = fire_hook('signup.rules', $rules);

                    $password_confirm = input('val.cpassword');
                    if (isset($password_confirm) && input('val.password') !== $password_confirm) {
                        $message = lang('password-match-error');
                    }

                    if (config('enable-gender', true)) {
                        $gender = input('val.gender');
                        if ($gender) {
                            $genders = get_genders();
                            if (!in_array($gender, $genders)) {
                                $message = lang('invalid-gender');
                            }
                        }
                    }

                    if (input('val.country') && !is_valid_country(input('val.country'))) {
                        $message = lang('invalid-country');
                    }

                    if (config('enable-birthdate', true)) {
                        if (isset($val['dob_input']) && $val['dob_input']) {
                            if (!is_numeric($val['birth_day'])) {
                                $message = lang('invalid-birth-day');
                            }
                            $months = config('months');
                            if (!array_key_exists($val['birth_month'], $months)) {
                                $message = lang('invalid-birth-month');
                            }
                            $currentYear = date('Y');
                            $minAge = config("birthdate-min-age", 10);
                            $maxYear = $currentYear - $minAge;
                            if ($val['birth_year'] > $maxYear) {
                                $message = lang('birth-year-exceed');
                            }
                        } else if (input('val.birth_day') && input('val.birth_month') && input('val.birth_year')) {
                            if (!is_numeric(input('val.birth_day'))) {
                                $message = lang('invalid-birth-day');
                            }
                            $months = config('months');
                            if (!array_key_exists(input('val.birth_month'), $months)) {
                                $message = lang('invalid-birth-month');
                            }
                            $currentYear = date('Y');
                            $minAge = config("birthdate-min-age", 10);
                            $maxYear = $currentYear - $minAge;
                            if (input('val.birth_year') > $maxYear) {
                                $message = lang('birth-year-exceed');
                            }
                        }
                    }
                    validator($val, $rules);

                    if (validation_passes()) {
                        $fieldRules = array();
                        if (input('val.fields')) {
                            foreach (get_form_custom_fields('user') as $field) {
                                if ($field['required']) {
                                    $fieldRules[$field['title']] = 'required';
                                }
                            }
                        }
                        if ($fieldRules) {
                            validator(input('val.fields'), $fieldRules);
                        }
                    }

                    if (validation_passes() && !$message) {
                        $added = add_user($val);
                        if ($added) {
                            if (isset($val['avatar']) && $val['avatar']) {
                                $user_id = $added;
                                $avatar = $val['avatar'];
                                list($header, $data) = explode(',', $avatar);
                                preg_match('/data\:image\/(.*?);base64/i', $header, $matches);
                                $extension = $matches[1];
                                if (!in_array($extension, array('jpg', 'png', 'gif', 'jpeg'))) {
                                    exit('Invalid Format '.$extension);
                                }
                                $data = str_replace(' ', '+', $data);
                                $data = base64_decode($data);
                                $dir = config('temp-dir', path('storage/tmp')).'/awesome_cropper';
                                if (!is_dir($dir)) {
                                    mkdir($dir, 0777, true);
                                }
                                $path = $dir.'/avatar_'.get_userid().'_'.time().'.'.$extension;
                                file_put_contents($path, $data);
                                $uploader = new Uploader($path, 'image', false, true);
                                $uploader->setPath($user_id.'/'.date('Y').'/photos/profile/');
                                if ($uploader->passed()) {
                                    $avatar = $uploader->resize()->toDB('profile-avatar', $user_id, 1)->result();
                                    update_user_avatar($avatar, $user_id, $uploader->insertedId, false);
                                } else {
                                    $message = $uploader->getError();
                                }
                            }
                            fire_hook("signup.completed", null, array($added, $val));
                            fire_hook("user.type.signup.completed", $added);
                            send_user_welcome_email($val['username']);
                            if (isset($val['email_address']) && $val['email_address'] && isset($val['phone_no']) && $val['phone_no']) {
                                $username = config('user-primary-contact', 'email') == 'phone' ? $val['phone_no'] : $val['email_address'];
                            } elseif (isset($val['email_address']) && $val['email_address']) {
                                $username = $val['email_address'];
                            } elseif (isset($val['phone_no']) && $val['phone_no']) {
                                $username = $val['phone_no'];
                            } else {
                                $username = $val['username'];
                            }
                            $password = $val['password'];
                            $login = login_user($username, $password, true, false, (bool)input('ajax'));
                            if ($login) {
                                $status = 1;
                                $reload = 1;
                                $user = find_user($added);
                                $redirect_url = get_user_home_url(null, $user);
                            }
                        }
                    } else {
                        $message = $message ? $message : validation_first();
                    }
                } else {
                    $message = lang('invalid-captcha-code');
                }
            } else {
                $message = lang('tos-error');
            }
        }

        $_GET['message'] = $message;

        fire_hook('signup.get.parameters', null, array());

        $signup_page = "signup/content";
        $signup_page = fire_hook("custom.signup.page", $signup_page);
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
    return $app->render(view($signup_page, array('message' => $message)));
}

function signup_activate_pager($app) {
    $app->leftMenu = false;
    if (is_loggedIn()) redirect_to_pager('feed');
    $app->setTitle(lang('activate-email'));

    $hash = input('code');
    if ($hash) {
        $verifyHash = mail_hash_valid($hash, true);
        if ($verifyHash) {
            activate_user($verifyHash);
            update_user(array('email_verified' => 1));
            $user = find_user($verifyHash);
            login_with_user($user);
            delete_mail_hash($hash);

            return go_to_user_home();
        }
    }
    $message = "";
    $email = input('email');
    if ($email) {
        $send = send_user_activation_link($email);
        if (!$send) {
            $message = lang('failed-to-send-activation-link');
        }
    }
    return $app->render(view('signup/activate', array('message' => $message)));
}