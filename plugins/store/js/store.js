


$(document).ready(function (){
    $('#store-producer .table').dataTable({
        iDisplayLength: 10,
        orderFixed: [[0, 'desc']]
    });
});


$(document).on("click",".remove_producer",function(){
    var i = $(this).data("id");
    //alert(i);
    $.ajax({
        url : baseUrl + "store/remove_producer?id="+i,
        success : function (data){
            $("#row"+i).fadeOut();
        }
    })
    return false;
});

$(document).on("click",".remove_product",function(){
    var o = $(this);
    $('#confirmModal').modal({show:true});
        $('#confirm-button').unbind().click(function() {
            window.location = o.attr('data-url');
            $('#confirmModal').modal('hide');
        });
    return false;
});




$(document).on("click",".add-more-attr",function(){
    var n = $(this).attr('data-number');
    n++;
    var c = $(".attr-container");
    var m = '<div id="'+n+'"><br/><div class=""><input class="form-control form-inline input-attr" type="text" name="val[label][]"/>' +
        '<input class="form-control form-inline input-attr" type="text" name="val[values][]"/><br/></div><a onclick="return remove_more('+n+')" href="">remove</a> </div>';
    c.append(m);
    $(this).attr('data-number',n);
    return false;
});

function remove_more(i){
    $("#"+i).remove();
    return false;
}

//handle store following status
function storeFollowingStatus(div){
    var f_lang,unf_lang,status;
    f_lang = $(div).attr('data-follow');
    unf_lang = $(div).attr('data-unfollow');
    status = $(div).attr('data-status');
    var store_id = $(div).attr('data-sid');
    //alert(status);
    if(status == 0 ){
      $(div).text(unf_lang);
        $(div).attr('data-status',1);
        $(div).removeClass('btn-success');
        $(div).addClass('btn-danger');
        $.ajax({
            url : baseUrl + "store/follower?action=follow&store_id="+store_id
        })
    }else{
        $(div).text(f_lang);
        $(div).attr('data-status',0);
        $(div).removeClass('btn-danger');
        $(div).addClass('btn-success');
        $.ajax({
            url : baseUrl + "store/follower?action=unfollow&store_id="+store_id
        })
    };
    return false;
}

function additionalImagesCount(){
    var span = $('.additional-image-count');
    var imageInput = document.getElementById("store-add-images");
    var l = imageInput.files.length;
    //console.debug(l);
    if (!imageInput.files.length) return false;
    span.html(imageInput.files.length).fadeIn();
    return false;
}



$(document).on('click','.rating',function(){
    var rating = $(this).data('id');
    var holders = $('.product-rating-container');
    holders.attr('data-status','yes');
    holders.attr('data-myrating',rating);
    $('#myProductrating').val(rating);
    $('#RatingStatus').val('yes');
    ratingMeter(rating);
    //insert rating into database
    var type = holders.data('type');
    var typeid = holders.data('typeid');
    $.ajax({
        url : baseUrl + "rating?type="+type+ "&type_id="+typeid+"&rating="+rating,
        succss:function(){

        }
    })
});

function adminHasPaid(sid,amt){
    var o = $('#store_'+sid);
    $.ajax({
        url : baseUrl + "admincp/store/withdrawal_requests/update",
        data : {store_id : sid,amount : amt},
        success : function(data){
            o.fadeOut();
        }
    })
    adminAlertDialog($('#AdminMessage').val());
    return false;
}
$(document).on('mouseover','.rating',function(){
    var len = $(this).data('id');
    ratingMeter(len);
});


$(document).on('mouseout','.rating',function(){
    var o = $('#RatingStatus');
    var status = o.val();
    if(status == 'yes'){
       var rating = o.data('myrating');
        var r = $('#myProductrating').val();
        ratingMeter(r);
      // console.debug(r);
    }else{
        for(var j=1;j < 6;j++){
            $('.star'+j).removeClass('background-rating');
        }
    }

});

//apply my rating
$(function(){
    var o =$('.product-rating-container');
    var status = o.data('status');
    if(status == 'yes'){
       var rating = o.data('myrating');
        ratingMeter(rating)
    }

});


function ratingMeter(len){
   // alert(len);
    for(var j=1;j < 6;j++){
        $('.star'+j).removeClass('background-rating');
    }
    var i = 1;
    do{
        $('.star'+i).addClass('background-rating');
        i++;
    }
    while( len >= i);
    return true;
}

