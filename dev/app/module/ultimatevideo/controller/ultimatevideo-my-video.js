define([
    'global/base/BrowseController',
    'text!tpl/ultimatevideo/ultimatevideo-search-video.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {
        $site.debug > 2 && console.log('UltimateVideoMyVideoController');
        /**
         * check require permission
         */
        $site.requirePerm('ynuv_can_view_video');
        $scope.canCreateVideo =  $site.getPerm('ynuv_can_upload_video');

        /**
         * init data load
         */
        $scope.dataReady = false;

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.searchVideos = {
            sView: 'my',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 20,
            sOrder: 'creation_date',
        };
        $scope.videoItemOptions = ['watch_later', 'add_to_playlist', 'edit', 'delete'];
    };
});
