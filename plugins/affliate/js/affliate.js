//
$(document).on('click','.search-request',function(){
    var obj = $(this);
    var ad = obj.data('ad');
    var h = obj.html();
    obj.prop('disabled', true);
    var st = $('#startDate').val();
    var et = $('#endDate').val();
    var status = $('#status').val();
    var p = $("#request-manager");
    var c = p.find('tbody');

    if (!isValidDate(st) || !isValidDate(et)) {
        notifyError("Invalid Date, Try Again");

        //if it is admin
        if(ad == 'yes'){
            alert('Invalid Date, Try Again');
        }
        return false
    }

    p.find('.indicator').fadeIn();
    $.ajax({
        url: baseUrl + "affliate/ajax",
        method: 'POST',
        data: {action: 'request-duration', from_date: st, end_date: et, ad : ad, status : status },
        success: function (data) {
            var json = jQuery.parseJSON(data);
            p.find('.indicator').fadeOut();
            c.html(json.view);
            obj.prop('disabled', false);
        }
    });
    return false;
});
$(document).on('click','#cancel-request',function(){
    var obj = $(this);
    var rid = obj.data('id');
    $('#confirmModal').modal({show:true});
    $('#confirm-button').unbind().click(function() {
        $.ajax({
            url: baseUrl + "affliate/ajax",
            method: 'POST',
            data: {action: 'cancel-request', rid : rid },
            success: function (data) {
                var json = jQuery.parseJSON(data);
                $('#confirmModal').modal('hide');
                $("#request-"+rid).fadeOut(500);
                notifySuccess(json.txt);
            }
        });
    });
    return false;
});

$(document).on('click','.submit-money-request',function(){
    var m = $("#RequestAff");
    m.modal('show');
    var obj = $(this);
    obj.prop('disabled',true);
    var pnt = $("#pnt").val();
    var msg = $('#msg').val();
    var os = 0;
    if($("#emoney-checked-op").length){
        var os = ($('#emoney-checked-op').prop('checked')) ? 1 : 0 ;
    }
    var c = $("#requstTable");//container;
    $.ajax({
        url: baseUrl + "affliate/ajax",
        method: 'POST',
        data: {action: 'request-payout', pnt : pnt, msg : msg, os : os},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            if(json.status == 1){
                m.modal('hide');
                if($("#empty-row-request").length){
                    $("#empty-row-request").fadeOut();
                }
                c.find('tbody').append(json.view);
                notifySuccess(json.msg);
            }else{
                notifyError(json.msg);
                m.modal('hide');
            }
            obj.prop('disabled', false);
            //reset these
            $("#pnt").val('');
            $('#msg').val('');
        },
        error : function(){
            m.modal('hide');
            notifyError('Oops! something is not right. Try Again');
        }
    });
   return false;
});
$(document).on('click','.request-m-button',function(){
   var m = $("#RequestAff");
    m.modal('show');
   return false;
});

$(document).on('click','#modify-aff-c-info',function(){
   var m = $("#ContactEditModal");
    m.modal('show');
   return false;
});
function reset_canvas(){
    $('#canvas').remove();
    $('#canvasTwo').remove();
    $('.linchart').append('<canvas id="canvas"></canvas>');
    $('.piechart').append('<canvas id="canvasTwo"></canvas>');
}
$(document).on('click','#line-chart-menu',function(){
    $('.piechart').hide();
    $('.linchart').show();
    var obj = $(this);
    $('#pie-chart-menu').addClass('btn-secondary').removeClass('btn-info');
    obj.removeClass('btn-secondary').addClass('btn-info');
    $("#refresh-aff-stat").click();
    return false;
    var cf = get_config('no');
    var ctx = document.getElementById("canvas").getContext("2d");
    window.myLine = new Chart(ctx, cf);
    return false;
});

$(document).on('click','#pie-chart-menu',function(){
    reset_canvas();
    $('.linchart').hide();
    $('.piechart').show();
    var obj = $(this);
    $('#line-chart-menu').addClass('btn-secondary').removeClass('btn-info');
    obj.removeClass('btn-secondary').addClass('btn-info');
    var cf = get_pie_config();
    var ctx = document.getElementById("canvasTwo").getContext("2d");
    window.pie = new Chart(ctx, cf);
    return false;
});

