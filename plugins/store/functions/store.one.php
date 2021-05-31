<?php

function getStoreOrderBySingleOrderId($id,$uid = null){
    $userid = ($uid) ? $uid : get_userid();
    $sql = db()->query("SELECT * FROM lp_store_order WHERE store_owner='{$userid}' AND order_id='{$id}'");
    if($sql->num_rows > 0){
        return $sql->fetch_assoc();
    }
    return 0;
}

function importStoreCategories()
{
    $arr = array(
        array(
            'name' => 'Phones and Tablets',
            'image' => img('store::images/import/1.jpg'),
            'sub' => array(
                array(
                    'name' => 'iOS Phones',
                    'image' => img('store::images/import/2.jpg')
                ),
                array(
                    'name' => 'Android Phones',
                    'image' => img('store::images/import/3.jpg')
                ),
                array(
                    'name' => 'Ipad',
                    'image' => img('store::images/import/4.jpg')
                ),
                array(
                    'name' => 'Android Tablets',
                    'image' => img('store::images/import/5.png')
                ),
                array(
                    'name' => 'Power Banks',
                    'image' => img('store::images/import/6.jpg')
                )
            )
        ),
        array(
            'name' => 'TV and Electronics',
            'image' => img('store::images/import/7.jpg'),
            'sub' => array(
                array(
                    'name' => 'Television',
                    'image' => img('store::images/import/8.png')
                ), array(
                    'name' => 'Home Theatre',
                    'image' => img('store::images/import/9.jpg')
                ),
                array(
                    'name' => 'Cameras',
                    'image' => img('store::images/import/10.jpg')
                ),
                array(
                    'name' => 'Musical Instruments',
                    'image' => img('store::images/import/11.jpg')
                )
            )
        ),
        array(
            'name' => 'Men Wears',
            'image' => img('store::images/import/12.jpg'),
            'sub' => array(
                array(
                    'name' => 'Trousers',
                    'image' => img('store::images/import/13.jpg')
                ), array(
                    'name' => 'T shirts',
                    'image' => img('store::images/import/14.jpg')
                ),
                array(
                    'name' => 'under wears',
                    'image' => img('store::images/import/15.jpg')
                ),array(
                    'name' => 'Jerseys',
                    'image' => img('store::images/import/16.jpg')
                ),
                array(
                    'name' => 'Shoes',
                    'image' => img('store::images/import/17.jpg')
                )
            )
        ),
        array(
            'name' => 'Women Wears',
            'image' => img('store::images/import/18.jpg'),
            'sub' => array(
                array(
                    'name' => 'Clothing',
                    'image' => img('store::images/import/19.jpg')
                ), array(
                    'name' => 'Shoes',
                    'image' => img('store::images/import/20.jpg')
                ),
                array(
                    'name' => 'under wears',
                    'image' => img('store::images/import/21.jpg')
                ),array(
                    'name' => 'Accessories',
                    'image' => img('store::images/import/22.jpg')
                )
            )
        ),
        array(
            'name' => 'Computing',
            'image' => img('store::images/import/23.jpg'),
            'sub' => array(
                array(
                    'name' => 'Laptops',
                    'image' => img('store::images/import/24.jpg')
                ), array(
                    'name' => 'Projectors',
                    'image' => img('store::images/import/25.jpg')
                ),
                array(
                    'name' => 'Keyboards',
                    'image' => img('store::images/import/26.jpg')
                ),array(
                    'name' => 'Hard Disks Drive',
                    'image' => img('store::images/import/27.jpg')
                ),
                array(
                    'name' => 'Printers and Scanners',
                    'image' => img('store::images/import/28.jpg')
                )
            )
        ),
        array(
            'name' => 'Groceries',
            'image' => img('store::images/import/29.JPG'),
            'sub' => array(
                array(
                    'name' => 'Air Freshners',
                    'image' => img('store::images/import/30.jpg')
                ), array(
                    'name' => 'Laundry',
                    'image' => img('store::images/import/31.jpg')
                ),
                array(
                    'name' => 'Canned Foods',
                    'image' => img('store::images/import/32.jpg')
                ),
                array(
                    'name' => 'Flours',
                    'image' => img('store::images/import/33.jpg')
                ),
                array(
                    'name' => 'Juice',
                    'image' => img('store::images/import/34.jpg')
                ),
                array(
                    'name' => 'Wine',
                    'image' => img('store::images/import/35.jpg')
                ),array(
                    'name' => 'Spirits',
                    'image' => img('store::images/import/36.jpg')
                ),
                array(
                    'name' => 'Toilet Soaps',
                    'image' => img('store::images/import/37.jpg')
                ),
                array(
                    'name' => 'Sanitary Towels',
                    'image' => img('store::images/import/38.jpg')
                ),
                array(
                    'name' => 'Pet Food',
                    'image' => img('store::images/import/39.jpg')
                )
            )
        ),
        array(
            'name' => 'Health and Beauty',
            'image' => img('store::images/import/40.jpg'),
            'sub' => array(
                array(
                    'name' => 'Male Fragrances',
                    'image' => img('store::images/import/41.jpg')
                ), array(
                    'name' => 'Female Fragrances',
                    'image' => img('store::images/import/42.jpg')
                ),
                array(
                    'name' => 'Female Skin Care',
                    'image' => img('store::images/import/43.jpg')
                ),
                array(
                    'name' => 'Male Skin Care',
                    'image' => img('store::images/import/44.jpg')
                ),
                array(
                    'name' => 'Hair Care',
                    'image' => img('store::images/import/45.jpg')
                ),
                array(
                    'name' => 'Make-up',
                    'image' => img('store::images/import/46.jpg')
                ),
                array(
                    'name' => 'Dietary supplements',
                    'image' => img('store::images/import/47.jpg')
                ),
            )
        ),
        array(
            'name' => 'Home and office',
            'image' => img('store::images/import/48.jpg'),
            'sub' => array(
                array(
                    'name' => 'Fridges and Freezers',
                    'image' => img('store::images/import/48.jpg')
                ), array(
                    'name' => 'Cooking appliances',
                    'image' => "",
                ),
                array(
                    'name' => 'Microwave',
                    'image' => ""
                ),
                array(
                    'name' => 'Kettles',
                    'image' => ""
                ),
                array(
                    'name' => 'Toasters',
                    'image' => ""
                ),
                array(
                    'name' => 'Wall Art',
                    'image' => ""
                ),
                array(
                    'name' => 'Water Dispenser',
                    'image' => ""
                )
            )
        ),
        array(
            'name' => 'Baby,Kids & Toys',
            'image' => img('store::images/import/49.jpeg'),
            'sub' => array(
                array(
                    'name' => 'Diapers',
                    'image' => ""
                ), array(
                    'name' => 'Bottle Feeding',
                    'image' => ""
                ),
                array(
                    'name' => 'Breast Feeding',
                    'image' => ""
                ),
                array(
                    'name' => 'Baby cream',
                    'image' => ""
                ),
                array(
                    'name' => 'Dolls',
                    'image' => ""
                ),
                array(
                    'name' => 'Educational Toys',
                    'image' => ""
                ),
                array(
                    'name' => 'Baby safety',
                    'image' => ""
                )
            )
        ),
        array(
            'name' => 'Games & Console',
            'image' => img('store::images/import/50.jpg'),
            'sub' => array(
                array(
                    'name' => 'Playstation',
                    'image' => ""
                ), array(
                    'name' => 'PC Gaming',
                    'image' => ""
                ),
                array(
                    'name' => 'Nintendo Accessories',
                    'image' => ""
                ),
                array(
                    'name' => 'Xbox 360',
                    'image' => ""
                ),
                array(
                    'name' => 'PSP',
                    'image' => ""
                ),
                array(
                    'name' => 'Junior and Family',
                    'image' => ""
                ),
            )
        ),
        array(
            'name' => 'Watches and Sun glasses',
            'image' => img('store::images/import/51.jpg'),
            'sub' => array(
                array(
                    'name' => 'Bracelet Watches',
                    'image' => ""
                ), array(
                    'name' => 'Leather watches',
                    'image' => ""
                ),
                array(
                    'name' => 'Rubber watches',
                    'image' => ""
                ),
                array(
                    'name' => 'Digital wathces',
                    'image' => ""
                ),
                array(
                    'name' => 'Women Sunglasses',
                    'image' => ""
                ),
                array(
                    'name' => 'Men Sunglasses',
                    'image' => ""
                )
            )
        ),
        array(
            'name' => 'Automobile',
            'image' => img('store::images/import/52.jpg'),
            'sub' => array(
                array(
                    'name' => 'Vaccum Cleaners',
                    'image' => ""
                ),
                array(
                    'name' => 'Brushers and Washers',
                    'image' => ""
                ),
                array(
                    'name' => 'Air Freshners',
                    'image' => ""
                ),
                array(
                    'name' => 'Engine and Engine Parts',
                    'image' => ""
                ),
                array(
                    'name' => 'Filters',
                    'image' => ""
                ),
                array(
                    'name' => 'Starter & Alternator',
                    'image' => ""
                ),
                array(
                    'name' => 'Additives',
                    'image' => ""
                ),
                array(
                    'name' => 'Engine Oils',
                    'image' => ""
                )
            )
        ),
    );

    foreach($arr as $key=>$main){
        $name = $main['name'];
        $img = $main['image'];
        //add and upload category
        $pid = addImportCategory($name,$img,0);
        $subarr = $main['sub'];
        foreach($subarr as $k=>$v){
            $name = $v['name'];
            $img = $v['image'];
            addImportCategory($name,$img,$pid);
        }
    }
    //echo '<pre>', print_r($arr), '</pre>';die();
    return true;
}

