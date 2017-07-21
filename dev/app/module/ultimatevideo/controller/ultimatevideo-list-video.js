define([
    'angular',
    'ultimatevideo/model/video',
    'global/base/ListController'
], function(angular, VideoModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location, $site) {
        $site.debug > 2 && console.log('UltimateVideoListVideoController');
        $scope.dataReady = false;
        
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more videos'),
            itemModel: VideoModel,
            apiService: 'ultimatevideo/fetch',
            listById: false,
            getQueryData: function(){return $scope.$parent.searchVideos;},
        });
    };
});