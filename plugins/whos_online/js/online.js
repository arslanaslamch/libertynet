$(function(){
    if($('.whos_online').length){
        setInterval(function(){
            $.ajax({
                url : baseUrl + "online/members/check",
                success : function(data){
                    var d = jQuery.parseJSON(data);
                    $('.whos_online').html(d.v);
                    reloadInits();
                }
            })
        },10000);
    }
});