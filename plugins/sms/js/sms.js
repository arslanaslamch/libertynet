//2fa start
function generate_backup_code(){
    var d = $(this);
    //d.prop('disabled',true);
    var modal = $("#backupModal");
    modal.modal('show');
    modal.find('.indicator').fadeIn();
    $.ajax({
        url : baseUrl + "sms/two-factor",
        data : {action : 'generate'},
        success : function(data) {
            //d.prop('disabled',false);
            modal.find('.indicator').hide();
            modal.find('.code-lists').html(data);
        }
    });
    return false;
}

function download_btxt(){
    $.ajax({
        url : baseUrl + "sms/two-factor",
        data : {action : 'download-txt'},
        success : function(data) {
            //window.location.href = data;
        }
    });
}

function copyBackupToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
}

$(document).on('click','#copybcup',function(){
    var o = $(this);
    $('#texta-code-g').select();
    document.execCommand("copy");
    notifySuccess(o.data('copied'));
   return false;
});

//2fa end

function resend_sms(){
    var el = $('#resend');
   if(document.getElementById('resend').dataset.state === 'no'){
       document.getElementById('resend').dataset.state = 'yes';
       el.html('Sending...');
       $.ajax({
           url : baseUrl + "signup/resend",
           success : function(data) {
               el.html(data);
           }
       })
   }
}

function activate_user(div){
    var id = div;
    $.ajax({
        url: baseUrl + "sms/admin_activate?id="+id,
        success : function(data){
         $('#'+id).fadeOut();
        }
    });
}

function verify_code(){
    var code = $('#code').val();
    $.ajax({
        url : baseUrl + "sms/verify?code="+code,
        success: function (data){

            var json = jQuery.parseJSON(data);
            if(json.status == 0){
                notifyInfo(json.message);
            }else{
                notifySuccess(json.message);
                $(location).attr('href', baseUrl+json.url);
            }
       }
    });
    return false;
}

function verify_code_forgot_sms(){
    var code = $('#code').val();
    $.ajax({
        url : baseUrl + "forgot-password/sms?code="+code,
        success: function (data){

            var json = jQuery.parseJSON(data);
            if(json.status == 0){
                notifyInfo(json.message);
            }else{
                notifySuccess(json.message);
                code = json.hash;
                $(location).attr('href', baseUrl+'reset/password?code='+code);
            }
        }
    })
}
function send_code_forgot_password(){
    var ph = $("#hidden").val($("#input-number").intlTelInput("getNumber"));
    var inputed = $('#input-number').val();
    //var location = $('#location').val();
    var err = $('#error_message').val();
    if(inputed == '' || inputed == null){ return notifyError(err);}
    var username = $('#username').val();
    if(username == undefined){username='';};
    var pnum = $('#hidden').val();
    // var sending = document.getElementById('submit_sms').dataset.sending;
    var sending =  $('#submit_sms').data('sending');
    $('#submit_sms').val(sending);
    $.ajax({
        url: baseUrl + "forgot-password/sms",
        data:{
            ph:pnum

        },
        success: function(data){
            var json = jQuery.parseJSON(data);

            if(json.status==0){
                notifyError(json.message);
            }else{
                $('#submit_sms').hide();
                $('#input-number').val(inputed).prop('disabled', true);
                // $("#input-number");
                $('#confirm').show();
                $('#change_number').show()
            }
        }
    });
}


