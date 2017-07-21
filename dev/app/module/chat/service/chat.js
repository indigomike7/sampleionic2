define([
    'chat/model/chat-message'
],function(ChatMessageModel) {

    return function($rootScope, $interval, $http2, $site, $viewer, $state, $coreSettings, $modal) {

        var $chat = {
            storage: {
                currentId: null, // current chatting
                lastPingTimeStamp: null,
                muteNotification: true
            }
        };

        // reset data
        localStorage.removeItem('chatUnreadIds');

        $chat.setUnread = function(id) {

            id = parseInt(id);

            if ($chat.isCurrent(id)) {
                return false;
            }

            var unreadIds = JSON.parse(localStorage.getItem('chatUnreadIds')) || [];

            if (unreadIds.indexOf(id) == -1) {
                unreadIds.push(id);
            }

            localStorage.setItem('chatUnreadIds', JSON.stringify(unreadIds));

            $rootScope.$broadcast('chat:updateUnread', unreadIds);
        };

        $chat.removeUnread = function(id) {

            id = parseInt(id);

            var unreadIds = JSON.parse(localStorage.getItem('chatUnreadIds')) || [];

            if (unreadIds.indexOf(id) > -1) {
                unreadIds.splice(unreadIds.indexOf(id), 1);
            }

            localStorage.setItem('chatUnreadIds', JSON.stringify(unreadIds));

            $rootScope.$broadcast('chat:updateUnread', unreadIds);
        };

        $chat.getUnreads = function() {

            return JSON.parse(localStorage.getItem('chatUnreadIds')) || [];
        };

        $chat.setCurrent = function(id) {

            id = parseInt(id);

            $chat.storage.currentId = id;
            $chat.removeUnread(id);
        };

        $chat.removeCurrent = function() {

            delete($chat.storage.currentId);
        };

        $chat.isCurrent = function(id) {

            return $chat.storage.currentId == id;
        };

        $chat.setMuteNotification = function(bool) {

            $chat.storage.muteNotification = bool;

            $rootScope.$broadcast('chat:updateMuteNotification', bool);
        };

        $chat.isMuteNotification = function() {

            return $chat.storage.muteNotification;
        };

        $chat.setVibrateStatus = function(status) {

            localStorage.setItem('chatVibrateStatus', status || 'on');
        };

        $chat.getVibrateStatus = function() {

            return localStorage.getItem('chatVibrateStatus') || 'on';
        };

        $chat.setLastPingTimestamp = function(timestamp) {

            $chat.storage.lastPingTimeStamp = timestamp;
        };

        $chat.getLastPingTimestamp = function() {

            return $chat.storage.lastPingTimeStamp;
        };

        $chat.startPing = function(delay) {

            if ($chat.pingPromise) {
                return console.log('pinging');
            }

            delay = delay || 5e3;

            $chat.pingPromise = $interval($chat.ping, delay);
        };

        $chat.stopPing = function() {

            return $interval.cancel($chat.pingPromise);
        };

        $chat.ping = function() {

            // skip in cases
            if ($coreSettings.get('chat_module') != 'chat' 
            || ($state.current.name != 'app.chat' && $state.current.name != 'app.chatid' && $chat.isMuteNotification())
            || ionic.isWebViewDetached) {
                return;
            }

            var postData = {
                iGetNewMessages: 1,
                iLastTimeStamp: $chat.getLastPingTimestamp(),
                user_id: $viewer.get('iUserId')
            };

            $http2.post('chat/ping', postData)
            .success($chat.pingSuccess)
            .error($chat.pingError);
        };

        $chat.pingSuccess = function(data) {

            if (data.error_code) {
                return console.warn('pingSuccess', data);
            }

            $chat.setLastPingTimestamp(data.iLastTimeStamp);

            var newItems = data.aNewMessages.map(function(item) {
                return $.extend({}, ChatMessageModel, item);
            });

            var vibrate = false;

            for (var i = 0; i < newItems.length; i++) {
                var sender = parseInt(newItems[i].getSenderId());
                if (sender != $viewer.get('iUserId')) {
                    $chat.setUnread(sender);
                    vibrate = true;
                }
            }

            $rootScope.$broadcast('chat:ping', newItems);
            
            if (vibrate && $chat.getVibrateStatus() == 'on' && !window.isInBackground) {
                $modal.vibrate(100);
            }
        };

        $chat.pingError = function() {

            console.warn('pingError', arguments);
        };

        return $chat;
    };
});