define([
    'global/base/ItemController'
], function(Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog, $location, $modal, $site, $http2, $rootScope) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];
            var item = $scope.item;

            settingBtns.push({
                text: gettextCatalog.getString('Quote post'),
                action: function() {
                    $location.path('app/page_topic_quotepost/' + item.getTopicId() + '/' + item.getId());
                }
            });

            if (!item.isOwner()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Report this post'),
                    action: $scope.onItemReport
                });
            }

            if (item.canEdit()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Edit post'),
                    action: function() {
                        $location.path('app/page_topic_editpost/' + item.getId());
                    }
                });
            }

            if (item.canDelete()) {
                settingBtns.push({
                    text: gettextCatalog.getString('Delete post'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return settingBtns;
        });
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this post?'),
            'page/delete_post',
            function(){return {iPostId: $scope.item.getId()};},
            function(data){
                if (data.message) {
                    $modal.toast(data.message);
                }
    
                $scope.items.deleteItem($scope.item.getId());

                if (!$scope.items.length) {
                    $scope.goBack();
                }
            }
        );
    };
});