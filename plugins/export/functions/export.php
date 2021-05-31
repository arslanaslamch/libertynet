<?php
function export_get_feeds($userId = null){
    $userId = $userId?$userId:get_userid();
    $sql = "SELECT * FROM feeds WHERE entity_type='user' AND entity_id ='{$userId}'";
    $query = db()->query($sql);
    if($query) {
        $results = array();
        while($fetch = $query->fetch_assoc()) {
            $feed = get_arranged_feed($fetch);
            if($feed and $feed['status'] == 1) {
                $results[] = $feed;
            } else {
                //think we should delete this
            }
        }

        return $results;
    }
    return array();
}

function get_export_details($type){
    clear_temp_data(false);
    $result = array('id' => '', 'title' => '', 'content' => '');
    $app = app();
    $app->exportData = true;
    if ($type == 'feed'){
        $feeds = export_get_feeds();
        $result['id'] = $type;
        $result['title'] = lang('feed::feed');
        $result['content'] = view('export::feed/content', array('feeds' => $feeds));

    } else if ($type == 'profile'){
        $result['id'] = $type;
        $result['title'] = lang('profile');
        $result['content'] = view('export::profile/layout');
    } else if ($type == 'friends'){
        $result['id'] = $type;
        $result['title'] = lang('friends');
        $users = list_connections(get_friends())->results();
        $result['content'] = view('export::user/display', compact('users'));
    } else if ($type == 'followers'){
        $result['id'] = $type;
        $result['title'] = lang('followers');
        $users = list_connections(get_followers())->results();
        $result['content'] = view('export::user/display', compact('users'));
    } else if ($type == 'following'){
        $result['id'] = $type;
        $result['title'] = lang('following');
        $users = list_connections(get_following())->results();
        $result['content'] = view('export::user/display', compact('users'));
    } else if ($type == 'blog'){
        $result['id'] = $type;
        $result['title'] = lang('blog::blogs');
        $blogs = get_blogs('mine','','',get_userid(),10000)->results();
        $result['content'] = view('export::blog/display', compact('blogs'));
    } else if ($type == 'group'){
        $result['id'] = $type;
        $result['title'] = lang('group::groups');
        $groups = get_groups('yours','',100000,'','')->results();
        $result['content'] =  view('export::group/lists', compact('groups'));
    } else if ($type == 'page'){
        $result['id'] = $type;
        $result['title'] = lang('page::pages');
        $pages = get_pages('mine','','100000','','')->results();
        $result['content'] = view('export::page/mine', compact('pages'));
    } else if ($type == 'video'){
        $result['id'] = $type;
        $result['title'] = lang('video::videos');
        $videos = get_videos('mine','','','','100000');
        $result['content'] = view('export::video/index', compact('videos'));
    } else if ($type == 'music'){
        $result['id'] = $type;
        $result['title'] = lang('music::musics');
        $musics = get_musics('mine','','','','100000');
        $result['content'] = view('export::music/index', compact('musics'));
    } else if ($type == 'photo'){
        $result['id'] = $type;
        $result['title'] = lang('photo::photos');
        $photos = get_photos(get_userid(),'user-all',1000000,0,'');
        $result['content'] = view('export::photo/photo', compact('photos'));
    } else if ($type == 'event'){
        $result['id'] = $type;
        $result['title'] = lang('event::events');
        $events = get_events('me','',1000000);
        $result['content'] =  view('export::event/browse', compact('events'));
    } else if ($type == 'marketplace'){
        $result['id'] = $type;
        $result['title'] = lang('marketplace::marketplaces');
        $filters = array('category_id' => '', 'term' => '', 'user_id' => get_userid(), 'featured' => '', 'min_price' => '', 'max_price' => '', 'location' => '');
        $listings = marketplace_get_listings($filters,'',1000000);
        $result['content'] = view('export::marketplace/marketplace', compact('listings'));
    } else{
        $result = fire_hook('export.content.load', $type);
    }
    return $result;
}

