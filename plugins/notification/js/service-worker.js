notification = {
    init: function() {

    },

    push: function(type, details){

    },

    pop: {
        options: {
            title: '',
            body: '',
            icon: '',
            link: '',
            direction: '',
            vibrate: [200, 100, 200, 100, 200, 100, 200],
            tag: '',
        },

        set: function (options) {
            notification.pop.options = options;
        },

        create: function (options) {
            if (options) {
                notification.pop.set(options);
            }
            return self.registration.showNotification(notification.pop.options.title, notification.pop.options);
        },

        notify: function (options) {
            if (options) {
                notification.pop.set(options);
            }
            if (typeof Notification === 'undefined') {
                console.log('This browser does not support push notification');
            } else if (Notification.permission === "granted") {
                notification.pop.create();
            } else if (Notification.permission !== 'denied') {
                if (!('permission' in Notification)) {
                    Notification.permission = permission;
                }
                if (permission === 'granted') {
                    notification.pop.create();
                }
            }
        }
    }
};

self.addEventListener('notificationclick', function(event) {
    let url = event.currentTarget.notification.pop.options.link.trim();
    url = url ? url : baseUrl;
    event.notification.close();
    if(url) {
        event.waitUntil(
            clients.matchAll({type: 'window'}).then(function(windowClients) {
                for (let i = 0; i < windowClients.length; i++) {
                    let client = windowClients[i];
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
        );
    }
});