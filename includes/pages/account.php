<?php

get_menu("dashboard-main-menu", 'profile')->setActive(true);

register_account_menu();

/**
 * @param $app
 * @return mixed
 */
function general_pager($app) {
	$app->topMenu = lang('settings');

	$action = input('action', 'general');
	$status = 0;
	$message = '';
	$redirect_url = '';

	switch($action) {
		case 'delete':
			$app->setTitle(lang('delete-my-account'));
			$message = null;
			$password = input('password');
			if(!hash_check($password, get_user_data('password'))) {
				$message = lang('invalid-password');
			} else {
				$status = delete_user();
				if($status) {
					$message = lang('user-deleted');
					$redirect_url = url_to_pager('logout');
				}
			}
			$content = view('account/delete', array('message' => $message));
		break;

		case 'general':
            $userid = get_userid();
            $old_user = get_user();
            get_menu("account-menu", "general")->setActive();
			$val = input('val');
			if($val) {
				CSRFProtection::validate();
				$process = true;
				$username_changed = false;
				if(input_file('image')) {
					$uploader = new Uploader(input_file('image'), 'image');
					$uploader->setPath(get_userid().'/'.date('Y').'/photos/profile/');
					if($uploader->passed()) {
						$avatar = $uploader->resize()->toDB('profile-avatar', get_userid(), 1)->result();
						update_user_avatar($avatar, null, $uploader->insertedId);
					} else {
						$process = false;
						$message = $uploader->getError();
					}
				}

				/**
				 * @var $username
				 * @var $email_address
				 */
				extract($val);
				if(config('allow-change-username', true) and isset($username) and $username != get_user_data('username')) {
					validator(array('username' => $username), array('username' => 'required|alphanum|usernameedit'));
					if(validation_passes()) {
						$username_changed = true;
						if(config('loose-verify-badge-username', true)) {
							$val['verified'] = 0;
						}
					} else {
						$process = false;
						$message = validation_first();
					}
                } elseif(isset($val['username'])) {
                    unset($val['username']);
                }

				if(config('allow-change-email', true) and isset($email_address) and $email_address != get_user_data('email_address')) {
					validator(array('email_address' => $email_address), array('email_address' => 'email'));
					if(validation_passes()) {
                        if (!email_available($email_address, $userid)) {
                            $process = false;
                            $message = lang('email-address-is-in-use');
                        }
					} else {
						$process = false;
						$message = validation_first();
					}
                } elseif(isset($val['email_address'])) {
                    unset($val['email_address']);
                }

                if(config('enable-phone-no', true)) {
                    if(config('allow-phone-no-change', true) and isset($phone_no) and $phone_no != get_user_data('phone_no')) {
                        validator(array('phone_no' => $phone_no), array('phone_no' => 'phoneno'));
                        if(validation_passes()) {
                            if (!phone_no_available($phone_no, $userid)) {
                                $process = false;
                                $message = lang('phone-no-is-in-use');
                            }
                        } else {
                            $process = false;
                            $message = validation_first();
                        }
                    }
                } elseif(isset($val['phone_no'])) {
                    unset($val['phone_no']);
                }
                unset($val['code']);
                unset($val['number']);

				validator($val, array(
					'first_name' => 'required',
					'last_name' => 'required'
				));
				if(!validation_passes()) {
					$message = validation_first();
					$process = false;
				}
				if($process) {
				    $val['completion'] = profile_completion($val);
					$status = save_user_general_settings($val);
					if($status) {
						$message = lang('general-settings-saved');
                        $new_user = get_user(true);
                        if($old_user['email_address'] != $new_user['email_address']) {
                            update_user(array('email_verified' => 0));
                        }
                        if($old_user['phone_no'] != $new_user['phone_no']) {
                            update_user(array('phone_no_verified' => 0));
                        }
						fire_hook('user.type.signup.completed', get_userid());
						fire_hook('users.category.update', 'update', array($val, get_userid(), true));
                        $new_user = get_user(true);
                        if($new_user['phone_no_verified'] && $new_user['email_verified']) {
                            save_security_settings(array('enable-tfa' => 0));
                        }
						if($username_changed) {
							login_with_user(find_user(get_userid()), true);
						}
						$redirect_url = url_to_pager('account');
					}
				}
			}
			$app->setTitle(lang('general-settings'));
            $account_page = "account/general";
            $account_page = fire_hook("account.custom.page", $account_page);
            $content = view($account_page, array('message' => $message));
		break;

		case 'password':
            get_menu("account-menu", "change-password")->setActive();
			$val = input('val');
			$app->setTitle(lang('update-password'));
			$success = '';
			if($val) {
				CSRFProtection::validate();
				/**
				 * expected values
				 * @var $new
				 * @var $current
				 * @var $confirm
				 */
				extract($val);
				if($new and $current and $confirm) {
					$currentPass = get_user_data('password');
					//$current = hash_make($current);
					if(hash_check($current, $currentPass)) {
						if($new != $confirm) {
							$message = lang('new-password-not-match');
						} else {
							$password = hash_make($new);
							$status = update_user(array('password' => $password));
							if($status) {
								$success = lang('password-changed');
								$message = $success;
								$redirect_url = url_to_pager('account').'?action=password';
							}
						}
					} else {
						$message = lang('current-password-not-match');
					}
				} else {
					$message = lang('all-fields-required');
				}
			}
			$content = view('account/password', array('message' => $message, 'success' => $success));
		break;

		case 'notifications':
            get_menu("account-menu", "notification")->setActive();
			$app->setTitle(lang('notification-settings'));
			if($val = input('val')) {
				fire_hook('notification.settings.save', $val);
				$status = save_privacy_settings($val);
				if($status) {
					$message = lang('notification-saved');
					$redirect_url = url_to_pager('account').'?action=notifications';
				}
			}
			$content = view('account/notifications');
		break;

		case 'privacy':
            get_menu("account-menu", "privacy")->setActive();
			$app->setTitle(lang('privacy-settings'));
			if($val = input('val')) {
				$status = save_privacy_settings($val);
				if($status) {
					$message = lang('privacy-saved');
					$redirect_url = url_to_pager('account').'?action=privacy';
				}
			}
			$content = view('account/privacy');
		break;

		case 'security':
            $user = get_user();
            get_menu("account-menu", "security")->setActive();
			$app->setTitle(lang('security-settings'));
            $security_questions = get_security_questions();
			if($val = input('val')) {
                $val['tfa-tos'] = isset($val['tfa-tos']) ? $val['tfa-tos'] : 0;
                if(!$val['tfa-tos'] && $val['enable-tfa']) {
                    $message = lang('must-accept-tfa-tos');
                } elseif(!$user['email_verified'] && !$user['phone_no_verified'] && $val['enable-tfa']) {
                    $message = lang(config('enable-phone-no', false) ? 'two-factor-require-email-phone' : 'two-factor-require-email');
                } else {
                    $val['security-question']['question'] = isset($security_questions[$val['security-question']['question']]) ? $val['security-question']['question'] : '';
                    $val['security-question']['answer'] = $val['security-question']['question'] ? $val['security-question']['answer'] : '';
                    $status = save_security_settings($val);
                    if($status) {
                        $message = lang('security-settings-saved');
                        $redirect_url = url_to_pager('account').'?action=security';
                    }
                }
			}
            $security_settings = get_security_settings();
            $user = get_user();
			$content = view('account/security', array('user' => $user, 'security_settings' => $security_settings, 'security_questions' => $security_questions));
		break;

		case 'blocked':
            get_menu("account-menu", "blocked")->setActive();
                $app->setTitle(lang('blocked-members'));
			$content = view('account/block-members', array('users' => get_blocked_members()));
		break;

		case 'profile':
			$id = input('id');
			$category = get_custom_field_category($id);
            get_menu("account-menu", $category['slug'])->setActive();
			if($category) {
				$app->setTitle(lang($category['title']));
				$val = input('val');
				if($val) {
					CSRFProtection::validate();
					$field_rules = array();
					foreach(get_custom_fields('user', $id) as $field) {
						if($field['required']) {
							$field_rules[$field['title']] = 'required';
						}
					}
					if($field_rules) {
						$validator = validator(input('val.fields'), $field_rules);
					}
					if(validation_passes() && !$message) {
                        if(isset($val['fields'])) {
                            $val['fields'] = fire_hook('custom.fields.before.save', $val['fields']);
                        }
						$status = save_user_profile_details($val);
						if($status) {
							$message = lang('profile-saved');
							$redirect_url = url_to_pager('account').'?action=profile&id='.$id;
						}
					} else {
						$message = validation_first();
					}
				}
			} else {
				$redirect_url = url_to_pager('account');
			}
			$content = view('account/profile', array('slug' => $id, 'category' => $category, 'message' => $message));
		break;

		default:
			$content = fire_hook('account.settings', null, array($action));
		break;
	}

	if(input('val') && input('ajax')) {
		$result = array(
			'status' => (int) $status,
			'message' => (string) $message,
			'redirect_url' => (string) $redirect_url,
		);
		$response = json_encode($result);
		return $response;
	}

	if($redirect_url) {
		return redirect($redirect_url);
	}

	return $app->render(view('account/layout', array('content' => $content)));
}

