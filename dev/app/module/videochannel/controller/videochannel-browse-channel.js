define([
    'global/base/BrowseController',
    'text!tpl/videochannel/videochannel-search-channel.html'
], function(BrowseController, searchTemplate) {
    return function($scope, $injector, $site) {
        $site.debug > 2 && console.log('VideoChannelBrowseCtrl');
        
        /**
         * check require permission
         */

         // TODO
        $site.requirePerm('videochannel.can_access_videos');
        $scope.canCreateChannel =  $site.getPerm('videochannel.can_add_channels');
        $scope.canCreateChannel ? $scope.hasOtherButton = true : $scope.hasOtherButton = false;
        /**
         * init data load
         */

        $scope.dataReady = false;
        
        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchChannels = {
            sView: 'all_channels',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 10,
            sOrder: 'latest',
        };
    };
});