$(document).on('click','#refresh-aff-stat',function(){
    reset_canvas();
    var obj = $(this);
    obj.prop('disabled', true);
    var st = $('#startDate').val();
    var et = $('#endDate').val();
    var status = $('input[name=status]:checked').val();
    console.log()
    var p = $(".chart-tins");
    var c = p.find('tbody');

    if (!isValidDate(st) || !isValidDate(et)) {
        notifyError("Invalid Date, Try Again");
        return false;
    }
    p.find('.indicator').fadeIn();
    $.ajax({
        url: baseUrl + "affliate/ajax",
        method: 'POST',
        data: {action: 'statistics', from_date: st, end_date: et, status: status},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            if(json.status == 1){
                //process
                //d is the data array
                //l is the is the label, equ, date
                $('#json_encode_date').val(json.l);
                $('#json_encode').val(json.d);
                $('#p-data').val(json.pd);
                var cf = get_config('ajax',json.l,json.d);
                var ctx = document.getElementById("canvas").getContext("2d");
                window.myLine = new Chart(ctx, cf);
                p.find('.indicator').fadeOut();
                obj.prop('disabled', false);
            }else{
                notifyError('Oops! something is not right. Try Again');
            }
        },
        error : function(){
            notifyError('Oops! something is not right. Try Again');
        }
    });
   return false;
});

function get_pie_config(){
    var lb = jQuery.parseJSON($("#label-encode").val());
    var pd = jQuery.parseJSON($('#p-data').val()); //pdata
    var pc = jQuery.parseJSON($('#p-color').val()); //pcolor

    var config = {
        type: 'pie',
        data: {
            datasets: [{
                data: pd,
                backgroundColor: pc,
                label: 'Dataset 1'
            }],
            labels: lb
        },
        options: {
            responsive: true
        }
    };
    return config;
}

function get_config(t, l, d){
    if(t != 'ajax'){
        var l = jQuery.parseJSON($('#json_encode_date').val());
        var d = jQuery.parseJSON($('#json_encode').val());
    }
    var ct = $("#chartTitle").val();
    var tm = $("#chartTime").val();
    var pe = $("#pointEarned").val();
    //var def = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	//console.log(l);
    var config = {
        type: 'line',
            data: {
        labels: l,
            datasets: d
    },
        options: {
            responsive: true,
                title: {
                display: true,
                    text: ct
            },
            tooltips: {
                mode: 'index',
                    intersect: false,
            },
            hover: {
                mode: 'nearest',
                    intersect: true
            },
            scales: {
                xAxes: [
                    {
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: tm
                        }
                    }
                ],
                    yAxes: [
                    {
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: pe
                        }
                    }
                ]
            }
        }
    };
    return config;
}


window.onload = function () {
    if($('#canvas').length){
        var config = get_config('no',0,0);
        var ctx = document.getElementById("canvas").getContext("2d");
        window.myLine = new Chart(ctx, config);
    }
};


$(document).on('click', '.submit-filter-com-track', function () {
    var obj = $(this);
    var h = obj.html();
    obj.prop('disabled', true);
    var st = $('#startDate').val();
    var et = $('#endDate').val();
    var name = $('#name').val();
    var ptype = $('#ptype').val();
    var status = $('#status').val();
    var ad = $('#ad').val();
    var p = $("#comissonTrack");
    var c = p.find('tbody');

    if (!isValidDate(st) || !isValidDate(et)) {
        notifyError("Invalid Date, Try Again");
        return false
    }
    p.find('.indicator').fadeIn();
    $.ajax({
        url: baseUrl + "affliate/ajax",
        method: 'POST',
        data: {action: 'ajax-commission-tracking', ad : ad, from_date: st, end_date: et, name: name, ptype: ptype, status: status},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            p.find('.indicator').fadeOut();
            c.html(json.view);
            obj.prop('disabled', false);
        }
    });
    return false;
});

