if(typeof notification === 'undefined') {
    notification = {
        init: function() {
            notification.attachEvents();
            if (typeof Notification !== 'undefined') {
                if (Notification.permission === 'default') {
                    Notification.requestPermission().then(function (permission) {
                        if (!('permission' in Notification)) {
                            Notification.permission = permission;
                        }
                    });
                }
            }
        },

        attachEvents: function() {
            $(document).on('mouseover', '.notification', function() {
                $(this).find('.actions').show();
            });

            $(document).on('mouseout', '.notification', function() {
                $(this).find('.actions').hide();
            });
        },

        markRead: function(id) {
            var c = $("#notification-" + id);
            var b = c.find('.mark-button');
            var status = b.attr('data-status');
            var lRead = b.data('read');
            var lMark = b.data('mark');
            var type = (status == '0') ? 1 : 0;
            $.ajax({
                url: baseUrl + 'notification/mark?id=' + id + '&type=' + type + '&csrf_token=' + requestToken,
            });
            if(status == 0) {
                c.removeClass("notification-unread");
                b.attr('title', lRead).attr('data-status', type);
            } else {
                c.addClass("notification-unread");
                b.attr('title', lMark).attr('data-status', type);
            }
            return false;
        },

        delete: function(id) {
            var c = $("#notification-" + id);
            c.fadeOut();
            $('div[id=notification-' + id + ']').fadeOut();
            //$('.notifications-dropdown').show();
            $.ajax({
                url: baseUrl + 'notification/delete?id=' + id + '&csrf_token=' + requestToken,
            });
            return false;
        },

        push: function(type, d) {
            if(type === 'notification') {
                var notyCounts = 0;
                var a = $("#notification-dropdown-container > a");
                if(!a.find('span').length) {
                    a.append('<span class="count" style="display:none"></span>')
                }
                var span = a.find('span');
                var nIds = '';
                $.each(d, function(pushId, nId) {
                    if(!Pusher.hasPushId(pushId)) {
                        Pusher.addPushId(pushId);
                        nIds += (nIds) ? ',' + nId : nId;
                    }
                    notyCounts += 1;
                });

                if(notyCounts > 0) {
                    span.html(notyCounts > 9 ? '9+' : notyCounts).fadeIn();
                    Pusher.addCount(notyCounts);
                } else {
                    span.remove();
                }

                a.click(function() {
                    Pusher.removeCount(notyCounts);
                    span.hide();
                });
                if(nIds) {
                    $.ajax({
                        url: baseUrl + 'notification/preload?csrf_token=' + requestToken,
                        data: {ids: nIds},
                        success: function(data) {
                            var c = $(".notification-dropdown-result-container");
                            c.prepend(data);
                            if(data) {
                                notification.pop.open(data);
                            }
                            reloadInits();
                        }
                    })
                }
            }
        },

        dropdown: {
            show: function () {
                var dropdown = $(".notifications-dropdown");
                var indicator = dropdown.find('#notification-dropdown-indicator');
                var content = dropdown.find('.notification-dropdown-result-container');
                if(dropdown.css('display') == 'none') {
                    dropdown.fadeIn();
                    indicator.show();
                    $.ajax({
                        url: baseUrl + 'notification/load/latest?csrf_token=' + requestToken,
                        success: function(data) {
                            content.html(data);
                            indicator.hide();
                            var counter = $('#notification-dropdown-container > a > span');
                            counter.remove();
                            reloadInits();
                        }
                    })
                } else {
                    dropdown.fadeOut();
                }
                $(document).click(function(e) {
                    if(!$(e.target).closest("#notification-dropdown-container").length) dropdown.hide();
                });
                return false;
            }
        },

        pop: {
            open: function (data) {
                $("#notification-popup").find('#content').html(data);
                $("#notification-popup").fadeIn();
                setTimeout(function() {
                    $("#notification-popup").fadeOut(300);
                }, 5000);
            },

            close: function () {
                $("#notification-popup").fadeOut(300);
                return false;
            }
        }
    };
}

/**
 * @deprecated Use notification.pop.open instead
 */
function initNotificationPopup(data) {
    return notification.pop.open(data);
}

/**
 * @deprecated Use notification.pop.close instead
 */
function closeNotificationpopup() {
    return notification.pop.close(data);
}

/**
 * @deprecated Use notification.dropdown.show instead
 */
function show_notification_dropdown() {
    return notification.dropdown.show();
}

/**
 * @deprecated Use notification.markRead instead
 */
function process_notification_mark(id) {
    return notification.markRead(id);
}

/**
 * @deprecated Use notification.delete instead
 */
function delete_notification(id) {
    return notification.delete(id);
}

Pusher.addHook('notification.push');

$(function() {
    notification.init();
});
