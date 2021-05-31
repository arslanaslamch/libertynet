<?php

function offline_pager($app){
    $app->setTitle(lang("educational::download"));
    $id = input('id',null);
    $ed = Educational::getInstance();
    $book = $ed->getBook($id);
    if($book){
        return download_file($book[0]['book'],$book[0]['title']);
    }
    return redirect_back();
}

function books_pager($app)
{
    $app->setTitle(lang("educational::books"));
    $ed = Educational::getInstance();
    $term = input('term', null);
    $category = input('category', null);
    $type = input('type', 'all');
    if ($type == 'browse') $type = 'all';
    $genre = input('genre',null);
    $author = input('author',null);
    $filter = input('filter','all');
    $books = $ed->getBooks($type, $term, $category,$genre,$author,$filter);
    return $app->render(view("educational::home", array('books' => $books)));
}

function profile_pager($app){
    $app->setTitle(lang("educatonal::books"));
    $uid = $app->profileUser['id'];
    $ed = Educational::getInstance();
    $books = $ed->profileBooks($uid);
    return $app->render(view("educational::profile",array('books'=>$books)));
}
function edit_pager($app)
{
    $app->setTitle(lang("educational::edit"));
    $id = segment(1);
    $slug = segment(2);
    $ed = Educational::getInstance();
    $book = $ed->getBook($id, $slug);
    $action = input('action',null);
    $val = input('val');
    $message = null;
    if ($book) {
        $book = $book[0];
        if ($ed->isBookOwner($book)) {
            if($action == 'delete'){
                $ed->deleteBook($book);
                return redirect(url('books'));
            }

            if ($val) {
                $rules = array(
                    'title' => 'required',
                    'description' => 'required',
                    'category' => 'required',
                    'author' => 'required',
                );
                validator($val, $rules);
                if (validation_passes()) {
                    //book file
                    $book_file = $val['book'];
                    $book = input_file('book');
                    if ($book) {
                        $uploader = new Uploader($book, 'file');
                        if ($uploader->passed()) {
                            $uploader->setPath('books/files/');
                            $ext = $uploader->extension;
                            $allowed_ext = explode(',',config('books-extentions-allowed','pdf,docx,doc,epub'));
                            $allowed_ext[] = 'epub';
                            if (!in_array($ext,$allowed_ext)) {
                                $message = lang("educational::only-the-following-book-types-is-allowed").$allowed_ext;
                                return $app->render(view("educational::add", array('message' => $message)));
                            }
                            $book_file = $uploader->uploadFile()->result();
                            $val['book'] = $book_file;
                        } else {
                            $message = lang("educational::error-occured-uploading-books-try-again");
                            return $app->render(view("educational::edit", array('message' => $message)));
                        }
                    }

                    //when no book is uploaded, we go back
                    if (!$book_file) {
                        $message = lang("educational::no-book-file-choosen");
                        return $app->render(view("educational::edit", array('message' => $message)));
                    }
                    //let us now
                    $image = $val['photo'];
                    $file = input_file('photo');
                    if ($file) {
                        $uploader = new Uploader($file);
                        if ($uploader->passed()) {
                            $uploader->setPath('books/preview/');
                            $image = $uploader->resize(700, 500)->result();
                        }
                    }
                    $val['photo'] = $image;
                    $slug = toAscii($val['title']);
                    if(!$slug){
                        $slug = md5(time());
                    }
                    $val['slug'] = $slug;
                    //print_r($val);die();
                    $ed->saveBook($val, $id);
                    return redirect(url_to_pager("single-book", array('slug' => $slug, 'id' => $id)));
                } else {
                    $message = validation_first();
                    return $app->render(view("educational::edit", array('book' => $book, 'message' => $message)));
                }
            }
            return $app->render(view("educational::edit", array('book' => $book, 'message' => $message)));
        }
    }
    return redirect(url('books'));
}

function add_pager($app)
{
    $app->setTitle(lang("educational::add-new-book"));
    $val = input('val');
    $ed = Educational::getInstance();
    $message = null;
    if (is_loggedIn() and user_has_permission('can-create-books') and config('allow-members-to-create-books', true)) {
        //no problem
    } else {
        return redirect(url_to_pager("books-page"));
    }
    if ($val) {
        $rules = array(
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
            'author' => 'required',
        );
        validator($val, $rules);
        if (validation_passes()) {
            //book file
            $book_file = null;
            $book = input_file('book');
            if ($book) {
                $uploader = new Uploader($book, 'file');
                if ($uploader->passed()) {
                    $uploader->setPath('books/files/');
                    $ext = $uploader->extension;
                    $allowed_ext = explode(',',config('books-extentions-allowed','pdf,docx,doc,epub'));
                    $allowed_ext[] = 'epub';
                    if (!in_array($ext,$allowed_ext)) {
                        $message = lang("educational::only-the-following-book-types-is-allowed").config('books-extentions-allowed','pdf,docx,doc');
                        return $app->render(view("educational::add", array('message' => $message)));
                    }
                    $book_file = $uploader->uploadFile()->result();
                    $val['book'] = $book_file;
                } else {
                    echo $uploader->getError();die();
                    $message = lang("educational::error-occured-uploading-books-try-again");
                    return $app->render(view("educational::add", array('message' => $message)));
                }
            }

            //when no book is uploaded, we go back
            if (!$book_file) {
                $message = lang("educational::no-book-file-choosen");
                return $app->render(view("educational::add", array('message' => $message)));
            }
            //let us now
            $image = img("educational::images/default.jpg");
            $file = input_file('photo');
            if ($file) {
                $uploader = new Uploader($file);
                if ($uploader->passed()) {
                    $uploader->setPath('books/preview/');
                    $image = $uploader->resize(700, 500)->result();
                }
            }
            $val['photo'] = $image;
            $slug = toAscii($val['title']);
            if(!$slug){
                $slug = md5(time());
            }
            $val['slug'] = $slug;
            $id = $ed->saveBook($val, 'new');
            return redirect(url_to_pager("single-book", array('slug' => $slug, 'id' => $id)));
        } else {
            $message = validation_first();
            return $app->render(view("educational::add", array('message' => $message)));
        }
    }
    return $app->render(view("educational::add", array('message' => $message)));
}

function ajax_pager($app){
    $action = input('action',null);
    $ed = Educational::getInstance();
    switch($action){
        case 'dcount':
            $id = input('id');
            $ed->IncreaseDownloadCount($id);
            return true;
            break;
    }
    return true;
}

/*function read_pager($app)
{
    $id = segment(1);
    $slug = segment(2);
    $ed = Educational::getInstance();
    $book = $ed->getBook($id, $slug);
    if ($book) {
        $book = $book[0];
        if ($ed->canViewBook($book)) {
            $app->setTitle($book['title']);
            return $app->render(view("educational::read", array('book' => $book)));
        }
    }
    return redirect(url('books'));
}*/

function single_pager($app)
{
    $id = segment(1);
    $slug = segment(2);
    $ed = Educational::getInstance();
    $book = $ed->getBook($id, $slug);
    if ($book) {
        $book = $book[0];
        $app->setTitle($book['title'])->setDescription(str_limit(strip_tags($book['description']), 100));
        set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $book['title'], 'description' => str_limit(strip_tags($book['description']), 100), 'image' => $book['image'] ? url_img($book['image'], 200) : ''));

        if ($ed->canViewBook($book)) {
            $app->setTitle($book['title']);
            $ed->updateBookView($book['id']);
            $app->Book = $book;
            return $app->render(view("educational::single", array('book' => $book)));
        }
    }
    return redirect(url('books'));
}