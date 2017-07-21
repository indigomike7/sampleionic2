define([
    'global/base/BaseController',
], function(Ctrl) {
    return function($scope, $injector, $state) {
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.searchFeeds = {
            sItemType: 'directory',
            sParentId: 'directory',
            iItemId: $state.params.id,
            iAmountOfFeed: 10,
            iMinId: 0,
            iMaxId: 0
        };
    };
});
