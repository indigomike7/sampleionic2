define(function() {

    return function($rootScope, $ynchat, $site, $interval, $viewer, $modal, $state) {

        var $ynchatWebsocket = {};
        var callbacks = {};
        var conn;

        $ynchatWebsocket.bind = function(event_name, callback) {

            callbacks[event_name] = callbacks[event_name] || [];
            callbacks[event_name].push(callback);
            return $ynchatWebsocket; // chainable
        };

        $ynchatWebsocket.send = function(event_name, event_data) {

            $ynchatWebsocket.conn.send(event_data);
        };

        $ynchatWebsocket.connect = function() {

            var WSUrl = $ynchat.getWSUrl();

            if (typeof(MozWebSocket) == 'function') {
                $ynchatWebsocket.conn = new MozWebSocket(WSUrl);
            } else {
                $ynchatWebsocket.conn = new WebSocket(WSUrl);
            }

            $ynchatWebsocket.conn.onmessage = function(evt) {

                console.log('$ynchatWebsocket.conn.onmessage', arguments);

                var oData = JSON.parse(evt.data);

                var action = null;
                if (undefined != oData.action && null != oData.action && oData.action.length > 0) {
                    action = oData.action;
                }

                var method = null;
                if (undefined != oData.method && null != oData.method && oData.method.length > 0) {
                    method = oData.method;
                }

                var data = null;
                if (undefined != oData.data && null != oData.data) {
                    data = oData.data;
                }

                switch (action) {
                    case 'echo':
                        if (!method || !$ynchatWebsocket.hasOwnProperty(method)) {
                            console.log('method not supported');
                        } else {
                            $ynchatWebsocket[method](data);
                        }
                        break;
                    default:
                        break;
                }
            };

            $ynchatWebsocket.conn.onclose = function() {

                $rootScope.$broadcast('ynchatWebsocket:close');

                $ynchatWebsocket.tryConnect();
            };

            $ynchatWebsocket.conn.onerror = function() {
                
                $rootScope.$broadcast('ynchatWebsocket:error');

                $ynchatWebsocket.tryConnect();
            };

            $ynchatWebsocket.conn.onopen = function() {

                if ($ynchatWebsocket.interval) {
                    $interval.cancel($ynchatWebsocket.interval);
                    delete($ynchatWebsocket.interval);
                }

                var sendData = {
                    action: 'authConnectionWithPlatform', 
                    data: {
                        sUserIdHash: $ynchat.getConfig('sUserIdHash')
                    }
                };

                $ynchatWebsocket.send('message', JSON.stringify(sendData));

                $rootScope.$broadcast('ynchatWebsocket:open');
            };
        };

        $ynchatWebsocket.disconnect = function() {

            if ($ynchatWebsocket.conn != null) {
                $ynchatWebsocket.conn.close();
                $ynchatWebsocket.conn = null;
            }
        };

        $ynchatWebsocket.reconnect = function() {

            $ynchatWebsocket.disconnect();
            $ynchatWebsocket.connect();
        };

        $ynchatWebsocket.tryConnect = function() {

            $ynchatWebsocket.interval || ($ynchatWebsocket.interval = $interval($ynchatWebsocket.reconnect, 5e3));
        };

        $ynchatWebsocket.isOpen = function() {

            if (!$ynchatWebsocket.conn || $ynchatWebsocket.conn.readyState != 1) {
                return false;
            }

            return true;
        };

        /**
         * Method defined by server side
         */
        $ynchatWebsocket.authConnectionWithPlatformRes = function(data) {
            
            $rootScope.$broadcast('ynchatWebsocket:authenticated', data);
        };

        /**
         * Method defined by server side
         */
        $ynchatWebsocket.sendMessageAsTextRes = function(data) {

            if (data.iSenderId != $viewer.get('iUserId')) {
                $ynchat.setUnread(data.iSenderId);
                if ($ynchat.getVibrateStatus() == 'on' && !window.isInBackground && ($state.current.module == 'ynchat' || !$ynchat.isMuteNotification())) {
                    $modal.vibrate(100);
                }
            }

            $rootScope.$broadcast('ynchatWebsocket:newTextMessage', data);
        };

        /**
         * Method defined by server side
         */
        $ynchatWebsocket.sendMessageWithFilesRes = function(data) {

            if (data.iSenderId != $viewer.get('iUserId')) {
                $ynchat.setUnread(data.iSenderId);
                if ($ynchat.getVibrateStatus() == 'on' && !window.isInBackground && ($state.current.module == 'ynchat' || !$ynchat.isMuteNotification())) {
                    $modal.vibrate(100);
                }
            }

            $rootScope.$broadcast('ynchatWebsocket:newFileMessage', data);
        };

        $rootScope.$on('ynchat:updateConfig', function() {
            $ynchatWebsocket.tryConnect();
        });

        return $ynchatWebsocket;
    };
});