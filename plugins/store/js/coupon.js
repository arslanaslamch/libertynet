$(document).on('click','.add-product-coupon',function(){
   var npc = $('#coupon_products_selected_container'); //new product container
    var opc = $('.old-proudcts-container');
    npc.append(opc.find(':selected'));
    return false
});
$(document).on('click','.remove-product-coupon',function(){
    var npc = $('#coupon_products_selected_container'); //new product container
    var opc = $('.old-proudcts-container');
    opc.append(npc.find(':selected'));
    return false
});

$(document).on('click','.add-all-product-coupon',function(){
    var npc = $('#coupon_products_selected_container'); //new product container
    var opc = $('.old-proudcts-container');
    opc.map(function(i,el){
        $(this).find('option').prop('selected',true);
    });
    npc.append(opc.find(':selected'));
    return false
});
$(document).on('click','.remove-all-products-coupon',function(){
    var npc = $('#coupon_products_selected_container'); //new product container
    var opc = $('.old-proudcts-container');
    npc.map(function(i,el){
        $(this).find('option').prop('selected',true);
    });
    opc.append(npc.find(':selected'));
    return false;

});


$(document).on('click','#couponSlide',function(){
    $('.coupon-box').slideToggle();
    return false;
});

$(document).on('click','#submitCouponCode',function(){
   var c = $('#couponCode').val();
    if(c == ''){
        return notifyError();
    }
    var i = $('.coupon-box');
    i.find('input').hide();
    i.find('.indicator').fadeIn();
    $.ajax({
        url : baseUrl + "coupon/verify",
        data : {code : c},
        success : function(data){
           // console.log(data);
            var json = jQuery.parseJSON(data);
            i.find('.indicator').hide();
            i.find('input').fadeIn();
            if(json.status == 0){
                notifyError(json.message);
            }else{
                window.location.href = json.url;
            }
        }
    });
    return false;
});
function open_flat_rate_amount(div){
    var v = $(div).val();
    if(v == 'flat-rate'){
        $('#shipping_amount').fadeIn();
    }else{
        $('#shipping_amount').fadeOut();
        $('#shipping_amount').find('input').val('0');
    }
}

