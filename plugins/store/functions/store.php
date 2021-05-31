<?php

function getProductCategoryText($product){
    if(strpos($product['category'],'::') !== false){
        $ar = explode('::',$product['category']);
        unset($ar[0]);
        if(count($ar) > 1){
            echo ucwords($ar[1]).' > '.ucwords($ar[2]);
        }else{
            echo $ar[1];
        }
    }else{
        echo ucwords($product['category']);
    }
}

function lp_store_categories($type_case='get_categories',$limit =10,$type='category'){
    switch($type_case){
        case 'get_categories':
                $sql = "SELECT * FROM `lp_store_categories` WHERE `parent_id`=0 AND type='{$type}' ORDER BY `cat_order`";
                return return_query_array($sql);
            break;
    }
};
function store_get_category_by_slug($slug){
    $sql = "SELECT * FROM `lp_store_categories` WHERE `slug`='{$slug}' OR id='{$slug}'";
    return return_query_array($sql);
}

function store_category_exists($slug) {
    $db = db()->query("SELECT id FROM lp_store_categories WHERE slug='{$slug}' LIMIT 1");
    if ($db and $db->num_rows > 0) return true;
    return false;
}

function update_store_category_order($id, $order,$type = 'category') {
    db()->query("UPDATE `lp_store_categories` SET `cat_order`='{$order}' WHERE `id`='{$id}' AND `type`='{$type}'");
}

function get_store_subcategories($parent_id){
    $sql = "SELECT * FROM `lp_store_categories` WHERE `parent_id`='{$parent_id}' ORDER BY `cat_order`";
    return return_query_array($sql);
}
function add_store_category($val,$type ='category'){

    /**
     * @var $title
     * @var $category
     */
    if($type == 'category'){
        extract($val);
        $titleSlug = 'store_category_'.md5(time().serialize($val)).'_title';
        $englishValue = $title['english'];
        foreach($title as $langId => $t) {
            if (!$t) $t = $englishValue;
            add_language_phrase($titleSlug, $t, $langId, 'Store');
        }
        $slug = toAscii($englishValue);


        if (empty($slug)) $slug = md5(time());
        if (store_category_exists($slug)) {
            $slug = md5($slug.time());
        }

    }else{
        //slides
        $slug = md5(time());
        $titleSlug = 'store_category_'.md5(time()).'_title';
        $category = 0;
    }

    $image = "";
    $file = input_file('image');
    if($file) {
        $uploader = new Uploader($file);
        if($uploader->passed()) {
            $uploader->setPath('store/categories/');
            $image = $uploader->resize(null, null)->result();
        }
    }else{
        if($type == 'slides'){
            return false;
            //we are returning false all slides are expected to have images
        }
    }
    db()->query("INSERT INTO lp_store_categories (title,parent_id,slug,image,type) VALUES(
    '{$titleSlug}','{$category}','{$slug}','{$image}','{$type}'
    )");
    $insertedId = db()->insert_id;
    fire_hook("added.category.store",null, array($insertedId,$type,$val));
    return $insertedId;
}

function save_store_category($val, $cat,$type = 'category') {
    /**
     * @var $title
     * @var $category
     */
    if($type == 'category'){
        extract($val);
        $englishValue = $title['english'];
        $slug = $cat['title'];
        foreach($title as $langId => $t) {
            if (!$t) $t = $englishValue;
            (phrase_exists($langId, $slug)) ? update_language_phrase($slug, $t, $langId, 'Store') : add_language_phrase($slug, $t, $langId, 'Store');
        }
    }else{
        //slides
        $slug = md5(time());
        $titleSlug = 'store_category_'.md5(time()).'_title';
        $category = 0;
    }
    $image = $cat['image'];
    $file = input_file('image');
    if($file) {
        $uploader = new Uploader($file);
        if($uploader->passed()) {
            $uploader->setPath('store/categories/');
            $image = $uploader->resize(null, null)->result();
        }
    }
    $categoryId = $cat['id'];

    db()->query("UPDATE lp_store_categories SET parent_id='{$category}',image='{$image}' WHERE id='{$categoryId}'");
    fire_hook("save.category.store",null,array($categoryId,$type,$val));
    return true;
}


function add_store_seller($val){
    /**
     * @var $name
     * @var $email
     * @var $phone
     * @var $address
     * @var $enable_paypal
     * @var $paypal_username
     * @var $paypal_password
     * @var $paypal_signature
     * @var $corporate_email
     * @var $cname
     * @var $location
     * @var $desc
     * @var $website
     * @var $slug
     */
    extract($val);
    $name = sanitizeText($name);
    $email = sanitizeText($email);
    $phone = sanitizeText($phone);
    $address = sanitizeText($address);
    $enable_paypal = '';
    $paypal_username = '';
    $paypal_signature = '';
    $paypal_password = '';

    $corporate_email = sanitizeText($corporate_email);
    $cname = sanitizeText($cname);
    $location = sanitizeText($location);
    $desc = sanitizeText($desc);
    $website = urlencode($website);

    ////////////////////////////////
    //saf cov pic start////////////
    /////////////////////////////

    $cov_img = '';
    $fi = input_file('cov_img');
    if ($fi) {
        $uploader = new Uploader($fi);
        if ($uploader->passed()) {
            $uploader->setPath('stores/cover/');
            $cov_img = $uploader->resize(700, 500)->result();
        }
    }
    ////////////////////////////////
    //saf cov pic end ////////////
    /////////////////////////////

    $time = time();
    ////////////////////////////////
    //saf slider pic start////////////
    /////////////////////////////
    $image = '';
    //slide customization begins
    $file = input_file('image');
    /* if ($file) {
         $uploader = new Uploader($file);
         if ($uploader->passed()) {
             $uploader->setPath('stores/cover/');
             $image = $uploader->resize(700, 500)->result();
         }
     }
    */
    //echo "we  here";die();
    //saf
    $images = '';
    if($file && isset($file[0]['size']) && ($file[0]['size'] != 0)) {
        //echo '<pre>', print_r($file),'</pre>';die();
        $images = array();
        //$validate = new Uploader($audio_files, 'audio');
        // if($validate->passed()) {
        foreach($file as $fil) {
            $uploader = new Uploader($fil);
            $path = get_userid().'/'.date('Y').'/stores/';
            $uploader->setPath($path);
            if($uploader->passed()) {
                $image = $uploader->uploadFile()->result();
                $images[] = $image;

            } else {
                $result['status'] = 0;
                $result['message'] = $uploader->getError();
                //return $result;
                return false;
            }
        }

        if(!empty($images)) {
            $images = perfectSerialize($images);
        }
    }
    //////////////////////////////////
    //saf slider end////////////
    /////////////////////////////
    //echo "were arer";die();
    $userid = get_userid();
    $sql = "INSERT INTO `lp_stores`(name,email,address,phone,enable_paypal,user_id,paypal_username,paypal_password,paypal_signature,cname,describ,location,website,image,cov_img,slug,time,status,featured,corporate_email)
     VALUES ('{$name}','{$email}','{$address}','{$phone}','{$enable_paypal}','{$userid}','{$paypal_username}','{$paypal_password}','{$paypal_signature}','{$cname}','{$desc}','{$location}','{$website}','{$images}','{$cov_img}','{$slug}','{$time}',0,0,'{$corporate_email}')";
    db()->query($sql);

    /*echo db()->error;
    die();*/
    $inserted_id = db()->insert_id;
    if(is_admin()){
        db()->query("UPDATE lp_stores SET status=1 WHERE s_id='{$inserted_id}'");
    }
    fire_hook('store_added',null,array($inserted_id));
    fire_hook("store.added.saf",$inserted_id,array($val));
    return true;
}