function addImportSlides(){
    $arr = array(
        img('store::images/slides/1.jpg'),
        img('store::images/slides/2.png'),
        img('store::images/slides/3.jpg'),
        img('store::images/slides/4.jpg'),
        img('store::images/slides/5.JPG'),
        img('store::images/slides/6.jpg')

    );
    foreach($arr as $a){
        $type = "category";
        $titleSlug = "";
        $pid = 0;
        $slug = time();
        $image = $a;
        $type = 'slides';
        db()->query("INSERT INTO lp_store_categories (title,parent_id,slug,image,type,import) VALUES(
    '{$titleSlug}','{$pid}','{$slug}','{$image}','{$type}','1'
    )");
    }
    return true;
}

function deleteImportedSlides(){
    $q = db()->query("DELETE FROM `lp_store_categories` WHERE import='1' AND type='slides'");
    return true;
}

function deleteImportedStoreCategories(){
    $q = db()->query("SELECT * FROM `lp_store_categories` WHERE import='1' AND type='category'");
    if($q->num_rows > 0){
        while($r = $q->fetch_assoc()){
            $id = $r['id'];
            $img = $r['image'];
            $sql = "DELETE FROM `lp_store_categories` WHERE `id`='{$id}'";
            //we are not deleting image incase admin decided to add
            db()->query($sql);
        }
    }
    return true;
}

