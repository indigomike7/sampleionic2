define([
    'marketplace/model/listing',
    'global/base/ListController'
], function(ListingModel, ListCtrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site, $state) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more listings'),
            itemModel: ListingModel,
            apiService: 'marketplace/fetch',
            getQueryData: function() {
                return $scope.$parent.listingListData;
            }
        });
    };
});