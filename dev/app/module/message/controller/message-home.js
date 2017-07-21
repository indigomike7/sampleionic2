define([
    'global/base/BaseController'
], function(Ctrl) {

    return function($scope, $injector) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        return $scope;
    };
});