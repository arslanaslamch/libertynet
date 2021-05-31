<?php
load_functions('export::export');
register_asset('export::css/export.css');
register_pager('personal/data',array('use' => 'export::export@export_personal_pager', 'filter' => 'auth', 'as' => 'data_personal_page'));
register_pager('personal/data/download',array('use' => 'export::export@export_personal_data_download', 'filter' => 'auth', 'as' => 'personal_data_download'));

register_hook('system.started', function ($app) {
    $app->exportData = false;
});

register_hook('image.url', function ($url){
    $app = app();
    if (isset($app->exportData) && $app->exportData == true){
        $dir =  config('temp-dir', path('storage/tmp')).'/'.'personal_information/'.get_userid();
        $matches = explode('storage/uploads/', $url);
        if (count($matches) > 1){
            $filePath = $matches[1];
            $fileX = explode('/', $filePath);
            $lastEl = array_values(array_slice($fileX, -1))[0];
            $fileDir = rtrim(explode($lastEl, $filePath)[0], '/');
            $fileDir = $dir.'/'.$fileDir;
            if(!is_dir($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.'/'.$lastEl, file_get_contents($url));
            $url = $filePath;
        }else{
            $fileX = explode('/', $url);
            $lastEl = array_values(array_slice($fileX, -1))[0];
            $imgDir = $dir.'/images/';
            if(!is_dir($imgDir)) {
                mkdir($imgDir, 0777, true);
            }
            try{
                file_put_contents($imgDir.'/'.$lastEl, file_get_contents($url));
                $url = 'images/'.$lastEl;
            } catch (Exception $e){
                exit($url);
            }
        }
    }
    return $url;
});
register_hook('parse.css.content.url', function ($boolean){
    $app = app();
    if (isset($app->exportData) && $app->exportData == true){
        $boolean[0] = false;
    }
    return $boolean;
});



add_menu('export-data-menu', array('id' => 'feed', 'link' => url_to_pager('data_personal_page').'?action=feed', 'title' => lang('feed::feed'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'profile', 'link' => url_to_pager('data_personal_page').'?action=profile', 'title' => lang('profile'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'friends', 'link' => url_to_pager('data_personal_page').'?action=friends', 'title' => lang('friends'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'followers', 'link' => url_to_pager('data_personal_page').'?action=followers', 'title' => lang('followers'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'following', 'link' => url_to_pager('data_personal_page').'?action=following', 'title' => lang('following'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'blog', 'link' => url_to_pager('data_personal_page').'?action=blog', 'title' => lang('blog::blog'), 'icon' => array('class' => 'ion-android-clipboard','color'=> '')));
add_menu('export-data-menu', array('id' => 'group', 'link' => url_to_pager('data_personal_page').'?action=group', 'title' => lang('group::groups'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'page', 'link' => url_to_pager('data_personal_page').'?action=page', 'title' => lang('page::pages'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'video', 'link' => url_to_pager('data_personal_page').'?action=video', 'title' => lang('video::videos'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'music', 'link' => url_to_pager('data_personal_page').'?action=music', 'title' => lang('music::musics'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'photo', 'link' => url_to_pager('data_personal_page').'?action=photo', 'title' => lang('photo::photos'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'marketplace', 'link' => url_to_pager('data_personal_page').'?action=marketplace', 'title' => lang('marketplace::marketplaces'), 'icon' => ''));
add_menu('export-data-menu', array('id' => 'event', 'link' => url_to_pager('data_personal_page').'?action=event', 'title' => lang('event::events'), 'icon' => ''));

if (plugin_loaded('business')){
    add_menu('export-data-menu', array('id' => 'business', 'link' => url_to_pager('data_personal_page').'?action=business', 'title' => lang('business::businesses'), 'icon' => ''));
    register_hook('export.content.load', function ($type){
        if ($type == 'business'){
            $result = array();
            $result['id'] = $type;
            $result['title'] = lang('business::businesses');
            $user_id = get_userid();
            $category_id = '';
            $filter = array('category_id' => '', 'keywords' => '', 'mine' => $user_id, 'user_id' => $user_id, 'min_price' => '', 'max_price' => '', 'has_photo' => '', 'location' => '', 'pending' => '', 'featured' => '', 'page' => '', 'limit' => '100000', 'admin' => false);
            $businesses = business_get_businesses($filter);
            $categories = business_get_categories();
            $category = $category_id ? lang(business_get_category($category_id)['category']) : lang('business::all-businesses');
            $result['content'] = view('export::business/businesses', compact('businesses','categories','category_id','category'));
            return $result;
        }
    });
    register_hook('export.content.page', function ($action){
        if ($action == 'business'){
            $user_id = get_userid();
            $category_id = '';
            $filter = array('category_id' => '', 'keywords' => '', 'mine' => $user_id, 'user_id' => $user_id, 'min_price' => '', 'max_price' => '', 'has_photo' => '', 'location' => '', 'pending' => '', 'featured' => '', 'page' => '', 'limit' => '100000', 'admin' => false);
            $businesses = business_get_businesses($filter);
            $categories = business_get_categories();
            $category = $category_id ? lang(business_get_category($category_id)['category']) : lang('business::all-businesses');
            $content = view('export::business/businesses', compact('businesses','categories','category_id','category'));
            return $content;
        }
    });
}



register_hook("before-render-js", function ($html) {
    $html .= <<<EOT
        <script>
        var pushDriver = 'do_nothing';
        </script>
EOT;
    return $html;
});


register_hook('account.settings.menu', function () {
    add_menu('account-menu', array('id' => 'download_information', 'link' => url_to_pager('data_personal_page').'?action=feed', 'title' => lang('export::download-information'), 'icon' => array('class' => 'fa fa-info-circle', 'color' => '#008000')));

});