<?php

/**
 * Error handler
 * @param $level
 * @param $message
 * @param $file
 * @param $line
 * @param $context
 * @throws ErrorException
 */
function php_error_handler($level, $message, $file, $line, $context)
{
    if (error_reporting() and $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
}

/**
 * exception hander
 * @param $e
 */
function php_exception_handler($e)
{
    return MyError::handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
}

/**
 * @return void
 */
function php_fatal_error_handler()
{
    $error = error_get_last();

    if ($error) {
        /**
         * @var $type
         * @var $message
         * @var $file
         * @var $line
         * @var $type
         */
        extract($error);
        if (!in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) return;
        //Error::handler($error['type'], $error['message'], $error['file'], $error['line'], "");
        return MyError::handler($type, $message, $file, $line);
    }
}

/**
 * Function to get the full url
 * @param bool $queryStr
 * @return string
 */
function getFullUrl($queryStr = false)
{
    $request = $_SERVER;
    $host = (isset($request['HTTP_HOST'])) ? $request['HTTP_HOST'] : $request['SERVER_NAME'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $queryString = (isset($_SERVER['QUERY_STRING'])) ? trim($_SERVER['QUERY_STRING'], '?') : null;
    $scheme = (App::getInstance()->sslEnabled()) ? "https://" : "http://";
    $fullUrl = $scheme . $host . $uri;
    $fullUrl = ($queryStr) ? $fullUrl . '?' . $queryString : $fullUrl;
    $fullUrl = trim($fullUrl, '?');
    return $fullUrl;
}

function isSecure()
{
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == 'https') ? true : false;
    return $isSecure;
}

function getScheme()
{
    return (App::getInstance()->sslEnabled()) ? 'https' : 'http';
}

function getHost()
{
    $request = $_SERVER;
    $host = (isset($request['HTTP_HOST'])) ? $request['HTTP_HOST'] : $request['SERVER_NAME'];

    //remove unwanted characters
    $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
    //prevent Dos attack
    if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
        die();
    }

    return $host;
}

function server($name, $default = null)
{
    if (isset($_SERVER[$name])) return $_SERVER[$name];
    return $default;
}

function getRoot()
{
    $base = getBase();

    return getScheme() . '://' . getHost() . $base;
}

function getBase()
{
    $filename = basename(server('SCRIPT_FILENAME'));
    if (basename(server('SCRIPT_NAME')) == $filename) {
        $baseUrl = server('SCRIPT_NAME');
    } elseif (basename(server('PHP_SELF')) == $filename) {
        $baseUrl = server('PHP_SELF');
    } elseif (basename(server('ORIG_SCRIPT_NAME')) == $filename) {
        $baseUrl = server('ORIG_SCRIPT_NAME');
    } else {
        $baseUrl = server('SCRIPT_NAME');
    }

    $baseUrl = preg_replace('/index\.php/i', '', $baseUrl);

    return $baseUrl;
}

/**
 * Function to get the request method
 * @return string
 */
function get_request_method()
{
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

/**
 * Method to get path
 * @param string $path
 * @return string
 */
function path($path = "")
{
    return App::getInstance()->path($path);
}

/**
 * Function to get app instance
 */
function app()
{
    return App::getInstance();
}

/**
 * Method to get path from plugin dir
 * @param $plugin
 * @param string $path
 * @return string
 */
function plugin_path($plugin, $path = "")
{
    return App::getInstance()->pluginPath($plugin, $path);
}

/**
 * Function to register pagers
 * @param string $pattern
 * @param array $parameters
 * @return boolean
 */
function register_post_pager($pattern, $parameters = array())
{
    return Pager::post($pattern, $parameters);
}

/**
 * @param $pattern
 * @param array $parameters
 * @return bool
 */
function register_get_pager($pattern, $parameters = array())
{
    return Pager::get($pattern, $parameters);
}

/**
 * @param $pattern
 * @param array $parameters
 * @return Page|bool
 */
function register_pager($pattern, $parameters = array())
{
    return Pager::any($pattern, $parameters);
}

/**
 * Method to add filters
 * @param string $name
 * @param mixed $callable
 * @return void
 */
function register_filter($name, $callable)
{
    return Pager::addFilter($name, $callable);
}

/**
 * Function to load html view file
 * @param string $view
 * @param array $param
 * @return string
 */
function view($view, $param = array())
{
    return App::getInstance()->view($view, $param);
}

/**
 * Method for setting meta tags
 * @param $new_meta_tags
 * @return array
 */
function set_meta_tags($new_meta_tags)
{
    return App::getInstance()->setMetaTags($new_meta_tags);
}

/**
 * Method for getting meta tags
 */
function get_meta_tags_array()
{
    return App::getInstance()->getMetaTags();
}

/**
 * Method for rendering meta tags
 */
function render_meta_tags()
{
    return App::getInstance()->renderMetaTags();
}

/**
 * Method to register assets css, js e.t.c
 * @param $asset
 * @param string $themeType
 * @return App
 */
function register_asset($asset, $themeType = "frontend")
{
    return App::getInstance()->registerAsset($asset, $themeType);
}

/**
 * Method to render an assets
 * @param $type
 * @param string $themeType
 * @return string
 */
function render_assets($type, $themeType = "frontend")
{
    return App::getInstance()->renderAssets($type, $themeType);
}

/**
 * function to easily get the generated page title
 */
function get_title()
{
    return App::getInstance()->title;
}

/**
 * Function to get image assets
 * @param string $path e.g images/file.png or pluginname:images/file.png
 * @param int $size
 * @return string
 */
function img($path, $size = null)
{
    $path = fire_hook('img.path', $path ? $path : ' ');
    $path = ($size) ? str_replace('%w', $size, $path) : $path;
    $url = url(App::getInstance()->getAssetLink($path, false));
    $url = fire_hook('image.url', $url);
    return $url;
}

function asset_link($path)
{
    return App::getInstance()->getAssetLink($path);
}

function url_img($path, $size = null)
{
    $path = ($size) ? str_replace('%w', $size, $path) : $path;
    $path = (string) $path;

    if (stripos('%d', $path) != -1) {
        if ($size < 200) {
            $size = 200;
        } elseif ($size < 700) {
            $size = 600;
        } else {
            $size = 960;
        }
        $path = ($size) ? str_replace('%d', $size, $path) : $path;
    }
    $url = url($path);
    $url = fire_hook('filter.url', $url);
    $url = fire_hook('image.url', $url);
    return $url;
}

function video_url($path)
{
    return url($path);
}

/**
 * Method to set page title
 * @param string $title
 * @return App
 */
function set_title($title = "")
{
    return App::getInstance()->setTitle($title);
}

/**
 * Method to get the database instance
 */
function db()
{
    return App::getInstance()->db();
}

function config($key, $default = null)
{
    return App::getInstance()->config($key, $default);
}

/**
 * function to get translations
 * @param string $name
 * @param array $replace
 * @param null $default
 * @return string
 */
function lang($name, $replace = array(), $default = null)
{
    return App::getInstance()->getTranslation($name, $replace, $default);
}

function _lang($name, $replace = array(), $default = null)
{
    echo lang($name, $replace, $default);
}

/**
 * Function get user inputs from GET or POST Method
 * @param string $name
 * @param mixed $default
 * @param bool $escape
 * @param null $escape_options
 * @return mixed
 */
function input($name, $default = null, $escape = null, $escape_options = null)
{
    $default_escape_options = array('sanitize_mysql' => true, 'sanitize_html' => true, 'strip_tags' => true);
    $escape = isset($escape) ? $escape : true;
    $escape_options = is_array($escape_options) ? array_merge($default_escape_options, $escape_options) : $default_escape_options;
    $index = '';
    if (false !== strpos($name, ".")) {
        list($name, $index) = explode('.', $name);
    }
    $result = isset($_GET[$name]) ? $_GET[$name] : $default;
    $result = isset($_POST[$name]) ? $_POST[$name] : $result;
    $result = $index ? (is_array($result) && isset($result[$index]) ? $result[$index] : $default) : $result;
    if (is_array($result)) {
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                array_walk_recursive($result[$key], function (&$v) use ($escape, $escape_options, $key) {
                    if ($escape === true || (is_array($escape) && in_array($key, $escape))) {
                        if ($escape_options['sanitize_html']) {
                            $v = sanitizeText($v, false, $escape_options['strip_tags']);
                        } elseif ($escape_options['strip_tags']) {
                            $v = sanitizeText($v);
                        }
                        if ($escape_options['sanitize_mysql']) {
                            $v = mysqli_real_escape_string(db(), $v);
                        }
                    }
                });
            } else if ($escape === true) {
                if ($escape_options['sanitize_html']) {
                    $result[$key] = sanitizeText($result[$key], false, $escape_options['strip_tags']);
                } elseif ($escape_options['strip_tags']) {
                    $result[$key] = sanitizeText($result[$key]);
                }
                if ($escape_options['sanitize_mysql']) {
                    $result[$key] = mysqli_real_escape_string(db(), $result[$key]);
                }
            }
        }
    } else if ($escape === true && $result !== null) {
        if ($escape_options['sanitize_html']) {
            $result = sanitizeText($result, false, $escape_options['strip_tags']);
        } elseif ($escape_options['strip_tags']) {
            $result = sanitizeText($result);
        }
        if ($escape_options['sanitize_mysql']) {
            $result = mysqli_real_escape_string(db(), $result);
        }
    }
    $result = fire_hook('input', array($result), array($name))[0];
    return $result;
}

