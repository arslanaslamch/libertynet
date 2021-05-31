<?php
load_functions("tips::tips");
register_asset("tips::css/tips.css");
register_asset("tips::js/tips.js");
//register_asset("social::js/social.js");

register_pager('tips/check',array(
    'as'=>'tips_check',
    'use'=>'tips::tips@tips_pager',
));
register_pager('tips/update',array(
    'as'=>'tips_update',
    'use'=>'tips::tips@tips_update_pager',
    'filter'=>'auth'
));

register_hook('header',function(){
    echo '<div class="lucid-overlay"></div>';
});



register_hook('after-render-js',function($html){
    $app = app();
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        $html .= "<script>
   function profileJs(){
      tips_ajax('profile');
   }
   </script>";
        $html .= "<script> $(function(){
           profileJs('profile')
         });
    try {
          addPageHook('profileJs');
     } catch (e) {}
     </script>";
    }
    return $html;

 });

