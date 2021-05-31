//contest entry owner
$(document).on('click','.re-by-ca',function () {
    var o = $(this);
    var ty = o.data('etype');
    var cid = o.data('cid');
    var eid = o.data('eid');
    $('#confirmModal').modal({show: true});
    $('#confirm-button').unbind().click(function () {
        o.closest('.media-sm').css('opacity', '0.5');
        $.ajax({
            url: baseUrl + 'contest/ajax?csrf_token=' + requestToken + '&cid=' + cid + '&eid='+eid+'&ty='+ty,
            type: 'GET',
            success: function (data) {
                o.closest('.media-sm').fadeOut();
                $('#confirmModal').modal('hide');
            }
        })
    });
   return false;
});
//promote
$(document).on('click','#change-photo-p-contest',function(){
   var o = $(this);
   var v = $('.contest-iframe-wrapper textarea').val();
   if(o.prop('checked') === true){
       //we want show images
       $('.contest-iframe-wrapper textarea').val(v.replace('img=0','img=1'));
       $('.contest-image-promote').fadeIn();
   }else{
       $('.contest-iframe-wrapper textarea').val(v.replace('img=1','img=0'));
       $('.contest-image-promote').hide();
   }
});

$(document).on('click','#change-description-p-contest',function(){
   var o = $(this);
   var v = $('.contest-iframe-wrapper textarea').val();
   if(o.prop('checked') === true){
       //we want show images
       $('.contest-iframe-wrapper textarea').val(v.replace('desc=0','desc=1'));
       $('.sub-contest-description').fadeIn();
   }else{
       $('.contest-iframe-wrapper textarea').val(v.replace('desc=1','desc=0'));
       $('.sub-contest-description').hide();
   }
});

$(document).on('click','.contest-iframe-wrapper textarea',function(){
   $(this).select();
});
$(document).on('click','#promote-contest',function(){
   var m = $("#contestPromoteModal");
   var cid = $(this).data('cid');
    m.find('.contest-promote-result').html('');
   m.find('.indicator').fadeIn();

   m.modal("show");
    $.ajax({
        url: baseUrl + 'contest/ajax',
        type: 'POST',
        data: {csrf_token: requestToken, action: 'promote-contest', cid: cid},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            m.find('.indicator').hide();
            m.find('.contest-promote-result').html(json.view);
        }
    });
    return false;
   return false;
});

/** photo contest js start **/
function upload_contest_photos(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $("#photo-url-sb").val(''); //set the url photo to empty
            $('#preview-image-c-entry').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}


function uploadContestUrlImage() {
    var link = $(".urlLink");
    var linkValue = link.val();
    $("#photo-url-sb").val(linkValue);
    if (link !== '') {
        $('#preview-image-c-entry').attr('src', linkValue);
        $(".urlLink").val('');
        $('.modal-photo').modal('hide');
        /*$('#preview-image-c-entry').on('error',function() {
            notifyError('Image does not exist !!');
             $('#preview-image-c-entry').attr('src', '');
        });*/

    } else {
        var error = "The field must not be empty";
        notifyError(error);
    }
}

/** photo contest js end **/

$(document).on('click', "#ann-btn", function () {
    var o = $(this);
    $("#annModal").find("#acid").val(o.data('acid'));
    $("#annModal").find("#atype").val(o.data('atype'));
    $("#annModal").find("#aid").val(o.data('aid'));
    $("#annModal").find("#acontent").val('');
    $("#annModal").find("#atitle").val('');
    $("#annModal").find("#alink").val('');
    var m = $("#annModal").modal("show");
    return false;
});

$(document).on('click', ".edit-contest-annoucement", function () {
    var o = $(this);
    $("#annModal").find("#acid").val(o.data('acid'));
    $("#annModal").find("#atype").val(o.data('atype'));
    $("#annModal").find("#aid").val(o.data('aid'));
    $("#annModal").find("#acontent").val(o.data('acontent'));
    $("#annModal").find("#atitle").val(o.data('atitle'));
    $("#annModal").find("#alink").val(o.data('alink'));
    var m = $("#annModal").modal("show");
    return false;
});

//accordion
addPageHook('runContestAcc');
$(function () {
    runContestAcc()
});

function runContestAcc() {
    if ($("#contest-ann").length) {
        $("#contest-ann").accordion({
            collapsible: true,
            active: false,
            heightStyle: "content"
        });
    }
}

$(document).on('click', '#contest-ann h3', function () {
    $(this).find('i').toggleClass("ion-chevron-down ion-chevron-up");
});
$(document).on('click', '#vote-contest-entry', function () {
    var o = $(this);
    var status = o.attr('data-status');
    var utext = o.data('un');
    var vtext = o.data('vote');
    if (status == 0) {
        //you are just voting
        o.removeClass('btn-info').addClass('btn-danger');
        o.html('<i class="ion-thumbsdown"></i>  ' + utext);
        var nstatus = 1;
    }

    if (status == 1) {
        //we want to remove voting
        o.removeClass('btn-danger').addClass('btn-info');
        o.html('<i class="ion-thumbsup"></i>  ' + vtext);
        var nstatus = 0;
    }
    o.attr('data-status', nstatus);
    o.prop('disabled', true);
    var eid = $("#eid").val();
    var cid = $("#cid").val();
    var et = $("#et").val();
    $.ajax({
        url: baseUrl + 'contest/ajax',
        type: 'POST',
        data: {csrf_token: requestToken, action: 'vote-entry', cid: cid, etype: et, eid: eid, status: status},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            o.prop('disabled', false);

            $('#vote-count').html(json.votes);
            notifySuccess(json.msg);
        },
        error : function () {
            notifyError('unable to vote at the moment');
        }
    });
    return false;
});