function addImportCategory($name,$img,$pid = 0){
    $val = array();
    foreach(get_all_languages() as $language){
        $val['title'][$language['language_id']] = $name;
    }
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
    $image = "";
    //image
    if($img){
        /*$uploader = new Uploader($img,"image",false,true,false);
        if($uploader->passed()) {
            $uploader->setPath('store/categories/import/');
            $image = $uploader->resize(null, null)->result();
        }*/
        $image = $img;
    }
    $type = "category";
    db()->query("INSERT INTO lp_store_categories (title,parent_id,slug,image,type,import) VALUES(
    '{$titleSlug}','{$pid}','{$slug}','{$image}','{$type}','1'
    )");
    return db()->insert_id;
}

function storeCategoryImage($cat, $size = 200)
{
    return ($cat['image']) ? url_img($cat['image'], $size) : img("store::images/cat-default.png");
}

function store_is_parent_category_active($category)
{
    if (segment(2) == 'categories') {
        $slug = segment(3);
        if ($slug) {
            $arr = explode('_', $slug);
            $id = $arr[0];
            if ($id == $category['id']) {
                return true;
            }
        }
    }
    return false;
}

function getProductsCatCount($sb)
{
    $id = $sb['id'];
    //$q = db()->query("SELECT * FROM lp_products WHERE category_id='{$id}'");
    $q = db()->query("SELECT * FROM lp_products WHERE category_id='{$id}' GROUP BY store_id");
    return $q->num_rows;
}

function product_has_discount($product)
{
    $current_time = time();
    $discount_expire_time = $product['e_date'];
    if ($product['discount_price'] && $product['price'] && $discount_expire_time && ($discount_expire_time > $current_time)) {
        $d = $product['discount_price'];
        $ex = $product['price'];
        if(!$ex) return false;
        if((float)$ex < (float)$d) return false;
        $diff = ($ex - $d);
        $p = ($diff / $ex) * 100;
        return floor($p) . '%';
    }
    return false;
}

function store_url($slug = null, $store = null)
{
    $store = $store ? $store : false;
    if (!$store && isset(app()->profileStore)) {
        $store = app()->profileStore;
    }
    return $store ? url_to_pager("store-profile", array('slug' => $store['slug'])) . '/' . $slug : false;
}

function get_store_cover($store = null, $original = true)
{
    $default = img("images/cover.jpg");
    if (!$original and !empty($store['store_cover_resized'])) return url_img($store['store_cover_resized']);
    if (!empty($store['store_cover'])) return url_img($store['store_cover']);
    return ($original) ? '' : $default;
}

function get_store_logo($size, $store = null)
{
    $avatar = $store['image'];
    if ($avatar) {
        return url(str_replace('%w', $size, $avatar));
    } else {

        return $image = img("store::images/comingsoon.png");
    }
}

function get_store_details($index, $store = null)
{
    $store = ($store) ? $store : app()->profileStore;
    if (isset($store[$index])) return $store[$index];
    return false;
}

function update_store_details($fields, $storeId)
{
    $sqlFields = "";
    foreach ($fields as $key => $value) {
        $value = sanitizeText($value);
        $sqlFields .= (empty($sqlFields)) ? "`{$key}`='{$value}'" : ",`{$key}`='{$value}'";
    }
    db()->query("UPDATE `lp_stores` SET {$sqlFields} WHERE `s_id`='{$storeId}'");
    fire_hook("store.updated", array($storeId));
}