function block_user_pager($app) {
	$userid = segment(2);
	block_user($userid);
	return go_to_user_home();
}

function unblock_user_pager() {
	$userid = segment(2);
	unblock_user($userid);
	return redirect_back();
}

function verify_email($app) {
    $title = lang('verify-email');
    $description = lang('email-start-desc');

    $email = get_user_data('email_address');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'email' => $email,
        'html' => '',
        'redirect_url' => ''
    );

    $val = input('val');
    if($val) {
        CSRFProtection::validate();
        if(email_verify($val['code'])) {
            $result['status'] = 1;
            $result['message'] = lang('email-verification-success');
            $result['html'] = '';
            $result['redirect_url'] = url('account');
        } else {
            $result['status'] = 0;
            $result['message'] = lang('email-verification-success-error');
        }
        $result['html'] = view('account/verify/email', array('email' => $email, 'status' => $result['status'], 'message' => $result['message']));
    } else {
        if(time() - session_get('email-verification-resend-time', 0) > config('email-verification-resend-timeout', 60)) {
            if(email_verify_send($email)) {
                $result['status'] = 1;
                $result['message'] = lang('email-verification-code-message-success', array('email' => $email));
            } else {
                $result['status'] = 0;
                $result['message'] = lang('email-verification-code-message-error', array('email' => $email));
            }
        } else {
            $result['status'] = 0;
            $result['message'] = lang('email-verification-code-message-error', array('email' => $email));
        }
        session_put('email-verification-resend-time', time());
        $result['html'] = view('account/verify/email', array('email' => $email, 'status' => $result['status'], 'message' => $result['message']));
    }
    if(input('ajax')) {
        $response = json_encode($result, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        return $response;
    } else {
        $app->setTitle($title);
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

function verify_phone_no($app) {
    $title = lang('verify-phone-no');
    $description = lang('phone-no-start-desc');

    $phone_no = preg_replace('/(^[^\+])/', '+$1', get_user_data('phone_no'));

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'phone_no' => $phone_no,
        'html' => '',
        'redirect_url' => ''
    );

    $val = input('val');
    if($val) {
        CSRFProtection::validate();
        if(phone_no_verify($val['code'])) {
            $result['status'] = 1;
            $result['message'] = lang('phone-no-verification-success');
            $result['redirect_url'] = url('account');
        } else {
            $result['status'] = 0;
            $result['message'] = lang('phone-no-verification-error');
        }
        $result['html'] = view('account/verify/phone_no', array('phone_no' => $phone_no, 'status' => $result['status'], 'message' => $result['message']));
    } else {
        if(time() - session_get('phone-no-verification-resend-time', 0) > config('phone-no-verification-resend-timeout', 60)) {
            if(phone_no_verify_send($phone_no)) {
                $result['status'] = 1;
                $result['message'] = lang('phone-no-verification-code-message-success', array('phone-no' => $phone_no));
            } else {
                $result['status'] = 0;
                $result['message'] = lang('phone-no-verification-code-message-error', array('phone-no' => $phone_no));
            }
        } else {
            $result['status'] = 0;
            $result['message'] = lang('phone-no-verification-code-message-error', array('phone-no' => $phone_no));
        }
        session_put('phone-no-verification-resend-time', time());
        $result['html'] = view('account/verify/phone_no', array('phone_no' => $phone_no, 'status' => $result['status'], 'message' => $result['message']));
    }
    if(input('ajax')) {
        $response = json_encode($result, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        return $response;
    } else {
        $app->setTitle($title);
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}