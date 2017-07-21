define([
    'angular',
    'global/base/BaseController'
], function(angular, BaseController) {
    
    return function($scope, $injector, gettext, gettextCatalog, $location, $site) {

        $site.requirePerm('photo.can_view_photos');

        $scope.canViewAlbums =  $site.getPerm('photo.can_view_photo_albums');
        $scope.canViewPhotos =  $site.getPerm('photo.can_view_photos');
        $scope.canAddPhoto = $site.getPerm('photo.can_upload_photos') && $site.getPerm('photo.can_create_photo_album');

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        return $scope;
    };
});