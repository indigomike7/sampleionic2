define([
    'angular',
    'videochannel/model/videochannel-channel',
    'global/base/ListController'
], function(angular, VideoModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location) {

        $scope.dataReady = false;
        
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more channels'),
            itemModel: VideoModel,
            apiService: function(){
                if (typeof $scope.$parent.searchVideosApi != 'undefined') {
                    return $scope.$parent.searchVideosApi;
                }
                
                return 'videochannel/fetch';
            },
            listById: false,
            getQueryData: function(){return $scope.$parent.searchChannels;},
        });
    };
});