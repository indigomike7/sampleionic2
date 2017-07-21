define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $injector, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {$scope: $scope});
        
        console.log('PageDetailInfoCtrl');
        
        return $scope;
    };
});