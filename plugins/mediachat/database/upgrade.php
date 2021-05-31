<?php
function mediachat_upgrade_database() {
	$db = db();
	$db->query("CREATE TABLE IF NOT EXISTS `mediachat_calls` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `type` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL,
        `session_description` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `caller_id` INT(11) UNSIGNED NOT NULL,
        `call_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `seen` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
        `seen_time` TIMESTAMP NULL DEFAULT NULL,
        `received` TINYINT(1) UNSIGNED NOT NULL,
        `receiver_id` INT(11) UNSIGNED NOT NULL,
        `received_time` TIMESTAMP NULL DEFAULT NULL,
        `ended` TINYINT(1) UNSIGNED NOT NULL,
        `end_time` TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("CREATE TABLE IF NOT EXISTS `mediachat_ice_servers` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `url` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `type` VARCHAR(8) COLLATE utf8_unicode_ci NOT NULL,
        `username` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `credential` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
        `active` TINYINT(1) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    if($db->query("SELECT COUNT(id) FROM mediachat_ice_servers")->fetch_row()[0] == 0) {
        $db->query("INSERT INTO `mediachat_ice_servers` (url, type, username, credential, active) VALUES
        ('stun:stun.l.google.com:19302', 'stun', '', '', 1),
        ('stun:stun.services.mozilla.com:3478', 'stun', '', '', 1),
        ('turn:turn.anyfirewall.com:443?transport=tcp', 'turn', 'webrtc', 'webrtc', 1);");
    }

    try {
        $db->query("ALTER TABLE `users` ADD `support_streaming_on` timestamp NULL DEFAULT 0;");
    } catch(Exception $e) {}
}