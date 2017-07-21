define([
    'subscription/model/subscription',
    'global/base/ListController'
], function(SubscriptionModel, ListCtrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site, $state, $viewer) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            noMoreText: null,
            enableLoadMore: false,
            itemModel: SubscriptionModel,
            apiService: 'subscribe/fetch',
            getQueryData: function() {
                return {};
            }
        });
    };
});