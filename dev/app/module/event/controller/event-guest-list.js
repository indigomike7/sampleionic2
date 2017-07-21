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
            apiService: 'event/viewguestlist',
            listData: {
                iEventId: $scope.item.getId(),
                iRSVP: $scope.guestListModalOptions.iRSVP,
                iAmountOfMember: 9999
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