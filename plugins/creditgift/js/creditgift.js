
function creditgift_show_friends(button, event) {
    event.preventDefault();
    var id = document.getElementById('credit_quantity').value;
    //var id = $('#credit_quantity').val();
    //alert(id);
    document.getElementById('submit').href = baseUrl + 'creditgift/ajax_show_friends?amount=' + id;
    var url = $(button).attr('href') + '&csrf_token=' + requestToken;
    if (url.indexOf('#') === 0) {
        $(url).modal('open');
    } else {
        $("#creditgift-show-friends").html('').modal();
        $.get(url, function(data) {
            $("#creditgift-show-friends").html(data);
            $('input:text:visible:first').focus();
        });
    }
    return false;
};

function creditgift_send(id, friend ) {
    var sender = $("#creditgift-show-friends");
    sender.modal("show");
    var loading = sender.find('.foreground');
    loading.fadeIn();
    var indicator = sender.find('.indicator');
    indicator.fadeIn();
    $.getJSON(baseUrl + 'creditgift/ajax_show_friends?amount=' + id + '&friend=' + friend + '&csrf_token=' + requestToken , function(data) {
        indicator.hide();
        var messageParent = sender.find('.message');
        var message = data.status == true ? sender.find('.alert-success') : sender.find('.alert-danger');
        messageParent.fadeIn();
        message.fadeIn().css("display","inline-block");
        setTimeout(function() {
            message.hide();
            messageParent.hide();
            loading.hide();
        }, 2000);
        window.location = baseUrl + "creditgift";
    });
    return false;
}


function creditgift_paginate_friends(id, page) {
    var sender = $("#creditgift-show-friends");
    sender.modal("show");
    var loading = sender.find('.foreground');
    loading.fadeIn();
    var indicator = sender.find('.indicator');
    indicator.fadeIn();
    var friends = sender.find('#creditgift-modal-friends-list');
    //friends.html('');
    $.ajax({
        url : baseUrl + 'creditgift/ajax_show_friends?amount=' + id + '&page=' + page + '&csrf_token=' + requestToken,
        success : function(data) {
            indicator.hide();
            loading.hide();
            sender.html(data);
        }
    });
    return false;
}
$(document).on("click", ".sendOne", function (e) {

    e.preventDefault();

    var _self = $(this);

    var recieverId = _self.data('id');
    $("#recieverId").val(recieverId);

    $(_self.attr('href')).modal('show');
});

function closeCreditFriendsModal() {
    $('#creditgift-show-friends').modal('hide');
    $('.modal-backdrop').remove();
    $('#creditgift-show-friends').fadeOut();
    $('body').removeClass('modal-open');
    //window.location.reload(false);
}

$(document).on('click', '#creditgift-show-friends [data-dismiss="modal"]', function (e) {
    closeCreditFriendsModal();
});
