define([
	'global/base/ItemController',
	'global/viewer',
],function(ItemController, Viewer){
    return function($scope, $injector){
        $injector.invoke(ItemController, this, {$scope: $scope});
    };
});
