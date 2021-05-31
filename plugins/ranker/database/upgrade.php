<?php
function ranker_upgrade_database() {
	$db = db();
	
	 try {
        $db->query("CREATE TABLE IF NOT EXISTS `rank_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `point` int(11) NOT NULL,
  `category_order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");
    } catch(Exception $e) {
    }
	
	 try {
        $db->query("CREATE TABLE IF NOT EXISTS `rank_points` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
   `user_id` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
   `point` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");
	$db->query("CREATE TABLE IF NOT EXISTS `rank_ranges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `range` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
    ");
	
    } catch(Exception $e) {
    }
	try {
		$db->query("ALTER TABLE `feeds` ADD `point` int(11) NOT NULL AFTER `video`");
	} catch(Exception $e) {
	}
	try {
		$db->query("ALTER TABLE `likes` ADD `point` int(11) NOT NULL AFTER `type_id`");
	} catch(Exception $e) {
	}
}