<?php

function contest_upgrade_database() {
    register_site_page('contests', array('title' => 'contest::contests', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'contests', 'content', 'middle');
        Widget::add(null, 'contests', 'plugin::contest|menu', 'left');
        Widget::add(null, 'contests', 'plugin::contest|categories', 'left');
    });

    register_site_page('contests-my-entries', array('title' => 'contest::my-contest-entries', 'column_type' => TWO_COLUMN_LEFT_LAYOUT), function() {
        Widget::add(null, 'contests-my-entries', 'content', 'middle');
        Widget::add(null, 'contests-my-entries', 'plugin::contest|menu', 'left');
        Widget::add(null, 'contests-my-entries', 'plugin::contest|categories', 'left');
    });
    register_site_page('contest-add', array('title' => 'contest::contests-add-page', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'contest-add', 'content', 'middle');
    });
    /*
    register_site_page('contest-manage', array('title' => 'contest::manage-contests', 'column_type' => ONE_COLUMN_LAYOUT), function() {
        Widget::add(null, 'contest-manage', 'content', 'middle');
    });
    register_site_page('contest-page', array('title' => 'contest::contest-view-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null, 'contest-page', 'content', 'middle');
        Widget::add(null, 'contest-page', 'plugin::contest|menu', 'right');
        Widget::add(null, 'contest-page', 'plugin::contest|related', 'right');
    });*/

    try{
        $db = db();
        if($db->query("SELECT COUNT(id) FROM contest_categories")->fetch_row()[0] == 0) {
            $preloaded_categories = array('Animal','Entertainment', 'Tech', 'Politics', 'Food','Sport','Others');
            $i = 1;
            foreach($preloaded_categories as $preloaded_category) {
                foreach(get_all_languages() as $language) {
                    $post_vars['title'][$language['language_id']] = $preloaded_category;
                }
                /**
                 * @var $title
                 */
                $expected = array('title' => '');
                extract(array_merge($expected, $post_vars));
                $titleSlug = 'contest_category_'.md5(time().serialize($post_vars)).'_title';
                foreach($title as $langId => $t) {
                    add_language_phrase($titleSlug, $t, $langId, 'contest');
                }
                foreach($title as $langId => $t) {
                    (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, 'contest') : add_language_phrase($titleSlug, $t, $langId, 'contest');
                }
                $db->query("INSERT INTO contest_categories(title, category_order) VALUES('".$titleSlug."', '".$i."')");
                $i++;
            }
        }
    } catch (Exception $e){}
}