$(document).on('click', '#c-accpt-join', function () {
    var o = $(this);
    o.prop('disabled', true);
    var id = o.data('cid');
    $.ajax({
        url: baseUrl + 'contest/ajax',
        type: 'POST',
        data: {csrf_token: requestToken, action: 'join-contest', id: id},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            o.prop('disabled', true);
            notifySuccess(json.msg);
            loadPage(json.url);
        }
    });
    return false;
});

$(document).on('click', '#join-contest', function () {
    $("#contestTermsModal").modal('show');
    return false;
});

$(document).on('click', '#invite-contest-friends', function () {
    var id = $(this).data('cid');
    var modal = $("#contestInviteModal");
    modal.find('.indicator').fadeIn();
    modal.find('.contest-ajax-result').html('');
    $("#contestInviteModal").modal("show");
    $.ajax({
        url: baseUrl + 'contest/ajax',
        type: 'POST',
        data: {csrf_token: requestToken, action: 'invite', id: id},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            modal.find('.indicator').hide();
            modal.find('.contest-ajax-result').html(json.view);
            //modal.modal('hide');
        }
    });
    return false;
});

/*import start**/
$(document).on('click', '.follow-contest', function () {
    var o = $(this);
    var s = o.attr('data-status');
    var uf = o.data('unfollow');
    var f = o.data('follow');
    var fing = o.data('following');
    var id = o.data('id');
    //var dc = $("#dflc");
    if (s == 1) {
        //it is currently following, we want to unfollow
        o.attr('data-status', 0);
        //o.removeClass('dfollowing');
        //o.addClass('dnotfollowing');
        o.html('<i class="ion-android-checkbox-blank"></i>' + f);
        //min-1
        //dc.html(parseInt(dc.html()) - 1);
    } else {
        //is currently not following, we want to follow now
        o.attr('data-status', 1);
        //o.addClass('dfollowing');
        //o.removeClass('dnotfollowing');
        o.html('<i class="ion-android-checkbox"></i>' + uf);
        //plus 1
        //dc.html(parseInt(dc.html()) + 1);
    }

    $.ajax({
        url: baseUrl + "contest/ajax",
        method: 'POST',
        data: {action: 'follow', status: s, id: id},
        success: function (data) {

        }
    });
    return false;
});

window.running_contest = false;
$(document).on('keyup', '#friend-name', function () {
    var vl = $(this).val();
    var i = $('.friend-bring-result-contest .indicator');
    var c = $('.friend-bring-result-contest');
    if (vl.length > 2) {
        c.fadeIn();
        i.fadeIn();
        $('.friend-bring-result-contest .r').html("");
        //$('.friend-bring-result-contest .r').removeClass('slimscroll');
        var s = window.running_contest;
        //if it is currently ongoing, do not allow new process
        if (s) return false;
        window.running_contest = true;
        $.ajax({
            url: baseUrl + "contest/ajax",
            method: 'POST',
            data: {action: 'search-friends', term: vl},
            success: function (data) {
                var json = jQuery.parseJSON(data);
                i.hide();
                $('.friend-bring-result-contest .r').html(json.v);
                // $('.friend-bring-result-contest .r').addClass('slimscroll').attr('data-height','250px');
                window.running_contest = false;
                reloadInits();
            }
        });
    } else {
        c.hide();
        i.hide();
        $('.friend-bring-result-contest .r').html("");
        //$('.friend-bring-result-contest .r').removeClass('slimscroll');
    }
});

//when we click on friend
$(document).on('click', '.choosing-friends-contest', function () {
    var o = $(this);
    var id = o.data('id');
    var name = o.data('name');
    var l = o.data('l');
    if ($('#uid' + id).length) {
        $('#uid' + id).remove();
    }
    var uid = 'uid' + id;
    var html = '<p class="cuser-line" id="' + uid + '" ><input checked type="checkbox" name="val[friends][]" value="' + id + '" />  ' + name + '</p>'
    $(".contest-friends-list .slimscroll").prepend(html);
    notifySuccess(name + "  " + l);
    $('.friend-bring-result-contest').hide();
    reloadInits();
    return false;
});

$(document).on('click', 'body', function (e) {
    if (!$(e.target).closest($('.friend-bring-result-contest')).length) $('.friend-bring-result-contest').fadeOut();
});

function checkAllcontest() {
    $('.cuser-line').find('input').prop('checked', true);
    return false;
}

