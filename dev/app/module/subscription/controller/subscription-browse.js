define([
    'global/base/BaseController',
    'subscription/model/subscription'
], function(BaseController, SubscriptionModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $ionicSideMenuDelegate) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.subscriptionListData = {
            sView: 'browse'
        };

        $ionicSideMenuDelegate.canDragContent(false);

        $scope.$on('$destroy', function() {
            $ionicSideMenuDelegate.canDragContent(true);
        });
    };
});