define([
    'angular',
    'video/model/video',
    'global/base/ListController'
], function(angular, VideoModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location) {

        $scope.dataReady = false;
        
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more videos'),
            itemModel: VideoModel,
            apiService: 'video/fetch',
            listById: false,
            getQueryData: function(){return $scope.$parent.searchVideos;},
        });
    };
});