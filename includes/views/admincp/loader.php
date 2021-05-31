<?php
//register assets for frontend
register_asset("css/bootstrap.min.css");
register_asset("css/ionicons.min.css");
register_asset("css/line-awesome.min.css");
register_asset("css/font-awesome.min.css");
register_asset("css/perfect-scrollbar.css");
register_asset("css/colorpicker.css");
register_asset("css/tagify.css");
register_asset("css/morris.css");
register_asset("css/codemirror.min.css");
$text_editor_method = config('text-editor-method', 'tinyMCEInit');
if($text_editor_method == 'froalaInit') {
    register_asset("css/froala_editor.pkgd.min.css");
    register_asset("css/froala_style.min.css");
}
register_asset("css/style.css");

register_asset("js/jquery.js");
register_asset("js/jquery-ui.js");
register_asset("js/perfect-scrollbar.jquery.js");
register_asset("js/jquery.form.min.js");
register_asset("js/tether.min.js");
register_asset("js/popper.min.js");
register_asset("js/bootstrap.min.js");
register_asset("js/colorpicker.js");
register_asset("js/jquery.tagify.min.js");
register_asset("js/flot.js");
register_asset("js/morris.js");
register_asset("js/hook.js");
register_asset("js/codemirror.min.js");
if($text_editor_method == 'ckEditorInit') {
    register_asset("js/ckeditor.js");
}
if($text_editor_method == 'froalaInit') {
    register_asset("js/xml.min.js");
    register_asset("js/froala_editor.pkgd.min.js");
    register_asset("js/file.min.js");
}
register_asset("js/script.js");
register_asset("js/editor.js");
register_asset("js/theme.extend.js");
