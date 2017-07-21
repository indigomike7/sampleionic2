define([
    'message/model/message',
    'global/base/ListController'
], function(Model, Ctrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site, $state) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            itemModel: Model,
            apiService: 'message/detail',
            listData: {
                iItemId: $scope.item.getId()
            }
        });

        $scope.loadMore = function() {
            $http2.get($scope.apiService, $scope.listData)
            .success($scope.loadMoreSuccess)
            .error($scope.loadMoreError)
            .finally(function() {
                $scope.$broadcast('scroll.infiniteScrollComplete');
                $scope.canLoadMore = false;
            });
        };
    };
});