function save_store_seller($val,$fromAdmin=false){
    /**
     * @var $name
     * @var $email
     * @var $phone
     * @var $address
     * @var $enable_paypal
     * @var $paypal_username
     * @var $paypal_password
     * @var $paypal_signature
     * @var $cname
     * @var $location
     * @var $desc
     * @var $corporate_email
     * @var $website
     */
    extract($val);
    $store_id = $val['store_id'];
    $name = sanitizeText($name);
    $email = sanitizeText($email);
    $phone = sanitizeText($phone);
    $address = sanitizeText($address);
    $enable_paypal = '';
    $paypal_username = '';
    $paypal_password ='';
    $paypal_signature ='';
    $cname = sanitizeText($cname);
    $location = sanitizeText($location);
    $desc = sanitizeText($desc);
    $website = urlencode($website);
    $corporate_email = sanitizeText($corporate_email);

    ////////////////////////////////
    //saf cov pic start////////////
    /////////////////////////////
    $seller = lpGetStoreById(segment(1));
    $cov_img = $seller['cov_img'];

    $fi = input_file('cov_img');
    if ($fi) {
        $uploader = new Uploader($fi);
        if ($uploader->passed()) {
            $uploader->setPath('stores/cover/');
            $cov_img = $uploader->resize(700, 500)->result();

        }
    }
    ////////////////////////////////
    //saf cov pic end////////////
    /////////////////////////////

    ////////////////////////////////
    //saf sliderstart////////////
    /////////////////////////////

    $images = perfectUnserialize($seller['image']);
    $images = '' ? array(): perfectUnserialize($seller['image']);
    $file = input_file('image');

    if($file[0]['size'] != 0){
        $images = array();
        $validate = new Uploader(null, 'image', $file);

        if ($validate->passed()) {
            foreach($file as $fil) {
                $uploader = new Uploader($fil);
                $path = get_userid().'/'.'store/additional_photos/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $images[] = $uploader->resize(700, 500)->result();;
                }
            }
        }
    }
    $images = perfectSerialize($images);
    ////////////////////////////////
    //saf slider end////////////
    /////////////////////////////

    $userid = get_userid();
    $sql = "UPDATE `lp_stores` SET `corporate_email`='{$corporate_email}',`image`='{$images}', `cov_img`='{$cov_img}',`name`='{$name}',`email`='{$email}',`address`='{$address}',
    `phone`='{$phone}',`enable_paypal`='{$enable_paypal}',`paypal_username`='{$paypal_username}',
    `paypal_password`='{$paypal_password}',`paypal_signature`='{$paypal_signature}',website='{$website}',describ='{$desc}',cname='{$cname}',location='{$location}' WHERE `s_id`='{$store_id}'";
    db()->query($sql);

    if($fromAdmin){
        $status = $val['status'];
        $featured = $val['featured'];
        $verified = $val['verified'];
        db()->query("UPDATE lp_stores SET status='{$status}', featured='{$featured}',verified='{$verified}' WHERE s_id='{$store_id}'");
    }
    fire_hook("store.updated.saf",$store_id,array($val));
    forget_cache("current_user_store".$userid);
    return true;
}

function user_id_aleady_exists_in_seller_table(){
    $user_id = get_userid();
    $sql = "SELECT * FROM `lp_stores` WHERE `user_id`='{$user_id}'";
    $result = return_query_array($sql);
    if(empty($result)) return false;
    return true;
}

function get_seller($id,$type='by_user_id'){
    //$user_id = ($uid) ? $uid : get_userid();
   // $user_id = get_userid();
    if($type == 'by_user_id'){
        $sql = "SELECT * FROM `lp_stores` WHERE `user_id`='{$id}'";
    }
    if($type=='by_store_slug'){
        $sql = "SELECT * FROM `lp_stores` WHERE `slug`='{$id}'";
    }
    return return_query_array($sql);
}

function add_store_producer($val){
    /**
     * @var $name
     * @var $email
     * @var $phone
     * @var $address
     *
     */
    extract($val);
    $userid = get_userid();
    $sql = "INSERT INTO `lp_store_producer`(name,email,address,phone,user_id) VALUES('{$name}','{$email}','{$address}','{$phone}','{$userid}')";
    db()->query($sql);
    $inserted_id = db()->insert_id;
    fire_hook('producer_added',null,array($inserted_id));
    return true;
}


