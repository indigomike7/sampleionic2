define([
    'group/model/group',
    'global/base/BaseController'
], function(Model, Ctrl) {
    return function($scope, $injector, $state, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.searchFeeds =  {
            sItemType: 'groups',
            sParentId: 'groups',
            iItemId: $state.params.iGroupId,
            iAmountOfFeed: 5,
        };
        
        return $scope;
    };
});