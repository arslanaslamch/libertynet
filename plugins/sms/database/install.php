<?php

function sms_install_database(){
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS `sms_verification` (
	`s_id` INT(11) NOT NULL AUTO_INCREMENT,
	`verified` TINYINT(4) NOT NULL DEFAULT '0',
	`user_id` INT(11) NULL DEFAULT NULL,
	`phone_num` VARCHAR(50) NULL DEFAULT NULL,
	`scode` VARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY (`s_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");
}