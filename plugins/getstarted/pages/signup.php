<?php
/**
 * Signup welcome steps pager
 */
function welcome_pager($app) {
    $app->leftMenu = false;
    $app->sideChat = false;
    $step = input('step');
    $message = null;
    session_put('welcome-page-visited', 'visited');
    $app->onHeaderContent = false;
    $app->setTitle(lang('welcome'));
    fire_hook('collect.get.started', $step, array());
    $user_unset_required_fields = user_required_fields(true);
    switch ($step) {
        case 'import':
            if ($user_unset_required_fields) {
                redirect_to_pager('signup-welcome');
            } else {
                $content = view('getstarted::import', array('message' => $message));
            }
        break;
        case 'add-people':
            if ($user_unset_required_fields) {
                redirect_to_pager('signup-welcome');
            } else {
                $content = view('getstarted::add-people', array('message' => $message));
            }
        break;
        case 'finish':
            if ($user_unset_required_fields) {
                redirect_to_pager('signup-welcome');
            } else {
                update_user(array('welcome_passed' => 1), null, true);
                return go_to_user_home();
            }
        break;
        case 'info':
            $status = 0;
            $pass = 1;
            $redirect_url = '';
            $val = input('val');
            $message = null;
            if ($val) {
                CSRFProtection::validate();
                if(config('enable-birthdate', true)) {
                    if(isset($val['dob_input']) && $val['dob_input']) {
                        $dob = explode('/', $val['dob_input']);
                        $_POST['val']['birth_month'] = strtolower($dob[0]);
                        $_POST['val']['birth_day'] = $dob[1];
                        $_POST['val']['birth_year'] = $dob[2];
                        $val = input('val');
                    }
                }
                fire_hook('getstarted.info', null, array($val));
				if(input_file('avatar')) {
                    $user_id = get_userid();
                    $avatar = input_file('avatar');
                    $uploader = new Uploader($avatar, 'image');
                    $uploader->setPath($user_id.'/'.date('Y').'/photos/profile/');
                    if ($uploader->passed()) {
                        $avatar = $uploader->resize()->toDB('profile-avatar', get_userid(), 1)->result();
                        update_user_avatar($avatar, null, $uploader->insertedId, false);
                    } else {
                        $message = $uploader->getError();
                    }
				} elseif (input('avatar')) {
                    $user_id = get_userid();
                    $avatar = input('avatar');
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
                        $avatar = $uploader->resize()->toDB('profile-avatar', get_userid(), 1)->result();
                        update_user_avatar($avatar, null, $uploader->insertedId, false);
                    } else {
                        $message = $uploader->getError();
                    }
                }
                if (!$message) {
                    $user_data = array();
                    $email = input('val.email_address', null);
                    if (isset($email)) {
                        $user_data['email_address'] = $email;
                        $user = get_user();
                        if (!filter_var($user['email_address'], FILTER_VALIDATE_EMAIL)) {
                            validator(array('email_address' => $email), array('email_address' => 'email'));
                            if (validation_passes()) {
								if (!email_available($email, $user['id'])) {
                                    $pass = false;
                                    $message = lang('email-address-is-in-use');
                                }
							} else {
                                $pass = false;
                                $message = validation_first();
                            }
                        }
                    }
                    $bio = input('val.bio');
                    if (isset($bio)) {
                        $user_data['bio'] = $bio;
                    }
                    $gender = input('val.gender');
                    if (isset($gender)) {
                        $user_data['gender'] = $gender;
                    }
                    $birth_day = input('val.birth_day');
                    if (isset($birth_day)) {
                        $user_data['birth_day'] = $birth_day;
                    }
                    $birth_month = input('val.birth_month');
                    if (isset($birth_month)) {
                        $user_data['birth_month'] = $birth_month;
                    }
                    $birth_year = input('val.birth_year');
                    if (isset($birth_year)) {
                        $user_data['birth_year'] = $birth_year;
                    }
                    $city = input('val.city');
                    if (isset($city)) {
                        $user_data['city'] = $city;
                    }
                    $state = input('val.state');
                    if (isset($state)) {
                        $user_data['state'] = $state;
                    }
                    $country = input('val.country');
                    if (isset($country)) {
                        $user_data['country'] = $country;
                    }
                    if ($pass) {
                        $user_data['completion'] = profile_completion($user_data);
                        $status = update_user($user_data, get_userid(), true);
                        if ($status) {
                            $message = lang('info-saved');
                            if (input('val.fields')) {
                                $field_rules = array();
                                foreach (get_form_custom_fields('user') as $field) {
                                    if ($field['required']) {
                                        $field_rules[$field['title']] = 'required';
                                    }
                                }
                                if ($field_rules) {
                                    validator(input('val.fields'), $field_rules);
                                }
                                if (validation_passes()) {
                                    $saved = save_user_profile_details($val);
                                    if ($saved) {
                                        $status = true;
                                    } else {
                                        $status = false;
                                        $message = lang('info-not-saved');
                                    }
                                } else {
                                    $status = false;
                                    $message = validation_first();
                                }
                            }
                        } else {
                            $message = lang('user-update-error');
                        }
                    }
                }
                if ($status) {
                    $user_unset_required_fields = user_required_fields(true);
                    if ($user_unset_required_fields) {
                        $status = false;
                        $message = lang('required-fields', array('fields' => ucwords(implode(', ', $user_unset_required_fields))));
                        $redirect_url = '';
                    } else {
                        $redirect_url = plugin_loaded('social') ? url_to_pager('signup-welcome').'?step=import' : url_to_pager('signup-welcome').'?step=add-people';
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
            } else {
                if ($user_unset_required_fields && server('HTTP_REFERER') == url_to_pager('signup-welcome')) {
                    $message = lang('required-fields', array('fields' => ucwords(implode(', ', $user_unset_required_fields))));
                }
            }
            if ($redirect_url) {
                return redirect($redirect_url);
            }
            $content = view('getstarted::info', array('message' => $message));
        break;
        default:
            $status = 0;
            $pass = 1;
            $redirect_url = '';
            $val = input('val');
            $message = '';
            $reload = 0;
            $step = 0;
            if ($val) {
                CSRFProtection::validate();
                if(config('enable-birthdate', true)) {
                    if(isset($val['dob_input']) && $val['dob_input']) {
                        $dob = explode('/', $val['dob_input']);
                        $_POST['val']['birth_month'] = strtolower($dob[0]);
                        $_POST['val']['birth_day'] = $dob[1];
                        $_POST['val']['birth_year'] = $dob[2];
                        $val = input('val');
                    }
                }
                fire_hook('getstarted.info', null, array($val));
				if(input_file('avatar')) {
                    $user_id = get_userid();
                    $avatar = input_file('avatar');
                    $uploader = new Uploader($avatar, 'image');
                    $uploader->setPath($user_id.'/'.date('Y').'/photos/profile/');
                    if ($uploader->passed()) {
                        $avatar = $uploader->resize()->toDB('profile-avatar', get_userid(), 1)->result();
                        update_user_avatar($avatar, null, $uploader->insertedId, false);
                    } else {
                        $message = $uploader->getError();
                    }
				} elseif (input('avatar')) {
                    $user_id = get_userid();
                    $avatar = input('avatar');
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
                        $avatar = $uploader->resize()->toDB('profile-avatar', get_userid(), 1)->result();
                        update_user_avatar($avatar, null, $uploader->insertedId, false);
                    } else {
                        $message = $uploader->getError();
                    }
                }
                if (!$message) {
                    $user_data = array();
                    $email = input('val.email_address', null);
                    if (isset($email)) {
                        $user_data['email_address'] = $email;
                        $user = get_user();
                        if (!filter_var($user['email_address'], FILTER_VALIDATE_EMAIL)) {
                            validator(array('email_address' => $email), array('email_address' => 'email'));
                            if (validation_passes()) {
                                if (!email_available($email, $user['id'])) {
                                    $pass = false;
                                    $message = lang('email-address-is-in-use');
                                }
                            } else {
                                $pass = false;
                                $message = validation_first();
                            }
                        }
                    }
                    $bio = input('val.bio');
                    if (isset($bio)) {
                        $user_data['bio'] = $bio;
                    }
                    $gender = input('val.gender');
                    if (isset($gender)) {
                        $user_data['gender'] = $gender;
                    }
                    $birth_day = input('val.birth_day');
                    if (isset($birth_day)) {
                        $user_data['birth_day'] = $birth_day;
                    }
                    $birth_month = input('val.birth_month');
                    if (isset($birth_month)) {
                        $user_data['birth_month'] = $birth_month;
                    }
                    $birth_year = input('val.birth_year');
                    if (isset($birth_year)) {
                        $user_data['birth_year'] = $birth_year;
                    }
                    $city = input('val.city');
                    if (isset($city)) {
                        $user_data['city'] = $city;
                    }
                    $state = input('val.state');
                    if (isset($state)) {
                        $user_data['state'] = $state;
                    }
                    $country = input('val.country');
                    if (isset($country)) {
                        $user_data['country'] = $country;
                    }
					if (isset($user_data['birth_day'], $user_data['birth_month'], $user_data['birth_year']) && $user_data['birth_day'] && $user_data['birth_month'] && $user_data['birth_year']) {
						$birth_day = $user_data['birth_day'];
						$birth_month = date_parse($user_data['birth_month'])['month'];
						$birth_year = $user_data['birth_year'];
						if (!checkdate((int)$birth_month, (int)$birth_day, (int)$birth_year)) {
							$pass = false;
							$message = lang('invalid-birth-day');
						}
					}
                    if ($pass) {
                        $user_data['completion'] = profile_completion($user_data);
                        $status = update_user($user_data, get_userid(), true);
                        if ($status) {
                            $message = lang('info-saved');
                            if (input('val.fields')) {
                                $field_rules = array();
                                foreach (get_form_custom_fields('user') as $field) {
                                    if ($field['required']) {
                                        $field_rules[$field['title']] = 'required';
                                    }
                                }
                                if ($field_rules) {
                                    validator(input('val.fields'), $field_rules);
                                }
                                if (validation_passes()) {
                                    $saved = save_user_profile_details($val);
                                    if ($saved) {
                                        $status = true;
                                    } else {
                                        $status = false;
                                        $message = lang('info-not-saved');
                                    }
                                } else {
                                    $status = false;
                                    $message = validation_first();
                                }
                            }
                        } else {
                            $message = lang('user-update-error');
                        }
                    }
                }
                if ($status) {
                    $user_unset_required_fields = user_required_fields(true);
                    if ($user_unset_required_fields) {
                        $status = false;
                        $message = lang('required-fields', array('fields' => ucwords(implode(', ', $user_unset_required_fields))));
                        $step = (int) !array_intersect(array('birthday', 'avatar', 'gender'), $user_unset_required_fields);
                        $redirect_url = '';
                    } else {
                        update_user(array('welcome_passed' => 1), null, true);
                        $reload = 1;
                        $redirect_url = get_user_home_url();
                    }
                }
                if (input('ajax')) {
                    $result = array(
                        'status' => (int)$status,
                        'message' => $message,
                        'redirect_url' => (string)$redirect_url,
                        'reload' => $reload,
                        'step' => $step,
                    );
                    $response = json_encode($result);
                    return $response;
                }
            } else {
                if ($user_unset_required_fields && server('HTTP_REFERER') == url_to_pager('signup-welcome')) {
                    $message = lang('required-fields', array('fields' => ucwords(implode(', ', $user_unset_required_fields))));
                }
            }
            if ($redirect_url) {
                return redirect($redirect_url);
            }
            $content = view('getstarted::getstarted', array('message' => $message));
        break;
    }
//$content = $app->view('getstarted::welcome/layout');
    return $app->render($content);
}