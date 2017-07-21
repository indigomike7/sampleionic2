define([
    'angular',
    'global/base/BaseController'
], function(angular, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog, $site) {
        $site.debug > 2 && console.log('UltimateVideoHomeController');
        /**
         * init base 
         */
        $scope.dataReady = false;
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
    };
});