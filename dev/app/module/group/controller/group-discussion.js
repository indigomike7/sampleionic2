define([
    'group/model/group',
    'global/base/BaseController'
], function(GroupModel, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $state) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.searchDiscussionTopics = {
            iGroupId: $scope.item.getId(),
            sView: 'all',
            iGroup: 1,
            iAmount: 5,  
        };
        return $scope;
    };
});