function ratings(e, type, typeId) {
    var b = $(e);
    var rate = b.val();
    if (rate && type && typeId) {
        $.ajax({
            beforeSend: function () {
                $(e).attr('disabled', 'disabled');
            },
            url: baseUrl + 'add/rating?rate=' + rate + '&type=' + type + '&type_id=' + typeId,
            success: function (data) {
                $(e).removeAttr('disabled');
                data = JSON.parse(data);
                if (data.status == '1') {
                    $('.ratings-' + type + '-' + typeId).html(data.content);
                    $('.user-' + type + '-' + typeId).html(data.rate);
                    notifySuccess(data.message);
                } else {
                    notifyError(data.message);
                }
            }
        });

        return false;
    }
}