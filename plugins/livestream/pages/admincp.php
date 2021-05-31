<?php
get_menu('admin-menu', 'plugins')->setActive();
get_menu('admin-menu', 'plugins')->findMenu('admin-livestream-manager')->setActive();

/**
 * Category list page
 * @param App $app
 * @return bool|string
 */
function category_list_page($app) {
    $title = lang('livestream::categories');
    $description = lang('livestream::category-list-desc');
    $categories = Livestream::getCategories();
    $app->setTitle($title);
    $links = array(
        array(
            'title' => lang('livestream::add-category'),
            'url' => url_to_pager('admin-livestream-category-add'),
            'type' => 'primary',
            'modal' => true
        )
    );
    $list = view('livestream::admincp/category/list', array('categories' => $categories));
    $content = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $list, 'links' => $links));
    return $app->render($content);
}

/**
 * Category add page
 * @param App $app
 * @return string|null
 */
function category_add_page($app) {
    $title = lang('livestream::add-category');
    $description = lang('livestream::category-add-desc');

    $result = array(
        'status' => 0,
        'title' => $title,
        'description' => $description,
        'message' => '',
        'data' => array(),
        'html' => '',
        'redirect_url' => ''
    );

    $category = input('category');
    $result['html'] = view('livestream::admincp/category/add', array('category' => $category));
    if($category) {
        validator($category, array(
            'title' => 'required',
            'parent_id' => 'required',
        ));
        if(validation_passes()) {
            $added = Livestream::addCategory($category);
            if($added) {
                $category = Livestream::getCategory($added);
                $result['status'] = 1;
                $result['message'] = lang('livestream::category-add-success');
                $result['html'] = view('livestream::admincp/category/get', array('category' => $category));
                $result['redirect_url'] = url_to_pager('admin-livestream-category-list');
            } else {
                $result['message'] = lang('livestream::category-add-error');
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
                'title' => lang('livestream::categories'),
                'url' => url_to_pager('admin-livestream-category-list'),
                'type' => 'secondary',
                'modal' => false
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Category edit page
 * @param App $app
 * @return bool|string
 */
function category_edit_page($app) {
    $title = lang('livestream::edit-categories');
    $description = lang('livestream::category-edit-desc');

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
    $category = Livestream::getCategory($id);
    $result['title'] = lang('livestream::category-edit', array('title' => $category['title']));
    $result['html'] = view('livestream::admincp/category/edit', array('category' => $category));
    if($id) {
        $category = input('category');
        if($category) {
            validator($category, array(
                'title' => 'required',
                'parent_id' => 'required'
            ));
            if(validation_passes()) {
                $saved = Livestream::editCategory($category);
                if ($saved) {
                    $category = Livestream::getCategory($id);
                    $result['status'] = 1;
                    $result['message'] = lang('livestream::category-edit-success');
                    $result['html'] = view('livestream::admincp/category/get', array('category' => $category));
                    $result['redirect_url'] = url_to_pager('admin-livestream-category-list');
                } else {
                    $result['message'] = lang('livestream::category-edit-error');
                }
            } else {
                $result['message'] = validation_first();
            }
        } else {
            $result['status'] = 1;
        }
    } else {
        $result['message'] = lang('livestream::category-edit-error');
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('livestream::categories'),
                'url' => url_to_pager('admin-livestream-category-list'),
                'type' => 'secondary',
                'modal' => false
            ),
            array(
                'title' => lang('livestream::add-category'),
                'url' => url_to_pager('admin-livestream-category-add'),
                'type' => 'info',
                'modal' => true
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Livestream Order Category Page
 * @param App $app
 * @return bool|string
 */
function category_order_page($app) {
    $ids = input('data');
    for($i = 0; $i < count($ids); $i++) {
        Livestream::orderCategory($ids[$i], $i);
    }
    return;
}

/**
 * Category delete page
 * @param App $app
 * @return bool|string
 */
function category_delete_page($app) {
    $title = lang('livestream::delete-category');
    $description = lang('livestream::category-delete-desc');

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
    $category = Livestream::getCategory($id);
    $result['title'] = lang('livestream::category-delete', array('title' => $category['title']));
    $result['html'] = view('livestream::admincp/category/delete', array('category' => $category));
    if ($confirm) {
        $deleted = Livestream::deleteCategory($id);
        if ($deleted) {
            $result['status'] = 1;
            $result['message'] = lang('livestream::category-delete-success');
            $result['redirect_url'] = url_to_pager('admin-livestream-category-list');
        } else {
            $result['message'] = lang('livestream::category-delete-error');
        }
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('livestream::categories'),
                'url' => url_to_pager('admin-livestream-category-list'),
                'type' => 'secondary',
                'modal' => false
            ),
            array(
                'title' => lang('livestream::add-category'),
                'url' => url_to_pager('admin-livestream-category-add'),
                'type' => 'info',
                'modal' => true
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Server list page
 * @param App $app
 * @return bool|string
 */
function server_list_page($app) {
    $title = lang('livestream::ice-servers');
    $description = lang('livestream::server-list-desc');
    $servers = Livestream::getICEServers();
    $app->setTitle($title);
    $links = array(
        array(
            'title' => lang('livestream::add-server'),
            'url' => url_to_pager('admin-livestream-server-add'),
            'type' => 'primary',
            'modal' => true
        )
    );
    $list = view('livestream::admincp/server/list', array('servers' => $servers));
    $content = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $list, 'links' => $links));
    return $app->render($content);
}

/**
 * Server add page
 * @param App $app
 * @return string|null
 */
function server_add_page($app) {
    $title = lang('livestream::add-server');
    $description = lang('livestream::server-add-desc');

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
    $result['html'] = view('livestream::admincp/server/add', array('server' => $server));
    if($server) {
        validator($server, array(
            'type' => 'required',
            'active' => 'required'
        ));
        if(validation_passes()) {
            $added = Livestream::addICEServer($server);
            if($added) {
                $server = Livestream::getICEServer($added);
                $result['status'] = 1;
                $result['message'] = lang('livestream::server-add-success');
                $result['html'] = view('livestream::admincp/server/get', array('server' => $server));
                $result['redirect_url'] = url_to_pager('admin-livestream-server-list');
            } else {
                $result['message'] = lang('livestream::server-add-error');
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
                'title' => lang('livestream::servers'),
                'url' => url_to_pager('admin-livestream-server-list'),
                'type' => 'secondary',
                'modal' => false
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Server edit page
 * @param App $app
 * @return bool|string
 */
function server_edit_page($app) {
    $title = lang('livestream::edit-servers');
    $description = lang('livestream::server-edit-desc');

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
    $server = Livestream::getICEServer($id);
    $result['title'] = lang('livestream::server-edit', array('title' => $server['url']));
    $result['html'] = view('livestream::admincp/server/edit', array('server' => $server));

    if($id) {
        $server = input('server');
        if($server) {
            validator($server, array(
                'type' => 'required',
                'active' => 'required'
            ));
            if(validation_passes()) {
                $saved = Livestream::editICEServer($id, $server);
                if ($saved) {
                    $server = Livestream::getICEServer($id);
                    $result['status'] = 1;
                    $result['message'] = lang('livestream::server-edit-success');
                    $result['html'] = view('livestream::admincp/server/get', array('server' => $server));
                    $result['redirect_url'] = url_to_pager('admin-livestream-server-list');
                } else {
                    $result['message'] = lang('livestream::server-edit-error');
                }
            } else {
                $result['message'] = validation_first();
            }
        } else {
            $result['status'] = 1;
        }
    } else {
        $result['message'] = lang('livestream::server-edit-error');
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('livestream::servers'),
                'url' => url_to_pager('admin-livestream-server-list'),
                'type' => 'secondary',
                'modal' => false
            ),
            array(
                'title' => lang('livestream::add-server'),
                'url' => url_to_pager('admin-livestream-server-add'),
                'type' => 'info',
                'modal' => true
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Server delete page
 * @param App $app
 * @return bool|string
 */
function server_delete_page($app) {
    $title = lang('livestream::delete-server');
    $description = lang('livestream::server-delete-desc');

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
    $server = Livestream::getICEServer($id);
    $result['title'] = lang('livestream::server-delete', array('title' => $server['url']));
    $result['html'] = view('livestream::admincp/server/delete', array('server' => $server));
    if ($confirm) {
        $deleted = Livestream::deleteICEServer($id);
        if ($deleted) {
            $result['status'] = 1;
            $result['message'] = lang('livestream::server-delete-success');
            $result['redirect_url'] = url_to_pager('admin-livestream-server-list');
        } else {
            $result['message'] = lang('livestream::server-delete-error');
        }
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('livestream::servers'),
                'url' => url_to_pager('admin-livestream-server-list'),
                'type' => 'secondary',
                'modal' => false
            ),
            array(
                'title' => lang('livestream::add-server'),
                'url' => url_to_pager('admin-livestream-server-add'),
                'type' => 'info',
                'modal' => true
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Livestream list page
 * @param App $app
 * @return bool|string
 */
function livestream_list_page($app) {
    $title = lang('livestream::livestreams');
    $description = lang('livestream::livestream-list-desc');
    $livestreams = Livestream::getLivestreams();
    $app->setTitle($title);
    $links = array();
    $list = view('livestream::admincp/livestream/list', array('livestreams' => $livestreams));
    $content = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $list, 'links' => $links));
    return $app->render($content);
}

/**
 * Livestream edit page
 * @param App $app
 * @return bool|string
 */
function livestream_edit_page($app) {
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

    $id = input('id');
    $livestream = Livestream::get($id);
    $result['title'] = lang('livestream::livestream-edit', array('title' => $livestream['title']));
    $result['html'] = view('livestream::admincp/livestream/edit', array('livestream' => $livestream));

    if($id) {
        $livestream = input('livestream');
        if($livestream) {
            validator($livestream, array(
                'title' => 'required',
                'active' => 'required'
            ));
            if(validation_passes()) {
                $saved = Livestream::edit($livestream);
                if ($saved) {
                    $livestream = Livestream::get($id);
                    $result['status'] = 1;
                    $result['message'] = lang('livestream::livestream-edit-success');
                    $result['html'] = view('livestream::admincp/livestream/get', array('livestream' => $livestream));
                    $result['redirect_url'] = url_to_pager('admin-livestream-list');
                } else {
                    $result['message'] = lang('livestream::livestream-edit-error');
                }
            } else {
                $result['message'] = validation_first();
            }
        } else {
            $result['status'] = 1;
        }
    } else {
        $result['message'] = lang('livestream::livestream-edit-error');
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('livestream::livestreams'),
                'url' => url_to_pager('admin-livestream-list'),
                'type' => 'secondary',
                'modal' => false
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Livestream delete page
 * @param App $app
 * @return bool|string
 */
function livestream_delete_page($app) {
    $title = lang('livestream::delete-livestream');
    $description = lang('livestream::livestream-delete-desc');

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
    $livestream = Livestream::get($id);
    $result['title'] = lang('livestream::livestream-delete', array('title' => $livestream['title']));
    $result['html'] = view('livestream::admincp/livestream/delete', array('livestream' => $livestream));
    if ($confirm) {
        $deleted = Livestream::delete($id);
        if ($deleted) {
            $result['status'] = 1;
            $result['message'] = lang('livestream::livestream-delete-success');
            $result['redirect_url'] = url_to_pager('admin-livestream-list');
        } else {
            $result['message'] = lang('livestream::livestream-delete-error');
        }
    }

    if(input('ajax')) {
        $response = json_encode($result);
        return $response;
    } else {
        $app->setTitle($title);
        $links = array(
            array(
                'title' => lang('livestream::livestreams'),
                'url' => url_to_pager('admin-livestream-list'),
                'type' => 'secondary',
                'modal' => false
            )
        );
        $result['html'] = view('livestream::admincp/layout/wrapper', array('title' => $title, 'description' => $description, 'content' => $result['html'], 'links' => $links));
        return $result['redirect_url'] ? redirect($result['redirect_url']) : $app->render($result['html']);
    }
}

/**
 * Livestream Admin Batch Actions Page
 * @param App $app
 * @return bool|string
 */
function livestream_action_batch_pager($app) {
    $action = input('action');
    $ids = explode(',', input('ids'));
    foreach($ids as $id) {
        switch($action) {
            case 'activate-server':
                Livestream::editICEServer($id, array('active' => 1));
            break;

            case 'deactivate-server':
                Livestream::editICEServer($id, array('active' => 0));
            break;

            case 'delete-server':
                Livestream::deleteICEServer($id);
            break;

            case 'delete-category':
                db()->query("DELETE FROM `livestream_categories` WHERE `id` = ".$id);
            break;

            case 'delete':
                Livestream::delete($id);
            break;
        }
    }
    return redirect_back();
}
