<?php

get_menu('admin-menu', 'settings')->setActive();

get_menu('admin-menu', 'settings')->findMenu('text-message-manager')->setActive();

function settings_pager($app) {
	$app->setTitle(lang('text-message-settings'));
	$val = input("val");
	if($val) {
		CSRFProtection::validate();
		//we are saving the settings
		save_admin_settings($val);
	}

	$settings = array(
		'text-message-driver' => array(
			'type' => 'selection',
			'title' => lang('text-message-driver'),
			'description' => lang('text-message-driver-desc'),
			'value' => 'TwilioTextMessage',
			'data' => array(
				'TwilioTextMessage' => 'Twilio',
				'NexmoTextMessage' => 'Nexmo'
			)
		),

        'text-message-phone-number' => array(
            'type' => 'text',
            'title' => lang('text-message-phone-number'),
            'description' => lang('text-message-phone-number-desc'),
            'value' => '',
        ),

        'text-message-twilio-account-sid' => array(
            'type' => 'text',
            'title' => lang('text-message-twilio-account-sid'),
            'description' => lang('text-message-twilio-account-sid-desc'),
            'value' => '',
        ),

        'text-message-twilio-auth-token' => array(
            'type' => 'text',
            'title' => lang('text-message-twilio-auth-token'),
            'description' => lang('text-message-twilio-auth-token-desc'),
            'value' => '',
        ),

        'text-message-nexmo-api-key' => array(
            'type' => 'text',
            'title' => lang('text-message-nexmo-api-key'),
            'description' => lang('text-message-nexmo-api-key-desc'),
            'value' => '',
        ),

        'text-message-nexmo-api-secret' => array(
            'type' => 'text',
            'title' => lang('text-message-nexmo-api-secret'),
            'description' => lang('text-message-nexmo-api-secret-desc'),
            'value' => '',
        ),
    );
	
	return $app->render(view('settings/content', array('title' => lang('text-message-settings'), 'message' => '', 'description' => lang('text-message-settings-desc'), 'settings' => $settings)));
}

function text_messaging_pager($app) {
	$app->setTitle(lang('text-messaging'));
    $val = input('val', null, array('body', 'subject'));
	$message = null;
	if($val) {
		CSRFProtection::validate();
		/**
		 * @var $subject
		 * @var $body
		 * @var $to
		 * @var $non
		 * @var $selected
		 * @var $subscription
		 */
		extract($val);
        $db = db();
		$query = null;
		if($to == 'all') {
			$query = $db->query("SELECT * FROM users ");
		} elseif($to == 'selected') {
			if(isset($selected)) {
				$selected = implode(',', $selected);
				$query = $db->query("SELECT * FROM users  WHERE id IN ({$selected}) ");
			}
		} elseif($to == 'non-active') {
			$number = (int) $non['number'];
			$type = $non['type'];
			$time = time();
			if($type == 'day') {
				$time = $time - ($number * 86400);
			} elseif($type == 'month') {
				$time = $time - ($number * 2628000);
			} else {
				$time = $time - ($number * 31540000);
			}
			$query = $db->query("SELECT * FROM users  WHERE online_time < {$time}");
		}
		if($query) {
			ini_set('max_execution_time', '0');
			$sent = 0;
			$total = 0;
            $text_message = new TextMessage();
			while($user = $query->fetch_assoc()) {
                $total++;
			    if($user['phone_no'] && $text_message->send($body, $user['phone_no'])) {
                    $sent++;
                }
			}
			$message = lang('num-text-message-sent', array('num' => $sent.'/'.$total));
		} else {
			$message = lang('text-message-not-sent');
		}
	}

	return $app->render(view('text_message/bulk', array('message' => $message)));
}
