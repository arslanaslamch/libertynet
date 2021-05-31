<?php

get_menu("admin-menu", "plugins")->setActive();
get_menu("admin-menu", "plugins")->findMenu('admin-donations')->setActive();

function list_pager($app){
    $app->setTitle(lang("donation::donations"));
    $don = Donation::getInstance();
    $term = input('term',null);
    $donations = $don->getDonations('admincp',$term);
    return $app->render(view("donation::admincp/lists",array('donations'=>$donations)));
}
/*function add_pager($app){
    $app->setTitle(lang("donation::create"));
    $message = null;
    $val = input('val');
    $don = Donation::getInstance();
    if($val){
        $rules = array(
            'title'=>'required',
            'description'=>'required',
            'category'=>'required',
            'target_amount'=>'required',
        );
        validator($val,$rules);
        if(validation_passes()){
            //let us check expire time
            $et = $val['expire_time'];
            $uxt = $don->unixDonationTime($et);
            $bad_time = false;
            if($uxt){
                if(time() > $uxt){
                    $bad_time = true;
                }
            }else{
                $bad_time = true;
            }
            if($bad_time){
                $message = lang("donation::invalid-expire-time");
                return $app->render(view("donation::admincp/create",array('message'=>$message)));
            }
            //let us add our image
            //unix time reset
            $val['expire_time'] = $uxt;
            $image = img("donation::images/default.jpg");
            $file = input_file('photo');
            if ($file) {
                $uploader = new Uploader($file);
                if ($uploader->passed()) {
                    $uploader->setPath('donation/preview/');
                    $image = $uploader->resize(700, 500)->result();
                }
            }
            $val['photo'] = $image;
            $id = $don->saveDonation($val,'new');
            return redirect(url_to_pager("admincp-donation-lists"));
        }
    }
    return $app->render(view("donation::admincp/create",array('message'=>$message)));
}*/

/*function admincp_settings($app){
    $app->setTitle(lang("donation::settings"));
    $val = input('val');
    $don = Donation::getInstance();
    if($val){
        $sk = $val['sk'];
        $pk = $val['pk'];
        $pe = $val['pe'];
        $uid = $val['uid'];
        $don->saveSettings($pe,$pk,$sk,$uid);
        return redirect(url_to_pager("admincp-donation-lists").'?success=true');
    }
    return redirect_to_pager("admincp-donation-lists");
}*/

function manage_pager($app){
    $app->setTitle(lang("donation::manage-donation"));
    $id = input('id');
    if(!$id) return redirect_to_pager("admincp-donation-lists");
    $don = Donation::getInstance();
    $donation = $don->getSingle($id);
    $action = input("action");
    switch($action){
        case 'edit':
            $val = input('val');
            $message = null;
            if ($val) {
                $rules = array(
                    'title' => 'required',
                    'description' => 'required',
                    'category' => 'required',
                    'full_description' => 'required',
                    'target_amount' => 'required',
                );
                //echo '<pre>', print_r($val) ,'</pre>';die();
                validator($val, $rules);
                if (validation_passes()) {
                    //let us check expire time
                    if (isset($val['unlimited'])) {
                        //it means the time is unlimited
                        $val['expire_time'] = "";
                    } else {
                        $et = $val['expire_time'];
                        $uxt = $don->unixDonationTime($et);
                        $bad_time = false;
                        if ($uxt) {
                            if (time() > $uxt) {
                                $bad_time = true;
                            }
                        } else {
                            $bad_time = true;
                        }
                        if ($bad_time) {
                            $message = lang("donation::invalid-expire-time");
                        }
                        //let us add our image
                        //unix time reset
                        $val['expire_time'] = $uxt;
                    }

                    //published
                    $val['published'] = ($val['save'] == 'pub') ? 1 : 0;
                    $image = ($val['image']) ? $val['image'] : img("donation::images/default.jpg");
                    $file = input_file('photo');
                    if ($file) {
                        $uploader = new Uploader($file);
                        if ($uploader->passed()) {
                            $uploader->setPath('donation/preview/');
                            $image = $uploader->resize(700, 500)->result();
                        }
                    }
                    $val['photo'] = $image;
                    $don->saveDonation($val, $id,true);
                    $donation = $don->getSingle($id);
                    $donation = $donation[0];
                    $message = lang("donation::donation-saved-successfully");
                } else {
                    $message = validation_first();
                }
            }
            $donation = $don->getSingle($id);
            if(!$donation) return redirect_to_pager("admincp-donation-lists");
            return $app->render(view("donation::admincp/edit",array('donation'=>$donation[0],'message'=>$message)));
            break;
        case 'delete':
            $don->deleteDonation($id);
            return redirect_to_pager("admincp-donation-lists");
            break;
    }
    return redirect_to_pager("admincp-donation-lists");
}

function categories_pager($app) {
    $app->setTitle(lang('donation::manage-categories'));
    $donation = Donation::getInstance();
    return $app->render(view('donation::admincp/categories/lists', array('categories' => $donation->get_categories())));
}

function categories_add_pager($app) {
    $app->setTitle(lang('donation::add-category'));
    $message = null;

    $val = input('val');
    if ($val) {
        CSRFProtection::validate();
        $donation = Donation::getInstance();
        $donation->add_category($val);
        return redirect_to_pager('admincp-donations-categories');
    }

    return $app->render(view('donation::admincp/categories/add', array('message' => $message)));
}

function manage_category_pager($app) {
    $action = input('action', 'order');
    $id = input('id');
    $donation = Donation::getInstance();
    switch($action) {
        default:
            $ids = input('data');
            for($i = 0; $i < count($ids); $i++) {
                $donation->update_category_order($ids[$i], $i);
            }
            break;
        case 'edit':
            $message = null;
            $image = null;
            $val = input('val');
            $app->setTitle(lang('donation::edit-category'));
            $category = $donation->get_category($id);
            if (!$category) return redirect_to_pager('admincp-donations-categories');
            if ($val) {
                CSRFProtection::validate();
                $donation->save_category($val, $category);
                return redirect_to_pager('admincp-donations-categories');
                //redirect to category lists
            }
            return $app->render(view('donation::admincp/categories/edit', array('message' => $message, 'category' => $category)));
            break;
        case 'delete':
            $category = $donation->get_category($id);
            if (!$category) return redirect_to_pager('admincp-donations-categories');
            $donation->delete_category($id, $category);
            return redirect_to_pager('admincp-donations-categories');
            break;
    }
    return $app->render();
}