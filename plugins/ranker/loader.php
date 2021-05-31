<?php
load_functions("ranker::ranker");
register_asset("ranker::css/ranker.css");


register_pager("admincp/members/rankings", array('use' => "ranker::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-rankings'));
register_pager("admincp/ranks", array('use' => "ranker::admincp@ranks_pager", 'filter' => 'admin-auth', 'as' => 'admincp-ranks'));
register_pager("admincp/ranges", array('use' => "ranker::admincp@ranges_pager", 'filter' => 'admin-auth', 'as' => 'admincp-ranges'));
register_pager("admincp/ranks/range", array('use' => "ranker::admincp@ranks_range_pager", 'filter' => 'admin-auth', 'as' => 'admincp-rank-range'));
register_pager("admincp/rank/add", array('use' => "ranker::admincp@add_rank_pager", 'filter' => 'admin-auth', 'as' => 'admincp-add-rank'));
register_pager("admincp/rank/point/manage", array('use' => "ranker::admincp@manage_rank_point", 'filter' => 'admin-auth', 'as' => 'admincp-rank-manage-point'));
//register_pager("admincp/ranker/categories", array('use' => "ranker::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-rank-categories'));
register_pager("admincp/ranker/categories/add", array('use' => "ranker::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-rank-categories-add'));
register_pager("admincp/ranker/category/manage", array('use' => "ranker::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-rank-manage-category'));
register_pager("admincp/ranker/range/manage", array('use' => "ranker::admincp@manage_range_pager", 'filter' => 'admin-auth', 'as' => 'admincp-range-manage'));


register_hook('system.started', function($app) {
	if($app->themeType == 'frontend' or $app->themeType == 'mobile') {
		register_asset("help::css/help.css");
		register_asset("help::js/help.js");
	}
});

register_hook("admin-started", function() {

	add_menu("admin-menu", array('icon' => 'ion-document-text', "id" => "admin-rankings", "title" => lang('ranker::manage-rankings'), "link" => '#'));
	get_menu("admin-menu", "plugins")->addMenu(lang('ranker::rankings-manager'), '#', 'admin-rankings');
	get_menu("admin-menu", "plugins")->findMenu('admin-rankings')->addMenu(lang('ranker::members'), url_to_pager("admincp-rankings"), "manage");
	get_menu("admin-menu", "plugins")->findMenu('admin-rankings')->addMenu(lang('ranker::ranks'), url_to_pager("admincp-ranks"), "ranks");
	get_menu("admin-menu", "plugins")->findMenu('admin-rankings')->addMenu(lang('ranker::set-ranges-category'), url_to_pager("admincp-ranges"), "range");
	

});

register_hook('feed-title', function($feed) {
	
	echo "<p style='color: grey;font-size: 11px'>".get_rank($feed['user_id'])."</p> ";
});