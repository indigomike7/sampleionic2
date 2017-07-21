define([
    'global/base/BaseController'
], function(BaseController) {

    return function($scope, $injector, $http2, $site, $modal, gettext, gettextCatalog, $chat, $ionicScrollDelegate, socket, $ionicPopover, $ionicActionSheet, $timeout, $rootScope, $viewer) {
        if (typeof cordova != 'undefined') {
            cordova.plugins.Keyboard.disableScroll(true);
        }

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });
        $chat.setCurrent($scope.chatObj.getId());

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