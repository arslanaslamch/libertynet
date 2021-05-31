<?php
//icon
register_hook("shortcut.menu.images", function ($arr) {
    $arr['ion-ios-book-outline'] = img("educational::images/books.png");
    return $arr;
});


load_functions('educational::educational');
register_hook("system.started", function ($app) {
    if ($app->themeType == 'frontend') {
        register_asset('educational::css/educational.css');
        //register_asset('educational::js/epub.min.js');
        register_asset('educational::js/educational.js');
    }
});

register_hook("role.permissions", function ($roles) {
    $roles[] = array(
        'title' => lang('educational::books-permissions'),
        'description' => '',
        'roles' => array(
            'can-create-books' => array('title' => lang('books::can-create-books'), 'value' => 1),
        )
    );
    return $roles;
});

register_hook("added.new.book",function($val,$id){
    $userid = get_userid();
    $privacy = $val['privacy'];
    $outcome =  add_feed(array(
        'user_id'=>$userid,
        'entity_id' => $userid,
        'entity_type' => 'user',
        'type' => 'feed',
        'type_id' => 'books',
        'type_data' => $id,
        'privacy' => $privacy,
        'auto_post' => true,
        'can_share' => false,
        'location' => 'books'
    ));
    /*$link = url_to_pager('single-book',array('id'=>$id,'slug'=>$val['slug']));
    $title = lang("educational::added-a-new-book");
    add_activity($link,$title);*/
});

register_hook('feed-title', function($feed) {
    if ($feed['type_id'] == "books") {
        $id = $feed['type_data'];
        $ed = Educational::getInstance();
        $book = $ed->getBook($id);
        if($book){
            $book = $book[0];
            $url = url_to_pager('single-book',array('id'=>$book['id'],'slug'=>$book['slug']));
            $str =  lang('educational::added-a-new');
            $str .= '<a  href="'.$url.'">';
            $str .= ' '.lang('educational::book').'</a>';
            echo $str;
        }
    }
});

register_hook("feed.arrange", function($feed) {
    if ($feed['location'] == 'books') {
        $feed['location'] = '';
        $ed = Educational::getInstance();
        $book = $ed->getBook($feed['type_data']);
        if (!$book) {
            $feedId = $feed['feed_id'];
            db()->query("DELETE FROM feeds WHERE feed_id='{$feedId}'");
            return false;
        };
        $feed['book'] = $book[0];
    }
    return $feed;
});

register_hook("feed.content", function($feed) {
    if ($feed['type_id'] == 'books') {
        if (isset($feed['book']) and $feed['book']) {
            $book = $feed['book'];
            echo  view('educational::feed_view',array('book' =>$book));

        }
    }
});

register_pager("{id}/books", array("use" => "educational::educational@profile_pager", "as" => "profile-books", 'filter' => 'profile'))
    ->where(array('id' => '[a-zA-Z0-9\_\-]+'));


register_hook('profile.started', function($user) {
    add_menu('user-profile-more', array('title' => lang('educational::books'), 'as' => 'books', 'link' => profile_url('books', $user)));
});
//notification start
register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'books') {
        $ed = Educational::getInstance();
        $book = $ed->getBook($typeId);
        $book = $book[0];
        $subscribers = get_subscribers($type, $typeId);
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-comment',$userid, 'books.comment', $typeId, $book, null, $text);
            }
        }
    }
});

register_hook("like.item", function($type, $typeId, $userid) {
    if ($type == 'books') {
        $ed = Educational::getInstance();
        $book = $ed->getBook($typeId);
        $book = $book[0];
        if ($book['user_id'] and $book['user_id'] != get_userid()) {
            send_notification_privacy('notify-site-like', $book['user_id'], 'books.like', $typeId, $book);
        }
    } elseif($type == 'comment') {
        $comment = find_comment($typeId, false);
        if ($comment and $comment['user_id'] != get_userid()) {
            if ($comment['type'] == 'books') {
                $ed = Educational::getInstance();
                $book = $ed->getBook($comment['type_id']);
                $book = $book[0];
                send_notification_privacy('notify-site-like', $comment['user_id'], 'books.like.comment', $comment['type_id'], $book);
            }
        }
    }
});