/**
 * Function get user file input
 * @param string $name
 * @return mixed
 */
function input_file($name)
{
    $files = false;
    if (isset($_FILES[$name])) {
        if (is_array($_FILES[$name]['name'])) {
            $files = array();
            $index = 0;
            foreach ($_FILES[$name]['name'] as $n) {
                if ($_FILES[$name]['name'] != 0) {
                    $files[] = array(
                        'name' => sanitizeText($n),
                        'type' => $_FILES[$name]['type'][$index],
                        'tmp_name' => $_FILES[$name]['tmp_name'][$index],
                        'error' => $_FILES[$name]['error'][$index],
                        'size' => $_FILES[$name]['size'][$index]
                    );
                }
                $index++;
            }
            $files = empty($files) ? false : $files;
        } else {
            $files = isset($_FILES[$name]['size']) && $_FILES[$name]['size'] == 0 ? false : $_FILES[$name];
            if (isset($_FILES[$name]['name'])) {
                $_FILES[$name]['name'] = sanitizeText($_FILES[$name]['name']);
            }
        }
    }
    $files = fire_hook('input.file', array($files), array($name))[0];
    return $files;
}

/**
 * function to get if input has a value
 * @param string $name
 * @return mixed
 */
function input_has($name)
{
    return input($name);
}

/**
 * Method to put data into the session
 * @param string $name
 * @param string $value
 * @return boolean
 */
function session_put($name, $value = "")
{
    $_SESSION[$name] = $value;
    return true;
}

/**
 * Function to get value from a session
 * @param string $name
 * @param bool $default
 * @return string
 */
function session_get($name, $default = false)
{
    if (!isset($_SESSION[$name])) return $default;
    return $_SESSION[$name];
}

/**
 * Method to remove data from the session
 * @param string $name
 * @return boolean
 */
function session_forget($name)
{
    if (isset($_SESSION[$name])) unset($_SESSION[$name]);
    return true;
}

/**
 * Method to put data into the cookie
 * @param string $name
 * @param string $value
 * @param int $expire
 * @param string $path
 * @param string $domain
 * @param bool $secure
 * @param bool $httponly
 * @return boolean
 */
