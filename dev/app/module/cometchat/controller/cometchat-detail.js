define([
    'global/base/BaseController'
], function(BaseController) {

    return function($scope, $injector, $http, $site, $modal, gettext, gettextCatalog, $cometchat) {

        if (typeof cordova != 'undefined') {
            cordova.plugins.Keyboard.disableScroll(true);
        }

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $cometchat.setCurrent($scope.cometchatObj.getId());

        $scope.$on('$destroy', function() {
            $cometchat.removeCurrent();
            if (typeof cordova != 'undefined') {
                cordova.plugins.Keyboard.disableScroll(false);
            }
        });
    };
});