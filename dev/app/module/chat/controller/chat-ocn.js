define([
    'chat/model/chat-message'
], function(ChatMessageModel) {

    return function($scope, $rootScope, $chat, $location,
        $ionicSideMenuDelegate, $ionicViewSwitcher) {
        $scope.unreadIds = $chat.getUnreads();
        $scope.isMuteNotification = $chat.isMuteNotification();

        $scope.updateUnread = function(e) {
            $scope.unreadIds = $chat.getUnreads();
            $scope.$$phase || $scope.$apply();

            // fix render issue on iOS 9
            if (!$scope.isMuteNotification && !$scope.isHideOCN) {
                angular.element('.chat-notification').hide().show(0);
            }
        };

        $scope.updateMuteNotification = function(e, bool) {

            $scope.isMuteNotification = bool;
        };

        $scope.goToChat = function() {
            // direct user to the detailed chat screen of the latest conversation or chat home
            var length = $scope.unreadIds.length;
            if (length) {
                if (ionic.Platform.isIPad()) {
                    $ionicSideMenuDelegate.toggleRight(false);
                    $ionicViewSwitcher.nextTransition('none');
                    $location.path('app/chat/' + $scope.unreadIds[length - 1]);
                } else {
                    $rootScope.$broadcast('chat:ocnClick', {
                        id: $scope.unreadIds[length - 1]
                    });
                }
            } else {
                $ionicSideMenuDelegate.toggleRight();
            }
        };

        // $chat.startPing();

        $scope.$on('chat:updateUnread', $scope.updateUnread);

        $scope.$on('chat:updateMuteNotification', $scope.updateMuteNotification);
    };
});