<?php
function onlinetv_install_database() {
	$db = db();

	$db->query("CREATE TABLE IF NOT EXISTS `onlinetv_paid` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`tv_id` INT(11) NOT NULL DEFAULT '0',
	`time` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
");

	$db->query("CREATE TABLE IF NOT EXISTS `onlinetvs` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`category_id` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`entity_type` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`entity_id` INT(11) NOT NULL,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`slug` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`source_url` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`source` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`country` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`description` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`source_embed` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`image` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`tags` TEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`status` INT(11) NOT NULL DEFAULT '1',
	`views` INT(11) NOT NULL DEFAULT '0',
	`privacy` INT(11) NOT NULL DEFAULT '1',
	`update_time` INT(11) NOT NULL,
	`time` INT(11) NOT NULL,
	`featured` TINYINT(1) NOT NULL DEFAULT '0',
	`price` FLOAT NOT NULL DEFAULT '0',
	`import` FLOAT NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");

	$db->query("CREATE TABLE IF NOT EXISTS `onlinetv_categories` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`category_order` INT(11) NOT NULL,
	`parent_id` TINYINT(50) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;");
	//echo "yeah";die();

	/** @var $title */
	if($db->query("SELECT COUNT(id) FROM onlinetv_categories")->fetch_row()[0] == 0) {
		$preloaded_categories = array('Arts and Culture','Health','Business','Technology',
            'Sports', 'Horror', 'Entertainment', 'Music','Comedy','Kids','Documentary','News TV','LifeStyle');
		$i = 1;
		foreach($preloaded_categories as $preloaded_category) {
			foreach(get_all_languages() as $language) {
				$post_vars['title'][$language['language_id']] = $preloaded_category;
			}
			$expected = array('title' => '');
			extract(array_merge($expected, $post_vars));
			$titleSlug = 'onlinetv_category_'.md5(time().serialize($post_vars)).'_title';
			foreach($title as $langId => $t) {
				add_language_phrase($titleSlug, $t, $langId, 'onlinetv');
			}
			foreach($title as $langId => $t) {
				(phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'onlinetv') : add_language_phrase($titleSlug, $t, $langId, 'onlinetv');
			}
			$db->query("INSERT INTO onlinetv_categories(title, category_order) VALUES('".$titleSlug."', '".$i."')");
			$i++;
		}
	}
}