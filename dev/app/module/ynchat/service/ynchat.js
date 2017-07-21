define([
    'ynchat/model/ynchat-message'
],function(YnchatMessageModel) {

    return function($rootScope, $interval, $http2, $site, $viewer, $state, $globalData, $timeout) {

        var $ynchat = {
            storage: {
                currentId: null, // current ynchatting
                muteNotification: true
            },
            config: {},
            usersettings: {}
        };

        // reset data
        localStorage.removeItem('ynchatUnreadIds');

        $ynchat.setUnread = function(id) {

            id = parseInt(id);

            if ($ynchat.isCurrent(id)) {
                return false;
            }

            var unreadIds = JSON.parse(localStorage.getItem('ynchatUnreadIds')) || [];

            if (unreadIds.indexOf(id) == -1) {
                unreadIds.push(id);
            }

            localStorage.setItem('ynchatUnreadIds', JSON.stringify(unreadIds));

            $rootScope.$broadcast('ynchat:updateUnread', unreadIds);
        };

        $ynchat.removeUnread = function(id) {

            id = parseInt(id);

            var unreadIds = JSON.parse(localStorage.getItem('ynchatUnreadIds')) || [];

            if (unreadIds.indexOf(id) > -1) {
                unreadIds.splice(unreadIds.indexOf(id), 1);
            }

            localStorage.setItem('ynchatUnreadIds', JSON.stringify(unreadIds));

            $rootScope.$broadcast('ynchat:updateUnread', unreadIds);
        };

        $ynchat.getUnreads = function() {

            return JSON.parse(localStorage.getItem('ynchatUnreadIds')) || [];
        };

        $ynchat.setCurrent = function(id) {

            id = parseInt(id);

            $ynchat.storage.currentId = id;
            $ynchat.removeUnread(id);
        };

        $ynchat.removeCurrent = function() {

            delete($ynchat.storage.currentId);
        };

        $ynchat.isCurrent = function(id) {

            return $ynchat.storage.currentId == id;
        };

        $ynchat.setMuteNotification = function(bool) {

            $ynchat.storage.muteNotification = bool;

            $rootScope.$broadcast('ynchat:updateMuteNotification', bool);
        };

        $ynchat.isMuteNotification = function() {

            return $ynchat.storage.muteNotification;
        };

        $ynchat.setVibrateStatus = function(status) {

            localStorage.setItem('ynchatVibrateStatus', status || 'on');
        };

        $ynchat.getVibrateStatus = function() {

            return localStorage.getItem('ynchatVibrateStatus') || 'on';
        };

        $ynchat.updateConfig = function() {

            if (!$viewer.get('iUserId')) {
                return $timeout($ynchat.updateConfig, 5e3);
            }

            var successCb = function(data) {
                
                if (data.error_code) {
                    return $timeout($ynchat.updateConfig, 5e3);
                }
                
                // save config
                $ynchat.config = data.config;
                $ynchat.usersettings = data.usersettings;

                $rootScope.$broadcast('ynchat:updateConfig');
            };

            var errorCb = function() {
                
                console.warn('ynchat/getConfig', arguments);
                $timeout($ynchat.updateConfig, 5e3);
            };

            $http2.post('ynchat/getConfig', {user_id: $viewer.get('iUserId')}).success(successCb).error(errorCb);
        };

        $ynchat.getConfig = function(key) {

            return key ? $ynchat.config[key] : $ynchat.config;
        };

        $ynchat.getUserSetting = function(key) {

            return key ? $ynchat.usersettings[key] : $ynchat.usersettings;
        };

        $ynchat.setUserSetting = function(object) {

            $.extend($ynchat.usersettings, object);
        };

        $ynchat.getFileUrl = function(fileId) {

            return $site.getApiUrl('ynchat/download?id=' + fileId);
        };

        $ynchat.updateSettings = function() {

            var successCb = function(data) {
                
                if (data.error_code) {
                    return console.warn('ynchat/pingStatus', arguments);
                }

                $ynchat.setUserSetting(data);

                $rootScope.$broadcast('ynchat:updateSettings');
            };

            var errorCb = function() {
                
                console.warn('ynchat/pingStatus', arguments);
            };

            $http2.post('ynchat/pingStatus').success(successCb).error(errorCb);
        };

        $ynchat.getWSUrl = function() {

            var siteHost = document.createElement('a');
            siteHost.href = $site.siteHost;

            var iPort = $ynchat.getConfig('iPort') || 9009;
            var iStunnelPort = $ynchat.getConfig('iStunnelPort') || 9010;
            var sServerUrl = $ynchat.getConfig('sServerUrl');
            var sIpPublic = $ynchat.getConfig('sIpPublic');

            var WSProtocol = siteHost.protocol == 'https:' ? 'wss:' : 'ws:';
            var WSPort = WSProtocol == 'wss:' ? iStunnelPort : iPort;
            var WSHost = !sIpPublic ? sServerUrl : sIpPublic;
            var WSUrl = WSProtocol + '//' + WSHost + ':' + WSPort + '/chat';

            return WSUrl;
        };

        return $ynchat;
    };
});