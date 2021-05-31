<?php
/**
 * function to add country
 * @param string $name
 * @return boolean
 */
function add_country($name) {
	if(country_exists($name)) return false;
	db()->query("INSERT INTO `countries` (`country_name`) VALUES('{$name}')");
	fire_hook("country_added", $name, array($name));
	forget_cache("countries");
	return true;
}

function country_exists($name) {
	$query = db()->query("SELECT country_name FROM `countries` WHERE `country_name`='{$name}'");
	if($query and $query->num_rows > 0) return true;
	return false;
}

function get_countries() {
	if(cache_exists("countries")) {
		return get_cache("countries");
	} else {
		$query = db()->query("SELECT `country_name`,`id` FROM `countries` ORDER BY `country_name` ASC");
		if($query) {
			$r = fetch_all($query);
			$result = array();
			foreach($r as $k) {
				$result[$k['id']] = $k['country_name'];
			}
			set_cacheForever("countries", $result);
			return $result;
		} else {
			return array();
		}
	}
}

function get_country($id) {
	$countries = get_countries();
	if(isset($countries[$id])) return $countries[$id];
	return false;
}

function save_country($id, $name) {
	db()->query("UPDATE `countries` SET `country_name`='{$name}' WHERE `id`='{$id}'");
	forget_cache("countries");
}

function delete_country($id) {
	db()->query("DELETE FROM `countries` WHERE `id`='{$id}'");
	forget_cache("countries");
}

function count_states($id) {
	return count(get_states($id));
}

function get_states($id) {
	$key = "country_".$id."_states";
	if(cache_exists($key)) {
		return get_cache($key);
	} else {
		$query = db()->query("SELECT `id`,`state_name` FROM `country_states` WHERE `country_id`='{$id}' ORDER BY `state_name`");
		if($query) {
			$r = fetch_all($query);
			$result = array();
			foreach($r as $k) {
				$result[$k['id']] = $k['state_name'];
			}
			set_cacheForever($key, $result);
			return $result;
		} else {
			return array();
		}
	}
}

function add_state($id, $name) {
	$key = "country_".$id."_states";
	if(state_exists($id, $name)) return false;
	$query = db()->query("INSERT INTO `country_states` (country_id,state_name) VALUES('{$id}','{$name}')");
	forget_cache($key);
	fire_hook("state_added", $name, array($id, $name));
	return true;
}

function state_exists($id, $name) {
	$query = db()->query("SELECT state_name FROM `country_states` WHERE `country_id`='{$id}' AND `state_name`='{$name}'");
	if($query and $query->num_rows > 0) return true;
	return false;
}

function get_state($id) {
	$query = db()->query("SELECT state_name,country_id FROM country_states WHERE id='{$id}'");
	if($query) return fetch_all($query);
	return false;
}

function delete_state($id, $country) {
	db()->query("DELETE FROM `country_states` WHERE `id`='{$id}'");
	$key = "country_".$country."_states";
	forget_cache($key);
}

function save_state($id, $name, $country) {
	db()->query("UPDATE country_states SET state_name='{$name}',country_id='{$country}' WHERE id='{$id}'");
	$key = "country_".$country."_states";
	forget_cache($key);
	return true;
}

function is_valid_country($country) {
	$countries = get_countries();
	if(in_array($country, $countries)) return true;
	return false;
}

