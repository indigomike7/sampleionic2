define([
    'user/model/user',
    'global/base/ListController'
], function(Model, ListCtrl) {
    
    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        
        $.extend($scope, {
            itemModel: Model,
            apiService: 'like/listalllikes',
            listData: {
                iAmountOfLike: 9999,
                iItemId: $scope.obj.getId(),
                sItemType: $scope.obj.getType(),
                sParentId: $scope.obj.getParentModuleId()
            }
        });

        $scope.loadMore = function() {

            $http2.post($scope.apiService, $scope.listData)
            .success($scope.loadMoreSuccess)
            .error($scope.loadMoreError)
            .finally(function() {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                $scope.canLoadMore = false;
            });
        };
    };
});