<?php

function upload($app) {
    header('Content-Type: text/json');
    $type = input('type', 'image');
    $privacy = input('privacy', 1);
    $status_key = input('status_key', 'status');
    $link_key = input('link_key', 'link');
    $message_key = input('message_key', 'message');
    $file_name = input('file_name', 'file');
    $file = input_file($file_name);
    $user_id = get_userid();
    $result = new stdClass();
    $result->$status_key = 0;
    $result->$link_key = '';
    $result->$message_key = '';
    if($file) {
        $uploader = new Uploader($file, $type);
        $uploader->setPath('editor/'.$user_id.'/'.date('Y/m/d'));
        if($uploader->passed()) {
            $uploader->toDB('editor-'.$type, $user_id, $privacy);
            $uploader->uploadFile();
            $link = url($uploader->result());
            $result->$status_key = 1;
            $result->$link_key = $link;
            $result->$message_key = lang('upload-successful');
        } else {
            $result->$message_key = $uploader->getError();
        }
    } else {
        $result->$message_key = lang('file-not-uploaded');
    }
    $response = json_encode($result, JSON_PRETTY_PRINT);
    return $response;
}