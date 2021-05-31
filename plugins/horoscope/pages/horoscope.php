<?php
function horoscope_pager($app)
{
    $app->setTitle(lang('horoscope::daily-horoscope'));
    //$app->leftMenu = false;
    $sign = input('sign');
    $signs = horoscope_get_signs_horoscope();
    $horoscope = false;
    if ($sign) {
        $horoscope = get_sign_horoscope($sign);
    }
    return $app->render(view('horoscope::horoscope', ['signs' => $signs, 'horoscope' => $horoscope]));
}
