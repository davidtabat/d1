document.observe("dom:loaded", function() {

    if (typeof notificationCheckUrl !== 'undefined') {
        window.setInterval(checkTickets, notificationInterval);
    }

    function checkTickets() {
        new Ajax.Request(notificationCheckUrl, {
            method: 'get',
            onCreate: function(request) {
                Ajax.Responders.unregister(varienLoaderHandler.handler);
            },
            onSuccess: function (transport) {
                Ajax.Responders.register(varienLoaderHandler.handler);
                if (transport.responseText.isJSON()) {
                    var data = transport.responseText.evalJSON();
                    //notificationCheckUrl = data.url;
                    if (data.messages.length) {
                        for (i=0; i < data.messages.length; i++) {
                            notifyMe(data.messages[i]);
                        }
                    }

                    if (data['new_tickets_cnt']) {
                        $('desktop_notification_tickets').update(data['new_tickets_cnt']);
                    }
                    if (data['new_messages_cnt']) {
                        $('desktop_notification_messages').update(data['new_messages_cnt']);
                    }
                }
            }
        });
    }

    function notifyMe(message) {
        // Let's check if the browser supports notifications
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification");
        }

        // Let's check whether notification permissions have already been granted
        else if (Notification.permission === "granted") {
            // If it's okay let's create a notification
            showNotification(message);
        }

        // Otherwise, we need to ask the user for permission
        else if (Notification.permission !== 'denied') {
            Notification.requestPermission(function (permission) {
                // If the user accepts, let's create a notification
                if (permission === "granted") {
                    showNotification(message);
                }
            });
        }
    }

    function showNotification(message) {
        if (typeof message.title != 'undefined') {
            //var notification = new Notification(message.title, {
            //    'body': message.message,
            //    'icon': notificationIcon,
            //});

            var notification = new Notification(message.message, {
                //'body': message.message,
                'icon': notificationIcon,
            });
            notification.onclick = function (event) {
                event.preventDefault();
                window.open(message.url, '_blank');
            }
        }
    }
});