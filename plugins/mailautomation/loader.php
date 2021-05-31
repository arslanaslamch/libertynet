<?php

load_functions('mailautomation::mailautomation');
register_asset('mailautomation::css/mailautomation.css');
register_hook('system.started', function($app) {
    if($app->themeType == 'backend') {
        register_asset('mailautomation::css/daterangepicker.css');
        register_asset('mailautomation::js/moment.js');
        register_asset('mailautomation::js/daterangepicker.js');
        register_asset('mailautomation::js/mailautomation.js');
    }
});


register_pager("mail-automations/service/cron/highlights", array('use' => "mailautomation::mailautomation@service_highlights_pager", 'as' => 'mail-automation-service'));
register_pager("mail-automations/service/cron/auto", array('use' => "mailautomation::mailautomation@service_auto_pager", 'as' => 'mail-automation-service-auto'));
register_pager("mail-automations/track", array('use' => "mailautomation::mailautomation@track_pager", 'as' => 'automation-track'));
register_pager("admincp/mail-automations", array('use' => "mailautomation::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations'));
register_pager("admincp/mailautomations/add", array('use' => "mailautomation::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations-add'));
register_pager("admincp/mailautomations/manage", array('use' => "mailautomation::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations-manage'));
register_pager("admincp/mailautomations/cron-settings", array('use' => "mailautomation::admincp@cron_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations-cronjob'));
register_pager("admincp/mailautomations/stats", array('use' => "mailautomation::admincp@stats_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations-stats'));
register_pager("admincp/mailautomation/ajax", array('use' => "mailautomation::admincp@ajax_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations-ajax'));
register_pager("admincp/mailautomation/logs", array('use' => "mailautomation::admincp@mail_logs_pager", 'filter' => 'admin-auth', 'as' => 'admincp-mail-automations-logs'));

register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang('mailautomation::mail-automations'), '#', 'admin-mailautomation');
    get_menu("admin-menu", "plugins")->findMenu('admin-mailautomation')->addMenu(lang('mailautomation::all-automations'), url_to_pager("admincp-mail-automations"), "manage");
    get_menu("admin-menu", "plugins")->findMenu('admin-mailautomation')->addMenu(lang('mailautomation::add-new-automation'), url_to_pager("admincp-mail-automations-add"), "add");
    get_menu("admin-menu", "plugins")->findMenu('admin-mailautomation')->addMenu(lang('mailautomation::recently-sent'), url_to_pager("admincp-mail-automations-logs"), "logs");
    get_menu("admin-menu", "plugins")->findMenu('admin-mailautomation')->addMenu(lang('mailautomation::statistics'), url_to_pager("admincp-mail-automations-stats"), "stats");
});

register_hook("after-render-js",function($html){
    $html .="<script>
   function render_mail_js(){
    $('#mastartDate').daterangepicker({
      singleDatePicker: true,
      startDate: moment().subtract(6, 'days')
    });

    $('#maendDate').daterangepicker({
      singleDatePicker: true,
      startDate: moment()
    });
   }
   $(function(){
       render_mail_js();
   });
   try{
       
   }catch (e) {
     addPageHook('render_mail_js')
   }
   
   </script>";
    return $html;
});