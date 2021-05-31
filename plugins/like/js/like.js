function like_item(type, typeId) {
    var likeButtons = $('.like-button-' + type + '-' + typeId);
    var dislikeButtons = $('.dislike-button-' + type + '-' + typeId);
    var reactButtons = $('.react-button-' + type + '-' + typeId);
    var likeCounts = $('.like-count-' + type + '-' + typeId);
    var dislikeCounts = $('.dislike-count-' + type + '-' + typeId);
    var reactionCounts = $('.reactions-' + type + '-' + typeId);
    var reactors = $('.reactors-' + type + '-' + typeId);
    var hideIcon = likeButtons.data('hide-icon') || 0;
    var hideCount = likeButtons.data('hide-count') || 0;
    var status = likeButtons.attr('data-status');
    if (status == 0) {
        likeButtons.addClass('liked').attr('data-status', 1);
        dislikeButtons.removeClass('disliked');
        dislikeButtons.attr('data-status', 0);
        reactButtons.removeClass('liked');
        w = 1;
    } else {
        likeButtons.removeClass('liked');
        likeButtons.attr('data-status', 0);
        w = 0;
    }

    $.ajax({
        url: baseUrl + 'like/item?type=' + type + '&type_id=' + typeId + '&w=' + w + '&action=like&hide_icon=' + hideIcon + '&hide_count=' + hideCount + '&csrf_token=' + requestToken,
        type: 'GET',
        beforeSend: function() {
            likeButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            dislikeButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            likeCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            dislikeCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactionCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactors.css({'opacity': '0.5', 'pointer-events' : 'none'});
        },
        success: function (data) {
            data = JSON.parse(data);
            likeButtons.css({'opacity': '1', 'pointer-events' : 'all'});
            dislikeButtons.css({'opacity': '1', 'pointer-events' : 'all'});
            reactButtons.html(data.react_button);
            reactButtons.css({'opacity': '1', 'pointer-events' : 'all'});
            likeCounts.html(data.likes);
            likeCounts.css({'opacity': '1', 'pointer-events' : 'all'});
            dislikeCounts.html(data.dislikes);
            dislikeCounts.css({'opacity': '1', 'pointer-events' : 'all'});
            reactionCounts.html(data.reactions);
            reactionCounts.css({'opacity': '1', 'pointer-events' : 'all'});
            reactors.html(data.reacts);
            reactors.css({'opacity': '1', 'pointer-events' : 'all'});
        }
    });
    return false;
}

function dislike_item(type, typeId) {
    var likeButtons = $('.like-button-' + type + '-' + typeId);
    var dislikeButtons = $('.dislike-button-' + type + '-' + typeId);
    var reactButtons = $('.react-button-' + type + '-' + typeId);
    var likeCounts = $('.like-count-' + type + '-' + typeId);
    var dislikeCounts = $('.dislike-count-' + type + '-' + typeId);
    var reactionCounts = $('.reactions-' + type + '-' + typeId);
    var reactors = $('.reactors-' + type + '-' + typeId);
    var hideIcon = dislikeButtons.data('hide-icon') || 0;
    var hideCount = dislikeButtons.data('hide-count') || 0;
    var status = dislikeButtons.attr('data-status');
    if (status == 0) {
        likeButtons.removeClass('liked');
        likeButtons.attr('data-status', 0);
        dislikeButtons.addClass('disliked').attr('data-status', 1);
        reactButtons.removeClass('liked');
        w = 1;
    } else {
        dislikeButtons.removeClass('disliked');
        dislikeButtons.attr('data-status', 0);
        w = 0;
    }

    $.ajax({
        url: baseUrl + 'like/item?type=' + type + '&type_id=' + typeId + '&w=' + w + '&action=dislike&hide_icon=' + hideIcon + '&hide_count=' + hideCount + '&csrf_token=' + requestToken,
        type: 'GET',
        beforeSend: function() {
            likeButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            dislikeButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            likeCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            dislikeCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactionCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactors.css({'opacity': '0.5', 'pointer-events' : 'none'});
        },
        success: function (data) {
            data = JSON.parse(data);
            likeButtons.css({'opacity': '1', 'pointer-events' : 'all'});
            dislikeButtons.css({'opacity': '1', 'pointer-events' : 'all'});
            reactButtons.html(data.react_button);
            reactButtons.css({'opacity': '1', 'pointer-events' : 'all'});
            likeCounts.html(data.likes);
            likeCounts.css({'opacity': '1', 'pointer-events' : 'all'});
            dislikeCounts.html(data.dislikes);
            dislikeCounts.css({'opacity': '1', 'pointer-events' : 'all'});
            reactionCounts.html(data.reactions);
            reactionCounts.css({'opacity': '1', 'pointer-events' : 'all'});
            reactors.html(data.reacts);
            reactors.css({'opacity': '1', 'pointer-events' : 'all'});
        }
    });

    return false
}

