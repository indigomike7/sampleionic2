define([
    'global/base/ListController',
    'user/model/user'
], function(ListCtrl, Model) {
    return function($scope, $injector, $modal, gettextCatalog, $http2, $site) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            itemModel: Model,
            apiService: 'directory/getinvitepeople',
            listData: {
                iBusinessId: $scope.item.getId()
            }
        });

        $scope.loadMore = function() {
            $http2.get($scope.apiService, $scope.listData)
                .success($scope.loadMoreSuccess)
                .error($scope.loadMoreError)
                .finally(function() {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                    $scope.canLoadMore = false;
                });
        };

        $scope.markAll = function() {
            $scope.items.forEach(function(item, i) {
                item.selected = true;
            });
        };

        $scope.unmarkAll = function() {
            $scope.items.forEach(function(item, i) {
                item.selected = false;
            });
        };

        $scope.getSelectedIds = function() {
            var ids = [];

            $scope.items.forEach(function(item, i) {
                if (item.selected) {
                    ids.push(item.getId());
                }
            });

            return ids;
        };

        $scope.isValidData = function(bAlert) {
            var ids = $scope.getSelectedIds();

            if (!ids.length) {
                bAlert && $modal.alert(gettextCatalog.getString('Please select friend to invite.'));
                return false;
            }

            return true;
        };

        $scope.onInvite = function() {
            if ($scope.isProcessing) {
                return;
            }

            if (!$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            var postData = {
                iBusinessId: $scope.item.getId(),
                sUserIds: $scope.getSelectedIds().join(',')
            };

            var config = {
                timeout: 0 // wait until complete this action
            };

            $http2.post('directory/invite', postData, config)
                .success($scope.inviteSuccess)
                .error($scope.inviteError)
                .finally(function() {
                    $scope.isProcessing = false;
                });
        };

        $scope.inviteSuccess = function(data) {
            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Invited member(s) successfully.'));
            $scope.hideInviteModal();
        };

        $scope.inviteError = function() {
            $site.debug > 2 && console.warn('onInvite', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
    };
});
