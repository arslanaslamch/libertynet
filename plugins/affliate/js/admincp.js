
function show_aff_details(id){
    var obj = $("#affDetails");
    var c = $(obj).find("#results")
    c.html("");
    obj.modal('show');
    obj.find(".indicator").fadeIn();
    $.ajax({
        url : baseUrl + "admincp/ajax",
        method: 'POST',
        data: {action: 'aff-details', id : id },
        success: function (data) {
            var json = jQuery.parseJSON(data);
            obj.find(".indicator").fadeOut();
            c.html(json.view);
        }
    });
    return false;

}
$(document).on('click','.submit-aff-filter',function(){
    var obj = $(this);
    obj.prop('disabled',true);
    //run ajax update affliates
    var st = $('#startDate').val();
    var et = $('#endDate').val();
    var nm = $("#name").val();
    var status = $("#status").val();

    var p = $("#admincp-aff-row");
    var c = p.find('tbody');
    //class
    c.html('');
    if (!isValidDate(st) || !isValidDate(et)) {
        alert('Invalid Date');
        return false
    }

    p.find('.indicator').fadeIn();
    $.ajax({
        url : baseUrl + "admincp/ajax",
        method: 'POST',
        data: {action: 'affliate-row', from_date: st, end_date: et,name : nm,status : status  },
        success: function (data) {
            var json = jQuery.parseJSON(data);
            p.find('.indicator').fadeOut();
            c.html(json.view);
            obj.prop('disabled', false);
        }
    });
   return false;
});
$(document).on('click','#reply-earnings',function(){
    var obj = $(this);
    var type = obj.data('type');
    var action = (type == 'earnings') ? 'reponse-earning' : 'request-respond';
    obj.prop('disabled',true);
    var m = $("#replyModal");
    var msg = $("#msg").val();
    var v = $("#sumit-value").val();
    var id = $("#eid").val();
    var c = $(".ajax-response");
    $.ajax({
        url : baseUrl + "admincp/ajax",
        method : 'POST',
        data: {action: action , id : id, v : v, msg : msg},
        success: function (data) {
            m.modal('hide');
            var json = jQuery.parseJSON(data);
            //empty the action field
            if(action == 'request-respond'){
                $('#response-time-'+id).html(json.da);
            }
            $('#contain-'+id).html('N/A');
            //status reply field
            $("#stat-"+id).html(json.txt);
            $("#reason-"+id).html(msg);
            c.html(json.view);
            obj.prop('disabled',false);
            c.fadeOut(1500);
        }
    });
   return false;
});

$(document).on('click','.earn-action',function(){
    var obj = $(this);
    var id = obj.data('id');
    var m = $("#replyModal");
    m.find('#eid').val(id);
    m.modal('show');
   return false;
});

$(document).on('click','.approve-aff',function(){
    var obj = $(this);
    var y = obj.data('yes');
    obj.prop('disabled',true);
    var id = obj.data('id');
    var message = $(this).data('message');
    var m = $("#admin-confirm-modal");
    var c = $(".ajax-response");
    if (message != undefined) {
        m.find('.modal-body').html(message);
    }
    m.modal('show');
    m.find('.admin-confirmed').unbind().click(function() {
        //run ajax update affliates
        $.ajax({
            url : baseUrl + "admincp/ajax",
            method : 'POST',
            data: {action: 'approve-affliate', id : id},
            success: function (data) {
                m.modal('hide');
                var json = jQuery.parseJSON(data);
                c.append(json.view);
                $("#stat-"+id).html(y);
                obj.fadeOut();
            }
        })
    });
   // m.modal('hide');
   return false;
});

$(document).on('click','.delete-aff',function(){
    var obj = $(this);
    var y = obj.data('yes');
    obj.prop('disabled',true);
    var id = obj.data('id');
    var message = $(this).data('message');
    var m = $("#admin-confirm-modal");
    var c = $(".ajax-response");
    if (message != undefined) {
        m.find('.modal-body').html(message);
    }
    m.modal('show');
    m.find('.admin-confirmed').unbind().click(function() {
        //run ajax update affliates
        $.ajax({
            url : baseUrl + "admincp/ajax",
            method : 'POST',
            data: {action: 'delete-affliate', id : id},
            success: function (data) {
                m.modal('hide');
                var json = jQuery.parseJSON(data);
                c.append(json.view);
                $("#aff-"+id).fadeOut();
            }
        })
    });
   // m.modal('hide');
   return false;
});