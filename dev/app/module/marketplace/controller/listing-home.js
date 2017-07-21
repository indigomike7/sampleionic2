define([
    'global/base/BaseController'
], function(Ctrl) {

    return function($scope, $injector, gettext, gettextCatalog) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
    };
});