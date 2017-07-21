define([
    'global/base/BrowseController',
    'text!tpl/photo/photo-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $injector) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchPhotos = {
            sFilterBy: 'all',
            sView: 'all',
            iPage: 1,
            sOrder: "latest",
            iAmountOfPhoto: 24 // 4 rows, 3 photos per rows
        };
        
        $scope.getPhotoItemExtraData  = function(){
            return {
                sParentType: 'all_photos',
                iParentId: -1,
            };
        };
        
        return $scope;
    };
});