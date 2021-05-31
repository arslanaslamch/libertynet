<?php

function iframe_pager($app){
    $id = input('id');
    if($id){
        //echo $id;die();
        //echo $app->render();
        echo '<style>'.file_get_contents(img("donation::css/donation.css")).'</style>';
        echo view("donation::iframe/content",array('id'=>$id,'ifr'=>'yes'));
        return "";
    }
    return false;
}

function ajax_pager($app)
{
    $action = input('action');
    $don = Donation::getInstance();
    switch ($action) {
        case 'invite':
            $val = input('val');
            $id = input('id');
            $message = "";
            if ($val) {
                //let us check if there are friends and send them notification
                if(is_loggedIn()){
                    if ($val['friends']) {
                        $friends = $val['friends'];
                        $friends[] = get_userid();
                        foreach ($friends as $k => $uid) {
                            //send notification to reach users
                            send_notification_privacy('notify-site-tag-you', $uid, 'donation.invite', $id);
                        }
                        $message = lang("donation::notification-sent-successfull");
                    }
                }
                //let us check emails
                if ($val['emails']) {
                    $subject = sanitizeText($val['subject']);
                    $donation = $don->getSingle($id);
                    $donation = $donation[0];
                    $content = $don->emailTemplateReplace($val['message'], $donation, 'iv');
                    $emails = trim($val['emails']);
                    $scm = "";
                    $emails = explode(',',$emails);
                    foreach ($emails as $em) {
                        $em = trim($em);
                        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            //print_r("here");die();
                            mailer()->setAddress($em)->setSubject($subject)->setMessage($content)->send();
                            $scm = lang("donation::message-sent-successfully");
                        }
                    }
                    $message .= $scm;
                }
                $status = 1;
                if($message == ''){
                    $status = 0;
                    $message = lang("donation::no-invitation-sent");
                }
                return json_encode(array('message'=>$message,'status'=>$status));
            }
            break;
        case 'follow':
            if(!is_loggedIn()) return false;
            $s = input('status');
            $id = input('id');
            if($s){
                //na hin be say, we want to unfollow
                $don->deleteMyOldFollowing($id);
            }else{
                //zero, we want to follow
                $don->deleteMyOldFollowing($id);
                $don->newDonationFollower($id);
            }
            break;
        case 'search-friends':
            $term = sanitizeText(input('term'));
            $lists = $don->getFriendsLists($term);
            $view = view("donation::friends-lists", array('lists' => $lists));
            return json_encode(array('v' => $view));
            break;
        case 'all-friends':
            $friends = get_friends();
            $html = "";
            foreach ($friends as $u) {
                $user = find_user($u);
                if (!$user) continue;
                $uid = 'uid' . $u;
                $name = get_user_name($user);
                $html .= '<p class="cuser-line" id="' . $uid . '" ><input checked type="checkbox" name="val[friends][]" value="' . $u . '" />  ' . $name . '</p>';
            }
            $status = ($html) ? 1 : 0;
            $view = ($html) ? $html : lang("donation::no-friend-found");
            return json_encode(array('status' => $status, 'v' => $view));
            break;
    }
}