function get_iso_countries() {
    return array (
        'ac' => 'Ascension Island',
        'ad' => 'Andorra',
        'ae' => 'United Arab Emirates',
        'af' => 'Afghanistan',
        'ag' => 'Antigua and Barbuda',
        'ai' => 'Anguilla',
        'al' => 'Albania',
        'am' => 'Armenia',
        'ao' => 'Angola',
        'aq' => 'Antarctica',
        'ar' => 'Argentina',
        'as' => 'American Samoa',
        'at' => 'Austria',
        'au' => 'Australia',
        'aw' => 'Aruba',
        'ax' => 'Åland Islands',
        'az' => 'Azerbaijan',
        'ba' => 'Bosnia and Herzegovina',
        'bb' => 'Barbados',
        'bd' => 'Bangladesh',
        'be' => 'Belgium',
        'bf' => 'Burkina Faso',
        'bg' => 'Bulgaria',
        'bh' => 'Bahrain',
        'bi' => 'Burundi',
        'bj' => 'Benin',
        'bl' => 'Saint Barthélemy',
        'bm' => 'Bermuda',
        'bn' => 'Brunei Darussalam',
        'bo' => 'Bolivia',
        'bq' => 'Bonaire, Sint Eustatius and Saba',
        'br' => 'Brazil',
        'bs' => 'Bahamas',
        'bt' => 'Bhutan',
        'bv' => 'Bouvet Island',
        'bw' => 'Botswana',
        'by' => 'Belarus',
        'bz' => 'Belize',
        'ca' => 'Canada',
        'cc' => 'Cocos (Keeling) Islands',
        'cd' => 'Congo, the Democratic Republic of the',
        'cf' => 'Central African Republic',
        'cg' => 'Congo',
        'ch' => 'Switzerland',
        'ci' => 'Côte d\'Ivoire',
        'ck' => 'Cook Islands',
        'cl' => 'Chile',
        'cm' => 'Cameroon',
        'cn' => 'China',
        'co' => 'Colombia',
        'cr' => 'Costa Rica',
        'cu' => 'Cuba',
        'cv' => 'Cape Verde',
        'cw' => 'Curaçao',
        'cx' => 'Christmas Island',
        'cy' => 'Cyprus',
        'cz' => 'Czech Republic',
        'de' => 'Germany',
        'dg' => 'Diego Garcia',
        'dj' => 'Djibouti',
        'dk' => 'Denmark',
        'dm' => 'Dominica',
        'do' => 'Dominican Republic',
        'dz' => 'Algeria',
        'ec' => 'Ecuador',
        'ee' => 'Estonia',
        'eg' => 'Egypt',
        'eh' => 'Western Sahara',
        'er' => 'Eritrea',
        'es' => 'Spain',
        'et' => 'Ethiopia',
        'fi' => 'Finland',
        'fj' => 'Fiji',
        'fk' => 'Falkland Islands',
        'fm' => 'Micronesia',
        'fo' => 'Faroe Islands',
        'fr' => 'France',
        'ga' => 'Gabon',
        'gb' => 'United Kingdom',
        'gd' => 'Grenada',
        'ge' => 'Georgia',
        'gf' => 'French Guiana',
        'gg' => 'Guernsey',
        'gh' => 'Ghana',
        'gi' => 'Gibraltar',
        'gl' => 'Greenland',
        'gm' => 'Gambia',
        'gn' => 'Guinea',
        'gp' => 'Guadeloupe',
        'gq' => 'Equatorial Guinea',
        'gr' => 'Greece',
        'gs' => 'South Georgia and the South Sandwich Islands',
        'gt' => 'Guatemala',
        'gu' => 'Guam',
        'gw' => 'Guinea-Bissau',
        'gy' => 'Guyana',
        'hk' => 'Hong Kong',
        'hm' => 'Heard Island and McDonald Islands',
        'hn' => 'Honduras',
        'hr' => 'Croatia',
        'ht' => 'Haiti',
        'hu' => 'Hungary',
        'id' => 'Indonesia',
        'ie' => 'Ireland',
        'il' => 'Israel',
        'im' => 'Isle of Man',
        'in' => 'India',
        'io' => 'British Indian Ocean Territory',
        'iq' => 'Iraq',
        'ir' => 'Iran',
        'is' => 'Iceland',
        'it' => 'Italy',
        'je' => 'Jersey',
        'jm' => 'Jamaica',
        'jo' => 'Jordan',
        'jp' => 'Japan',
        'ke' => 'Kenya',
        'kg' => 'Kyrgyzstan',
        'kh' => 'Cambodia',
        'ki' => 'Kiribati',
        'km' => 'Comoros and Mayotte',
        'kn' => 'Saint Kitts and Nevis',
        'kp' => 'North Korea',
        'kr' => 'South Korea',
        'kw' => 'Kuwait',
        'ky' => 'Cayman Islands',
        'kz' => 'Kazakhstan',
        'la' => 'Laos',
        'lb' => 'Lebanon',
        'lc' => 'Saint Lucia',
        'li' => 'Liechtenstein',
        'lk' => 'Sri Lanka',
        'lr' => 'Liberia',
        'ls' => 'Lesotho',
        'lt' => 'Lithuania',
        'lu' => 'Luxembourg',
        'lv' => 'Latvia',
        'ly' => 'Libya',
        'ma' => 'Morocco',
        'mc' => 'Monaco',
        'md' => 'Moldova',
        'me' => 'Montenegro',
        'mf' => 'Saint Martin (French part)',
        'mg' => 'Madagascar',
        'mh' => 'Marshall Islands',
        'mk' => 'North Macedonia',
        'ml' => 'Mali',
        'mm' => 'Myanmar',
        'mn' => 'Mongolia',
        'mo' => 'Macao',
        'mp' => 'North Mariana Islands',
        'mq' => 'Martinique',
        'mr' => 'Mauritania',
        'ms' => 'Montserrat',
        'mt' => 'Malta',
        'mu' => 'Mauritius',
        'mv' => 'Maldives',
        'mw' => 'Malawi',
        'mx' => 'Mexico',
        'my' => 'Malaysia',
        'mz' => 'Mozambique',
        'na' => 'Namibia',
        'nc' => 'New Caledonia',
        'ne' => 'Niger',
        'nf' => 'Norfolk Island',
        'ng' => 'Nigeria',
        'ni' => 'Nicaragua',
        'nl' => 'Netherlands',
        'no' => 'Norway',
        'np' => 'Nepal',
        'nr' => 'Nauru',
        'nu' => 'Niue',
        'nz' => 'New Zealand',
        'om' => 'Oman',
        'pa' => 'Panama',
        'pe' => 'Peru',
        'pf' => 'French Polynesia',
        'pg' => 'Papua New Guinea',
        'ph' => 'Philippines',
        'pk' => 'Pakistan',
        'pl' => 'Poland',
        'pm' => 'Saint Pierre and Miquelon',
        'pn' => 'Pitcairn',
        'pr' => 'Puerto Rico',
        'ps' => 'Palestine',
        'pt' => 'Portugal',
        'pw' => 'Palau',
        'py' => 'Paraguay',
        'qa' => 'Qatar',
        're' => 'Réunion',
        'ro' => 'Romania',
        'rs' => 'Serbia',
        'ru' => 'Russia',
        'rw' => 'Rwanda',
        'sa' => 'Saudi Arabia',
        'sb' => 'Solomon Islands',
        'sc' => 'Seychelles',
        'sd' => 'Sudan',
        'se' => 'Sweden',
        'sg' => 'Singapore',
        'sh' => 'Saint Helena',
        'si' => 'Slovenia',
        'sj' => 'Svalbard and Jan Mayen',
        'sk' => 'Slovakia',
        'sl' => 'Sierra Leone',
        'sm' => 'San Marino',
        'sn' => 'Senegal',
        'so' => 'Somalia',
        'sr' => 'Suriname',
        'ss' => 'South Sudan',
        'st' => 'São Tomé and Príncipe',
        'sv' => 'El Salvador',
        'sx' => 'Sint Maarten (Dutch part)',
        'sy' => 'Syria',
        'sz' => 'Swaziland',
        'tc' => 'Turks and Caicos Islands',
        'td' => 'Chad',
        'tf' => 'French Southern Territories',
        'tg' => 'Togo',
        'th' => 'Thailand',
        'tj' => 'Tajikistan',
        'tk' => 'Tokelau',
        'tl' => 'Timor-Leste',
        'tm' => 'Turkmenistan',
        'tn' => 'Tunisia',
        'to' => 'Tonga',
        'tr' => 'Turkey',
        'tt' => 'Trinidad and Tobago',
        'tv' => 'Tuvalu',
        'tw' => 'Taiwan',
        'tz' => 'Tanzania',
        'ua' => 'Ukraine',
        'ug' => 'Uganda',
        'um' => 'United States Minor Outlying Islands',
        'us' => 'United States',
        'uy' => 'Uruguay',
        'uz' => 'Uzbekistan',
        'va' => 'Vatican City',
        'vc' => 'Saint Vincent and the Grenadines',
        've' => 'Venezuela',
        'vg' => 'Virgin Islands, British',
        'vi' => 'Virgin Islands, U.S.',
        'vn' => 'Vietnam',
        'vu' => 'Vanuatu',
        'wf' => 'Wallis and Futuna',
        'ws' => 'Samoa',
        'xk' => 'Kosovo',
        'yd' => 'South Yemen',
        'ye' => 'Yemen',
        'yt' => 'Mayotte',
        'za' => 'South Africa',
        'zm' => 'Zambia',
        'zw' => 'Zimbabwe',
    );
}