function show_likes(type, typeId) {
    var modal = $('#photoViewer');
    modal.modal('hide');
    var m = $('#likesModal');
    var modal = $('#photoViewer');
    modal.modal('hide');
    var title = m.find('.modal-title');
    title.html(title.data('like'));
    m.modal('show');
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url: baseUrl + 'like/load/people?type=' + type + '&type_id=' + typeId + '&action=1&csrf_token=' + requestToken,
        success: function (data) {
            indicator.hide();
            lists.html(data);
        }
    });
    return false;
}

function show_dislikes(type, typeId) {
    var m = $('#likesModal');
    var modal = $('#photoViewer');
    modal.modal('hide');
    var title = m.find('.modal-title');
    title.html(title.data('dislike'));
    m.modal('show');
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url: baseUrl + 'like/load/people?type=' + type + '&type_id=' + typeId + '&action=0&csrf_token=' + requestToken,
        success: function (data) {
            indicator.hide();
            lists.html(data);

        }
    })
    return false;
}

function show_reactors(t, type, typeId) {
    var modal = $('#photoViewer');
    modal.modal('hide');
    var m = $('#likesModal');
    var o = $(t);
    var title = m.find('.modal-title');
    title.html(o.data('otitle'));
    m.modal('show');
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url: baseUrl + 'like/load/people?type=' + type + '&type_id=' + typeId + '&action=3&csrf_token=' + requestToken,
        success: function (data) {
            indicator.hide();
            lists.html(data);
        }
    });
    return false;
}

function react(type, typeId, code, button) {
    var likeButtons = $('.like-button-' + type + '-' + typeId);
    var dislikeButtons = $('.dislike-button-' + type + '-' + typeId);
    var reactButtons = $('.react-button-' + type + '-' + typeId);
    var likeCounts = $('.like-count-' + type + '-' + typeId);
    var dislikeCounts = $('.dislike-count-' + type + '-' + typeId);
    var reactionCounts = $('.reactions-' + type + '-' + typeId);
    var reactors = $('.reactors-' + type + '-' + typeId);
    var hideIcon = button.data('hide-icon') || 0;
    var hideCount = button.data('hide-count') || 0;
    $.ajax({
        url: baseUrl + 'like/react?type=' + type + '&type_id=' + typeId + '&code=' + code + '&hide_icon=' + hideIcon + '&hide_count=' + hideCount + '&csrf_token=' + requestToken,
        beforeSend: function() {
            button.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactButtons.css({'opacity': '0.5', 'pointer-events' : 'none'});
            likeButtons.removeClass('liked');
            dislikeButtons.removeClass('disliked');
            likeCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            dislikeCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactionCounts.css({'opacity': '0.5', 'pointer-events' : 'none'});
            reactors.css({'opacity': '0.5', 'pointer-events' : 'none'});
        },
        success: function (response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status) {
                likeButtons.css({'opacity': '1', 'pointer-events' : 'all'});
                dislikeButtons.css({'opacity': '1', 'pointer-events' : 'all'});
                reactButtons.css({'opacity': '1', 'pointer-events' : 'all'});
                reactButtons.html(data.react_button);
                likeCounts.html(data.likes);
                likeCounts.css({'opacity': '1', 'pointer-events' : 'all'});
                dislikeCounts.html(data.dislikes);
                dislikeCounts.css({'opacity': '1', 'pointer-events' : 'all'});
                reactionCounts.html(data.reactions);
                reactionCounts.css({'opacity': '1', 'pointer-events' : 'all'});
                reactors.css({'opacity': '1', 'pointer-events' : 'all'}).html(data.reacts);
                code == 1 ? likeButtons.attr('data-status', 1) : likeButtons.attr('data-status', 0);
                dislikeButtons.attr('data-status', 0);
                button.css({'opacity': '1', 'pointer-events' : 'all'});
                reloadInits();
            }
        }
    });
}

$(function () {
    $(document).on('mouseover', '.react-button', function () {
        var t = $(this);
        var target = t.data('target');
        var pane = $('.react-items-' + target);
        pane.data('status', 1);
        pane.fadeIn(400, function () {
            pane.data('status', 2);
        });
        pane.addClass('active');
    });

    $(document).on('click', '.react-items a', function () {
        var $obj = $(this);
        var $type = $obj.data('type');
        var $typeId = $obj.data('target');
        var $code = $obj.data('code');
        var $b = $('.react-button-' + $type + '-' + $typeId);
        $b.addClass('liked');
        react($type, $typeId, $code, $obj);
        $('.react-items').fadeOut(400, function () {
            if ($(this).data('status') === 2) {
                $(this).data('status', 0);
                $(this).removeClass('active');
            }
        });
        return false;
    });

    $(document).on('click', '.react-button', function () {
        var $obj = $(this);
        var $type = $obj.data('type');
        var $typeId = $obj.data('target')
        if ($obj.hasClass('liked')) {
            react($type, $typeId, 0, $obj);
            $obj.removeClass('liked');
        } else {
            $obj.addClass('liked');
            react($type, $typeId, 1, $obj);
        }
        return false;
    });

    $(document).on('mousemove', 'body', function (e) {
        if (!$(e.target).closest($('.feed-react')).length) {
            $('.react-items').fadeOut(400, function () {
                if ($(this).data('status') === 2) {
                    $(this).data('status', 0);
                    $(this).removeClass('active');
                }
            });
        }
    });
});