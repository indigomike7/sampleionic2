define([
    'photo/model/photo',
    'global/base/ListController'
], function(Model, Ctrl) {
    
    return function($scope, $injector, $modal, gettext, gettextCatalog, $q, $http2) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more photos'),
            itemModel: Model,
            apiService: 'photo/fetch_photo',
            listById: false,
            getItemExtraData: $scope.$parent.getPhotoItemExtraData,
            getQueryData: function(){return $scope.$parent.searchPhotos;},
        });

        $scope.loadMore = function() {
            
            $scope.$qMore = $q.defer();

            if(null == $scope.listData)
                $scope.listData =  $scope.getQueryData();

            $scope.noItems = false;

            if ($scope.listById) {
                $scope.listData.sAction = 'more';
            }
            
            var sendData =  $scope.listData;
            
            $http2.get($scope.apiService, $scope.listData,{
                // cache: true,
                timeout: $scope.$qMore.promise
            })
            .success($scope.loadMoreSuccess).error($scope.loadMoreError)
            .finally(function() {
                $scope.canLoadMore = false;
            });
        };

        // Init without infiniteScroll
        $scope.loadMore();
        
        return $scope;
    };
});