function unCheckAllcontest() {
    $('.cuser-line').find('input').prop('checked', false);
    return false;
}

$(document).on('click', '#import-all-friends', function () {
    var o = $(this);
    o.prop('disabled', true);
    $.ajax({
        url: baseUrl + "contest/ajax",
        method: 'POST',
        data: {action: 'all-friends'},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            if (json.status) {
                $(".contest-friends-list .slimscroll").html(json.v);
                reloadInits();
            } else {
                notifyError(json.v);
            }
            o.prop('disabled', false);
        }
    });
    return false;
});

/*import end**/

function date_time_picker_contest() {
    var today = new Date();
    //contest start date
    if($('.start-c-date').length){
        var picker = tui.DatePicker.createRangePicker({
            startpicker: {
                //date: today,
                input: '.start-c-date',
                container: '.c-start-date-container'
            },
            endpicker: {
                //date: today,
                input: '.end-c-date',
                container: '.c-end-date-container'
            },
            timepicker: {
                layoutType: 'tab',
                inputType: 'spinbox'
            },
            format: 'yyyy-MM-dd HH:mm A',
            selectableRanges: [
                [today, new Date(today.getFullYear() + 1, today.getMonth(), today.getDate())]
            ]
        });
    }
    //update contest start date
    if($('.start-c-date-update')){
        //today
        //alert(today);
        //var now = new Date($('.start-c-date-update').data('now') * 1000);
        //var end = new Date($('.start-c-date-update').data('end') * 1000);
        var picker = tui.DatePicker.createRangePicker({
            startpicker: {
                //date: $('.start-c-date-update').val(),
                input: '.start-c-date-update',
                container: '.c-start-date-container'
            },
            endpicker: {
                //date: $('.end-c-date-update').val(),
                input: '.end-c-date-update',
                container: '.c-end-date-container'
            },
            timepicker: {
                layoutType: 'tab',
                inputType: 'spinbox'
            },
            format: 'yyyy-MM-dd HH:mm A'

        });
    }

    //submission date
    if($('.start-se-date').length){
        var picker_entries = tui.DatePicker.createRangePicker({
            startpicker: {
                //date: today,
                input: '.start-se-date',
                container: '.se-start-date-container'
            },
            endpicker: {
                //date: today,
                input: '.end-se-date',
                container: '.se-end-date-container'
            },
            timepicker: {
                layoutType: 'tab',
                inputType: 'spinbox'
            },
            format: 'yyyy-MM-dd HH:mm A',
            selectableRanges: [
                [today, new Date(today.getFullYear() + 1, today.getMonth(), today.getDate())]
            ]
        });
    }

    //update submition date
    if($('.start-se-date-update')){
        var picker_entries = tui.DatePicker.createRangePicker({
            startpicker: {
                //date: today,
                input: '.start-se-date-update',
                container: '.se-start-date-container'
            },
            endpicker: {
                //date: today,
                input: '.end-se-date-update',
                container: '.se-end-date-container'
            },
            timepicker: {
                layoutType: 'tab',
                inputType: 'spinbox'
            },
            format: 'yyyy-MM-dd HH:mm A'
        });
    }

    //voting
    if($('.start-v-date').length){
        var picker_voting = tui.DatePicker.createRangePicker({
            startpicker: {
                //date: today,
                input: '.start-v-date',
                container: '.v-start-date-container'
            },
            endpicker: {
                //date: today,
                input: '.end-v-date',
                container: '.v-end-date-container'
            },
            timepicker: {
                layoutType: 'tab',
                inputType: 'spinbox'
            },
            format: 'yyyy-MM-dd HH:mm A',
            selectableRanges: [
                [today, new Date(today.getFullYear() + 1, today.getMonth(), today.getDate())]
            ]
        });
    }

    //update voting
    if($('.start-v-date-update')){
        var picker_voting = tui.DatePicker.createRangePicker({
            startpicker: {
                //date: today,
                input: '.start-v-date-update',
                container: '.v-start-date-container'
            },
            endpicker: {
                //date: today,
                input: '.end-v-date-update',
                container: '.v-end-date-container'
            },
            timepicker: {
                layoutType: 'tab',
                inputType: 'spinbox'
            },
            format: 'yyyy-MM-dd HH:mm A'

        });
    }

}

$(function () {
    date_time_picker_contest();
if(!$('#admin-confirm-modal').length){
    addPageHook('date_time_picker_contest');
}
});

//admncp start
$('.switch').on('click', function (e) {
    if (e.target.classList.contains('contest-slider')) {
        var checked = $(this).closest('.switch').find('input:checked');
        var notChecked = $(this).closest('.switch').find('input:not(:checked)');
        checked.attr('checked', false);
        notChecked.attr('checked', true);
        notChecked.click();
        //get the value of the one not checked that is now clicked to be checked
        //i am enabling
        $.ajax({
            url: baseUrl + 'admincp/contest/ajax?cid=' + notChecked.data('cid') +
                '&csrf_token=' + requestToken + '&v=' + notChecked.val()+ '&action=featured-contest',
        });
    }
});




