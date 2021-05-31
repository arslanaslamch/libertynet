<?php

function educational_install_database() {
    db()->query("CREATE TABLE IF NOT EXISTS `lh_books` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`comment` INT(11) NOT NULL DEFAULT '0',
	`privacy` INT(11) NOT NULL DEFAULT '0',
	`featured` INT(11) NOT NULL DEFAULT '0',
	`views` INT(11) NOT NULL DEFAULT '0',
	`downloads` INT(11) NOT NULL DEFAULT '0',
	`price` FLOAT NOT NULL DEFAULT '0',
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`author` VARCHAR(255) NULL DEFAULT NULL,
	`genre` VARCHAR(255) NULL DEFAULT NULL,
	`category` VARCHAR(255) NULL DEFAULT NULL,
	`slug` VARCHAR(255) NULL DEFAULT NULL,
	`description` TEXT NULL,
	`image` TEXT NULL,
	`book` TEXT NULL,
	`time` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");

    db()->query("CREATE TABLE IF NOT EXISTS `books_categories` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`category_order` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");

    $cTableName = "`books_categories`";
    $sql = 'SELECT id FROM '.$cTableName;
    $q = db()->query($sql);
    if($q->num_rows < 1){
        $arr =  array('Children Literature', 'Comics','Verse Novels','Mythology','Jokes','Memoirs','Love Stories','Fantasy','Food','Fiction','Historical Literature','Science','Others');
        foreach($arr as $k=>$a){
            $titleSlug = "educational_category_".md5(time().serialize($arr).$k).'_title';

            foreach(get_all_languages() as $language){
                $langId = $language['language_id'];
                add_language_phrase($titleSlug, $a, $langId, 'educational');
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
}