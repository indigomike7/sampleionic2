define([], function() {

    return function($scope, $cometchat) {

        if (typeof cordova != 'undefined') {
            cordova.plugins.Keyboard.disableScroll(true);
        }

        $scope.showCometchat = function(obj) {

            $scope.cometchatObj = obj;

            $cometchat.setCurrent($scope.cometchatObj.getId());

            $scope.$broadcast('cometchat:objChange', obj);
        };

        $scope.$on('$destroy', function() {
            
            $cometchat.removeCurrent();
            
            if (typeof cordova != 'undefined') {
                cordova.plugins.Keyboard.disableScroll(false);
            }
        });
    };
});