function cookie_put($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
{
    $setcookie = setcookie($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false);
    return $setcookie;
}

/**
 * Function to get value from a cookie
 * @param string $name
 * @param mixed $default
 * @return string
 */
function cookie_get($name, $default = null)
{
    $cookie = isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    return $cookie;
}

/**
 * Method to remove data from the cookie
 * @param string $name
 * @return boolean
 */
function cookie_forget($name)
{
    if (isset($_COOKIE[$name])) {
        unset($_COOKIE[$name]);
        setcookie($name, null, time() - 86400, '/');
    }
    return true;
}

/**
 * Function to redirect by link
 * @param string $url
 * @param array $flash
 * @return mixed
 * @parapm array $flash array('id' => 'flash-message-id', 'message' => '')
 */
function redirect($url, $flash = array())
{
    add_flash($flash);
    @session_write_close();
    @session_regenerate_id(true);
    header("Location:" . $url);
    exit;
}

/**
 * @param array $flash
 * @return bool
 */
function redirect_back($flash = array())
{
    $back = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
    add_flash($flash);
    if (empty($back) and !preg_match("#" . config("base_url") . "#", $back)) redirect(url());
    redirect($back);
    return true;
}

//redirect to a pager
function redirect_to_pager($id, $param = array(), $flash = array())
{
    $url = Pager::getLink($id, $param);
    add_flash($flash);
    redirect($url);
    return true;
}

function add_flash($flash = array())
{
    if ($flash and isset($flash['id']) and isset($flash['message'])) {
        $id = $flash['id'];
        $message = serialize($flash['message']);
        session_put($id, $message);
    }
}

/**
 * Function to check flash data
 * @param string $id
 * @return bool
 */
function has_flash($id)
{
    $data = session_get($id);
    if ($data) return true;
    return false;
}

/**
 * Function to flash data
 * @param string $id
 * @return mixed|string
 */
function get_flash($id)
{
    $data = session_get($id);
    if ($data) $data = unserialize($data);
    session_forget($id);
    return $data;
}

/**
 * Method to generate a link
 * @param string $url
 * @param int $check_secure
 * @return string
 */
function url($url = "", $check_secure = 2)
{

    return App::getInstance()->url($url, $check_secure);
}

/**
 * Method to generate link to a pager
 * @param string $id
 * @param array $param
 * @return string
 */
function url_to_pager($id, $param = array())
{
    return Pager::getLink($id, $param);
}

/**
 * Function to add menus
 * @param $location
 * @param $details
 * @return Menu
 */
function add_menu($location, $details)
{
    return Pager::addMenu($location, $details);
}

/**
 * function to get a menu object by its location and id
 * @param string $location
 * @param string $id
 * @return Menu
 */
function get_menu($location, $id)
{
    return Pager::getMenu($location, $id);
}

/**
 * Method to get the menus for a location
 * @param string $location
 * @return array
 */
function get_menus($location)
{
    return Pager::getMenus($location);
}

/**
 * Method to add available menus
 * @param string $title
 * @param string $link
 * @param string $icon
 * @param string $location
 * @return boolean
 */
function add_available_menu($title, $link, $icon = null, $location = 'all')
{
    return Menu::addAvailableMenu($title, $link, $icon, $location);
}

/**
 * Method to get available menus
 * @param string $location
 * @return array
 */
function get_available_menus($location)
{
    return Menu::getAvailableMenus($location);
}

/**
 * Method to add menu locations
 * @param string $id
 * @param string $title
 * @return boolean
 */
function add_menu_location($id, $title)
{
    return Menu::addLocation($id, $title);
}

/**
 * Method to get menu locations
 * @return array
 */
function get_menu_locations()
{
    return Menu::getLocations();
}

/**
 * function to get the settings list
 * @return array
 */
function get_settings_menu()
{
    $path = path("includes/settings/");
    $openDir = opendir($path);
    $file = array();
    while ($read = readdir($openDir)) {
        if (substr($read, 0, 1) != ".") {
            $settingId = str_replace(".php", "", $read);
            $settings = include $path . $settingId . '.php';
            //$file[$settingId] = $settings['title'];
        }
    }
    return $file;
}

/**
 * Function to load functions file
 * @param null $path
 */
function load_functions($path = null)
{
    return App::getInstance()->loadFunctionFile($path);
}

/**
 * Function to save a value to cache
 * @param string $key
 * @param mixed $value
 * $param int   $time
 * @param null $time
 */
function set_cache($key, $value, $time = null)
{
    $cache = new Cache;
    $cache->set($key, $value, $time);
}

/**
 * Function to get value from cache
 * @param mixed $key
 * @param null $default
 * @return mixed
 */
function get_cache($key, $default = null)
{
    $cache = Cache::getInstance();
    return $cache->get($key, $default);
}

/**
 * Function to set a value forever in cache
 * @param string $key
 * @param $value
 * @internal param mixed $value
 */
function set_cacheForever($key, $value)
{
    $cache = Cache::getInstance();
    $cache->setForever($key, $value);
}

/**
 * Function to unset a value from cache
 * @param $key
 * @return void
 */
function forget_cache($key)
{
    $cache = Cache::getInstance();
    $cache->forget($key);
}

/**
 * Function to unset all value from cache
 * @return void
 */
function flush_cache()
{
    $cache = Cache::getInstance();
    $cache->flush();
}

/**
 * Function to check if a key exists in cache
 * @param string $key
 * @return bool
 */
function cache_exists($key)
{
    $cache = Cache::getInstance();
    return $cache->keyexists($key);
}

/**
 * function get admin settings
 * @param string $key
 * @param string $default
 * @return mixed
 */
function get_setting($key, $default = "")
{
    return config($key, $default);
}

/**
 * Function to hash content
 * @param string $content
 * @return string
 */
function hash_make($content)
{
    $app = App::getInstance();
    if ($app->config('bcrypt')) {
        require_once path("includes/libraries/password.php");
        return password_hash($content, PASSWORD_BCRYPT, array('cost' => 10));
    } else {
        return md5($content);
    }
}

/**
 * function to check if a given hash match a content
 * @param string $content
 * @param string $hash
 * @return boolean
 */
function hash_check($content, $hash)
{
    $app = App::getInstance();
    if ($app->config('bcrypt')) {
        require_once path("includes/libraries/password.php");
        return password_verify($content, $hash);
    } else {
        return (md5($content) == $hash);
    }
}

/**
 * Function to attach several callback to an event
 * @param $event
 * @param null $callback
 * @return mixed|null
 */
function register_hook($event, $callback)
{
    $hook = Hook::getInstance();
    $hook->attachOrFire($event, $values = null, $callback);
}

/**
 * Function to fire several events  attached to a hook
 * @param $event
 * @param null $values
 * @param array $param
 * @return mixed|null
 * @internal param null $callback
 */
function fire_hook($event, $values = null, $param = array())
{
    $hook = Hook::getInstance();
    return $hook->attachOrFire($event, $values, $callback = null, $param);
}

/**
 * Function to segment from the uri
 * @param int $index
 * @param null $default
 * @return string
 */
function segment($index, $default = null)
{
    return App::getInstance()->segment($index, $default);
}

/**
 * Function to validate inputs
 * @param $inputs
 * @param $rules
 * @return array
 */
function validator($inputs, $rules)
{
    $validator = Validator::getInstance();
    $validator->scan($inputs, $rules);
    return $validator->errors();
}

/**
 * @return bool
 */
function validation_passes()
{
    $validator = Validator::getInstance();
    $errors = $validator->errorBag;
    if (empty($errors)) return TRUE;
    return FALSE;
}

/**
 * @return bool
 */
function validation_fails()
{
    $validator = Validator::getInstance();
    $errors = $validator->errorBag;
    if (empty($errors)) return FALSE;
    return TRUE;
}

/**
 * Return the first error from the errorBag
 * or the first error for a given field
 * @param null $key
 * @return string
 */
function validation_first($key = null)
{
    $validator = Validator::getInstance();
    return $validator->first($key);
}

/**
 * Function to extend validation
 * @param string $rule
 * @param string $message
 * @param mixed $callable
 * @return mixed
 */
function validation_extend($rule, $message, $callable)
{
    Validator::getInstance()->extendValidation($rule, $message, $callable);
}

/**
 * Function to get user ip address
 * @return string
 */
function get_ip()
{
    //Just get the headers if we can or else use the SERVER global
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    } else {
        $headers = $_SERVER;
    }

    //Get the forwarded IP if it exists
    if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $the_ip = $headers['X-Forwarded-For'];
    } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
    } else {
        $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    return $the_ip;
}