$('#myTab a').click(function (e) {
    e.preventDefault()
    $(this).tab('show')
});
$(document).on('click','#showRatings',function(){
    $('html, body').animate({
        scrollTop: $('#rating').offset().top - 120
    }, 500);
});


$(document).on("click",'#save-to-wishlist,.save-to-wishlist',function(){
    var btn = $(this);
    var notlist = true;
    if(btn.data('list') == "yes"){
        notlist = false;
    }
    var product_id = btn.data('id');
    var attributes ={};
    if(notlist){
        btn.attr('disabled',true);
        var i = 1;
        $('.attributes').each(function(){
            var k = $("#attr_"+i).val();
            var key = k.toString();
            attributes[key] = $(this).val();
            i++;
        });
    }
    $.ajax({
        url : baseUrl + "store/add_to_wishlist",
        data : {pid : product_id,'attrib' :attributes},
        type : 'POST',
        success : function(data){
            if(notlist){
                btn.attr('disabled',false);
            }
            notifyInfo(data)
        }
    });
    return false;
});

$(document).on('click','#remove_wish_list',function(){
    var product_id = $(this).data('pid');
    confirm.action(function(){
        $.ajax({
            url : baseUrl + "store/remove_wishlist",
            data : {pid : product_id},
            success : function(data){
                $('#product_'+product_id).fadeOut();
            }
        })
    });
    return false;
});
//add to cart
$(document).on('click','.add-to-cart,#add-to-cart',function(){
    var btn = $(this);
    var notlist = true;
    var product_id = btn.data('id');
    var count = $('.ProductCount');
    var previous_count = parseInt(count.text());
    if(btn.data('list') == "yes"){
        //we are adding to cart from list;
        btn.find('i').removeClass('ion-ios-cart');
        btn.find('i').addClass('ion-load-a');
        var sm = btn.data('sm');
        notlist = false;
    }else{
        var old_t = btn.text();
        var sm = $('#successMessage').val();
        var processing = $('#processing').val();
        btn.attr('disabled',true);
        btn.text(processing);
    }

   var i = 1;

   var attributes ={};
   if(notlist){
       $('.attributes').each(function(){
           var k = $("#attr_"+i).val();
           var key = k.toString();
           attributes[key] = $(this).val();
           i++;
       });
   }
    if($("#quantity").length){
        var quantity = $("#quantity").val();
    }else{
        var quantity = 1;
    }

    //console.debug(attributes);
    //send it to php to hold
    //new count
    //count.text(previous_count + 1);
    //console.log(JSON.stringify(attributes));
    // console.log(attributes);

   $.ajax({
        url : baseUrl + "store/add_to_cart",
       method : 'POST',
       data : {q : quantity, 'attrib' :attributes, 'pid' : product_id ,csrf_token : requestToken },
        success : function (data){
            var json = jQuery.parseJSON(data);
            if(notlist){
                btn.text(old_t);
                btn.attr('disabled',false);
            }
            count.text(json.count);
            //console.debug(data);
            $('.sidebar-cart').fadeIn();
            var modal = $("#store-modal");
            modal.modal('hide');
            if($('.mcart-products-wraper').length){
                $('.mcart-products-wraper').html(json.view);
            }
            //alertDialog(sm);
           notifySuccess(sm);
            if(notlist === false){
                btn.find('i').removeClass('ion-load-a');
                btn.find('i').addClass('ion-ios-cart');
            }
        }

    });
   return false;
});

function removeFromCart(id){
    var count = $('.ProductCount');
    confirm.action(function(){
        var productId = id;
        $('.product_'+productId).fadeOut();
        $('.product_'+productId).remove();
        $.ajax({
            url : baseUrl + "store/cart/remove",
            data : {pid : productId},
            success : function(data){
                count.text(data);
                update_quantity();
                $('.modal').modal('hide');
            }
        });
    });
    return false;
}

function adminAlertDialog(m){
        $('#alertModal').modal({show:true});
        if (m) $("#alertModal").find('.modal-body').html(m);
}

function updateOrderStatus(pid,s){
    var v = pid;
    //$(id).val()
    var status = $(s).val();
    $.ajax({
        url: baseUrl + "admincp/store/order/update",
        data : {pid:v,s : status}

    });

    adminAlertDialog($('#AdminMessage').val());

}
function printMe(){
    window.print();
}

