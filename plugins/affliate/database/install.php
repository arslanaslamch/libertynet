<?php

function affliate_install_database() {
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `affliates` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL DEFAULT '0',
	`add_info` TEXT NOT NULL,
	`email` VARCHAR(255) NOT NULL DEFAULT '0',
	`address` VARCHAR(255) NOT NULL DEFAULT '0',
	`phone` VARCHAR(255) NOT NULL DEFAULT '0',
	`secret` VARCHAR(255) NOT NULL DEFAULT '0',
	`time` VARCHAR(255) NOT NULL DEFAULT '0',
	`status` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `secret` (`secret`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_commision_rules` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`payment_type` VARCHAR(255) NOT NULL DEFAULT '0',
	`level` VARCHAR(255) NOT NULL DEFAULT '0',
	`val` VARCHAR(255) NOT NULL DEFAULT '0',
	`user_group` VARCHAR(255) NOT NULL DEFAULT '2',
	`time` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_earnings` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`earned_from` INT(11) NOT NULL,
	`time` VARCHAR(255) NOT NULL,
	`ptype` VARCHAR(255) NOT NULL,
	`amount` VARCHAR(255) NOT NULL,
	`percent` VARCHAR(255) NOT NULL,
	`com_amt` VARCHAR(255) NOT NULL,
	`com_points` VARCHAR(255) NOT NULL,
	`reason` VARCHAR(255) NOT NULL,
	`status` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_gainers` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`level` INT(11) NOT NULL DEFAULT '0',
	`percent` FLOAT NOT NULL DEFAULT '0',
	`time` VARCHAR(255) NOT NULL DEFAULT '0',
	`reffered` INT(11) NOT NULL DEFAULT '0',
	`ptype` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_network` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`ref_id` INT(11) NOT NULL DEFAULT '0',
	`parent_id` INT(11) NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`time` VARCHAR(255) NOT NULL DEFAULT '0',
	`link` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_requests` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`request_time` VARCHAR(255) NOT NULL DEFAULT '0',
	`request_message` VARCHAR(255) NOT NULL DEFAULT '0',
	`amount` VARCHAR(255) NOT NULL DEFAULT '0',
	`points` VARCHAR(255) NOT NULL DEFAULT '0',
	`response_time` VARCHAR(255) NOT NULL DEFAULT '0',
	`response_message` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_tc` (
	`id` INT(11) NOT NULL,
	`content` TEXT NULL,
	`image` TEXT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;");

    $db->query("CREATE TABLE IF NOT EXISTS `aff_url_links` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`key_slug` VARCHAR(255) NOT NULL DEFAULT '0',
	`link` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `key_slug` (`key_slug`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");

    $txt = "Terms and Condition";
    $img = null;
    db()->query("INSERT INTO aff_tc (id,content,image) VALUES ('1','{$txt}','{$img}')");
}