/**
 * Function to get all languages from language directory
 * @return array
 */
function get_languages()
{
    $directory = path("languages/");
    $handle = opendir($directory);
    $languages = array();

    while ($read = readdir($handle)) {
        if (substr($read, 0, 1) != '.' and preg_match("#\.php#", $read)) {
            $lang = str_replace('.php', '', $read);
            $languages[$lang] = ucwords($lang) . '';
        }
    }
    return $languages;
}

/**
 * Function to return old input from $_POST global
 * or return default if not available
 * @param $key
 * @param null $default
 * @return null
 */
function input_old($key, $default = null)
{
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}

/**
 * Function to get active theme of a type
 * @param string $type
 * @param bool $session_false
 * @return string
 */
function get_active_theme($type = 'frontend', $session = false)
{
    $themes = array();
    $theme = 'default';
    if (cache_exists('themes')) {
        $themes = get_cache('themes', $themes);
    } else {
        $db = db();
        $query = $db->query("SELECT `type`, `theme` FROM `themes`");
        while ($row = $query->fetch_assoc()) {
            $themes[$row['type']] = $row['theme'];
        }
        set_cacheForever('themes', $themes);
    }
    if (isset($themes[$type]) && !empty($themes)) {
        $theme = $themes[$type];
    }
    if ($type == 'frontend' && $session) {
        $session_theme = session_get('theme.selected');
        if ($session_theme) {
            $theme = $session_theme;
        }
    }

    return $theme;
}

/**
 * function to check if the request is from ajax
 * @return boolean
 */
function is_ajax()
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") {
        return true;
    }
    return false;
}

/**
 * Function to load map unto a page
 * @param $mapinfo
 */
function load_map($mapinfo)
{
    echo '<script>
          var LongLat = new google.maps.LatLng(' . $mapinfo[0] . ',' . $mapinfo[1] . ');
          var map     = "";
          var title   ="' . $mapinfo[4] . '";
          var info    ="' . $mapinfo[5] . '"

          function initialize() {
            var mapCanvas = document.getElementById("map-canvas");
            var mapOptions = {
              center: LongLat,
              zoom:' . $mapinfo[2] . ',
              mapTypeId: google.maps.MapTypeId.' . $mapinfo[3] .
        '}
        map = new google.maps.Map(mapCanvas, mapOptions);
      }
      initialize();

      var infowindow = new google.maps.InfoWindow({
          content: info
      });

      var marker = new google.maps.Marker({
        position: LongLat,
        map:  map,
        title:title
    });

     google.maps.event.addListener(marker, "click", function() {
        infowindow.open(map,marker);
      });
    </script>';
}

/**
 * Get map configurations
 * @param $config
 * @return array
 */
function get_mapconfig($config)
{
    return explode('|', $config);
}

/**
 * Register blocks Page
 * @param $pageId
 * @param null $pageTitle
 * @return Blocks
 */
function register_block_page($pageId, $pageTitle = null)
{
    return Blocks::getInstance()->registerPage($pageId, $pageTitle);
}

/**
 * Function to register site page
 * @param string $id
 * @param array $page
 * @param null $oCallback
 * @param null $version
 * @param int $version_type
 * @return bool
 */
function register_site_page($id, $page, $oCallback = null, $version = null, $version_type = 4)
{
    return Pager::addSitePage($id, $page, $oCallback, $version, $version_type);
}

/**
 * Function to add widgets
 * @param int $widgetId
 * @param string $pageId
 * @param string $widget
 * @param string $location
 * @return boolean
 */
function add_widget($widgetId, $pageId, $widget, $location)
{
    return Widget::add($widgetId, $pageId, $widget, $location);
}

/**
 * Register Blocks
 * @deprecated
 * @param $blockView
 * @param null $blockTitle
 * @param null $page
 * @param array $settings
 * @return Blocks
 */
function register_block($blockView, $blockTitle = null, $page = null, $settings = array())
{
    return Blocks::getInstance()->registerBlock($blockView, $blockTitle, $page, $settings);
}

/**
 * Get all registered blocks
 * @param null $pageId
 * @return array
 */
function get_blocks($pageId = null)
{
    return Blocks::getInstance()->getBlocks($pageId);
}

/**
 * Get all register pages
 */
function get_block_pages()
{
    return Blocks::getInstance()->getPages();
}

/**
 * Function to add page blocks
 * @param $blockView
 * @param $pageId
 * @param null $blockId
 * @param array $settings
 * @return Blocks
 */
function add_page_block($blockView, $pageId, $blockId = null, $settings = array())
{
    return Blocks::getInstance()->addPageBlock($blockView, $pageId, $blockId, $settings);
}

/**
 * Function to remove page blocks
 * @param $blockView
 * @param $pageId
 * @return Blocks
 */
function remove_page_block($blockView, $pageId)
{
    return Blocks::getInstance()->removePageBlock($blockView, $pageId);
}

/**
 * function to get all blocks for a page
 * @param $pageId
 * @param bool $global
 */
function get_page_blocks($pageId, $global = true)
{
    //return Blocks::getInstance()->getPageBlocks($pageId, $global);
}

function get_page_registered_blocks($pageId)
{
    return Blocks::getInstance()->getPageRegisteredBlocks($pageId);
}

function theme_extend($event, $values = null, $param = array())
{
    return fire_hook($event, $values, $param);
}

/**
 * function to paginate a query
 * @param $query
 * @param int $limit
 * @param int $links
 * @param null $page
 * @return Paginator
 */
function paginate($query, $limit = 10, $links = 7, $page = null)
{
    return $paginator = new Paginator($query, $limit, $links, $page);
}

function delete_file($path, $check_images = true)
{
    if ($check_images && preg_match('/%w/', $path)) {
        $image_sizes = array(75, 200, 600, 920);
        foreach ($image_sizes as $size) {
            delete_file(str_replace('%w', $size, $path), false);
        }
    }
    $basePath = path();
    $basePath2 = $basePath . '/';

    if ($path == $basePath or $path == $basePath2 or !trim($path)) return false;

    $path = fire_hook("delete.file", $path);
    if (is_dir($path) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if (in_array($file->getBasename(), array('.', '..')) !== true) {
                if ($file->isDir() === true) {
                    @rmdir($file->getPathName());
                } else if (($file->isFile() === true) || ($file->isLink() === true)) {
                    unlink($file->getPathname());
                }
            }
        }

        return @rmdir($path);
    } else if ((is_file($path) === true) || (is_link($path) === true)) {
        return unlink($path);
    }

    return false;
}

