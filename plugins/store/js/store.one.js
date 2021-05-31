$(document).on('mouseover','.parent-category',function(){
   var o = $(this);
   //remove all the active class
    $('.parent-category').removeClass('active');
    o.addClass('active');
    if(o.hasClass('all-cat')){
        $('.category-wrapper-content').fadeOut();
        return false;
    }
   var pc = $('.category-wrapper-content'); //parent container
   var imgsr = o.data('img');
   pc.find('img').attr('src',imgsr);
   var html = o.find('ul').clone();
   var container = $('.category-put');
   container.html(html);
   pc.fadeIn();
   return false;
});

$(document).on('mousemove', 'body', function(e) {
    if(!$(e.target).closest($('.top-with-cateogory-wrapper')).length){
        $('.category-wrapper-content').fadeOut();
        $('.parent-category').removeClass('active');
    }
});

$(document).on('click','.cart-modal-toggle',function(){
    var m = $("#cartModal");
    m.modal("toggle");
   return false;
});
$(document).on('mouseover','.store-sub-with-image',function(){
    var o = $(this);
    var pc = $('.category-wrapper-content'); //parent container
    var imgsr = o.data('img');
    pc.find('img').attr('src',imgsr);
    return false;
});
/*function correctTinyMce(){
    $('form').bind('form-pre-serialize', function(e) {
        tinyMCE.triggerSave();
    });
}
$(function(){
    correctTinyMce();
});
addPageHook('correctTinyMce');*/
function storeBannerSlider(){
    if($('.store-banner-slider-content').length){
        $('.store-banner-slider-content').slick({
            dots: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            speed: 1200,
            appendArrows: $('.store-can-hold-arrow'),
            prevArrow: "<span class='store_nav prev'> <i class='ion-chevron-left'></i></span>",
            nextArrow: "<span class='store_nav next'> <i class='ion-chevron-right'></i></span>",
            autoplay: true,
            autoplaySpeed: 4000,

        });
    }
}
$(function(){
    storeBannerSlider();
});

addPageHook("storeBannerSlider");

/*$(document).on("click",".product-quick-view",function(){
	var o = $(this);
	var id = o.data('id');
	var name = o.data('name');
	var modal = $("#quickStoreView");
	//alert("we ar here");
	$('.product-qfback').html("");
	modal.modal("show");
	modal.find('.modal-title').html(name);
	modal.find('.indicator').fadeIn();
	
	$.ajax({
        url : baseUrl + "quickview/product",
        data : {pid : id},
        type : 'POST',
        success : function(data){
			var json = jQuery.parseJSON(data);
            //modal.modal("hide");
			modal.find('.indicator').hide();
			$('.product-qfback').html(json.v);
        }
    });
	return false;
});*/

function upload_store_profile_cover(reposition) {
    toggle_profile_cover_indicator(true);
    var id = $('#group-profile-container').data('id');
    $("#profile-cover-change-form").ajaxSubmit({
        url : baseUrl + 'store/change/cover?id=' + id,
        success: function(data) {
            var result = jQuery.parseJSON(data);
            if (result.status == 0) {
                notifyError(result.message);
            } else {
                var img = result.image;
                $('.profile-cover-wrapper img').attr('src', img);
                $('.profile-resize-cover-wrapper img').attr('src', result.original);
                if(reposition) {
                    reposition_user_profile_cover();
                }
            }
            toggle_profile_cover_indicator(false);
        }
    })
}

function save_store_profile_cover() {
    var i = $('#profile-cover-resized-top').val();
    var id = $('#group-profile-container').data('id');
    var width = $('#group-profile-container').data('width');
    if (i == 0) {
        refresh_profile_cover_positioning()
    } else {
        toggle_profile_cover_indicator(true);
        $.ajax({
            url : baseUrl + 'store/cover/reposition?pos=' + i + '&id=' + id+'&width=' + width + '&csrf_token=' + requestToken,
            success: function(data) {
                $('.profile-cover-wrapper img').attr('src', data);
                toggle_profile_cover_indicator(false);
                refresh_profile_cover_positioning();
            },
            error : function() {
                toggle_profile_cover_indicator(false);
                refresh_profile_cover_positioning();
            }
        })
    }
    return false;
}

function remove_store_profile_cover(img) {

    $('.profile-cover-wrapper img').attr('src', img);
    $('.profile-resize-cover-wrapper img').attr('src', '');
    var id = $('#group-profile-container').data('id');
    $.ajax({
        url : baseUrl + 'store/cover/remove?id=' + id + '&csrf_token=' + requestToken,
    });
    return false;
}

function upload_store_logo() {
    var form = $("#group-profile-image-form");
    show_profile_image_indicator(true);
    var id = form.data('id');
    form.ajaxSubmit({
        url : baseUrl + 'store/change/logo?id=' + id,
        success : function(data) {
            data = jQuery.parseJSON(data);
            show_profile_image_indicator(false);
            if (data.status) {
                $(".profile-image").attr('src', data.image);
            } else {
                alertDialog(data.message);
            }
            form.find('input[type=file]').val('')
        },
        uploadProgress : function(event, position, total, percent) {
            var uI = $(".profile-image-indicator .percent-indicator");
            uI.html(percent + '%').fadeIn();

        },
        error : function() {
            show_profile_image_indicator(false);
            alertDialog("An error occurred");
            form.find('input[type=file]').val('')
        }
    })
}

function submitStoreCategoryFilter(o){
    //var o = $(this);
    var v = $("#store-top-key").val();
    if(v == ''){
        notifyInfo($(o).data('info'));
        return false;
    }
    var url = $("#search-category-value").val();
    loadPage(url+'?term='+v);
    return false;
}


//awon eyan ti emoney
$(document).on('click','.refund-charge-btn',function(){
   var o = $(this);
   var t = o.attr('data-t');
   var oid = o.attr('data-oid');
    $.ajax({
        url : baseUrl + "admincp/store/charge-refund/orders?id="+oid+'&action='+t,
        success : function (data){
            if(t == 'refund'){
                $('.refund-order-btn-'+oid).fadeOut();
                $('.charge-order-btn-'+oid).fadeIn();
            }else{
                $('.charge-order-btn-'+oid).fadeOut();
                $('.refund-order-btn-'+oid).fadeIn();
            }
        }
    })
  return false;
});
