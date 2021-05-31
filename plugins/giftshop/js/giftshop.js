$('#gift-modal').on('show.bs.modal', function (e) {

});

$('#gift-modal').on('hide.bs.modal', function (e) {

});

$('#giftshop-show-friends').on('show.bs.modal', function (e) {
    var backdrops = $('.modal-backdrop');
    var backdrop = backdrops[backdrops.length - 1];
    $(backdrop).attr('id', 'giftshop-show-friends-backdrop');
    $(backdrop).css('z-index', 1050);
    $(this).css('z-index', 1051);
});

$('#giftshop-show-friends').on('hide.bs.modal', function (e) {

});

$('#giftshop-show-friends [data-dismiss="modal"]').on('click', function (e) {
    closeGiftFriendsModal();
});

function show_modal(image, giftId, giftTitle, price, userCredit, buyButton) {
    imageTag = $("#image");
    imageTag.attr('src', image);

    document.getElementById('giftId').value = giftId;
    document.getElementById('giftTitle').innerHTML = giftTitle;
    if (price < 1) {
        var fee = "Free";
    }
    else {
        var fee = price;
    }
    document.getElementById('submit').href = baseUrl + 'giftshop/ajax_show_friends?id=' + giftId + '&cost=' + price;
    document.getElementById('giftPrice').innerHTML = fee;
    if (userCredit >= price) {
        document.getElementById('submit').style.display = 'block';
    }
    else {
        document.getElementById('submit').style.display = 'none';
    }
    $("#gift-modal").modal();

    return false;
}

function show_modal_mine(image, giftId, giftTitle) {
    var price = 0;
    imageTag = $("#image");
    imageTag.attr('src', image);
    document.getElementById('giftId').value = giftId;
    document.getElementById('giftTitle').innerHTML = giftTitle;
    document.getElementById('submit').href = baseUrl + 'giftshop/ajax_show_friends?id=' + giftId + '&cost=' + price;
    document.getElementById('submit').style.display = 'block';
    $("#gift-modal").modal();
    return false;
}

function gifthop_show_friends(button, event) {
    event.preventDefault();
    var url = $(button).attr('href') + '&csrf_token=' + requestToken;
    if (url.indexOf('#') == 0) {
        $(url).modal('show');
    } else {
        $("#giftshop-show-friends").html('').modal('show');
        $.get(url, function (data) {
            $("#giftshop-show-friends").html(data);
        });
    }
    return false;
}

function closeGiftFriendsModal() {
    $('#giftshop-show-friends').modal('hide');
    $('#giftshop-show-friends-backdrop').remove();
    $('#giftshop-show-friends').fadeOut();
    window.location.reload(false);
}

function giftshop_send(id, friend, price) {
    var sender = $("#giftshop-show-friends");
    sender.modal("show");
    var loading = sender.find('.foreground');
    loading.fadeIn();
    var indicator = sender.find('.indicator');
    indicator.fadeIn();
    $.getJSON(baseUrl + 'giftshop/ajax_show_friends?id=' + id + '&friend=' + friend + '&csrf_token=' + requestToken + '&cost=' + price, function (data) {
        indicator.hide();
        var messageParent = sender.find('.message');
        var message = data.status == true ? sender.find('.alert-success') : sender.find('.alert-danger');
        messageParent.fadeIn();
        message.fadeIn().css("display", "inline-block");
        setTimeout(function () {
            message.hide();
            messageParent.hide();
            loading.hide();
            closeGiftFriendsModal();
        }, 2000);
    });
    return false;
}


function giftshop_paginate_friends(id, page, e) {
    var sender = $("#giftshop-show-friends");
    sender.modal("show");
    var loading = sender.find('.foreground');
    loading.fadeIn();
    var indicator = sender.find('.indicator');
    indicator.fadeIn();
    var friends = sender.find('#giftshop-modal-friends-list');
    friends.html('');
    $.ajax({
        url: baseUrl + 'giftshop/ajax_show_friends?id=' + id + '&page=' + page + '&csrf_token=' + requestToken,
        success: function (data) {
            indicator.hide();
            loading.hide();
            $(sender).html(data);
        }
    });
    return false;
}