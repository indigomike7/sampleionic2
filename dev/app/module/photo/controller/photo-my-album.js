define([
    'global/base/BrowseController',
    'text!tpl/photo/photo-album-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $site, $injector, $viewer) {
        
        if(!$site.getPerm('photo.can_view_photo_albums')){
            $location.path('app/photos/my');
        }

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchAlbums  = {
            sFilterBy: 'my',
            sView: 'my',
            iPage: 1,
            sSearch: '',
            sOrder: 'latest',
            iUserId: $viewer.get('iUserId'),
            iCategoryId: 0,
            iAmountOfAlbum: 24, // 2 x4 items,
        };
        
        $scope.sView  = 'my';
        
        return $scope;
    };
});