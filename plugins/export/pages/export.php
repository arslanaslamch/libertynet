<?php
function export_personal_pager($app){
    $app->setTitle("Personal Data");
    $content = '';
    $action = input('action', 'feed');
    switch($action) {
        case 'feed';
            $feeds = export_get_feeds();
            $content = view('export::feed/content', array('feeds' => $feeds));
        break;
        case 'profile';
            $content = view('export::profile/layout');
        break;
        case 'friends';
            $users = list_connections(get_friends())->results();
            $content = view('export::user/display', compact('users'));

        break;
        case 'followers';
            $users = list_connections(get_followers())->results();
            $content = view('export::user/display', compact('users'));
        break;
        case 'following';
            $users = list_connections(get_following())->results();
            $content = view('export::user/display', compact('users'));
        break;
        case 'blog';
            $blogs = get_blogs('mine','','',get_userid(),10000)->results();
            $content = view('export::blog/display', compact('blogs'));
        break;
        case 'group';
            $groups = get_groups('yours','',100000,'','')->results();
            $content = view('export::group/lists', compact('groups'));
        break;
        case 'page';
            $pages = get_pages('mine','','100000','','')->results();
            $content = view('export::page/mine', compact('pages'));
        break;
        case 'video';
            $videos = get_videos('mine','','','','100000');
            $content = view('export::video/index', compact('videos'));
        break;
        case 'music';
            $musics = get_musics('mine','','','','100000');
            $content = view('export::music/index', compact('musics'));
        break;
        case 'photo';
            $photos = get_photos(get_userid(),'user-all',1000000,0,'');
            $content = view('export::photo/photo', compact('photos'));
        break;
        case 'marketplace';
            $filters = array('category_id' => '', 'term' => '', 'user_id' => get_userid(), 'featured' => '', 'min_price' => '', 'max_price' => '', 'location' => '');
            $listings = marketplace_get_listings($filters,'',1000000);
            $content = view('export::marketplace/marketplace', compact('listings'));
        break;
        case 'event';
            $events = get_events('me','',1000000);
            $content = view('export::event/browse', compact('events'));
        break;
        default;
            $content = fire_hook('export.content.page', null, array($action));
        break;
    }
    return $app->render(view("export::page", array('content' => $content)));
}
function export_personal_data_download($app){

    $actionDetails = array();
    $actionSelected = input('selected');
    if ($actionSelected){
        foreach ($actionSelected as $item){
            $actionDetails[] = get_export_details($item);
        }
        $zippedHTML = generate_export_html($actionDetails);
        app()->exportData = false;
        clear_temp_data(false);
        render_assets('css');
        render_assets('js');
        download_file($zippedHTML);
        sleep(10);
        delete_file($zippedHTML);
    }
    redirect_to_pager('data_personal_page');
}