function save_store_producer($val){
    /**
     * @var $name
     * @var $email
     * @var $phone
     * @var $address
     * @var $pid
     */
    extract($val);
    $userid = get_userid();
    $sql = "UPDATE `lp_store_producer` SET `name`='{$name}',`email`='{$email}',`address`='{$address}',`phone`='{$phone}' WHERE `pid`='{$pid}'";
    db()->query($sql);
    $inserted_id = db()->insert_id;
    fire_hook('producer_added',null,array($inserted_id));
    return true;
}

function get_store_producer($user=null,$type='single'){
    $uid = ($user) ? $user : get_userid();
    $userid = get_userid();
    if($type=='all'){
        $sql = "SELECT * FROM `lp_store_producer` WHERE user_id='{$uid}'";
    }else{
        $sql = "SELECT * FROM `lp_store_producer` WHERE `user_id`='{$userid}'";
    }
    return return_query_array($sql);
}

function get_store_producer_by_id($pid){
    $userid = get_userid();
    $sql = "SELECT * FROM `lp_store_producer` WHERE `user_id`='{$userid}' AND `pid`='{$pid}'";
    return return_query_array($sql);
}

function removeStoreProducer($id,$userid=null){
    $uid = ($userid) ? $userid : get_userid();
    db()->query("DELETE FROM lp_store_producer WHERE `user_id`='{$uid}' AND pid='{$id}'");
    return true;
}

function own_store($uid=null){
    $userid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM lp_stores WHERE user_id='{$userid}'");

    if($q->num_rows > 0){
        return true;
    }
    return false;
}

function is_store_owner($seller){
    if($seller['user_id'] == get_userid()) return true;
    return fire_hook("assigned.check.more",'store',array($seller['s_id']));
    //return false;
}
//store slug exist
function storeSlugExist($slug){
    $sql = db()->query("SELECT * FROM lp_stores WHERE slug='{$slug}'");
    if($sql->num_rows > 0) return true;
    return false;
}

/**Product Functions*/

function productSlugExist($slug){
    $sql = db()->query("SELECT * FROM lp_products WHERE slug='{$slug}'");
    if($sql->num_rows > 0) return true;
    return false;
}

function save_product($val,$admin=false){
    $expected = array(
        'product_name'=>'',
        'description'=>'',
        'quantity'=>'',
        'tags'=>'',
        'type'=>'',
        'category'=>'',
        'price'=>'',
        'd_price'=>'',
        'end_day'=>'',
        'end_month'=>'',
        'end_year'=>'',
        'end_hour'=>'',
        'end_minute'=>'',
        'end_time_type'=>'',
        'labels'=>'',
        'id'=>'',
        'pay_on_delivery'=>'',
        'producer'=>'',
        'discount_duration'=>'',
        'store_id'=>'',
        'slug'=>''
    );
    extract(array_merge($expected,$val));
    /**
     *
     * @var $producer
     * @var $discount_duration
     * @var $pay_on_delivery
     *
     * @var $product_name
     * @var $description
     * @var $quantity
     * @var $category
     * @var $tags
     * @var $type
     * @var $price
     * @var $d_price
     * @var $end_day
     * @var $end_month
     * @var $end_year
     * @var $end_hour
     * @var $end_minute
     * @var $end_time_type
     * @var $label
     * @var $id
     * @var $slug
     * @var $store_id
     */
     if(is_numeric($id)){
         $product = getSingleProduct($id);
     }
    $product_name = sanitizeText($product_name);
    $description = sanitizeText($description);
    $tags = sanitizeText($tags);
    $type = sanitizeText($type);
    $price = sanitizeText($price);
    $d_price = sanitizeText($d_price);
    $e_date = "";
    if($d_price != ''){
        //it means the seller is giving a discount
        //check the discount timing
        try{
            $date_and_time = explode(' ',$discount_duration);
            $date = explode('/',$date_and_time[0]);
            $end_year = $date[0];
            $end_month = $date[1];
            $end_day = $date[2];
            $end_time = explode(':',$date_and_time[1]);
            $end_hour = $end_time[0];
            $end_minute = $end_time[0];
            $e_date = mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
        }catch (Exception $e){
            $e_date = mktime(0,0,0,12,20,2020);
        }
    }


    //main_photo
    $main_photo = ($id=='none') ? '': $product['main_photo'];
    $mi = input_file('main_image');
    if ($mi) {
        $uploader = new Uploader($mi);
        if ($uploader->passed()) {
            $path = get_userid().'/'."store/main_photo/";
            $uploader->setPath($path);
            $main_photo = $uploader->resize(700, 500)->result();
        }
    }

    //other images
    $additional_images = ($id == 'none') ? array() : perfectUnserialize($product['additional_images']);
    $imagesFile = input_file('add_images');

    if($imagesFile[0]['size'] != 0){
        $additional_images = array();
        $validate = new Uploader(null, 'image', $imagesFile);

        if ($validate->passed()) {
            foreach($imagesFile as $im) {
                $uploader = new Uploader($im);
                $path = get_userid().'/'.'store/additional_photos/';
                $uploader->setPath($path);
                if ($uploader->passed()) {
                    $additional_images[] = $uploader->resize(700, 500)->result();;
                }
            }
        }
    }
    /*print_r($additional_images);
    die();*/

    $additional_images = perfectSerialize($additional_images);
    //intangible product
    $f = input_file("product_file");

    $intangible_product = ($id == 'none') ? '' : $product['product_path'];
    if($f && isset($f[0]) and $f[0]['size'] !=0){
       // print_r($f); die();
        $validate = new Uploader(null, 'file', $f);

        if ($validate->passed()) {
            // print_r($f); die();
            foreach($f as $fi){
                $uploader = new Uploader($fi, 'file');
                $path = get_userid().'/store/files/';
                $root = $uploader->setPath($path)->uploadFile();
                $intangible_product = $root->result;
            }
        }
    }
    //echo '<pre>', print_r($intangible_product),'</pre>'; die();
    $labels = perfectSerialize($label);
    $time = time();
   //store_id

    $store = getCurrentUserStore();
    $user_id = get_userid();
    $store_id = ($id=='none') ? $store['s_id'] : $product['store_id'];
    /*print_r($val);
    die();*/

    if($id == "none"){
       $status = (!config('enable-admin-approve-product',1)) ? 1 : 0 ;
       $sql = "INSERT INTO lp_products(name,tags,product_path,type,quantity,price,discount_price,e_date,description,category,main_photo,labels,status,slug,time,additional_images,store_id,pay_on_delivery,producer,user_id)
        VALUES('{$product_name}','{$tags}','{$intangible_product}','{$type}','{$quantity}','{$price}','{$d_price}','{$e_date}','{$description}','{$category}','{$main_photo}','{$labels}','{$status}','{$slug}','{$time}','{$additional_images}','{$store_id}','{$pay_on_delivery}','{$producer}','{$user_id}')";
        db()->query($sql);
        $product_id = db()->insert_id;

    }else{
        //update
        $state  = (!config('enable-admin-approve-product',1)) ? 1 : 0 ;
        $product_id = $id;
        $sql= "UPDATE lp_products SET name='{$product_name}',tags='{$tags}',pay_on_delivery='{$pay_on_delivery}',producer='{$producer}',
        product_path='{$intangible_product}',type='{$type}',quantity='{$quantity}',price='{$price}',
        discount_price='{$d_price}',e_date='{$e_date}',description='{$description}',category='{$category}',main_photo='{$main_photo}',labels='{$labels}',additional_images='{$additional_images}' WHERE id='{$id}'";
        db()->query($sql);
        if(!$state){
            //prodoctus are auto approve
            db()->query("UPDATE lp_products SET status='1' WHERE id='{$product_id}'");
        }

        fire_hook("product.updated",null,array($product_id,$val));
    }

    if($admin){
        $status = $val['status'];
        $featured = $val['featured'];
        db()->query("UPDATE lp_products SET status='{$status}', featured='{$featured}' WHERE id='{$product_id}'");
    }

if($id == 'none'){

    //echo $product_id; die();
    $outcome =  add_feed(array(
        'user_id'=>$user_id,
        'entity_id' => $user_id,
        'entity_type' => 'user',
        'type' => 'feed',
        'type_id' => 'products',
        'type_data' => $product_id,
        'privacy' => 1,
        'auto_post' => true,
        'can_share' => false,
        'location' => 'product'
    ));
}

  //  fire_hook("job.added", null, array($jobId, $val));
    if($id == 'none'){
         fire_hook("product.added",null,array($product_id,$val));
    }
   
   return true;
}

