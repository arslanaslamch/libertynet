function show_message_dropdown() {
    var dropdown = $(".message-dropdown");
    var indicator = dropdown.find('#message-dropdown-indicator');
    var content = dropdown.find('.message-dropdown-result-container');
    if (dropdown.css('display') === 'none') {
        dropdown.fadeIn();
        indicator.show();
        $.ajax({
            url: baseUrl + 'chat/load/dropdown?csrf_token=' + requestToken,
            success: function (data) {
                content.html(data);
                indicator.hide();
                var counter = $('#message-dropdown-container > a > span');
                counter.remove();
                reloadInits();
            }
        })
    } else {
        dropdown.fadeOut();
    }
    $(document).click(function (e) {
        if (!$(e.target).closest("#message-dropdown-container").length) dropdown.hide();
    });
    return false;
}

function open_chat_onlines(obj) {
    var obj = $("#chat-boxes-container .opener-head");
    var o = $("#chat-boxes-container");
    if (obj.hasClass('opened')) {
        //close it
        o.find('.main').hide();
        obj.removeClass('opened');
    } else {
        obj.addClass('opened');
        o.find('.main').show();
    }
    return false;
}

function change_online_status(type, c) {
    var b = $(".onlines-container .opener-head .dropdown-button span");
    b.removeClass('online-icon').removeClass('busy-icon').removeClass('invisible-icon');
    b.addClass(c);

    var b = $(".onlines-container .chat-opener-head .dropdown-button span");
    b.removeClass('online-icon').removeClass('busy-icon').removeClass('invisible-icon');
    b.addClass(c);

    $.ajax({
        url: baseUrl + 'chat/set/status?type=' + type + '&csrf_token=' + requestToken,
    });
    return false;
}

function switch_chat_onlines_type(type) {
    var o = $("#chat-boxes-container");
    var u = o.find('.main > ul');
    u.find('a').removeClass('active');
    $(".chat-opener-head .btn-group > a").removeClass('active')
    $(".chat-opener-head .btn-group ").find('.' + type).addClass('active')
    o.find('.online-lists').find('.lists').hide();
    if (type == 'friend') {
        //we are showing friend list
        var c = o.find('.online-friend-list');
        u.find('.friend').addClass('active');
        c.show();
    } else {
        var c = o.find('.online-group-list');
        u.find('.group').addClass('active');
        c.show();
        if (c.html().replace(/\s+/g, '') == '') {
            $.ajax({
                url: baseUrl + 'chat/load/groups?csrf_token=' + requestToken,
                success: function (data) {
                    c.html(data);
                }
            })
        }
    }
    return false;
}

function switch_chat_send_button(e) {
    var b = $("#chat-send-button");
    var i = $("#chat-send-input");
    if ($(e).prop('checked')) {
        b.hide();
        v = 0;
    } else {
        b.show();
        v = 1;
    }
    $.ajax({
        url: baseUrl + 'chat/update/send/privacy?v=' + v + '&csrf_token=' + requestToken,
    })

}

/**
 * Chat Object
 * @type {{}}
 */
