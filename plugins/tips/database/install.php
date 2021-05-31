<?php

function tips_install_database(){
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `tips` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`feed` INT NOT NULL DEFAULT '0',
	`profile` INT NOT NULL DEFAULT '0',
	`group` INT NOT NULL DEFAULT '0',
	`page` INT NOT NULL DEFAULT '0',
	`user_id` INT NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
");
}