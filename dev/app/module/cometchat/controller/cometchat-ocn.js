define([
    'cometchat/model/cometchat-message'
], function(CometchatMessageModel) {

    return function($scope, $rootScope, $http, $site, gettext, gettextCatalog,
        $cometchat, $viewer, $location, $timeout, $ionicSideMenuDelegate,
        $ionicViewSwitcher) {

        $scope.unreadIds = $cometchat.getUnreads();
        $scope.isMuteNotification = $cometchat.isMuteNotification();

        $scope.updateUnread = function(e, newMessages) {

            $scope.unreadIds = $cometchat.getUnreads();

            // fix render issue on iOS 9
            if (!$scope.isMuteNotification && !$scope.isHideOCN) {
                angular.element('.chat-notification').hide().show(0);
            }
        };

        $scope.updateMuteNotification = function(e, bool) {

            $scope.isMuteNotification = bool;
        };

        $scope.goToCometchat = function() {

            // direct user to the detailed chat screen of the latest conversation or chat home
            var length = $scope.unreadIds.length;
            if (length) {
                if (ionic.Platform.isIPad()) {
                    $ionicSideMenuDelegate.toggleRight(false);
                    $ionicViewSwitcher.nextTransition('none');
                    $location.path('app/cometchat/' + $scope.unreadIds[length - 1]);
                } else {
                    $rootScope.$broadcast('cometchat:ocnClick', {
                        id: $scope.unreadIds[length - 1]
                    });
                }
            } else {
                $ionicSideMenuDelegate.toggleRight();
            }
        };

        $scope.initStatus = function() {

            var postData = {
                sStatus: $cometchat.getStatus(),
                user_id: $viewer.get('iUserId')
            };

            $http.post($site.getCometchatApiUrl('chat/changestatus'), postData)
                .success($scope.initStatusSuccess)
                .error($scope.initStatusError);
        };

        $scope.initStatusSuccess = function(data) {

            if (data.error_code) {
                return console.warn(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                console.log(data.message);
            }

            $cometchat.setStatus(data.sStatus);
        };

        $scope.initStatusError = function() {

            console.warn('initStatusError', arguments);
        };

        if ($viewer.get('iUserId')) {
            $scope.initStatus();
        }

        $scope.$on('user:login', function() {
            $timeout($scope.initStatus, 1e3);
        });

        $cometchat.startPing();

        $scope.$on('cometchat:ping', $scope.updateUnread);

        $scope.$on('cometchat:updateMuteNotification', $scope.updateMuteNotification);
    };
});