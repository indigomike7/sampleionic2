define(function() {
    return function($scope, $modal, gettextCatalog, $http2, $viewer) {
        $scope.shareData = {
            iItemId: $scope.item.getParentModuleId() ? $scope.item.getItemId() : $scope.item.getId(),
            sItemType: $scope.item.getParentModuleId() ? $scope.item.getItemType() : $scope.item.getType(),
            sContent: '',
            iPostType: 2 // 1: On your wall, 2: On a friend's wall
        };

        $scope.searchFriends = {
            iUserId: $viewer.get('iUserId'),
            sSearch: '',
            iPage: 1,
            iAmountOfFriend: 20
        };

        $scope.selectedFriends = [];

        $scope.selectFriend = function(friend) {
            if (!$scope.isSelectedFriend(friend)) {
                $scope.selectedFriends.push(friend);
            }

            $scope.searchFriends.sSearch = '';
        };

        $scope.deselectFriend = function(friend) {
            var index = $scope.getSelectedFriendIds().indexOf(parseInt(friend.getId()));

            if (index > -1) {
                $scope.selectedFriends.splice(index, 1);
            }
        };

        $scope.isSelectedFriend = function(friend) {
            if ($scope.getSelectedFriendIds().indexOf(parseInt(friend.getId())) > -1) {
                return true;
            }

            return false;
        };

        $scope.getSelectedFriendIds = function() {
            var ids = [];

            for (var i = 0; i < $scope.selectedFriends.length; i++) {
                ids.push(parseInt($scope.selectedFriends[i].getId()));
            }

            return ids;
        };

        $scope.isValidData = function(bAlert) {
            if (!$scope.getSelectedFriendIds().length) {
                bAlert && $modal.alert(gettextCatalog.getString('Please choose a recipient'));
                return false;
            }

            if (!$scope.shareData.sContent) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter the message'));
                return false;
            }

            return true;
        };

        $scope.onSave = function() {
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.save();
        };

        $scope.save = function() {
            $scope.isProcessing = true;

            $scope.shareData.sFriends = $scope.getSelectedFriendIds().join(',');

            $http2.post('feed/share', $scope.shareData).success($scope.doSaveSuccess).error($scope.doSaveError).finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveSuccess = function(data) {
            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            if (data.message) {
                $modal.toast(data.message);
            }

            $scope.hideShareOnFriendWallModal();
        };

        $scope.doSaveError = function() {
            console.error('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
    };
});
