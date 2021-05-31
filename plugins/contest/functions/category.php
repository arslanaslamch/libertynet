<?php

function contest_add_category($val) {
    $expected = array(
        'title' => '',
        'category' => ''
    );

    /**
     * @var $title
     * @var $desc
     * @var $category
     */
    extract(array_merge($expected, $val));
    $titleSlug = "contest_category_".md5(time().serialize($val)).'_title';
    foreach($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'contest');
    }
    $order = db()->query('SELECT id FROM contest_categories');
    $order = $order->num_rows;
    db()->query("INSERT INTO `contest_categories`(`title`,`category_order`,`parent_id`) VALUES('".$titleSlug."','".$order."','".$category."')");
    return true;
}

function save_contest_category($val, $category) {
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     */
    extract(array_merge($expected, $val));
    $titleSlug = $category['title'];

    foreach($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, ' contest') : add_language_phrase($titleSlug, $t, $langId, 'contest');
    }

    return true;
}

function get_contest_categories() {
    $query = db()->query("SELECT * FROM `contest_categories` WHERE parent_id ='0' ORDER BY `category_order` ASC");
    return fetch_all($query);
}
function get_contest_parent_categories($id) {
    $db = db()->query("SELECT * FROM `contest_categories` WHERE parent_id='{$id}' ORDER BY `category_order` ASC");
    $result = fetch_all($db);
    return $result;
}

function get_contest_category($id) {
    $query = db()->query("SELECT * FROM `contest_categories` WHERE `id`='".$id."'");
    return $query->fetch_assoc();
}

function delete_contest_category($id, $category) {
    delete_all_language_phrase($category['title']);
    db()->query("DELETE FROM `contest_categories` WHERE `id`='".$id."'");
    return true;
}

function update_contest_category_order($id, $order) {
    db()->query("UPDATE `contest_categories` SET `category_order`='".$order."' WHERE  `id`='".$id."'");
}