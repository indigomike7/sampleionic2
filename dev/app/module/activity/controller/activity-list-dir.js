define([
    'angular',
    'activity/model/feed',
    'global/base/ListController'
], function(angular, FeedModel, ListCtrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $state, $http2, $timeout, $globalData) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });
        
        $.extend($scope, {
            noMoreText: gettextCatalog.getString('No more activities'),
            itemModel: FeedModel,
            apiService: 'feed/fetch',
            listById: true,
            getQueryData: function(){
                return $scope.$parent.searchFeeds;
            }
        });

        if ($state.current.name == 'app.newsfeed') {
            $scope.enableLoadNew = true;
        }

        $scope.checkLoadNew = function() {
            if ($scope.isFirstLoad) {
                return;
            }

            if ($globalData.get('hasNewFeed')) {
                $globalData.set('hasNewFeed', false);
                if ($scope.enableLoadNew) {
                    $scope.loadNew();
                }
            }
        };

        $scope.$on('refresh', $scope.loadNew);
        $scope.$on('state-change-success', $scope.checkLoadNew);

        $scope.initAndroidRefresher = function() {
            $scope.defRefresh = null;

            $('.ptr-wrapper').pullToRefresh({
                callback: function() {
                    $scope.defRefresh = $.Deferred();
                    $scope.loadNew();
                    return $scope.defRefresh.promise();
                }
            });

            $scope.$on('scroll.refreshComplete', function() {
                $scope.defRefresh && $scope.defRefresh.resolve();
            });
        };

        $timeout(function() {
            if (ionic.Platform.isAndroid() && $scope.enableLoadNew) {
                $scope.initAndroidRefresher();
            }
        }, 500);
    };
});