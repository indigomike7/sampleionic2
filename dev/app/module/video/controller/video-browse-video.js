define([
    'global/base/BrowseController',
    'text!tpl/video/video-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {
        
        /**
         * check require permission
         */
        $site.requirePerm('video.can_access_videos');
        $scope.canCreateVideo =  $site.getPerm('video.can_upload_videos');

        /**
         * init data load
         */
        $scope.dataReady = false;
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchVideos = {
            sView: 'all',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 20,
            sOrder: 'creation_date',
        };
    };
});
