<?php
function index_pager() {
	$accept = installer_input('accept');

	if($accept && get_request_method() === 'POST') {
		redirect(url('install/require'));
	}
	echo view('installer/index');
}

function database_pager() {
    set_time_limit(0);
    $message = null;
    if(session_get('purchase')) {
        $val = installer_input('val');
        if($val) {
            validator($val, array(
                'host' => 'required',
                'username' => 'required',
                'name' => 'required'
            ));
            if(validation_passes()) {
                $config_content = file_get_contents(path('config-holder.php'));
                $config_content = str_replace(array('{localhost}', '{root}', '{dbname}', '{dbpassword}', '{installed}'), array($val['host'], $val['username'], $val['name'], $val['password'], '0'), $config_content);
                file_put_contents(path('config.php'), $config_content);
                if(app()->config) {
                    app()->config = array_merge(app()->config, include(__DIR__.'/../config.php'));
                }
                try {
                    if(app()->db) {
                        db()->close();
                        app()->db = null;
                    }
                    $db = db();
                } catch (Exception $e) {
                    $db = null;
                }
                if ($db) {
                    require path('includes/database/install.php');
                    require path('includes/database/upgrade.php');
                    core_install_database(true);
                    update_core_settings();
                    core_upgrade_database();
                    session_put('database-details', serialize($val));
                    installer_plugins();
                    install_languages();
                    fire_hook('install.first');
                    redirect_to_pager('install-info');
                } else {
                    $message = 'Failed to connect to database with provided information. Please, review the details.';
                }
            } else {
                $message = validation_first();
            }
        }
    } else {
        redirect_to_pager('install-requirements');
    }
	echo view('installer/database', array('message' => $message));
}

function database_steps_pager() {
    set_time_limit(0);
    $result = array(
        'status' => 0,
        'message' => ''
    );
    if(session_get('purchase')) {
        $step = installer_input('step');
        switch ($step) {
            case 'start':
                $val = installer_input('val');
                if($val) {
                    validator($val, array(
                        'host' => 'required',
                        'username' => 'required',
                        'name' => 'required'
                    ));
                    if(validation_passes()) {
                        $config_content = file_get_contents(path('config-holder.php'));
                        $config_content = str_replace(array('{localhost}', '{root}', '{dbname}', '{dbpassword}', '{installed}'), array($val['host'], $val['username'], $val['name'], $val['password'], '0'), $config_content);
                        file_put_contents(path('config.php'), $config_content);
                        if(app()->config) {
                            app()->config = array_merge(app()->config, include(__DIR__.'/../config.php'));
                        }
                        try {
                            if(app()->db) {
                                db()->close();
                                app()->db = null;
                            }
                            $db = db();
                        } catch (Exception $e) {
                            $db = null;
                        }
                        if($db) {
                            session_put('database-details', serialize($val));
                            $result['message'] = 'Database connection successful.';
                            $result['status'] = 1;
                        } else {
                            $result['message'] = 'Failed to connect to database with provided information. Please review the details.';
                        }
                    } else {
                        $result['message'] = validation_first();
                    }
                } else {
                    $result['message'] = 'Invalid request.';
                }
                break;
            case 'core':
                $action = installer_input('action');
                if($action === 'install') {
                    require path('includes/database/install.php');
                    core_install_database(true);
                    $result['status'] = 1;
                    $result['message'] = 'Core features installed.';
                } else if($action === 'update') {
                    update_core_settings();
                    $result['status'] = 1;
                    $result['message'] = 'Core settings updated.';

                } else if($action === 'upgrade') {
                    require path('includes/database/upgrade.php');
                    core_upgrade_database();
                    $result['status'] = 1;
                    $result['message'] = 'Core features upgraded.';
                } else {
                    $result['status'] = 1;
                    $result['message'] = 'Installing core features.';
                    $result['actions'] = array('install', 'update', 'upgrade');
                }
                break;
            case 'plugin':
                $plugin = installer_input('plugin');
                $plugins = get_all_plugins();
                if($plugin) {
                    plugin_activate($plugin);
                    $result['status'] = 1;
                    $result['message'] = $plugins[$plugin]['title'].' plugin installed.';
                } else {
                    $excluded_plugins = array('invitationsystem');
                    foreach ($excluded_plugins as $key) {
                        if(array_key_exists($key, $plugins)) {
                            unset($plugins[$key]);
                        }
                    }
                    $result['status'] = 1;
                    $result['message'] = 'Installing plugins.';
                    $result['plugins'] = array_keys($plugins);
                }
                break;
            case 'language':
                $language = installer_input('language');
                $languages = array('english', 'spanish', 'russian', 'italian', 'french', 'portuguese', 'japanese', 'arabic');
                if($language) {
                    update_language_phrases($language);
                    $result['status'] = 1;
                    $result['message'] = ucwords($language).' language installed.';
                } else {
                    $result['status'] = 1;
                    $result['message'] = 'Installing languages.';
                    $result['languages'] = $languages;
                }
                break;
            case 'finish':
                fire_hook('install.first');
                $result['status'] = 1;
                $result['message'] = 'Database installation complete.';
                $result['redirect_url'] = url_to_pager('install-info');
                break;
        }
    } else {
        $result['redirect_url'] = url_to_pager('install-requirements');
    }

	$response = json_encode($result);
	return $response;
}

function install_setup_system() {
	Pager::addSitePage(null, array('slug' => 'terms-and-condition', 'title' => array('english' => 'Terms and condition'), 'content' => 'Your content here', 'page_type' => 'auto'));
	Pager::addSitePage(null, array('slug' => 'privacy-policy', 'title' => array('english' => 'Privacy Policy'), 'content' => 'Your content here', 'page_type' => 'auto'));
	Pager::addSitePage(null, array('slug' => 'disclaimer', 'title' => array('english' => 'Disclaimer'), 'content' => 'Your content here', 'page_type' => 'auto'));
	Pager::addSitePage(null, array('slug' => 'about', 'title' => array('english' => 'About Us'), 'content' => 'Your content here', 'page_type' => 'auto'));

	load_functions('game::game');
	game_add_category(array('title' => array('english' => 'Action')));
	game_add_category(array('title' => array('english' => 'Racing')));
	game_add_category(array('title' => array('english' => 'Fighting')));
	game_add_category(array('title' => array('english' => 'Sports')));

	load_functions('event::event');
	event_add_category(array('title' => array('english' => 'Wedding')));
	event_add_category(array('title' => array('english' => 'Outing')));
	event_add_category(array('title' => array('english' => 'Party')));

	load_functions('page::page');
	page_add_category(array('title' => array('english' => 'Web Designer'), 'desc' => array('english' => '')));
}

function require_pager() {
	$message = session_get('require-message');
	echo fire_hook('install.require');
	echo view('installer/requirements', array('message' => $message));
}


function info_pager() {
	if(!session_get('purchase')) {
	    redirect(url('install/requirements'));
    }
	$message = null;
	$val = input('val');
	if($val) {

		install_setup_system();
		fire_hook('core.info.in');
		$message = 'All fields are required and make sure your password match and you provide a valid email address';
	}
	echo view('installer/info', array('message' => $message));
}

function finish_pager() {
	echo view('installer/finish');
}