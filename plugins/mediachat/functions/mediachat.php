<?php

class MediaChat {

    public static $streamSupport = false;

    public static function makeCall($receiver_id, $type = 'voice') {
        $user_id = get_userid();
        $result = array(
            'status' => 0,
            'message' => '',
            'data' => array(
                'id' => 0,
                'type' => $type,
                'session_description' => '',
                'caller_id' => $user_id,
                'call_time' => time(),
                'seen' => 0,
                'seen_time' => null,
                'received' => 0,
                'receiver_id' => $receiver_id,
                'received_time' => null,
                'ended' => 0,
                'end_time' => null
            )
        );
        $db = db();
        $sql = "SELECT `id` FROM `mediachat_calls` WHERE ((`caller_id` = '".$user_id."' AND `receiver_id` = ".$receiver_id.") OR (`caller_id` = '".$receiver_id."' AND `receiver_id` = ".$user_id.")) AND ended = 0";
        $query = $db->query($sql);
        if($query->num_rows) {
            while($row = $query->fetch_row()) {
                MediaChat::endCall($row[0]);
            }
        }
        if($query) {
            $user = find_user($receiver_id);
            if(user_is_online($user)) {
                if(MediaChat::userBusy($receiver_id)) {
                    $result['message'] = lang('mediachat::user-busy');
                } else {
                    $result['message'] = lang('mediachat::connecting');
                    $sql = "INSERT INTO `mediachat_calls`(`caller_id`, `call_time`, `receiver_id`, `type`) VALUES(".$user_id.", NOW(), ".$receiver_id.", '".$type."')";
                    $query = $db->query($sql);
                    if($query) {
                        $id = $db->insert_id;
                        $result['status'] = 1;
                        $result['data'] = MediaChat::getCall($id);
                        fire_hook('incoming-call', null, array($receiver_id, $id, $type));
                    }
                }
            } else {
                $result['message'] = lang('mediachat::user-offline');
            }
        } else {
            $result['status'] = lang('mediachat::error-in-connection');
        }
        return $result;
    }

    public static function receiveCall($id) {
        $result = array(
            'status' => 0,
            'message' => '',
            'data' => array()
        );
        $db = db();
        $call = MediaChat::getCall($id);
        if($call) {
            $caller = find_user($call['caller_id']);
            if (user_is_online($caller)) {
                if (MediaChat::userBusy($call['caller_id'])) {
                    MediaChat::endCall($id);
                    $result['message'] = lang('mediachat::user-busy');
                } elseif ($call['received']) {
                    $result['message'] = lang('mediachat::already-connected');
                } elseif ($call['ended']) {
                    $result['message'] = lang('mediachat::call-ended');
                } else {
                    $sql = "UPDATE `mediachat_calls` SET received = 1, received_time = NOW() WHERE `id` = ".$id;
                    $query = $db->query($sql);
                    if ($query) {
                        $result['status'] = 1;
                        $result['message'] = lang('mediachat::connecting');
                    } else {
                        $result['status'] = lang('mediachat::error-in-connection');
                    }
                }
            } else {
                $result['status'] = lang('mediachat::user-offline');
            }
            $result['data'] = MediaChat::getCall($id);
        }
        return $result;
    }

    public static function setSessionDescription($id, $session_description) {
        $db = db();
        $sql = "UPDATE `mediachat_calls` SET session_description = '".$db->real_escape_string($session_description)."', call_time = NOW() WHERE `id` = ".$id;
        $result = $db->query($sql);
		fire_hook('mediachat.call.session.description', array('status' => $result), array($id, $session_description));
        return $result ? true : false;
    }

    public static function seeCall($id) {
        $db = db();
        $user_id = get_userid();
        $sql = "UPDATE `mediachat_calls` SET `seen` = 1, seen_time = NOW() WHERE `id` = ".$id." AND `receiver_id` = ".$user_id;
        $query = $db->query($sql);
        return $query ? true : false;
    }

    public static function endCall($id) {
        $db = db();
        $sql = "UPDATE `mediachat_calls` SET `ended` = 1, `end_time` = NOW() WHERE `id` = '".$id."'";
        $query = $db->query($sql);
        $call = MediaChat::getCall($id);
        pusher()->sendMessage($call['caller_id'], 'mediachat.call.ended', array('id' => $call['id']), null, false);
        pusher()->sendMessage($call['receiver_id'], 'mediachat.call.ended', array('id' => $call['id']), null, false);
        return $query ? true : false;
    }

