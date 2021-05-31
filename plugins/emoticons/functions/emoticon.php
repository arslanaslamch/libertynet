<?php
function add_emoticon($val) {
	$expected = array(
		'title' => '',
		'symbol' => '',
		'width' => '',
		'height' => '',
		'icon' => '',
		'category' => 1
	);

	/**
	 * @var $title
	 * @var $symbol
	 * @var $width
	 * @var $height
	 * @var $icon
	 * @var $category
	 */
	extract(array_merge($expected, $val));
	$emoticon_exists = emoticon_exists($symbol);
	if($emoticon_exists) {
		$emoticon_id = $emoticon_exists;
		save_emoticon($val, $emoticon_id);
		return true;
	}
	$query = db()->query("INSERT INTO emoticons (name,category,symbol,path,width,height)VALUES(
    '{$title}','{$category}','{$symbol}','{$icon}','{$width}','{$height}'
    )");
	forget_cache("emoticons-0");
	forget_cache("emoticons-1");
	forget_cache("emoticons-2");
	forget_cache("emoticons-0-translation");
	forget_cache("emoticons-1-translation");
	forget_cache("emoticons-2-translation");
	forget_cache("emoticons-0-translation-fixed-dimension");
	forget_cache("emoticons-1-translation-fixed-dimension");
	forget_cache("emoticons-2-translation-fixed-dimension");
    fire_hook("emoticons.added", null, array($val));
	return true;
}

function update_emoticon_icon($symbol, $path) {
	db()->query("UPDATE emoticons SET path='{$path}' WHERE symbol='{$symbol}'");
	return true;
}

function save_emoticon($val, $emoticon) {
	$expected = array(
		'title' => '',
		'symbol' => '',
		'width' => '',
		'height' => '',
		'icon' => '',
		'category' => 1
	);

	/**
	 * @var $title
	 * @var $symbol
	 * @var $width
	 * @var $height
	 * @var $icon
	 * @var $category
	 */
	extract(array_merge($expected, $val));
	//if (emoticon_exists($symbol)) return false;
	$sql = "name='{$title}',symbol='{$symbol}',width='{$width}',height='{$height}', category='{$category}'";
	if($icon) $sql .= ",path='{$icon}'";
	db()->query("UPDATE emoticons SET {$sql} WHERE id='{$emoticon}'");
	forget_cache("emoticons-0");
	forget_cache("emoticons-1");
	forget_cache("emoticons-2");
	forget_cache("emoticons-0-translation");
	forget_cache("emoticons-1-translation");
	forget_cache("emoticons-2-translation");
	forget_cache("emoticons-0-translation-fixed-dimension");
	forget_cache("emoticons-1-translation-fixed-dimension");
	forget_cache("emoticons-2-translation-fixed-dimension");
	fire_hook("emoticons.edited", null, array($emoticon, $val));
	return true;
}

function emoticon_exists($symbol) {
	$db = db();
	$query = $db->query("SELECT id FROM emoticons WHERE symbol='{$symbol}'");
	if($query and $query->num_rows > 0) {
		$row = $query->fetch_row();
		$id = $row[0];
		return $id;
	}
	return false;
}

function list_emoticons($type) {
	$type = ($type == 'emoticons') ? 1 : 2;
	$query = db()->query("SELECT * FROM emoticons WHERE category='{$type}'");
	return fetch_all($query);
}

function get_emoticon($id) {
	$query = db()->query("SELECT * FROM emoticons WHERE id='{$id}'");
	return $query->fetch_assoc();
}

function delete_emoticon($id) {
	forget_cache("emoticons-0");
	forget_cache("emoticons-1");
	forget_cache("emoticons-2");
	forget_cache("emoticons-0-translation");
	forget_cache("emoticons-1-translation");
	forget_cache("emoticons-2-translation");
	forget_cache("emoticons-0-translation-fixed-dimension");
	forget_cache("emoticons-1-translation-fixed-dimension");
	forget_cache("emoticons-2-translation-fixed-dimension");
	return db()->query("DELETE FROM emoticons WHERE id='{$id}'");
}

function get_emoticon_translation($type = 0, $fixed_dimension = false) {
    $translation = array();
	$cache_name = 'emoticons-'.$type.'-translation'.($fixed_dimension ? '-fixed-dimension' : '');
	if(cache_exists($cache_name)) {
		$translation = get_cache($cache_name);
	} else {
        $emoticons = get_emoticons($type);
        foreach($emoticons as $id => $emoticon) {
            if($fixed_dimension) {
                $size = $emoticon['category'] == 1 ? 30 : 80;
                $img = '<img class="emoticon-img" src="'.url_img($emoticon['path']).'" width="'.$size.'" height="'.$size.'" />';
            } else {
                $img = '<img class="emoticon-img" src="'.url_img($emoticon['path']).'"';
                $img .= $emoticon['width'] ? ' width="'.$emoticon['width'].'"' : '';
                $img .= $emoticon['height'] ? ' height="'.$emoticon['height'].'"' : '';
                $img .= ' />';
            }
            $translation[$id] = $img;
    		set_cacheForever($cache_name, $translation);
        }
    }
    return $translation;
}

function get_emoticons($type = 0) {
	$cacheName = "emoticons-".$type;
	if(cache_exists($cacheName)) {
		return get_cache($cacheName);
	} else {
	    $sql = "SELECT * FROM emoticons";
	    if($type) {
	         $sql .= " WHERE category = ".$type;
	    }
		$query = db()->query($sql);
		$list = array();
		while($fetch = $query->fetch_assoc()) {
			$list[$fetch['symbol']] = $fetch;
		}

		set_cacheForever($cacheName, $list);
		return $list;
	}
}

function find_emoticons($term) {
	$query = db()->query("SELECT * FROM emoticons WHERE name LIKE '%{$term}%' OR symbol='{$term}'");
	$list = array();
	while($fetch = $query->fetch_assoc()) {
		$list[$fetch['symbol']] = $fetch;
	}
	return $list;
}