$(document).ready(function() {
	$(document).on('click', '#statistic-site-widget .height-toggle', function() {
        var minHeight = '243px';
        var maxHeight = '930px';
        if($('#statistic-site-widget .menu').css('height') == minHeight) {
            $('#statistic-site-widget .menu').css('height', maxHeight);
            $('#statistic-site-widget .height-toggle .indicator').css('transform', 'rotate(180deg)');
        } else {
            $('#statistic-site-widget .menu').css('height', minHeight);
            $('#statistic-site-widget .height-toggle .indicator').css('transform', 'rotate(0deg)');
        }
    });
});
function deleteSession(id) {
    var c = $("#session-wrapper-" + id);
    confirm.action(function () {
        c.css('opacity', '0.5');
        let url = baseUrl + 'account?id=' + id + '&csrf_token=' + requestToken + '&action=sessions&delete=1';
        //loadPage(url);
		window.location.href = url;
    });
    return false;
}