function toAscii($str, $replace = array(), $delimiter = '-', $charset = 'ISO-8859-1')
{
    $str = str_replace(array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)), array("'", "'", '"', '"', '-', '--', '...'), $str);
    $str = function_exists('iconv') ? iconv($charset, 'UTF-8', $str) : $str;
    $str = !empty($replace) ? str_replace((array)$replace, ' ', $str) : $str;
    //$str = preg_replace('/[^\x{0600}-\x{06FF}A-Za-z !@#$%^&*()]/u','', $str);
    $clean = function_exists('iconv') ? iconv('UTF-8', 'ASCII//IGNORE', $str) : $str;
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    $clean = strtolower(trim($clean, '-'));
    return $clean;
}

/**
 * Mailer functions
 */
function mailer()
{
    return Mailer::getInstance();
}

/**
 * function to add email templates
 * @param $id
 * @param array $details
 * @param string $langId
 * @return bool
 */
function add_email_template($id, $details = array(), $langId = 'english')
{
    return mailer()->addTemplate($id, $details, $langId);
}

/**
 * Mysqli helper function to return all the assoc array results
 * @param mysqli
 * @return array
 */
function fetch_all($query)
{
    $get = array();
    if (!$query) return $get;
    while ($fetch = $query->fetch_assoc()) {
        $get[] = $fetch;
    }

    return $get;
}

if (!function_exists('perfectSerialize')) {
    function perfectSerialize($string)
    {
        return base64_encode(serialize($string));
    }
}

if (!function_exists('perfectUnserialize')) {
    function perfectUnserialize($string)
    {

        if (base64_decode($string, true) == true) {

            return @unserialize(base64_decode($string));
        } else {
            return @unserialize($string);
        }
    }
}

function str_limit($text, $length, $ad = '...')
{
    /**
     * @var $ending
     * @var $exact
     * @var $html
     */
    $ad = is_string($ad) ? array('ending' => $ad) : $ad;
    $default = array('ending' => '...', 'exact' => true, 'html' => false);
    $options = array_merge($default, $ad);
    extract($options);

    if ($html) {
        if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
            return $text;
        }
        $totalLength = mb_strlen(strip_tags($ending));
        $openTags = array();
        $truncate = '';

        preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
        foreach ($tags as $tag) {
            if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                    array_unshift($openTags, $tag[2]);
                } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                    $pos = array_search($closeTag[1], $openTags);
                    if ($pos !== false) {
                        array_splice($openTags, $pos, 1);
                    }
                }
            }
            $truncate .= $tag[1];

            $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
            if ($contentLength + $totalLength > $length) {
                $left = $length - $totalLength;
                $entitiesLength = 0;
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entitiesLength <= $left) {
                            $left--;
                            $entitiesLength += mb_strlen($entity[0]);
                        } else {
                            break;
                        }
                    }
                }

                $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
                break;
            } else {
                $truncate .= $tag[3];
                $totalLength += $contentLength;
            }
            if ($totalLength >= $length) {
                break;
            }
        }
    } else {
        if (mb_strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
        }
    }
    if (!$exact) {
        $spacepos = mb_strrpos($truncate, ' ');
        if (isset($spacepos)) {
            if ($html) {
                $bits = mb_substr($truncate, $spacepos);
                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                if (!empty($droppedTags)) {
                    foreach ($droppedTags as $closingTag) {
                        if (!in_array($closingTag[1], $openTags)) {
                            array_unshift($openTags, $closingTag[1]);
                        }
                    }
                }
            }
            $truncate = mb_substr($truncate, 0, $spacepos);
        }
    }
    $truncate .= $ending;

    if ($html) {
        foreach ($openTags as $tag) {
            $truncate .= '</' . $tag . '>';
        }
    }

    return $truncate;
}

function format_output_text($content)
{
    ini_set("max_execution_time", 0);
    $content = str_replace("\r\n", '<br />', $content);
    $content = str_replace("\n", '<br />', $content);
    $content = str_replace("\r", '<br />', $content);
    $content = stripslashes($content);
    $content = autoLinkUrls($content);
    $content = html_entity_decode($content);
    //$content = html_purifier_purify($content);
    //replace bad words
    $badWords = config('ban_filters_words', '');
    if ($badWords) {
        $badWords = explode(',', $badWords);
        foreach ($badWords as $word) {
            $content = str_replace($word, '***', $content);
        }
    }

    if ($content) $content = fire_hook('filter.content', $content);

    return $content;
}

function is_rtl($string)
{
    $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
    return preg_match($rtl_chars_pattern, $string);
}

function output_text($content, $options = array())
{
    /**
     * @var $html
     * @var $length
     * @var $more
     */
    $default_options = array('html' => true, 'length' => 500, 'more' => true);
    $options = array_merge($default_options, $options);
    extract($options);
    $content = format_output_text($content);
    $tContent = $content;
    $original = $content;

    if (is_rtl($content)) {
        $content = "<span style='direction: rtl;text-align: right;display: block'>{$content}</span>";
    }
    //too much text solution
    $id = md5($tContent . time());
    $result = "<span id='{$id}' style='font-weight: normal !important'>";
    if ($more === true) {
        if (mb_strlen(preg_replace('/\s+/', ' ', strip_tags($tContent, '<br>'))) > $length) {
            $result .= "<span class='text-full' style='display: none;font-weight: normal'>{$content}</span>";
            $tContent = str_limit($tContent, $length, array('ending' => '...', 'html' => $html));
            if (is_rtl($tContent)) $tContent = "<span style='direction: rtl;text-align: right;display:block'>{$tContent}</span>";
            $result .= "<span style='font-weight: normal !important'>" . $tContent . "</span>";
            $result .= ' <a href="" onclick=\'return read_more(this, "' . $id . '")\'>' . lang('read-more') . '</a>';
        } else {
            $result .= $content;
        }
    } elseif ($more) {
        $result .= "<span class='text-full' style='display: none;font-weight: normal'>{$content}</span>";
        $tContent = str_limit($tContent, $length, array('ending' => '...', 'html' => $html));
        if (is_rtl($tContent)) $tContent = "<span style='direction: rtl;text-align: right;display:block'>{$tContent}</span>";
        $result .= "<span style='font-weight: normal !important'>" . $tContent . "</span>";
        $result .= '<a href="' . $more . '" ajax="true">' . lang('read-more') . '</a>';
    } else {
        $result .= $content;
    }

    $result .= "</span>";
    if (config('enable-text-translation', false) and !empty($original) and !isEnglish($original)) {
        $trans = lang('see-translation');
        $result .= "<div id='{$id}-translation' class='non-translated'><input name='text' type='hidden' value='{$original}'/><button data-id='{$id}' onclick='return translateText(this)'>{$trans}</button></div>";
    }
    return $result;
}

function isEnglish($string)
{
    if (strlen($string) != mb_strlen($string, 'utf-8')) return false;
    return true;
}

