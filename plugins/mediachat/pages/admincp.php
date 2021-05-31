<?php
get_menu('admin-menu', 'plugins')->setActive();
get_menu('admin-menu', 'cms')->findMenu('admin-mediachat-server-list')->setActive();

/**
 * Server list page
 * @param App $app
 * @return bool|string
 */
function server_list_page($app) {
    $title = lang('mediachat::ice-servers');
    $description = lang('mediachat::server-list-desc');
    $servers = Mediachat::getICEServers();
    $app->setTitle($title);
    $links = array(
        array(
            'title' => lang('mediachat::add-server'),
            'url' => url_to_pager('admin-mediachat-server-add'),
            'type' => 'primary',
            'modal' => true
        )
    );
    $list = view('mediachat::admincp/server/list', array('servers' => $servers));
    $content = view('mediachat::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $list, 'links' => $links));
    return $app->render($content);
}

/**
 * Server add page
 * @param App $app
 * @return string|null
 */
function server_add_page($app) {
    $title = lang('mediachat::add-server');
    $description = lang('mediachat::server-add-desc');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'data' => array(),
        'html' => '',
        'redirect_url' => ''
    );

    $server = input('server');
    $result['html'] = view('mediachat::admincp/server/add', array('server' => $server));
    if($server) {
        validator($server, array(
            'type' => 'required',
            'active' => 'required'
        ));
        if(validation_passes()) {
            $added = Mediachat::addICEServer($server);
            if($added) {
                $server = Mediachat::getICEServer($added);
                $result['status'] = 1;
                $result['message'] = lang('mediachat::server-add-success');
                $result['html'] = view('mediachat::admincp/server/get', array('server' => $server));
                $result['redirect_url'] = url_to_pager('admin-mediachat-server-list');
            } else {
                $result['message'] = lang('mediachat::server-add-error');
            }
        } else {
            $result['message'] = validation_first();
        }
    }
    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('mediachat::servers'),
                'url' => url_to_pager('admin-mediachat-server-list'),
                'type' => 'secondary',
                'modal' => false
            )
        );
        $result['html'] = view('mediachat::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Server edit page
 * @param App $app
 * @return bool|string
 */
function server_edit_page($app) {
    $title = lang('mediachat::edit-servers');
    $description = lang('mediachat::server-edit-desc');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'data' => array(),
        'html' => '',
        'redirect_url' => ''
    );

    $id = input('id');
    $server = Mediachat::getICEServer($id);
    $result['title'] = lang('mediachat::server-edit', array('title' => $server['url']));
    $result['html'] = view('mediachat::admincp/server/edit', array('server' => $server));

    if($id) {
        $server = input('server');
        if($server) {
            validator($server, array(
                'type' => 'required',
                'active' => 'required'
            ));
            if(validation_passes()) {
                $saved = Mediachat::editICEServer($id, $server);
                if ($saved) {
                    $server = Mediachat::getICEServer($id);
                    $result['status'] = 1;
                    $result['message'] = lang('mediachat::server-edit-success');
                    $result['html'] = view('mediachat::admincp/server/get', array('server' => $server));
                    $result['redirect_url'] = url_to_pager('admin-mediachat-server-list');
                } else {
                    $result['message'] = lang('mediachat::server-edit-error');
                }
            } else {
                $result['message'] = validation_first();
            }
        } else {
            $result['status'] = 1;
        }
    } else {
        $result['message'] = lang('mediachat::server-edit-error');
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('mediachat::servers'),
                'url' => url_to_pager('admin-mediachat-server-list'),
                'type' => 'secondary',
                'modal' => false
            ),
            array(
                'title' => lang('mediachat::add-server'),
                'url' => url_to_pager('admin-mediachat-server-add'),
                'type' => 'info',
                'modal' => true
            )
        );
        $result['html'] = view('mediachat::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Server delete page
 * @param App $app
 * @return bool|string
 */
function server_delete_page($app) {
    $title = lang('mediachat::delete-server');
    $description = lang('mediachat::server-delete-desc');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'data' => array(),
        'html' => '',
        'redirect_url' => ''
    );

    $id = input('id');
    $confirm = input('confirm');
    $server = Mediachat::getICEServer($id);
    $result['title'] = lang('mediachat::server-delete', array('title' => $server['url']));
    $result['html'] = view('mediachat::admincp/server/delete', array('server' => $server));
    if ($confirm) {
        $deleted = Mediachat::deleteICEServer($id);
        if ($deleted) {
            $result['status'] = 1;
            $result['message'] = lang('mediachat::server-delete-success');
            $result['redirect_url'] = url_to_pager('admin-mediachat-server-list');
        } else {
            $result['message'] = lang('mediachat::server-delete-error');
        }
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('mediachat::servers'),
                'url' => url_to_pager('admin-mediachat-server-list'),
                'type' => 'secondary',
                'modal' => false
            ),
            array(
                'title' => lang('mediachat::add-server'),
                'url' => url_to_pager('admin-mediachat-server-add'),
                'type' => 'info',
                'modal' => true
            )
        );
        $result['html'] = view('mediachat::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

function mediachat_action_batch_pager($app) {
    $action = input('action');
    $ids = explode(',', input('ids'));
    foreach($ids as $id) {
        switch($action) {
            case 'activate-server':
                MediaChat::editICEServer($id, array('active' => 1));
            break;

            case 'deactivate-server':
                MediaChat::editICEServer($id, array('active' => 0));
            break;

            case 'delete-server':
                MediaChat::deleteICEServer($id);
            break;
        }
    }
    return redirect_back();
}