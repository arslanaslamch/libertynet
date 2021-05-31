<?php

register_hook('admin.settings', function ($settings, $type, $id) {
    if ($type === 'core' && $id === 'user' && isset($settings['user']['settings'])) {
        $settings['user']['settings']['user-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'themes/default/images/user/list-cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'ads' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['ads-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/ads/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'blog' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['blog-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/blog/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'event' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['event-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/event/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'forum' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['forum-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/forum/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'game' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['game-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/game/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'group' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['group-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/group/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'marketplace' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['marketplace-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/marketplace/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'music' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['music-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/music/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'page' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['page-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/page/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'photo' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['photo-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/photo/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'video' && isset($settings['site-settings']['settings'])) {
        $settings['site-settings']['settings']['video-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/video/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'auction' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['auction-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/auction/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'business' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['business-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/business/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'property' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['property-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/property/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'giftshop' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['giftshop-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/giftshop/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'poll' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['poll-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/poll/images/cover.jpg'
        );
    }
    if ($type === 'plugin' && $id === 'hashtag' && isset($settings['user-other-settings']['settings'])) {
        $settings['user-other-settings']['settings']['hashtag-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/hashtag/images/cover.jpg'
        );
    }
	if ($type === 'plugin' && $id === 'quiz' && isset($settings['site-other-settings']['settings'])) {
        $settings['site-other-settings']['settings']['quiz-list-cover'] = array(
            'type' => 'image',
            'title' => 'List Cover',
            'description' => '',
            'value' => 'plugins/quiz/images/cover.png'
        );
    }
  
    return $settings;
});
