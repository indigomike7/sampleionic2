define([
    'page/model/page',
    'global/base/BaseController'
], function(Model, Ctrl) {
    return function($scope, $injector, $state, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.searchFeeds =  {
            sItemType: 'pages',
            sParentId: 'pages',
            iItemId: $state.params.iPageId,
            iAmountOfFeed: 5,
        };
        
        return $scope;
    };
});