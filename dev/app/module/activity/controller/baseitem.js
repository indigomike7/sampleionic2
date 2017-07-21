define([
    'activity/plugin/headline',
    'global/base/ItemController'
], function(HeadlinePlugin, ItemController) {
    
    return function($scope, $injector, gettext, gettextCatalog, $ionicActionSheet, $modal, $site, $http2, $rootScope, $state) {
        
        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        if ($scope.item) {
            $scope.headline = HeadlinePlugin.get($scope.item, gettext, gettextCatalog, $state);
        }
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this post?'),
            'feed/delete',
            function(){return {
                iActionId: $scope.item.getId(),
                sParentId: $scope.item.getParentModuleId() || ''
            };},
            function(data){
                $scope.items.deleteItem($scope.item.getId());
            }
        );

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($scope.item.canDelete()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Delete this post'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            if (!$scope.item.isOwner()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Report this post'),
                    action: $scope.onItemReport
                });
            }

            return settingBtns;
        });

    };
});