function lp_get_products($type='any',$limit=10,$filter,$sort,$term,$category,$tag,$location=null){
    $subCategoriesArr = array();
    if(!$location){
        if($category ){
            $categorySlug = getMySlugFromMyName($category);

            $categoryResultArr = getCategoryParentIdFromSlug($categorySlug,'*');
            if($categoryResultArr['parent_id'] == 0){
                //this is parent, get its sub categories
                $sub_categories =  get_store_subcategories($categoryResultArr['id']);
                foreach($sub_categories as $key=>$value){
                    $subCategoriesArr[] =  lang($value['title']);
                }
                /*print_r($sub_categories);
                die();*/
            }
        }
    }
    //from the navigation/menu
    if($location){
        //category_id
        $category_ids = $location;
        $categoryArr = explode('::',$category_ids);
        if(count($categoryArr) > 1){
            //this is family
            $child = $categoryArr[0];
            $parent = $categoryArr[1];

            $sql = "SELECT * FROM lp_products WHERE category_id ='{$child}' AND category_parent_id='{$parent}'";
        }else{
            //this is single
            $child = $categoryArr[0];
            $sql = "SELECT * FROM lp_products WHERE category_id ='{$child}'";
        }

        if($term){
            $sql .= " AND name LIKE '%{$term}%' OR producer LIKE '%{$term}%'
            OR description LIKE '%{$term}%' OR category LIKE '%{$term}%' ";
            if($subCategoriesArr){
                foreach($subCategoriesArr as $sb){
                    $sql .=" OR category LIKE '%{$sb}%'";
                }
            }
            $sql .= "OR tags LIKE '%{$term}%'";
        }
        if ($filter == 'featured') $sql .= " AND featured = 1 ";
        if($sort == 'newest'){
            $sql .=" ORDER BY time DESC";
        }elseif($sort=="price_low_to_high"){
            $sql .=" ORDER BY price DESC";
        }elseif($sort=="price_high_to_low"){
            $sql .=" ORDER BY price ASC";
        }
        return paginate($sql,$limit);
    }
    $userid =get_userid();




    if($type== 'any'){
        $sql = "SELECT * FROM lp_products ORDER BY time DESC ";
    }
    if($type == 'mine'){
        $sql = "SELECT * FROM lp_products WHERE user_id='{$userid}'";
        if($term){
           $sql .= " AND name LIKE '%{$term}%' OR producer LIKE '%{$term}%'
            OR description LIKE '%{$term}%' OR category LIKE '%{$term}%' ";
            if($subCategoriesArr){
                foreach($subCategoriesArr as $sb){
                    $sql .=" OR category LIKE '%{$sb}%'";
                }
            }
            $sql .= "OR tags LIKE '%{$term}%'";
        }
        if ($filter == 'featured') $sql .= " AND featured = 1 ";
        if($sort == 'newest'){
            $sql .=" ORDER BY time DESC";
        }elseif($sort=="price_low_to_high"){
            $sql .=" ORDER BY price DESC";
        }elseif($sort=="price_high_to_low"){
            $sql .=" ORDER BY price ASC";
        }
        //db()->query($sql);

    }
    if($type == 'browse'){

        $sql = "SELECT * FROM lp_products";

        if($term){
            $sql .= " WHERE name LIKE '%{$term}%' OR producer LIKE '%{$term}%'
             OR description LIKE '%{$term}%' OR category LIKE '%{$term}%' OR tags LIKE '%{$term}%'" ;
            if($category){
                $sql .= " AND category='{$category}'";
                if($subCategoriesArr){
                    foreach($subCategoriesArr as $sb){
                        $sql .=" OR category LIKE '%{$sb}%'";
                    }
                }
            }
            if($tag){
                $sql .= " AND tags LIKE '%{tags}%'";
            }

            if($filter == 'featured'){
                $sql .=" AND featured=1";
            }
            if(config('enable-admin-approve-product',false)){
                 $sql .=" AND status =1 ";
            }
            if($sort =='newest'){
                $sql .=" ORDER BY time DESC";
            }elseif($sort == "price_low_to_high"){
                $sql .=" ORDER BY price DESC";
            }elseif($sort=="price_high_to_low"){
                $sql .=" ORDER BY price ASC";
            }

            if($filter == 'top'){
                $sql  =" ,views DESC ";
            }
        }
        if(!$term){
            //filter,category,feature
            if($filter == 'featured' && $category != '' && $tag){
                $sql .=" WHERE featured = 1 AND category='{$category}' AND tags LIKE '%{$tag}%'";
                if($subCategoriesArr){
                    foreach($subCategoriesArr as $sb){
                        $sql .=" OR category LIKE '%{$sb}%'";
                    }
                }
            }
            if($filter == 'any' && $category != '' && $tag){
                $sql .= " category='{$category}' AND tags LIKE '%{$tag}%'";
                if($subCategoriesArr){
                    foreach($subCategoriesArr as $sb){
                        $sql .=" OR category LIKE '%{$sb}%'";
                    }
                }
            }
            if($filter == 'featured' && $category=='' && $tag){
                $sql .= " WHERE featured=1 AND tags LIKE '%{$tag}%'";
            }

            if($filter == 'featured' && !$category && !$tag){
                $sql .=' WHERE featured=1';
            }
            if($filter== 'all' && $category =='' && $tag){
                $sql .= " WHERE tags LIKE '%{$tag}%'";
            }
            if($filter== 'all' && $category !='' && !$tag){
                $sql .= " WHERE category='{$category}'";
                if($subCategoriesArr){
                    foreach($subCategoriesArr as $sb){
                        $sql .=" OR category LIKE '%{$sb}%'";
                    }
                }
            }
            if($filter == 'featured' && $category !='' && !$tag){
                $sql .= " WHERE category='{$category}' AND featured=1";
            }
            if($filter == 'all' && !$category && !$tag){
                if(config('enable-admin-approve-product',false)){
                $sql .=" WHERE status =1";
                }
            }else{
                if(config('enable-admin-approve-product',false)){
                    $sql .=" AND status =1";
                }
            }

            if($sort =='newest'){
                $sql .=" ORDER BY time DESC";
            }elseif($sort=="price_low_to_high"){
                $sql .=" ORDER BY price DESC";
            }elseif($sort=="price_high_to_low"){
                $sql .=" ORDER BY price ASC";
            }
            elseif($filter == 'top' && $sort){
                $sql  .=" ,views DESC ";
            }else{
                $sql .= " ORDER BY views";
            }


        }

    }

   /* echo $sql;
   die();*/
    return paginate($sql,$limit);
}

