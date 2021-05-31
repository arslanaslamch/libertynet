<?php
function core_upgrade_database()
{
    $db = db();

    try {
        $db->query("ALTER TABLE `languages` ADD `dir` VARCHAR(50) NOT NULL DEFAULT 'ltr' AFTER `active` ;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `verification_requests` CHANGE `ignored` `approved` TINYINT(1) NOT NULL DEFAULT '0';");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `feeds` ADD `feeling_data` TEXT NOT NULL AFTER `type_data`;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `feeds` ADD `is_poll` INT NOT NULL DEFAULT '0' AFTER `feeling_data`;");
    } catch (Exception $e) {
    }
	try {
		$db->query("ALTER TABLE `feeds` ADD `custom_friends` TEXT NOT NULL AFTER `tags`;");
	} catch(Exception $e) {}

    $db->query("CREATE TABLE IF NOT EXISTS `mailing_list_subscriptions` (
	  `id` INT(1) NOT NULL AUTO_INCREMENT,
	  `user_id` INT(11) NOT NULL,
	  `hash` VARCHAR(255) NOT NULL,
	  `status` tinyINT(1) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    $db->query("CREATE TABLE IF NOT EXISTS `poll_answers` (
        `answer_id` INT(11) NOT NULL AUTO_INCREMENT,
        `poll_id` INT(11) NOT NULL,
        `answer_text` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
        `voters` INT(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`answer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

    $db->query("CREATE TABLE IF NOT EXISTS `poll_results` (
      `user_id` INT(11) NOT NULL,
      `poll_id` INT(11) NOT NULL,
      `answer_id` INT(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    try {
        $db->query("ALTER TABLE  `feeds` ADD  `poll_voters` INT NOT NULL DEFAULT  '0' AFTER  `is_poll` ;");
    } catch (Exception $e) {
    }

    dump_upgrade_email_templates();

    try {
        $db->query("ALTER TABLE  `static_pages` ADD  `description` TEXT NOT NULL AFTER  `tags`, ADD  `keywords` TEXT NOT NULL AFTER  `description` , ADD  `page_type` VARCHAR(255) NOT NULL DEFAULT  'auto' AFTER  `keywords` , ADD  `column_type` VARCHAR(255) NOT NULL AFTER  `page_type` ;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE  `blocks` ADD  `block_location` VARCHAR(50) NOT NULL AFTER  `block_view` ;");
    } catch (Exception $e) {
    }

    $db->query("CREATE TABLE IF NOT EXISTS `menus` (
      `id` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
      `menu_location` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
      `title` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
      `link` text COLLATE utf8_unicode_ci NOT NULL,
      `open_new_tab` INT(11) NOT NULL DEFAULT '0',
      `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'manual',
      `ajaxify` INT(11) NOT NULL DEFAULT '1',
      `icon` text COLLATE utf8_unicode_ci NOT NULL,
      `menu_order` INT(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    try {
        $db->query("ALTER TABLE `users` ADD `featured` TINYINT(1) UNSIGNED NOT NULL ;");
    } catch (Exception $e) {
    }
    try {
        $db->query("ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    } catch (Exception $e) {
    }
    try {
        $db->query("ALTER TABLE users MODIFY username VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
    } catch (Exception $e) {
    }
    try {
        $db->query("ALTER TABLE users MODIFY email_address VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
    } catch (Exception $e) {
    }
    try {
        $db->query("ALTER TABLE users MODIFY social_address VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
    } catch (Exception $e) {
    }

    $db->query("CREATE TABLE IF NOT EXISTS `verification_questions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `entity` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `input_type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `verification_answers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `question_id` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `answer` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;");

    try {
        $db->query("ALTER TABLE  `users` ADD  `social_info` VARCHAR(1000) NOT NULL ");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE  `users` ADD  `membership_type` VARCHAR(255) NOT NULL AFTER  `birth_year` , ADD  `membership_expire_time` INT NOT NULL AFTER  `membership_type` , ADD  `membership_plan` INT NOT NULL AFTER  `membership_expire_time` ;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `languages` ADD `enable` INT(2) NOT NULL DEFAULT '1' AFTER `active`;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `language_phrases` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    } catch (Exception $e) {
    }

    $db->query("CREATE TABLE IF NOT EXISTS`promotion_code` (
    `id` tinyINT(4) NOT NULL AUTO_INCREMENT,
    `coupon` VARCHAR(255) NOT NULL,
    `discount` INT(10) NOT NULL,
    `no_of_use` VARCHAR(10) NOT NULL,
    `status` INT(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;");

    $db->query("CREATE TABLE IF NOT EXISTS `fcm_tokens` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `token` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
      `user_id` INT(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `education_list` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `user_id` varchar(20) NOT NULL,
	 `type` varchar(50) NOT NULL,
	 `company` varchar(100) NOT NULL,
	 `position` varchar(100) NOT NULL,
	 `city` varchar(50) NOT NULL,
	 `description` text NOT NULL,
	 `present` varchar(2) NOT NULL,
	 `start_day` varchar(10) NOT NULL,
	 `start_month` varchar(20) NOT NULL,
	 `start_year` varchar(10) NOT NULL,
	 `end_day` varchar(10) NOT NULL,
	 `end_month` varchar(20) NOT NULL,
	 `end_year` varchar(10) NOT NULL,
	 `school` varchar(200) NOT NULL,
	 `degree` varchar(10) NOT NULL,
	 `graduated` varchar(3) NOT NULL,
	 `concentrations` varchar(100) NOT NULL,
	 PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1");


    $db->query('CREATE TABLE IF NOT EXISTS `request_throttle` (
	  `id` int(10) UNSIGNED NOT NULL,
	  `uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
	  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `user_agent` text COLLATE utf8_unicode_ci NOT NULL,
	  `timeout` int(10) UNSIGNED NOT NULL,
	  `request_count` int(10) UNSIGNED NOT NULL,
	  `expire_time` timestamp NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

    try {
        $db->query('ALTER TABLE `request_throttle` ADD PRIMARY KEY (`id`);');
    } catch (Exception $e) {
    }
    try {
        $db->query('ALTER TABLE `request_throttle` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;');
    } catch (Exception $e) {
    }

    try {
        $db->query("CREATE TABLE IF NOT EXISTS `user_locations` ( 
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `lon` DECIMAL(9,6) NOT NULL,
            `lat` DECIMAL(8,6) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
    } catch (Exception $e) {
    }

    try {
        $db->query("CREATE TABLE IF NOT EXISTS `member_subscriptions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `payment_method` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `subscription_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `customer_id` varchar(50) COLLATE utf8_unicode_ci NULL,
            `plan_id` varchar(50) COLLATE utf8_unicode_ci NULL,
            `txn_id` varchar(50) COLLATE utf8_unicode_ci NULL,
            `amount` DOUBLE NOT NULL,
            `currency` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
            `interval` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
            `interval_count` tinyint(2) NOT NULL,
            `payer_email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `valid_from` datetime NOT NULL,
            `valid_to` datetime NOT NULL,
            `status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
    } catch (Exception $e) {
    }

    try {
        $db->query("CREATE TABLE IF NOT EXISTS `feed_embeds` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `feed_id` int(11) NOT NULL,
      `embed_code` TEXT COLLATE utf8_unicode_ci NOT NULL,
	  `embed_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
    } catch (Exception $e) {
    }


    try {
		$db->query("ALTER TABLE `announcement_hide` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
    } catch (Exception $e) {}
    try {
		$db->query("ALTER TABLE `feed_pinned` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
    } catch (Exception $e) {}
    try {
		$db->query("ALTER TABLE `group_members` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
    } catch (Exception $e) {}
    try {
		$db->query("ALTER TABLE `language_phrases` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
    } catch (Exception $e) {}
    try {
		$db->query("ALTER TABLE `menus` ADD `pid` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`pid`);");
    } catch (Exception $e) {}
    try {
		$db->query("ALTER TABLE `page_invites` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
    } catch (Exception $e) {}
    try {
		$db->query("ALTER TABLE `poll_results` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);");
    } catch (Exception $e) {}

    dump_site_pages();
}

function dump_site_pages()
{
    register_site_page("home", array('title' => 'landing', 'column_type' => TOP_NO_CONTAINER_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'home', 'splash', 'top');
        //Widget::add(null,'home','top-members', 'middle');
        Menu::saveMenu('header-account-menu', 'saved', 'saved', 'manual', '1', 'fa fa-save');

        //trick to delete the plugin folders
        //delete_file(path('themes/default/plugins/'));
        //delete_file(path('themes/default/plugins/'));
    });
    register_site_page("signup", array('title' => 'Signup', 'column_type' => TOP_NO_CONTAINER_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'signup', 'content', 'middle');
    });
    register_site_page("login", array('title' => 'login', 'column_type' => TOP_NO_CONTAINER_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'login', 'content', 'middle');
    });
    register_site_page("forgot-password", array('title' => 'forgot-password', 'column_type' => THREE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'forgot-password', 'content', 'middle');
    });
    register_site_page("reset-password", array('title' => 'reset-password', 'column_type' => THREE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'reset-password', 'content', 'middle');
    });
    register_site_page("signup-activate", array('title' => 'signup-activate', 'column_type' => THREE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'signup-activate', 'content', 'middle');
    });
    register_site_page("account", array('title' => 'account-page', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'account', 'content', 'middle');
        Widget::add(null, 'account', 'account-menu', 'left');
        Menu::saveMenu('header-account-menu', lang('account-settings'), 'account', 'manual', '1', 'fa fa-cogs');
    });
    register_site_page("profile", array('title' => 'user-profile-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function () {
        Widget::add(null, 'profile', 'content', 'middle');
        Widget::add(null, 'profile', 'profile-info', 'right');
    });

    register_site_page("saved", array('title' => 'saved', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'saved', 'content', 'middle');
        Widget::add(null, 'saved', 'saved-menu', 'left');
        Menu::saveMenu('header-account-menu', lang('saved'), 'saved', 'manual', '1', 'fa fa-save');
    });
    register_site_page("feed", array('title' => 'feed', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function () {
        Widget::add(null, 'feed', 'content', 'middle');
        Widget::add(null, 'feed', 'user-card', 'right');
        Menu::saveMenu('main-menu', 'news-feed', 'feed', 'manual', true, 'ion-home');
    });

    register_site_page("view-post", array('title' => 'feed::view-feed', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function () {
        Widget::add(null, 'view-post', 'content', 'middle');
        Widget::add(null, 'view-post', 'plugin::relationship|suggestions', 'right');
    });

    register_site_page('mailing-unsubscribe', array('title' => lang('unsubscribe'), 'column_type' => TOP_NO_CONTAINER_ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'mailing-unsubscribe', 'content', 'middle');
    });

    register_site_page('people', array('title' => lang('people'), 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function () {
        Widget::add(null, 'people', 'content', 'middle');
        Widget::add(null, 'people', 'peoplefilter', 'left');
        Menu::saveMenu('main-menu', 'people', 'people', 'manual', 1, 'ion-android-people');
    });

    register_site_page("membership-choose-plan", array('title' => 'membership-choose-plan', 'column_type' => ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'membership-choose-plan', 'content', 'middle');
        Widget::add(null, 'profile', 'membership|membership', 'right');
    });
    register_site_page("membership-payment", array('title' => 'membership-payment', 'column_type' => ONE_COLUMN_LAYOUT), function () {
        Widget::add(null, 'membership-payment', 'content', 'middle');
    });

    $db = db();
    try {
        $db->query("ALTER TABLE  `feeds` ADD  `status` INT NOT NULL DEFAULT  '1' AFTER  `can_share` ;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE  `users` ADD  `membership_type` VARCHAR(255) ");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `membership_plans` CHANGE `price` `price` DOUBLE NOT NULL");
    } catch (Exception $e) {
    }

    $account_menus = array(
        array('link' => 'account', 'icon' => 'fa fa-cogs'),
        array('link' => '?action=invitations', 'icon' => 'fa fa-user-plus'),
        array('link' => 'activities', 'icon' => 'fa fa-area-chart'),
        array('link' => 'ads/create', 'icon' => 'fa fa-plus-square'),
        array('link' => 'announcements', 'icon' => 'fa fa-bullhorn'),
        array('link' => 'saved', 'icon' => 'fa fa-save'),
    );
    $location = "header-account-menu";
    foreach ($account_menus as $menu) {
        $query = $db->query("SELECT * FROM menus WHERE menu_location='" . $location . "' AND link ='{$menu['link']}'");
        $result = $query->fetch_assoc();
        if (is_array($result) && $result['icon'] == "") {
            $db->query("UPDATE menus SET icon = '{$menu['icon']}' WHERE menu_location='" . $location . "' AND link ='{$menu['link']}'");
        }
    }

    try {
        $db->query("ALTER TABLE `users` ADD `security_settings` TEXT NOT NULL AFTER `privacy_info`;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `users` ADD `phone_no` BIGINT(20) NOT NULL AFTER `social_email`;");
    } catch (Exception $e) {
    }

    try {
        $db->query("ALTER TABLE `users` ADD `email_verified` TINYINT(1) NOT NULL AFTER `verified`, ADD `email_verification` VARCHAR(255) NOT NULL AFTER `email_verified`, ADD `phone_no_verified` TINYINT(1) NOT NULL AFTER `email_verification`, ADD `phone_no_verification` VARCHAR(33) NOT NULL AFTER `phone_no_verified`;");
    } catch (Exception $e) {
    }
}

function dump_upgrade_email_templates()
{
    add_email_template("header", array(
        'title' => 'Header',
        'description' => 'This can be added to other emails using the [header] placeholder',
        'body_content' => '<div style="background: #ECECEC; padding: 16px; text-align: center;">
    <img src="' . url_img('[site-logo]') . '" style="max-width: 200px;" />
</div>',
        'placeholders' => '[site-logo]'
    ));

    add_email_template("footer", array(
        'title' => 'Footer',
        'description' => 'This can be added to other emails using the [footer] placeholder',
        'body_content' => '<div style="padding: 16px; background-color: #CCCCCC; font-family: sans-serif; font-size: 12px; color:#FFFFFF">
    <div style="width: 500px; margin: auto;">
        <p>You have received this email because you are a member of [site-title]. This is a genuine email from [site-title]. However, if you receive an email which you are concerned does not legitimately originate from us, you can report before deleting it.</p>
        <h4>Legal</h4>
        <p>This email message is confidential and for use by the addressee only. If the message is received by anyone other than the addressee, please delete it from your computer. [site-title] does not accept responsibility for changes made to this message after it was sent.</p>
        <p>Whilst all reasonable care has been taken to avoid the transmission of viruses, it is the responsibility of the recipient to ensure that onward transmission, opening or use of this message and any attachments will not adversely affect its systems or data. No responsibility is accepted by [site-title] in this regard and the recipient should carry out such virus and other</p>
        <p style="text-align: center">© [site-title] [year]</p>
    </div>
</div>',
        'placeholders' => '[site-title],[year]'
    ));

    add_email_template("forgot-password", array(
        'title' => 'Forgot Password',
        'description' => 'This is forgot password email template sent when user want to reset password',
        'subject' => 'Reset your password',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Password reset</h1>
    <p>You have request for password reset. Please follow this link below to reset your password.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">Reset</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
        'placeholders' => '[link]'
    ));

    add_email_template("signup-activate", array(
        'title' => 'Signup Activation',
        'description' => 'This is activation email template sent when user want first signup to your network',
        'subject' => 'Welcome to [site-title] - Confirm your email address',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Email Activation</h1>
    <p>Thank you for joining our social network. Please follow the link or copy the code below to validate your email</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">Activate</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[code]</a></p>
</div>
[footer]',
        'placeholders' => '[link],[code],[site-title],[recipient-title],[recipient-link]'
    ));

    add_email_template("signup-welcome", array(
        'title' => 'Signup Welcome Email',
        'description' => 'This is welcome email template sent when user want first signup to your network',
        'subject' => 'Welcome to [site-title]',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Welcome</h1>
    <p>Thank you for joining our social network. Please follow the link below to log in to you account</p>
    <p style="text-align: center"><a href="[login_link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">Login</a></p>
    <p style="text-align: center"><a href="[login_link]" style="color: #555555;">[login_link]</a></p>
</div>
[footer]',
        'placeholders' => '[login_link],[site-title],[recipient-title],[recipient-link]'
    ));

    add_email_template("friend-request", array(
        'title' => 'Friend Request',
        'description' => 'This is friend request mail sent to users',
        'subject' => 'New Friend Request',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Friend Request</h1>
    <p>You have a new friend request from [fullname]. Please follow the link below to respond to the friend request.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">Respond to friend request</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
        'placeholders' => '[link],[fullname]'
    ));

    add_email_template("comment-post", array(
        'title' => 'Comment on post',
        'description' => 'This is mail sent to post owner when someone make a comment on it',
        'subject' => 'New Comment On Your Post',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Comment on post</h1>
    <p>You have a new comment from [fullname] on your post. Please follow the link below to see the post.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">See post</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
        'placeholders' => '[link],[fullname]'
    ));

    add_email_template("comment-photo", array(
        'title' => 'Comment on Photo',
        'description' => 'This is mail sent to photo owner when someone make a comment on it',
        'subject' => 'New Comment On Your Photo',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Comment on photo</h1>
    <p>You have a new comment from [fullname] on your photo. Please follow the link below to see the photo.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">See photo</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
        'placeholders' => '[link],[fullname]'
    ));

    add_email_template("post-on-wall", array(
        'title' => 'Posted on your timeline',
        'description' => 'This is mail sent to profile owner when someone posted on his/her wall',
        'subject' => '[fullname] posted on your wall',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Post on Timeline</h1>
    <p>[fullname] has posted on your timeline. Please follow the link below to see the post.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">See post</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
        'placeholders' => '[link],[fullname]'
    ));

    add_email_template("friend-acceptance", array(
        'title' => 'Friend request accepted',
        'description' => 'This is mail sent for friend request acceptance',
        'subject' => '[fullname] accepted your friend request',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Friend request accepted</h1>
    <p>[fullname] accepted your friend request. Please follow the link below to see the profile.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">See post</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
        'placeholders' => '[link],[fullname]'
    ));

    add_email_template('email-verification', array(
        'title' => lang('email-verification'),
        'description' => lang('email-verification-mail-template-desc'),
        'subject' => '[site-title] - Verify your email address',
        'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Email Verification</h1>
    <p>Please use the code below to verify your email address.</p>
    <p style="text-align: center"><span style="padding: 8px; font-size: 24px; font-weight: bold; color: #555555; display: inline-block">[code]</span></p>
</div>
[footer]',
        'placeholders' => '[link],[code],[site-title],[recipient-title],[recipient-link]'
    ));
}