function more_fields_pager($app)
{
    $app->setTitle(lang("donation::update-donations"));
    $id = input('id');
    $t = input('t');
    $don = Donation::getInstance();
    $donation = $don->getSingle($id);
    if (!$donation) {
        MyError::error404();
        return '';
    }
    $donation = $donation[0];
    if ($donation['user_id'] != get_userid()) return redirect(url('donations'));
    $view = "";
    $message = "";
    $val = input('val');
    switch ($t) {
        case 'mi':
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
                    $don->saveDonation($val, $donation['id']);
                    $donation = $don->getSingle($id);
                    $donation = $donation[0];
                    $message = lang("donation::donation-saved-successfully");
                } else {
                    $message = validation_first();
                }
            }
            $view = view("donation::more-fields/mi", array('donation' => $donation, 'message' => $message));
            break;
        case 'pm':
            //payment
            if ($val) {
                CSRFProtection::validate();
                $don->updateDonateMore($donation, $val, 'pm');
                $message = lang("donation::saved-successfully");
                $donation = $don->getSingle($id);
                $donation = $donation[0];
            }
            $view = view("donation::more-fields/pm", array('donation' => $donation, 'message' => $message));
            break;
        case 'ci':
            //contact information
            $view = view("donation::more-fields/ci", array('donation' => $donation, 'message' => $message));
            break;
        case 'iv':
            //invitation
            if ($val) {
                //let us check if there are friends and send them notification
                if(is_loggedIn()){
                if ($val['friends']) {
                    $friends = $val['friends'];
                    //$friends[] = get_userid();
                    foreach ($friends as $k => $uid) {
                        //send notification to reach users
                        send_notification_privacy('notify-site-tag-you', $uid, 'donation.invite', $id);
                    }
                    $message = lang("donation::notification-sent-successfull");
                }
                }
                //let us check emails
                if ($val['emails']) {
                    $subject = sanitizeText($val['subject']);
                    $content = $don->emailTemplateReplace($val['message'], $donation, 'iv');
                    $emails = trim($val['emails']);
                    $scm = "";
                    $emails = explode(',',$emails);
                    foreach ($emails as $em) {
                        $em = trim($em);
                        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            //print_r("here");die();
                            mailer()->setAddress($em)->setSubject($subject)->setMessage($content)->send();
                            $scm = lang("donation::message-sent-successfully");
                        }
                    }
                    $message .= $scm;
                }
            }
            $email = $don->emailTemplateInvite();
            $view = view("donation::more-fields/iv", array('donation' => $donation, 'message' => $message, 'email' => $email));
            break;
        case 'ec':
            //terms and condition
            if ($val) {
                CSRFProtection::validate();
                $don->updateDonateMore($donation, $val, 'ec');
                $message = lang("donation::saved-successfully");
                $donation = $don->getSingle($id);
                $donation = $donation[0];
            }
            $view = view("donation::more-fields/ec", array('donation' => $donation, 'message' => $message));
            break;
        case 'ga':
            //gallery
            if ($val) {
                CSRFProtection::validate();
                $images = array();
                $imagesFile = input_file("photo");
                if($imagesFile){
                    $validate = new Uploader(null, 'photo', $imagesFile);
                    if ($validate->passed()) {
                        foreach ($imagesFile as $im) {
                            $uploader = new Uploader($im);
                            $uploader->setPath('donation/preview/'.$id.'/');
                            if ($uploader->passed()) {
                                $image = $uploader->noThumbnails()->resize()->result();
                                $images[] = $image;
                            } else {
                                $result['status'] = 0;
                                $result['message'] = $uploader->getError();
                                return json_encode($result);
                            }
                        }
                    } else {
                        $message = $validate->getError();
                        $view = view("donation::more-fields/ga", array('donation' => $donation, 'message' => $message));
                        return $app->render(view("donation::more-fields", array('donation' => $donation, 'view' => $view)));
                    }
                }
                $images = perfectSerialize($images);
                $val['images'] = $images;
                $don->updateDonateMore($donation, $val, 'ga');
                $message = lang("donation::saved-successfully");
                $donation = $don->getSingle($id);
                $donation = $donation[0];
            }
            $view = view("donation::more-fields/ga", array('donation' => $donation, 'message' => $message));
            break;

    }
    return $app->render(view("donation::more-fields", array('donation' => $donation, 'view' => $view)));
}

function home_pager($app)
{
    $app->setTitle(lang("donation::donations"));
    $donation = Donation::getInstance();
    $type = input('type', 'all');
    if ($type == 'browse') $type = 'all';
    $term = input('term', null);
    $category = input('category', null);
    $donations = $donation->getDonations($type, $term, $category);
    return $app->render(view("donation::home", array('donations' => $donations)));
}

function create_pager($app)
{
    $app->setTitle(lang("donation::create"));
    $message = null;

    $val = input('val');
    $don = Donation::getInstance();
    //check if user can add donation
    if (is_loggedIn() and user_has_permission('can-create-new-donation') and config('allow-members-create-donations', true)) {

    } else {
        return redirect(url('donations'));
    }
    if ($val) {
        $rules = array(
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
            'full_description' => 'required',
            'target_amount' => 'required',
        );
       // echo '<pre>', print_r($val) ,'</pre>';die();
        validator($val, $rules);
        if (validation_passes()) {
            //let us check expire time
            if (isset($val['unlimited'])) {
                //it means the time is unlimited
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
                    return $app->render(view("donation::create", array('message' => $message)));
                }
                //let us add our image
                //unix time reset
                $val['expire_time'] = $uxt;
            }

            //published
            $val['published'] = $val['save'] == 'pub' ? 1 : 0;
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
            $id = $don->saveDonation($val, 'new');
            return redirect(url_to_pager("more-fields") . '?t=pm&id=' . $id);
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view("donation::create", array('message' => $message)));
}