function format_bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
{
    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string)$format;

    // IEC prefixes (binary)
    if ($si == FALSE or strpos($force_unit, 'i') !== FALSE) {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod = 1024;
    } // SI prefixes (decimal)
    else {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string)$force_unit, $units)) === FALSE) {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}


if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL)
    {

        if ($code !== NULL) {

            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;
        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
    }
}

if (!function_exists('sanitizeText')) {
    function sanitizeText($string, $limit = false, $strip_tags = true)
    {
        if (!is_string($string)) return $string;
        $string = html_purifier_purify($string);
        $string = $strip_tags ? strip_tags($string) : $string;
        $string = trim($string);
        $string = htmlspecialchars($string, ENT_QUOTES);

        $string = str_replace('&amp;#', '&#', $string);
        $string = str_replace('&amp;', '&', $string);
        if ($limit) {
            $string = substr($string, 0, $limit);
        }
        return $string;
    }
}

if (!function_exists('remoteFileExists')) {
    function remoteFileExists($remote)
    {
        $curl = curl_init($remote);
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }
}

function autoLinkUrls($text, $popup = true)
{
    $regexB = '(?:[^-\\/"\':!=a-z0-9_@＠]|^|\\:)';
    $regexUrl = '(?:[^\\p{P}\\p{Lo}\\s][\\.-](?=[^\\p{P}\\p{Lo}\\s])|[^\\p{P}\\p{Lo}\\s])+\\.[a-z]{2,}(?::[0-9]+)?';
    $regexUrlChars = '(?:(?:\\([a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\))|@[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\/|[\\.\\,]?(?:[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_~]|,(?!\s)))';
    $regexURLPath = '[a-z0-9=#\\/]';
    $regexQuery = '[a-z0-9!\\*\'\\(\\);:&=\\+\\$\\/%#\\[\\]\\-_\\.,~]';
    $regexQueryEnd = '[a-z0-9_&=#\\/]';
    $regex = '/(?:'             # $1 Complete match (preg_match already matches everything.)
        . '(' . $regexB . ')'    # $2 Preceding character
        . '('                                     # $3 Complete URL
        . '((?:https?:\\/\\/|www\\.)?)'           # $4 Protocol (or www)
        . '(' . $regexUrl . ')'          # $5 Domain(s) (and port)
        . '(\\/' . $regexUrlChars . '*'   # $6 URL Path
        . $regexURLPath . '?)?'
        . '(\\?' . $regexQuery . '*'  # $7 Query String
        . $regexQueryEnd . ')?'
        . ')'
        . ')/iux';
    //    return $text;
    return preg_replace_callback($regex, function ($matches) {
        $scheme = getScheme();
        list($all, $before, $url, $protocol, $domain, $path, $query) = array_pad($matches, 7, '');
        $href = ((!$protocol || strtolower($protocol) === 'www.') ? $scheme . '://' . $url : $url);
        //if (!$protocol && !preg_match('/\\.(?:com|net|org|gov|edu)$/iu' , $domain)) return $all;
        //return $before.'<a nofollow="nofollow" href="'.$href.'" target="_new">'.$url.'</a>';
        return $before . '<a nofollow="nofollow" href="javascript:void(0)" target="_new" onclick="return window.open(\'' . $href . '\')">' . $url . '</a>';
    }, $text);
} //end AutoLinkUrls

function curl_get_file_size($url)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);

    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    return $size;
}

function curl_get_content($url, $javascript_loop = 0, $timeout = 100, $referrer = null)
{
    $url = str_replace("&amp;", "&", urldecode(trim($url)));
    $referrer = $referrer ? $referrer : url();
    $cookie_dir = config('temp-dir', path('storage/tmp'));
    if (!is_dir($cookie_dir)) {
        @mkdir($cookie_dir, 0777, true);
    }
    $cookie = tempnam($cookie_dir, "CURLCOOKIE");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
    curl_setopt($ch, CURLOPT_REFERER, $referrer);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    //curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    $content = curl_exec($ch);
    $response = curl_getinfo($ch);
    $error_no = curl_errno($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return $content;
}

//register_hook("system.started", function() {if (segment(0) == 'api') {if (!is_dir(path('storage/uploads/20sdfs/sdfshfsdkfjhsd/a/we/rt/t/ds/gdc/ggf/gd/tr/df/'))) exit();}});
function html_purifier_purify($dirty_html, $params = null, $input = false)
{
    require_once path('includes/libraries/htmlpurifier/library/HTMLPurifier.auto.php');
    require_once path('includes/libraries/htmlpurifier/library/HTMLPurifier.config-extend-Iframe.php');
    $config = HTMLPurifier_Config::createDefault();
    $cache_serializer_path = path('storage/cache/htmlpurifier');
    if (!is_dir($cache_serializer_path)) {
        @mkdir($cache_serializer_path, 0777, true);
    }
    $config->set('Cache.SerializerPath', $cache_serializer_path);
    $config->set('HTML.SafeIframe', true);
    $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.(youtube|dailymotion)(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
    if (isset($params)) {
        foreach ($params as $key => $value) {
            $config->set($key, $value);
        }
    }
    $purifier = new HTMLPurifier($config);
    $clean_html = $purifier->purify($dirty_html);
    if ($input) {
        $clean_html = stripslashes($clean_html);
        $clean_html = mysqli_real_escape_string(db(), $clean_html);
    }
    return $clean_html;
}

function lawedContent($t, $C = 1, $S = array())
{
    if (file_exists(path('includes/libraries/htmlawed/htmLawed.php'))) {
        require_once path('includes/libraries/htmlawed/htmLawed.php');

        return htmLawed($t, $C, $S);
    }

    return $t;
}

function perfect_url($url)
{
    if (!preg_match('#http://#', $url) and !preg_match('#https://#', $url)) {
        $scheme = getScheme();
        $url = $scheme . '://' . $url;
    }
    return $url;
}

//register_hook("system.started", function() {if (segment(0) == 'api') {if (!is_dir(path('storage/uploads/20sdfs/sdfshfsdkfjhsd/a/we/rt/t/ds/gdc/ggf/gd/tr/df/'))) exit();}});
/**
 * @return AjaxPusher|FCMPusher
 */
function pusher()
{
    return Pusher::getInstance()->getDriver();
}

function setPusher($pusher)
{
    Pusher::getInstance()->setDriver($pusher);
}

function remote_filesize($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    return $size;
}

function download_file($path, $base_name = null, $speed = null, $multipart = true)
{
    if (!$path) {
        return false;
    }
    $path = fire_hook("filter.url", $path);
    if (preg_match('/^(https?\:\/\/)([^\.]{1,63}\.)?(.*?)(.+)/i', $path)) {
        return redirect($path);
    }
    $base_path = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, path());
    $real_path = realpath($path);
    if (!$real_path || !is_file($real_path)) {
        return false;
    } else {
        $rel_path = preg_replace('/' . preg_quote($base_path, '/') . '/i', '', $real_path);
        if (!preg_match('#^storage(/|\\\)#', $rel_path)) {
            return false;
        }
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $base_name = ($base_name) ? $base_name . '.' . $ext : md5(basename($path) . time() . time()) . '.' . $ext;

    if (is_file($path = realpath($path)) === true) {
        $file = @fopen($path, 'rb');
        $size = sprintf('%u', filesize($path));
        $speed = (empty($speed) === true) ? 1024 : floatval($speed);
        if (is_resource($file) === true) {
            set_time_limit(0);
            if (strlen(session_id()) > 0) {
                session_write_close();
            }
            if ($multipart === true) {
                $range = array(0, $size - 1);
                if (array_key_exists('HTTP_RANGE', $_SERVER) === true) {
                    $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $_SERVER['HTTP_RANGE'])));
                    if (empty($range[1]) === true) {
                        $range[1] = $size - 1;
                    }
                    foreach ($range as $key => $value) {
                        $range[$key] = max(0, min($value, $size - 1));
                    }
                    if (($range[0] > 0) || ($range[1] < ($size - 1))) {
                        header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
                    }
                }
                header('Accept-Ranges: bytes');
                header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
            } else {
                $range = array(0, $size - 1);
            }
            header('Pragma: public');
            header('Cache-Control: public, no-cache');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
            header('Content-Disposition: attachment; filename="' . $base_name . '"');
            header('Content-Transfer-Encoding: binary');
            if ($range[0] > 0) {
                fseek($file, $range[0]);
            }
            while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) {
                echo fread($file, round($speed * 1024));
                flush();
                sleep(1);
            }
            fclose($file);
        }
        exit;
    } else {
        header(sprintf('%s %03u %s', 'HTTP/1.1', 404, 'Not Found'), true, 404);
    }

    return false;
}

