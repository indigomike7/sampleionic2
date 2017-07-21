define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog, $state) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        $scope.searchDiscussionTopics = {
            iPageId: $scope.item.getId(),
            sView: 'all',
            iPage: 1,
            iAmount: 5,  
        };
        return $scope;
    };
});