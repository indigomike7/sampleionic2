define([
    'global/base/BrowseController'
], function(BrowseController) {
    return function($scope, $injector, $site) {
        /**
         * check require permission
         */
        $site.requirePerm('directory.view');
        $scope.canAddItem = $site.getPerm('directory.create');

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });
    };
});
