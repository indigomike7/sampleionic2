define([
    'global/base/ItemController'
], function(Ctrl) {
    return function($scope, $http2, $injector, gettextCatalog, $modal, $location) {
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.onItemSetting = function() {
            $scope._setting($scope, $scope.getActions)();
        };

        $scope.hasActions = function() {
            return $scope.getActions().length ? true : false;
        };

        $scope.getActions = function() {
            var btns = [];

            if ($scope.item.bCanEdit) {
                btns.push({
                    text: gettextCatalog.getString('Edit'),
                    action: $scope.onEdit
                });
            }

            if ($scope.item.bCanDelete) {
                btns.push({
                    text: gettextCatalog.getString('Delete'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return btns;
        };

        $scope.onEdit = function() {
            $location.path('app/directory/review/' + $scope.item.getId() + '/edit');
        };

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure you want to delete this business?'),
            'directory/delete_review',
            function() {
                return {
                    iReviewId: $scope.item.getId()
                };
            },
            function(data) {
                $scope.items.deleteItem($scope.item.getId());
                angular.extend($scope.obj, {
                    bCanReview: data.bCanReview,
                    iTotalReview: Math.max($scope.obj.iTotalReview - 1, 0)
                });
            }
        );
    };
});
