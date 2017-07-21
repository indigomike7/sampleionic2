define([
    'angular',
    'global/base/BaseController'
], function(angular, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog, $site) {

        /**
         * init base 
         */
        $scope.canViewAllChannel = $site.getPerm('user.is_admin');
        $scope.canViewMyChannel = $site.getPerm('videochannel.can_add_channels');
        $scope.dataReady = false;
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
    };
});