function updateWithdrawStatus(wid,s){
    var v = wid;
    //$(id).val()
    var status = $(s).val();
    $.ajax({
        url: baseUrl + "admincp/store/withdrawal_requests/update",
        data : {wid:v,s : status}

    })
    adminAlertDialog($('#AdminMessage').val());
}
$(document).on('click','#clear-cart',function(){
    $('#confirmModal').modal({show:true});
    var body = $("#confirmModal").find('.modal-body');
    var clearMsg = $('#clearMsg').val();
    body.html(clearMsg);
    $('#confirm-button').unbind().click(function() {
        $('.cart-body').remove();
        $('.cart-foot').remove();
        $.ajax({
            url : baseUrl + "store/clear_cart"
        });
        $('#confirmModal').modal('hide');
        var count = $('.ProductCount');
        count.text(0);
    });
return false

});
function update_quantity(){
    var price = [];
    var quantity = [];
    var total = 0;
    var ap = $('.actualPrice');
    var eachTotal = [];
    $('.price').each(function(){
        price.push($(this).val());
    });
    $('.quantity-cart').each(function(){
        quantity.push($(this).val());
    });
    if(price.length > 0){
        for(var i =0; i < price.length;i++){
            // console.log(parseFloat(price[i].replace(',','')));
            total += (parseFloat(price[i].replace(',',''))) * parseInt(quantity[i]);
            eachTotal.push(parseFloat(price[i].replace(',','')) * parseInt(quantity[i]));
        }
        // console.log(Number(total).toFixed(2));
        var p=0;
        ap.each(function(){
            $(this).text(addCommas(Number(eachTotal[p]).toFixed(2)));
            p++;
        })
    }


    //console.log(price);
    //console.debug(total);
    var t = addCommas(Number(total).toFixed(2));
    $('.cart-total').text(t);
    // console.debug(total);
    return false;
}
$(document).on('click','#update-quantity',update_quantity);

function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

$(document).on('click','#toggleShipping',function(){
    if($('#toggleShipping').prop('checked')){
        $('#s_fn').val($('#b_fn').val());
        $('#s_ln').val($('#b_ln').val());
        $('#s_company').val($('#b_company').val());
        $('#s_em').val($('#b_em').val());
        $('#s_p').val( $('#b_p').val());
        $('#s_country').val($('#b_country').val());
        $('#s_a').val($('#b_a').val());
        $('#s_z').val($('#b_z').val());
        $('#s_city').val( $('#b_city').val());
    }else{
       $('#s_fn').val('');
        $('#s_ln').val('');
        $('#s_company').val('');
        $('#s_em').val('');
        $('#s_p').val('');
        $('#s_country').val('');
        $('#s_a').val('');
        $('#s_z').val('');
        $('#s_city').val('');

    }
});

function submitCheckout(f){
    var form = $(f);
    var btn = $("#submitOrder");
    var oldText = btn.text();
    var processing = $('#processing').val();
    btn.attr('disabled',true);
    btn.text(processing).css('font-size','18px');

    form.ajaxSubmit({
        url : form.attr('action'),
        success : function(data){
            var json = jQuery.parseJSON(data);
            if(json.status == 0){
                notifyError(json.msg.replace(/_/g,' '));
                btn.attr('disabled',false);
                btn.text(oldText);
            }else{
                //success
                window.location.href = json.msg;
            }
           // console.debug(data);
        },
        error : function(){

        }

    })

    return false
}

function showPaymentMethod(div){
    if(div == 'now'){
        $('#submitOrder').hide();
        $('.payment-method').show();

    }else{
        $('.payment-method').hide();
        $('#submitOrder').show();
    }

}

$(document).on("click","#submitOrder",function(){
    var btn = $("#submitOrder");
    var old_text = btn.html();

    if($('.paymentMethod').prop('checked')){
        btn.prop('disabled',true);
         btn.html($("#processing").val());
        window.location.href = baseUrl + "store/payment/ondelivery";
        return true;
        /*$.ajax({
            url  : baseUrl + "store/payment/ondelivery",
            success : function(data){
                var json = jQuery.parseJSON(data);
                if(json.status == 0){
                    notifyError(json.msg);

                    btn.prop('disabled',false);
                    btn.html(old_text);

                }else{
                    window.location.href = json.url;
                }

            }
        });*/
    }
});

$(document).on('click','#request-withdraw',function(){
    var obj = $('.widthGroup');
    if(obj.attr('data-type') == 'hide'){
        obj.attr('data-type','show') ;
        obj.fadeIn();
    }else{
        obj.attr('data-type','hide') ;
        obj.fadeOut();
    }
    return false;
});

