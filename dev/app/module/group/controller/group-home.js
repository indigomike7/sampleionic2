define([
    'group/model/group',
    'global/base/BaseController'
], function(GroupModel, Ctrl) {
    return function($scope, $site, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        return $scope;
    };
});