$(document).on('click', '.submit-filter-date', function () {
    var obj = $(this);
    var h = obj.html();
    obj.prop('disabled', true);
    var st = $('#startDate').val();
    var et = $('#endDate').val();
    var p = $("#lnkt");
    var c = p.find('tbody');

    if (!isValidDate(st) || !isValidDate(et)) {
        notifyError("Invalid Date, Try Again");
        return false
    }

    p.find('.indicator').fadeIn();
    $.ajax({
        url: baseUrl + "affliate/ajax",
        method: 'POST',
        data: {action: 'ajax-search-date-link', from_date: st, end_date: et },
        success: function (data) {
            var json = jQuery.parseJSON(data);
            p.find('.indicator').fadeOut();
            c.html(json.view);
            obj.prop('disabled', false);
        }
    });
    return false;
});

function isValidDate(dateString) {
    // First check for the pattern
    if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString))
        return false;

    // Parse the date parts to integers
    var parts = dateString.split("/");
    var day = parseInt(parts[1], 10);
    var month = parseInt(parts[0], 10);
    var year = parseInt(parts[2], 10);

    // Check the ranges of month and year
    if (year < 1000 || year > 3000 || month == 0 || month > 12)
        return false;

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
};

$(document).on('click', '.preview-code', function () {
    var m = $("#quick-post-modal");
    m.find('.modal-dialog').removeClass('modal-md').addClass('modal-md');
    m.find('.modal-body').html($("#copy-codey textarea").val());
    m.modal('show');
    return true;
});

$(document).on('click', '#copy-codey textarea', function () {
    $(this).focus().select();
});

function change_user_commsion_rules(d) {
    var obj = $(d);
    var v = $(d).val();
    var p = $('#table-comission-rule');
    p.find('.indicator').fadeIn();
    obj.prop('disabled', true);
    var c = p.find('tbody');
    //c.html('');
    $.ajax({
        url: baseUrl + "affliate/ajax",
        method: 'POST',
        data: {action: 'front-end', v: v},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            p.find('.indicator').fadeOut();
            obj.prop('disabled', false);
            c.html(json.view);
        }
    });
}

function submit_af_link(o) {
    var form = $(o);
    notifyInfo('processing...');
    form.ajaxSubmit({
        url: form.attr('action'),
        success: function (data) {
            var json = jQuery.parseJSON(data);
            if (json.status == 1) {
                $('.auto_l').val(json.str);
                notifySuccess('successful');
            } else {
                notifyError(json.msg);
            }
        },
        error: function () {
            notifyError('Oops! Error Ocurred, Try Again');
        }
    });
    return false;
}


$('.links-container input').on('focus', function () {
    var $this = $(this)
        .one('mouseup.mouseupSelect', function () {
            $this.select();
            return false;
        })
        .one('mousedown', function () {
            // compensate for untriggered 'mouseup' caused by focus via tab
            $this.off('mouseup.mouseupSelect');
        })
        .select();
});

$(document).on('click', '.clipborad', function () {
    var id = $(this).data('id');
    copyToClipboard(id)
    notifySuccess('Copied');
    return false;
});
function copyToClipboard(elementId) {
    var aux = document.createElement("input");
    aux.setAttribute("value", document.getElementById(elementId).value);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);
}

$(document).on('click', '#show-aff-tc', function () {
    var modal = $("#tc-modal");
    modal.modal("show");
    return false;
});

$(document).on('click', '#manage-commission', function () {
    var obj = $(this);
    var id = obj.data('id');
    var key = obj.data('key'); //like slug
    var arr = obj.data('arr');
    var modal = $("#commissionModal");
    var limit = $('#limit').val();

    modal.find("#pt").val(key);
    ajax_call_admincp($('#ug').val(), 'admincp');
    modal.modal('show');
    return false;
});

function user_group_commision(obj, t) {
    ajax_call_admincp($(obj).val(), t);
}

function ajax_call_admincp(k, t) {
    var k = k;
    var c = $('.wrapper-result-com .content');
    c.html(" ");
    var pt = $("#pt").val();
    //alert(pt);
    var wrapper = $(".wrapper-result-com");
    wrapper.find('.indicator').fadeIn();
    if (t == 'admincp') {
        //it means i am setting inside input
        //get the user group commssion rules
        $.ajax({
            url: baseUrl + "affliate/ajax",
            method: 'POST',
            data: {action: 'admincp-commission', k: k, pt: pt},
            success: function (data) {
                var json = jQuery.parseJSON(data);
                wrapper.find('.indicator').fadeOut();
                c.html(json.view);
            }
        });
    }
}