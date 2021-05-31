pusher = {
    driver: 'ajax',

    init: function() {
        if(typeof pushDriver !== 'undefined') {
            pusher.driver = pushDriver;
        }
        for(let i in pusher.drivers) {
            pusher.drivers[i].init();
        }
    },

    onMessage: function(data) {
        if(data) {
            let notifications = data.notifications;
            if(notifications) {
                self.clients.matchAll({includeUncontrolled: true, type: 'window'}).then(function(clients) {
                    let tabActive = false;
                    clients.forEach(function(client) {
                        if(client.visibilityState === 'visible') {
                            tabActive = true;
                        }
                    });
                    // console.log(tabActive, notifications);
                    for(let i in notifications) {
                        if(notifications.hasOwnProperty(i) && notifications[i].status && notifications[i].options.title.length && (!tabActive || notifications[i].showInForeground)) {
                            notification.pop.notify(notifications[i].options);
                        }
                    }
                });
            }
        }
    },

    drivers: {
        poll: {
            init: function() {
                if(pusher.driver === 'ajax' && loggedIn) {
                    pusher.drivers.poll.check();
                }
            },

            check: function() {
                let headers = new Headers();
                headers.append('pragma', 'no-cache');
                headers.append('cache-control', 'no-cache');
                fetch('./ajax/push/check?csrf_token=' + requestToken + '&sw=1', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: headers
                }).then(function(response) {
                    if (response.status === 200) {
                        response.json().then(function(data) {
                            //console.log('AJAX Push Message Arrive in SW');
                            pusher.onMessage(data);
                            setTimeout(function() {
                                if(loggedIn) {
                                    pusher.drivers.poll.check()
                                }
                            }, ajaxInterval);
                        }).catch(function() {
                            setTimeout(function() {
                                if (loggedIn) {
                                    pusher.drivers.poll.check()
                                }
                            }, ajaxInterval);
                        });
                    } else {
                        setTimeout(function() {
                            if(loggedIn) {
                                pusher.drivers.poll.check()
                            }
                        }, ajaxInterval);
                    }
                }).catch(function(error) {
                    setTimeout(function() {
                        if (loggedIn) {
                            pusher.drivers.poll.check()
                        }
                    }, ajaxInterval);
                });
            }
        },

        FCM: {
            messaging: null,
            token: null,
            permission: false,

            init: function() {
                if(typeof firebase === 'undefined') {
                    return;
                }
                let config = {
                    apiKey: firebaseAPIKey,
                    authDomain: firebaseAuthDomain,
                    databaseURL: firebaseDatabaseURL,
                    projectId: firebaseProjectId,
                    storageBucket: firebaseStorageBucket,
                    messagingSenderId: firebaseMessagingSenderId
                };
                if(!(config.messagingSenderId + '').length) {
                    return false;
                }
                firebase.initializeApp(config);
                pusher.drivers.FCM.messaging = firebase.messaging();
                pusher.drivers.FCM.permission = true;
                pusher.drivers.FCM.setToken();
                pusher.drivers.FCM.messaging.setBackgroundMessageHandler(function(payload) {
                    self.clients.matchAll({includeUncontrolled: true, type: 'window'}).then(function(clients) {
                        clients.forEach(function(client) {
                            client.postMessage(payload);
                        });
                    });
                    let data = JSON.parse(payload.data.pushes);
                    pusher.onMessage(data);
                });
            },

            setToken: function(token) {
                if(token) {
                    pusher.drivers.FCM.token = token;
                    pusher.drivers.FCM.updateServerToken();
                } else {
                    pusher.drivers.FCM.messaging.getToken().then(function(token) {
                        if(token) {
                            pusher.drivers.FCM.token = token;
                            pusher.drivers.FCM.updateServerToken();
                        } else {
                            console.log('No Instance ID token available. Request permission to generate one.');
                        }
                    }).catch(function(error) {
                        if(error.code === 'messaging/notifications-blocked') {
                            console.log('Service Worker unable to get FCM permission to notify. Falling back to AJAX Polling');
                            pusher.driver = 'ajax';
                            pusher.drivers.poll.init();
                        } else {
                            console.log('An error occurred while retrieving token. ', error);
                        }
                    });
                }
            },

            updateServerToken: function(token) {
                token = token || pusher.drivers.FCM.token;
                fetch('./fcm/token/update?token=' + token + '&csrf_token=' + requestToken, {
                    method: 'GET',
                    credentials: 'same-origin'
                });
            }
        }
    }
};

self.addEventListener('activate', function(event) {
    if(enableBackgroundNotification) {
        pusher.init();
    }
});