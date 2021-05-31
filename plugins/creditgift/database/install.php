<?php
function creditgift_install_database() {
	$db = db();
	$db->query("CREATE TABLE IF NOT EXISTS `creditgift` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id`  int(11) unsigned NOT NULL,
      `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `amount`  int(11) unsigned NOT NULL,
      `date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query("CREATE TABLE IF NOT EXISTS `creditgift_rank` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `rank` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `credit`  int(11) unsigned NOT NULL,
        `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `rank_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query("CREATE TABLE IF NOT EXISTS `creditgift_plan` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `unit`  int(11) unsigned NOT NULL,
       `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
       `cost` int(11) unsigned NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query("CREATE TABLE IF NOT EXISTS `creditgift_sales` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `user_id` int(11) unsigned NOT NULL,
       `unit`  int(11) unsigned NOT NULL,
       `cost` int(11) unsigned NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	$db->query("ALTER TABLE users ADD `credit_balance`  int(11) unsigned NOT NULL");
	$db->query("ALTER TABLE users ADD `credit_spent`  int(11) unsigned NOT NULL");

	$db = db();
	$r1 = 'Rank 1';
	$des1 = 'This is for users whose credit falls within range of rank 1 credit rank';
	$r2 = 'Rank 2';
	$des2 = 'This is for users whose credit falls within range of rank 2 credit rank';
	$r3 = 'Rank 3';
	$des3 = 'This is for users whose credit falls within range of rank 3 credit rank';
	$r4 = 'Rank 4';
	$des4 = 'This is for users whose credit falls within range of rank 4 credit rank';
	$r5 = 'Rank 5';
	$des5 = 'This is for users whose credit falls within range of rank 5 credit rank';
	$db->query("INSERT INTO creditgift_rank (rank,credit,description) VALUES ('$r1','20','$des1')");
	$db->query("INSERT INTO creditgift_rank (rank,credit,description) VALUES ('$r2','50','$des2')");
	$db->query("INSERT INTO creditgift_rank (rank,credit,description) VALUES ('$r3','100','$des3')");
	$db->query("INSERT INTO creditgift_rank (rank,credit,description) VALUES ('$r4','200','$des4')");
	$db->query("INSERT INTO creditgift_rank (rank,credit,description) VALUES ('$r5','500','$des5')");

	//installation bonus for existing members which compesate for signups
	$bonus = config('creditgift-signup-bonus');
	$db->query("UPDATE users SET credit_balance='$bonus' WHERE id !=0 ");

	$db->query("CREATE TABLE IF NOT EXISTS `credit_plans` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `amount` int(11) NOT NULL,
        `price` int(11) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");

}