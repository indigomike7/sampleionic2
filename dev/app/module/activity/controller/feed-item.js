define([
    'activity/plugin/headline',
    'activity/controller/baseitem',
    'activity/model/feed',
    'global/helper'
], function(HeadlinePlugin, BaseItemCtrl, FeedModel, Helper) {
    return function($scope, $injector, $state, $site, $requestQueue, gettext, gettextCatalog, $timeout) {
        $injector.invoke(BaseItemCtrl, this, {
            $scope: $scope
        });

        $scope.queue = false;
        $scope.queueStatus = '';

        $scope.updateItem = function() {
            $scope.queue = false;

            if ($scope.queueStatus == 'deleted') {
                $scope.items.deleteItem($scope.item.getId());
                $scope.updateMinMax && $scope.updateMinMax();
            } else {
                var newData = $requestQueue.getFromQueue($scope.item.getId());

                if (newData.etag == $scope.item.etag) {
                    return;
                }

                $.extend($scope.item, newData);

                $scope.headline = HeadlinePlugin.get($scope.item, gettext, gettextCatalog, $state);
            }
        };

        $scope.checkOnScreen = function() {
            if ($scope.queue) {
                $scope.updateItem();
            } else if (Helper.isOnScreen($scope.element)) {
                $scope.queue = true;
                $requestQueue.addToQueue($scope.item.getId(), function(status) {
                    $scope.queueStatus = status;
                    $timeout(function() {
                        if (!$scope.isScrolling) {
                            $scope.updateItem();
                        }
                    }, 250);
                });
            }
        };

        $scope.$on('state-change-success', $scope.checkOnScreen);
        $scope.$on('scroll-complete', $scope.checkOnScreen);
    };
});