<?php

load_functions('supportsystem::supportsystem');
register_asset('supportsystem::css/supportsystem.css');
register_hook('system.started', function($app) {
    if($app->themeType == 'frontend') {
        register_asset('supportsystem::js/supportsystem.js');
    }else{
        register_asset('supportsystem::js/admin.js');
    }
});
register_hook("role.permissions", function($roles) {
    $roles[] = array(
        'title' => lang('supportsystem::support-ticket-permissions'),
        'description' => '',
        'roles' => array(
            'can-add-support-ticket' => array('title' => lang('supportsystem::can-add-support-ticket'), 'value' => 1),
        )
    );
    return $roles;
});

register_pager('support/help-center', array(
    'use' => 'supportsystem::supportsystem@dashboard_pager',
    'as' => 'supportsystem-dashboard-page',
    'filter'=>'auth'
));

register_pager('support/my-tickets', array(
    'use' => 'supportsystem::supportsystem@my_tickets_pager',
    'as' => 'supportsystem-my-ticket-page',
    'filter'=>'auth'
));

register_pager('support/moderate-tickets', array(
    'use' => 'supportsystem::supportsystem@ticket_moderate_pager',
    'as' => 'supportsystem-moderate-ticket-page',
    'filter'=>'auth'
));

register_pager('support/create-ticket', array(
    'use' => 'supportsystem::supportsystem@create_pager',
    'as' => 'supportsystem-create-page',
    'filter'=>'auth'
));
register_pager("support/ticket/reply", array('use' => 'supportsystem::supportsystem@reply_pager', 'as' => 'supportsystem-reply-page','filter'=>'auth'));
register_pager("support/ticket/download", array('use' => 'supportsystem::supportsystem@download_pager', 'as' => 'supportsystem-download-page','filter'=>'auth'));
register_pager("support/ticket/search", array('use' => 'supportsystem::supportsystem@search_pager', 'as' => 'supportsystem-search-page'));
register_pager("support/ticket/ajax", array('use' => 'supportsystem::supportsystem@ajax_pager', 'as' => 'supportsystem-ajax-page'));

register_pager("support/ticket/{slugs}", array('use' => 'supportsystem::supportsystem@view_pager', 'as' => 'supportsystem-view-page','filter'=>'auth'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));
register_pager("support/article/{slugs}", array('use' => 'supportsystem::supportsystem@view_article_pager', 'as' => 'supportsystem-article-page','filter'=>'auth'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));
register_pager("support/categories/{slugs}", array('use' => 'supportsystem::supportsystem@categories_pager', 'as' => 'supportsystem-faq-category-page','filter'=>'auth'))->where(array('slugs' => '[a-zA-Z0-9\-\_\.]+'));

//admincp
register_pager("admincp/supportsystem/dashboard", array('use' => "supportsystem::admincp@dashboard_pager", 'filter' => 'admin-auth', 'as' => 'admincp-support-system-dashboard'));
register_pager("admincp/supportsystem/tickets", array('use' => "supportsystem::admincp@tickets_pager", 'filter' => 'admin-auth', 'as' => 'admincp-support-system-tickets'));
register_pager("admincp/supportsystem/moderators", array('use' => "supportsystem::admincp@moderators_pager", 'filter' => 'admin-auth', 'as' => 'admincp-support-system-moderator'));
register_pager("admincp/supportsystem/articles", array('use' => "supportsystem::admincp@articles_pager", 'filter' => 'admin-auth', 'as' => 'admincp-support-system-articles'));
register_pager("admincp/supportsystem/departments", array('use' => "supportsystem::admincp@departements_pager", 'filter' => 'admin-auth', 'as' => 'admincp-list-tickets-departments'));
register_pager("admincp/supportsystem/departments/add", array('use' => "supportsystem::admincp@departments_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-add-ss-departments'));
register_pager("admincp/supportsystem/departments/manage", array('use' => "supportsystem::admincp@manage_departments_pager", 'filter' => 'admin-auth', 'as' => 'admincp-manage-tickets-departments'));


register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang('supportsystem::supportsystem'), '#', 'admin-supportsystem');
    get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->addMenu(lang('supportsystem::dashboard'), url_to_pager("admincp-support-system-dashboard"), "tickets-dashboard");
    get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->addMenu(lang('supportsystem::manage-tickets'), url_to_pager("admincp-support-system-tickets"), "manage-tickets");
    get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->addMenu(lang('supportsystem::ticket-moderators'), url_to_pager("admincp-support-system-moderator"), "manage-tickets-moderators");
    get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->addMenu(lang('supportsystem::manage-tickets-departments'), url_to_pager("admincp-list-tickets-departments").'?type=ticket', "manage-tickets-departments");
    get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->addMenu(lang('supportsystem::help-center-articles'), url_to_pager("admincp-support-system-articles"), "help-center-articles");
    get_menu("admin-menu", "plugins")->findMenu('admin-supportsystem')->addMenu(lang('supportsystem::help-center-categories'), url_to_pager("admincp-list-tickets-departments").'?type=faq', "help-center-categories");
});

add_available_menu('supportsystem::need-help', url_to_pager("supportsystem-dashboard-page"), 'ion-help');

register_hook("display.notification", function($notification) {
    if($notification['type'] == 'support.system.ticket.new') {
        return view("supportsystem::notifications/new", array('notification' => $notification));
    } elseif($notification['type'] == 'support.system.owner.replied') {
        return view("blog::notifications/owner-replied", array('notification' => $notification));
    } elseif($notification['type'] == 'support.system.moderator.replied') {
        return view("blog::notifications/moderator-replied", array('notification' => $notification));
    }
});