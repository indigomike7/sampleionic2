define([
    'global/base/ItemController',
    'text!tpl/marketplace/listing-invite-list.html'
], function(ItemController, inviteListTemplate) {
    return function($scope, $injector, gettext, gettextCatalog, $modal, $site, $http2, $rootScope, $location, $ionicModal) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];

            if (!$scope.item.isOwner()) {
                btns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport
                });
            }

            if ($scope.item.canEdit()) {
                btns.push({
                    text: gettextCatalog.getString('Edit Listing'),
                    action: function() {
                        $location.path('app/listing/' + $scope.item.getId() + '/edit');
                    }
                });

                btns.push({
                    text: gettextCatalog.getString('Edit Photos'),
                    action: function() {
                        $location.path('app/listing/' + $scope.item.getId() + '/edit/photo');
                    }
                });
            }

            if ($scope.item.isOwner()) {
                btns.push({
                    text: gettextCatalog.getString('Invite'),
                    action: $scope.showInviteList
                });
            }

            if ($scope.item.canDelete()) {
                btns.push({
                    text: gettextCatalog.getString('Delete Listing'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return btns;
        });

        $scope.showInviteList = function() {

            $scope.removeInviteList();

            $scope.inviteListModal = $ionicModal.fromTemplate(inviteListTemplate, {
                scope: $scope
            });

            $scope.inviteListModal.show();
        };

        $scope.hideInviteList = function() {

            $scope.inviteListModal && $scope.inviteListModal.hide();
        };

        $scope.removeInviteList = function() {

            $scope.inviteListModal && $scope.inviteListModal.remove();
        };

        $scope.$on('$destroy', function() {

            $scope.removeInviteList();
        });

        $scope.onItemDelete = function() {

            if ($scope.isProcessingDelete) {
                return;
            }

            var confirmCb = function(selected) {
                if (1 == selected) {
                    $scope.doItemDelete();
                }
            };

            $modal.confirm(gettextCatalog.getString('Are you sure to delete this listing?'), confirmCb, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.doItemDelete = function() {

            $scope.isProcessingDelete = true;

            var postData = {
                iListingId: $scope.item.getId()
            };

            $http2.post('marketplace/delete', postData)
                .success($scope.doDeleteSuccess)
                .error($scope.doDeleteError)
                .
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

            $scope.items.deleteItem($scope.item.getId());
        };

        $scope.doDeleteError = function() {

            console.warn('doItemDelete', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };
    };
});