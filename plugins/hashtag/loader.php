<?php
load_functions("hashtag::hashtag");
register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		//register assets
		register_asset("hashtag::css/hashtag.css");
		//register_asset("mention::js/mention.js");
	}
});

register_hook('search-dropdown-start', function($content, $term) {
	$hashtags = search_hashtags($term);
	if($hashtags) {
		$content .= view('hashtag::search-dropdown', array('hashtags' => $hashtags));
	}
	return $content;
});

register_hook('feed.added', function($id, $val) {
	load_functions("hashtag::hashtag");
	if(isset($val['content'])) {
		$content = $val['content'];
		$hashtags = hashtag_parse($content);
		if($hashtags) {
			foreach($hashtags as $hashtag) {
				add_hashtag($hashtag);
			}
		}
	}
});


register_hook('filter.content', function($content) {
	$hashtags = hashtag_parse($content);
	if($hashtags) {
		usort($hashtags, function($a, $b) {
			return strlen($b) - strlen($a);
		});
		foreach($hashtags as $hashtag) {
			if(!in_array($hashtag, array('#039'))) {
				$color = config('hashtag-color', '#3498db');
				$link = " <a ajax='true' style='color:{$color} !important' href='".url_to_pager('hashtag')."?t=".str_replace('#', '', $hashtag)."'>".$hashtag."</a> ";
				$content = preg_replace('/(^|[^A-Za-z0-9-_])'.$hashtag.'([^A-Za-z0-9-_]|$)/mi', '$1'.$link.'$2', $content);
			}
		}
	}

	return $content;
});

register_hook("feeds.query", function($type, $typeId) {
	if($type == 'hashtag') {
		$sqlFields = get_feed_fields();
		$sql = "SELECT {$sqlFields} FROM `feeds` ";
		if(empty($typeId)) {
			$top = get_top_hashtags(6);
			if(!$top) return false;

			$sql .= "WHERE (";
			$sl = "";
			foreach($top as $hash) {
				$hashtag = $hash['hashtag'];
				$sl .= ($sl) ? " OR feed_content LIKE '%{$hashtag}%' " : " feed_content LIKE '%{$hashtag}%' ";
			}
			$sql .= $sl.")";
		} else {
			$typeId = (preg_match('/#/', $typeId)) ? $typeId : '#'.$typeId;
			$sql .= " WHERE `feed_content` LIKE '%{$typeId}%' ";

		}
		//$sql .= " AND (privacy='1' or privacy='2')";

		return $sql;
	}
	return '';
});

add_menu("dashboard-menu", array("icon" => "<i class='ion-pound'></i>", "id" => "hashtag", "title" => lang("discover"), "link" => url("hashtag")));

register_pager("hashtag", array('use' => 'hashtag::hashtag@hashtags_pager', 'as' => 'hashtag'));

add_menu_location('hashtag-menu', 'hashtag::hashtag-menu');
add_available_menu('hashtag::hashtag', 'hashtag', 'ion-pound');
