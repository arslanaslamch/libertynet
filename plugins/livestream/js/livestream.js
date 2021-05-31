if (typeof livestream === 'undefined') {
    livestream = {
        id: null,
        sessionDescription: null,
        iceServers: [{urls: 'stun:stun.l.google.com:19302'}, {urls: 'stun:stun.services.mozilla.com:3478'}],
        iceTransportPolicy: 'all',
        peerConnection: null,
        peerConnections: {},
        candidateQueue: [],
        offerersCandidateQueue: {},
        memberCount: 0,
        status: 0,
        joinToken: null,
        userId: null,
        pollInterval: 5000,
        keepAliveInterval: null,
        recordLimitInterval: null,
        startTime: null,
        timeUpdate: null,
        ended: null,
        uploading: null,
        recordLimit: 10000000,
        blob: new Blob([], {type: 'video/webm', name: 'blob.webm'}),
        uploadWithPusher: false,
        devices: [],
        options: {
            constraint: {audio: true, video: true},
            player: '.livestream-player',
            muted: true,
            record: false,
            audioDevice: null,
            videoDevice: null,
            streamReadyCallback: function() {
                if (!livestream.options.audioDevice || !livestream.options.videoDevice) {
                    livestream.listDevices().then(function(devices) {
                        Hook.fire('livestream.options.updated');
                    }).catch(function(reason) {
                    });
                }
            }
        },
        clone: null,

        init: function (options) {
            options = options || {};
            if (!livestream.clone) {
                livestream.clone = jQuery.extend(true, {}, livestream);
            }
            if (options.destroy) {
                if (livestream.peerConnection) {
                    livestream.peerConnection.close();
                }
                for (let i in livestream.peerConnections) {
                    if (livestream.peerConnections.hasOwnProperty(i)) {
                        livestream.peerConnections[i].close();
                    }
                }
                if (livestream.keepAliveInterval) {
                    clearInterval(livestream.keepAliveInterval);
                }
                window.livestream = jQuery.extend(true, {}, livestream.clone);
                return window.livestream.init({reload: true});
            }
            livestream.recordLimit = maxVideoSize || livestream.recordLimit;
            livestream.pollInterval = ajaxInterval || livestream.pollInterval;
            livestream.live = window.live;
            livestream.addPhrases();
            if (!options.reload) {
                livestream.addEvents();
                Pusher.addHook('livestream.push');
                if (typeof addPageHook === 'function') {
                    addPageHook(function() {
                        livestream.init({destroy: true});
                        if (window.location.href.match(/livestream\/?[^\/]+/g)) {
                            Hook.fire('livestream.content.loaded', null, [window.location.href]);
                        }
                    });
                }
                if (window.location.href.match(/livestream\/?[^\/]+/g)) {
                    Hook.fire('livestream.content.loaded', null, [window.location.href]);
                }
            }
        },

        addEvents: function () {
            Hook.register('ajax.form.submit.serialize.before', function(result, form, request, jqForm, options) {
                switch (request.path) {
                    case 'livestream/start':
                        if (livestream.options.constraint.video) {
                            if (!$(form).find('input[name="image_data"][type="hidden"]').length) {
                                $(form).prepend('<input type="hidden" name="image_data" class="livestream-image-input" />');
                            }
                            let imageInput = $(form).find('input[name="image_data"][type="hidden"]');
                            imageInput.val(livestream.live.snapTake());
                        }
                        break;

                    default:
                        break
                }
            });

            Hook.register('ajax.form.submit.success', function(result, form, request, response) {
                let data = JSON.parse(response);
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }
                switch (request.path) {
                    case 'livestream/start':
                        if (data.status) {
                            let startTime = new Date().getTime();
                            let options = livestream.options;
                            if (livestream.keepAliveInterval) {
                                clearInterval(livestream.keepAliveInterval);
                            }
                            livestream.keepAliveInterval = setInterval(function() {
                                livestream.keepAlive();
                            }, livestream.pollInterval);
                            $('.livestream-modal .modal-title').html(data.livestream.title);
                            $('.main.livestream-content').html(data.view);
                            livestream.id = data.livestream.id;
                            livestream.startTime = startTime;
                            livestream.type = data.livestream.type;
                            livestream.userId = data.livestream.user_id;
                            livestream.token = data.livestream.token;
                            livestream.status = data.livestream.status;
                            livestream.ended = false;
                            livestream.iceTransportPolicy = data.ice_transport_policy;
                            livestream.iceServers = data.ice_servers;

                            livestream.resetTimer();
                            Pusher.sendMessage({type: 'livestream.ended', livestream_id : livestream.id, data: {start_time: startTime / 1000}});

                            options.streamReadyCallback = function(live) {
                                return livestream.startTimer();
                            };
                            options.recorderReadyCallback = function(live) {
                                return live.recorderStart();
                            };
                            livestream.live.init(options);
                            if (livestream.live.recorder) {
                                if (livestream.recordlimitInterval) {
                                    clearInterval(livestream.recordlimitInterval);
                                }
                                livestream.recordlimitInterval = setInterval(function() {
                                    let blob = new Blob(livestream.live.chunks, {
                                        type: 'video/webm',
                                        name: 'blob.webm'
                                    });
                                    if (blob.size < livestream.recordLimit) {
                                        livestream.blob = blob;
                                    } else {
                                        notify(Math.round(livestream.recordLimit / 1000000) + 'MB ' + language.phrase('record-limit-reached'), 'warning');
                                        clearInterval(livestream.recordlimitInterval);
                                    }
                                });
                            } else {
                                notify(language.phrase('browser-not-support-recording'), 'warning');
                            }
                        }
                        break;

                    default:
                        break
                }
            });

            Hook.register('pusher.send.before', function(result, data) {
                if (typeof data === 'object' && data.type === 'livestream.ended' && data.livestream_id === livestream.id && livestream.uploading !== false) {
                    livestream.uploading = true;
                }
            });

            Hook.register('pusher.send.progress', function(result, progress) {
                if (livestream.uploading === true && livestream.uploadWithPusher) {
                    let percent = Math.round(progress * 100);
                    if (percent === 100) {
                        livestream.uploading = false;
                    }
                    $('#site-wide-notification .message').html(language.phrase('uploading-stream') + ' ' + percent + '%' + ' ' + language.phrase('complete'));
                }
            });

            Hook.register('pusher.send.after', function(result, data) {
                if (typeof data === 'object' && data.type === 'livestream.ended' && data.livestream_id === livestream.id && livestream.uploading === true) {
                    livestream.uploading = false;
                }
            });

            Hook.register('livestream.content.loaded', function(result, url, data) {
                let path = url.replace(new RegExp('^' + baseUrl.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + '/' + '-]', 'g'), '\\$&'), 'gi'), '').split('#')[0].split('?')[0].replace(/\/$/gi, '');
                switch (path) {
                    case 'livestream/start':
                        if (livestream.supported()) {
                            livestream.updateDeviceOptions().then(function(options) {
                                Hook.fire('livestream.options.updated');
                            });
                        } else {
                            notify(language.phrase('livestream-not-supported'), 'error');
                        }
                        break;

                    default:
                        if (path.match(/^livestream\/[^\/]+/g)) {
                            let view = $('.livestream-content .livestream-view');
                            livestream.id = view.data('livestream-id');
                            livestream.type = view.data('livestream-type');
                            livestream.userId = view.data('livestream-user-id');
                            livestream.token = view.data('livestream-token');
                            livestream.status = view.data('livestream-status');
                            livestream.startTime = view.data('livestream-start-timestamp') * 1000;
                            livestream.iceServers = view.data('livestream-ice-servers');
                            livestream.iceTransportPolicy = view.data('livestream-ice-transport-policy');

                            if (livestream.status === 1) {
                                livestream.resetTimer();
                                livestream.supported() ? livestream.join() : notify(language.phrase('livestream-not-supported'), 'error');
                            }
                        }
                        break
                }
            });

            Hook.register('livestream.options.updated', function(result) {
                let isAudio = !livestream.options.constraint.video;
                let image = $('.livestream-content .livestream-input-image input[type="file"]');
                if (isAudio) {
                    if (image.val()) {
                        setTimeout(function() {
                            let preview = $('.livestream-content .livestream-input-image');
                            $('.livestream-content .media').css('background-image', preview.css('background-image'));
                        }, 500);
                    } else {
                        $('.livestream-content .media').css('background-image', 'url(' + baseUrl + 'plugins/livestream/images/no_image.png' + ')');
                    }
                } else {
                    $('.livestream-content .media').css('background-image', 'none');
                }
                livestream.live.init(livestream.options)
            });

            Hook.register('livestream.member.left', function(result, token) {
                if (livestream.peerConnections[token]) {
                    livestream.peerConnections[token].close();
                    delete livestream.peerConnections[token];
                    livestream.memberCount = Object.keys(livestream.peerConnections).length;
                    Pusher.sendMessage({
                        type: 'livestream.member.count',
                        livestream_id: livestream.id,
                        count: livestream.memberCount
                    }, 'type');
                    $('.livestream-content .viewers-count .count').html(livestream.memberCount);
                }
            });

            Hook.register('livestream.member.joined', function(result, token) {
                if (livestream.peerConnections[token]) {
                    livestream.memberCount = Object.keys(livestream.peerConnections).length;
                    Pusher.sendMessage({
                        type: 'livestream.member.count',
                        livestream_id: livestream.id,
                        count: livestream.memberCount
                    }, 'type');
                    $('.livestream-content .viewers-count .count').html(livestream.memberCount);
                }
            });

            $(document).on('shown.bs.modal', '.livestream-modal', function(e) {
                let invoker = $(e.relatedTarget);
                let modal = $('.livestream-modal');
                let previousUrl = $(modal).data('url');
                let url = $(invoker).attr('href');
                let modalTitle = $(modal).find('.modal-title');
                let modalContent = $(modal).find('.modal-content');
                let modalBody = $(modal).find('.modal-body');
                if (!modalContent.find('div.livestream-modal-ajax-loading').length) {
                    modalContent.prepend('<div class="livestream-modal-ajax-loading"></div>');
                }
                if (url && url !== previousUrl) {
                    modalTitle.html($(invoker).data('title'));
                    modalBody.html('');
                    $.ajax({
                        url: url + (url.indexOf('?') >= 0 ? '&' : '?') + 'ajax=true&csrf_token=' + requestToken,
                        beforeSend: function () {
                            modalContent.find('div.livestream-modal-ajax-loading').fadeIn('fast');
                        },
                        success: function (response) {
                            let data = JSON.parse(response);
                            $(modalTitle).html(data.title);
                            $(modalBody).html(data.html);
                            if (typeof reloadInits === 'function') {
                                reloadInits();
                            }
                            $(modal).data('url', url);
                            $(modal).attr('data-url', url);
                            if (typeof data.message !== 'undefined' && data.message) {
                                notify(data.message, data.status ? 'success' : 'error');
                            }
                            if (typeof data.redirect_url !== 'undefined' && data.redirect_url) {
                                window.location.href = data.redirect_url;
                            }
                            Hook.fire('livestream.content.loaded', null, [url, data]);
                            modalContent.find('div.livestream-modal-ajax-loading').fadeOut('fast');
                        },
                        error: function (error) {
                            notify(language.phrase('modal-load-error'), 'error');
                            modalContent.find('div.livestream-modal-ajax-loading').fadeOut('fast');
                        }
                    });
                } else {
                    Hook.fire('livestream.content.loaded', null, [url, false]);
                }
            });

            $(document).on('hide.bs.modal', '.livestream-modal', function(e) {
            });

            $(document).on('change', '.livestream-input-image input[type="file"]', function(e) {
                let isAudio = !livestream.options.constraint.video;
                let image = $('.livestream-content .livestream-input-image input[type="file"]');
                if (isAudio) {
                    if (image.val()) {
                        setTimeout(function() {
                            let preview = $('.livestream-content .livestream-input-image');
                            $('.livestream-content .media').css('background-image', preview.css('background-image'));
                        }, 500);
                    } else {
                        $('.livestream-content .media').css('background-image', 'url(' + baseUrl + 'plugins/livestream/images/no_image.png' + ')');
                    }
                } else {
                    $('.livestream-content .media').css('background-image', 'none');
                }
            });

            $(document).on('click', '.livestream-input-type .magic-select-option', function(e) {
                let option = e.target;
                let type = $(option).data('value');
                let form = $(option).closest('form');
                let initialConstraint = JSON.parse(JSON.stringify(livestream.options.constraint));
                $(form).data('livestream-type', type);
                $(form).attr('data-livestream-type', type);
                if (type === 'audio') {
                    livestream.options.constraint.audio = !!livestream.options.audioDevice ? {deviceId: {exact: livestream.options.audioDevice}} : false;
                    livestream.options.constraint.video = false
                } else {
                    livestream.options.constraint.audio = !!livestream.options.audioDevice ? {deviceId: {exact: livestream.options.audioDevice}} : false;
                    livestream.options.constraint.video = !!livestream.options.videoDevice ? {deviceId: {exact: livestream.options.videoDevice}} : false;
                }
                if (JSON.stringify(initialConstraint) !== JSON.stringify(livestream.options.constraint)) {
                    Hook.fire('livestream.options.updated');
                }
            });

            $(document).on('click', '.livestream-input-camera .magic-select-option', function(e) {
                let option = e.target;
                let videoDeviceId = $(option).data('value');
                let form = $(option).closest('form');
                let initialVideoDevice = JSON.parse(JSON.stringify(livestream.options.videoDevice));
                $(form).data('livestream-camera', videoDeviceId);
                $(form).attr('data-livestream-camera', videoDeviceId);
                livestream.options.videoDevice = videoDeviceId;
                livestream.options.constraint.video = !!livestream.options.videoDevice ? {deviceId: {exact: livestream.options.videoDevice}} : false;
                if (JSON.stringify(initialVideoDevice) !== JSON.stringify(livestream.options.videoDevice)) {
                    Hook.fire('livestream.options.updated');
                }
            });

            $(document).on('submit', '.admin .livestream-form.ajax-form', function (e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                url = url ? url : window.location.href;
                document.activeElement.blur();
                if (!form.find('div.ajax-form-loading').length) {
                    form.prepend('<div class="ajax-form-loading"></div>');
                }
                if (!form.find('input[name="ajax"]').length) {
                    form.prepend('<input type="hidden" name="ajax" value="1" />');
                }

                form.find('div.ajax-form-loading').fadeIn('fast');

                form.ajaxSubmit({
                    url: url,
                    success: function (data) {
                        data = JSON.parse(data);
                        if (typeof data === 'string') {
                            data = JSON.parse(data);
                        }
                        if (typeof data.message !== 'undefined' && data.message) {
                            notify(data.message, data.status ? 'success' : 'error');
                        }
                        if (typeof data.redirect_url !== 'undefined' && data.redirect_url) {
                            document.location.href = data.redirect_url;
                        } else {
                            let action = typeof data.action !== 'undefined' && data.action ? data.action : '';
                            switch (action) {
                                case 'add':

                                    break;

                                case 'edit':

                                    break;

                                case 'delete':

                                    break;

                                default:
                                    break;
                            }
                            form.find('div.ajax-form-loading').fadeOut('fast');
                        }
                    },
                    error: function (error) {
                        notify('Error occurred while submitting form', 'error');
                        form.find('div.ajax-form-loading').fadeOut('fast');
                    }
                });
                return false;
            });

            $(document).on('change', '.server-input-type', function (e) {
                let type = $(this).val();
                let form = $(this).closest('form');
                $(form).data('server-type', type);
                $(form).attr('data-server-type', type);
            });

            $(document).on('click', '.livestream-input-microphone .magic-select-option', function(e) {
                let option = e.target;
                let audioDeviceId = $(option).data('value');
                let form = $(option).closest('form');
                let initialAudioDevice = JSON.parse(JSON.stringify(livestream.options.audioDevice));
                $(form).data('livestream-microphone', audioDeviceId);
                $(form).attr('data-livestream-microphone', audioDeviceId);
                livestream.options.audioDevice = audioDeviceId;
                livestream.options.constraint.audio = !!livestream.options.audioDevice ? {deviceId: {exact: livestream.options.audioDevice}} : false;

                if (JSON.stringify(initialAudioDevice) !== JSON.stringify(livestream.options.audioDevice)) {
                    Hook.fire('livestream.options.updated');
                }
            });

            $(document).on('click', '.livestream-stop-button', function(e) {
                let button = e.target;
                if (!button.disabled) {
                    livestream.end();
                }
            });

            $(document).on('click', '.livestream-modal button.close', function(e) {
                if (!livestream.ended) {
                    let message = language.phrase('livestream-close-confirm');
                    $('#confirmModal #confirm-button').unbind().click(function() {
                        $('#confirmModal').modal('hide');
                        livestream.end();
                    });
                    $('#confirmModal .modal-body').html(message);
                    $('#confirmModal').modal('show');
                }
            });
        },

        addPhrases: function () {
            if (typeof livestreamPhrases !== 'undefined') {
                language.addPhrases(livestreamPhrases);
            }
        },

        supported: function () {
            let result = false;
            result = typeof navigator !== 'undefined' && typeof navigator.mediaDevices !== 'undefined' && typeof navigator.mediaDevices.getUserMedia !== 'undefined' && typeof navigator.mediaDevices.enumerateDevices !== 'undefined' && typeof window.RTCPeerConnection !== 'undefined' ? true : result;
            return result;
        },

        listDevices: function () {
            return new Promise(function(resolve, reject) {
                navigator.mediaDevices.enumerateDevices().then(function(devices) {
                    livestream.devices = devices;
                    livestream.updateDeviceOptions(devices);
                    return resolve(devices);
                }).catch(function(reason) {
                    return reject(reason);
                });
            });
        },

        updateDeviceOptions: function (devices) {
            return new Promise(function(resolve, reject) {
                devices = devices || livestream.devices;
                let videoDeviceOptions = document.querySelector('.livestream-content .magic-select[data-name="livestream[camera]"] .magic-select-content');
                let audioDeviceOptions = document.querySelector('.livestream-content .magic-select[data-name="livestream[microphone]"] .magic-select-content');
                videoDeviceOptions.innerHTML = '';
                audioDeviceOptions.innerHTML = '';
                for (let i = 0; i < devices.length; ++i) {
                    let deviceInfo = devices[i];
                    let option = document.createElement('div');
                    option.className = 'magic-select-option';
                    option.setAttribute('data-value', deviceInfo.deviceId);
                    if (deviceInfo.kind === 'audioinput') {
                        let deviceName = deviceInfo.label || language.phrase('microphone') + ' ' + (i + 1);
                        option.innerHTML = deviceName;
                        audioDeviceOptions.appendChild(option);
                        if (!livestream.options.audioDevice) {
                            livestream.options.audioDevice = deviceInfo.deviceId;
                            $('.livestream-form [name="livestream[microphone]"]').val(livestream.options.audioDevice);
                            audioDeviceOptions.parentNode.querySelector('.magic-select-toggle').innerHTML = deviceName;
                        }
                    } else if (deviceInfo.kind === 'videoinput') {
                        let deviceName = deviceInfo.label || language.phrase('camera') + ' ' + (i + 1);
                        option.innerHTML = deviceName;
                        videoDeviceOptions.appendChild(option);
                        if (!livestream.options.videoDevice) {
                            livestream.options.videoDevice = deviceInfo.deviceId;
                            $('.livestream-form [name="livestream[camera]"]').val(livestream.options.videoDevice);
                            videoDeviceOptions.parentNode.querySelector('.magic-select-toggle').innerHTML = deviceName;
                        }
                    }
                }
                return resolve({video: videoDeviceOptions, audio: audioDeviceOptions});
            });
        },

        join: function () {
            let view = $('.livestream-content .livestream-view');
            let offererId = view.data('user-id');
            let offererToken = view.data('token');

            if (!livestream.joinToken && livestream.id && offererId && offererId !== livestream.userId && offererToken && offererToken !== livestream.token && livestream.status === 1) {

                livestream.joinToken = offererToken;

                livestream.peerConnection = new RTCPeerConnection({iceTransportPolicy: livestream.iceTransportPolicy, iceServers: livestream.iceServers});

                livestream.peerConnection.onicecandidate = function(e) {
                    if (e.candidate) {
                        console.debug(livestream.userId, 'ICE (' + (e.candidate.candidate.indexOf('relay') < 0 ? 'STUN' : 'TURN') + ') Sent: ', e.candidate);
                        Pusher.sendMessage({
                            type: 'livestream.ice.candidate',
                            livestream_id: livestream.id,
                            user_id: livestream.userId,
                            sender_id: offererId,
                            token: offererToken,
                            data: e.candidate
                        });
                    }
                };

                livestream.peerConnection.oniceconnectionstatechange = function(e) {
                    let loading = $('.livestream-content .livestream-view .livestream-loading');
                    if (livestream.peerConnection) {
                        switch (livestream.peerConnection.iceConnectionState) {
                            case 'connected':
                                loading.removeClass('active');
                                break;

                            case 'completed':
                                notify(language.phrase('joined'), 'success');
                                loading.removeClass('active');
                                break;

                            case 'failed':
                                livestream.stopTimer();
                                notify(language.phrase('connection-failed'), 'error');
                                loading.addClass('active');
                                break;

                            case 'disconnected':
                                livestream.stopTimer();
                                notify(language.phrase('disconnected'), 'warning');
                                loading.addClass('active');
                                break;

                            case 'closed':
                                livestream.stopTimer();
                                notify(language.phrase('livestream-ended'), 'success');
                                loading.addClass('active');
                                break;
                        }
                    }
                };

                livestream.peerConnection.onaddstream = function(e) {
                    let player = document.querySelector(livestream.options.player);
                    player.onloadedmetadata = function(e) {
                        let playPromise = e.target.play();
                        playPromise !== undefined ? playPromise.then(function() {}).catch(function() {}) : undefined;
                        e.target.muted = false;
                        e.target.setAttribute('muted', false);
                    };
					console.log(e.stream);
                    player.srcObject = e.stream;
                    livestream.startTimer();
                    closeNotify();
                };

                livestream.peerConnection.addTransceiver('audio');
                if (livestream.type === 'video') {
                    livestream.peerConnection.addTransceiver('video');
                }

                livestream.peerConnection.createOffer().then(function(offer) {
                    livestream.sessionDescription = offer;
                    livestream.peerConnection.setLocalDescription(offer)
                }).then(function() {
                    notify(language.phrase('joining'), 'warning', 3600000);
                    Pusher.sendMessage({
                        type: 'livestream.session.description',
                        livestream_id: livestream.id,
                        user_id: livestream.userId,
                        sender_id: offererId,
                        token: offererToken,
                        data: livestream.peerConnection.localDescription
                    });
                }).catch(function(reason) {
                    console.log(reason);
                });
            }
        },

        accept: function (detail) {
            let offererId = detail.sender_id;
            if (detail.livestream_id && detail.livestream_id === livestream.id) {
                if (detail.sender_id && detail.sender_id !== livestream.userId) {
                    if (detail.token && detail.token !== livestream.token) {
                        let offererToken = detail.token;
                        if (livestream.status === 1 && typeof detail.data === 'object' && detail.data.type === 'offer') {
                            let offer = detail.data;
                            if (typeof livestream.peerConnections[offererToken] === 'undefined') {
                                livestream.peerConnections[offererToken] = new RTCPeerConnection({iceTransportPolicy: livestream.iceTransportPolicy, iceServers: livestream.iceServers});
                                livestream.peerConnections[offererToken].onicecandidate = function(e) {
                                    if (e.candidate) {
                                        console.debug(offererId, 'ICE (' + (e.candidate.candidate.indexOf('relay') < 0 ? 'STUN' : 'TURN') + ') Sent: ', e.candidate);
                                        Pusher.sendMessage({
                                            type: 'livestream.ice.candidate',
                                            livestream_id: livestream.id,
                                            user_id: offererId,
                                            sender_id: livestream.userId,
                                            token: offererToken,
                                            data: e.candidate
                                        });
                                    }
                                };

                                livestream.peerConnections[offererToken].oniceconnectionstatechange = function(e) {
                                    if (livestream.peerConnections[offererToken]) {
                                        switch (livestream.peerConnections[offererToken].iceConnectionState) {
                                            case 'connected':
                                                Hook.fire('livestream.member.joined', null, [offererToken, 'connected']);
                                                break;

                                            case 'failed':
                                                Hook.fire('livestream.member.left', null, [offererToken, 'failed']);
                                                break;

                                            case 'disconnected':
                                                Hook.fire('livestream.member.left', null, [offererToken, 'disconnected']);
                                                break;

                                            case 'closed':
                                                Hook.fire('livestream.member.left', null, [offererToken, 'closed']);
                                                break;
                                        }
                                    }
                                };

                                livestream.peerConnections[offererToken].addStream(livestream.live.stream);
                                livestream.peerConnections[offererToken].setRemoteDescription(new RTCSessionDescription(offer)).then(function() {
                                    return livestream.peerConnections[offererToken].createAnswer();
                                }).then(function(answer) {
                                    return livestream.peerConnections[offererToken].setLocalDescription(answer);
                                }).then(function() {
                                    Pusher.sendMessage({
                                        type: 'livestream.session.description',
                                        livestream_id: livestream.id,
                                        user_id: offererId,
                                        sender_id: livestream.userId,
                                        token: offererToken,
                                        data: livestream.peerConnections[offererToken].localDescription
                                    });
                                });
                            }
                        }
                    }
                }
            }
        },

        end: function () {
            if (livestream.id && !livestream.ended) {
                closeNotify();
                livestream.status = 2;
                livestream.ended = true;
                $('.livestream-content .livestream-stop-button').each(function(index, button) {
                    button.disabled = true;
                    button.setAttribute('disabled', true);
                });
                $('.livestream-content .livestream-loading').addClass('active');
                for (let i in livestream.peerConnections) {
                    if (livestream.peerConnections.hasOwnProperty(i)) {
                        livestream.peerConnections[i].close();
                    }
                }
                livestream.stopTimer();
                livestream.live.recorderStop();
                if (!livestream.joinToken) {
                    // livestream.blob = livestream.live.recorderSave();
                    livestream.blob = livestream.blob.slice(0, livestream.blob.size, 'video/webm');
                    livestream.blob.name = 'blob.webm';
                    if(livestream.uploadWithPusher) {
                        let fileReader = new FileReader();
                        fileReader.onload = function(e) {
                            notify(language.phrase('uploading-stream') + ' 0%' + ' ' + language.phrase('complete'), 'warning', 3600000);
                            Pusher.sendMessage({type: 'livestream.ended', livestream_id : livestream.id, data: {record: e.target.result}});
                        };
                        fileReader.readAsDataURL(livestream.blob);
                    } else {
                        let formData = new FormData();
                        formData.append('livestream_id', livestream.id);
                        formData.append('record', livestream.blob, livestream.blob.name);
                        $.ajax({
                            url: baseUrl + 'livestream/ajax?action=end&csrf_token=' + requestToken,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            xhr: function () {
                                let xhr = new window.XMLHttpRequest();
                                xhr.upload.addEventListener('progress', function(e) {
                                    if (e.lengthComputable) {
                                        let progress = e.loaded / e.total;
                                        if (livestream.uploading === true) {
                                            let percent = Math.round(progress * 100);
                                            if (percent === 100) {
                                                livestream.uploading = false;
                                            }
                                            $('#site-wide-notification .message').html(language.phrase('uploading-stream') + ' ' + percent + '%' + ' ' + language.phrase('complete'));
                                        }
                                    }
                                }, false);
                                return xhr;
                            },
                            beforeSend: function () {
                                notify(language.phrase('uploading-stream'), 'warning', 3600000);
                                livestream.uploading = true;
                            },
                            success: function (response) {
                                let data = JSON.parse(response);
                                livestream.uploading = false;
                                livestream.endCallback(data);
                            },
                            error: function (error) {
                                notify(language.phrase('stream-upload-failed'), 'error');
                            }
                        });
                    }
                } else {
                    let modal = $('.livestream-modal');
                    modal.modal('hide');
                    modal.find('.modal-body').html('');
                    modal.data('url', '');
                    livestream.live.end();
                    livestream.init({destroy: true});
                }
                if (livestream.recordlimitInterval) {
                    clearInterval(livestream.recordlimitInterval);
                }
                if (livestream.keepAliveInterval) {
                    clearInterval(livestream.keepAliveInterval);
                }
                livestream.live.end();
            } else if (!livestream.id) {
                let modal = $('.livestream-modal');
                livestream.ended = true;
                modal.modal('hide');
                modal.find('.modal-body').html('');
                modal.data('url', '');
                livestream.live.end();
                livestream.init({destroy: true});
            }
        },

        endCallback: function (data) {
            closeNotify();
            livestream.uploading = false;
            livestream.status = data.status;
            notify(data.message, data.status === 3 ? 'success' : 'warning');
            if (data.link) {
                document.querySelectorAll(livestream.options.player).forEach(function(player) {
                    player.srcObject = undefined;
                    player.parentNode.replaceChild($(data.player).get(0), player);
                });
                $('.livestream-content .livestream-view').data('livestream-status', data.status);
                $('.livestream-content .livestream-stop-button').each(function(index, button) {
                    button.remove();
                });
                livestream.init({destroy: true});
                $('.livestream-content .viewers-count').remove();
                $('.livestream-content .time-elapsed').remove();
                $('.livestream-content .livestream-loading').removeClass('active');
                if (typeof reloadInits === 'function') {
                    reloadInits();
                }
            } else {
                let modal = $('.livestream-modal');
                modal.modal('hide');
                modal.data('url', '');
                livestream.init({destroy: true});
                loadPage(baseUrl + 'livestreams');
            }
        },

        keepAlive: function () {
            if (livestream.status === 1) {
                Pusher.sendMessage({
                    type: 'livestream.keep.alive',
                    livestream_id: livestream.id,
                    data: livestream.id
                }, 'type');
            }
        },

        startTimer: function () {
            livestream.timeUpdate = setInterval(function() {
                let timeElapsed = Math.ceil((Date.now() - livestream.startTime) / 1000);
                document.querySelector('.livestream-content .time-elapsed').innerHTML = ('00' + Math.floor(parseInt(timeElapsed, 10) / 60)).slice(-2) + ':' + ('00' + (parseInt(timeElapsed, 10) % 60)).slice(-2);
            }, 500);
        },

        stopTimer: function () {
            clearInterval(livestream.timeUpdate);
        },

        resetTimer: function () {
            document.querySelector('.livestream-content .time-elapsed').innerHTML = '00:00';
        },

        push: function (type, details) {
            if (type === 'livestream.ice.candidate') {
                for (let id in details) {
                    if (details.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
                        for (let index = 0; index < details[id].length; index++) {
                            if (details[id][index].livestream_id === livestream.id) {
                                if (!livestream.joinToken && typeof livestream.peerConnections[details[id][index].token] !== 'undefined') {
                                    if(livestream.peerConnections[details[id][index].token].remoteDescription) {
                                        console.debug(details[id][index].sender_id, 'ICE (' + (details[id][index].data.candidate.indexOf('relay') < 0 ? 'STUN' : 'TURN') + ') Received: ', new RTCIceCandidate(details[id][index].data));
                                        livestream.peerConnections[details[id][index].token].addIceCandidate(new RTCIceCandidate(details[id][index].data));
                                        Pusher.addPushId(id);
                                    }
                                } else if (details[id][index].token === livestream.joinToken) {
                                    if(livestream.peerConnection.remoteDescription) {
                                        console.debug(details[id][index].sender_id, 'ICE (' + (details[id][index].data.candidate.indexOf('relay') < 0 ? 'STUN' : 'TURN') + ') Received: ', new RTCIceCandidate(details[id][index].data));
                                        livestream.peerConnection.addIceCandidate(new RTCIceCandidate(details[id][index].data));
                                        Pusher.addPushId(id);
                                    }
                                }
                            }
                        }
                    }
                }
            } else if (type === 'livestream.session.description') {
                for (let id in details) {
                    if (details.hasOwnProperty(id) && !Pusher.hasPushId(id) && details[id].livestream_id === livestream.id && (Date.now() / 1000) - details[id].time < ((livestream.pollInterval / 1000) * 2) + 60) {
                        if (details[id].data.type === 'offer') {
                            livestream.accept(details[id]);
                        } else if (details[id].data.type === 'answer') {
                            livestream.peerConnection.setRemoteDescription(new RTCSessionDescription(details[id].data)).then(function() {
                            });
                        }
                        Pusher.addPushId(id);
                    }
                }
            } else if (type === 'livestream.member.count') {
                for (let id in details) {
                    if (details.hasOwnProperty(id) && !Pusher.hasPushId(id) && details[id].livestream_id === livestream.id) {
                        $('.livestream-content .viewers-count .count').html(details[id].count);
                        Pusher.addPushId(id);
                    }
                }
            } else if (type === 'livestream.widget.list.ongoing') {
                $('.livestream-content.livestream-widget.livestream-ongoing').html(details);
            } else if (type === 'livestream.comment') {
                for (let id in details) {
                    if (details.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
                        if (parseInt(details[id].livestream_id) === parseInt(livestream.id)) {
                            $('.livestream-content .right > .feed-footer .list').html(details[id].view);
                        }
                        Pusher.addPushId(id);
                    }
                }
            } else if (type === 'livestream.ended') {
                for (let id in details) {
                    if (details.hasOwnProperty(id) && !Pusher.hasPushId(id)) {
                        if (details[id].livestream_id === livestream.id && livestream.status === 1) {
                            if (livestream.joinToken) {
                                livestream.ended = true;
                                if (livestream.peerConnection) {
                                    livestream.peerConnection.close();
                                }
                                livestream.endCallback(details[id]);
                            } else if (livestream.ended) {
                                for (let i in livestream.peerConnections) {
                                    if (livestream.peerConnections.hasOwnProperty(i)) {
                                        livestream.peerConnections[i].close();
                                    }
                                }
                                livestream.endCallback(details[id]);
                            } else {
                                livestream.end();
                            }
                        }
                        Pusher.addPushId(id);
                    }
                }
            }
        }
    };

    $(function() {
        livestream.init();
    });
}