register_hook("comment.add", function($type, $typeId, $text) {
    if ($type == 'books') {
        $ed = Educational::getInstance();
        $book = $ed->getBook($typeId);
        $book = $book[0];
        $subscribers = get_subscribers($type, $typeId);
        if(!in_array($book['user_id'], $subscribers)) {
            $subscribers[] = $book['user_id'];
        }
        foreach($subscribers as $userid) {
            if ($userid != get_userid()) {
                send_notification_privacy('notify-site-comment',$userid, 'books.comment', $typeId, $book, null, $text);
            }
        }

    }
});

register_hook("display.notification", function($notification) {
    if ($notification['type'] == 'books.like') {
        return view("educational::notifications/like", array('notification' => $notification, 'book' => unserialize($notification['data'])));
        //delete_notification($notification['notification_id']);
    }
    elseif($notification['type'] == 'books.like.comment') {
        return view("educational::notifications/like-comment", array('notification' => $notification, 'book' => unserialize($notification['data'])));
        //delete_notification($notification['notification_id']);
    }
    elseif($notification['type'] == 'books.comment') {
        return view("educational::notifications/comment", array('notification' => $notification, 'book' => unserialize($notification['data'])));
        //delete_notification($notification['notification_id']);
    }
});

//notification end


register_pager("rating/books", array(
    'filter' => 'user-auth',
    'as' => 'rate-books',
    'use' => 'educational::rating@rating_index_pager'
));

register_pager("books/ajax", array(
    'filter' => 'user-auth',
    'as' => 'books-ajax',
    'use' => 'educational::educational@ajax_pager'
));
register_pager('books', array(
    'use' => 'educational::educational@books_pager',
    'as' => 'books-page',
    /*'filter' => 'auth'*/
));

register_pager('book/add', array(
    'use' => 'educational::educational@add_pager',
    'as' => 'add-books-page',
    'filter' => 'auth'
));

register_pager('book/offline', array(
    'use' => 'educational::educational@offline_pager',
    'as' => 'book-offline',
    'filter' => 'auth'
));

register_pager('book/{id}/{slug}', array(
    'use' => 'educational::educational@single_pager',
    'as' => 'single-book',
    /*'filter' => 'auth'*/
))->where(array('slug' => '[a-zA-Z0-9\-\_]+', 'id' => '[0-9]+'));

register_pager('book/{id}/{slug}/edit', array(
    'use' => 'educational::educational@edit_pager',
    'as' => 'edit-book',
    'filter' => 'auth'
))->where(array('slug' => '[a-zA-Z0-9\-\_]+', 'id' => '[0-9]+'));

/*register_pager('book/{id}/{slug}/read', array(
    'use' => 'educational::educational@read_pager',
    'as' => 'read-book',
    'filter' => 'auth'
))->where(array('slug' => '[a-zA-Z0-9\-\_]+','id'=>'[0-9]+'));;*/

add_available_menu('educational::e-library', 'books', 'ion-ios-book-outline');

register_pager("admincp/books/categories", array('use' => "educational::admincp@categories_pager", 'filter' => 'admin-auth', 'as' => 'admincp-educational-categories'));
register_pager("admincp/books/categories/add", array('use' => "educational::admincp@categories_add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-educational-categories-add'));
register_pager("admincp/books/category/manage", array('use' => "educational::admincp@manage_category_pager", 'filter' => 'admin-auth', 'as' => 'admincp-educational-manage-category'));

register_pager("admincp/books/manage", array(
        'use' => "educational::admincp@lists_pager",
        'filter' => 'admin-auth',
        'as' => 'admincp-books')
);

register_pager("admincp/book/add", array(
        'use' => "educational::admincp@add_book_admin_pager",
        'filter' => 'admin-auth',
        'as' => 'admincp-add-books')
);

register_hook("admin-started", function () {
    get_menu("admin-menu", "plugins")->addMenu(lang('educational::books-manager'), '#', 'admin-books');
    get_menu("admin-menu", "plugins")->findMenu('admin-books')->addMenu(lang('educational::manage-books'), url_to_pager("admincp-books"), "manage-books");
    get_menu("admin-menu", "plugins")->findMenu('admin-books')->addMenu(lang('educational::add-new-book'), url_to_pager("admincp-add-books"), "add-books");
    get_menu("admin-menu", "plugins")->findMenu('admin-books')->addMenu(lang('educational::manage-categories'), url_to_pager("admincp-educational-categories"), "categories");
});

register_hook('user.delete', function($userid) {
    $ed = Educational::getInstance();
     $d = db()->query("SELECT * FROM lh_books WHERE user_id='{$userid}'");
    while($book = $d->fetch_assoc()) {
        $ed->deleteBook($book);
    }

});