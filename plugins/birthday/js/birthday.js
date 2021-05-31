$(document).on('click','.wish-birthday',function(){
    let o = $(this);
    let n = o.data('name');
    let uid = o.data('uid');
    let m = $("#BirthdayModal");
    let t = m.find('.modal-title').data('bw') + ' '+ n;
    m.find('.modal-title').html(t);
    m.modal('show');
    $('#to-uid').val(uid);
   return false;
});

$(document).on('click','#BirthdayModal .btn-block',function(){
    var o = $(this);
    var msg = $('#BirthdayModal textarea').val();
    let uid = $("#to-uid").val();
    if(!msg) return notifyError('Field can not be empty');
    o.prop('disabled',true);
   $.ajax({
       url : baseUrl + 'birthday/ajax',
       method : 'POST',
       data : {msg : msg,action : 'congrats', uid : uid},
       success : function (data) {
           let m = jQuery.parseJSON(data);
           notifySuccess(m.msg);
           $("#BirthdayModal").modal('hide');
           $('#BirthdayModal textarea').val('');
           o.prop('disabled',false);
       }
   });
   return false;
});

//display ballon
$(function(){
    /*$('.birthday-images-wrapper img').each(function(i, obj) {
        //test
        //if(i === 1){
            let bottom = Math.floor((Math.random() * 600) + 50);
            //let left = Math.floor((Math.random() * 600) + 1);
            $(obj).css({bottom : -bottom, position: 'relative'});
        //}

    });
    $(".birthday-images-wrapper").appendTo('body')
        .fadeIn()
        .css({top:800,position:'absolute',width : $(window).width});
        .animate({top:-2000}, 18000, function() {
            //callback
        });*/
    birthdayShoutout()
});
try{
    addPageHook('birthdayShoutout');
}catch (e) {

}
function birthdayShoutout(){
    if($('.birthday-images-wrapper').length){
        $(".birthday-images-wrapper").appendTo('body')
            .fadeIn()
            .css({top:700,position:'absolute',width : $(window).width})
        $('.birthday-images-wrapper img').each(function(i, obj) {

            let bottom = Math.floor((Math.random() * 600) + 50);
            let left = Math.floor((Math.random() * 600) + 400);
            let right = Math.floor((Math.random() * 600) + 300);
            let top = Math.floor((Math.random() * 600) + 50);
            $(obj).css({bottom : -bottom, position: 'relative'});
            $(obj).animate({top:-1500,left : -left, right : right}, 19000, function() {
                $('.birthday-images-wrapper').hide();
            });

        });
    }
}

$(document).on('click','.birthday-toggle',function(){
     let istatus = $(this).find('input').prop('checked');
     let s = (istatus) ? 1 : 0;
      console.log(s);
    $.ajax({
        url : baseUrl + 'birthday/ajax',
        method : 'POST',
        data : {status : s,action : 'friends-only'},
        success : function (data) {
            loadPage(window.location.href);
        }
    })
});