<?php

function store_install_database(){


 db()->query("CREATE TABLE IF NOT EXISTS `lp_billing_details` (
	`b_id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`first_name` VARCHAR(255) NULL DEFAULT NULL,
	`last_name` VARCHAR(255) NULL DEFAULT NULL,
	`company` VARCHAR(255) NULL DEFAULT NULL,
	`email_address` VARCHAR(255) NULL DEFAULT NULL,
	`address` VARCHAR(255) NULL DEFAULT NULL,
	`phone` VARCHAR(255) NULL DEFAULT NULL,
	`country` VARCHAR(255) NULL DEFAULT NULL,
	`city` VARCHAR(255) NULL DEFAULT NULL,
	`zip` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`b_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");
    db()->query("CREATE TABLE IF NOT EXISTS `lp_order` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`bid` INT(11) NOT NULL DEFAULT '0',
	`sid` INT(11) NOT NULL DEFAULT '0',
	`product` TEXT NOT NULL,
	`time` INT(11) NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`invoice` VARCHAR(255) NULL DEFAULT NULL,
	`total_price` VARCHAR(255) NULL DEFAULT NULL,
	`payment_type` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");
    db()->query("CREATE TABLE  IF NOT EXISTS `lp_stores` (
	`s_id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`corporate_email` VARCHAR(255) NULL DEFAULT NULL,
	`slug` VARCHAR(255) NULL DEFAULT NULL,
	`cname` VARCHAR(255) NULL DEFAULT NULL,
	`website` VARCHAR(255) NULL DEFAULT NULL,
	`location` VARCHAR(255) NULL DEFAULT NULL,
	`describ` TEXT NULL,
	`image` TEXT NULL,
	`email` VARCHAR(50) NULL DEFAULT NULL,
	`address` VARCHAR(50) NULL DEFAULT NULL,
	`phone` VARCHAR(50) NULL DEFAULT NULL,
	`enable_paypal` INT(11) NULL DEFAULT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`time` INT(11) NULL DEFAULT NULL,
	`status` INT(11) NULL DEFAULT '0',
	`featured` INT(11) NULL DEFAULT '0',
	`paypal_signature` VARCHAR(255) NULL DEFAULT NULL,
	`paypal_username` VARCHAR(255) NULL DEFAULT NULL,
	`paypal_password` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`s_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_products` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`pay_on_delivery` INT(11) NOT NULL DEFAULT '0',
	`views` INT(11) NOT NULL DEFAULT '0',
	`producer` VARCHAR(255) NULL DEFAULT NULL,
	`name` VARCHAR(50) NULL DEFAULT NULL,
	`tags` VARCHAR(255) NULL DEFAULT NULL,
	`product_path` VARCHAR(255) NULL DEFAULT NULL,
	`type` VARCHAR(255) NULL DEFAULT NULL,
	`quantity` INT(11) NULL DEFAULT NULL,
	`price` FLOAT NULL DEFAULT NULL,
	`discount_price` FLOAT NULL DEFAULT NULL,
	`e_date` INT(11) NULL DEFAULT NULL,
	`description` TEXT NULL,
	`additional_images` TEXT NULL,
	`category` VARCHAR(50) NULL DEFAULT NULL,
	`main_photo` VARCHAR(255) NULL DEFAULT NULL,
	`labels` TEXT NULL,
	`l_val` TEXT NULL,
	`slug` VARCHAR(255) NULL DEFAULT NULL,
	`status` INT(11) NULL DEFAULT NULL,
	`time` INT(11) NULL DEFAULT NULL,
	`store_id` INT(11) NULL DEFAULT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`featured` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_lp_products_lp_stores` (`store_id`),
	CONSTRAINT `FK_lp_products_lp_stores` FOREIGN KEY (`store_id`) REFERENCES `lp_stores` (`s_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_ratings` (
	`rid` INT(11) NOT NULL AUTO_INCREMENT,
	`rating` INT(11) NOT NULL DEFAULT '0',
	`user_id` INT(11) NULL DEFAULT NULL,
	`type` VARCHAR(50) NULL DEFAULT NULL,
	`type_id` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`rid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_shipping_details` (
	`b_id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`first_name` VARCHAR(255) NULL DEFAULT NULL,
	`last_name` VARCHAR(255) NULL DEFAULT NULL,
	`address` VARCHAR(255) NULL DEFAULT NULL,
	`company` VARCHAR(255) NULL DEFAULT NULL,
	`email_address` VARCHAR(255) NULL DEFAULT NULL,
	`phone` VARCHAR(255) NULL DEFAULT NULL,
	`country` VARCHAR(255) NULL DEFAULT NULL,
	`city` VARCHAR(255) NULL DEFAULT NULL,
	`zip` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`b_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");


    db()->query("CREATE TABLE IF NOT EXISTS `lp_store_categories` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`slug` VARCHAR(255) NOT NULL,
	`parent_id` INT(11) NOT NULL,
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`cat_order` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_store_followers` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`status` INT(11) NOT NULL DEFAULT '0',
	`user_id` INT(11) NULL DEFAULT NULL,
	`store_id` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_store_order` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`store_owner` INT(11) NULL DEFAULT NULL,
	`quantity` INT(11) NULL DEFAULT NULL,
	`attr` TEXT NULL,
	`store_id` INT(11) NULL DEFAULT NULL,
	`product_id` INT(11) NULL DEFAULT NULL,
	`order_id` INT(11) NULL DEFAULT NULL,
	`status` INT(11) NULL DEFAULT NULL,
	`sub_total` FLOAT NULL DEFAULT NULL,
	`price` FLOAT NULL DEFAULT '0',
	`time` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_store_producer`  (
	`pid` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`email` VARCHAR(255) NULL DEFAULT NULL,
	`address` VARCHAR(255) NULL DEFAULT NULL,
	`phone` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`pid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=4
;
");

    db()->query("CREATE TABLE IF NOT EXISTS `lp_store_withdrawal` (
	`w_id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`status` INT(11) NULL DEFAULT NULL,
	`store_id` INT(11) NULL DEFAULT NULL,
	`time` INT(11) NULL DEFAULT NULL,
	`amount` FLOAT NULL DEFAULT NULL,
	`method` VARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY (`w_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");
    db()->query("CREATE TABLE IF NOT EXISTS `lp_wishlist` (
	`w_id` INT(11) NOT NULL AUTO_INCREMENT,
	`attr` TEXT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`product_id` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`w_id`),
	INDEX `FK_lp_wishlist_lp_products` (`product_id`),
	CONSTRAINT `FK_lp_wishlist_lp_products` FOREIGN KEY (`product_id`) REFERENCES `lp_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

    db()->query("CREATE TABLE `lp_account_settings` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`payment_type` VARCHAR(50) NOT NULL DEFAULT '0',
	`skrill_email` VARCHAR(50) NOT NULL DEFAULT '0',
	`paypal_email` VARCHAR(50) NOT NULL DEFAULT '0',
	`country` VARCHAR(50) NOT NULL DEFAULT '0',
	`swift_code` VARCHAR(50) NOT NULL DEFAULT '0',
	`branch_address` VARCHAR(50) NOT NULL DEFAULT '0',
	`account_number` VARCHAR(50) NOT NULL DEFAULT '0',
	`account_name` VARCHAR(50) NOT NULL DEFAULT '0',
	`address` VARCHAR(255) NOT NULL DEFAULT '0',
	`city_state` VARCHAR(50) NOT NULL DEFAULT '0',
	`phone` VARCHAR(50) NOT NULL DEFAULT '0',
	`bank_name` VARCHAR(50) NOT NULL DEFAULT '0',
	`bank_address` VARCHAR(255) NOT NULL DEFAULT '0',
	`add_info` TEXT NOT NULL,
	`time` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=4
;
");

}