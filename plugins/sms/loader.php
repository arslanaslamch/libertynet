<?php
register_asset('sms::css/sms.css');
register_asset('sms::css/intlTelInput.css');
register_asset('sms::js/utils.js');
register_asset('sms::js/intlTelInput.js');
register_asset('sms::js/sms.js');

load_functions('sms::sms');
if(config('show-sms-verification-after-signup',true)){
    register_hook("signup.completed",function($userid, $user){
         //print_r($user);die();
        $user = find_user($userid);
        if(isset($user['id'])){
            $user_id = $user['id'];
        }else{
            $user_id = $user[0];
        }
        //$user = find_user($user['id']);

        if(config('Allow-new-users-to-skip-process',1) == 1){
            //print_r($user); die();
            // echo $user_id; die();
            activate_user($user_id);
            $u = find_user($user_id);
            login_with_user($u,true);
        };
        $_SESSION['the_user'] = $user;
        session_put("current_user_id", $user_id);
        //session_put("sv_loggin_password", $user['password']);
        if(isset($_SESSION['from_sign_in'])){
            unset($_SESSION['from_sign_in']);
        }
        //return redirect('sms-verification?userid='.$user);

        $url = url_to_pager('sms-verify');
        //echo $url;die();
        return redirect($url);
    });
}


register_hook("login.query",function($q,$username){
    if(!input('webview',false)){
        if(config('allow-users-to-login-with-mobile-number',1)){
            //check if user name is a number
            $number = $username;
            if(is_numeric($username)){
                $number = str_replace('+','',$username);
                $number = substr($number,-10);

            }
            //$q = db()->query("SELECT `id`,`password`,bannned,activated,username FROM users u LEFT JOIN sms_verification s ON u.id=s.user_id WHERE `username`='{$username}' OR `email_address`='{$username}' OR phone_num='{$username}'");
            $sql = "SELECT `id`, `password`, bannned, activated, username, ip_address FROM users u LEFT JOIN sms_verification s ON u.id=s.user_id WHERE `username`='{$username}' OR `email_address`='{$username}' OR RIGHT(phone_num, 10)='{$number}'";
            $q = db()->query($sql);
        }
    }
    return $q;
});


register_pager('sms/go-home',array(
    'as'=>'sms-goto-home',
    'use'=>'sms::sms@goto_home_pager'
));

register_pager('sms/verification',array(
    'as'=>'sms-verify',
    'use'=>'sms::sms@sms_index_pager'
));

/*register_pager('sms-verification/delete',array(
    'as'=>'mumu',
    'use'=>'sms::sms@delete_mf_pager'
));*/

register_pager('signup/phone',array(
    'as'=>'sms-get_phone_number',
    'use'=>'sms::sms@sms_getph_pager'
));
register_pager('testing/phone',array(
    'as'=>'phone-t',
    'use'=>'sms::sms@test_phone_number_pager'
));
register_pager('sms/verify',array(
    'as'=>'sms-verify_code',
    'use'=>'sms::sms@sms_verify_pager'
));

register_pager('signup/resend',array(
    'as'=>'sms-verify_resend-code',
    'use'=>'sms::sms@sms_resend_pager'
));

/*register_pager('signup/activation',array(
    'as'=>'signup-activate',
    'use'=>'sms::sms@sms_activation_pager'
));*/

register_pager('sms/admin_activate',array(
    'as'=>'sms-activate',
    'use'=>'sms::sms@sms_admin_pager'
));

register_pager('admincp/sms-verification',array(
    'as'=>'sms-lists',
    'filter'=>'admin-auth',
    'use'=>'sms::sms@sms_admin_list_pager'
));

register_pager('admincp/sms-verification/users',array(
    'as'=>'sms-all-users',
    'filter'=>'admin-auth',
    'use'=>'sms::admincp@all_users_pager'
));

register_pager('admincp/send/single',array(
    'as'=>'sms-sms-single',
    'filter'=>'admin-auth',
    'use'=>'sms::admincp@send_single_pager'
));

register_pager('admincp/sms-to-members',array(
    'as'=>'blast-sms',
    'filter'=>'admin-auth',
    'use'=>'sms::sms@sms_blast_pager'
));

register_pager('forgot-password',array(
    'as'=>'forgot-password',
    'use'=>'sms::sms@sms_forgot_password'
));

