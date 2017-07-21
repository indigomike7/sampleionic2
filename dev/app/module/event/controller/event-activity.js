
define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $state, $injector) {
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.searchFeeds  = {
            sItemType: $scope.item.getType(),
            sParentId: $scope.item.getType(),
            iItemId: $state.params.id,
            iAmountOfFeed: 5,
            iMinId: 0,
            iMaxId: 0
        };

        return $scope;
    };
});