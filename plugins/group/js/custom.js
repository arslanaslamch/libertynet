function show_group_joins(typeId,typ) {
    var modal = $('#photoViewer');
    modal.modal('hide');
    var m = $('#joinsModal');
    var modal = $('#photoViewer');
    modal.modal('hide');
  
    m.modal('show');
    var indicator = m.find('.indicator');
    indicator.fadeIn();
    var lists = m.find('.user-lists');
    lists.html('');
    $.ajax({
        url: baseUrl + 'join/load/group?type_id=' + typeId + '&typ=' + typ + '&action=1&csrf_token=' + requestToken,
        success: function (data) {
            indicator.hide();
            lists.html(data);
        }
    });
    return false;
}