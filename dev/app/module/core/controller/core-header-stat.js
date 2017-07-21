define([
    'text!tpl/message/conversation-popover-list.html',
    'text!tpl/notification/notification-popover-list.html',
    'text!tpl/request/request-popover-list.html'
], function(messageListTpl, notificationListTpl, requestListTpl) {
    return function($scope, $rootScope, $site, $modal, gettext, gettextCatalog, $http2, $viewer, $ionicPopover, $interval, $globalData) {

        $scope.showRequest = function($event) {

            $scope.requestPopover && $scope.requestPopover.remove();

            $scope.requestPopover = $ionicPopover.fromTemplate(requestListTpl, {
                scope: $scope
            });

            $scope.requestPopover.show($event);
        };

        $scope.showMessage = function($event) {

            $scope.messagePopover && $scope.messagePopover.remove();

            $scope.messagePopover = $ionicPopover.fromTemplate(messageListTpl, {
                scope: $scope
            });

            $scope.messagePopover.show($event);
        };

        $scope.showNotification = function($event) {

            $scope.notificationPopover && $scope.notificationPopover.remove();

            $scope.notificationPopover = $ionicPopover.fromTemplate(notificationListTpl, {
                scope: $scope
            });

            $scope.notificationPopover.show($event);
        };

        $scope.markReadAllNotification = function() {

            if ($scope.isProcessingMarkRead) {
                return;
            }

            $scope.isProcessingMarkRead = true;

            $http2.post('notification/markreadall')
            .success($scope.markReadSuccess)
            .error($scope.markReadError)
            .finally(function() {
                $scope.isProcessingMarkRead = false;
            });
        };

        $scope.markReadSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.notificationPopover.remove();
            $scope.stat.totalNotification = 0;
            $globalData.set('stat', $scope.stat);
        };

        $scope.markReadError = function() {

            console.warn('markReadAllNotificationError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.onGetStat = function() {
            
            $scope.stat = $globalData.get('stat'); // get cached data
            $scope.getStat(); // get new data
            $scope.interval = $interval($scope.getStat, 30e3); // get by interval
        };

        $scope.getStat = function() {

            $http2.post('notification/update')
            .success($scope.getStatSuccess)
            .error(function(){})
            .finally(function(){});
        };

        $scope.getStatSuccess = function(data) {

            if (data.error_code) {
                return; // skip error messages for interval
            }

            $scope.stat = {
                totalRequest: data.iNumberOfFriendRequest,
                totalMessage: data.iNumberOfMessage,
                totalNotification: data.iNumberNotification
            };

            $globalData.set('stat', $scope.stat);
        };

        $scope.onGetStat();

        $scope.$on('notification:new', $scope.getStat);

        $scope.$on('$destroy', function() {
            $interval.cancel($scope.interval);
        });

        $scope.$on('$stateChangeSuccess', function() {
            $scope.requestPopover && $scope.requestPopover.remove();
            $scope.messagePopover && $scope.messagePopover.remove();
            $scope.notificationPopover && $scope.notificationPopover.remove();
        });
    };
});