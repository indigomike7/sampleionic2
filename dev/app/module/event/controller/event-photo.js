define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $state, $injector) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
                
        $scope.getPhotoItemExtraData  = function(){
            return {
                iParentId: $scope.item.getId(),
                sParentType: $scope.item.getType(),
            };
        };
        $scope.searchPhotos = {
            
            iParentId: $scope.item.getId(),
            sParentType: $scope.item.getType(),
            iPage: 1,
            sOrder: "recent",
            iAmountOfPhoto: 24 // 4 rows, 3 photos per rows
        };
        
        return $scope;
    };
});