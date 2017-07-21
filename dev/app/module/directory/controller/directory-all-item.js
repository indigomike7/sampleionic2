define([
    'directory/controller/directory-item'
], function(DirectoryItemCtrl) {
    return function($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal) {
        $injector.invoke(DirectoryItemCtrl, this, {
            $scope: $scope
        });

        $scope.getActions = function() {
            var btns = [];

            if ($scope.item.bCanClaim) {
                btns.push({
                    text: gettextCatalog.getString('Claim This Business'),
                    action: $scope.claim
                });
            }

            return btns;
        };
    };
});
