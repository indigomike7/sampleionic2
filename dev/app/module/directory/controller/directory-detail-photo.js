define([
    'directory/model/directory-business',
    'global/base/BaseController'
], function(Model, Ctrl) {
    return function($scope, $injector,$state,  $modal, gettext, gettextCatalog) {

        $injector.invoke(Ctrl, this, {$scope: $scope});

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
            iLimit: 24 // 4 rows, 3 photos per rows
        };

        $scope.searchPhotosApi = 'directory/fetch_photos';

        return $scope;
    };
});