function getSingleProduct($id,$type='any'){
    if($type=='approved'){
        $sql = db()->query("SELECT * FROM lp_products WHERE status=1 AND (id='{$id}' OR slug='{$id})'");
    }
    if($type == 'any'){
        $sql = db()->query("SELECT * FROM lp_products WHERE id='{$id}' OR slug='{$id}'");
    }

    if($sql->num_rows > 0){
        return $sql->fetch_assoc();
    }
    return  array(
        'name'=>'',
        'description'=>'',
        'quantity'=>'',
        'tags'=>'',
        'type'=>'',
        'category'=>'',
        'price'=>0,
        'd_price'=>'',
        'end_day'=>'',
        'end_month'=>'',
        'end_year'=>'',
        'end_hour'=>'',
        'end_minute'=>'',
        'end_time_type'=>'',
        'labels'=>'',
        'id'=>'',
        'pay_on_delivery'=>'',
        'producer'=>'',
        'discount_duration'=>'',
        'store_id'=>'',
        'slug'=>''
    );
   // return false;

}
//get all stores
function lp_get_stores($filter =null,$term=null,$type = null){
    $sql = "SELECT * FROM lp_stores WHERE name != '' ";
    if($term){
        $sql .=" AND name LIKE '%{$term}%' OR address LIKE '%{$term}%' OR email LIKE '%{$term}%' OR website LIKE '%{$term}%'";
    }

    if($type == 'mine' AND is_loggedIn()){
        $uid = get_userid();
        $sql .=" AND user_id='{$uid}'";
    }

    if($filter == 'featured'){
        $sql .= " AND featured=1";
    }
    if($filter == 'latest'){
        $sql .= " ORDER BY time";
    }

    return paginate($sql,10 );
}

function lpGetStoreById($id,$param=null){
    $sql = "SELECT * FROM lp_stores WHERE s_id='{$id}' OR slug='{$id}'";
   $result =  return_query_array($sql);
    if($result){
        if($param){
            return $result[0][$param];
        }
        return $result[0];
    }else{
        return false;
    }

}

/*function getStoreLogo($store,$size = 200){
    $image = ($store['image']) ? url_img($store['image']) : img("store::images/comingsoon.png");
    $image = str_replace('%w', $size, $image);
    return $image;

}*/
//customization too
////////////////////////////////
//saf home images start////////////
/////////////////////////////
function getStoreLogo($store,$size = 200){
    $image = ($store['image']) ? url_img($store['image']) : img("store::images/comingsoon.png");
    $image = $store['cov_img'] ? $store['cov_img'] : (perfectUnserialize($image) ? perfectUnserialize($image)[0] : $image);
    $image = str_replace('%w', $size, $image);
    $url = url($image);
    $url = fire_hook('filter.twice.url', $url,array($image));
    return $url;
}
////////////////////////////////
//saf homepage images end////////////
/////////////////////////////
///

function getProductMainImage($product){
    $image = ($product['main_photo']) ? url_img($product['main_photo']) : img("store::images/commingsoon.png");
    return $image;

}

