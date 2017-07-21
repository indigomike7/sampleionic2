define([
    'global/base/ItemController'
],function(ItemController){
    return function($scope, $injector, gettext, gettextCatalog, $location){
        
        $injector.invoke(ItemController, this, {$scope: $scope});
        
    };
});
