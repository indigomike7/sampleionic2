define([
    'ynchat/controller/ynchat-base',
    'ynchat/model/ynchat'
], function(YnchatBaseCtrl, YnchatModel) {

    return function($scope, $injector, $location, $http2, $site, gettext, gettextCatalog) {

        if (typeof cordova != 'undefined') {
            cordova.plugins.Keyboard.disableScroll(true);
        }

        $injector.invoke(YnchatBaseCtrl, this, {
            $scope: $scope
        });

        /**
         * Settings
         */
        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            settingBtns.push({
                text: gettextCatalog.getString('View old conversations'),
                action: function() {
                    $location.path('app/ynchat/' + $scope.ynchatObj.getId() + '/history');
                    $scope.$$phase || $scope.$apply();
                }
            });

            return settingBtns;
        });

        /**
         * Get current chatting user data
         */
        $scope.getObjData = function() {

            var sendData = {
                iUserId: $scope.ynchatObj.getId()
            };

            var successCb = function(data) {

                if (data.error_code) {
                    return console.warn('user/detail', arguments);
                }

                $scope.ynchatObj = $.extend($scope.ynchatObj, data);
            };

            var errorCb = function() {

                console.warn('user/detail', arguments);
            };

            $http2.get('user/detail', sendData).success(successCb).error(errorCb);
        };

        $scope.getObjData();
    };
});