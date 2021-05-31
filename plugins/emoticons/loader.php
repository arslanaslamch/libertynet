<?php
load_functions("emoticons::emoticon");
register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		//register assets
		register_asset("emoticons::css/emoticons.css");
		register_asset("emoticons::js/emoticons.js");
	}
});

register_hook("admin-started", function() {

	get_menu("admin-menu", "cms")->addMenu(lang("emoticons::emoticons"), "#", "emoticons");
	get_menu("admin-menu", "cms")->findMenu('emoticons')->addMenu(lang("manage"), url_to_pager("admincp-emoticons"), "manage");
	get_menu("admin-menu", "cms")->findMenu('emoticons')->addMenu(lang("emoticons::add-emoticons"), url_to_pager("admincp-emoticons-add"), "add");

});

register_pager("emoticon/search", array("use" => "emoticons::emoticon@search_pager", 'filter' => 'auth'));

/**Registering admincp pagers***/
register_pager("admincp/emoticons", array('use' => "emoticons::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-emoticons'));
register_pager("admincp/emoticons/add", array('use' => "emoticons::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-emoticons-add'));
register_pager("admincp/emoticons/manage", array('use' => "emoticons::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-emoticons-manage'));

register_pager("emoticon/load", array('use' => "emoticons::emoticon@emoticon_load_pager", 'filter' => 'auth'));

register_hook('filter.content', function($content) {
    $emoticons = array();
    if ($content) {
         $emoticons_translation = get_emoticon_translation();
        if(str_replace(array_keys($emoticons_translation), '', $content) != strlen($content)) {
            $content = strtr($content, $emoticons_translation);
        }
    }
    return $content;
	$emoticons = get_emoticons(1);
	foreach($emoticons as $e => $d) {
		$img = "<img class='emoticon-img' src='".url_img($d['path'])."' ";
		if($d['width']) $img .= " width='".$d['width']."'";
		if($d['height']) $img .= " height='".$d['height']."'";
		$img .= "/>";
		$content = preg_replace('/( |[^\x20-\x7E]|^)'.preg_quote($e, '/').'( |[^\x20-\x7E]|$)/', "$1".$img."$2", $content);
	}
	$emoticons = get_emoticons(2);
	foreach($emoticons as $e => $d) {
		$img = "<img class='emoticon-img' src='".url_img($d['path'])."' ";
		if($d['width']) $img .= " width='".$d['width']."'";
		if($d['height']) $img .= " height='".$d['height']."'";
		$img .= "/>";
		$content = preg_replace('/( |[^\x20-\x7E]|^)'.preg_quote($e, '/').'( |[^\x20-\x7E]|$)/', "$1".$img."$2", $content);
	}
	return $content;
});