//check if the current user own this
function is_seller($userid=null){
    $uid = ($userid) ? $userid : get_userid();
    $sql = db()->query("SELECT * FROM lp_stores WHERE user_id='{$uid}'");
    if($sql->num_rows > 0 ) return $sql->fetch_assoc();
    return false;

}

//

function getMyStoreProducts($id,$limit=3, $useUserId= false){
    $sql = "SELECT * FROM lp_products WHERE store_id='{$id}'";
    if($useUserId){
        $uid = get_userid();
        $sql = "SELECT * FROM lp_products WHERE user_id='{$uid}'";
    }
    return paginate($sql,$limit);
}

function isFollowingStore($store_id){
    $userid = get_userid();
   //try to select the
    $sql = db()->query("SELECT * FROM lp_store_followers WHERE store_id='{$store_id}' AND user_id='{$userid}' AND status=1");
    if($sql->num_rows > 0) return true;
    return false;
}

function hasUnfollowedStore($store_id){
    $userid = get_userid();
    //try to select the
    $sql = db()->query("SELECT * FROM lp_store_followers WHERE store_id='{$store_id}' AND user_id='{$userid}' AND status=0");
    if($sql->num_rows > 0) return true;
    return false;
}

function getCurrentUserStore($userid=null){
    $uid = ($userid)? $userid :  get_userid();
    /*if(cache_exists('current_user_store'.$uid)){
        return get_cache('current_user_store');
    }*/
    $response = db()->query("SELECT * FROM lp_stores WHERE user_id='{$uid}'");
    if($response->num_rows > 0){
        $result = $response->fetch_assoc();
       // set_cacheForever('current_user_store'.$uid,$result);
        return $result;
    }
    return false;
}
function store_get_month_name($month) {
    $months = array(
        'january'=>1,
        'february'=>2,
        'march'=>3,
        'april'=>4,
        'may'=>5,
         'june'=>6,
         'july'=>7,
       'august'=>8,
        'september'=>9,
       'october'=>10,
         'november'=>11,
         'december'=>12
    );
    return $months[$month];
}
function getStoreOrders($limit=10,$type='all',$uid=null, $storeId = null){
    $userid =($uid) ? $uid :  get_userid();
    $month = (input('month')) ? store_get_month_name(input('month')) : input('month',date('n'));
    $year = (input('year')) ? input('year') : input('year',date('Y'));;
    $time = mktime(0,0,0,$month,1,$year);

    switch($type){
        case 'all' :{
            if($storeId){
                $sql = "SELECT * FROM lp_store_order WHERE store_owner='{$userid}' AND store_id='{$storeId}' ORDER BY time DESC";
            }else{
                $sql = "SELECT * FROM lp_store_order WHERE store_owner='{$userid}' ORDER BY time DESC";
            }

        }
            break;
        case 's' :{
            //s is successful
            if($storeId){
                $sql = "SELECT * FROM lp_store_order WHERE store_owner='{$userid}' AND store_id='{$storeId}' status=1 AND time > '{$time}' ORDER BY time DESC";
            }else{
                $sql = "SELECT * FROM lp_store_order WHERE store_owner='{$userid}' AND status=1 AND time > '{$time}' ORDER BY time DESC";
            }
        }
    }
    return paginate($sql,$limit);
}


function getSingleStoreOrder($id,$uid=null){
    $userid = ($uid) ? $uid : get_userid();
    $sql = db()->query("SELECT * FROM lp_store_order WHERE store_owner='{$userid}' AND id='{$id}'");
    if($sql->num_rows > 0){
        return $sql->fetch_assoc();
    }
    return false;
}

function countStoreTotalProducts($sid){
   $query = db()->query("SELECT * FROM lp_products WHERE store_id='{$sid}'");
    if($query->num_rows > 0){
        return $query->num_rows;
    }else{
        return 0;
    }

}

function countNumberOfSoldItems($productId){
    $query = db()->query("SELECT * FROM lp_store_order WHERE product_id='{$productId}' AND status=1");
    return $query->num_rows;
}

function getStoreTotalSalesAmount($sid){
   $sum = db()->query("SELECT (SUM(sub_total) + SUM(shipping_price)) as amt FROM lp_store_order WHERE store_id='{$sid}' AND status=1 AND emoney=0 ");
    //echo db()->error; die();
   if($r = $sum->fetch_assoc()['amt']){
       //echo "yes";die();
         return $r;
   }
    return 0;

}

function getStoreTotalWithdrawn($sid){
    $sum = db()->query("SELECT SUM(amount) as amt FROM lp_store_withdrawal WHERE store_id='{$sid}' AND status=1");
    if($r = $sum->fetch_assoc()['amt']) return $r;
    return 0;
}

function getStoreTotalPayout($sid){
    $sum = db()->query("SELECT SUM(amount) as amt FROM lp_store_withdrawal WHERE store_id='{$sid}'");
    if($r = $sum->fetch_assoc()['amt']) return $r;
    return 0;
}

function getUserLastWithdrawnDate($sid){
    $sum = db()->query("SELECT time FROM lp_store_withdrawal WHERE store_id='{$sid}' AND status=1 ORDER BY time DESC LIMIT 1");
   // print_r($sum);die();
    if($sum->num_rows > 0) return $sum->fetch_assoc()['time'];
    return 0;
}

function countStoreTotalSales($store_id){
    $query = db()->query("SELECT * FROM lp_store_order WHERE store_id='{$store_id}' AND status=1");
    return $query->num_rows;
}

//sum all column with status zero
function getUserProcessingAmount($sid){
    $sum = db()->query("SELECT SUM(amount) as amt FROM lp_store_withdrawal WHERE store_id='{$sid}' AND status=0");
    if($r = $sum->fetch_assoc()['amt']) return $r;
    return 0;
}



function getUserAvailableBalance($userid=null){
    $uid = ($userid) ? $userid : get_userid();
    $store = getCurrentUserStore($uid);
    $sid = $store['s_id'];
    $total_sales = getStoreTotalSalesAmount($sid);
    //get the total withdrawn
    $Payout = getStoreTotalPayout($sid);
    $available_balance = $total_sales - $Payout;
    return $available_balance;
}

