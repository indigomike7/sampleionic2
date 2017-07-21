define([
    'global/base/BaseController'
], function(Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $state) {

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.searchDiscussionTopics = {
            iBusinessId: $scope.item.getId(),
            sView: 'all',
            iPage: 1,
            iAmount: 5,  
        };
        return $scope;
    };
});