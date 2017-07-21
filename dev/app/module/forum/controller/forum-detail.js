define([
    'forum/model/forum',
    'forum/model/thread',
    'global/base/BrowseController',
    'text!tpl/forum/forum-search.html'
], function(ForumModel, ThreadModel, BrowseController, searchTemplate) {

    return function($scope, $injector, $state, $http2, $site, gettext, gettextCatalog, $modal, $location, $ionicScrollDelegate) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $scope.item = $.extend({}, ForumModel, {
            iForumId: $state.params.iForumId
        });

        $scope.searchData = {
            sSearchType: 'thread'
        };

        $scope.sendData = {
            iPage: 1,
            iAmountOfThread: 10,
            iForumId: $scope.item.getId()
        };

        $scope.totalPage = 1;

        $scope.getItem = function() {

            $http2.get('forum/detail', $scope.sendData).success($scope.getItemSuccess).error($scope.getItemError);
        };

        $scope.getItemSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data.aForum);
            $scope.searchData.sForumIds = $scope.item.getId();
            $scope.jumpId = $scope.item.getId();
            $scope.totalPage = Math.ceil($scope.item.getTotalThread() / $scope.sendData.iAmountOfThread) || 1;

            $scope.subForums = $scope.mapSubForums(data.aSubForum);

            $scope.announcements = $scope.mapThreads(data.aAnnouncement);

            $scope.threads = $scope.mapThreads(data.aThread);

            $scope.dataReady = true;
        };

        $scope.getItemError = function() {

            console.error('getItem', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.mapSubForums = function(items) {

            return (items || []).map(function(item) {
                return $.extend({}, ForumModel, item, {
                    aSubForum: (item.aSubForum || []).map(function(sub) {
                        return $.extend({}, ForumModel, sub);
                    })
                });
            });
        };

        $scope.mapThreads = function(items) {

            return (items || []).map(function(item) {
                return $.extend({}, ThreadModel, item);
            });
        };

        $scope.onJump = function() {

            $location.path('app/forum/' + $scope.jumpId);
            $scope.$$phase || $scope.$apply();
        };

        $scope.onChangePage = function(e, iPage) {

            $scope.threads = [];
            $scope.sendData.iPage = iPage;
            $scope.loadThreads(iPage);

            $ionicScrollDelegate.$getByHandle('forum-detail').scrollTop();
        };

        $scope.loadThreads = function(iPage) {

            $scope.isLoadingThreads = true;

            $http2.get('forum/detail', $scope.sendData).success(function(data) {
                if (iPage == $scope.sendData.iPage) {
                    $scope.loadThreadsSuccess(data);
                }
            }).error(function() {
                if (iPage == $scope.sendData.iPage) {
                    console.warn('loadThreads', arguments);
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                }
            }).finally(function() {
                if (iPage == $scope.sendData.iPage) {
                    $scope.isLoadingThreads = false;
                }
            });
        };

        $scope.loadThreadsSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.threads = $scope.mapThreads(data.aThread);
        };

        $scope.$on('pagination:change', $scope.onChangePage);

        $scope.getItem();
    };
});