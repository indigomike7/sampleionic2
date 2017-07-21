define([
    'directory/controller/directory-item'
], function(DirectoryItemCtrl) {
    return function($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal) {
        $injector.invoke(DirectoryItemCtrl, this, {
            $scope: $scope
        });

        $scope.getActions = function() {
            var btns = [];

            if ($scope.item.bCanManage) {
                btns.push({
                    text: gettextCatalog.getString('Edit Information'),
                    action: $scope.onEdit
                });

                btns.push({
                    text: gettextCatalog.getString('Update Cover Photos'),
                    action: $scope.onUpdateCover
                });
            }

            return btns;
        };
    };
});
