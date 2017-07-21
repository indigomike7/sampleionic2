define([], function() {

    return function($scope, $chat, $viewer, $ionicPopover, $ionicActionSheet, $timeout) {

        if (typeof cordova != 'undefined') {
            cordova.plugins.Keyboard.disableScroll(true);
        }
        $scope.showChat = function(obj) {
            $scope.chatObj = obj;

            $chat.setCurrent($scope.chatObj.getId());
            if(parseInt($viewer.get('iUserId')) < parseInt($scope.chatObj.getId()))
                $scope.thread_id = $viewer.get('iUserId') + ':' + $scope.chatObj.getId();
            else
                $scope.thread_id = $scope.chatObj.getId() + ':' + $viewer.get('iUserId');
            $scope.$broadcast('chat:objChange', obj);
        };

        $scope.$on('$destroy', function() {
            
            $chat.removeCurrent();
            
            if (typeof cordova != 'undefined') {
                cordova.plugins.Keyboard.disableScroll(false);
            }
        });

        /**
         * Popover for pick emoticon, sticker
         */
        $scope.showSmilePopover = function($event) {
            $scope.smilePopover || ($scope.smilePopover = $ionicPopover.fromTemplate(require('text!tpl/chat/chat-smile.html'), {
                scope: $scope
            }));

            $timeout(function() {
                $scope.smilePopover.show($event);
                if (ionic.Platform.isAndroid()) {
                    $scope.disableScrollContent();
                }
            }, 300);
        };

        $scope.hideSmilePopover = function() {

            $scope.smilePopover && $scope.smilePopover.hide();
        };

        $scope.$on('popover.hidden', function() {

            if (ionic.Platform.isAndroid()) {
                $scope.enableScrollContent();
            }
        });
    };
});