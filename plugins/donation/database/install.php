<?php

function donation_install_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `donation_categories` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`category_order` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");

    $db->query("CREATE TABLE IF NOT EXISTS `lh_donations` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`category` VARCHAR(255) NULL DEFAULT NULL,
	`expire_time` VARCHAR(255) NULL DEFAULT NULL,
	`cur` VARCHAR(255) NULL DEFAULT NULL,
	`location` VARCHAR(255) NULL DEFAULT NULL,
	`paypal_email` VARCHAR(255) NULL DEFAULT NULL,
	`publishable_key` VARCHAR(255) NULL DEFAULT NULL,
	`secret_key` VARCHAR(255) NULL DEFAULT NULL,
	`ytube` VARCHAR(255) NULL DEFAULT NULL,
	`target_amount` DOUBLE NULL DEFAULT '0',
	`donation_min` DOUBLE NULL DEFAULT '0',
	`verified` INT(11) NULL DEFAULT '0',
	`featured` INT(11) NULL DEFAULT '0',
	`time` INT(11) NULL DEFAULT '0',
	`status` INT(11) NULL DEFAULT '0',
	`published` INT(11) NULL DEFAULT '1',
	`anonymous` INT(11) NULL DEFAULT '0',
	`unlimited` INT(11) NULL DEFAULT '0',
	`views` INT(11) NULL DEFAULT '0',
	`expired` INT(11) NULL DEFAULT '0',
	`closed` INT(11) NULL DEFAULT '0',
	`privacy` INT(11) NULL DEFAULT '1',
	`who_comment` INT(11) NULL DEFAULT '1',
	`who_donate` INT(11) NULL DEFAULT '1',
	`description` TEXT NULL,
	`full_description` TEXT NULL,
	`predefined` TEXT NULL,
	`image` TEXT NULL,
	`gallery` TEXT NULL,
	`terms` TEXT NULL,
	`t_message` TEXT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=8;");

    $db->query("CREATE TABLE IF NOT EXISTS `lh_donations_followers` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`did` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `lh_donation_raised` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`did` INT(11) NULL DEFAULT '0',
	`amount` FLOAT NULL DEFAULT '0',
	`anonymous` INT(11) NULL DEFAULT '0',
	`user_id` INT(11) NULL DEFAULT '0',
	`full_name` VARCHAR(255) NULL DEFAULT '0',
	`cur` VARCHAR(255) NULL DEFAULT 'USD',
	`email_address` VARCHAR(255) NULL DEFAULT '0',
	`msg` TEXT NULL,
	`status` INT(11) NULL DEFAULT '1',
	`to_feed` INT(11) NULL DEFAULT '0',
	`time` INT(11) NULL DEFAULT '1',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $arr =  array('Animals','Arts','Children','Education','Hospices','Religion','Sport','Others');
    $cTableName = "`donation_categories`";
    foreach($arr as $k=>$a){
        $titleSlug = "donation_category_".md5(time().serialize($arr).$k).'_title';

        foreach(get_all_languages() as $language){
            $langId = $language['language_id'];
            add_language_phrase($titleSlug, $a, $langId, 'donation');
        }


        $time = time();
        $order = db()->query('SELECT id FROM '.$cTableName);
        $order = $order->num_rows;
        $query = db()->query("INSERT INTO ".$cTableName."(
            `title`,`category_order`) VALUES(
            '{$titleSlug}','{$order}'
            )
        ");
    }


}