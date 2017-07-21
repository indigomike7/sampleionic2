define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $injector, $state) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        var iUserId  = $state.params.iUserId;
        
        // option to fetch photos list
        $scope.searchPhotos =  {
            // iParentId: $scope.item.getId(),
            // sParentType: 'profile_photo',
            bIsUserProfile: 1,
            iUserId: $scope.item.getId(),
            iInDetails: 1,
            iPage: 1,
            sOrder: "latest",
            iAmountOfPhoto: 24 // 4 rows, 3 photos per rows
        };
        
        // options to view photo details
        $scope.getPhotoItemExtraData = function(){
            return {
                iParentId: $scope.item.getId(),
                sParentType: 'profile_photo', 
            };
        };
        return $scope;
    };
});