$(document).on('click','.ss-helpful-btn',function(){
    let o = $(this);
    let action = 'article-feedback';
    let aid = o.data('aid');
    let type = o.data('type');
    $.ajax({
        beforeSend: function () {
            $(o).closest('p').hide();
        },
        url: baseUrl + 'support/ticket/ajax?aid=' + aid+'&action='+ action+'&type='+type,
        success: function (data) {
            $('.help-center-answer-response').show();
        }
    });
   return false;
});

function choose_ss_file(){
    return $("#ss-type-file").click();
}

function ss_ticket_submit_reply(t) {
    var form = $(t).closest('form');
    //var indicator = $("#ads-indicator");
    //indicator.fadeIn();

    //var iC = $("#ads-form-input-container");
    //iC.css('opacity', '0.4');
    let container = $('.ss-reply-box-content');
    form.ajaxSubmit({
        url : form.attr('action'),
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if(json.status){
                container.append(json.view);
                form.trigger('reset');
                notifySuccess(json.msg);
            }else{
                notifyError(json.msg)
            }
        },
        error : function() {
            //indicator.hide();
            //iC.css('opacity', 1);
        }
    });
    return false;
}

function ss_close_ticket(o){
    let obj = $(o);
    let tid = obj.data('tid');
    let action = "close-ticket";
    let from = obj.data('from');
    $.ajax({
        beforeSend: function () {
            $(o).attr('disabled', 'disabled');
        },
        url: baseUrl + 'support/ticket/ajax?tid=' + tid+'&action='+ action+'&from='+from,
        success: function (data) {
            data = JSON.parse(data);
            if (data.status == '1') {
                window.location.href = data.url
            } else {
                notifyError(data.message);
            }
        }
    });
}