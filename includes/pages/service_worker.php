<?php

function service_worker($app) {
    header('Content-Type: application/javascript');
    $base_url = url();
    $logged_in = is_loggedIn() ? 1 : 0;
    $request_token = CSRFProtection::getToken();
    $push_driver = config('pusher-driver');
    $firebase_api_key = config('firebase-api-key-legacy');
    $firebase_auth_domain = config('firebase-auth-domain');
    $firebase_database_url = config('firebase-database-url');
    $firebase_project_id = config('firebase-project-id');
    $firebase_storage_bucket = config('firebase-storage-bucket');
    $firebase_messaging_sender_id = config('firebase-messaging-sender-id');
    $firebase_public_vapid_key = config('firebase-public-vapid-key');
    $ajax_interval = config('ajax-polling-interval', 5000);
    $script_tags = render_assets('js', 'service-worker');
    $asset_dir = 'storage/assets/js/';
    preg_match('/src=(\'|")([^(\'|")]*?)\/'.preg_quote($asset_dir, '/').'([^(\'|")]*?)(\'|")/', $script_tags, $matches);
    $asset_file = $matches[3];
    $script_path = path($asset_dir.$asset_file);
    $script = file_get_contents($script_path);

    $constants = <<<EOT
const baseUrl = '$base_url';
const loggedIn = $logged_in;
const requestToken = '$request_token';
const pushDriver = '$push_driver';
const ajaxInterval = $ajax_interval;
const firebaseAPIKey = '$firebase_api_key';
const firebaseAuthDomain = '$firebase_auth_domain';
const firebaseDatabaseURL = '$firebase_database_url';
const firebaseProjectId = '$firebase_project_id';
const firebaseStorageBucket = '$firebase_storage_bucket';
const firebaseMessagingSenderId = '$firebase_messaging_sender_id';
const firebasePublicVapidKey = '$firebase_public_vapid_key';
EOT;
    $constants = fire_hook('service-worker.js.constants', $constants);
    $script = $constants."\n".$script;
    return $script;
}