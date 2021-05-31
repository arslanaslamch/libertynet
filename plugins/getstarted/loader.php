<?php

register_hook('system.started', function ($app) {
    $allowed_segments = fire_hook('user.welcome.segment.allowed', array('api', 'account', 'membership', 'logout', 'promotion', 'relationship'));
    $allowed_segments[] = 'user';
    if (!is_admin() && is_loggedIn() && !in_array(segment(0), $allowed_segments)) {
        if (user_required_fields(true)) {
            redirect_to_pager('signup-welcome');
            exit;
        }
    }
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        register_asset('getstarted::css/getstarted.css');
        register_asset('getstarted::js/getstarted.js');
    }
});

register_pager('user/welcome', array(
    'use' => 'getstarted::signup@welcome_pager',
    'as' => 'signup-welcome',
    'filter' => 'auth'
));

register_hook("after-render-js", function($html) {
	$getstarted_phrases = json_encode(array(
		'next' => lang('getstarted::next'),
		'previous' => lang('previous'),
		'finish' => lang('getstarted::finish'),
		'current-step' => lang('getstarted::current-step'),
		'pagination' => lang('getstarted::pagination'),
		'loading' => lang('getstarted::loading')
	));
	$html .= <<<EOT
<script>
			var getstartedPhrases = $getstarted_phrases;
			</script>
EOT;
	return $html;
});