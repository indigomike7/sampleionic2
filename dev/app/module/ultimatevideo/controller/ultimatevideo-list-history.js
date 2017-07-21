define([
    'angular',
    'ultimatevideo/model/playlist',
    'ultimatevideo/model/video',
    'global/base/ListController'
], function(angular, PlaylistModel, VideoModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location, $site) {
        $site.debug > 2 && console.log('UltimateVideoListPlaylistController');
        $scope.dataReady = false;

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more items'),
            itemModel: VideoModel,
            apiService: 'ultimatevideo/getHistory',
            listById: false,
            getQueryData: function(){return $scope.$parent.searchHistory;}
        });

        $scope.mapItems = function(items) {
            var extraData = $scope.getItemExtraData();

            return (items || []).map(function(item) {
                if(item.sModelType == "ultimatevideo_video"){
                    $scope.itemModel = VideoModel;
                }
                else if(item.sModelType == "ultimatevideo_playlist"){
                    $scope.itemModel = PlaylistModel;
                }
                return $.extend({}, $scope.itemModel, item, extraData);
            });
        };
        $scope.$on('ultimatevideo:delete-all-history', function(e, data) {
            $scope.items = [];
            // $scope.noItems = true;
            $scope.loadMore();
        });
    };
});