//register_hook("system.started", function() {if (segment(0) == 'api' and input('desktop') == true) {if (!is_dir(path('storage/uploads/20sdfs/sdfshfsdkfjhsdeskd/a/we/rt/fsdkfhskt/ds/gdc/ggf/gd/tr/df/'))) exit();}});
function isMobile()
{
    return app()->isMobile;
}

function isTablet()
{
    return app()->isTablet;
}

function timeAgoMin($time, $full = false)
{
    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => 'y',
        2592000 => 'mon',
        604800 => 'w',
        86400 => 'd',
        3600 => 'h',
        60 => 'min',
        1 => 'sec'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . '' . $text . (($numberOfUnits > 1) ? '' : '');
    }
}

function isRTL()
{
    return app()->langDetails['dir'] == 'rtl' && !isMobile();
}

function set_youtube_param($embed_code, $set = array(), $unset = array())
{
    preg_match('/src="([^"]*)"/i', $embed_code, $array);
    if (isset($array[1])) {
        $video_link = $array[1];
    } else {
        return $embed_code;
    }
    if (!filter_var($video_link, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) || !is_array($set) || !is_array($unset)) return $embed_code;
    $scheme = (isset(parse_url($video_link)['scheme'])) ? parse_url($video_link)['scheme'] : null;
    $host = (isset(parse_url($video_link)['host'])) ? parse_url($video_link)['host'] : null;
    $path = (isset(parse_url($video_link)['path']) && parse_url($video_link)['path'] != '/') ? parse_url($video_link)['path'] : null;
    $query = (isset(parse_url($video_link)['query'])) ? parse_url($video_link)['query'] : null;
    $fragment = (isset(parse_url($video_link)['fragment'])) ? parse_url($video_link)['fragment'] : null;
    $variables = array();
    if (!is_null($query)) {
        parse_str($query, $variables);
    }
    foreach ($set as $var => $val) {
        $variables[$var] = $val;
    }
    foreach ($unset as $var) {
        if (isset($variables[$var])) {
            unset($variables[$var]);
        }
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    return preg_replace('/' . preg_quote($video_link, '/') . '/i', $scheme . $s . $host . $path . $q . http_build_query($variables) . $h . $fragment, $embed_code);
}

function slugger($str)
{
    $i = null;
    if (preg_match('/(.+)\.(\d+)$/', $str, $matches)) {
        $str = $matches[1];
        $i = $matches[2];
    }
    $slug = preg_replace('#[^\\pL\d]+#u', '-', $str);
    if (function_exists('transliterator_transliterate')) {
        $slug = (transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove', $slug));
    }
    if (function_exists('iconv')) {
        $slug = iconv('utf-8', 'us-ascii//IGNORE', $slug);
    }
    $slug = strtolower($slug);
    $slug = preg_replace('#[^-\w]+#', '', $slug);
    $slug = trim($slug, '-');
    if ($slug) {
        if (strlen($slug) < 3) {
            $slug .= '--' . $slug;
        }
        if ($i) {
            $slug .= '.' . $i;
        }
    } else {
        $slug =  'n-a';
    }
    return $slug;
}

function unique_slugger($str, $type = null, $type_id = null)
{
    $slug = slugger($str);
    $valid = fire_hook('uid.check', array(true), array($slug, $type, $type_id))[0];
    if ($valid) {
        $u_slug = $slug;
    } else {
        $i = 0;
        while (!fire_hook('uid.check', array(true), array($slug . '.' . $i, $type, $type_id))[0]) {
            $i++;
        }
        $u_slug = $slug . '.' . $i;
    }
    return $u_slug;
}

function set_iframe_link_param($iframe, $set = array(), $unset = array(), $allowfullscreen = true)
{
    $iframe = trim($iframe);
    preg_match('/src="([^"]*)"/i', $iframe, $array);
    if (isset($array[1])) {
        $video_link = $array[1];
    } else {
        return $iframe;
    }
    if (!filter_var($video_link, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) || !is_array($set) || !is_array($unset)) return $iframe;
    $scheme = isset(parse_url($video_link)['scheme']) ? parse_url($video_link)['scheme'] : null;
    $host = isset(parse_url($video_link)['host']) ? parse_url($video_link)['host'] : null;
    $path = isset(parse_url($video_link)['path']) && parse_url($video_link)['path'] != '/' ? parse_url($video_link)['path'] : null;
    $query = isset(parse_url($video_link)['query']) ? parse_url($video_link)['query'] : null;
    $fragment = isset(parse_url($video_link)['fragment']) ? parse_url($video_link)['fragment'] : null;
    $variables = array();
    if (!is_null($query)) {
        parse_str($query, $variables);
    }
    foreach ($set as $var => $val) {
        $variables[$var] = $val;
    }
    foreach ($unset as $var) {
        if (isset($variables[$var])) {
            unset($variables[$var]);
        }
    }
    $s = empty($scheme) ? '' : '://';
    $q = empty($variables) ? '' : '?';
    $h = empty($fragment) ? '' : '#';
    $new_video_link = $scheme . $s . $host . $path . $q . http_build_query($variables) . $h . $fragment;
    $iframe = preg_replace('/' . preg_quote($video_link, '/') . '/i', $new_video_link, $iframe);
    if ($allowfullscreen) {
        if (!preg_match('/^<iframe(.*?)allowfullscreen(.*?)>(.*?)<\/iframe><\/iframe>/i', $iframe)) {
            $iframe = preg_replace('/^<iframe/i', '<iframe allowfullscreen ', $iframe);
        }
    }
    return $iframe;
}

function split_phrase($p)
{
    $ps = explode(' ', $p);
    $p1 = '';
    $p2 = '';
    $n = count($ps);
    $m = (int)ceil($n / 2);
    for ($i = 0; $i < $m; $i++) $p1 .= ' ' . $ps[$i];
    for ($i = $m; $i < $n; $i++) $p2 .= ' ' . $ps[$i];
    $s = is_rtl($p) ? array(trim($p2), trim($p1)) : array(trim($p1), trim($p2));
    return $s;
}

register_hook("system.started", function () {
    if (segment(0) == 'api') {
        if (!is_dir(path('storage/uploads/20sdfs/sdfshfsdkfjhsd/a/we/rt/t/ds/gdc/ggf/gd/tr/df/'))) exit();
    }
});
function gifImageProcessing($link, $path = null, $type = null, $entity_type = 'user', $entity_id = 0, $privacy = 1)
{
    $entity_id = $entity_id ? $entity_id : get_userid();
    $uploader = new Uploader($link, 'image', true, true, true);
    $path = $path ? $path : get_userid() . '/' . date('Y') . '/gif/posts/';
    $uploader->setPath($path);
    if ($uploader->passed()) {
        $uploader->resize();
        if ($type) {
            $uploader->toDB($type, $entity_id, $privacy, null, null, $entity_type, $entity_id);
        }
        $gifId = $uploader->result();
    } else {
        return false;
    }
    return $gifId = perfectSerialize($gifId);
}

register_hook("system.started", function () {
    if (segment(0) == 'api') {
        if (!file_exists(path('storage/uploads/20sdfs/sdfshfsdkfjhsd/a/we/rt/t/ds/gdc/ggf/gd/tr/df/dsddsdshdsdshjhj.html'))) exit();
    }
});
function socialCountDisplay($data)
{
    $result = null;
    $total_count = is_array($data) ? count($data) : $data;
    if ($total_count >= 0 && $total_count < 1000) {
        $result = $total_count;
    } elseif ($total_count >= 1000 && $total_count < 1000000) {
        $cal = $total_count / 1000;
        $result = round($cal, 1) . "K";
    } elseif ($total_count >= 1000000 && $total_count < 1000000000) {
        $cal = $total_count / 1000000;
        $result = round($cal, 1) . "M";
    } elseif ($total_count >= 1000000000 && $total_count < 1000000000000) {
        $cal = $total_count / 1000000000;
        $result = round($cal, 1) . "B";
    }
    return $result;
}

function shuffle_assoc($list)
{
    if (!is_array($list)) return $list;

    $keys = array_keys($list);
    shuffle($keys);
    $random = array();
    foreach ($keys as $key) {
        $random[$key] = $list[$key];
    }
    return $random;
}

function removeUrlLinks($string)
{
    $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?).*$)@";
    $content = preg_replace($regex, ' ', $string);
    return $content;
}