register_pager('forgot-password/email',array(
    'as'=>'forgot-password_email',
    'use'=>'sms::sms@sms_forgot_password_email'
));

register_pager('forgot-password/sms',array(
    'as'=>'forgot-password_sms',
    'use'=>'sms::sms@sms_forgot_password_sms'
));
register_pager('signup/skip_sms',array(
    'as'=>'skip-sms-verification',
    'use'=>'sms::sms@sms_skip_process',
));

register_pager('sms/invite_friends',array(
    'as'=>'sms-invite-friends',
    'use'=>'sms::sms@invite_friends_pager',
));
register_hook('admin-started',function (){

   get_menu('admin-menu','plugins')->addMenu(lang('sms::sms'),'sms-manager');
    get_menu('admin-menu','plugins')->findMenu('sms-manager')->addMenu(lang('sms::message-members'),url_to_pager('blast-sms'),'sms-blast');
    get_menu('admin-menu','plugins')->findMenu('sms-manager')->addMenu(lang('sms::sms-verification'),url_to_pager('sms-lists'),'sms-lists');
    get_menu('admin-menu','plugins')->findMenu('sms-manager')->addMenu(lang('sms::all-users'),url_to_pager('sms-all-users'),'sms-all-users');
});

register_hook('privacy-settings',function(){
   echo view('sms::update-phone-number');
});

register_hook('account.general.form.buttons.extend',function(){
   echo view('sms::update-general-phn');
});

register_hook('user.delete', function($userid) {
    db()->query("DELETE FROM `sms_verification` WHERE user_id='{$userid}'");
});

//twofacto after signin
if(config('enable-sign-in-two-facto',true)){
    register_hook("more.account.menu",function(){
       $html = '<li>
				<a ajax="true" href="'.url('account').'?action=two-facto">'.lang('sms::two-authentication').'</a>
			</li>';
       echo $html;
    });
}

register_hook("account.settings",function($action){
    if($action == 'two-facto'){
        app()->setTitle(lang("sms::two-facto-authentication-settings"));
        $val = input('val');
        if($val){
            save_privacy_settings($val);
            $status = 1;
            $message = lang('sms::two-factor-settings-saved');
            $redirect_url = '';
            $result = array(
                'status' => (int) $status,
                'message' => (string) $message,
                'redirect_url' => (string) $redirect_url,
            );
            $response = json_encode($result);
            return $response;
        }
        return view("sms::two-facto");
    }
});

register_pager('sms/two-factor',array(
    'as'=>'sms-two-factor',
    'use'=>'sms::sms@two_factor_pager',
));

register_pager('login/tfa',array(
    'as'=>'sms-two-auth',
    'use'=>'sms::sms@two_factor_auth_pager',
));
register_pager('login/tfa/backup',array(
    'as'=>'sms-two-auth-backup',
    'use'=>'sms::sms@two_factor_auth_backup_pager',
));

register_hook("user.welcome.segment.allowed",function($arr){
    $arr[] ='sms';
    $arr[] ='signup';
   return $arr;
});

/*register_hook('more.login.filter',function($username,$password){
    if(is_array($username)){
        $username = $username['username'];
    }else{
        $username = $username;
    }
   $user = find_user($username);
   if($user){
       if(get_privacy('two_facto_auth', 0,$user) == 1){
           //this user enable tfa
           //send code to his phone number
           $phn = getMyPhoneNumber();
           $msg = random_text('alnum',6);
           send_sms($msg,$phn);
           // and redirect now
           session_put('tfa_code',$msg);
           session_put('tfa_uid',$user['id']);

           //log the user out
           logout_user();
           return redirect(url_to_pager('sms-two-auth'));
       }
   }
});*/

if(!config('Allow-new-users-to-skip-process')){
    register_hook("collect.get.started",function($data){
        if(!getMyPhoneNumber()){
            $user = get_user();
            $_SESSION['the_user'] = $user;
            session_put("current_user_id", $user['id']);
            //session_put("sv_loggin_password", $user['password']);
            if(isset($_SESSION['from_sign_in'])){
                unset($_SESSION['from_sign_in']);
            }
            //return redirect('sms-verification?userid='.$user);

            $url = url_to_pager('sms-verify');
            //echo $url;die();
            return redirect($url);
        }
        return true;
    });
}



