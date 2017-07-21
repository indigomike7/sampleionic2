define([
    'global/base/ItemController'
], function(ItemController) {
    return function($scope, $injector, gettext, gettextCatalog, $modal, $site, $http2, $rootScope, $location) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if (!$scope.item.isOwner()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport,
                });
            }

            if ($scope.item.canEdit()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Edit Blog'),
                    action: function() {
                        $location.path('app/blog/' + $scope.item.getId() + '/edit');
                    }
                });
            }

            if ($scope.item.canDelete()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Delete Blog'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return settingBtns;
        });

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this blog entry?'),
            'blog/delete',
            function() {
                return {
                    iBlogId: $scope.item.getId()
                };
            },
            function(data) {
                $scope.items.deleteItem($scope.item.getId());
            }
        );
    };
});