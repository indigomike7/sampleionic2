define([
    'global/base/ItemController'
], function(ItemController) {
    return function($scope, $injector, gettext, gettextCatalog, $location, $modal, $site, $http2, $rootScope, $history) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.onPostLike = $scope._like($scope, function() {
            return $scope.post;
        });

        $scope.onPostReport = $scope._report($scope, function() {
            return $scope.post;
        });

        $scope.onPostShare = function() {

            if (typeof window.plugins == 'undefined' || !$scope.post.getSocialShareUrl()) {
                return;
            }

            window.plugins.socialsharing.share(null, null, null, $scope.post.getSocialShareUrl());
        };

        $scope.onPostQuote = function() {

            $location.path('app/forum_thread/' + $scope.post.getThreadId() + '/reply/' + $scope.post.getId());
            $scope.$$phase || $scope.$apply();
        };

        $scope.onPostSetting = $scope._setting($scope, function() {

            var btns = [];

            if (!$scope.post.isOwner()) {
                btns.push({
                    text: gettextCatalog.getString('Report this post'),
                    action: $scope.onPostReport
                });
            } else {
                btns.push({
                    text: gettextCatalog.getString('Edit post'),
                    action: function() {
                        $location.path('app/forum_post/' + $scope.post.getId() + '/edit');
                    }
                });

                btns.push({
                    text: gettextCatalog.getString('Delete post'),
                    action: $scope.onPostDelete,
                    destructive: true
                });
            }

            return btns;
        });

        $scope.onPostDelete = function() {

            if ($scope.isProcessingDelete) {
                return;
            }

            var confirmCb = function(selected) {
                if (1 == selected) {
                    $scope.doPostDelete();
                }
            };

            $modal.confirm(gettextCatalog.getString('Are you sure to delete this post?'), confirmCb, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.doPostDelete = function() {

            $scope.isProcessingDelete = true;

            var postData = {
                iPostId: $scope.post.getId()
            };

            $http2.post('forum/postdelete', postData).success($scope.doDeleteSuccess).error($scope.doDeleteError).
            finally(function() {
                $scope.isProcessingDelete = false;
            });
        };

        $scope.doDeleteSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.posts.deleteItem($scope.post.getId());

            if (!$scope.posts.length) {
                $history.splice();
                if ($scope.post.getForumId()) {
                    $location.path('app/forum/' + $scope.post.getForumId());
                } else {
                    $scope.goBack();
                }
            }
        };

        $scope.doDeleteError = function() {

            console.warn('doPostDelete', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
    };
});