function generate_export_html($menus){
    $cssAssets = render_assets('css');
    $jsAssets = render_assets('js');
    $content = view("export::header");


    $content .="<div class='box'>";
    $content .="<div class='box-content'>";
    $content .="<div class='row'>";

    $content .="<div class='col-2'>";
    $content .= "</div>";



    $content .="<div class='col-8'>";
    $content .="<div class='row'>";

    $active = '';
    $content .="<div class='col-3'>";
    $content .= '<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">';
    $n = 1;
    foreach ($menus as $menu){
        $active = ($n == 1)?' active':'';
        $content .='<a class="nav-link'.$active.'" id="v-pills-'.$menu['id'].'-tab" data-toggle="pill" href="#v-pills-'.$menu['id'].'" role="tab" aria-controls="v-pills-profile" aria-selected="false">'.$menu['title'].'</a>';
        $n++;
    }
    $content .= '</div>';
    $content .="</div>";

    $content .="<div class='col-9'>";

    $content .= '<div class="tab-content" id="v-pills-tabContent">';
    $n = 1;

    foreach ($menus as $menu){
        $active = ($n == 1)?' active show':'';
        $content .= '<div class="tab-pane fade'.$active.'" id="v-pills-'.$menu['id'].'" role="tabpanel" aria-labelledby="v-pills-'.$menu['id'].'-tab">';
        $content .= $menu['content'];
        $content .= '</div>';
        $n++;
    }
    $content .= '</div>';

    $content .= '</div>';

    $content .="</div>";
    $content .= "</div>";


    $content .="<div class='col-2'>";
    $content .= "</div>";


    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";


    $content .= view("export::footer");

    $content .= $jsAssets;

    $dir =  config('temp-dir', path('storage/tmp')).'/'.'personal_information/'.get_userid();
    if(!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $user = get_user();
    $name = $user['username'].'-Personal data-'.get_userid().".html";

    $path = $dir.'/'.$name;

    preg_match("/\/storage\/(.*?)'/",$cssAssets,$matches);
    $file = $matches[0];$file = str_replace("'", '', $file);
    $cssAssets = ltrim($file, '/');
    preg_match("/\/storage\/(.*?)'/",$jsAssets,$matches);
    $file = $matches[0];
    $file = str_replace("'", '', $file);
    $jsAssets = ltrim($file, '/');


    $cssPath = $dir.'/css';
    $jsPath = $dir.'/js';
    $fontPath = $dir.'/fonts';
    if(!is_dir($cssPath)) {
        mkdir($cssPath, 0777, true);
    }
    if(!is_dir($jsPath)) {
        mkdir($jsPath, 0777, true);
    }
    if(!is_dir($fontPath)) {
        mkdir($fontPath, 0777, true);
    }
    file_put_contents($cssPath.'/style.css', file_get_contents(path($cssAssets)));
    file_put_contents($jsPath.'/script.js', file_get_contents(path($jsAssets)));
    $fonts = path('themes/default/fonts');
    $fontsPath = str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $fonts);
    export_full_copy($fontsPath, $fontPath);
    file_put_contents($path, $content);
    $zip_html = zip_file_data($user, $dir);
    delete_file($dir);
    return $zip_html;
}

function zip_file_data($data, $source){
    $path_new = path('storage/uploads')."/".get_userid()."/".date('Y').'/personal_information';
    if(!is_dir($path_new)) {
        mkdir($path_new, 0777, true);
    }
    $destination = $path_new.'/Personal-data-'.$data['username']."-".get_userid().".zip";
    delete_file($destination);
    //if (is_string($source)) $source_arr = array($source); // convert it to array
    $destination = str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $destination);
    $source = str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $source);

    $cmd = 'cd "'.$source.'" && zip -r "'.$destination.'" . 2>&1';
    $output = shell_exec($cmd);

    return $destination;
}

function delete_archive_file($path){
    $dir = $path;
    $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ( $ri as $file ) {
        $file->isDir() ?  rmdir($file) : unlink($file);
    }
    return true;
}
function export_full_copy($source, $target) {
    if ( is_dir( $source ) ) {
        @mkdir( $target );
        $d = dir( $source );
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }
            $Entry = $source . '/' . $entry;
            if ( is_dir( $Entry ) ) {
                export_full_copy( $Entry, $target . '/' . $entry );
                continue;
            }
            copy( $Entry, $target . '/' . $entry );
        }

        $d->close();
    }else {
        copy( $source, $target );
    }
}