function get_iso_country_calling_codes() {
    $iso_codes = array (
        'ac' => '247',
        'ad' => '376',
        'ae' => '971',
        'af' => '93',
        'ag' => '1 268',
        'ai' => '1 264',
        'al' => '355',
        'am' => '374',
        'ao' => '244',
        'aq' => '672 1',
        'ar' => '54',
        'as' => '1 684',
        'at' => '43',
        'au' => '61',
        'aw' => '297',
        'ax' => '358 18',
        'az' => '994',
        'ba' => '387',
        'bb' => '1 246',
        'bd' => '880',
        'be' => '32',
        'bf' => '226',
        'bg' => '359',
        'bh' => '973',
        'bi' => '257',
        'bj' => '229',
        'bl' => '590',
        'bm' => '1 441',
        'bn' => '673',
        'bo' => '591',
        'bq' => '599 7, 599 3, 599 4',
        'br' => '55',
        'bs' => '1 242',
        'bt' => '975',
        'bv' => '55',
        'bw' => '267',
        'by' => '375',
        'bz' => '501',
        'ca' => '1',
        'cc' => '61 89162',
        'cd' => '243',
        'cf' => '236',
        'cg' => '242',
        'ch' => '41',
        'ci' => '225',
        'ck' => '682',
        'cl' => '56',
        'cm' => '237',
        'cn' => '86',
        'co' => '57',
        'cr' => '506',
        'cu' => '53',
        'cv' => '238',
        'cw' => '599 9',
        'cx' => '61 89164',
        'cy' => '357',
        'cz' => '420',
        'de' => '49',
        'dg' => '246',
        'dj' => '253',
        'dk' => '45',
        'dm' => '1 767',
        'do' => '1 809, 1 829, 1 849',
        'dz' => '213',
        'ec' => '593',
        'ee' => '372',
        'eg' => '20',
        'eh' => '212',
        'er' => '291',
        'es' => '34',
        'et' => '251',
        'fi' => '358',
        'fj' => '679',
        'fk' => '500',
        'fm' => '691',
        'fo' => '298',
        'fr' => '33',
        'ga' => '241',
        'gb' => '44',
        'gd' => '1 473',
        'ge' => '995',
        'gf' => '594',
        'gg' => '44 1481, 44 7781, 44 7839, 44 7911',
        'gh' => '233',
        'gi' => '350',
        'gl' => '299',
        'gm' => '220',
        'gn' => '224',
        'gp' => '590',
        'gq' => '240',
        'gr' => '30',
        'gs' => '500',
        'gt' => '502',
        'gu' => '1 671',
        'gw' => '245',
        'gy' => '592',
        'hk' => '852',
        'hm' => '0',
        'hn' => '504',
        'hr' => '385',
        'ht' => '509',
        'hu' => '36',
        'id' => '62',
        'ie' => '353',
        'il' => '972',
        'im' => '44 1624, 44 7524, 44 7624, 44 7924',
        'in' => '91',
        'io' => '246',
        'iq' => '964',
        'ir' => '98',
        'is' => '354',
        'it' => '39',
        'je' => '44 1534',
        'jm' => '1 876',
        'jo' => '962',
        'jp' => '81',
        'ke' => '254',
        'kg' => '996',
        'kh' => '855',
        'ki' => '686',
        'km' => '269',
        'kn' => '1 869',
        'kp' => '850',
        'kr' => '82',
        'kw' => '965',
        'ky' => '1 345',
        'kz' => '77 6, 7 7',
        'la' => '856',
        'lb' => '961',
        'lc' => '1 758',
        'li' => '423',
        'lk' => '94',
        'lr' => '231',
        'ls' => '266',
        'lt' => '370',
        'lu' => '352',
        'lv' => '371',
        'ly' => '218',
        'ma' => '212',
        'mc' => '377',
        'md' => '373',
        'me' => '382',
        'mf' => '590',
        'mg' => '261',
        'mh' => '692',
        'mk' => '389',
        'ml' => '223',
        'mm' => '95',
        'mn' => '976',
        'mo' => '853',
        'mp' => '1 670',
        'mq' => '596',
        'mr' => '222',
        'ms' => '1 664',
        'mt' => '356',
        'mu' => '230',
        'mv' => '960',
        'mw' => '265',
        'mx' => '52',
        'my' => '60',
        'mz' => '258',
        'na' => '264',
        'nc' => '687',
        'ne' => '227',
        'nf' => '672 3',
        'ng' => '234',
        'ni' => '505',
        'nl' => '31',
        'no' => '47',
        'np' => '977',
        'nr' => '674',
        'nu' => '683',
        'nz' => '64',
        'om' => '968',
        'pa' => '507',
        'pe' => '51',
        'pf' => '689',
        'pg' => '675',
        'ph' => '63',
        'pk' => '92',
        'pl' => '48',
        'pm' => '508',
        'pn' => '64870',
        'pr' => '1 787, 1 939',
        'ps' => '970',
        'pt' => '351',
        'pw' => '680',
        'py' => '595',
        'qa' => '974',
        're' => '262',
        'ro' => '40',
        'rs' => '381',
        'ru' => '7',
        'rw' => '250',
        'sa' => '966',
        'sb' => '677',
        'sc' => '248',
        'sd' => '249',
        'se' => '46',
        'sg' => '65',
        'sh' => '290',
        'si' => '386',
        'sj' => '47 79',
        'sk' => '421',
        'sl' => '232',
        'sm' => '378',
        'sn' => '221',
        'so' => '252',
        'sr' => '597',
        'ss' => '211',
        'st' => '239',
        'sv' => '503',
        'sx' => '1 721',
        'sy' => '963',
        'sz' => '268',
        'tc' => '1 649',
        'td' => '235',
        'tf' => '262',
        'tg' => '228',
        'th' => '66',
        'tj' => '992',
        'tk' => '690',
        'tl' => '670',
        'tm' => '993',
        'tn' => '216',
        'to' => '676',
        'tr' => '90',
        'tt' => '1 868',
        'tv' => '688',
        'tw' => '886',
        'tz' => '255',
        'ua' => '380',
        'ug' => '256',
        'um' => '1 808',
        'us' => '1',
        'uy' => '598',
        'uz' => '998',
        'va' => '3939 06 698, 379',
        'vc' => '1 784',
        've' => '58',
        'vg' => '1 284',
        'vi' => '1 340',
        'vn' => '84',
        'vu' => '678',
        'wf' => '681',
        'ws' => '685',
        'xk' => '383',
        'yd' => '381',
        'ye' => '967',
        'yt' => '262 269, 262 639',
        'za' => '27',
        'zm' => '260',
        'zw' => '263',
    );
    $iso_countries = get_iso_countries();
    $iso_country_calling_codes = array();
    foreach ($iso_codes as $iso => $codes) {
        foreach (explode(', ', $codes) as $code) {
            $iso_country_calling_codes[] = array(
                'iso' => $iso,
                'country_name' => isset($iso_countries[$iso]) ? $iso_countries[$iso] : $iso,
                'country_flag' => get_country_flag($iso),
                'code' => $code
            );
        }
    }
    usort($iso_country_calling_codes, function ($a, $b) {
        return strcmp($a['country_name'], $b['country_name']);
    });
    return $iso_country_calling_codes;
}

