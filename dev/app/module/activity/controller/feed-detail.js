define([
    'activity/controller/baseitem',
    'activity/model/feed',
    'activity/plugin/headline'
], function(ItemController, FeedModel, HeadlinePlugin) {
    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $coreSettings, $dislike) {

        $injector.invoke(ItemController, this, {$scope: $scope});

        $scope.item = $.extend({}, FeedModel, {
            iActionId: $state.params.id,
            parentModuleId: $state.params.parentModuleId
        });
        
        
        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure to delete this post?'),
            'feed/delete',
            function(){return {
                iActionId: $scope.item.getId(),
                sParentId: $scope.item.getParentModuleId() || ''
            };},
            function(data){
                $scope.goBack();
            }
        );


        $scope.fetchData = function() {

            var sendData = {
                iActionId: $scope.item.getId(),
                bIsGetOneFeed: true,
                sParentId: $scope.item.getParentModuleId()
            };

            $http2.get('feed/get', sendData)
            .success($scope.fetchDataSuccess)
            .error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

        	console.log('fetchDataSuccess', data);

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data[0]);
            $scope.headline = HeadlinePlugin.get($scope.item, gettext, gettextCatalog, $state);
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchDataError', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.doDeleteSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.goBack();
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

        $scope.fetchData();
    };
});