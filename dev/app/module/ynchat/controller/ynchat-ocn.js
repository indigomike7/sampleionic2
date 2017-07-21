define([
    'ynchat/model/ynchat-message'
], function(YnchatMessageModel) {

    return function($scope, $state, $interval, $http2, $ynchat,
        $ynchatWebsocket, $location, $ionicSideMenuDelegate, $ionicViewSwitcher) {

        $scope.unreadIds = $ynchat.getUnreads();
        $scope.isMuteNotification = $ynchat.isMuteNotification();
        $scope.isHideOCN = ((ionic.Platform.isIPad() && $state.current.name == 'app.ynchat') ||
            $state.current.name == 'app.ynchatid' ||
            $state.current.name == 'app.ynchatidhistory');

        $scope.updateUnread = function(e) {

            $scope.unreadIds = $ynchat.getUnreads();
            $scope.$$phase || $scope.$apply();

            // fix render issue on iOS 9
            if (!$scope.isMuteNotification && !$scope.isHideOCN) {
                angular.element('.chat-notification').hide().show(0);
            }
        };

        $scope.updateMuteNotification = function(e, bool) {

            $scope.isMuteNotification = bool;
        };

        $scope.updateHideOCN = function(event, toState, toParams, fromState, fromParams) {

            $scope.isHideOCN = ((ionic.Platform.isIPad() && toState.name == 'app.ynchat') ||
                toState.name == 'app.ynchatid' ||
                toState.name == 'app.ynchatidhistory');
        };

        $scope.goToYnchat = function() {

            // direct user to the detailed chat screen of the latest conversation or chat home
            var length = $scope.unreadIds.length;
            if (length) {
                $ionicSideMenuDelegate.toggleRight(false);
                $ionicViewSwitcher.nextTransition('none');
                $location.path('app/ynchat/' + $scope.unreadIds[length - 1]);
            } else {
                $ionicSideMenuDelegate.toggleRight();
            }
        };

        $scope.onWebsocketOpen = function() {

            $scope.registerAgent();

            if (angular.isDefined($scope.intervalRegisterAgent)) {
                $interval.cancel($scope.intervalRegisterAgent);
            }

            $scope.intervalRegisterAgent = $interval($scope.registerAgent, 5 * 60e3);
        };

        $scope.registerAgent = function() {

            var successCb = function() {
                console.log('ynchat/update_agent', arguments);
            };

            var errorCb = function() {
                console.warn('ynchat/update_agent', arguments);
            };

            $http2.post('ynchat/update_agent').success(successCb).error(errorCb);
        };

        $ynchat.updateConfig();

        $scope.$on('ynchatWebsocket:open', $scope.onWebsocketOpen);
        $scope.$on('ynchat:updateUnread', $scope.updateUnread);
        $scope.$on('ynchat:updateMuteNotification', $scope.updateMuteNotification);
        $scope.$on('$stateChangeStart', $scope.updateHideOCN);
    };
});