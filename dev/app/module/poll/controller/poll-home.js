define([
    'global/base/BaseController',
    'poll/controller/poll-home-ipad'
], function(Controller, PollHomeIpadCtrl) {
    return function($scope, $injector, $site, gettext, gettextCatalog, $modal, $location) {
        /**
         * this is base controller for "browse polls", "my polls page"
         */

        /**
         * extends base classes
         */
        $injector.invoke(Controller, this, {
            $scope: $scope
        });
        
        if (ionic.Platform.isIPad()) {
            $injector.invoke(PollHomeIpadCtrl, this, {
                $scope: $scope
            });
        }
    };
});