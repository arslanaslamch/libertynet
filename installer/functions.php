<?php

function installer_plugins() {
    $plugins = get_all_plugins();

    foreach ($plugins as $key => $info) {
        if ($key != 'membership' && $key != 'invitationsystem') plugin_activate($key);
    }
    return true;
}

function install_languages() {
    update_all_language_phrases(false);
}

function installer_save_info($val) {
    /**
     * @var $title
     * @var $email
     * @var $username
     * @var $password
     * @var $confirm_password
     * @var $fullname
     */
    extract($val);
    if (!$title or !$email or !$username or !$password or !$confirm_password or $password != $confirm_password or !$fullname) return false;

    $name = explode(' ', $fullname);
    $firstName = $name[0];
    $lastName = (isset($name[1])) ? $name[1] : '';
    add_user(array(
        'username' => $username,
        'email_address' => $email,
        'password' => $password,
        'gender' => 'male',
        'role' => '1',
        'first_name' => $firstName,
        'last_name' => $lastName
    ));
    db()->query("UPDATE users SET role='1' WHERE id='1'");
    save_admin_settings(array('site_title' => $title));

    $val = unserialize(session_get("database-details"));
    /**
     * @var $host
     * @var $username
     * @var $name
     * @var $password
     */
    extract($val);

    if (empty($host) or !$username or !$name) return false;

    $configContent = file_get_contents(path('config-holder.php'));
    //replace the details
    $configContent = str_replace(array(
        '{localhost}', '{root}', '{dbname}', '{dbpassword}', '{installed}'
    ), array($host, $username, $name, $password, 'true'), $configContent);
    file_put_contents(path('config.php'), $configContent);
    return true;
}

function installer_input($name, $default = null, $escape = null) {
    $escape_options = array('sanitize_mysql' => false);
    return input($name, $default, $escape, $escape_options);
}
