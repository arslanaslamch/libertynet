$(document).on('click','.photo-viewer-donation',function(){
    var o = $(this);
    var id = parseInt(o.data('id'));
    var nt = (id + 1);
    var pr = (id - 1);
    var cur = o.data('image');
    var modal = $('.donation-viewer');
    var photoPane = modal.find('.viewer-left .center-do-image');
    modal.find('.viewer-left .nav-left a').attr('data-id',pr);
    modal.find('.viewer-left .nav-right a').attr('data-id',nt);
    photoPane.html("<img src='"+cur+"'/> ");
    modal.modal("show");
   return false;
});


$(document).on('click','.donation-viewer .nav-donation',function(){
    var o = $(this);
    var c = o.attr('data-id');
    if($('.nxt-id-'+c).length){
        $('.nxt-id-'+c).click();
    }else{
        $('.nxt-id-0').click();
    }
    return false;
});

$(document).on('click','#donateModal form button',function(){
    var v = $("#pmthody").val();
    var amt = $("#damt").val();
    var msg = $("#msg-cannot").val();
    var modal = $("#donateModal");
    if(v == 'stripe'){
        var p = $("#donation-stripe");
        if(p.length){
            p.find('script').attr('data-amount', 100 * amt);
            //modal.modal('hide');
            //$("#samt").val(amt);
            //p.find('form button').click();
        }else{
            notifyError(msg + v);
            return false;
        }
    }

    if(v == 'paypal'){
        if(!$("#paypal-pay").length){
           notifyError(msg + v);
            return false;
        }
    }
});
$(document).on('click','#donate-now-tc',function(){
    var $this = $(this);
    var modal = $("#donateModal");
    modal.find('.modal-title').html($this.data('title'));
    modal.find('#did').val($this.data('id'));
    modal.modal('show');
    return false;
});

$(document).on('click','#promot-cp',function(){
    var modal = $("#donateIframe");
    modal.modal('toggle');
    return false;
});

$(function(){
    $('#exdonation').datepick({minDate: new Date()});
});

$(document).on('click','.add-more-prededfined',function(){
   $(".predefined-more").append('<div class="predefined-input"><input type="text" style="" name="val[pre][]" class="form-control" /> <span><i class="ion-minus"></i></span></div>');
   return false;
});

$(document).on('click','.predefined-input i',function(){
    $(this).closest('.predefined-input').remove();
   return false;
});

window.running_donation = false;
$(document).on('keyup','#friend-name',function(){
    var vl = $(this).val();
    var i = $('.friend-bring-result-donation .indicator');
    var c = $('.friend-bring-result-donation');
    if(vl.length > 2){
        c.fadeIn();
        i.fadeIn();
        $('.friend-bring-result-donation .r').html("");
        //$('.friend-bring-result-donation .r').removeClass('slimscroll');
        var s = window.running_donation;
        //if it is currently ongoing, do not allow new process
        if(s) return false;
        window.running_donation = true;
        $.ajax({
            url : baseUrl + "donation/ajax",
            method : 'POST',
            data : {action : 'search-friends', term : vl},
            success : function(data){
                var json = jQuery.parseJSON(data);
                i.hide();
                $('.friend-bring-result-donation .r').html(json.v);
               // $('.friend-bring-result-donation .r').addClass('slimscroll').attr('data-height','250px');
                window.running_donation = false;
                reloadInits();
            }
        });
    }else{
        c.hide();
        i.hide();
        $('.friend-bring-result-donation .r').html("");
        //$('.friend-bring-result-donation .r').removeClass('slimscroll');
    }
});

//when we click on friend
$(document).on('click','.choosing-friends-donation',function(){
    var o = $(this);
    var id = o.data('id');
    var name = o.data('name');
    var l = o.data('l');
    if($('#uid'+id).length){
        $('#uid'+id).remove();
    }
    var uid = 'uid'+id;
    var html = '<p class="cuser-line" id="'+uid+'" ><input checked type="checkbox" name="val[friends][]" value="'+id+'" />  '+ name + '</p>'
    $(".friends-list .slimscroll").prepend(html);
    notifySuccess(name + "  " + l);
    reloadInits();
    return false;
});

$(document).on('click', 'body', function(e) {
    if(!$(e.target).closest($('.friend-bring-result-donation')).length) $('.friend-bring-result-donation').fadeOut();
});

function checkAllDonation(){
    $('.cuser-line').find('input').prop('checked',true);
    return false;
}

function unCheckAllDonation(){
    $('.cuser-line').find('input').prop('checked',false);
    return false;
}

$(document).on('click','#import-all-friends',function(){
    var o = $(this);
    o.prop('disabled',true);
    $.ajax({
        url : baseUrl + "donation/ajax",
        method : 'POST',
        data : {action : 'all-friends'},
        success : function(data){
            var json = jQuery.parseJSON(data);
            if(json.status){
                $(".friends-list .slimscroll").html(json.v);
                reloadInits();
            }else{
                notifyError(json.v);
            }
            o.prop('disabled',false);
        }
    });
   return false;
});

