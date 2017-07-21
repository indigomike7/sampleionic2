define([
    'user/model/user',
    'global/base/ListController'
], function(Model, Ctrl) {

    return function($scope, $injector, $modal, gettext, gettextCatalog, $http2, $site) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            itemModel: Model,
            apiService: 'groups/getinvitepeople',
            listById: false,
            listData: {
                iGroupId: $scope.item.getId(),
                sAction: "all",
                amountOfFriend: "9999",
                iGroup: 1,
                LastFriendIdViewed: "0"
            }
        });

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
                    ids.push(item.id);
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
                iGroupId: $scope.item.getId(),
                sUserId: $scope.getSelectedIds().join(','),
            };

            $http2.post('groups/invite', postData, {timeout: 0})
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

            $modal.toast(data.message || gettextCatalog.getString('Invited successfully.'));
            $scope.hideInviteList();
        };

        $scope.inviteError = function() {

            console.error('onInvite', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
        
        $scope.loadMore();
    };
});