function getStoreRequests($limit = 10){
    $userid = get_userid();
    $sql = "SELECT * FROM lp_store_withdrawal WHERE user_id='{$userid}'";
    return paginate($sql,$limit);
}

function insertNewWithdrawRequest($val){
    $amount =  sanitizeText($val['amount']);
    $method = sanitizeText($val['type']);
    $user_id = get_userid();
    $store = getCurrentUserStore($user_id);
    $sid = $store['s_id'];
    $time = time();

    $sql = db()->query("INSERT INTO lp_store_withdrawal(user_id,status,store_id,time,amount,method)
    VALUES ('{$user_id}',0,'{$sid}','{$time}','{$amount}','{$method}')");
     /*if(config('notify-admin-by-email-on-withdrawal-request',true)){
         sendAdminPayOutRequestEmail($store,$method,$amount);
     }*/

    return true;
}

function removeWishes($pid,$user_id){
    db()->query("DELETE FROM lp_wishlist WHERE product_id='{$pid}' AND user_id='{$user_id}'");
    return true;
}
function addToWishlist($pid,$attr){
    $attr = perfectSerialize($attr);
    $user_id = get_userid();
    removeWishes($pid,$user_id);
    db()->query("INSERT INTO lp_wishlist (attr,user_id,product_id) VALUES ('{$attr}','{$user_id}','{$pid}')");
    return true;
}

function getMyWishList($userid,$limit = 10){
    $sql = "SELECT * FROM lp_wishlist WHERE user_id='{$userid}'";
    return paginate($sql,$limit);
}

function removeStore($id){
    db()->query("DELETE FROM lp_stores WHERE s_id='{$id}'");
    fire_hook('store.delete',null,array($id));
    return true;
}

function deleteProduct($id){
    db()->query("DELETE FROM lp_products WHERE id='{$id}'");
    fire_hook('product.delete',null,array($id));
    return true;
}

function updateWithdrawalStatus($wid,$statusCode){
   db()->query("UPDATE lp_store_withdrawal SET status='{$statusCode}' WHERE w_id='{$wid}'");
    return true;
}
function getStoresEligibleForWithdraw(){
    $min = config('minimum-withdrawal-limit',10);
    $response = array();
   //s $sid = '';
   // $query = "SELECT (SUM(sub_total) - (SELECT SUM(amount) as wd FROM lp_store_withdrawal WHERE store_id='{$sid}' AND status=1)) AS amt,store_id FROM `lp_store_order`  GROUP BY store_id HAVING amt > '{$min}'";
    $query = db()->query("SELECT (SUM(sub_total) + SUM(shipping_price)) AS amt,store_id FROM `lp_store_order` WHERE status=1 AND emoney=0  GROUP BY store_id");
    if($query->num_rows >0 ){
        while($r = $query->fetch_assoc()){
            $store_id = $r['store_id'];
            $overall_earning = $r['amt'];
            $overall_withdrawn = getStoreTotalWithdrawn($store_id);
            $balance = $overall_earning - $overall_withdrawn;
            if($balance > $min){
                $r['balance'] = $balance;
                $response[] = $r;
            }
        }
    }
    return $response;
}

function getWithdrawalRequests($type){
    switch($type){
        case 'pending':
            $sql= "SELECT * FROM lp_store_withdrawal WHERE status=0 ORDER BY time DESC";
            break;
        case 'successful':
            $sql= "SELECT * FROM lp_store_withdrawal  WHERE status=1 ORDER BY time DESC";
            break;
        default:
            $sql = "SELECT * FROM lp_store_withdrawal ORDER BY time DESC";
            break;
    };
    return paginate($sql,'all');
}

function rearrange_product_quantity($pq){
    $storeIdArray= array();
    foreach($pq as $p=>$q){
        $product = getSingleProduct($p);
        $price = getProductPrice($product);
        $store_id = $product['store_id'];
        if(array_key_exists($store_id,$storeIdArray)){
            $lastTotal = $storeIdArray[$store_id];
            $storeIdArray[$store_id] = $lastTotal + ($price * $q);
        }else{
            $storeIdArray[$store_id] = $price * $q;
        }
    }
   // print_r($storeIdArray);
    //die();
    return $storeIdArray;
}

function getMySlugFromMyName($name){
    $slug = toAscii($name);
    //if (empty($slug)) $slug = md5(time());
    return $slug;
}

function getCategoryParentIdFromSlug($slug,$key='parent_id'){
    $q = db()->query("SELECT $key FROM lp_store_categories WHERE slug='{$slug}'");
    $r = $q->fetch_assoc();
    if($key=='parent_id')  return $r['parent_id'];
    return $r;
   // print_r($r);die();

}

function get_store_followers($sid){
    $sql = db()->query("SELECT user_id FROM lp_store_followers WHERE store_id='{$sid}' AND status=1");
    $response = array();
    if($sql->num_rows > 0){
        while($r = $sql->fetch_assoc()){
            $response[] = $r['user_id'];
        }
    }
    return $response;
}

function isOutofStock($product){
    $response = ((int)$product['quantity'] <= 0)  ? true : false;
    return $response;
}
function sendBuyerDownloadLink($order_id) {
    $order = getSingleOrder($order_id);
    if (!$order) return false;
    $user = find_user($order['user_id']);
    $billing_details = getBillingOrShippingDetails('lp_billing_details',$order['bid']);
   // $shipping_details = getBillingOrShippingDetails('lp_shipping_details',$order['sid']);
    $email = $billing_details['email_address'];
    $pq = perfectUnserialize($order['product']);
    foreach($pq as $p=>$q){
        $product = getSingleProduct($p);
        if($product['type'] == 'tangible') continue;
        if(!$product['product_path']) continue;
        $download_link = url($product['product_path']);
        $mailer = mailer();
        $mailer->setAddress($email, get_user_name($user))->template("send-download-link-to-buyer", array(
            'download_link' => $download_link,
            'recipient-title' => get_user_name($user),
            'recipient-link'  => profile_url(null, $user),
        ));
        $mailer->send();
    }
}

function sendSellerSmsNotification($number,$name,$s = null){
    if(config("send_seller_sms_notification",1)){
        if(plugin_exists('sms') && plugin_loaded('sms') && function_exists('send_sms')){
            //send sms
            if($s){
                $txt = lang("store::successfully-paid-for-your-product");
            }else{
                $txt = lang("store::ordered-your-product");
            }
            $msg = $name.' '.$txt;
            try{
                send_sms($msg,$number);
            }catch (Exception $e){

            }
        }
    }
    return false;
}


function sendSellerNewOrderNotification($store_email_address,$product,$sub_total,$order_id,$q,$bid,$store_owner,$price){
    $billing_details = getBillingOrShippingDetails('lp_billing_details',$bid);
    $billing_name = $billing_details['first_name'].' '.$billing_details['last_name'];
    $user = find_user($store_owner);
    $mailer = mailer();
    //saf start
    session_put('saf_store_owner_details',$user['id']);
    session_put('saf_store_email_address',$store_email_address);
    $mailer->setAddress($store_email_address, get_user_name($user))->template("notify-store-owner-of-new-order", array(
        'billing_name' => $billing_name,
        'billing_company'=>$billing_details['company'],
        //saf start
        'zip'=>$billing_details['zip'],
        'city'=>$billing_details['city'],
        'phone'=>$billing_details['phone'],
        'state'=>$billing_details['state'],
        //saf end
        'billing_address'=>$billing_details['address'],
        'email_address'=>$billing_details['email_address'],
        'billing_country'=>$billing_details['country'],
        'order_id'=>$order_id,
        'quantity'=>$q,
        'price'=>config('currency-sign').$price,
        'total'=>$sub_total,
        'product_name'=>$product['name'],
        'recipient-title' => get_user_name($user),
        'recipient-link'  => profile_url(null, $user),
    ));
    $mailer->send();

    //send
    send_notification_privacy('notify-site-store',$store_owner,'notify-seller.new-order',$order_id);
}
function getProductWebsiteAdmin(){
    $sql = db()->query("SELECT id FROM users WHERE role=1");
    $admins = array();
    if($sql->num_rows > 0){
        while($r = $sql->fetch_assoc()){
            $admins[] = $r['id'];
        }
    }
    return $admins;
}
function sendAdminPayOutRequestEmail($store,$method,$amount){
    $admin_user_ids = getProductWebsiteAdmin();
    $store_email_address = $store['email'];
    $store_name = $store['name'];
    if(!$admin_user_ids) return false;
    foreach($admin_user_ids as $uid){
        $user = find_user($uid);
        $email = $user['email_address'];
        $mailer = mailer();
        $mailer->setAddress($email, get_user_name($user))->template("notify-admin-withdrawal-request", array(
            'store_name'=>$store_name,
            'email_address'=>$store_email_address,
            'payment_method'=>$method,
            'amount'=>config('currency-sign').$amount,
            'recipient-title' => get_user_name($user),
            'recipient-link'  => profile_url(null, $user),
        ));
        $mailer->send();
    }
}

function getMyStoreAccountSettings($userid=null){
    $uid = ($userid) ? $userid : get_userid();
   $query =  db()->query("SELECT * FROM lp_account_settings WHERE user_id='{$uid}';");
    if($query->num_rows >0 ){
        return $query->fetch_assoc();
    }else{
        return false;
    }

}


function deleteOldWithdrawalSetup(){
    $uid = get_userid();
    db()->query("DELETE FROM lp_account_settings WHERE user_id='{$uid}'");
}
function saveWiredAccountDetails($val,$type){
     deleteOldWithdrawalSetup();
    /**
     * @var $swift_code
     * @var $bank_name
     * @var $bank_address
     * @var $account_number
     * @var $account_name
     * @var $address
     * @var $phone
     * @var $city_state
     * @var $country
     * @var $add_info
     * @var $skrill_email
     * @var $paypal_email
     */
    $time = time();
    $userid= get_userid();
    extract($val);
    if($type == 'wire-transfer'){
        $payment_type = 'Wire Transfer';
        //   die();
        db()->query("INSERT INTO lp_account_settings(payment_type,user_id,swift_code,bank_name,bank_address,account_number,account_name,address,phone,city_state,country,add_info,time)
    VALUES ('{$payment_type}','{$userid}','{$swift_code}','{$bank_name}','{$bank_address}','{$account_number}','{$account_name}','{$address}','{$phone}','{$city_state}','{$country}','{$add_info}','{$time}')");
    }

    if($type == 'skrill'){
        $payment_type = 'Skrill';
        db()->query("INSERT INTO lp_account_settings(user_id,payment_type,skrill_email,time)
        VALUES ('{$userid}','{$payment_type}','{$skrill_email}','{$time}')");
    }

    if($type == "paypal"){
        $payment_type = 'Paypal';
        db()->query("INSERT INTO lp_account_settings(user_id,payment_type,paypal_email,time)
        VALUES ('{$userid}','{$payment_type}','{$paypal_email}','{$time}')");
    }

    return true;
}

function getPagerBasedOnPaymentType($payment_type){
    switch($payment_type){
        case 'Wire Transfer':
            return 'wired-transfer';
        break;
        case 'Skrill':
            return 'skrill_manager_settings';
        break;
        case 'Paypal':
            return 'manager_paypal_transfer';
        break;
    }
    return 'manager-settings';
}

function getAllSuccessfulPayouts(){
    $sql = "SELECT * FROM lp_store_withdrawal";
   return  paginate($sql,10);
}

function countSellers(){
    $db = db();
    $q = $db->query("SELECT * FROM lp_stores");
    return $q->num_rows;
}
function countProducts(){
    $db = db();
    $q = $db->query("SELECT * FROM lp_products");
    return $q->num_rows;
}

function formatPriceNumber($price){
    return config('currency-sign','$').number_format((float)$price,2,'.',',');
}