function ToggleTwocheckout(){
    var obj = $('.TwoCheckout');
    if(obj.attr('data-type') == 'hide'){
        obj.attr('data-type','show') ;
        obj.fadeIn();
        $('html, body').animate({
            scrollTop: $(obj).offset().top - 120
        }, 500);
    }else{
        obj.attr('data-type','hide') ;
        obj.fadeOut();
    }
    return false;
}

function numbersOnly(input){
    var regex = /[^0-9-+]/gi;
    input.value = input.value.replace(regex,'');
}

$(document).on('click','#pay_to_seller_account',function(){
   // $('#main-wrapper').css({'opacity':'0.5'});
    var indicator = $('#pay_to_seller_account .indicator');
    indicator.fadeIn();
});
$(document).on('click','#TwoCheckoutBtn',function(){
    // $('#main-wrapper').css({'opacity':'0.5'});
    $(this).attr('disabled',true);
    $.ajax({
        url : baseUrl + "store/orders/checkout/payment",
        data :{action:'2CO'},
        success :function(data){
           $('.oid').val(data);
        }
    })
     $('#TwocheckoutForm').submit();
    $(this).attr('disabled',false);
});

$(document).on('click','.StoreMenuParent',function(){
    $(this).next('.storeMenuChildren').toggleClass('slide-down');
   // $(this).find('i').toggleClass('ion-chevron-down');
    return false;
});

$(document).on('mouseover','.parent',function(){
    $('.parent').find('i').attr('class','ion-plus');
    $('.parent ul').removeClass("add-slide-transition");
   $('.parent ul').addClass("remove-slide-transition");
    $(this).find('ul').removeClass('remove-slide-transition');
    $(this).find('ul').addClass('add-slide-transition');
    $(this).find('i').attr('class','ion-minus');
    return false;
});

/**compare product**/
$(document).on('click','.remove-compare-icon',function(){	
    var pid = $(this).data('p');
    $('#confirmModal').modal({show:true});
        $('#confirm-button').unbind().click(function() {
    
	
	$("#compare-"+pid).css('opacity',0.5);
	var cp = "#compare-"+pid;
	var rct = $("#remove-from-compare_txt").val(); //remove to compare text
    var atc = $("#add-to-compare_text").val(); //add to compare text
	var obj = $("#product-id-"+pid).find('.compare-add-me');
	  $('.pid-compare-trace-'+pid).hide();
	$.ajax({
       url : baseUrl + "products/ajax/compare",
       data : {'action' : 'add','p' : pid,'status':1},
       success : function(data){
		   var json = jQuery.parseJSON(data);
		  $("#compare-"+pid).fadeOut();
		   
          $(obj).attr('data-status',0);
          $(obj).find('span').html(atc);	
          notifyInfo(json.message); 		  
	   }
	});
            $('#confirmModal').modal('hide');
        });
	
	
});

$(document).on('click','.clear-compare',function(){
	$('#confirmModal').modal({show:true});
   $('#confirm-button').unbind().click(function() {
    
	$.ajax({
       url : baseUrl + "products/ajax/compare",
       data : {'action' : 'clear','status':1},
       success : function(data){
		   var json = jQuery.parseJSON(data)
          notifyInfo(json.message);
          $("#single-compare").find('table').html("Empty List"); 		  
	   }
	});
            $('#confirmModal').modal('hide');
        });
		return false;
});
$(document).on('click','.compare-add-me',function(){
   var obj = $(this);
   var status = $(obj).attr('data-status');
   var pid = $(this).data('id'); //product id
   var rct = $("#remove-from-compare_txt").val(); //remove to compare text
    var atc = $("#add-to-compare_text").val(); //add to compare text
    var nt = (status == 0) ? rct : atc; //new txt
    //console.log(atc+ ' plus '+rct);

   $.ajax({
       url : baseUrl + "products/ajax/compare",
       data : {'action' : 'add','p' : pid,'status':status},
       success : function(data){
		   //$(obj).find('span').html(nt);
           var json = jQuery.parseJSON(data);
           if(json.status == 403){
               //greater than 3
               notifyInfo(json.message);
               return false;
           }
           if(status == 0){
               $(obj).attr('data-status',1);
			   //we can not add compare to the list
			   if(!$("#compare-"+pid).length){
				   $('.extra-compare').append(json.view);
			   }   
			   notifySuccess(json.message);
			    $(obj).addClass('active');
           }else{
			   //if it 1, we can now remove it
			   $("#compare-"+pid).fadeOut();
               $(obj).attr('data-status',0);
			   notifyInfo(json.message);
			   $(obj).removeClass('active');
           };
           if($('.store-cc').length){
               $('.store-cc').html(json.count);
           }
       }
   });
    return false;
});