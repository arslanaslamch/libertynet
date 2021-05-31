<?php

get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-books')->setActive();

function lists_pager($app){
    $app->setTitle(lang("educational::manage-books"));
    $action = input('action',null);
    $ed = Educational::getInstance();
    $message = null;
    switch($action){
        case 'edit':
            $id = input('id',0);
            $book = $ed->getBook($id);
            $val = input('val');
            //print_r($book);die();
            if($book){
                $book = $book[0];
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
                                $allowed_ext = explode(',',config('books-extentions-allowed','pdf,docx,doc'));
                                if (!in_array($ext,$allowed_ext)) {
                                    $message = lang("educational::only-the-following-book-types-is-allowed").$allowed_ext;
                                    return $app->render(view("educational::add", array('message' => $message)));
                                }
                                $book_file = $uploader->uploadFile()->result();
                                $val['book'] = $book_file;
                            } else {
                                $message = lang("educational::error-occured-uploading-books-try-again");
                                return $app->render(view("educational::admincp/edit", array('message' => $message)));
                            }
                        }

                        //when no book is uploaded, we go back
                        if (!$book_file) {
                            $message = lang("educational::no-book-file-choosen");
                            return $app->render(view("educational::admincp/edit", array('message' => $message)));
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
                        $val['slug'] = $slug;
                        //print_r($val);die();
                        $ed->saveBook($val, $id, true);
                        $book = $ed->getBook($id, $slug);

                        $message = lang("educational::book-saved-successfully");
                        return $app->render(view("educational::admincp/edit", array('book' => $book[0], 'message' => $message)));
                    } else {
                        $message = validation_first();
                        return $app->render(view("educational::admincp/edit", array('book' => $book, 'message' => $message)));
                    }
                }
                return $app->render(view("educational::admincp/edit",array('book'=>$book,'message'=>$message)));
            }
            break;
        case 'delete':
            $id = input('id',0);
            $book = $ed->getBook($id);
            $ed->deleteBook($book[0]);
            return redirect_to_pager("admincp-books");
            break;
    }
    $term = input('term',null);
    $books = $ed->getBooks('all',$term);
    return $app->render(view("educational::admincp/list",array('books'=>$books)));
}

function add_book_admin_pager($app){
    $app->setTitle(lang("add"));
    $message = null;
    $ed = Educational::getInstance();
    $val = input('val');
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
                    $allowed_ext = explode(',',config('books-extentions-allowed','pdf,docx,doc'));
                    if (!in_array($ext,$allowed_ext)) {
                        $message = lang("educational::only-the-following-book-types-is-allowed").$allowed_ext;
                        return $app->render(view("educational::add", array('message' => $message)));
                    }
                    $book_file = $uploader->uploadFile()->result();
                    $val['book'] = $book_file;
                } else {
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
            $val['slug'] = $slug;
            $id = $ed->saveBook($val, 'new',true);
            return redirect_to_pager("admincp-books");
        } else {
            $message = validation_first();
            return $app->render(view("educational::admincp/add", array('message' => $message)));
        }
    }
    return $app->render(view("educational::admincp/add",array('message'=>$message)));
}

function categories_pager($app) {
    $app->setTitle(lang('educational::manage-categories'));
    $edu = Educational::getInstance();
    return $app->render(view('educational::admincp/categories/lists', array('categories' => $edu->get_categories())));
}

function categories_add_pager($app) {
    $app->setTitle(lang('educational::add-category'));
    $message = null;
    $val = input('val');
    if ($val) {
        CSRFProtection::validate();
        $edu = Educational::getInstance();
        $edu->add_category($val);
        return redirect_to_pager('admincp-educational-categories');
    }
    return $app->render(view('educational::admincp/categories/add', array('message' => $message)));
}

function manage_category_pager($app) {
    $action = input('action', 'order');
    $id = input('id');
    $edu = Educational::getInstance();
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                $edu->update_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('educational::edit-category'));
            $category = $edu->get_category($id);
            if (!$category) return redirect_to_pager('admincp-educational-categories');
            if ($val) {
                CSRFProtection::validate();
                $edu->save_category($val, $category);
                return redirect_to_pager('admincp-educational-categories');
                //redirect to category lists
            }
            return $app->render(view('educational::admincp/categories/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = $edu->get_category($id);
            if (!$category) return redirect_to_pager('admincp-educational-categories');
            $edu->delete_category($id, $category);
            return redirect_to_pager('admincp-educational-categories');
            break;
    }
    return $app->render();
}