    public static function endMissedCalls() {
        $db = db();
        $connection_timeout = config('mediachat-connection-timeout', 60);
        $ajax_polling_interval = (config('ajax-polling-interval', 5000) / 1000);
        $delay = $ajax_polling_interval + ($connection_timeout * 2);
        $sql = "SELECT `id` FROM `mediachat_calls` WHERE `session_description` IS NOT NULL AND `received` = 0 AND `ended` = 0 AND (NOW() - call_time) > ".$delay;
        $query = $db->query($sql);
        if($query) {
            while($row = $query->fetch_row()) {
                MediaChat::endCall($row[0]);
            }
        }
        return $query ? true : false;
    }

    public static function getCall($id) {
        $db = db();
        $sql = "SELECT * FROM `mediachat_calls` WHERE `id` = ".$id;
        $query = $db->query($sql);
        return $query->fetch_assoc();
    }

    public static function userBusy($user_id = null) {
        $user_id = isset($user_id) ? $user_id : get_userid();
        $db = db();
        $sql = "SELECT COUNT(`id`) FROM `mediachat_calls` WHERE `receiver_id` = '".$user_id."' AND `seen` = 1 AND `ended` = 0";
        $query = $db->query($sql);
		if($query) {
			$row = $query->fetch_row();
			return $row[0] ? true : false;
		}
    }

    public static function userPendingCalls($user_id = null) {
        $user_id = isset($user_id) ? $user_id : get_userid();
        $db = db();
        $connection_timeout = config('mediachat-connection-timeout', 60);
        $ajax_polling_interval = (config('ajax-polling-interval', 5000) / 1000);
        $delay = $ajax_polling_interval + ($connection_timeout * 2);
        $sql = "SELECT * FROM `mediachat_calls` WHERE `receiver_id` = ".$user_id." AND seen = 0 AND session_description IS NOT NULL AND (NOW() - call_time) < ".$delay." ORDER BY call_time";
        $query = $db->query($sql);
        return $query ? fetch_all($query) : false;
    }

    public static function getAvailableFriends() {
        $db = db();
        $available_friends  = array();
        $friends = array_merge(array(0), get_friends());
        $connection_timeout = config('mediachat-connection-timeout', 60);
        $ajax_polling_interval = (config('ajax-polling-interval', 5000) / 1000);
        $delay = $ajax_polling_interval + floor($connection_timeout / 2);
        $sql = "SELECT `id`, ((NOW() - support_streaming_on) < ".$delay.") AS available FROM `users` WHERE `id` IN (".implode(', ', $friends).")";
        $query = $db->query($sql);
        while($row = $query->fetch_assoc()) {
            $available_friends[$row['id']] = $row['available'];
        }
        return $available_friends;
    }

    public static function streamSupport($user_id = null) {
        $user_id = isset($user_id) ? $user_id : get_userid();
        $user_id = is_numeric($user_id) ? $user_id : 0;
        $db = db();
        $connection_timeout = config('mediachat-connection-timeout', 60);
        $ajax_polling_interval = (config('ajax-polling-interval', 5000) / 1000);
        $delay = $ajax_polling_interval + floor($connection_timeout / 2);
        $sql = "SELECT COUNT(`id`) FROM `users` WHERE `id` = ".$user_id." AND (NOW() - support_streaming_on) < ".$delay;
        $query = $db->query($sql);
        $row = $query->fetch_row();
        $user = find_user($user_id);
        return $row[0] ? true : false;
    }

    public static function streamSupportUpdate() {
        $db = db();
        $user_id = get_userid();
        $sql = "UPDATE `users` SET `support_streaming_on` = NOW() WHERE `id` = ".$user_id;
        $query = $db->query($sql);
        return $query ? true : false;
    }

    public static function sendICE($user_id, $id, $index, $ice) {
        if(config('pusher-driver', 'ajax') == 'ajax') {
            self::$ICEs[$user_id][$id][$index] = $ice;
        } else {
            pusher()->sendMessage($user_id, 'mediachat.call.ice.candidate', array(array('id' => (integer) $id, 'data' => $ice)), null, false);
        }
    }


