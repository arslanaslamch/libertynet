function show_page_likes(type, typeId,typ) {
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
        url: baseUrl + 'like/load/page?type=' + type + '&type_id=' + typeId + '&typ=' + typ + '&action=1&csrf_token=' + requestToken,
        success: function (data) {
            indicator.hide();
            lists.html(data);
        }
    });
    return false;
}