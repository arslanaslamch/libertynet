<?php
function weather_get_ip() {
	$ip = server('REMOTE_ADDR');
	$private = preg_match('/^192\.168/', $ip);
	$valid = ip2long($ip) !== false;
	if(!$valid || $private) {
		try {
			$response = @file_get_contents('https://api.ipify.org/?format=json');
			$ip_info = json_decode($response);
			$ip = isset($ip_info->ip) ? $ip_info->ip : false;
		} catch(Exception $e) {
			$ip = false;
		}
	}
	return $ip;
}

function weather_get_ip_info($ip = null) {
	$ip = isset($ip) ? $ip : weather_get_ip();
	try {
		$url = 'http://ip-api.com/json/'.$ip;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		$ip_info = json_decode($response);
	} catch(Exception $e) {
		$ip_info = false;
	}
	return $ip_info;
}

function weather_get_weather_info() {
	try {
		$ip_info = weather_get_ip_info();
		$city_name = $ip_info->city;
		$region_code = $ip_info->region;
		$api_key = config("open-weather-api-key");
        $url = 'http://api.openweathermap.org/data/2.5/forecast?appid='.$api_key.'&q='.$city_name.'&units=imperial';
        $content = file_get_contents($url);
	} catch(Exception $e) {
		$content = false;
	}
	return $content;
}

function weather_get_temp($fahr) {
	$temp_unit = config('weather-temp-unit', 'F');
	if($temp_unit == 'F') {
		$temp = ceil($fahr).'<span> °f </span>';
	} else {
		$temp = ceil(($fahr - 32) * 0.55).' <span> °c </span>';
	}
	return $temp;
}

function get_weather_week_day($time, $which = 'month', $no = 'M', $type = 'start') {
    $table = array('D' => '%a', 'l' => '%A', 'd' => '%d', 'j' => '%e', 'N' => '%u', 'w' => '%w', 'W' => '%W', 'M' => '%b', 'F' => '%B', 'm' => '%m', 'o' => '%G', 'y' => '%y', 'Y' => '%Y', 'H' => '%H', 'h' => '%I', 'g' => '%l', 'a' => '%P', 'A' => '%p', 'i' => '%M', 's' => '%S', 'U' => '%s', 'O' => '%z', 'T' => '%Z');
    return $time ? (isset($table[$no]) ? strftime($table[$no], $time) : date($no, $time)) : false;
}
