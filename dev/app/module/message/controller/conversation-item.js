define([
    'global/base/ItemController'
], function(ItemController) {
    return function($scope, $injector, gettext, gettextCatalog, $modal, $site, $http2, $rootScope) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });
    };
});