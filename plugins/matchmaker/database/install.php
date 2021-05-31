<?php
function matchmaker_install_database()
{
    $db = db();

    $db->query("CREATE TABLE IF NOT EXISTS `matches` (
                    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
                    `user_1` INT(11) unsigned NOT NULL,
                    `user_2` INT(11) unsigned NOT NULL,
                    `matched_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

    $db->query("CREATE TABLE IF NOT EXISTS `liked_by` (
                    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` INT(11) unsigned NOT NULL,
                    `liked_by` INT(11) unsigned NOT NULL,
                    `liked_at` DATETIME NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

}
