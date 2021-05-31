<?php

function facebook_auth_pager($app) {
    $app_id = config('facebook-app-id');
    $app_secret = config('facebook-secret-key');
    $my_url = url_to_pager('facebook-auth');
    $code = input('code');
    if (empty($code)) {
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        $dialog_url = "http://www.facebook.com/dialog/oauth?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&state=".$_SESSION['state'];
        redirect($dialog_url);
    }
    // if(input('state') != $_SESSION['state']) exit("The state does not match. You may be a victim of CSRF.");
    $token_url = "https://graph.facebook.com/oauth/access_token?"."client_id=".urlencode($app_id)."&redirect_uri=".urlencode($my_url)."&client_secret=".urlencode($app_secret)."&code=".urlencode($code);
    $ch = curl_init();
    try {
        $headers = array(
            'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
        );
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (!ini_get('open_basedir')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        if (FALSE === $response) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
    } catch (Exception $e) {
        echo curl_errno($ch).'<br/>';
        echo curl_error($ch).'<br/>';
        trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        exit;
    }
    curl_close($ch);
    $params = json_decode($response);
    if (isset($params->access_token) && $params->access_token) {
        $access_token = $params->access_token;
        $appsecret_proof = hash_hmac('sha256', $access_token, $app_secret);
        $graph_url = 'https://graph.facebook.com/me?access_token='.$access_token.'&appsecret_proof='.$appsecret_proof.'&fields=id,name,first_name,last_name,email,gender,picture.width(920).height(920)';
        $ch = curl_init();
        try {
            $headers = array(
                'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
            );
            curl_setopt($ch, CURLOPT_URL, $graph_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if (!ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            if (FALSE === $response) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
        } catch (Exception $e) {
            echo curl_errno($ch).'<br/>';
            echo curl_error($ch).'<br/>';
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
            exit;
        }
        curl_close($ch);
        $user = json_decode($response);
        $userProfile = $user;
    }
    if (isset($user) && $user) {
        $username = 'fb_'.$userProfile->id;
        $username = str_replace(array(' ', '.', '-'), '', $username);
        $details = array(
            'first_name' => $userProfile->first_name,
            'last_name' => $userProfile->last_name,
            'gender' => isset($userProfile->gender) && $userProfile->gender? $userProfile->gender : '',
            'country' => '',
            'email_address' => isset($userProfile->email) && $userProfile->email ? $userProfile->email : 'fb_'.$userProfile->id.'@facebook.com',
            'social_email' => 'fb_'.$userProfile->id.'@facebook.com',
            'password' => mt_rand(0, 9999).mt_rand(0, 9999).mt_rand(0, 9999),
            'username' => $username,
            'auth' => 'facebook',
            'authId' => $userProfile->id,
            'avatar' => isset($userProfile->picture->data->url) && $userProfile->picture->data->url ? $userProfile->picture->data->url : ''
        );
        return social_register_user($details);
    }

    return redirect($my_url);
}

function twitter_auth_pager($app) {

    try {
        $twitteroauth = getTwitter();
        $request_token = $twitteroauth->oauth(
            'oauth/request_token', [
                'oauth_callback' => url_to_pager('twitter-auth-data')
            ]
        );
        if ($twitteroauth->getLastHttpCode() != 200) {
            throw new \Exception('There was a problem performing this request');
        }
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $url = $twitteroauth->url(
            'oauth/authorize', [
                'oauth_token' => $request_token['oauth_token']
            ]
        );
        header('Location: '.$url);
    } catch (\ErrorException $e) {
        die($e->getMessage());
    }
}

function twitter_auth_data_pager($app) {
    $oauthVerifier = input('oauth_verifier');
    session_put('twitter_oauth_verifier', $oauthVerifier);
    fire_hook('twitter.auth.data');
    $oauthToken = session_get('oauth_token');
    $oauthTokenSecret = session_get('oauth_token_secret');

    if ($oauthVerifier and $oauthToken and $oauthTokenSecret) {
        $twitter = getTwitter($oauthToken, $oauthTokenSecret);
        $accessToken = $twitter->oauth('oauth/access_token', array('oauth_verifier' => $oauthVerifier));
        if ($twitter->getLastHttpCode() != 200) {
            die('Something went wrong, try again later');
        }
        session_forget('oauth_token');
        session_forget('oauth_token_secret');
        $twitter = getTwitter($accessToken['oauth_token'], $accessToken['oauth_token_secret']);
        $userProfile = $twitter->get('account/verify_credentials');
        $username = $userProfile->screen_name;
        $username = str_replace(array(' ', '.', '-'), '', $username);
        if ($twitter->getLastHttpCode() != 200) {
            die('Something went wrong, try again later');
        }
        if (isset($userProfile->error)) {
            return redirect_to_pager('twitter-auth');
        } else {
            $details = array(
                'first_name' => $userProfile->name,
                'last_name' => '',
                'genre' => '',
                'country' => '',
                'email_address' => 'tw_'.$userProfile->id.'@twitter.com',
                'social_email' => 'tw_'.$userProfile->id.'@twitter.com',
                'password' => mt_rand(0, 9999).mt_rand(0, 9999).mt_rand(0, 9999),
                'username' => $username,
                'avatar' => $userProfile->profile_image_url,
                'auth' => 'twitter',
                'authId' => $userProfile->id);

            return social_register_user($details);
        }
    } else {
        return redirect_to_pager('twitter-auth');
    }
}

function google_auth_pager($app) {
    $google = getGoogle();

    if (!input('code')) {
        //redirect to get its login
        return redirect($google->createAuthUrl());
    }


    try {
        //yes we have the code good
        $google->authenticate(input('code'));

        $google->setAccessToken($google->getAccessToken());
        $outh2 = new \Google_Service_Oauth2($google);
        $userinfo = $outh2->userinfo->get();

        $username = 'gplus_'.$userinfo->id;
        $username = str_replace(array(' ', '.', '-'), '', $username);
        $details = array(
            'first_name' => $userinfo->givenName,
            'last_name' => $userinfo->familyName,
            'gender' => ($userinfo->gender != null) ? $userinfo->gender : '',
            'country' => '',
            'email_address' => $userinfo->email ? $userinfo->email : 'gplus_'.$userinfo->id.'@google.com',
            'social_email' => 'gplus_'.$userinfo->id.'@google.com',
            'password' => mt_rand(0, 9999).mt_rand(0, 9999).mt_rand(0, 9999),
            'username' => $username,
            'auth' => 'google',
            'authId' => $userinfo->id,
            'avatar' => $userinfo->picture,
        );
        return social_register_user($details);

    } catch (\Exception $e) {
        //return \Redirect::to($google->createAuthUrl());
    }
}

function vk_auth_pager($app) {
    require_once(path('includes/libraries/vk/src/autoload.php'));
    $oauth = new VK\OAuth\VKOAuth();
	$client_id = config('vk-app-id');
	$redirect_uri = url_to_pager('vk-auth-data');
	$display = VK\OAuth\VKOAuthDisplay::PAGE;
	$scope = [VK\OAuth\Scopes\VKOAuthUserScope::WALL, VK\OAuth\Scopes\VKOAuthUserScope::GROUPS];
	$state = 'secret_state_code';
	$browser_url = $oauth->getAuthorizeUrl(VK\OAuth\VKOAuthResponseType::CODE, $client_id, $redirect_uri, $display, $scope, $state);
    return redirect($browser_url);
}

function vk_auth_data_pager($app) {
    require_once(path('includes/libraries/vk/src/autoload.php'));
    $oauth = new VK\OAuth\VKOAuth();
	$client_id = config('vk-app-id');
	$client_secret = config('vk-secret-key');
	$redirect_uri = url_to_pager('vk-auth-data');
    $code = input('code');
	try {
		$response = $oauth->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
		$access_token = $response['access_token'];
		$user_id = $response['user_id'];
		$vk = new VK\Client\VKApiClient();
		$response = $vk->users()->get($access_token, [
			'user_ids'  => [$user_id],
			'fields'    => ['id', 'first_name', 'last_name', 'nickname', 'screen_name', 'photo_big', 'gender', 'city', 'photo'],
		]);
	} catch(\Exception $e) {
		return redirect(url_to_pager('vk-auth'));
	}

	$user = $response[0];

	/**
	 * @var $first_name
	 * @var $last_name
	 * @var $screen_name
	 * @var $uid
	 * @var $gender
	 * @var $photo_big
	 */
	extract($user);

	$details = array(
		'first_name' => $first_name,
		'last_name' => $last_name,
		'gender' => '',
		'country' => '',
		'email_address' => $screen_name.'@vk.com',
		'social_email' => $screen_name.'@vk.com',
		'password' => mt_rand(0, 9999).mt_rand(0, 9999).mt_rand(0, 9999),
		'username' => $screen_name,
		'auth' => 'vk',
		'avatar' => $photo_big,
		'authId' => $id);

	return social_register_user($details);
}

function facebook_import_pager($app) {
    $app_id = config('facebook-app-id');
    $app_secret = config('facebook-secret-key');
    $my_url = url_to_pager('social-import-facebook');
    $code = input('code');
    if (empty($code)) {
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        $dialog_url = "http://www.facebook.com/dialog/oauth?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&state=".$_SESSION['state'];
        redirect($dialog_url);
    }
    // if(input('state') != $_SESSION['state']) exit("The state does not match. You may be a victim of CSRF.");
    $token_url = "https://graph.facebook.com/oauth/access_token?"."client_id=".urlencode($app_id)."&redirect_uri=".urlencode($my_url)."&client_secret=".urlencode($app_secret)."&code=".urlencode($code);
    $ch = curl_init();
    try {
        $headers = array(
            'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
        );
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (!ini_get('open_basedir')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        if (FALSE === $response) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
    } catch (Exception $e) {
        echo curl_errno($ch).'<br/>';
        echo curl_error($ch).'<br/>';
        trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        exit;
    }
    curl_close($ch);
    $params = json_decode($response);
    if (isset($params->access_token) && $params->access_token) {
        $access_token = $params->access_token;
        $appsecret_proof = hash_hmac('sha256', $access_token, $app_secret);
        $graph_url = "https://graph.facebook.com/me/friends?access_token=".$access_token."&appsecret_proof=".$appsecret_proof."&fields=id,name,first_name,last_name,email,gender";
        $ch = curl_init();
        try {
            $headers = array(
                'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
            );
            curl_setopt($ch, CURLOPT_URL, $graph_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if (!ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            if (FALSE === $response) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
        } catch (Exception $e) {
            echo curl_errno($ch).'<br/>';
            echo curl_error($ch).'<br/>';
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
            exit;
        }
        curl_close($ch);
        $user = json_decode($response);
        $friends = $user;
    }
    if (isset($user) && $user) {
        if (isset($friends->data)) {
            $emails = array();
            foreach ($friends->data as $friend) {
                $avatar = '';
                try {
                    $graph_url = 'https://graph.facebook.com/'.$friend['id'].'/picture?redirect=false&width=200&height=200';
                    $ch = curl_init();
                    try {
                        $headers = array(
                            'Referer: https://www.google.com.ng/_/chrome/newtab-serviceworker.js',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.59 Safari/537.36'
                        );
                        curl_setopt($ch, CURLOPT_URL, $graph_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        if (!ini_get('open_basedir')) {
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        }
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $response = curl_exec($ch);
                        if (FALSE === $response) {
                            throw new Exception(curl_error($ch), curl_errno($ch));
                        }
                    } catch (Exception $e) {
                        echo curl_errno($ch).'<br/>';
                        echo curl_error($ch).'<br/>';
                        trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
                        exit;
                    }
                    curl_close($ch);
                    $avatar = json_decode($response, true);
                    if ($avatar and isset($avatar['data']['url'])) {
                        $avatar = $avatar['data']['url'];
                        //$details['avatar'] = $avatar;
                    }
                } catch (\Exception $e) {
                }
                $emails[] = array(
                    'name' => $friend['name'],
                    'email' => 'fb_'.$friend['id'].'@facebook.com',
                    'avatar' => $avatar
                );
            }
            social_add_imports($emails, 'facebook');
            $emails = array(
                'type' => 'facebook',
                'emails' => $emails
            );
            session_put("invitee-imports", perfectSerialize($emails));
            echo "<script>window.close();</script>";
        } else {
            echo "<script>window.close();</script>";
        }
    } else {
        $_SESSION['state'] = md5(uniqid(rand(), TRUE));
        $dialog_url = "http://www.facebook.com/dialog/oauth?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&state=".$_SESSION['state'];
        redirect($dialog_url);
    }
}

function social_import_confirm_pager($app) {
    if (session_get('invitee-imports')) return 1;
    return 0;
}

function social_get_imports_pager($app) {
    $emails = session_get("invitee-imports");
    $emails = (!empty($emails)) ? perfectUnserialize($emails) : null;
    session_forget("invitee-imports");

    if (isset($emails['type'])) {
        return view('social::import/display', array('type' => $emails['type'], 'contacts' => $emails['emails']));
    }
}

function social_invite_user_pager($app) {
    CSRFProtection::validate(false);
    $email = input('email');
    mailer()->setAddress($email, '')->template('social-invite-member', array(
        'link' => url('signup'),
        'site-title' => config('site_title'),
        'inviter' => get_user_name(),
        'inviter-link' => profile_url(),
        'inviter-avatar' => get_avatar(75),
        'reg-link' => url_to_pager('signup')
    ))->send();

    return true;
}

function gmail_import_pager($app) {
    ini_set('max_execution_time', 300);

    require_once path('includes/libraries/Google/src/Google/Client.php');

    try {
        $client = new Google_Client();
        $client->setClientId(config('google-oauth-client-id'));
        $client->setClientSecret(config('google-oauth-client-secret'));
        $client->setRedirectUri(url_to_pager('social-import-gmail'));
        $client->addScope("https://www.google.com/m8/feeds");
        $accessToken = null;
        $code = input('code');
        if ($code) {
            $client->authenticate($code);
            $accessToken = $client->getAccessToken();
        }

        if (!empty($accessToken)) {

            $client->setAccessToken($accessToken);
            $access_token = json_decode($client->getAccessToken())->access_token;

            $limit = 1000;
            $url = "https://www.google.com/m8/feeds/contacts/default/full?alt=json&v=3.0&max-results=".$limit."&oauth_token=".$access_token;
            //ini_set('user_agent', 'Mozilla/5.0');
            $response = curl_get_content($url);

            $result = json_decode($response, true);
            $emails = array();

            if (isset($result['feed']['entry'])) {
                foreach ($result['feed']['entry'] as $entry) {
                    if (isset($entry['gd$email'])) {
                        $e = $entry['gd$email'][0]['address'];
                        $name = (isset($entry['gd$name']['gd$fullName']['$t'])) ? $entry['gd$name']['gd$fullName']['$t'] : '';
                        if (!$name) {
                            $e = explode('@', $e);
                            $name = $e[0];
                        }
                        $email = array(
                            'name' => $name,
                            'email' => $entry['gd$email'][0]['address'],
                            'avatar' => ''
                        );
                        $emails[] = $email;
                    }

                }
                social_add_imports($emails, 'gmail');
                $emails = array(
                    'type' => 'gmail',
                    'emails' => $emails
                );

                session_put("invitee-imports", perfectSerialize($emails));

                echo "<script>window.close();</script>";
            } else {
                $authUrl = $client->createAuthUrl();
                return redirect($authUrl);
            }

        } else {
            $authUrl = $client->createAuthUrl();

            return redirect($authUrl);
        }
    } catch (Exception $e) {
        session_put("invitee-imports", perfectSerialize(array()));
        echo "<script>window.close();</script>";
    }
}