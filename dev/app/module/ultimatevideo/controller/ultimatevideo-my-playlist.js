define([
    'global/base/BrowseController',
    'text!tpl/ultimatevideo/ultimatevideo-search-video.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {
        $site.debug > 2 && console.log('UltimateVideoMyPlaylistController');
        /**
         * check require permission
         */
        $site.requirePerm('ynuv_can_view_video');
        $scope.canCreatePlaylist =  $site.getPerm('ynuv_can_add_playlist');

        /**
         * init data load
         */
        $scope.dataReady = false;

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.searchPlaylists = {
            sView: 'my_playlists',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 20,
            sOrder: 'creation_date',
        };
    };
});
