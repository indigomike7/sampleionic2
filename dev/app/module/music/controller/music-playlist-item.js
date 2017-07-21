define([
    'global/base/ItemController'
],function(Ctrl){
    return function($scope, $http2,$site, $injector, gettext, gettextCatalog, $location, $modal){
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.onItemSetting = $scope._setting($scope, function() {
            
            var settingBtns = [];
    
            if (!$scope.item.isOwner()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Report this album'),
                    action: $scope.onItemReport
                });
            }
            
            return settingBtns;
        });
        
        $scope.onItemRate = $scope._rate($scope, function() {
            return {
                sItemType: $scope.item.getType(),
                iItemId: $scope.item.getId(),
                iRating: 0
            };
        });
    };
    
});
