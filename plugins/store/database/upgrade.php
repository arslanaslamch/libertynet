<?php
/*function getStoreCategoriesArr(){
   $q= db()->query("SELECT * FROM lp_store_categories");
   $result = array();
    if($q->num_rows > 0){
       while($r = $q->fetch_assoc()){
           $result[$r['id']] = lang($r['title']);
       }
    }
    print_r($result);
}
function update_products_categories(){
    $q =  db()->query("SELECT category,id FROM lp_products");
    if($q->num_rows > 0){
        $categories = getStoreCategoriesArr();
        $categories_string = array_keys($categories);
        while($r  = $q->fetch_assoc()){
            $category = $r['category'];
            $category_id = $
            $product_id = $r['id'];
            if()
            //check if the category is a parent or child.
            //if it a child, look for its parent and update it as parent::child
            //if it is a parent
            // db()->query("SELECT * FROM lp_store_categories WHERE ");
        }
    }
}*/

function store_upgrade_database(){

    //customization start
    db()->query("ALTER TABLE `lp_products` ADD `aff_link` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");

    db()->query("ALTER TABLE `lp_coupons` CHANGE COLUMN `products` `products` TEXT NULL DEFAULT NULL AFTER `coupon_value`;");
    db()->query("ALTER TABLE `lp_store_order` ADD COLUMN `commission` FLOAT NULL DEFAULT '0' AFTER `sub_total`");
    db()->query("ALTER TABLE `lp_store_order` ADD COLUMN `shipping_price` FLOAT NULL DEFAULT '0' AFTER `sub_total`");
    db()->query("ALTER TABLE `lp_order` ADD COLUMN `overall_commision` FLOAT NULL DEFAULT '0' AFTER `total_price`");
    db()->query("ALTER TABLE `lp_order` ADD COLUMN `shipping_price` FLOAT NULL DEFAULT '0' AFTER `total_price`");
    db()->query("ALTER TABLE `lp_stores` ADD COLUMN `verified` INT(11) NULL DEFAULT '0' AFTER `featured`;");
    db()->query("ALTER TABLE `lp_products` ADD COLUMN `category_id` INT(11) NULL DEFAULT '0';");
    db()->query("ALTER TABLE `lp_products` ADD COLUMN `category_parent_id` INT(11) NULL DEFAULT '0';");
    db()->query("CREATE TABLE IF NOT EXISTS `lp_shipping_settings` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`store_id` INT(11) NOT NULL DEFAULT '0',
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`zone` VARCHAR(50) NOT NULL DEFAULT '0',
	`shipping_method` VARCHAR(50) NOT NULL DEFAULT '0',
	`amount` VARCHAR(50) NOT NULL DEFAULT '0',
	`time` INT(11) NOT NULL DEFAULT '0',
	`regions` TEXT NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");


    db()->query("CREATE TABLE IF NOT EXISTS `lp_product_coupon` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`coupon_id` INT(11) NULL DEFAULT NULL,
	`code` VARCHAR(50) NULL DEFAULT NULL,
	`product_id` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK__lp_coupons` (`coupon_id`),
	INDEX `FK__lp_products` (`product_id`),
	CONSTRAINT `FK__lp_coupons` FOREIGN KEY (`coupon_id`) REFERENCES `lp_coupons` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__lp_products` FOREIGN KEY (`product_id`) REFERENCES `lp_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=20
;");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_coupons` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`store_id` INT(11) NOT NULL DEFAULT '0',
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`code` VARCHAR(50) NULL DEFAULT NULL,
	`expiry_date` VARCHAR(255) NULL DEFAULT NULL,
	`coupon_type` VARCHAR(255) NULL DEFAULT NULL,
	`coupon_value` VARCHAR(255) NULL DEFAULT NULL,
	`products` VARCHAR(255) NULL DEFAULT NULL,
	`time` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=2
;
");
    register_site_page("store_homepage",array(
       'title'=>lang('store::store'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store_homepage','content', 'middle');
        Widget::add(null,'store_homepage','plugin::store|manager_menu', 'left');
        Widget::add(null,'store_homepage','plugin::store|menu', 'left');
    });

    register_site_page("skrill_manager_settings",array(
        'title'=>"Skrill Setup Page",
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'skrill_manager_settings','content', 'middle');
        Widget::add(null,'skrill_manager_settings','plugin::store|manager_menu', 'left');
        Widget::add(null,'skrill_manager_settings','plugin::store|menu', 'left');
    });

    register_site_page("manager_paypal_transfer",array(
        'title'=>"Paypal Setup Page",
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'manager_paypal_transfer','content', 'middle');
        Widget::add(null,'manager_paypal_transfer','plugin::store|manager_menu', 'left');
        Widget::add(null,'manager_paypal_transfer','plugin::store|menu', 'left');
    });

    register_site_page("skrill_manager_settings",array(
        'title'=>"Skrill Setup Page",
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'skrill_manager_settings','content', 'middle');
        Widget::add(null,'skrill_manager_settings','plugin::store|manager_menu', 'left');
        Widget::add(null,'skrill_manager_settings','plugin::store|menu', 'left');
    });

    register_site_page("wired-transfer",array(
       'title'=>"Wired Transfer Setup Page",
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'wired-transfer','content', 'middle');
        Widget::add(null,'wired-transfer','plugin::store|manager_menu', 'left');
        Widget::add(null,'wired-transfer','plugin::store|menu', 'left');
    });

    register_site_page("manager-settings",array(
        'title'=>lang('store::account-settings'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'manager-settings','content', 'middle');
        Widget::add(null,'manager-settings','plugin::store|manager_menu', 'left');
        Widget::add(null,'manager-settings','plugin::store|menu', 'left');
    });

    register_site_page("single-product",array(
        'title'=>lang('store::single-product'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'single-product','content', 'middle');
        Widget::add(null,'single-product','plugin::store|menu', 'left');
        Widget::add(null,'single-product','plugin::store|categories', 'left');
    });

    register_site_page("store-order-manager",array(
        'title'=>lang('store::store-order'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store-order-manager','content', 'middle');
        Widget::add(null,'store-order-manager','plugin::store|manager_menu', 'left');

    });
    register_site_page("store-transactions-withdraw",array(
        'title'=>lang('store::withdraw'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store-transactions-withdraw','content', 'middle');
        Widget::add(null,'store-transactions-withdraw','plugin::store|manager_menu', 'left');

    });
    register_site_page("store-transactions",array(
        'title'=>lang('store::store-transactions'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store-transactions','content', 'middle');
        Widget::add(null,'store-transactions','plugin::store|manager_menu', 'left');

    });
    register_site_page("my_orders",array(
        'title'=>lang('store::my_order'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'my_orders','content', 'middle');
        Widget::add(null,'my_orders','plugin::store|menu', 'left');
        Widget::add(null,'my_orders','plugin::store|categories', 'left');
    });

    register_site_page("add-product",array(
        'title'=>lang('store::add-product'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'add-product','content', 'middle');
        Widget::add(null,'add-product','plugin::store|manager_menu', 'left');
    });

    register_site_page("store-manager",array(
        'title'=>lang('store::store-manager'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store-manager','content', 'middle');
        Widget::add(null,'store-manager','plugin::store|manager_menu', 'left');
    });

    register_site_page("store-products-manager",array(
        'title'=>lang('store::products-manager'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store-products-manager','content', 'middle');
        Widget::add(null,'store-products-manager','plugin::store|manager_menu', 'left');
    });

    register_site_page("store-manage-single-product",array(
        'title'=>lang('store::store-manage-single-product'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'store-manage-single-product','content', 'middle');
        Widget::add(null,'store-manage-single-product','plugin::store|manager_menu', 'left');
    });

    register_site_page("single-store",array(
        'title'=>lang('store::single-store'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'single-store','content', 'middle');
        Widget::add(null,'single-store','plugin::store|menu', 'left');
    });
 register_site_page("products-home",array(
        'title'=>lang('store::products'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'products-home','content', 'middle');
        Widget::add(null,'products-home','plugin::store|menu', 'left');
     Widget::add(null,'products-home','plugin::store|categories', 'left');
    });

    register_site_page("single-product",array(
        'title'=>lang('store::single-product'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'single-product','content', 'middle');
        Widget::add(null,'single-product','plugin::store|menu', 'left');
    });

    register_site_page("my-wish-list",array(
        'title'=>lang('store::my-wishlist'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'my-wish-list','content', 'middle');
        Widget::add(null,'my-wish-list','plugin::store|menu', 'left');
    });

    register_site_page("create-producer",array(
        'title'=>lang('store::add-producer'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'create-producer','content', 'middle');
        Widget::add(null,'create-producer','plugin::store|manager_menu', 'left');
    });

    register_site_page("producer-home",array(
        'title'=>lang('store::producer-home'),
        'column_type'=> TWO_COLUMN_LEFT_LAYOUT,

    ),function (){
        Widget::add(null,'producer-home','content', 'middle');
        Widget::add(null,'producer-home','plugin::store|manager_menu', 'left');
    });

    add_email_template("notify-store-owner-of-new-order", array(
        'title' => 'Notify Store Owner of new Product Order',
        'description' => 'This will notify the Store Owner when Someone ordered their product',
        'subject'   => lang('store::new-product-order'),
        'body_content' => '
            [header]
            This is to notify you that a customer has placed a new order on <h4>[product_name]</h4>.
            Below is the order Details.

            <strong>Customer Name: </strong> [billing_name]
            <strong>Company Name: </strong> [billing_company]
            <strong>Address: </strong> [billing_address]
            <strong>Email Address : </strong> [email_address]
            <strong>Country: </strong> [billing_country]
            <strong>Order ID: </strong> [order_id]
            <strong>Quantity: </strong> [quantity]
            <strong>Unit Price: </strong> [price]
            <strong>Total : </strong> [total]

            [footer]
        ',
        'placeholders' => '[product_name],[billing_name],[billing_company],
        [billing_address],[email_address],[billing_country],[email_address],[order_id],[quantity],[price],[total]'
    ));

    add_email_template("send-download-link-to-buyer", array(
        'title' => 'Send download link to buyer on successful Purchase',
        'description' => 'This will send the download link to Buyer',
        'subject'   => lang('store::product-download-link'),
        'body_content' => '
            [header]
            Thank you for purchasing our product. Below is the download link :

            <a href="[download_link]"> Download Now</a>

            [footer]
        ',
        'placeholders' => '[download_link]'
    ));

    add_email_template("send-order-notification-to-buyer", array(
        'title' => 'Send order notification to buyer',
        'description' => 'This will send the Email notification to Buyer',
        'subject'   => lang('store::product-download-link'),
        'body_content' => '
            [header]
            Thank you for patronizing our store. Your order is currently been processed. Below is the details of your order. :

            [cart]

                Thank you.
            [footer]
        ',
        'placeholders' => '[cart]'
    ));

    add_email_template("notify-admin-withdrawal-request", array(
        'title' => 'Notify Admin When user placed a new Withdrawal Request',
        'description' => 'This will notify the Website Admin when Seller request Withdraw',
        'subject'   => lang('store::new-withdrawal-request'),
        'body_content' => '
            [header]
            This is to notify you that a seller has placed a withdrawal Request.
            Below is the Sellers Details.

            <strong>Store Name: </strong> [store_name]
            <strong>Email Address: </strong> [email_address]
            <strong>Payment Method: </strong> [payment_method]
            <strong>Request Withdrawal Amount: </strong> [amount]

            [footer]
        ',
        'placeholders' => '[store_name],[payment_method],[amount],[balance],[email_address]'
    ));


    /** working latest version**/
    db()->query("ALTER TABLE `lp_store_categories` ADD COLUMN `image` TEXT NULL AFTER `cat_order`;");
    db()->query("ALTER TABLE `lp_stores` ADD COLUMN `store_cover_resized` TEXT NULL");
    db()->query("ALTER TABLE `lp_stores` ADD COLUMN `store_cover` TEXT NULL");
    db()->query("ALTER TABLE `lp_store_categories` ADD COLUMN `type` VARCHAR(255) NULL DEFAULT 'category' AFTER `title`;");
    db()->query("ALTER TABLE `lp_store_categories` ADD COLUMN `import` VARCHAR(255) NULL DEFAULT '0' AFTER `title`;");

    register_site_page('products-categories-home', array('title' => 'store::products-categories-home', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'products-categories-home', 'content', 'middle');
    });
    register_site_page('products-categories-list', array('title' => 'store::products-categories-list', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'products-categories-list', 'content', 'middle');
    });

    db()->query("ALTER TABLE `lp_store_order` ADD COLUMN `emoney` INT NULL DEFAULT '0' AFTER `price`");

}