<?php

get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-birthdays')->setActive();

function post_template_pager($app){
    $app->setTitle(lang("birthday::post-template"));
    $val = input('val');
    $saved = false;
    $template = Birthday::getTemplate('post');
    if($val){
        $image = $val['image'];
        $file = input_file('image');
        if($file) {
            $uploader = new Uploader($file);
            if($uploader->passed()) {
                $uploader->setPath('birthday/templates/');
                $image = $uploader->resize(700, 500)->result();
            }
        }
        $content = sanitizeText($val['content']);
        $admin = $val['admin'];
        $type = 'post';
        Birthday::addTemplate($type,$content,$image,$admin);
        $saved = lang("birthday::saved-successfully");
    }
    $template = Birthday::getTemplate('post');
    return $app->render(view("birthday::admin/post-template",array('template'=>$template,'saved'=>$saved)));
}


function email_template_pager($app){
    $app->setTitle(lang("birthday::email-template"));
    $val = input('val');
    $saved = false;
    $template = Birthday::getTemplate('email');
    if($val){
        $image = $val['image'];
        $file = input_file('image');
        if($file) {
            $uploader = new Uploader($file);
            if($uploader->passed()) {
                $uploader->setPath('birthday/templates/');
                $image = $uploader->resize(700, 500)->result();
            }
        }
        $content = sanitizeText($val['content']);
        $admin = 0;
        $type = 'email';
        $subject = sanitizeText($val['subject']);
        Birthday::addTemplate($type,$content,$image,$admin,$subject);
        $saved = lang("birthday::saved-successfully");
    }
    $template = Birthday::getTemplate('email');
    return $app->render(view("birthday::admin/email-template",array('template'=>$template,'saved'=>$saved)));
}

function reminder_template_pager($app){
    $app->setTitle(lang("birthday::email-template"));
    $val = input('val');
    $saved = false;
    $template = Birthday::getTemplate('reminder');
    if($val){
        $image = $val['image'];
        $file = input_file('image');
        if($file) {
            $uploader = new Uploader($file);
            if($uploader->passed()) {
                $uploader->setPath('birthday/templates/');
                $image = $uploader->resize(700, 500)->result();
            }
        }
        $content = sanitizeText($val['content']);
        $admin = 0;
        $type = 'reminder';
        $subject = sanitizeText($val['subject']);
        Birthday::addTemplate($type,$content,$image,$admin,$subject);
        $saved = lang("birthday::saved-successfully");
    }
    $template = Birthday::getTemplate('reminder');
    return $app->render(view("birthday::admin/reminder-template",array('template'=>$template,'saved'=>$saved)));
}