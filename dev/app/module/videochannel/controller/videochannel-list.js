define([
    'angular',
    'videochannel/model/videochannel',
    'global/base/ListController'
], function(angular, VideoModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location, $site) {
        $site.debug > 2 && console.log('VideoChannelListCtrl');
        $scope.dataReady = false;
        
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more videos'),
            itemModel: VideoModel,
            apiService: function(){
                if (typeof $scope.$parent.searchVideosApi != 'undefined') {
                    return $scope.$parent.searchVideosApi;
                }
                
                return 'videochannel/fetch';
            },
            listById: false,
            getQueryData: function(){return $scope.$parent.searchVideos;},
        });
    };
});