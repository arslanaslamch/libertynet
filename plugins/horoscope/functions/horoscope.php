<?php
function horoscope_get_signs()
{
    return [
        [
            'title' => 'Aquarius',
            'date' => 'January 20 - February 18',
        ],
        [
            'title' => 'Pisces',
            'date' => 'February 19 - March 20',
        ],
        [
            'title' => 'Aries',
            'date' => 'March 21 - April 19',
        ],
        [
            'title' => 'Taurus',
            'date' => 'April 20 - May 20',
        ],
        [
            'title' => 'Gemini',
            'date' => 'May 21 - June 20',
        ],
        [
            'title' => 'Cancer',
            'date' => 'June 21 - July 22',
        ],
        [
            'title' => 'Leo',
            'date' => 'July 23 - August 22',
        ],
        [
            'title' => 'Virgo',
            'date' => 'August 23 - September 22',
        ],
        [
            'title' => 'Libra',
            'date' => 'September 23 - October 22',
        ],
        [
            'title' => 'Scorpio',
            'date' => 'October 23 - November 21',
        ],
        [
            'title' => 'Sagittarius',
            'date' => 'November 22 - December 21',
        ],
        [
            'title' => 'Capricorn',
            'date' => 'December 22 - January 19',
        ],
    ];
}

function horoscope_get_signs_horoscope()
{
    $signs = horoscope_get_signs();
    foreach ($signs as $key => $sign) {
        $res = fetch_api("http://horoscope-api.herokuapp.com/horoscope/today/" . $sign['title']);
        $res = (array) json_decode($res);
        $res['horoscope'] = str_replace('Ganesha', 'Mother Earth', $res['horoscope']);
        $signs[$key]['horoscope'] = $res['horoscope'];
    }
    return $signs;
}

function get_sign_horoscope($sign)
{
    $res = fetch_api("http://horoscope-api.herokuapp.com/horoscope/today/$sign");
    $res = (array) json_decode($res);
    return str_replace('Ganesha', 'Mother Earth', $res['horoscope']);
}
