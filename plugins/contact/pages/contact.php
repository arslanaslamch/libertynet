<?php
function contact_pager($app) {
    $app->setTitle(lang('contact-us'));
    $message = null;
    $val = input('val', null, array('message'));
    if ($val) {
        CSRFProtection::validate();
        if (config('enable-captcha') == 2) {
            require_once path('includes/libraries/recaptcha/autoload.php');
            $recaptcha = new \ReCaptcha\ReCaptcha(config('recaptcha-secret'));
            $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        }
        if (!config("enable-captcha") or
            (config('enable-captcha') == 1 and strtolower(session_get("sv_captcha")) == strtolower(input('captcha')))
            or (config('enable-captcha') == 2 and $resp->isSuccess())) {
            $message = submit_contact($val) ? lang('contact::message-sent') : lang('contact::message-not-sent');
        } else {
            $message = lang('invalid-captcha-code');
        }
    }
    return $app->render(view('contact::contact', array('message' => $message)));
}