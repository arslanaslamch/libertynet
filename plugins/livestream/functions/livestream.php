<?php

class Livestream {
    public static function getICEServers($type = null, $active = null, $json = false) {
        $db = db();
        $sql = "SELECT * FROM `livestream_ice_servers` WHERE 1";
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
							if($result['status'] === 200) {								
								$ice_servers = $result['ice_servers'];
								foreach ($ice_servers as $index => $ice_server) {
									if(isset($ice_servers[$index]['url'])) {
										$ice_servers[$index]['urls'] = $ice_servers[$index]['url'];
										unset($ice_servers[$index]['url']);
									}
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
        $sql = "SELECT * FROM `livestream_ice_servers` WHERE id = ".$id;
        $query = $db->query($sql);
        $row = $query->fetch_assoc();
        $server = $row;
        return $server;
    }

    public static function addICEServer($server) {
        $fields = array_keys($server);
        $data = array_values($server);
        $db = db();
        $sql = "INSERT INTO `livestream_ice_servers` (`".implode('`, `', $fields)."`) VALUES ('".implode('\', \'', $data)."')";
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
        $sql = "UPDATE `livestream_ice_servers` SET ".implode(', ', $data)." WHERE `id` = ".$id;
        $query = $db->query($sql);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteICEServer($id) {
        $db = db();
        $sql = "DELETE FROM `livestream_ice_servers` WHERE `id` = ".$id;
        $query = $db->query($sql);
        return $query ? true : false;
    }

    /**
     * Generates unique token
     * @return string
     */
    public static function generateToken() {
        $token = md5(mt_rand(0, 9999).time().mt_rand(0, 9999).get_userid().mt_rand(0, 9999));
        return $token;
    }

    function _construct() {

    }

    /**
     * Gets the list of livestream categories
     * @param int $parent_id|null
     * @return array
     */
    public static function getCategories($parent_id = null) {
        if(isset($parent_id) && $parent_id) {
            $db = db();
            $sql = "SELECT * FROM `livestream_categories` WHERE parent_id = ".$parent_id." ORDER BY `order`";
            $query = $db->query($sql);
            $categories = fetch_all($query);
        } else {
            $db = db();
            $sql = "SELECT * FROM `livestream_categories` ORDER BY `order`";
            $query = $db->query($sql);
            $categories = fetch_all($query);
        }
        return $categories;
    }

    /**
     * Gets a livestream category
     * @param int $category_id
     * @return array|bool
     */
    public static function getCategory($category_id) {
        $db = db();
        $sql = "SELECT * FROM `livestream_categories` WHERE `id` = '{$category_id}'";
        $query = $db->query($sql);
        $num_rows = $query->num_rows;
        $category = $query->fetch_assoc();
        if(!$num_rows) {
            $category = false;
        }
        return $category;
    }

    /**
     * Add a category for livestreams
     * @param array $livestream
     * @return bool
     */
    public static function addCategory($livestream) {
        if(is_admin()) {
            $db = db();
            $title_slug = "livestream_category_".md5(time().serialize($livestream)).'_title';
            $title = $livestream['title'];
            $t = '';
            foreach($title as $lang_id => $t) {
                add_language_phrase($title_slug, $t, $lang_id, 'livestream');
            }
            $slug = unique_slugger($t);
            $parent_id = $livestream['parent_id'];
            $order = 0;
            $query = $db->query("INSERT INTO `livestream_categories`(`slug`, `title`, `parent_id`, `order`) VALUES('".$slug."', '".$title_slug."', ".$parent_id.", ".$order.")");
            if($query) {
                fire_hook('livestream.category.added', $db->insert_id);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Edits a livestream Category
     * @param array $livestream
     * @return bool
     */
    public static function editCategory($livestream) {
        if(is_admin()) {
            $db = db();
            $category = Livestream::getCategory($livestream['id']);
            $title_slug = $category['title'];
            $slug = isset($livestream['slug']) ? $livestream['slug'] : $category['slug'];
            $title = isset($livestream['title']) ? $livestream['title'] : array();
            $parent_id = isset($livestream['parent_id']) ? $livestream['parent_id'] : $category['parent_id'];
            $order = isset($livestream['order']) ? $livestream['order'] : $category['order'];
            foreach($title as $lang_id => $t) {
                phrase_exists($lang_id, $title_slug) ? update_language_phrase($title_slug, $t, $lang_id) : add_language_phrase($title_slug, $t, $lang_id, 'livestream');
            }
            $query = $db->query("UPDATE `livestream_categories` SET `slug` = '".$slug."', `parent_id` = ".$parent_id.", `order` = ".$order." WHERE `id` = ".$livestream['id']);
            if($query) {
                fire_hook('livestream.category.edited', $livestream['id']);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function orderCategory($id, $order) {
        $db = db();
        $query = $db->query("UPDATE `livestream_categories` SET `order` = '".$order."' WHERE `id` = '".$id."'");
        if($query) {
            fire_hook('livestream.category.ordered', $id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes a livestream Category
     * @param $id
     * @param null $new_id
     * @return bool
     */
    public static function deleteCategory($id, $new_id = null) {
        $db = db();
        $category = Livestream::getCategory($id);
        delete_all_language_phrase($category['title']);
        if(!$new_id) {
            $categories = Livestream::getCategories();
            $new_id = isset($categories[0]) ? $categories[0]['id'] : $id;
        }
        $query = $db->query("DELETE FROM `livestream_categories` WHERE `id` = ".$id);
        if($query) {
            $query = $db->query("UPDATE `livestreams` SET `category_id` = ".$new_id." WHERE `category_id` = ".$id);
            if($query) {
                fire_hook('livestream.category.deleted', $id);
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a livestream category exist
     * @param int $category_id
     * @return bool
     */
    public static function categoryExists($category_id) {
        $db = db();
        $sql = "SELECT `id` FROM `livestream_categories` WHERE `id` = ".$category_id;
        $query = $db->query($sql);
        $num_rows = $query->num_rows;
        $result = $num_rows ? true : false;
        return $result;
    }

    /**
     * Gets list of livestreams
     * @param array|null $filter
     * @param int|null $limit
     * @return Paginator
     */
    public static function getLivestreams($filter = array(), $limit = null) {
        $filter = isset($filter) ? $filter : array();
        $limit = isset($limit) ? $limit : config('livestreams-listing-limit', 20);
        $expected_filter = array('category_id' => null, 'user_id' => null, 'term' => null, 'featured' => null, 'id' => null, 'type' => null, 'status' => null);
        extract(array_merge($expected_filter, $filter));
        $where_sql = "";
        if(isset($category_id) && is_numeric($category_id)) {
            $where_sql .= " AND livestreams.category_id = ".$category_id;
        }
        if(isset($user_id)) {
            $where_sql .= " AND livestreams.user_id = ".$user_id;
        }
        if(isset($term)) {
            $where_sql .= " AND (livestreams.title LIKE '%".$term."%' OR livestreams.description LIKE '%".$term."%')";
        }
        if(isset($featured)) {
            $where_sql .= " AND livestreams.featured = ".$featured;
        }
        if(isset($type)) {
            $where_sql .= " AND livestreams.type = '".$type."'";
        }
        if(isset($id)) {
            $where_sql .= " AND (livestreams.id = '".$id."' OR livestreams.slug = '".$id."')";
        }
        if(isset($status)) {
            if(is_numeric($status)) {
                $where_sql .= " AND status = ".$status;
            }
        } else {
            $where_sql .= " AND status > 0";
        }
        $privacy_sql = fire_hook('privacy.sql', ' ');
        if($privacy_sql) {
            $where_sql .= " AND (".$privacy_sql.")";
        }
        if($where_sql) {
            $where_sql = " WHERE 1 ".$where_sql;
        }
        $sql = "SELECT livestreams.id, livestreams.slug, livestreams.title, livestreams.description, livestreams.category_id, livestreams.type, livestreams.token, livestreams.start_time, UNIX_TIMESTAMP(livestreams.start_time) AS start_timestamp, livestreams.end_time, livestreams.image, livestreams.path, livestreams.featured, livestreams.user_id, livestreams.entity_type, livestreams.entity_id, livestreams.privacy, livestreams.time, livestreams.status, livestreams.category_id, livestream_categories.id AS category_id, livestream_categories.title AS category_title, users.username, users.first_name, users.last_name, users.avatar, (SELECT COUNT(livestream_viewers.id) FROM livestream_viewers WHERE livestream_viewers.livestream_id = livestreams.id AND bot = 0) AS views FROM livestreams LEFT JOIN users ON livestreams.user_id = users.id LEFT JOIN livestream_categories ON livestreams.category_id = livestream_categories.id".$where_sql." ORDER BY livestreams.time DESC";
        $livestreams = paginate($sql, $limit, 7);
        return $livestreams;
    }

    /**
     * Gets a livestream
     * @param int $livestream_id
     * @return array|bool
     */
    public static function get($livestream_id) {
        $livestream = false;
        $paginator = Livestream::getLivestreams(array('id' => $livestream_id, 'status' => '*'));
        foreach($paginator->results() as $livestream) {
            break;
        }
        return $livestream;
    }

    /**
     * Adds a livestream
     * @param array $livestream
     * @return int|bool
     */
    public static function add($livestream) {
        /**
         * @var int $entity_type
         * @var int $entity_id
         */
        if(isset($livestream['entity'])) {
            $entity = explode('-', $livestream['entity']);
            if(count($entity) == 2) {
                $entity_type = $entity[0];
                $entity_id = $entity[1];
            }
        }
        if(!isset($entity_type) || !isset($entity_id)) {
            return false;
        }
        $db = db();
        $slug = is_admin() && isset($livestream['slug']) && $livestream['slug'] ? $livestream['slug'] : unique_slugger($livestream['title']);
        $user_id = is_admin() && isset($livestream['user_id']) ? $livestream['user_id'] : get_userid();
        $featured = is_admin() && isset($livestream['featured']) ? $livestream['featured'] : 0;
        $sql = "INSERT INTO `livestreams`(`slug`, `title`, `description`, `category_id`, `type`, `token`, `start_time`, `end_time`, `image`, `featured`, `user_id`, `entity_type`, `entity_id`, `privacy`, `time`, `status`) VALUES('".$slug."', '".$livestream['title']."', '".$livestream['description']."', ".$livestream['category_id'].", '".$livestream['type']."', '".$livestream['token']."', NOW(), NOW(), '".$livestream['image']."', ".$featured.", ".$user_id.", '".$entity_type."', ".$entity_id.", ".$livestream['privacy'].", NOW(), 1)";
        $query = $db->query($sql);
        if($query) {
            fire_hook('livestream.added', $db->insert_id);
            return $db->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Edits a livestream
     * @param array $livestream
     * @return bool
     */
    public static function edit($livestream) {
        if(Livestream::isOwner($livestream['id']) || is_admin()) {
            $db = db();
            $livestream['livestream'] = isset($livestream['livestream']) ? $livestream['livestream'] : Livestream::get($livestream['id']);
            $livestream['title'] = isset($livestream['title']) && $livestream['title'] ? $livestream['title'] : $livestream['livestream']['title'];
            $livestream['description'] = isset($livestream['description']) && $livestream['description'] ? $livestream['description'] : $livestream['livestream']['description'];
            $livestream['category_id'] = isset($livestream['category_id']) && $livestream['category_id'] ? $livestream['category_id'] : $livestream['livestream']['category_id'];
            $livestream['slug'] = is_admin() && isset($livestream['slug']) && $livestream['slug'] ? $livestream['slug'] : unique_slugger($livestream['title'], 'livestream', $livestream['id']);
            $livestream['user_id'] = is_admin() && isset($livestream['user_id']) ? $livestream['user_id'] : $livestream['livestream']['user_id'];
            $livestream['featured'] = is_admin() && isset($livestream['featured']) ? $livestream['featured'] : $livestream['livestream']['featured'];
            $livestream['image'] = isset($livestream['image']) ? $livestream['image'] : $livestream['livestream']['image'];
            $livestream['privacy'] = isset($livestream['privacy']) ? $livestream['privacy'] : $livestream['livestream']['privacy'];
            $livestream['featured'] = isset($livestream['featured']) && $livestream['featured'] ? $livestream['featured'] : $livestream['livestream']['featured'];
            $livestream['start_time'] = isset($livestream['start_time']) ? $livestream['start_time'] : $livestream['livestream']['start_time'];
            $sql = "UPDATE `livestreams` SET `slug` = '".$livestream['slug']."', `title` = '".$livestream['title']."', `description` = '".$livestream['description']."', `category_id` = ".$livestream['category_id'].", `image` = '".$livestream['image']."', `featured` = ".$livestream['featured'].", `user_id` = ".$livestream['user_id'].", `privacy` = ".$livestream['privacy'].", `start_time` = ".$livestream['start_time']." WHERE `id` = ".$livestream['id'];
            $query = $db->query($sql);
            if($query) {
                fire_hook('livestream.edited', $livestream['id']);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Deletes a livestream
     * @param int $livestream_id
     * @return bool
     */
    public static function delete($livestream_id) {
        $livestream = Livestream::get($livestream_id);
        if(Livestream::isOwner($livestream_id) || is_admin()) {
            $db = db();
            $db->query("DELETE FROM `livestreams` WHERE `id` = ".$livestream_id);
            if($livestream['image']) {
                delete_file(path($livestream['image']));
            }
            if($livestream['path']) {
                delete_file(path($livestream['path']));
            }
            fire_hook('livestream.deleted', $livestream_id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if a livestream exist
     * @param int $livestream_id
     * @return bool|mixed
     */
    public static function exists($livestream_id) {
        $db = db();
        $sql = "SELECT COUNT(id) FROM livestreams WHERE id = ".$livestream_id;
        $query = $db->query($sql);
        $row = $query->fetch_row();
        $result = (bool) $row[0];
        return $result;
    }

    /**
     * Checks if a user owns a livestream
     * @param int $livestream_id
     * @param int|null $user_id
     * @return bool
     */
    public static function isOwner($livestream_id, $user_id = null) {
        $user_id = isset($user_id) ? $user_id : get_userid();
        $livestream = Livestream::get($livestream_id);
        return is_loggedIn() && $livestream && $livestream['user_id'] == $user_id ? true : false;
    }

    /**
     * Gets the number of livestreams
     * @param array $filter
     * @return int
     */
    public static function count($filter = array()) {
        $filter = isset($filter) ? $filter : array();
        $db = db();
        $expected_filter = array('category_id' => null, 'user_id' => null, 'term' => null, 'featured' => null);
        extract(array_merge($expected_filter, $filter));
        $where_sql = "";
        if(isset($category_id)) {
            $where_sql .= " AND livestreams.category_id = ".$category_id;
        }
        if(isset($user_id)) {
            $where_sql .= " AND livestreams.user_id = ".$user_id;
        }
        if(isset($term)) {
            $where_sql .= " AND (livestreams.title LIKE '%".$term."%' OR livestreams.description LIKE '%".$term."%')";
        }
        if(isset($featured)) {
            $where_sql .= " AND livestreams.featured = ".$featured;
        }
        if($where_sql) {
            $where_sql = " WHERE ".$where_sql;
        }
        $sql = "SELECT COUNT(`id`) FROM `livestreams`".$where_sql;
        $query = $db->query($sql);
        $row = $query->fetch_row();
        return $row[0];
    }

    /**
     * Gets livestream ID from slug.
     * @param string $slug
     * @return int|null
     */
    public static function getId($slug) {
        $db = db();
        $query = $db->query("SELECT `id` FROM `livestreams` WHERE `slug` = '".$slug."'");
        $row = $query->fetch_row();
        $id = isset($row[0]) ? $row[0] : null;
        return $id;
    }

    /**
     * Gets livestream slug from ID.
     * @param int $id
     * @return int|null
     */
    public static function getSlug($id) {
        $db = db();
        $query = $db->query("SELECT `slug` FROM `livestreams` WHERE `id` = '".$id."'");
        $row = $query->fetch_row();
        $id = isset($row[0]) ? $row[0] : null;
        return $id;
    }

    /**
     * On livestream view handler
     * @param $livestream_id
     * @return bool
     */
    public static function viewed($livestream_id) {
        $db = db();
        $user_id = get_userid();
        $user_id = $user_id ? $user_id : 0;
        $ip = get_ip();
        $bot = !isset($_SERVER['HTTP_USER_AGENT']) || isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']) ? 1 : 0;
        $sql = "SELECT COUNT(id) FROM livestream_viewers WHERE livestream_id = ".$livestream_id." AND ((user_id IS NOT NULL AND user_id = '".$user_id."') OR ip = '".$ip."')";
        $query = $db->query($sql);
        $row = $query->fetch_row();
        $viewed = (bool) $row[0];
        if(!$viewed) {
            $sql = "INSERT INTO livestream_viewers (livestream_id, user_id, ip, last_view_time, bot) VALUES (".$livestream_id.", ".$user_id.", '".$ip."', NOW(), ".$bot.")";
            $query = $db->query($sql);
            if($query) {
                fire_hook('livestream.viewed.unique', null, array($livestream_id));
            }
        }
        if($query) {
            $sql = "UPDATE livestream_viewers SET last_view_time = NOW() WHERE livestream_id = ".$livestream_id." AND ((user_id IS NOT NULL AND user_id = ".$user_id.") OR ip = '".$ip."')";
            $query = $db->query($sql);
        }
        fire_hook('livestream.viewed');
        return $query ? true : false;
    }

    /**
     * Gets livestream Host
     * @param array $livestream
     * @return array
     */
    public static function getHost($livestream) {
        $result = array('name' => '', 'image' => '', 'link' => '', 'id' => '');
        $entity = fire_hook('entity.info', $livestream);
        $result['name'] = $entity['name'];
        $result['image'] = $entity['avatar'];
        $result['link'] = url($entity['id']);
        $result['id'] = $entity['id'];
        return $result;
    }

    /**
     * Checks user against livestream privacy
     * @param array $livestream
     * @return bool
     */
    public static function canView($livestream) {
        $livestream_id = is_numeric($livestream) ? $livestream : false;
        $livestream_id = !$livestream_id && isset($livestream['id']) ? $livestream['id'] : $livestream_id;
        $result = $livestream_id ? self::get($livestream_id) : $livestream_id;
        return $result;
    }

    /**
     * Keep livestream active
     * @param int $id
     * @return bool
     */
    public static function keepAlive($id) {
        $db = db();
        $sql = "UPDATE `livestreams` SET `end_time` = NOW() WHERE `id` = ".$id." AND `status` = 1";
        $query = $db->query($sql);
        return $query ? true : false;
    }

    /**
     * Update status for inactive livestream
     * @return bool
     */
    public static function endInactive() {
        $db = db();
        $timeout = ((config('ajax-polling-interval', 5000) / 1000) * 2) + 300;
        $where_sql = " WHERE NOW() - `end_time` > ".$timeout." AND status = 1";
        $sql = "SELECT `id`, `user_id`, `privacy` FROM `livestreams`".$where_sql;
        $query = $db->query($sql);
        if($query) {
            while($row = $query->fetch_assoc()) {
                $data = array('livestream_id' => (integer) $row['id'], 'message' => lang('livestream::livestream-ended'), 'status' => 2, 'link' => null, 'player' => null);
                pusher()->sendMessage($row['user_id'], 'livestream.ended', $data, null, false);
                if($row['privacy'] < 3) {
                    $friends = get_friends();
                    $followers = get_followers();
                    $subscribers = array_unique(array_merge($friends, $followers), SORT_REGULAR);
                    foreach ($subscribers as $subscriber_id) {
                        if ($subscriber_id != $row['user_id']) {
                            send_notification($subscriber_id, 'livestream.ended', $row['id']);
                            pusher()->sendMessage($subscriber_id, 'livestream.ended', $data, null, false);
                        }
                    }
                }
            }
            $sql = "UPDATE `livestreams` SET status = 2".$where_sql;
            $query = $db->query($sql);
        }
        return $query ? true : false;
    }

    /**
     * Keep livestream active
     * @param int $id
     * @param string|null $record
     * @return array
     */
    public static function end($id, $record = null) {
        $db = db();
        $livestream = Livestream::get($id);
        $data = array('livestream_id' => null, 'message' => null, 'status' => 0, 'link' => null, 'player' => null);
        if($livestream) {
            $data['livestream_id'] = (integer) $id;
            $user_id = $livestream['user_id'];
            if($user_id == get_userid()) {
                $sql = "UPDATE `livestreams` SET status = 2 WHERE `id` = ".$id;
                $query = $db->query($sql);
                if($query) {
                    $data['status'] = 2;
                    $data['message'] = lang('livestream::livestream-ended-not-uploaded');
                    $uploader = null;
                    $path = get_userid().'/'.date('Y').'/livestream/videos/';
                    $livestream_path = null;
                    if(is_array($record)) {
                        $uploader = new Uploader($record, 'video');
                    } else {
                        list($header, $video) = array_pad(explode(',', $record), 2, '');
                        if($video) {
                            preg_match('/data\:video\/(.*?);base64/i', $header, $matches);
                            $default_extension = 'webm';
                            $video_file_extensions = explode(',', config('video-file-types', 'mp4,mov,wmv,3gp,avi,flv,f4v,webm'));
                            if(!in_array($default_extension, $video_file_extensions)) {
                                $video_file_extensions[] = $default_extension;
                            }
                            $extension = isset($matches[1]) ? $matches[1] : $default_extension;
                            if(in_array($extension, $video_file_extensions)) {
                                $video = base64_decode(str_replace(' ', '+', $video));
                                $temp_dir = config('temp-dir', path('storage/tmp')).'/livestream/videos';
                                $file_name = 'video_'.get_userid().'_'.time();
                                if(!is_dir($temp_dir)) {
                                    mkdir($temp_dir, 0777, true);
                                }
                                $temp_path = $temp_dir.'/'.$file_name.'.'.$extension;
                                file_put_contents($temp_path, $video);
                                $uploader = new Uploader($temp_path, 'video', true, true);
                            }
                        }
                    }
                    if($uploader) {
                        if ($uploader->passed()) {
                            $uploader->setPath($path)->disableCDN();
                            $uploader->uploadVideo();
                            $livestream_path = $uploader->result();
                        } else {
                            $data['message'] = $uploader->getError();
                        }
                    }
                    if($livestream_path) {
                        $sql = "UPDATE `livestreams` SET path = '".$livestream_path."', status = 3 WHERE `id` = ".$id;
                        $query = $db->query($sql);
                        if($query) {
                            $livestream = Livestream::get($id);
                            fire_hook('livestream.uploaded', $livestream);
                            $livestream = Livestream::get($id);
                            $data['status'] = 3;
                            $data['link'] = url($livestream['path']);
                            $data['message'] = lang('livestream::livestream-ended-uploaded');
                            $data['player'] = view('livestream::livestream/player', array('link' => $data['link'], 'photo' => url_img($livestream['image'], 920)));
                        }
                    }
                    pusher()->sendMessage($user_id, 'livestream.ended', $data, null, false);
                    if($livestream['privacy'] < 3) {
                        $message_data = $data;
                        $message_data['message'] = lang('livestream::livestream-ended');
                        $friends = get_friends();
                        $followers = get_followers();
                        $subscribers = array_unique(array_merge($friends, $followers), SORT_REGULAR);
                        foreach ($subscribers as $subscriber_id) {
                            if ($subscriber_id != $user_id) {
                                send_notification($subscriber_id, 'livestream.ended', $livestream['id']);
                                pusher()->sendMessage($subscriber_id, 'livestream.ended', $message_data, null, false);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    public static function processAll($force = false) {
        $db = db();
        $sql = "SELECT livestreams.*, (SELECT COUNT(livestream_viewers.id) FROM livestream_viewers WHERE livestream_viewers.livestream_id = livestreams.id AND bot = 0) AS views FROM livestreams WHERE path != ''";
        if(!$force) {
            $sql .= " AND path LIKE '%.webm'";
        }
        $query = $db->query($sql);
        while($livestream = $query->fetch_assoc()) {
            @Livestream::process($livestream);
        }
    }

    public static function process($livestream) {
        $status = 0;
        $ffmpeg = config('video-ffmpeg-path', 'ffmpeg');
		$vcodec = config('video-ffmpeg-video-codec', 'h264');
		$acodec = config('video-ffmpeg-audio-codec', 'aac');
        if (($ffmpeg == 'ffmpeg' && (file_exists(trim(shell_exec('which ffmpeg 2>&1'))) || file_exists(trim(shell_exec('where ffmpeg 2>&1'))))) || ($ffmpeg != 'ffmpeg' && file_exists($ffmpeg) && is_executable($ffmpeg))) {
            $file_path = $livestream['path'];
            $path_info = pathinfo($file_path);
            $output_file = $path_info['dirname'].'/'.$path_info['filename'].'_encoded.mp4';
            $i = 0;
            while(file_exists($output_file)) {
                $output_file = $path_info['dirname'].'/'.$path_info['filename'].'_encoded_'.$i.'.mp4';
                $i++;
            }
            if ($file_path and file_exists(path($file_path))) {
                if (config('video-encoder') == 'ffmpeg') {
					exec('"'.$ffmpeg.'" -y -i "'.path($file_path).'" -vcodec '.$vcodec.' -level:v 3.0 -acodec '.$acodec.' -strict -2 "'.path($output_file).'"');
                }
                $db = db();
                if (file_exists(path($output_file)) && filesize(path($output_file))) {
                    if (path($file_path) != path($output_file)) {
                        delete_file(path($file_path));
                        $db->query("UPDATE livestreams SET path = '".$output_file."' WHERE id = ".$livestream['id']);
                        $status = 1;
                    }
                } elseif(file_exists(path($output_file))) {
                    delete_file(path($output_file));
                }
                if (!$status && config('video-encoder') == 'ffmpeg' && config('ignore-ffmpeg', false)) {
                    @rename(path($file_path), path($output_file));
                    $db->query("UPDATE livestreams SET path = '".$output_file."' WHERE id = ".$livestream['id']);
                    $status = 1;
                }
                if ($status) {
                    if(!config('enable-auto-video-processing')) {
                        send_notification($livestream['user_id'], 'livestream.processed', $livestream['id']);
                    }
                    fire_hook('livestream.processed', null, array($livestream, $livestream['id']));
                }
            }
        }
        return $status;
    }
}