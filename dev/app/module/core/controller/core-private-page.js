define([
    'global/base/BaseController',
],function(Ctrl){
   return function($scope, $injector, $history){
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $history.pop();
    };
});