function single_pager($app)
{
    $app->setTitle(lang("donation::donation"));
    $id = segment(1);
    $don = Donation::getInstance();
    $donation = $don->getSingle($id);
    if (!$donation) {
        MyError::error404();
        return '';
    }
    $donation = $donation[0];
    app()->Donation = $donation;
    $don->updateDonationView($id);
    $app->setTitle($donation['title'])->setDescription(str_limit(strip_tags($donation['description']), 100));
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $donation['title'], 'description' => str_limit(strip_tags($donation['description']), 100), 'image' => $donation['image'] ? url_img($donation['image'], 200) : ''));

    if($donation['closed'] and get_userid() != $donation['user_id']){
        return $app->render(view("donation::closed"));
    }
    return $app->render(view("donation::new-single", array('donation' => $donation)));
}

function donate_me_pager($app)
{
    $app->setTitle(lang("donation::donation"));
    $id = segment(1);
    $don = Donation::getInstance();
    $donation = $don->getSingle($id);
    if (!$donation) {
        MyError::error404();
        return '';
    }
    $donation = $donation[0];
    if($don->canViewDonation($donation));
    //$don->updateDonationView($id);
    $app->setTitle($donation['title'])->setDescription(str_limit(strip_tags($donation['description']), 100));
    set_meta_tags(array('name' => get_setting("site_title", "crea8socialPRO"), 'title' => $donation['title'], 'description' => str_limit(strip_tags($donation['description']), 100), 'image' => $donation['image'] ? url_img($donation['image'], 200) : ''));
    return $app->render(view("donation::donate-me-now", array('donation' => $donation)));
}

function settings_pager($app)
{
    $app->setTitle(lang("donation::settings"));
    $don = Donation::getInstance();
    $message = null;
    //$settings =  $don->getSettings();
    $val = input('val');
    if ($val) {
        $sk = $val['sk'];
        $pk = $val['pk'];
        $pe = $val['pe'];
        $don->saveSettings($pe, $pk, $sk);
        $message = lang("donation::settings-saved-successfully");
    }
    $settings = $don->getSettings();
    return $app->render(view("donation::settings", array('s' => $settings, 'message' => $message)));
}

function paypal_verify_pager($app)
{
    if (isset($_POST['did'])) {
        require_once(path('includes/libraries/paypal_class.php'));
        $id = $_POST['did'];
        $amount = $_POST['amount'];
        $don = Donation::getInstance();
        $paypal = new \paypal_class();

        if ($paypal->validate_ipn()) {
            //that means user has successfully paid
            //Change their order status
            //fire_hook("payment.aff",null,array('', $invoice));
            return $don->addNewRaised($id, $amount);
        }
    }
    return false;
}

/*function verify_stripe_pager()
{
    //print_r($_POST);die();
    CSRFProtection::validate();
    $token = input('stripeToken');
    if (!$token) return redirect_back();
    $id = input('did');
    $don = Donation::getInstance();
    $donation = $don->getSingle($id);
    $donation = $donation[0];
    $settings = $don->getSettings($donation['user_id']);
    $amt = input('amt', 0);
    $don->addNewRaised($id, $amt);
    require_once(path('includes/libraries/stripe/lib/Stripe.php'));

    try {

        \Stripe::setApiKey($settings['secret_key']);
        \Stripe_Charge::create(array(
            'amount' => $amt * 100, // this is in cents: $20
            'currency' => config('default-currency'),
            'card' => $token,
            'description' => $donation['description']
        ));
        $don->addNewRaised($id, $amt);
        //echo "die";die();

    } catch (\Exception $e) {
        // Declined. Don't process their purchase.
        // Go back, and tell the user to try a new card
        //echo $e->getMessage();die();
        return redirect_back();
    }
}*/


