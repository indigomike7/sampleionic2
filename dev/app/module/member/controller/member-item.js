define([
    'friend/controller/friend-item',
],function(Ctrl){
    return function($scope, $injector){
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        return $scope;
    };
});