function get_phone_number_country_code($phone_number) {
    $iso_country_calling_codes = get_iso_country_calling_codes();
    usort($iso_country_calling_codes, function ($a, $b) use ($phone_number) {
        $a = strlen(preg_replace('/^'.str_replace(' ', '', $a['code']).'/', '', $phone_number));
        $b = strlen(preg_replace('/^'.str_replace(' ', '', $b['code']).'/', '', $phone_number));
        if ($a == $b) {
            return 0;
        }
        return $a < $b ? -1 : 1;
    });
    $phone_number_country_code = $iso_country_calling_codes[0];
    $phone_number_country_code['number'] = preg_replace('/^'.str_replace(' ', '', $phone_number_country_code['code']).'/', '', $phone_number);
    return $phone_number_country_code;
}

function get_world_languages() {
	return array(
		'af' => array(
			'name' => 'afrikaans',
			'title' => 'Afrikaans'
		),
		'sq' => array(
			'name' => 'albanian',
			'title' => 'Albanian'
		),
		'am' => array(
			'name' => 'amharic',
			'title' => 'Amharic'
		),
		'ar' => array(
			'name' => 'arabic',
			'title' => 'Arabic'
		),
		'hy' => array(
			'name' => 'armenian',
			'title' => 'Armenian'
		),
		'az' => array(
			'name' => 'azerbaijani',
			'title' => 'Azerbaijani'
		),
		'eu' => array(
			'name' => 'basque',
			'title' => 'Basque'
		),
		'be' => array(
			'name' => 'belarusian',
			'title' => 'Belarusian'
		),
		'bn' => array(
			'name' => 'bengali',
			'title' => 'Bengali'
		),
		'bs' => array(
			'name' => 'bosnian',
			'title' => 'Bosnian'
		),
		'bg' => array(
			'name' => 'bulgarian',
			'title' => 'Bulgarian'
		),
		'ca' => array(
			'name' => 'catalan',
			'title' => 'Catalan'
		),
		'ceb' => array(
			'name' => 'cebuano',
			'title' => 'Cebuano'
		),
		'ny' => array(
			'name' => 'chichewa',
			'title' => 'Chichewa'
		),
		'zh-CN' => array(
			'name' => 'chinese-simplified',
			'title' => 'Chinese (Simplified)'
		),
		'zh-TW' => array(
			'name' => 'chinese-traditional',
			'title' => 'Chinese (Traditional)'
		),
		'co' => array(
			'name' => 'corsican',
			'title' => 'Corsican'
		),
		'hr' => array(
			'name' => 'croatian',
			'title' => 'Croatian'
		),
		'cs' => array(
			'name' => 'czech',
			'title' => 'Czech'
		),
		'da' => array(
			'name' => 'danish',
			'title' => 'Danish'
		),
		'nl' => array(
			'name' => 'dutch',
			'title' => 'Dutch'
		),
		'en' => array(
			'name' => 'english',
			'title' => 'English'
		),
		'eo' => array(
			'name' => 'esperanto',
			'title' => 'Esperanto'
		),
		'et' => array(
			'name' => 'estonian',
			'title' => 'Estonian'
		),
		'tl' => array(
			'name' => 'filipino',
			'title' => 'Filipino'
		),
		'fi' => array(
			'name' => 'finnish',
			'title' => 'Finnish'
		),
		'fr' => array(
			'name' => 'french',
			'title' => 'French'
		),
		'fy' => array(
			'name' => 'frisian',
			'title' => 'Frisian'
		),
		'gl' => array(
			'name' => 'galician',
			'title' => 'Galician'
		),
		'ka' => array(
			'name' => 'georgian',
			'title' => 'Georgian'
		),
		'de' => array(
			'name' => 'german',
			'title' => 'German'
		),
		'el' => array(
			'name' => 'greek',
			'title' => 'Greek'
		),
		'gu' => array(
			'name' => 'gujarati',
			'title' => 'Gujarati'
		),
		'ht' => array(
			'name' => 'haitian-creole',
			'title' => 'Haitian Creole'
		),
		'ha' => array(
			'name' => 'hausa',
			'title' => 'Hausa'
		),
		'haw' => array(
			'name' => 'hawaiian',
			'title' => 'Hawaiian'
		),
		'iw' => array(
			'name' => 'hebrew',
			'title' => 'Hebrew'
		),
		'hi' => array(
			'name' => 'hindi',
			'title' => 'Hindi'
		),
		'hmn' => array(
			'name' => 'hmong',
			'title' => 'Hmong'
		),
		'hu' => array(
			'name' => 'hungarian',
			'title' => 'Hungarian'
		),
		'is' => array(
			'name' => 'icelandic',
			'title' => 'Icelandic'
		),
		'ig' => array(
			'name' => 'igbo',
			'title' => 'Igbo'
		),
		'id' => array(
			'name' => 'indonesian',
			'title' => 'Indonesian'
		),
		'ga' => array(
			'name' => 'irish',
			'title' => 'Irish'
		),
		'it' => array(
			'name' => 'italian',
			'title' => 'Italian'
		),
		'ja' => array(
			'name' => 'japanese',
			'title' => 'Japanese'
		),
		'jw' => array(
			'name' => 'javanese',
			'title' => 'Javanese'
		),
		'kn' => array(
			'name' => 'kannada',
			'title' => 'Kannada'
		),
		'kk' => array(
			'name' => 'kazakh',
			'title' => 'Kazakh'
		),
		'km' => array(
			'name' => 'khmer',
			'title' => 'Khmer'
		),
		'ko' => array(
			'name' => 'korean',
			'title' => 'Korean'
		),
		'ku' => array(
			'name' => 'kurdish-Kurmanji',
			'title' => 'Kurdish (Kurmanji)'
		),
		'ky' => array(
			'name' => 'kyrgyz',
			'title' => 'Kyrgyz'
		),
		'lo' => array(
			'name' => 'lao',
			'title' => 'Lao'
		),
		'la' => array(
			'name' => 'latin',
			'title' => 'Latin'
		),
		'lv' => array(
			'name' => 'latvian',
			'title' => 'Latvian'
		),
		'lt' => array(
			'name' => 'lithuanian',
			'title' => 'Lithuanian'
		),
		'lb' => array(
			'name' => 'luxembourgish',
			'title' => 'Luxembourgish'
		),
		'mk' => array(
			'name' => 'macedonian',
			'title' => 'Macedonian'
		),
		'mg' => array(
			'name' => 'malagasy',
			'title' => 'Malagasy'
		),
		'ms' => array(
			'name' => 'malay',
			'title' => 'Malay'
		),
		'ml' => array(
			'name' => 'malayalam',
			'title' => 'Malayalam'
		),
		'mt' => array(
			'name' => 'maltese',
			'title' => 'Maltese'
		),
		'mi' => array(
			'name' => 'maori',
			'title' => 'Maori'
		),
		'mr' => array(
			'name' => 'marathi',
			'title' => 'Marathi'
		),
		'mn' => array(
			'name' => 'mongolian',
			'title' => 'Mongolian'
		),
		'my' => array(
			'name' => 'myanmar-burmese',
			'title' => 'Myanmar (Burmese)'
		),
		'ne' => array(
			'name' => 'nepali',
			'title' => 'Nepali'
		),
		'no' => array(
			'name' => 'norwegian',
			'title' => 'Norwegian'
		),
		'ps' => array(
			'name' => 'pashto',
			'title' => 'Pashto'
		),
		'fa' => array(
			'name' => 'persian',
			'title' => 'Persian'
		),
		'pl' => array(
			'name' => 'polish',
			'title' => 'Polish'
		),
		'pt' => array(
			'name' => 'portuguese',
			'title' => 'Portuguese'
		),
		'pa' => array(
			'name' => 'punjabi',
			'title' => 'Punjabi'
		),
		'ro' => array(
			'name' => 'romanian',
			'title' => 'Romanian'
		),
		'ru' => array(
			'name' => 'russian',
			'title' => 'Russian'
		),
		'sm' => array(
			'name' => 'samoan',
			'title' => 'Samoan'
		),
		'gd' => array(
			'name' => 'scots-gaelic',
			'title' => 'Scots Gaelic'
		),
		'sr' => array(
			'name' => 'serbian',
			'title' => 'Serbian'
		),
		'st' => array(
			'name' => 'sesotho',
			'title' => 'Sesotho'
		),
		'sn' => array(
			'name' => 'shona',
			'title' => 'Shona'
		),
		'sd' => array(
			'name' => 'sindhi',
			'title' => 'Sindhi'
		),
		'si' => array(
			'name' => 'sinhala',
			'title' => 'Sinhala'
		),
		'sk' => array(
			'name' => 'slovak',
			'title' => 'Slovak'
		),
		'sl' => array(
			'name' => 'slovenian',
			'title' => 'Slovenian'
		),
		'so' => array(
			'name' => 'somali',
			'title' => 'Somali'
		),
		'es' => array(
			'name' => 'spanish',
			'title' => 'Spanish'
		),
		'su' => array(
			'name' => 'sundanese',
			'title' => 'Sundanese'
		),
		'sw' => array(
			'name' => 'swahili',
			'title' => 'Swahili'
		),
		'sv' => array(
			'name' => 'swedish',
			'title' => 'Swedish'
		),
		'tg' => array(
			'name' => 'tajik',
			'title' => 'Tajik'
		),
		'ta' => array(
			'name' => 'tamil',
			'title' => 'Tamil'
		),
		'te' => array(
			'name' => 'telugu',
			'title' => 'Telugu'
		),
		'th' => array(
			'name' => 'thai',
			'title' => 'Thai'
		),
		'tr' => array(
			'name' => 'turkish',
			'title' => 'Turkish'
		),
		'uk' => array(
			'name' => 'ukrainian',
			'title' => 'Ukrainian'
		),
		'ur' => array(
			'name' => 'urdu',
			'title' => 'Urdu'
		),
		'uz' => array(
			'name' => 'uzbek',
			'title' => 'Uzbek'
		),
		'vi' => array(
			'name' => 'vietnamese',
			'title' => 'Vietnamese'
		),
		'cy' => array(
			'name' => 'welsh',
			'title' => 'Welsh'
		),
		'xh' => array(
			'name' => 'xhosa',
			'title' => 'Xhosa'
		),
		'yi' => array(
			'name' => 'yiddish',
			'title' => 'Yiddish'
		),
		'yo' => array(
			'name' => 'yoruba',
			'title' => 'Yoruba'
		),
		'zu' => array(
			'name' => 'zulu',
			'title' => 'Zulu'
		)
	);
}

