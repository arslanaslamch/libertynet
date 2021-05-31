<?php

load_functions('birthday::birthday');
register_asset('birthday::css/birthday.css');
register_asset('birthday::js/birthday.js');

register_pager("birthdays", array('use' => 'birthday::birthday@home_pager', 'as' => 'birthdays','filter'=>'auth'));
register_pager("birthday/ajax",array('use'=>'birthday::birthday@ajax_pager', 'as'=>'birthdays-ajax','filter'=>'auth'));

//cronjob
register_pager("birthday/tasks",array('use'=>'birthday::birthday@tasks_pager', 'as'=>'birthdays-tasks'));


add_available_menu('birthday::birthdays', 'birthdays', 'ion-ios-rose');
register_hook("shortcut.menu.images",function($arr){
    $arr['ion-ios-rose'] = img("birthday::image/c.jpeg");
    return $arr;
});


register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "birthday") {
        $user = find_user($feed['to_user_id']);
        $name = get_user_name($user);
        $url = profile_url(null,$user);
        $str = lang("birthday::congratulated-this-user");
        echo $str;
    }
});

register_pager("admincp/birthdays/post-template", array('use' => "birthday::admincp@post_template_pager", 'filter' => 'admin-auth', 'as' => 'admincp-post-template'));
register_pager("admincp/birthdays/email-template", array('use' => "birthday::admincp@email_template_pager", 'filter' => 'admin-auth', 'as' => 'admincp-email-template'));
register_pager("admincp/birthday/reminder-template", array('use' => "birthday::admincp@reminder_template_pager", 'filter' => 'admin-auth', 'as' => 'admincp-birthday-reminder-template'));



register_hook("admin-started", function() {
    get_menu("admin-menu", "plugins")->addMenu(lang('birthday::birthday-manager'), '#', 'admin-birthdays');
    get_menu("admin-menu", "plugins")->findMenu('admin-birthdays')->addMenu(lang('birthday::admin-post-template'), url_to_pager("admincp-post-template"), "admin-post-template");
    get_menu("admin-menu", "plugins")->findMenu('admin-birthdays')->addMenu(lang('birthday::email-template'), url_to_pager("admincp-email-template"), "email-template");
    get_menu("admin-menu", "plugins")->findMenu('admin-birthdays')->addMenu(lang('birthday::reminder-email-template'), url_to_pager("admincp-birthday-reminder-template"), "reminder-template");

});

register_hook('verify.badge',function($e,$u){
    //$r = flagRunCode($u);
    if(isset($u['first_name']) and isset($u['id'])){
        if(Birthday::hasBirthdayToday($u['id'])){
            $r = '<img title="'.lang("birthday::have-birthday-today").'" class="icon-b-text" src="'.img("birthday::image/c.jpeg").'" alt="icon" />';
            return $r.' '.$e;
        }
    }
    //print_r($u);die();
    return $e;
});
