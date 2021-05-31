<?php
function get_encounters(array $filters = [])
{
    /**
     * @var $miles
     * @var string $gender
     * @var $min_age
     * @var $max_age
     * 
     */
    $expected = ['miles' => config("default-miles"), 'gender' => '', 'max_age' => 100, 'min_age' => 18];
    $filters = array_merge($expected, $filters);
    extract($filters);
    $min_age = $min_age < 18 ? 18 : $min_age;
    $where = get_where_string(['gender' => $gender]);
    $db = db();
    $id = get_userid();
    $query = $db->query("SELECT p.id, username, first_name, last_name, gender, country, avatar, city, email_address, ip_address, role, online_time, lon, lat, birth_year  FROM `users` p INNER JOIN `user_locations` c ON c.user_id = p.id $where AND p.id != $id");
    if ($query) {
        return get_distance_from_user($filters, fetch_all($query));
    }
    return [];
}

function get_distance_from_user($filters, array $users)
{
    foreach ($users as $key => $user) {
        if ($loc = get_location($user['id'])) {
            $users[$key]['distance'] = get_distance(get_location(), $loc, config("default-unit"));
        } else {
            $users[$key]['distance'] = -1;
        }
    }

    return filter_encounters($users, $filters);
}

function filter_encounters($users, $filters)
{
    /**
     * @var $miles
     * @var $min_age
     * @var $max_age
     */
    extract($filters);

    return array_filter($users, function ($user) use ($miles, $min_age, $max_age) {
        if (!($user['distance'] > 0) || !($user['distance'] <= $miles)) return false;
        if (get_age($user['birth_year']) < $min_age || get_age($user['birth_year']) > $max_age) return false;
        if (has_liked_by(get_userid(), $user['id'])) return false;
        return true;
    });
}

function get_where_string(array $conditions, $operator = "AND")
{
    if (isset($conditions['gender']) && $conditions['gender'] == 'both') return '';
    $where = " WHERE ";
    $i = 0;
    foreach ($conditions as $field => $value) {
        if ($value !== '') {
            if ($i == 0) {
                $where .= "`$field` = '$value'";
            } else {
                $where .= " $operator `$field` = '$value'";
            }
        }

        $i++;
    }

    return $where == " WHERE " ? "" : $where;
}

function get_expected_gender()
{
    if (get_user_data('gender') == 'male') {
        return 'female';
    }
    return 'male';
}

function convert_distance($distance, string $from, string $unit)
{
    switch (strtoupper($from)) {
        case 'K':
            $distance = $distance / 1.609344;
            if ($unit == "K") {
                return ($distance * 1.609344);
            } else if ($unit == "N") {
                return ($distance * 0.8684);
            } else {
                return $distance;
            }
            break;
        case 'N':
            $distance = $distance / 0.8684;
            if ($unit == "K") {
                return ($distance * 1.609344);
            } else if ($unit == "N") {
                return ($distance * 0.8684);
            } else {
                return $distance;
            }
            break;
        default:
            if ($unit == "K") {
                return ($distance * 1.609344);
            } else if ($unit == "N") {
                return ($distance * 0.8684);
            } else {
                return $distance;
            }
            break;
    }
}

function get_age(int $year)
{
    return date('Y') - $year;
}

function like_encounter($user_id)
{
    $db = db();
    $auth_id = get_userid();
    if (has_liked_by($auth_id, $user_id)) return true;
    $date = date('Y-m-d H:i:s');
    $query = $db->query("INSERT INTO `liked_by` (`user_id`,`liked_by`, `liked_at`) VALUES($user_id, $auth_id, '$date')");
    if ($query) {
        send_notification($user_id, 'matchmaker.liked', $auth_id);
        if (has_matched($user_id)) save_as_match($auth_id, $user_id);
        return true;
    }
    if ($db->error) {
        $_SESSION['matchmaker_error'] = $db->error;
    }
    return false;
}

function save_as_match($user1, $user2)
{
    $db = db();
    $date = date('Y-m-d H:i:s');
    $query = $db->query("INSERT INTO `matches`(`user_1`, `user_2`, `matched_at`) VALUES($user1, $user2, '$date')");
    if ($query) {
        send_notification($user1, 'matchmaker.matched', $user2);
        send_notification($user2, 'matchmaker.matched', $user1);
        return $db->insert_id;
    }
    return false;
}

/**
 * Check if $user1 has liked $user2
 *
 * @param int $user1 User that is performing like action
 * @param int $user2 User action is being performed on
 * @return boolean
 */
function has_liked_by($user1, $user2)
{
    $db = db();
    $query = $db->query("SELECT `id` FROM `liked_by` WHERE `user_id`=$user2 AND `liked_by`=$user1");
    if ($query && $query->num_rows > 0) {
        return true;
    }
    return false;
}

function has_matched($user_id)
{
    $auth_id = get_userid();
    if (has_liked_by($auth_id, $user_id) && has_liked_by($user_id, $auth_id)) return true;
    return false;
}

function matchmaker_get_matches()
{
    $auth_id = get_userid();
    $query = "SELECT * FROM `matches` WHERE `user_1` = $auth_id OR `user_2` = $auth_id";
    return get_matches_as_users($query);
}

function get_matches_as_users($query)
{
    $paginator = paginate($query, 12);
    $matches = $paginator->results();

    foreach ($matches as $key => $match) {
        $id = $match['user_1'] == get_userid() ? $match['user_2'] : $match['user_1'];
        $user = $matches[$key]['user'] = find_user($id);
        if ($loc = get_location($user['id'])) {
            $matches[$key]['user']['distance'] = get_distance(get_location(), $loc, config("default-unit"));
        } else {
            $matches[$key]['user']['distance'] = -1;
        }
    }
    $paginator->results = $matches;
    return $paginator;
}

function matchmaker_get_likes()
{
    $auth_id = get_userid();
    $db = db();
    $query = "SELECT `id`, `liked_by`, `liked_at` FROM `liked_by` WHERE `user_id`=$auth_id";
    return get_likes_as_users($query);
}

function get_likes_as_users($query)
{
    $paginator = paginate($query, 12);
    $likes = $paginator->results();

    foreach ($likes as $key => $like) {
        if (has_matched($like['liked_by'])) {
            unset($likes[$key]);
        } else {
            $user = $likes[$key]['user'] = find_user($like['liked_by']);

            if ($loc = get_location($user['id'])) {
                $likes[$key]['user']['distance'] = get_distance(get_location(), $loc, config("default-unit"));
            } else {
                $likes[$key]['user']['distance'] = -1;
            }
        }
    }
    $paginator->results = $likes;
    return new \Paging($paginator);
}

class Paging
{
    public $total;
    private $paginator;
    private $results;

    public function __construct($paginator)
    {
        $this->total = $paginator->total;
        $this->results = $paginator->results;
        $this->paginator = $paginator;
    }

    public function results()
    {
        return $this->results;
    }

    public function links()
    {
        return $this->paginator->links();
    }
}