function get_world_locales() {
	return array(
		'af-ZA',
		'am-ET',
		'ar-AE',
		'ar-BH',
		'ar-DZ',
		'ar-EG',
		'ar-IQ',
		'ar-JO',
		'ar-KW',
		'ar-LB',
		'ar-LY',
		'ar-MA',
		'arn-CL',
		'ar-OM',
		'ar-QA',
		'ar-SA',
		'ar-SY',
		'ar-TN',
		'ar-YE',
		'as-IN',
		'az-Cyrl-AZ',
		'az-Latn-AZ',
		'ba-RU',
		'be-BY',
		'bg-BG',
		'bn-BD',
		'bn-IN',
		'bo-CN',
		'br-FR',
		'bs-Cyrl-BA',
		'bs-Latn-BA',
		'ca-ES',
		'co-FR',
		'cs-CZ',
		'cy-GB',
		'da-DK',
		'de-AT',
		'de-CH',
		'de-DE',
		'de-LI',
		'de-LU',
		'dsb-DE',
		'dv-MV',
		'el-GR',
		'en-029',
		'en-AU',
		'en-BZ',
		'en-CA',
		'en-GB',
		'en-IE',
		'en-IN',
		'en-JM',
		'en-MY',
		'en-NZ',
		'en-PH',
		'en-SG',
		'en-TT',
		'en-US',
		'en-ZA',
		'en-ZW',
		'es-AR',
		'es-BO',
		'es-CL',
		'es-CO',
		'es-CR',
		'es-DO',
		'es-EC',
		'es-ES',
		'es-GT',
		'es-HN',
		'es-MX',
		'es-NI',
		'es-PA',
		'es-PE',
		'es-PR',
		'es-PY',
		'es-SV',
		'es-US',
		'es-UY',
		'es-VE',
		'et-EE',
		'eu-ES',
		'fa-IR',
		'fi-FI',
		'fil-PH',
		'fo-FO',
		'fr-BE',
		'fr-CA',
		'fr-CH',
		'fr-FR',
		'fr-LU',
		'fr-MC',
		'fy-NL',
		'ga-IE',
		'gd-GB',
		'gl-ES',
		'gsw-FR',
		'gu-IN',
		'ha-Latn-NG',
		'he-IL',
		'hi-IN',
		'hr-BA',
		'hr-HR',
		'hsb-DE',
		'hu-HU',
		'hy-AM',
		'id-ID',
		'ig-NG',
		'ii-CN',
		'is-IS',
		'it-CH',
		'it-IT',
		'iu-Cans-CA',
		'iu-Latn-CA',
		'ja-JP',
		'ka-GE',
		'kk-KZ',
		'kl-GL',
		'km-KH',
		'kn-IN',
		'kok-IN',
		'ko-KR',
		'ky-KG',
		'lb-LU',
		'lo-LA',
		'lt-LT',
		'lv-LV',
		'mi-NZ',
		'mk-MK',
		'ml-IN',
		'mn-MN',
		'mn-Mong-CN',
		'moh-CA',
		'mr-IN',
		'ms-BN',
		'ms-MY',
		'mt-MT',
		'nb-NO',
		'ne-NP',
		'nl-BE',
		'nl-NL',
		'nn-NO',
		'nso-ZA',
		'oc-FR',
		'or-IN',
		'pa-IN',
		'pl-PL',
		'prs-AF',
		'ps-AF',
		'pt-BR',
		'pt-PT',
		'qut-GT',
		'quz-BO',
		'quz-EC',
		'quz-PE',
		'rm-CH',
		'ro-RO',
		'ru-RU',
		'rw-RW',
		'sah-RU',
		'sa-IN',
		'se-FI',
		'se-NO',
		'se-SE',
		'si-LK',
		'sk-SK',
		'sl-SI',
		'sma-NO',
		'sma-SE',
		'smj-NO',
		'smj-SE',
		'smn-FI',
		'sms-FI',
		'sq-AL',
		'sr-Cyrl-BA',
		'sr-Cyrl-CS',
		'sr-Cyrl-ME',
		'sr-Cyrl-RS',
		'sr-Latn-BA',
		'sr-Latn-CS',
		'sr-Latn-ME',
		'sr-Latn-RS',
		'sv-FI',
		'sv-SE',
		'sw-KE',
		'syr-SY',
		'ta-IN',
		'te-IN',
		'tg-Cyrl-TJ',
		'th-TH',
		'tk-TM',
		'tn-ZA',
		'tr-TR',
		'tt-RU',
		'tzm-Latn-DZ',
		'ug-CN',
		'uk-UA',
		'ur-PK',
		'uz-Cyrl-UZ',
		'uz-Latn-UZ',
		'vi-VN',
		'wo-SN',
		'xh-ZA',
		'yo-NG',
		'zh-CN',
		'zh-HK',
		'zh-MO',
		'zh-SG',
		'zh-TW',
		'zu-ZA'
	);
}

