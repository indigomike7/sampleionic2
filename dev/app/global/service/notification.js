define([
    'notification/model/notification'
], function(NotificationModel) {

    return function($rootScope, $http2, $site, $location, $coreSettings, $state) {

        var $notification = {
            storage: {}
        };

        $notification.doRegistrationWithAPNs = function() {
            
            var apnData = {
                "badge": "true",
                "sound": "true",
                "alert": "true",
                "ecb": "onAPNsReceiver"
            };

            var registerSuccess = function(result) {
                console.log('doRegistrationWithAPNs', JSON.stringify(result));
                var sendData =  {
                    sDeviceId: result,
                    sPlatform: ionic.Platform.isIPad() ? 'ipad' : 'ios'
                };
                $http2.post('device/register', sendData)
                .success(function() {
                    console.log('device/register', JSON.stringify(arguments));
                })
                .error(function() {
                    console.warn('device/register', JSON.stringify(arguments));
                });
            };

            var registerFail = function() {
                console.warn('doRegistrationWithAPNs', JSON.stringify(arguments));
            };

            try {
                var pushNotification = window.plugins.pushNotification;
                pushNotification.register(registerSuccess, registerFail, apnData);
            } catch (err) {
                console.error('doRegistrationWithAPNs', JSON.stringify(err));
            }
        };

        $notification.doRegistrationWithGCM = function() {

            var gcmData = {
                "senderID": $site.googleCloudMessageSenderId, // Project Number
                "ecb": "onGCMReceiver"
            };

            var registerSuccess = function() {
                console.log('doRegistrationWithGCM', JSON.stringify(arguments));
            };

            var registerFail = function() {
                console.warn('doRegistrationWithGCM', JSON.stringify(arguments));
            };

            try {
                var pushNotification = window.plugins.pushNotification;
                pushNotification.register(registerSuccess, registerFail, gcmData);
            } catch (err) {
                console.error('doRegistrationWithGCM', JSON.stringify(err));
            }
        };

        /*
         *	The function is actived whenever phone receives a push message from server
         */
        $notification.onAPNsReceiver = function(payload) {

            console.log('onAPNsReceiver', JSON.stringify(payload));
            
            $rootScope.$broadcast('notification:new', payload);

            if (payload.foreground == 0) {
                $notification.gotoDetailPage(payload);
            }
        };

        $notification.onGCMReceiver = function(e) {

            console.log('onGCMReceiver', JSON.stringify(e));
            
            switch (e.event) {
                case 'registered':
                    if (e.regid.length > 0) {
                        // Your GCM push server needs to know the regID before it can push to this device
                        // here is where you might want to send it the regID for later use.
                        var sendData =  {
                            sDeviceId: e.regid,
                            sPlatform: 'android'
                        };
                        
                        $http2.post('device/register', sendData).success(function() {
                            console.log('device/register', JSON.stringify(arguments));
                        }).error(function() {
                            console.warn('device/register', JSON.stringify(arguments));
                        });
                    }
                    break;

                case 'message':
                    $rootScope.$broadcast('notification:new', e.payload);

                    // if this flag is set, this notification happened while we were in the foreground.
                    // you might want to play a sound to get the user's attention, throw up a dialog, etc.
                    if (e.foreground) {
                        // if the notification contains a soundname, play it.
                        // var my_media = new Media("/android_asset/www/"+e.soundname);
                        // my_media.play();
                    } else {
                        // otherwise we were launched because the user touched a notification in the notification tray.
                        if (e.coldstart) {
                            console.log('onGCMReceiver: COLDSTART NOTIFICATION');
                            $notification.storage.payload = e.payload;
                        } else {
                            $notification.gotoDetailPage(e.payload);
                        }
                    }
                    break;

                case 'error':
                    break;

                default:
                    console.log('onGCMReceiver: Unknown, an event was received and we do not know what it is');
                    break;
            };
        };

        /*
         *	Get the detail of Notification thereafter forward to detail of feed/object page.
         */
        $notification.gotoDetailPage = function(payload) {

            if (payload.sType == 'notification') {
                var postData = {
                    iNotificationId: payload.iId
                };

                var success = function(data) {
                    if (data.error_code) {
                        return console.warn('notification/detail', JSON.stringify(data));
                    }
                    var item = $.extend({}, NotificationModel, data);
                    $location.path(item.getPath());
                    $rootScope.$$phase || $rootScope.$apply();
                };

                var error = function() {
                    console.warn('notification/detail', JSON.stringify(arguments));
                };

                $http2.get('notification/detail', postData).success(success).error(error);
            } else if (payload.sType == 'chat') {
                var path = 'app/' + $coreSettings.get('chat_module') + '/' + payload.iId;
                
                if ($state.current.name == 'app.init') {
                    return $site.delayPath = path; // will call in end of app.init state
                }

                $location.path(path);
                $rootScope.$$phase || $rootScope.$apply();
            }
        };

        window.onAPNsReceiver = function() {
            console.log('onAPNsReceiver', JSON.stringify(arguments));
            $notification.onAPNsReceiver.apply($notification, arguments);
        };

        window.onGCMReceiver = function() {
            $notification.onGCMReceiver.apply($notification, arguments);
        };

        $notification.checkDelayedCalls = function() {
            if ($notification.storage.payload) {
                $notification.gotoDetailPage($notification.storage.payload);
                $notification.storage.payload = null;
            }
        };

        $rootScope.$on('coreSettings:update', $notification.checkDelayedCalls);

        return $notification;
    };
});