    public static function getICEServers($type = null, $active = null, $json = false) {
        $db = db();
        $sql = "SELECT * FROM `mediachat_ice_servers` WHERE 1";
        if ($type) {
            $sql .= " AND type = '".$type."'";
        }
        if ($active) {
            $sql .= " AND active = 1";
        }
        $query = $db->query($sql);
        $servers = fetch_all($query);
        if($json) {
            $json = array();
            foreach ($servers as $server) {
                $urls = preg_replace_callback('~"[^"\\\\]*(?:\\\\.|[^"\\\\]*)*"(*SKIP)(*F)| \'([^\'\\\\]*(?:\\\\.|[^\'\\\\]*)*)\'~x', function($matches) {return '"' . preg_replace('~\\\\.(*SKIP)(*F)|"~', '\\"', $matches[1]) . '"';}, $server['url']);
                $urls_json = json_decode($urls, true);
                $urls = json_last_error() == JSON_ERROR_NONE ? $urls_json : $urls;
                $ice_server = array('urls' => $urls);
                if ($server['type'] == 'turn') {
                    if ($server['username']) {
                        $ice_server['username'] = $server['username'];
                    }
                    if ($server['credential']) {
                        $ice_server['credential'] = $server['credential'];
                    }
                    $ice_servers = array($ice_server);
                } elseif($server['type'] == 'turn') {
                    $ice_server['username'] = $server['username'];
                    $ice_server['credential'] = $server['credential'];
                    $ice_servers = array($ice_server);
                } elseif($server['type'] == 'twilio') {
                    $ice_server['urls'] = 'stun:global.stun.twilio.com:3478?transport=udp';
                    $ice_servers = array($ice_server);
                    if($server['username'] && $server['credential']) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'https://api.twilio.com/2010-04-01/Accounts/'.$server['username'].'/Tokens.json');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_USERPWD, $server['username'].':'.$server['credential']);
                        $response = curl_exec($ch);
                        if (!curl_errno($ch)) {
                            $result = json_decode($response, true);
                            $ice_servers = $result['ice_servers'];
                            foreach ($ice_servers as $index => $ice_server) {
                                if(isset($ice_servers[$index]['url'])) {
                                    $ice_servers[$index]['urls'] = $ice_servers[$index]['url'];
                                    unset($ice_servers[$index]['url']);
                                }
                            }
                        }
                        curl_close ($ch);
                    }
                } elseif($server['type'] == 'xirsys') {
                    $ice_servers = array($ice_server);
                    if($server['username'] && $server['credential']) {
                        $ch = curl_init();
                        curl_setopt_array($ch, array(
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_URL => $server['url'],
                            CURLOPT_USERPWD => $server['username'].':'.$server['credential'],
                            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                            CURLOPT_CUSTOMREQUEST => 'PUT'
                        ));
                        $response = curl_exec($ch);
                        if (!curl_errno($ch)) {
                            $result = json_decode($response, true);
                            $ice_servers = $result['iceServers'];
                            foreach ($ice_servers as $index => $ice_server) {
                                if(isset($ice_servers[$index]['url'])) {
                                    $ice_servers[$index]['urls'] = $ice_servers[$index]['url'];
                                    unset($ice_servers[$index]['url']);
                                }
                            }
                        }
                        curl_close ($ch);
                    }
                } else {
                    $ice_servers = array($ice_server);
                }
                $ice_servers = fire_hook('ice.servers', $ice_servers, array($server, $type, $active));
                foreach ($ice_servers as $ice_server) {
                    $json[] = $ice_server;
                }
            }
            $servers = $json;
        }
        return $servers;
    }


    public static function getICEServer($id) {
        $db = db();
        $sql = "SELECT * FROM `mediachat_ice_servers` WHERE id = ".$id;
        $query = $db->query($sql);
        $row = $query->fetch_assoc();
        $server = $row;
        return $server;
    }

    public static function addICEServer($server) {
        $fields = array_keys($server);
        $data = array_values($server);
        $db = db();
        $sql = "INSERT INTO `mediachat_ice_servers` (`".implode('`, `', $fields)."`) VALUES ('".implode('\', \'', $data)."')";
        $query = $db->query($sql);
        if ($query) {
            return $db->insert_id;
        } else {
            return false;
        }
    }
    
    public static function editICEServer($id, $server) {
        $data = array();
        foreach ($server as $key => $value) {
            $data[] = "`".$key."` = '".$value."'";
        }
        $db = db();
        $sql = "UPDATE `mediachat_ice_servers` SET ".implode(', ', $data)." WHERE `id` = ".$id;
        $query = $db->query($sql);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function deleteICEServer($id) {
        $db = db();
        $sql = "DELETE FROM `mediachat_ice_servers` WHERE `id` = ".$id;
        $query = $db->query($sql);
        return $query ? true : false;
    }
}
