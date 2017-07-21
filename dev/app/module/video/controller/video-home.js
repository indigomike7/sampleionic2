define([
    'angular',
    'global/base/BaseController'
], function(angular, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog) {

        /**
         * init base 
         */
        $scope.dataReady = false;
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
    };
});