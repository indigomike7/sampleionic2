define([
    'blog/controller/blog-item',
    'blog/model/blog'
], function(BlogItemView, BlogModel) {

    return function($scope, $state, $injector, $http2, $site, $modal, gettext, gettextCatalog, $location, $coreSettings, $dislike) {
        
        $injector.invoke(BlogItemView, this, {
            $scope: $scope
        });

        $scope.item = $.extend({}, BlogModel, {
            iBlogId: $state.params.id
        });

        $scope.showSettings = false;
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this blog entry?'),
            'blog/delete',
            function(){return {iBlogId: $scope.item.getId()};},
            function(data){
                $scope.goBack();
            }
        );

        $scope.fetchData = function() {

            var postData = {
                iBlogId: $scope.item.getId()
            };

            $http2.get('blog/detail', postData)
            .success($scope.fetchDataSuccess)
            .error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);
            
            if (!$scope.item.isOwner() || $scope.item.canEdit() || $scope.item.canDelete()
            || ($coreSettings.get('like_allow_dislike') && ($scope.item.getTotalDislike() > 0 || $scope.item.canDislike()))) {
                $scope.showSettings = true;
            }
            
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.deleteSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $location.path('app/blogs/my');
        };

        $scope.onItemSetting = $scope._setting($scope, function() {

            var settingBtns = [];

            if ($coreSettings.get('like_allow_dislike')) {

                if ($scope.item.getTotalDislike() > 0) {
                    settingBtns.push({
                        text: $dislike.getDislikeStat($scope.item),
                        action: $scope.onViewDisliked
                    });
                }

                if ($scope.item.canDislike()) {
                    settingBtns.push({
                        text: $scope.item.isDisliked() ? gettextCatalog.getString('Remove Dislike') : gettextCatalog.getString('Dislike'),
                        action: $scope.onItemDislike
                    });
                }
            }

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

        $scope.fetchData();
    };
});
