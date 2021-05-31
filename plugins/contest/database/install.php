<?php

function contest_install_database() {

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_announcement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `contest_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '0',
  `link` varchar(255) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `contest_id` int(11) NOT NULL DEFAULT '0',
  `time` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '0',
  `ref_name` varchar(255) NOT NULL DEFAULT 'blog_entry_contest',
  `slug` varchar(255) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `image` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `category_order` int(11) NOT NULL,
  `parent_id` tinyint(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


    db()->query("CREATE TABLE IF NOT EXISTS  `contest_followers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `contest_id` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `time` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '0',
  `ref_name` varchar(255) NOT NULL DEFAULT 'music_entry_contest',
  `slug` varchar(255) NOT NULL DEFAULT '0',
  `source` varchar(255) NOT NULL DEFAULT '0',
  `image` text NOT NULL,
  `description` text NOT NULL,
  `file_path` text NOT NULL,
  `code` text,
  `thumbnail` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `contest_id` int(11) NOT NULL DEFAULT '0',
  `time` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '0',
  `ref_name` varchar(255) NOT NULL DEFAULT 'photo_entry_contest',
  `slug` varchar(255) NOT NULL DEFAULT '0',
  `image` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `contest_id` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `time` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '0',
  `ref_name` varchar(255) NOT NULL DEFAULT 'video_entry_contest',
  `slug` varchar(255) NOT NULL DEFAULT '0',
  `source` varchar(255) NOT NULL DEFAULT '0',
  `image` text NOT NULL,
  `description` text NOT NULL,
  `file_path` text NOT NULL,
  `code` text,
  `thumbnail` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS  `contest_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL DEFAULT '0',
  `voter` int(11) NOT NULL DEFAULT '0',
  `entry_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `entry_type` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;");

    db()->query("CREATE TABLE IF NOT EXISTS `contests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `award` text COLLATE utf8_unicode_ci NOT NULL,
  `terms` text COLLATE utf8_unicode_ci NOT NULL,
  `contest_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contest_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entries_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entries_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `voting_start` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `voting_end` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `who_vote` tinyint(4) NOT NULL,
  `max_entries` tinyint(4) NOT NULL,
  `winners` tinyint(4) NOT NULL,
  `auto_approve` tinyint(4) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entity_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `views` int(11) NOT NULL DEFAULT '0',
  `privacy` int(11) NOT NULL DEFAULT '1',
  `update_time` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `join_fee` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
}