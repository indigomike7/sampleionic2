define([
    'global/base/BrowseController',
    'text!tpl/photo/photo-album-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $injector, $site, $location) {

        if(!$site.getPerm('photo.can_view_photo_albums')){
            $location.path('app/photos');
        }

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });



        $scope.searchTemplate = searchTemplate;
        
        $scope.searchAlbums = {
            sFilterBy: 'all',
            sView: 'all',
            iPage: 1,
            sSearch: '',
            sOrder: 'latest',
            iCategoryId: 0,
            iAmountOfAlbum: 24, // 2 x4 items,
        };
        
        $scope.sView  = 'all';
        
        return $scope;
    };
});
