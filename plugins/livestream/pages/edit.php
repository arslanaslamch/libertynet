<?php
/**
 * Livestream Edit Page
 * @param App $app
 * @return bool|string
 */
function edit_page($app) {
    $title = lang('livestream::edit-livestream');
    $description = lang('livestream::livestream-edit-desc');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'data' => array(),
        'html' => '',
        'redirect_url' => ''
    );

    $livestream = input('livestream', array());
    $id = isset($livestream['id']) ? $livestream['id'] : input('id', 0);
    $livestream_current = Livestream::get($id);
    $categories = Livestream::getCategories();
    $result['html'] = view('livestream::livestream/edit', array('livestream' => $livestream_current, 'categories' => $categories, 'message' => $result['message']));
    if($livestream) {
        $val = input('val', array());
        $livestream = array_merge($livestream, $val);
        validator($livestream, array(
            'title' => 'required',
            'categories' => 'required'
        ));
        if(validation_passes()) {
            CSRFProtection::validate();
            $livestream['image'] = $livestream_current['image'];
            $image = input_file('image');
            if($image) {
                $uploader = new Uploader($image);
                if($uploader->passed()) {
                    $uploader->setPath('livestream/livestreams/images/');
                    $livestream['image'] = $uploader->resize()->result();
                } else {
                    $result['message'] = $uploader->getError();
                }
            }
            if(!$result['message']) {
                $livestream['livestream'] = $livestream_current;
                $saved = Livestream::edit($livestream);
                if($saved) {
                    $livestream = Livestream::get($livestream['id']);
                    $result['html'] = view('livestream::livestream/edit', array('livestream' => $livestream, 'categories' => $categories, 'message' => $result['message']));
                    $result['status'] = 1;
                    $result['message'] = lang('livestream::livestream-edit-success');
                    $result['redirect_url'] = url_to_pager('livestream-view', array('slug' => $livestream['slug']));
                } else {
                    $result['message'] = lang('livestream::livestream-edit-error');
                }
            }
        } else {
            $result['message'] = validation_first();
        }
    }
    if(input('ajax')) {
        $response = json_encode($result, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(array(
            'title' => lang('livestream::livestreams'),
            'url' => url_to_pager('livestream-list'),
            'type' => 'primary',
            'confirm' => false,
            'modal' => false,
            'ajax' => true
        ));
        $result['html'] = view('livestream::layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}