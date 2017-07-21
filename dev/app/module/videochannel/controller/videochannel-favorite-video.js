define([
    'global/base/BrowseController',
    'text!tpl/videochannel/videochannel-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {
        $site.debug > 2 && console.log('VideoChannelFavoriteVideoCtrl');
        /**
         * check require permission
         */
        $site.requirePerm('videochannel.can_access_videos');
        $scope.canCreateVideo =  $site.getPerm('videochannel.can_upload_videos');
        $scope.canCreateVideo ? $scope.hasOtherButton = true : $scope.hasOtherButton = false;

        /**
         * init data
         */
                
        $scope.dataReady = false;
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchVideos = {
            sView: 'favorite',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 10,
            sOrder: 'latest',
        };
    };
});
