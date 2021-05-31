<?php

function mailautomation_install_database() {
    db()->query("CREATE TABLE IF NOT EXISTS `mailautomations` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`time` INT(11) NOT NULL DEFAULT '0',
	`na_count` INT(11) NOT NULL DEFAULT '0',
	`title` VARCHAR(255) NOT NULL DEFAULT '0',
	`slug` VARCHAR(255) NOT NULL DEFAULT '0',
	`subject` VARCHAR(255) NOT NULL DEFAULT '0',
	`body_content` VARCHAR(255) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");

    db()->query("CREATE TABLE IF NOT EXISTS `mailautomation_stats` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`subject` VARCHAR(255) NOT NULL DEFAULT '0',
	`matype` VARCHAR(255) NOT NULL DEFAULT 'auto',
	`email` VARCHAR(255) NOT NULL DEFAULT '0',
	`status` VARCHAR(255) NOT NULL DEFAULT '0',
	`time` VARCHAR(255) NOT NULL DEFAULT '0',
	`date_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");
}