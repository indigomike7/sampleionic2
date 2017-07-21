define([
    'global/base/BaseController',
    'subscription/model/subscription'
], function(BaseController, SubscriptionModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.subscriptionListData = {
            sView: 'upgrade'
        };

        $scope.getCurrentPackage = function() {

            $http2.get('user/getusergroupinfo')
            .success($scope.getCurrentPackageSuccess)
            .error($scope.getCurrentPackageError);
        };

        $scope.getCurrentPackageSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.currentPackage = $.extend({}, SubscriptionModel, {
                sTitle: data.sTitle
            });
        };

        $scope.getCurrentPackageError = function() {

            console.warn('getCurrentPackage', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.getCurrentPackage();
    };
});