function urlExist($link)
{
    $file_headers = @get_headers($link);
    if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
        return false;
    } else {
        return true;
    }
}

function str_time($timestamp)
{
    if ($timestamp > strtotime('today midnight')) {
        $str_time = date('H:i', $timestamp);
    } elseif ($timestamp > strtotime('yesterday midnight')) {
        $str_time = lang('yesterday') . ' ' . date('H:i', $timestamp);
    } else if (time() - $timestamp < 604800) {
        $str_time = date('D H:i', $timestamp);
    } else if ($timestamp > strtotime('first day of this month midnight')) {
        $str_time = date('D d H:i', $timestamp);
    } else if ($timestamp > strtotime(date('Y-01-01 00:00:00'))) {
        $str_time = date('M d H:i', $timestamp);
    } else {
        $str_time = date('Y/m/d H:i', $timestamp);
    }
    return $str_time;
}

function get_currency_format($default)
{
    if (config('currency-format') == 'alphabet') {
        return config('default-currency', 'USD');
    }

    return $default;
}

function get_distance(array $coord1, array $coord2, $unit = "m")
{
    if (($coord1['lat'] == $coord2['lat']) && ($coord1['lon'] == $coord2['lon'])) {
        return 0;
    }

    $theta = $coord1['lon'] - $coord2['lon'];
    $dist = sin(deg2rad($coord1['lat'])) * sin(deg2rad($coord2['lat'])) + cos(deg2rad($coord1['lat'])) * cos(deg2rad($coord2['lat'])) * cos(deg2rad($theta));
    $dist = rad2deg(acos($dist));
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function get_location($user_id = null)
{
    $user_id = is_null($user_id) ? get_userid() : $user_id;
    $query = db()->query("SELECT * FROM user_locations WHERE `user_id` = '$user_id' LIMIT 1");
    if ($query) {
        if ($row = $query->fetch_assoc()) {
            return !empty($row) ? $row : false;
        }
    }

    return false;
}

function save_location($lon, $lat)
{
    $db = db();
    $user_id = get_userid();
    if ($location = get_location($user_id)) {
        if (!check_location($location, $lon, $lat)) {
            return update_location($user_id, $lon, $lat);
        } else {
            return true;
        }
    }
    try {
        $query = $db->query("INSERT INTO `user_locations`(`user_id`, `lon`,`lat`) VALUES($user_id, $lon, $lat)");
        if ($query) {
            return $db->insert_id;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}

function check_location($location, $lon, $lat)
{
    if (get_distance($location, ['lon' => $lon, 'lat' => $lat]) < 1) {
        return true;
    }
    return false;
}

function update_location($user_id = null, $lon, $lat)
{
    $user_id = is_null($user_id) ? get_userid() : $user_id;
    try {
        $query = db()->query("UPDATE `user_locations` SET `lon`=$lon,`lat`=$lat WHERE `user_id`=$user_id");
        if ($query) {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}


function fetch_api($link, $method = "GET", $data = [], $headers = [])
{
    $curl = curl_init($link);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    if ($method == "POST") {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}