function send_code(){
    var ph = $("#hidden").val($("#input-number").intlTelInput("getNumber"));
    var inputed = $('#input-number').val();
    //var location = $('#location').val();
    var err = $('#error_message').val();
    if(inputed == '' || inputed == null){ return notifyError(err);}
    var username = $('#username').val();
    if(username == undefined){username='';};
    var pnum = $('#hidden').val();
   // var sending = document.getElementById('submit_sms').dataset.sending;
    var sending =  $('#submit_sms').data('sending');
    $('#submit_sms').val(sending);
    $.ajax({
        url: baseUrl + "signup/phone",
        data:{
            ph:pnum,
            username:username
        },
        success: function(data){
            var json = jQuery.parseJSON(data);

            if(json.status==0){
                notifyError(json.message);
            }else{
                $('#submit_sms').hide();
                $('#input-number').val(inputed).prop('disabled', true);
                // $("#input-number");
                $('#confirm').show();
                $('#change_number').show()
            }
       }
    });
}
function skip_verification(div){
var uid = div;
    $.ajax({
        url : baseUrl + "signup/skip_sms",
        data:{
            uid:uid
        },
        success: function (data){
            //alert(data);
            json = jQuery.parseJSON(data)
            if(json.status == 0){
                $(location).attr('href',baseUrl +"user/welcome");
            }else{
                $(location).attr('href',baseUrl +"feed");
            }
        }
    });
}
function change_number(){
    $('#submit_sms').show();
    $('#confirm').hide();
    $('#input-number').prop('disabled', false);
    $('#change_number').hide();
    var t = $('#translatedcode').val();
    $('#submit_sms').val(t);
}
$('#sms-form').submit(function(){
    $("#hidden").val($("#input-number").intlTelInput("getNumber"));
});
function numbersOnly(input){
    var regex = /[^0-9-+]/gi;
    input.value = input.value.replace(regex,'');
}

function runSmsJs(){
    $("#input-number,#InviteFriendSMS").intlTelInput({
        autoPlaceholder: true,
        initialCountry: "auto",
        geoIpLookup: function(callback) {
            $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "";
                callback(countryCode);
            });
        }
    });
}
if(!$('#admin-confirm-modal').length){
    addPageHook('runSmsJs');
}

$(function(){
    runSmsJs()
});

$(document).on('click','#SubmitInviteFriends',function(){
     var ph = $('#InviteFriendSMS').val();
    if(ph == ''){
        return notifyError();
    }
    if(!$("#InviteFriendSMS").intlTelInput("isValidNumber")){
       notifyError($('#invalidError').val());
       return false;
    }

    var phone = $("#hidden").val($("#InviteFriendSMS").intlTelInput("getNumber"));


    var num = phone.val();
   // return false;
    var i = $('.sms-invite');
    i.find('input').hide();
    i.find('.indicator').fadeIn();
    $.ajax({
        url : baseUrl + "sms/invite_friends",
        data : {n : num},
        success : function(data){
            // console.log(data);
            var json = jQuery.parseJSON(data);
            i.find('.indicator').hide();
            i.find('input').show();
            i.find('input').val('');
            notifySuccess(json.message);

        }
    });
    return false;
});

/** new update **/
$(document).on('click','.show-send-modal',function(){
    $("#phn").val($(this).data('id'));
    $("#sendSMSModal").modal('show');
   return false;
});

$(document).on('click','.show-edit-modal',function(){
    $("#phone_num").val($(this).data('ph'));
    $("#uid").val($(this).data('id'));
    $("#name").val($(this).data('name'));
    $("#editNumberModal").modal('show');
   return false;
});

$("#msg").keyup(function() {
    $('#count').html($(this).val().length)
}).keyup();


$(document).on('click','#sendSingleMsg',function(){
    var obj = $(this);
    obj.prop('disabled',true);
    if(obj.data('type') == 'edit'){
        var n = $("#phone_num").val();
        var uid = $("#uid").val();
        //var nm = $("#uid").val();
        $.ajax({
            url : baseUrl + "admincp/send/single",
            data : {n : n, uid : uid, t : 'edit'},
            method : 'POST',
            success : function(data){
                $("#editNumberModal").modal('hide');
                location.reload();
            },
            error : function(data){
            $("#editNumberModal").modal('hide');
            alert('Error Occured... Try Again');
        }
        });
        return false;
    }
    var n = $('#phn').val();
    var msg = $("#msg").val();
    $.ajax({
        url : baseUrl + "admincp/send/single",
        data : {n : n, msg : msg, t : 'send'},
        method : 'POST',
        success : function(data){
            $("#sendSMSModal").modal('hide');
            var json = jQuery.parseJSON(data);
            $('.wrapper-content .result').html(json.view);
            obj.prop('disabled',false);
        }
    });
    return false;
});