function get_lang_name($id) {
	$id = isset($id) ? $id : get_lang_id();
	$languages = get_world_languages();
	return isset($languages[$id]) ? $languages[$id]['name'] : $id;
}

function get_lang_title($id) {
	$id = isset($id) ? $id : get_lang_id();
	$languages = get_world_languages();
	return isset($languages[$id]) ? $languages[$id]['title'] : $id;
}

function get_lang_id($name = null) {
	$name = isset($name) ? $name : app()->lang;
	$lang_id = $name;
	$languages = get_world_languages();
	foreach($languages as $id => $language) {
		if($language['name'] == $name) {
			$lang_id = $id;
			break;
		}
	}
	return $lang_id;
}

function get_lang_locales($lang_id = null, $encode = null) {
	$lang_id = isset($lang_id) ? $lang_id : get_lang_id();
	$locales = get_world_locales();
	$lang_locales = array();
	foreach($locales as $locale) {
		preg_match('/'.preg_quote($lang_id, '/').'\-.*/', $locale, $matches);
		if(isset($matches[0]) && $matches[0]) {
			$lang_locales[] = str_replace('-', '_', $matches[0]);
		}
	}
	if(isset($encode)) {
		array_walk($lang_locales, function(&$value) use ($encode) {
			$value .= '.'.$encode;
		});
	}
	return $lang_locales;
}

