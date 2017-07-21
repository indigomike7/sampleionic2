define([
    'user/model/user',
    'global/base/ListController'
], function(Model, ListCtrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {

        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            itemModel: Model,
            apiService: 'marketplace/getinvitepeople',
            listData: {
                iListingId: $scope.item.getId(),
                amountOfFriend: 9999
            }
        });

        $scope.loadMore = function() {

            $http2.post($scope.apiService, $scope.listData)
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
                iListingId: $scope.item.getId(),
                sUserId: $scope.getSelectedIds().join(',')
            };

            var config = {
                method: 'POST',
                url: 'marketplace/invite',
                data: postData,
                timeout: 0 // wait until complete this action
            };

            $http2.post('marketplace/invite',postData, {timeout:0})
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
            $scope.hideInviteList();
        };

        $scope.inviteError = function() {

            console.error('onInvite', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
    };
});