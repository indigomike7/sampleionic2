define([
    'angular',
    'ultimatevideo/model/video',
    'global/base/ListController'
], function(angular, VideoModel, ListCtrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $location, $http2, $site, $ionicScrollDelegate) {
        $site.debug > 2 && console.log('UltimateVideoPlaylistVideoController');
        $scope.dataReady = false;
        $scope.isProcessing = false;
        $scope.iBeginIndex = 0;
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

        $scope.scrollTop = function() {
            $ionicScrollDelegate.scrollTop();
        };

        $scope.loadNext = function() {
            if($scope.isProcessing)
                return;

            $scope.isProcessing = true;
            if(null == $scope.listData)
                $scope.listData =  $scope.getQueryData();
            if($scope.listData.iTotalVideo == 0) {
                $scope.dataReady = true;
                return;
            }
            if($scope.items.length >= $scope.listData.iTotalVideo){
                $scope.iBeginIndex += $scope.listData.iLimit;
                $scope.listData.iPage++;
                $scope.isProcessing = false;
                $scope.dataReady = true;
                $scope.scrollTop();
                return;
            }
            $scope.dataReady = false;
            $http2.get($scope.apiService, $scope.listData,{
            })
                .success($scope.loadNextSuccess)//.error($scope.loadNexError)
                .finally(function() {
                    $scope.isProcessing = false;
                    $scope.dataReady = true;
                });
        };

        $scope.loadNextSuccess = function(data) {
            if (data.error_code) {
                return $modal.alert(data.error_message || 'Can not load data from server');
            }
            var moreItems = $scope.mapItems(data);
            if (0 == moreItems.length) {
                if (0 == $scope.items.length) {
                } else {
                    $scope.noMoreText && $modal.toast($scope.noMoreText);
                }
                if ($scope.isFirstLoad) {
                    $scope.isFirstLoad = false;
                }
                return;
            }
            $scope.iBeginIndex = $scope.items.length - ($scope.items.length % $scope.listData.iLimit);
            $scope.items = $scope.items.concat(moreItems);
            $scope.listData.iPage++;
            if ($scope.isFirstLoad) {
                $scope.isFirstLoad = false;
            }
            $scope.scrollTop();
        };
        
        $scope.loadPrev = function () {
            if($scope.listData.iPage <= 1)
                return;
            $scope.listData.iPage--;
            $scope.iBeginIndex -= $scope.listData.iLimit;
            $scope.scrollTop();
        };
        $scope.loadNext();
    };
});