$(document).on('click','#ivmc',function(){
    var c = $("#donateInvite");
    tmcd();
    c.modal("show");
    return false;
});
$(document).on('click','#ivm-wrapper .send-ivm',function(){
    var o = $(this);
    var id = $("#ivm-id").val();
    o.prop('disabled',true);
    var form = o.closest('form');
    var c = $("#donateInvite");
    form.ajaxSubmit({
        url : baseUrl + "donation/ajax?action=invite&id="+id,
        success: function(data) {
            var json = jQuery.parseJSON(data);
            c.modal("hide");
            o.prop('disabled',false);
            if (json.status == 0) {
                notifyError(json.message);
            } else{
                notifySuccess(json.message);
            }
        },
        error : function() {
            notifyError();
            c.modal("hide");
            o.prop('disabled',false);
        }
    });
   return false;
});
/*$('form').bind('form-pre-serialize', function(e) {
    tinyMCE.triggerSave();
});*/

/*function tmcd(){
    if($('.deditor').length){
        tinymce.remove();
        tinymce.init({
            selector: '.deditor',
            height: 250,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
            ],
            toolbar: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist table forecolor code | link',
            relative_urls: false,
            document_base_url: baseUrl
        });
    }
}
$(function(){
    tmcd();
});*/

$(document).on('click','.donation-smenu',function(){
   var o = $(this);
    var t = o.data('t');
   var all = $(".donation-smenu");
    all.removeClass('active');
    $('.each-d-c').hide();
    $('.d-'+t).fadeIn();
    o.addClass('active');
    return false;
});


$(document).on('click','.predefined-button-modal',function(){
   var o = $(this);
    var amt = o.data('amt');
    $("#damt").val(amt);
    return false;
});

$(document).on('click','.is-a-donate-donate-btn',function(){
    var o = $(this);
    var t = o.data('t');
    var derr_empt = o.data('empty');
    var tce = o.data('tce');
    var mne = o.data('mne');
    o.prop('disabled',true);
    var amt = $("#damt").val();
    var mn = $("#mini-donation").val();

    //check if the amount is emtypt
    if(amt == ""){
        notifyError(derr_empt);
        o.prop('disabled',false);
        return false;
    }

    //check for mininum
    if(parseInt(amt) < parseInt(mn)){
        notifyInfo(mne + mn);
        o.prop('disabled',false);
        return false;
    }


    //check if the terms and condition is chosen
    if($('#tmca').is(":checked")){

    }else{
        notifyInfo(tce);
        o.prop('disabled',false);
        return false;
    }

    //if all we can get here,
    //let us get the payment buttons with ajax
    if(t == 'stripe'){
        //let us process stripe now
        var handler = StripeCheckout.configure({
            key: o.data('pk'),
            image: o.data('logo'),
            token: function(token) {
                $("#sp_token").val(token.id);
                $("#sp_email").val(token.email);
                $("#donation-final-form").submit();
            }
        });

        // Open Checkout with further options
        handler.open({
            name: o.data('name'),
            description: o.data('desc'),
            currency : o.data('cur'),
            amount: parseInt(amt) * 100
        });

        // Close Checkout on page navigation
        $(window).on('popstate', function() {
            handler.close();
        });
    }

    if(t == 'paypal'){
        $("#donation-final-form").submit();
    }

   return false;
});


$('.follow-donation').on({

    mouseenter: function () {
        var o  = $(this);
        var df = o.html();
        o.attr('data-df',df);
        var s = o.attr('data-status');
        var uf = o.data('unfollow');
        var f = o.data('follow');
        var fing = o.data('following');
        //stuff to do on mouse enter
        if(s == 1){
            //show unfollow
            o.html(uf);
        }else{
            o.html(f);
        }
    },
    mouseleave: function () {
        var o  = $(this);
        var s = o.attr('data-status');
        if(s == 1){
            var df =  o.data('following');
        }else{
            var df = o.data('follow');
        }
        //stuff to do on mouse leave
        o.html(df);
    }
});


$(document).on('click','.follow-donation',function(){
    var o  = $(this);
    var s = o.attr('data-status');
    var uf = o.data('unfollow');
    var f = o.data('follow');
    var fing = o.data('following');
    var id = o.data('id');
    var dc = $("#dflc");
    if(s == 1){
        //it is currently following, we want to unfollow
        o.attr('data-status',0);
        o.removeClass('dfollowing');
        o.addClass('dnotfollowing');
        o.html(f);
        //min-1
        dc.html(parseInt(dc.html()) - 1);
    }else{
        //is currently not following, we want to follow now
        o.attr('data-status',1);
        o.addClass('dfollowing');
        o.removeClass('dnotfollowing');
        o.html(fing);
        //plus 1
        dc.html(parseInt(dc.html()) + 1);
    }

    $.ajax({
        url : baseUrl + "donation/ajax",
        method : 'POST',
        data : {action : 'follow',status : s, id : id},
        success : function(data){

        }
    });
   return false;
});

//awon eyan emoney
$(document).on('click','#donate-emoney',function(){
    var o = $(this);
    var derr_empt = o.data('empty');
    var tce = o.data('tce');
    var mne = o.data('mne');
    o.prop('disabled',true);
    var amt = $("#damt").val();
    var mn = $("#mini-donation").val();

    //check if the amount is emtypt
    if(amt == ""){
        notifyError(derr_empt);
        o.prop('disabled',false);
        return false;
    }

    //check for mininum
    if(parseInt(amt) < parseInt(mn)){
        notifyInfo(mne + mn);
        o.prop('disabled',false);
        return false;
    }


    //check if the terms and condition is chosen
    if($('#tmca').is(":checked")){

    }else{
        notifyInfo(tce);
        o.prop('disabled',false);
        return false;
    }
    $('#donation-final-form').append("<input type='hidden' name='val[emoney]' value='yes' />");
    $('#donation-final-form').submit();
    return false;
});