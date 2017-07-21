define([
    'directory/model/directory-business',
    'global/base/ListController'
], function(ItemModel, ListCtrl) {
    return function($scope, $injector, gettextCatalog) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            apiService: 'directory/fetch',
            noMoreText: gettextCatalog.getString('No more business'),
            itemModel: ItemModel,
            listById: false,
            getQueryData: function() {
                return $scope.$parent.listData;
            },
        });

        $scope.afterResetQuery = function() {

            var aLocation = $scope.listData.aLocation;
            
            if (aLocation) {
                $.extend($scope.listData, {
                    sLocationAddress: aLocation.formatted_address,
                    sLat: aLocation.geometry.location.lat(),
                    sLong: aLocation.geometry.location.lng()
                });
            } else {
                delete($scope.listData.sLocationAddress);
                delete($scope.listData.sLat);
                delete($scope.listData.sLong);
            }
        };
    };
});