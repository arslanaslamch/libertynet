function tv_change_embed_code(d){
    var o = $(d);
    var v = o.val();
    if(!v) return false;
    if(v == 'embed'){
        $('.embed-content').fadeIn();
        $('.url-content').hide();
    }else{
        //url
        $('.embed-content').hide();
        $('.url-content').fadeIn();
    }
    return false;
}