function get_country_flag($iso) {
    $flag_path = 'themes/default/images/flags/'.$iso.'.png';
    $country_flag = file_exists(path($flag_path)) ? $flag_path : false;
    return $country_flag;
}

function get_lang_flag($name = null) {
	$name = isset($name) ? $name : app()->lang;
	$lang_id = get_lang_id($name);

	$country_flags = array();
    $default_recommended_countries = array('us', 'de', 'it', 'jp', 'cn', 'sa', 'es', 'se');
    $recommended_countries = fire_hook('language.flags.recommends', $default_recommended_countries);

	$locales = get_lang_locales($lang_id);
	foreach($locales as $locale) {
		preg_match('/'.preg_quote($lang_id, '/').'_.*/', $locale, $matches);
		$array = explode('_', $matches[0]);
		$country_id = strtolower($array[(count($array) - 1)]);
		if(isset($matches[0]) && $matches[0]) {
			$lang_locales[] = str_replace('-', '_', $matches[0]);
			$flag_path = 'themes/default/images/flags/'.$country_id.'.png';
			if(file_exists(path($flag_path))) {
				$country_flags[$country_id] = $flag_path;
			}
		}
	}
	if($country_flags) {
		foreach($recommended_countries as $country_id) {
			if(isset($country_flags[$country_id])) {
				return $country_flags[$country_id];
			}
		}
		foreach($country_flags as $flag) {
			return $flag;
		}
	}
	return 'themes/default/images/flags/flag.png';
}

function date_separator() {
	return config('event-date-separator');
}

function time_separator() {
	return config('event-time-format');
}