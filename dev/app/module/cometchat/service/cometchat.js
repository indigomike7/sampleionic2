define([
    'cometchat/model/cometchat-message'
],function(CometchatMessageModel) {

    return function($rootScope, $interval, $http, $site, $viewer, $state, $coreSettings, $modal) {

        var $cometchat = {
            storage: {
                currentId: null, // current cometchatting
                lastPingTimeStamp: null,
                muteNotification: true,
                status: 'available'
            }
        };

        // reset data
        localStorage.removeItem('cometchatUnreadIds');

        $cometchat.setUnread = function(id) {

            id = parseInt(id);

            if ($cometchat.isCurrent(id)) {
                return false;
            }

            var unreadIds = JSON.parse(localStorage.getItem('cometchatUnreadIds')) || [];

            if (unreadIds.indexOf(id) == -1) {
                unreadIds.push(id);
            }

            localStorage.setItem('cometchatUnreadIds', JSON.stringify(unreadIds));

            $rootScope.$broadcast('cometchat:updateUnread', unreadIds);
        };

        $cometchat.removeUnread = function(id) {

            id = parseInt(id);

            var unreadIds = JSON.parse(localStorage.getItem('cometchatUnreadIds')) || [];

            if (unreadIds.indexOf(id) > -1) {
                unreadIds.splice(unreadIds.indexOf(id), 1);
            }

            localStorage.setItem('cometchatUnreadIds', JSON.stringify(unreadIds));

            $rootScope.$broadcast('cometchat:updateUnread', unreadIds);
        };

        $cometchat.getUnreads = function() {

            return JSON.parse(localStorage.getItem('cometchatUnreadIds')) || [];
        };

        $cometchat.setCurrent = function(id) {

            id = parseInt(id);

            $cometchat.storage.currentId = id;
            $cometchat.removeUnread(id);
        };

        $cometchat.removeCurrent = function() {

            delete($cometchat.storage.currentId);
        };

        $cometchat.isCurrent = function(id) {

            return $cometchat.storage.currentId == id;
        };

        $cometchat.setMuteNotification = function(bool) {

            $cometchat.storage.muteNotification = bool;

            $rootScope.$broadcast('cometchat:updateMuteNotification', bool);
        };

        $cometchat.isMuteNotification = function() {

            return $cometchat.storage.muteNotification;
        };

        $cometchat.setVibrateStatus = function(status) {

            localStorage.setItem('cometchatVibrateStatus', status || 'on');
        };

        $cometchat.getVibrateStatus = function() {

            return localStorage.getItem('cometchatVibrateStatus') || 'on';
        };

        $cometchat.setStatus = function(sStatus) {

            $cometchat.storage.status = sStatus;
        };

        $cometchat.getStatus = function() {

            return $cometchat.storage.status;
        };

        $cometchat.setLastPingTimestamp = function(timestamp) {

            $cometchat.storage.lastPingTimeStamp = timestamp;
        };

        $cometchat.getLastPingTimestamp = function() {

            return $cometchat.storage.lastPingTimeStamp;
        };

        $cometchat.startPing = function(delay) {

            if ($cometchat.pingPromise) {
                return console.log('pinging');
            }

            delay = delay || 5e3;

            $cometchat.pingPromise = $interval($cometchat.ping, delay);
        };

        $cometchat.stopPing = function() {

            return $interval.cancel($cometchat.pingPromise);
        };

        $cometchat.ping = function() {

            // skip in cases
            if ($coreSettings.get('chat_module') != 'cometchat' 
            || ($state.current.name != 'app.cometchat' && $state.current.name != 'app.cometchatid' && $cometchat.isMuteNotification())
            || ionic.isWebViewDetached) {
                return;
            }

            var postData = {
                iGetNewMessages: 1,
                iLastTimeStamp: $cometchat.getLastPingTimestamp(),
                user_id: $viewer.get('iUserId')
            };

            $http.post($site.getCometchatApiUrl('chat/ping'), postData).success($cometchat.pingSuccess).error($cometchat.pingError);
        };

        $cometchat.pingSuccess = function(data) {

            if (data.error_code) {
                return console.warn('pingSuccess', data);
            }

            $cometchat.setLastPingTimestamp(data.iLastTimeStamp);

            var newItems = data.aNewMessages.map(function(item) {
                return $.extend({}, CometchatMessageModel, item);
            });

            var vibrate = false;

            for (var i = 0; i < newItems.length; i++) {
                var sender = parseInt(newItems[i].getSenderId());
                if (sender != $viewer.get('iUserId')) {
                    $cometchat.setUnread(sender);
                    vibrate = true;
                }
            }

            $rootScope.$broadcast('cometchat:ping', newItems);
            
            if (vibrate && $cometchat.getVibrateStatus() == 'on' && !window.isInBackground) {
                $modal.vibrate(100);
            }
        };

        $cometchat.pingError = function() {

            console.warn('pingError', arguments);
        };

        return $cometchat;
    };
});