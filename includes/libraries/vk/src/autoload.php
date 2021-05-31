<?php
spl_autoload_register(static function($class_name) {
    $path = __DIR__.DIRECTORY_SEPARATOR.preg_replace('#\\\|/#', DIRECTORY_SEPARATOR, $class_name).'.php';
    include_once $path;
    if(file_exists($path) && !in_array($path, get_included_files(), FALSE)) {
        include_once $path;
    }
});