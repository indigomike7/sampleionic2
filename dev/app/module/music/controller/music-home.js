define([
    'global/base/BaseController'
], function(Ctrl) {

    return function($scope, $injector, gettext, gettextCatalog, $location) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
    };
});