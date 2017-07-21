define([
    'global/base/BrowseController',
    'text!tpl/videochannel/videochannel-search.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site, $state, $history) {

        /**
         * check require permission
         */
        $site.requirePerm('videochannel.can_access_videos');
        $scope.canCreateVideo =  $site.getPerm('videochannel.can_upload_videos');

        /**
         * init data load
         */
        var iChannelId = $state.params.iChannelId;
        $scope.dataReady = false;

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.searchVideos = {
            sView: 'channel_detail',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 10,
            sOrder: 'latest',
            iChannelId: iChannelId,
        };

        $scope.rootPage = $history.getRoot();
        $scope.backToRoot = function() {
            $history.backToRoot();
        };
    };
});
