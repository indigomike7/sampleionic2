define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $site, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        return $scope;
    };
});