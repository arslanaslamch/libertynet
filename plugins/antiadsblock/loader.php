<?php
load_functions("antiadsblock::antiadsblock");
register_asset("antiadsblock::css/antiadsblock.css");
register_asset("antiadsblock::js/antiadsblock.js");
register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		register_asset("help::css/help.css");
		register_asset("help::js/help.js");
	}
});
register_hook('anti.adsblock.detector', function () {
    echo view('antiadsblock::modal', array());
});