function donate_now_pager($app)
{
    $app->setTitle(lang("donation::donate"));
    $val = input('val');
    $type = input('type', 'request');

    if(input('PayerID',null)){
        require_once(path('plugins/donation/lib/paypal.class.php'));
        $paypal = new DonationPayPal();
        //this is where will will collect information and token return by paypal
        $paypal->DoExpressCheckoutPayment();
        return null;
    }
    //$id = input('id', null);
    $don = Donation::getInstance();
    if ($val) {
        $amount = (float)$val['amount'];
        $method = ($val['token'] != '') ? 'stripe' : 'paypal';
        $did = sanitizeText($val['did']);
        $donation = $don->getSingle($did);
        $donation = $donation[0];
        fire_hook("donation.before.method",$val,array());
        switch ($method) {
            case 'paypal':
                switch ($type) {
                    case 'request':
                        if (!$donation['paypal_email']) return $app->render(view("donation::cannot-receive", array('m' => $method)));
                        $email = $donation['paypal_email'];
                        $rid = $don->addNewRaised($val, $donation,'0'); //rid is raised
                        //Business start
                        /*require_once(path('plugins/donation/lib/paypal.class.php'));

                        $paypal = new DonationPayPal();

                        $paypal->SetExpressCheckOut($amount,$email,$donation,$rid);*/

                        //Business End

                        //non business Email start
                        require_once(path('plugins/donation/lib/DonationPaypalDirect.php'));
                        $paypal = new \DonationPaypalDirect();
                        $paypal->admin_mail = $email; //store email or Admin Email
                        $paypal->add_field('business', $email);
                        $paypal->add_field('cmd', '_donations');
                        //$paypal->add_field('rm', '2');
                        $paypal->add_field('return', url_to_pager('donate-now') . '?action=paypal&type=success&rid='.$rid.'&did='.$did);
                        $paypal->add_field('cancel_return', url_to_pager('donate-now') . '?action=paypal&type=cancel&rid='.$rid.'&did='.$did);
                        $paypal->add_field('notify_url', url_to_pager('donate-paypal-notify'));
                        $paypal->add_field('currency_code', $donation['cur']);
                        $paypal->add_field('amount', $amount);
                        $paypal->add_field('item_name', $donation['title']);
                        $paypal->add_field('item_number', $donation['description']);
                        $paypal->add_field('did', $donation['id']);
                        $paypal->submit_paypal_post();
                        //non business email end
                        break;
                    case 'cancel':
                        //it won get here because the val variable is null
                        return $app->render(view('donation::paypal/cancel'));
                        break;
                    case 'success':
                        //it won get here because the val variable is null
                        //let us now update the
                        return $app->render(view('donation::paypal/success'));
                        break;
                }
                break;
            case 'stripe':
                $amt = $amount;
                $token = $val['token'];
                //$rid = $don->addNewRaised($val, $donation,'1');

                require_once(path('includes/libraries/stripe/lib/Stripe.php'));
                try {

                    \Stripe::setApiKey($donation['secret_key']);
                    \Stripe_Charge::create(array(
                        'amount' => $amt * 100, // this is in cents: $20
                        'currency' => config('default-currency'),
                        'card' => $token,
                        'description' => $donation['description']
                    ));
                    $rid = $don->addNewRaised($val, $donation,'1');
                    if(isset($val['show_feed'])){
                        //the owner want to show on feed, let us help him
                        $don->tofeed($rid);
                    }
                    $don->sendThankyouMail($val,$donation);
                    return redirect(url_to_pager("single_donation",array('id'=>$donation['id'])));

                } catch (\Exception $e) {
                    // Declined. Don't process their purchase.
                    // Go back, and tell the user to try a new card
                    //echo $e->getMessage();die();
                    return redirect(url_to_pager("single_donation",array('id'=>$donation['id'])));
                }
                break;
        }
    }

    if ($type == 'success') {
        $id = input('did',null);
        return $app->render(view("donation::paypal/success",array('did'=>$id)));
    }
    if ($type == 'pending') {
        $id = input('did',null);
        return $app->render(view("donation::paypal/pending",array('did'=>$id)));
    }
    if ($type == 'error') {
        //$id = input('did',null);
        return $app->render(view("donation::paypal/error"));
    }
    if ($type == 'cancel') {
        $id = input('did',null);
        return $app->render(view("donation::paypal/cancel",array('did'=>$id)));
    }
   // print_r($_GET);die();
    return redirect(url('donations'));
}

function manage_pager($app)
{
    $app->setTitle(lang("donation::manage"));
    $id = segment(1);
    $message = null;
    $don = Donation::getInstance();
    $val = input('val');
    $action = input('action', null);
    $donation = $don->getSingle($id);
    $donation = $donation[0];
    if (!$donation) return redirect(url('donations'));
    if (!$don->canManageDonation($donation)) {
        return redirect(url('donations'));
    }
    if ($action == 'delete') {
        $don->deleteDonation($donation['id']);
        return redirect(url('donations'));
    }
    if ($val) {
        $rules = array(
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
            'target_amount' => 'required',
        );
        validator($val, $rules);
        if (validation_passes()) {
            //let us check expire time
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
                return $app->render(view("donation::manage", array('message' => $message, 'donation' => $donation)));
            }
            //unix time reset
            $val['expire_time'] = $uxt;
            //let us add our image
            $image = $val['image'];
            $file = input_file('photo');
            if ($file) {
                $uploader = new Uploader($file);
                if ($uploader->passed()) {
                    $uploader->setPath('donation/preview/');
                    $image = $uploader->resize(700, 500)->result();
                }
            }
            $val['photo'] = $image;
            $don->saveDonation($val, $id);
            return redirect(url_to_pager("single_donation", array('id' => $id)));
        }
    }

    return $app->render(view("donation::manage", array('message' => $message, 'd' => $donation[0])));
}