var Chat = {
    boxes: [],
    toLoad: [],
    tabs: 7, //holds number of tabs that can be opened
    messages: [],
    cidClosed: [],
    cidClosedRecent: [],
    chatBoxDelete: false,
    groupChatLeave: 0,
    tabsCount: 0,
    tabsBox:[],

    init: function () {
        var container = $('.messages-pane');
        if (container.length > 0) {
            var paginateChat = function (container) {
                if (!container.find('.chat-message-indicator').length) {
                    $.ajax({
                        url: baseUrl + 'chat/paginate?csrf_token=' + requestToken,
                        beforeSend: function () {
                            var i = $("<div class='chat-message-indicator'>" + indicator + "</div>");
                            container.prepend(i);
                        },
                        data: {offset: container.data('offset'), cid: $("#message-cid-input").val()},
                        success: function (data) {
                            data = $.parseJSON(data);
                            if (data.messages !== '') {
                                container.html(data.messages + container.html()).find('.chat-message-indicator').remove();
                                container.attr('data-offset', data.offset);
                                container.data('offset', data.offset);
                                reloadInits();
                            } else {
                                container.find('.chat-message-indicator').remove();
                            }
                        }
                    });
                }
            };
            if (container.hasClass('slimscroll')) {
                container.slimScroll({start: 'bottom', height: container.data('height')});
                container.slimScroll().bind('slimscroll', function (e, pos) {
                    if (pos === 'top') {
                        paginateChat(container)
                    }
                });
            } else {
                container.each(function (index, div) {
                    div.scrollTop = div.scrollHeight;
                    document.addEventListener('scroll', function (event) {
                        if (event.target.matches('.messages')) {
                            if (!div.scrollTop) {
                                paginateChat($(div));
                            }
                        }
                    }, true);
                });
            }
        }
        Chat.editor.init();
        Chat.attachEvents();
        Hook.register('slimscroll', Chat.slimscroll);

    },

    onGIFClick: function (gif) {
        $(gif).closest('form').submit();
    },

    attachEvents: function () {
        $(document).on('click', '#message-user-suggestion a', function () {
            var name = $(this).data('name');
            var id = $(this).data('id');
            if ($("#message-user-" + id).length > 0) {
                $("#message-user-suggestion").hide();
                return false;
            }
            var span = $('<span class="user" id="message-user-' + id + '">' + name + '<input type="hidden" name="val[user][]" value="' + id + '"/><a href="#!"><i class="ion-close"></i></a> </span>');
            var container = $("#message-to-lists");
            var theInput = container.find('input[type="text"]');
            span.insertBefore(theInput);
            span.find('a').click(function () {
                span.remove();
                return false;
            });
            $("#message-user-suggestion").hide();
            theInput.val('');
            return false;
        });

        $(document).on('click', '.online-status-change-button', function (e) {
            e.preventDefault();
            let button = $(e.target).closest('.online-status-change-button');
            $.ajax({
                url: baseUrl + 'chat/set/status?type=' + button.data('id') + '&csrf_token=' + requestToken,
            });
            $('.online-status-color-' + button.data('user-id') + ', .icon-status-' + button.data('user-id')).removeClass('online-icon').removeClass('busy-icon').removeClass('invisible-icon').addClass(button.data('class'));
        });

        $(document).on('click', '.chat-colors-container a', function () {
           var e = $(this);
           var colorB = e.data('color');
           var cid = e.closest('.chat-colors-container').data('cid');
           var container = $('#chat-box-'+cid);
           if (container.length == '0'){
               var uid = e.closest('.chat-colors-container').data('uid');
               container = $('#chat-box-uid-'+uid);
           }
            let color = '#'+colorB;
            container.find('.head').css('background-color', color +' !important');
            var button = '<i class="fa fa-circle select-color" aria-hidden="true" style="color: '+ color+ '"></i>';
            var messageList = container.find('.message-list');
            messageList.find('.owned .content').css('background-color', color +' !important');
            var editor = container.find('.editor');
            editor.find('.actions .icon-c i').css('color', color);
            var buttonHTML = container.find('.chat-color-button');
            buttonHTML.html(' ');buttonHTML.html(button);
            $.ajax({
                url: baseUrl + 'chat/set/color?cid=' + cid + '&color='+ colorB +'&csrf_token=' + requestToken,
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.status == '1') {
                    }
                }
            });
        });

        addPageHook(function() {
            let messagesPane = $('.messenger .messages-pane').get(0);
            if(messagesPane) {
                messagesPane.scrollTop = messagesPane.scrollHeight;
            }
        });

        Hook.register('page.full-container.list', function (result) {
            result.push('messages');
            return result;
        });

        Hook.register('chat.settings', function (result, container, cid, userId) {
            $.ajax({
                url: baseUrl + 'chat/settings?cid=' + cid + '&uid=' + userId,
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.status === 1) {
                        $(container).find('.chat-settings-content').append(data.content);
                        $(container).find('.chat-settings').show();
                    } else {
                        $(container).find('.chat-settings').remove();
                    }
                }
            });
        });

        Hook.register('chat.group.member.left.before', function (result, cid) {
            $('.chat-conversation-' + cid).remove();
            Chat.closeBox(cid);
        });

        Hook.register('pusher.finish', function () {
            let conversationLastUpdateTimes = document.getElementsByClassName('chat-conversation-last-update-time');
            let cIds = [];
            let cId;
            for (let i = 0; i < conversationLastUpdateTimes.length; i++) {
                cId = conversationLastUpdateTimes[i].getAttribute('data-cid');
                if(cId && cIds.indexOf(cId) === -1) {
                    cIds.push(cId);
                }
            }
            Pusher.sendMessage({type: 'chat.conversation.last.update.time', cids: cIds});
        });

        $(document).on('submit', '#message-chat-form', function () {
            var form = $(this);
            var upload = false;

            if (form.find('#chat-message-image-input').val() != '' || form.find('#chat-message-file-input').val() != '' || form.find('.chat-gif-input').val() != '') {
                upload = true;
            }

            form.ajaxSubmit({
                url: baseUrl + 'chat/send',
                uploadProgress: function (event, position, total, percent) {
                    if (!upload) return false;
                    var messagesPane = form.find('.messages-pane');
                    if (messagesPane.find('.message-upload-indicator').length > 0) {
                        var uI = messagesPane.find('.message-upload-indicator');
                    } else {
                        var uI = $("<div class='message-upload-indicator'>" + messagesPane.data('sending') + " <span></span></div>");
                        messagesPane.append(uI);

                    }
                    if (messagesPane.hasClass('slimscroll')) {
                        messagesPane.slimScroll({
                            scrollBy: messagesPane.prop('scrollHeight') + 'px'
                        });
                    } else {
                        messagesPane.get(0).scrollTop = messagesPane.get(0).scrollHeight;
                    }
                    uI.find('span').html(percent + '%');
                    if (percent == 100) {
                        uI.remove();
                    }
                },
                success: function (data) {
                    var json = $.parseJSON(data);
                    if (json.status == 0) {
                        notifyError(json.error);
                    } else {
                        Chat.messages.push(json.messageid);
                        var form = $("#message-chat-form");
                        var toInputs = form.find('.message-to-container');
                        var title = form.find('.messages-pane-title');
                        if (toInputs.length > 0 && toInputs.css('display') !== 'none') {
                            toInputs.hide();
                            title.find('.message-title').html(json.title);
                            window.history.pushState({'type': 'messenger', 'id': json.cid}, json.sitetitle, json.url);
                            document.title = json.sitetitle ? json.sitetitle : document.title;
                            Pusher.setPageTitle(document.title);
                            form.find('#message-cid-input').val(json.cid);
                            title.show();
                            $('.conversation > form > .head').html(json.conversation_head);
                            $('.conversation > form > .messaging .info').removeClass('single').removeClass('multiple').addClass(json.type).html(json.conversation_messaging_info);
                            $('.conversation > form > .info').removeClass('single').removeClass('multiple').addClass(json.type).html(json.conversation_info);
                            $("#message-right-pane").addClass('message-box-' + json.cid);
                        }
                        var messagesPane = form.find('.messages-pane');
                        messagesPane.append(json.message);
                        reloadInits();
                        if (messagesPane.hasClass('slimscroll')) {
                            messagesPane.slimScroll({
                                scrollBy: messagesPane.prop('scrollHeight') + 'px'
                            });
                        } else {
                            messagesPane.get(0).scrollTop = messagesPane.get(0).scrollHeight;
                        }
                        form.find('textarea').val('');
                    }
                    form.find('.chat-gif-input').val('');
                },
                error: function () {
                    form.find('.chat-gif-input').val('');
                }
            });
            form.find('textarea').val('');
            form.find('input[type=file]').val('');
            var voiceRecorder = form.find('.chat-editor-voice-recorder');
            var voiceRecorderId = $(voiceRecorder).data('id');
            if (typeof Chat.editor.editors[voiceRecorderId] !== 'undefined') {
                Chat.editor.editors[voiceRecorderId].voice.live.end();
            }
            return false;
        });

        $(document).on('submit', '#chat-group-modal-form', function () {
            var form = $(this);
            var upload = false;

            if (form.find('#chat-group-modal-message-image-input').val() != '' || form.find('#chat-group-modal-message-file-input').val() != '') {
                upload = true;
            }

            form.find('div.ajax-form-loading').fadeIn('fast');
            form.ajaxSubmit({
                url: baseUrl + 'chat/send',
                uploadProgress: function (event, position, total, percent) {
                    if (!upload) return false;
                    var messagesPane = form.find('.messages-pane');
                    if (messagesPane.find('.message-upload-indicator').length > 0) {
                        var uI = messagesPane.find('.message-upload-indicator');
                    } else {
                        var uI = $("<div class='message-upload-indicator'>" + messagesPane.data('sending') + " <span></span></div>");
                        messagesPane.append(uI);

                    }
                    if (messagesPane.hasClass('slimscroll')) {
                        messagesPane.slimScroll({
                            scrollBy: messagesPane.prop('scrollHeight') + 'px'
                        });
                    } else {
                        messagesPane.get(0).scrollTop = messagesPane.get(0).scrollHeight;
                    }
                    uI.find('span').html(percent + '%');
                    if (percent == 100) {
                        uI.remove();
                    }
                },
                success: function (data) {
                    var json = $.parseJSON(data);
                    if (json.status == 0) {
                        notifyError(json.error);
                    } else {
                        $('#chat-group-modal').modal('hide');
                        Chat.groupModalSelection = {};
                        $('#chat-group-modal .modal-body .chat-group-members .list').html('');
                        $('#chat-group-modal .modal-body .chat-group-members .selected .count').html(0);
                        Chat.open(json.cid, null, json.title, json.avatar1, json.avatar2);
                        form.find('textarea').val('');
                        form.find('.chat-gif-input').val('');
                    }
                    form.find('div.ajax-form-loading').fadeOut('fast');
                }, error: function () {
                    form.find('div.ajax-form-loading').fadeOut('fast');
                    form.find('.chat-gif-input').val('');
                }
            });
            form.find('textarea').val('');
            form.find('input[type=file]').val('');
            var voiceRecorder = form.find('.chat-editor-voice-recorder');
            var voiceRecorderId = $(voiceRecorder).data('id');
            if (typeof Chat.editor.editors[voiceRecorderId] !== 'undefined') {
                Chat.editor.editors[voiceRecorderId].voice.live.end();
            }
            return false;
        });

        $(document).on('click', '.add-emoticon', function (e) {

            if ($('.messages-pane').length > 0) {
                var t = $(this).data('target');
                var s = $(this).data('symbol');
                var v = $(t).val() + " " + s + " ";
                // alert($("#message-chat-form").html());
                if ($("#message-chat-form").find('textarea').val() == '') {
                    $(t).val(v);
                    //alert('am here');
                    $("#message-chat-form").submit();
                } else {
                    $(t).val(v).focus();
                }
                $('.emoticon-box').fadeOut();//we need to hide
            }
            e.preventDefault();
            return false;
        });

        $(document).on('click', '.chat-box .add-emoticon', function (e) {
            var t = $(this).data('target');
            var s = $(this).data('symbol');
            var v = $(t).val() + " " + s + " ";
            var target = $(t);
            if (target.val() == '') {
                target.val(v);
                //alert('#chat-box-form-' + target.data('id'));
                $('#chat-box-form-' + target.data('id')).submit();
            } else {
                target.val(v).focus();
            }

            $('.emoticon-box').fadeOut();//we need to hide
            return false;
        });

        $(document).on('keydown', '#message-editor-textarea', function (e) {
            if ((e.keyCode || e.which) === 13) {
                if ($("#chat-send-input").prop('checked')) {
                    e.preventDefault();
                    $("#message-chat-form").submit();
                }
            }
        });

        $(document).on('keydown', '#chat-group-modal-editor-textarea', function (e) {
            if ((e.keyCode || e.which) === 13) {
                e.preventDefault();
                $("#chat-group-modal-form").submit();
            }
        });

        var chatOnlineSearching = false;
        $(document).on('keyup', '.conversations-search-input', function (event) {
            let term = $(this).val();
            let container = $(this).closest('.conversations-list').find('.conversations-list-items').get(0);
            $('.conversations-list-items').data('term', term);
            if(term) {
                Chat.paginateConversations(container);
            } else {
                $(container).removeClass('search-result');
            }
        });

        $(document).on('keyup', '.online-search input[type=text]', function (event) {
            var term = $(this).val();
            if (!chatOnlineSearching) {
                var container = $(this).data('result');
                $.ajax({
                    url: baseUrl + 'chat/load/search?term=' + term + '&csrf_token=' + requestToken,
                    beforeSend: function () {
                        chatOnlineSearching = true;
                    },
                    success: function (response) {
                        $(container).html(response);
                        chatOnlineSearching = false;
                    },
                    error: function (error) {
                        chatOnlineSearching = false;
                    }
                });
            }
        });
    },

    push: function (type, d) {
        if (type === 'chat') {
            if (window.chatPushRun) {
                $.each(d, function (cid, cPushes) {
                    if (Chat.boxCreated(cid)) {
                        var ids = [];
                        var mIds = [];
                        var m_html = [];
                        $.each(cPushes, function (push, pD) {
                            if (!Pusher.hasPushId(push)) {
                                Pusher.addPushId(push);
                                if ($.inArray(pD.id, Chat.messages) == -1) {
                                    ids.push(pD.id);
                                    if (pD.user != Pusher.getUser()) {
                                        mIds.push(pD.id);
                                        if (typeof pD.html !== "undefined") {
                                            m_html.push(pD.html);
                                        }
                                    }
                                }
                            }
                        });
                        if (mIds.length > 0) {
                            if (Chat.minimized(cid)) {
                                //get the tab toggle and attach the event to mark messages read
                                var tab = Chat.getTab(cid);
                                if (tab.length > 0) {
                                    var count = tab.find('.count');
                                    if (count.length < 1) {
                                        count = $('<span class="count"></span>');
                                        tab.append(count);
                                    }
                                    count.html(mIds.length > 9 ? '9+' : mIds.length);
                                }
                                var box = Chat.getBox(cid);
                                var m = (box.data('mids') != undefined) ? box.data('mids') : '';

                                for (var i = 0; i < mIds.length; i++) {
                                    m += ',' + mIds[i];
                                }
                                box.attr('data-mids', m);
                                box.data('mids', m);

                            } else {
                                //mark necessary messages
                                Chat.markRead(mIds);
                            }
                            //if ($('.message-box-' + cid).length > 0) Chat.markRead(mIds);
                        }
                        if (ids.length > 0) {
                            if (m_html.length > 0) {
                                m_html.forEach(function (html) {
                                    Chat.appendMessage(cid, html);
                                });
                            } else {
                                // alert('L');
                                Chat.putToLoad(cid, ids);
                            }
                        }
                    } else {
                        // Chat.open(cid);
                        window.chatPushRun = true;
                        $.each(cPushes, function (push, pD) {
                            if (!Pusher.hasPushId(push)) Pusher.addPushId(push);
                        })
                    }
                });
            } else {
                $.each(d, function (cid, cPushes) {
                    window.chatPushRun = true;
                    $.each(cPushes, function (push, pD) {
                        if (!Pusher.hasPushId(push)) Pusher.addPushId(push);
                    })
                });
            }
            Chat.pushArrived();
        } else if (type === 'unread') {
            Chat.updateUnread(d);
        } else if (type === 'count-onlines') {
            $("#chat-boxes-container .opener-head > a span").html(d);
        } else if (type === 'onlines') {
            if (!$('#chat-boxes-container .onlines-container .online-search input[type=text]').val()) {
                //$("#chat-boxes-container .online-friend-list").html(d);
            }
            if (!$('#chat-group-modal .online-search input[type=text]').val()) {
                $("#chat-group-modal .online-friend-list").html(d);
            }
        } else if (type === 'chat-groups') {
            if (Chat.groupChatLeave > 0) {
                Chat.groupChatLeave--;
            } else {
                $('#chat-boxes-container .online-group-list').html(d);
            }
        } else if (type === 'chat.group.member.left') {
            for (let id in d) {
                if (d.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
                    $('.chat-conversation-' + d.cid).remove();
                    Chat.closeBox(d.cid);
                    Pusher.addPushId(id);
                }
            }
        } else if (type == 'chat-typing') {
            var currentTime = d.now - 20;
            var cids = d.cid;
            $.each(cids, function (cid, time) {
                var dBox = $("#chat-box-" + cid);
                if (dBox.length > 0 && time > currentTime) {
                    var ty = d.typing;
                    var img = d.img;
                    if (dBox.find('.typing-indicator').length < 1) {
                        var ind = $("<div class='typing-indicator'><span class='arrow-left'></span><span class='content'><img src='" + img + "'/> " + ty + "...</span></div>")
                        dBox.find('.message-list').append(ind);
                        var container = dBox.find('.message-list');
                        container.animate({scrollTop: container.prop("scrollHeight")}, 200);
                    }

                } else {
                    if (dBox.find('.typing-indicator').length > 0) {
                        dBox.find('.typing-indicator').remove();
                    }
                }
            })
        } else if (type === 'chat-seen') {
            $.each(d, function (cid, messageId) {
                $(".chat-message-" + messageId).find('.seen').fadeIn();
            });
        } else if (type === 'chat-opened') {

            if (((d instanceof Array && d.length) || (d instanceof Object && Object.keys(d).length)) && !Chat.chatBoxDelete) {
                var ids = [];
                var allIds = [];

                $.each(d, function (i, c) {

                    if (!Chat.boxCreated(i, c) && i != 0 && $.inArray(i, Chat.cidClosed) == -1) {
                        ids.push(i);
                    }
                    allIds.push(i);
                });

                if (ids.length > 0) {
                    $.ajax({
                        url: baseUrl + 'chat/get/conversations?csrf_token=' + requestToken,
                        data: {cids: ids},
                        success: function (data) {
                            var json = $.parseJSON(data);
                            $.each(json, function (cid, v) {
                                if ($.inArray(cid, Chat.cidClosed) == -1 && $.inArray(cid, Chat.cidClosedRecent) == -1) {
                                    if (v.title == null) return false;
                                    Chat.open(cid, v.uid, v.title, v.avatar, v.avatar2, null);
                                    Chat.cidClosedRecent = [];
                                }

                            })
                        }
                    })
                }
            } else {
                Chat.chatBoxDelete = false;
            }
        } else if(type === 'chat.conversation.last.update.time') {
            let conversationLastUpdateTimes = document.getElementsByClassName('chat-conversation-last-update-time');
            for (let id in d) {
                if (d.hasOwnProperty(id)) {
                    for (let cId in d[id]) {
                        if (d[id].hasOwnProperty(cId)) {
                            for (let i = 0; i < conversationLastUpdateTimes.length; i++) {
                                if(conversationLastUpdateTimes[i].getAttribute('data-cid') === cId) {
                                    conversationLastUpdateTimes[i].innerHTML = d[id][cId];
                                }
                            }
                        }
                    }
                    Pusher.addPushId(id);
                }
            }
        }
    },

    opened: function (cid, uid) {
        var div;
        if (cid != null && $("div[data-cid=" + cid + "]").length > 0) {
            div = $("div[data-cid=" + cid + "]");
        } else {
            div = $("div[data-uid=" + uid + "]");
        }
        if (div != undefined) return true;
        return false;
    },

    getBox: function (cid, uid) {
        var div;
        if (cid != null && $("div[data-cid=" + cid + "]").length > 0) {
            div = $("div[data-cid=" + cid + "]");
        } else {
            div = $("div[data-uid=" + uid + "]");
        }
        return div;
    },

    minimized: function (cid = null, box = null) {
        if (!box){
            box = this.getBox(cid);
        }
        if (box.length > 0 && box.css('height') == 'unset' || box.css('height') == '0px') return true;
        return false;
    },

    boxCreated: function (cid, uid) {
        if ((cid != null && $("div[data-cid=" + cid + "]").length > 0) || (uid && $("div[data-uid=" + uid + "]").length > 0) || $('.message-box-' + cid).length > 0) return true;
        return false;
    },

    registerBox: function (cid, uid) {
        $.ajax({
            url: baseUrl + 'chat/register/open?csrf_token=' + requestToken,
            data: {cid: cid, uid: uid}
        });
        Pusher.sendMessage({'chat.registration': {action: 'add', cid: cid, uid: uid}});
        var n = [];
        for (i = 0; i <= this.cidClosed.length; i++) {
            if (this.cidClosed[i] != cid) n.push(this.cidClosed[i]);
        }

        this.cidClosed = n;

        //alert(this.cidClosed);
    },

    unRegisterBox: function (cid) {
        $.ajax({
            url: baseUrl + 'chat/register/open?action=delete&csrf_token=' + requestToken,
            data: {cid: cid}
        });
        Chat.chatBoxDelete = true;
        Pusher.sendMessage({type: 'chat.registration', action: 'delete', cid: cid, uid: null});
        this.cidClosedRecent.push(cid);
        this.cidClosed.push(cid);
    },

    validateAllowTabs: function () {
        var tabs = 1;
        $('.chat-tab').each(function () {
            tabs += 1;
        });

        if (tabs > this.tabs) {
            $('.chat-tab').each(function (i, e) {
                if (i == 0) {
                    var tab = $(this);
                    Chat.closeBox(tab.data('tab'));
                }
            });
        }
    },

    groupModalSelection: {},

    addGroupModalSelection: function (uid, img, title) {
        if (typeof this.groupModalSelection['u' + uid] != 'undefined') {
            delete this.groupModalSelection['u' + uid];
        }
        this.groupModalSelection['u' + uid] = {
            uid: uid,
            title: title,
            img: img
        };
        var html = '';
        var count = 0;
        for (i in this.groupModalSelection) {
            html += '<div id="chat-group-modal-selection-' + this.groupModalSelection[i].uid + '" class="media media-sm chat-group-modal-selection"><div class="media-left"><div class="media-object"><img src="' + this.groupModalSelection[i].img + '"> </div></div><div class="media-body"><h6 class="media-heading">' + this.groupModalSelection[i].title + '</h6></div><input type="hidden" name="val[user][]" value="' + this.groupModalSelection[i].uid + '" /><span class="close ion-close" onclick="Chat.removeGroupModalSelection(' + this.groupModalSelection[i].uid + ')"></span></div>';
            count++;
        }
        $('#chat-group-modal .modal-body .chat-group-members .list').html(html);
        $('#chat-group-modal .modal-body .chat-group-members .selected .count').html(count);
    },

    removeGroupModalSelection: function (uid) {
        if (typeof this.groupModalSelection['u' + uid] != 'undefined') {
            delete this.groupModalSelection['u' + uid];
        }
        var html = '';
        var count = 0;
        for (i in this.groupModalSelection) {
            html += '<div id="chat-group-modal-selection-' + this.groupModalSelection[i].uid + '" class="media media-sm chat-group-modal-selection"><div class="media-left"><div class="media-object"><img src="' + this.groupModalSelection[i].img + '"> </div></div><div class="media-body"><h6 class="media-heading">' + this.groupModalSelection[i].title + '</h6></div><input type="hidden" name="val[user][]" value="' + this.groupModalSelection[i].uid + '" /><span class="close ion-close" onclick="Chat.removeGroupModalSelection(' + this.groupModalSelection[i].uid + ')"></span></div>';
            count++;
        }
        $('#chat-group-modal .modal-body .chat-group-members .list').html(html);
        $('#chat-group-modal .modal-body .chat-group-members .selected .count').html(count);
    },
    maximize: function(box){
        var box = $(box);
        if (!this.minimized('', box)){
            this.hideBox('', box);
        } else {
            box.show();
            var boxBody = box.find('.chat-box');
            boxBody.css('height','370px');
            box.css('height','370px');
            boxBody.find('.slimScrollDiv, .editor').show();
            boxBody.find('.head .actions .minimize-button').show();
            boxBody.find('.head .actions .maximize-button').hide();
        }
    },
    open: function (cid, uid, title, img, img2, reg, elem, entityType = 'user', entityId = null) {
        let color = $(elem).attr('data-color');
        if (elem && $(elem).closest('#chat-group-modal').length) {
            this.addGroupModalSelection(uid, img, title);
            return false;
        } else if (elem && !$(elem).closest('#chat-group-modal').length) {
            let a = Hook.fire("group.modal.selection", uid, [img, title, elem]);
            if (!a) return false;
        }
        var id, div;
        if ($('.message-box-' + cid).length > 0) {
            //we must not open that since we are on the message page
        } else {
            //
            $(".message-dropdown").hide();
            if (this.boxCreated(cid, uid)) {
                let checkBox = $("div").find("[data-uid='" + uid + "']");
                if (cid == null && $('#chat-box-uid-' + uid).length > 0){
                    var box = $('#chat-box-uid-' + uid);
                    this.maximize(box);

                } else if (cid == null && $('#chat-box-uid-' + uid).length == 0){
                    let checkBox = $("div").find("[data-uid='" + uid + "']");
                    this.maximize(checkBox);
                }
                return false;
                //alert(cid);
                if (cid != null && $("div[data-cid=" + cid + "]").length > 0) {
                    div = $("div[data-cid=" + cid + "]");
                    id = cid;
                } else {
                    div = $("div[data-uid=" + uid + "]");
                    id = 'uid-' + uid;
                }
                this.showBox(div, id);
            } else {
                this.validateAllowTabs();
                if (cid != null) {
                    id = cid

                } else {
                    id = 'uid-' + uid;
                }

                this.boxes.push(id);
                div = $("<div data-typing='0' data-id='" + id + "' id='chat-box-" + id + "' class='chat-box chat-box-intact' data-cid='" + cid + "' data-uid='" + uid + "'></div>");
                div.html($(".chat-box-template").html());

                if (cid != null) div.attr('id', 'chat-box-' + cid);
                //chat header
                var head = div.find('.head');
                if (title != undefined) {
                    head.prepend(uid ? '<a href="' + baseUrl + uid + '" ajax="true" style="color: #FFFFFF">' + title + '</a>' : '<span style="color: #FFFFFF">' + title + '</span>');
                }
                if (uid != '') {
                    div.find('.editor form').prepend("<input type='hidden' name='val[user][]' value='" + uid + "' />");
                }

                if (cid != '') {
                    div.find('.editor form .message-cid-input').val(cid)
                }

                $("#chat-boxes-container .chat-boxes-wrapper").append(div);
                div.draggable({
                    containment: 'window',
                    cursor: 'move',
                    iframeFix: true,
                    appendTo: 'body',
                    handle: '.head',
                    scroll: false,
                    zIndex: 9999,
                    start: function (e, ui) {
                        var o = $(ui.helper);
                        o.removeClass('chat-box-intact');
                    }
                });

                //do some event attachment
                if (color != '') head.css('background-color', color + ' !important');
                head.find('.close-button').attr('data-id', id);
                head.find('.minimize-button').attr('data-id', id);
                Hook.fire('mediachat.friends.available', null, [head.get(0), uid]);
                Hook.fire('chat.settings', null, [head.get(0), id, uid]);
                head.find('.close-button').click(function () {
                    Chat.closeBox($(this).data('id'));
                    return false;
                });
                head.find('.minimize-button').click(function () {
                    Chat.hideBox($(this).data('id'));
                    return false;
                });
                head.find('.maximize-button').click(function () {
                    Chat.maximize(Chat.getBox($(this).data('id')));
                    return false;
                });

                var form = div.find('.editor form');
                form.attr('id', 'chat-box-form-' + id);
                div.find('.editor form').data('id', id);
                var textarea = div.find('.editor form textarea');
                textarea.attr('data-id', id);
                textarea.data('id', id);
                textarea.attr('id', id + '-textarea');
                textarea.keydown(function (e) {
                    var arBox = $("#chat-box-" + $(this).data('id'));
                    if (arBox.length && arBox.data('type') != undefined && arBox.data('type') == 'single') {
                        if ((e.keyCode || e.which) != 13) {
                            if (arBox.data('typing') == 0) {
                                arBox.attr('data-typing', 1);
                                arBox.data('typing', 1);
                                $.ajax({
                                    url: baseUrl + 'chat/typing?cid=' + arBox.data('id') + '&csrf_token=' + requestToken,
                                })
                            }

                        } else {
                            //alert('here')
                            arBox.attr('data-typing', 0);
                            arBox.data('typing', 0);
                        }
                    }
                });

                textarea.blur(function (e) {

                    var arBox = $("#chat-box-" + $(this).data('id'));
                    arBox.attr('data-typing', 0);
                    arBox.data('typing', 0);
                    $.ajax({
                        url: baseUrl + 'chat/remove/typing?cid=' + arBox.data('id') + '&csrf_token=' + requestToken,
                    });
                });

                var emoticonButton = div.find('.editor form .emoticon-button');
                emoticonButton.attr('data-target', id + '-textarea').data('target', id + '-textarea');
                var form = div.find('.editor form');
                form.find('.chat-editor-voice-recorder').attr('id', 'chat-editor-voice-recorder-' + id).attr('data-id', id);
                form.submit(function () {
                    var upload = false;
                    var form = $(this);
                    if (form.find('.chat-box-image-input').val() != '' || form.find('.chat-box-file-input').val() != '') {
                        upload = true;
                    }

                    $(this).ajaxSubmit({
                        url: baseUrl + 'chat/send',
                        uploadProgress: function (event, position, total, percent) {
                            if (!upload) return false;
                            var dBox = $("#chat-box-" + form.data('id'));
                            var messagesPane = dBox.find('.message-list');
                            dBox.find('.message-list').find('.typing-indicator').remove();
                            if (messagesPane.find('.message-upload-indicator').length > 0) {
                                var uI = messagesPane.find('.message-upload-indicator');
                            } else {
                                var uI = $("<div class='message-upload-indicator'>" + messagesPane.data('sending') + " <span></span></div>");
                                messagesPane.append(uI);

                            }
                            if (messagesPane.hasClass('slimscroll')) {
                                messagesPane.slimScroll({
                                    scrollBy: messagesPane.prop('scrollHeight') + 'px'
                                });
                            } else {
                                messagesPane.get(0).scrollTop = messagesPane.get(0).scrollHeight;
                            }
                            uI.show();
                            uI.find('span').html(percent + '%');
                            if (percent == 100) {
                                uI.remove();
                            }
                        },
                        dataType: 'json',
                        success: function (data) {
                            var json = data;
                            if (json.status == 0) {
                                notifyError(json.error);
                            } else {
                                Chat.messages.push(json.messageid);
                                var box = $("#chat-box-" + form.data('id'));
                                if (box.data('cid') == null && reg == undefined) Chat.registerBox(json.cid, json.uid);
                                box.attr('data-cid', json.cid);
                                box.data('cid', json.cid);
                                box.attr('data-type', json.type);
                                box.data('type', json.type);

                                box.find('.editor form .message-cid-input').val(json.cid)
                                var container = box.find('.message-list');
                                container.append(json.message);
                                //container.animate({scrollTop : container.prop("scrollHeight")}, 200)
                                Chat.scrollerToBottom(box);
                                container.find('.seen').fadeOut();
                                reloadInits();
                            }
                            form.find('.chat-gif-input').val('');
                        },
                        error: function () {
                            form.find('.chat-gif-input').val('');
                        }
                    });
                    form.find('textarea').val('');
                    form.find('input[type=file]').val('');
                    var voiceRecorder = form.find('.chat-editor-voice-recorder');
                    var voiceRecorderId = $(voiceRecorder).data('id');
                    if (typeof Chat.editor.editors[voiceRecorderId] !== 'undefined') {
                        Chat.editor.editors[voiceRecorderId].voice.live.end();
                    }
                    return false;
                })
                div.find('.editor form textarea').keydown(function (e) {

                    if ((e.keyCode || e.which) == 13) {

                        form.submit();
                        return false;
                    }
                });
                div.find('.editor form').submit(function () {

                });
                div.find('.editor form .chat-box-image-selector').click(function () {
                    div.find('.editor form .chat-box-image-input').click();
                    return false;
                });

                div.find('.editor form .chat-box-file-selector').click(function () {
                    div.find('.editor form .chat-box-file-input').click();
                    return false;
                });
                div.find('.editor form input[type=file]').change(function () {
                    if (div.find('.editor form textarea').val() == '') {
                        div.find('.editor form').submit();
                    }
                });

                //this.repositionBoxes();
                this.addTab(div, id, img, img2);

                if (cid != null && reg == undefined && entityType == 'user') Chat.registerBox(cid);
                if (color != '') head.css('background-color', color + ' !important');

                //preload chat box details
                $.ajax({
                    url: baseUrl + 'chat/preload?csrf_token=' + requestToken,
                    data: {cid: cid, uid: uid, entity_type: entityType, entity_id: entityId},
                    success: function (data) {
                        var json = $.parseJSON(data);
                        if (div.data('cid') == null && reg == undefined && entityType == 'user') Chat.registerBox(json.cid, json.uid);
                        div.attr('data-cid', json.cid);
                        div.data('cid', json.cid);
                        div.attr('data-type', json.type);
                        div.data('type', json.type);
                        var container = div.find('.message-list');
                        container.prepend(json.messages);
                        //container.animate({scrollTop : container.prop("scrollHeight")}, 200)
                        Chat.applyScroller(div);
                        div.attr('data-uid', json.uid);
                        div.find('.editor form .message-cid-input').val(json.cid);
                        div.find('.editor form .message-entity-type-input').val(entityType);
                        div.find('.editor form .message-entity-id-input').val(entityId);
                        // Chat color
                        var headerColor = json.header_color;
                        if (headerColor !='') head.css('background-color', headerColor +' !important');
                        div.find('.actions .icon-c i').css('color', headerColor +' !important');
						var button = '<i class="fa fa-circle select-color" aria-hidden="true" style="color: '+ headerColor + '"></i>';
						var buttonHTML = div.find('.chat-color-button');
                        buttonHTML.html(' ');buttonHTML.html(button);
                        div.find('.chat-colors-container').attr('data-cid', json.cid);
                        div.find('.chat-colors-container').attr('data-uid', json.uid);
                        head.find('.maximize-button').attr('data-id', json.cid);
                        if (json.type == 'multiple' && json.accepted == '0'){
                            div.find('.editor').append(json.accept_view);
                        } else if (json.type == 'multiple' && json.accepted == '1'){
                            div.find('.editor form').show();
                        }
                        reloadInits();
                    }
                })
            }

        }

        return false;
    },

    applyScroller: function (div) {
        var container = div.find('.message-list');
        container.slimScroll({
            height: '255px',
            start: 'bottom'
        });
        container.slimScroll().bind('slimscroll', function (e, pos) {
            if (pos === 'top') {
                if (container.find('.chat-message-indicator').length === 0) {
                    $.ajax({
                        url: baseUrl + 'chat/paginate?csrf_token=' + requestToken,
                        beforeSend: function () {
                            var i = $('<div class="chat-message-indicator">' + indicator + '</div>');
                            container.prepend(i);
                        },
                        data: {offset: container.data('offset'), cid: div.data('cid')},
                        success: function (data) {
                            data = $.parseJSON(data);
                            if (data.messages !== '') {
                                container.html(data.messages + container.html()).find('.chat-message-indicator').remove();
                                container.attr('data-offset', data.offset);
                                container.data('offset', data.offset)
                                reloadInits();
                            } else {
                                container.find('.chat-message-indicator').remove();
                            }
                        }
                    });
                }
            }
        })
    },

    scrollerToBottom: function (div) {
        var container = div.find('.message-list');
        container.slimScroll({
            scrollBy: container.prop('scrollHeight') + 'px'
        })
    },

    addTab: function (d, id, img, img2) {
        var div = $('<div id="chat-box-tab-' + id + '"  data-tab="' + id + '" class="tab chat-tab active conversation-avatar"><a class="toggle" href="#"></a><a class="close-button" href=""><i class="ion-android-close"></i></a> </div>');
        var toggle = div.find('.toggle');
        toggle.append('<span style="background-image: url(' + img + ')"></span>');
        if (img2) {
            toggle.addClass('multiple');
            toggle.append('<span style="background-image: url(' + img2 + ')"></span>');
        }
        toggle.click(function () {
            var b = $("#chat-box-" + id);
            Chat.showBox(b, id);
            return false;
        });
        div.mouseover(function () {
            div.find('.close-button').show();
        });

        div.mouseout(function () {
            div.find('.close-button').hide();
        });

        div.find('.close-button').click(function () {
            Chat.closeBox(id);
            return false;
        });
        $(".chat-tabs-container").append(div);

        //hide all chat-box-intact and show this
        this.showBox(d, id);
    },

    getTab: function (cid) {
        var box = this.getBox(cid);
        var id = box.data('id');
        return $("#chat-box-tab-" + id);
    },

    showBox: function (div, id) {
        let boxCount = $('.chat-boxes-wrapper .ui-draggable').length;
        let rightCSS = '0px';
        if (boxCount <= 3){
            if (boxCount == '0'){
                div.css('right', rightCSS);
            } else if(boxCount == '2'){
                rightCSS = '260px';
                div.css('right', rightCSS);
            } else if (boxCount == '3'){
                rightCSS = '520px';
                div.css('right', rightCSS);
            }
        } else if (boxCount > 3){
         let lastBox = $(".chat-boxes-wrapper .ui-draggable:first-child");
         let cid = lastBox.data('cid');
         if(cid != null && $('#chat-box-'+cid).length > 0)
         {
             Chat.closeBox(cid);
         }else
             {
             let uid = lastBox.data('uid');
             let boxUID = $('#chat-box-uid-'+uid);
             cid = boxUID.data('cid');
             Chat.closeBox(cid);
             boxUID.remove();
             $("div[data-tab='" + cid + "']").remove();
         }
            $(".chat-boxes-wrapper .ui-draggable:first-child").css('right', '0px');
            $(".chat-boxes-wrapper .ui-draggable:nth-child(2)").css('right', '260px');
            div.css('right', '520px');
        }

/*        if (div.hasClass('chat-box-intact')) {
            $(".chat-box-intact").hide();
            $(".chat-tabs-container .tab").removeClass('active');
            $("div[data-tab='" + id + "']").addClass('active');
        }*/

        div.show();
        if (div.data('mids') != undefined && div.data('mids') != '') {
            ids = div.data('mids');
            div.attr('data-mids', '');
            div.data('mids', '');
            this.markRead(ids.split(','));
        }

        var id = div.data('id');
        var tab = $("#chat-box-tab-" + id);
        if (tab.find('.count').length > 0) tab.find('.count').remove();
        this.scrollDown(div);
    },

    scrollDown: function (box) {
        var container = box.find('.message-list');
        container.animate({scrollTop: container.prop("scrollHeight")}, 200)
    },

    closeBox: function (id) {
        var box = $("#chat-box-" + id);
        var tab = $("div[data-tab='" + id + "']");
        if (box.data('cid') != null) {
            this.unRegisterBox(box.data('cid'))
        }
        tab.remove();
        box.remove();

        let boxLength = $(".chat-boxes-wrapper .ui-draggable").length;
        if (boxLength == 1){
            $(".chat-boxes-wrapper .ui-draggable:first-child").css('right', '0px');
        }else if(boxLength > 1){
            $(".chat-boxes-wrapper .ui-draggable:first-child").css('right', '0px');
            $(".chat-boxes-wrapper .ui-draggable:nth-child(2)").css('right', '260px');
        }

        var tab = '';
/*        $(".chat-tabs-container .tab").each(function () {
            tab = $(this);
        });

        if (tab) {
            div = $("#chat-box-" + tab.data('tab'));
            this.showBox(div, tab.data('tab'));
        }*/

        //alert(box.data('cid'));
        return false;
    },

    hideBox: function (id = null, box = null) {
        if (!box) box = $("#chat-box-" + id);
        var boxBody = box.find('.chat-box');
        boxBody.css('height','unset');
        box.css('height','unset');
        boxBody.find('.slimScrollDiv, .editor').hide();
        boxBody.find('.head .actions .minimize-button').hide();
        boxBody.find('.head .actions .maximize-button').show();
    },

    appendMessage: function (cid, html) {

        if ($('.message-box-' + cid).length > 0) {
            //we must not open that since we are on the message page
            var c = $('.message-box-' + cid);
            var container = c.find('.messages-pane');
            container.append(html).animate({scrollTop: container.prop("scrollHeight")}, 200);
        }
        //get chat box
        var box = this.getBox(cid);
        if (box.length > 0) {
            box.find('.message-list').find('.typing-indicator').remove();
            var container = box.find('.message-list');
            container.append(html).animate({scrollTop: container.prop("scrollHeight")}, 200);
            container.find('.seen').fadeOut();

            /* var pane = $('#messages-pane-' + cid);
            if(pane.length) {
                pane.append(html).animate({scrollTop: container.prop("scrollHeight")}, 200);
            } */
        }
    },

    putToLoad: function (cid, ids) {
        this.toLoad.push([cid, ids]);
    },

    pushArrived: function () {
        //from here we can load the messages need
        if (this.toLoad.length > 0) {
            $.ajax({
                url: baseUrl + 'chat/load/messages?csrf_token=' + requestToken,
                type: 'POST',
                data: {data: this.toLoad},
                success: function (data) {
                    var json = $.parseJSON(data);
                    Chat.toLoad = [];
                    $.each(json.messages, function (cid, html) {
                        Chat.appendMessage(cid, html);
                    });
                    reloadInits();
                }
            })
        }
    },
    markRead: function (mIds) {
        $.ajax({
            url: baseUrl + 'chat/mark/read?csrf_token=' + requestToken,
            data: {ids: mIds},
            success: function (c) {
                Chat.updateUnread(c);
            },
            error: function () {
                Chat.markRead(mIds);
            }
        })
    },
    updateUnread: function (d) {
        var c = $("#message-dropdown-container");
        var a = $("#message-dropdown-container > a");
        if (d > 0) {
            var span = a.find('span');
            if (!span.length) {
                var span = $("<span class='count'></span>");
                a.append(span);
            }
            span.html(d > 9 ? '9+' : d);
            Pusher.addCount(d);
        } else {
            if (a.find('span').length) {
                a.find('span').remove();
            }
        }
    },

    refreshonlineFriendsList: function (force) {
        if (!$('#chat-boxes-container .onlines-container .online-search input[type=text]').val() || !$('#chat-group-modal .online-search input[type=text]').val() || force) {
            $.ajax({
                url: baseUrl + 'chat/friends/online?csrf_token=' + requestToken,
                success: function (html) {
                    if (!$('#chat-boxes-container .onlines-container .online-search input[type=text]').val() || force) {
                        $("#chat-boxes-container .online-friend-list").html(html);
                    }
                    if (!$('#chat-group-modal .online-search input[type=text]').val() || force) {
                        $("#chat-group-modal .online-friend-list").html(html);
                    }
                }
            });
        }
    },

    onFriendConnected: function (user) {
        Chat.refreshonlineFriendsList();
    },

    onFriendDisconnected: function (user) {
        Chat.refreshonlineFriendsList();
    },

    slimscroll: function (result, element, position, event) {
        if ($(element).hasClass('conversation-list') && !$(element).hasClass('search-result') && $(element).hasClass('paginate') && position === 'bottom') {
            Chat.paginateConversations(element);
        }
    },

    paginateConversations: function (container) {
        var offset = $(container).data('offset') || 0;
        var limit = $(container).data('limit') || 0;
        var cid = $(container).data('cid') || 0;
        var entityType = $(container).data('entity_type');
        var entityId = $(container).data('entity_id');
        var loading = parseInt($(container).data('loading')) || 0;
        var status = parseInt($(container).data('status')) || 1;
        container = container || $('conversation-list').get(0);
        if (container && ((!loading && status) || $(container).data('term'))) {
            $.ajax({
                url: baseUrl + 'chat/load/conversations?offset=' + offset + '&limit=' + limit + '&cid=' + cid + '&term=' + $(container).data('term') + '&entity_type='+ entityType + '&entity_id='+ entityId +'&csrf_token=' + requestToken,
                beforeSend: function () {
                    $(container).data('loading', 1);
                    if(!$(container).find('.chat-message-indicator').length) {
                        $(container).append($('<div class="chat-message-indicator">' + indicator + '</div>'));
                    }
                },
                success: function (response) {
                    var data = JSON.parse(response);
                    $(container).find('.conversations-list-item.searched').remove();
                    if($(container).data('term')) {
                        $(container).addClass('search-result');
                    } else {
                        $(container).removeClass('search-result');
                    }
                    if (data.status) {
                        $(container).find('.chat-message-indicator').remove();
                        $(container).html($(container).html() + data.html);
                    }
                    if(!$(container).data('term')) {
                        $(container).data('offset', data.offset);
                        $(container).data('limit', data.limit);
                        $(container).data('cid', data.cid);
                        $(container).data('status', data.status);
                        $(container).find('.chat-message-indicator').remove();
                        $(container).data('loading', 0);
                        $(container).attr('data-loading', 0);
                    }
                    reloadInits();
                },
                error: function (e) {
                    $(container).find('.chat-message-indicator').remove();
                    $(container).data('loading', 0);
                    $(container).attr('data-loading', 0);
                }
            });
        }
    },

    editor: {
        editors: [],

        init: function () {
            Chat.editor.attachEvents();
        },

        attachEvents: function () {
            $(document).on('click', '.chat-editor-voice-recorder .control', function (e) {
                e.preventDefault();
                var container = $(this).closest('.chat-editor-voice-recorder');
                var id = $(container).data('id');
                if ($(container).hasClass('recording')) {
                    Chat.editor.editors[id].voice.live.recorderStop();
                    Chat.editor.editors[id].voice.live.recorderSave();
                } else if ($(container).hasClass('recorded')) {
                    Chat.editor.editors[id].voice.live.recorderStart(true);
                } else {
                    if (typeof Chat.editor.editors[id] === 'undefined') {
                        Chat.editor.editors[id] = {};
                    }
                    Chat.editor.editors[id].voice = {};
                    Chat.editor.editors[id].voice.live = window.live;
                    Chat.editor.editors[id].voice.live.init({
                        autoplay: false,
                        constraint: {audio: true},
                        record: $(container).find('.voice-input'),
                        recorderReadyCallback: function (live) {
                            Chat.editor.editors[id].voice.live.recorderStart(true);
                            $(container).removeClass('recorded');
                            $(container).addClass('recording');
                        },
                        recorderStartCallback: function (live) {
                            $(container).removeClass('recorded');
                            $(container).addClass('recording');
                        },
                        recorderStopCallback: function (live) {
                            $(container).removeClass('recording');
                            $(container).addClass('recorded');
                        },
                        recorderSaveCallback: function (live) {
                            var reader = new FileReader();
                            reader.onload = function (event) {
                                $(container).find('.voice-input').val(event.target.result);
                            };
                            reader.readAsDataURL(live.blob);
                        },
                        liveEndCallback: function (live) {
                            $(container).removeClass('recorded');
                            $(container).removeClass('recording');
                            $(container).find('.voice-input').val('');
                        },
                    });
                }
            });

            $(document).on('click', '.chat-editor-voice-recorder .play', function (e) {
                e.preventDefault();
                var container = $(this).closest('.chat-editor-voice-recorder');
                var id = $(container).data('id');
                Chat.editor.editors[id].voice.live.record.play();
            });

            $(document).on('click', '.chat-editor-voice-recorder .close', function (e) {
                e.preventDefault();
                var container = $(this).closest('.chat-editor-voice-recorder');
                var id = $(container).data('id');
                Chat.editor.editors[id].voice.live.end();
            });
        }
    }
};

