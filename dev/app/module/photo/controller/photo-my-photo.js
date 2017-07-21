define([
    'global/base/BrowseController',
    'text!tpl/photo/photo-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $injector, $viewer) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchPhotos = {
            sFilterBy: 'my',
            sView: 'my',
            iPage: 1,
            sOrder: "latest",
            iAmountOfPhoto: 24 // 4 rows, 3 photos per rows
        };
        
        $scope.getPhotoItemExtraData  = function(){
            return {
                sParentType: 'my_photos',
                iParentId: $viewer.get('iUserId'),
            };
        };
        
        return $scope;
    };
});