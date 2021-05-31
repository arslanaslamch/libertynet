<?php

function educational_upgrade_database() {
    register_site_page('books-page', array('title' => 'educational::books-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'books-page', 'content', 'middle');
        Widget::add(null, 'books-page', 'plugin::educational|menu', 'right');
        Widget::add(null, 'books-page', 'plugin::educational|search', 'right');
    });
    register_site_page('single-book', array('title' => 'educational::single-book', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'single-book', 'content', 'middle');
        Widget::add(null, 'single-book', 'plugin::educational|menu', 'right');
        Widget::add(null, 'single-book', 'plugin::educational|related', 'right');
        Widget::add(null, 'single-book', 'plugin::educational|search', 'right');
    });
	
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

}