window.chatPushRun = false;

Pusher.addHook('Chat.push');

//addPageHook('Chat.init');

function chat_message_upload() {
    var form = $('#message-chat-form');
    if (form.find('textarea').val() == '') {
        form.submit();
    }
}

function delete_conversation() {
    var cid = $("#message-cid-input").val();
    var url = baseUrl + 'chat/delete/conversation?cid=' + cid;
    return confirm.url(url);
}

function leaveGroupChat(cid) {
    Hook.fire('chat.group.member.left.before', null, [cid]);
    Chat.groupChatLeave++;
    $.ajax({
        url: baseUrl + 'chat/leave/conversation?cid=' + cid + '&ajax=1',
        success: function (data) {
            data = JSON.parse(data);
            Hook.fire('chat.group.member.left', null, [cid, data]);
        }
    });
    return false;
}

function acceptGroupChat(cid) {
    let div = $('#chat-box-'+cid);
    div.css('opacity', '0.5');
    $.ajax({
        url: baseUrl + 'chat/accept/conversation?cid=' + cid + '&ajax=1',
        success: function (data) {
            data = JSON.parse(data);
            if (data.status == 1){
                div.css('opacity', '1');
                div.find('.editor .group-chat-acceptance').remove();
                div.find('.editor form').show();
                notifySuccess(data.message);
            } else {
                notifyError(data.message);
            }
        }
    });
    return false;
}

function delete_chat_message(id) {
    var message = $('.chat-message-' + id);
    message.css('opacity', '0.5');
    $.ajax({
        url: baseUrl + 'chat/delete/message?id=' + id + '&csrf_token=' + requestToken,
        success: function () {
            message.slideUp().remove();
        },
        error: function () {
            message.css('opacity', 1);
        }
    });
    return false;
}

$(function () {
    Hook.register('friend.connected', Chat.onFriendConnected);
    Hook.register('friend.disconnected', Chat.onFriendDisconnected);
    Chat.init();
});

function inArrayJs(item, arr) {
    var item_as_string = JSON.stringify(item);

    var contains = arr.some(function(ele){
        return JSON.stringify(ele) === item_as_string;
    });
    return contains;
}
function onlyUnique(value, index, self) {
    return self.indexOf(value) === index;
}
