define([
    'global/base/ItemController',
    'text!tpl/directory/directory-invite.html'
], function(Ctrl) {
    return function($scope, $http2, $injector, gettextCatalog, $location, $modal, $site, $ionicModal) {
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.goToDetail = function() {
            $location.path('app/directory/business/' + $scope.item.getId());
        };

        $scope.onItemSetting = function() {
            $scope._setting($scope, $scope.getActions)();
        };

        $scope.hasActions = function() {
            return $scope.getActions().length ? true : false;
        };

        $scope.getActions = function() {
            var btns = [];

            if ($scope.item.bCanLike) {
                if ($scope.item.bIsLiked) {
                    btns.push({
                        text: gettextCatalog.getString('Unlike'),
                        action: $scope.onItemLike
                    });
                } else {
                    btns.push({
                        text: gettextCatalog.getString('Like'),
                        action: $scope.onItemLike
                    });
                }
            }

            if ($scope.item.bCanShare) {
                btns.push({
                    text: gettextCatalog.getString('Share'),
                    action: $scope.onItemShare
                });
            }

            if ($scope.item.bCanFollow) {
                if ($scope.item.bIsFollowing) {
                    btns.push({
                        text: gettextCatalog.getString('Unfollow'),
                        action: $scope.unfollow
                    });
                } else {
                    btns.push({
                        text: gettextCatalog.getString('Follow'),
                        action: $scope.follow
                    });
                }
            }

            if ($scope.item.bCanFavourite) {
                if ($scope.item.bIsFavourite) {
                    btns.push({
                        text: gettextCatalog.getString('Unfavorite'),
                        action: $scope.unfavourite
                    });
                } else {
                    btns.push({
                        text: gettextCatalog.getString('Favorite'),
                        action: $scope.favourite
                    });
                }
            }

            if ($scope.item.bCanReport) {
                btns.push({
                    text: gettextCatalog.getString('Report Business'),
                    action: $scope.onItemReport
                });
            }

            if ($scope.item.bCanMessageOwner) {
                btns.push({
                    text: gettextCatalog.getString('Message Owner'),
                    action: $scope.onMessageOwner
                });
            }

            if ($scope.item.bCanManage) {
                btns.push({
                    text: gettextCatalog.getString('Edit Information'),
                    action: $scope.onEdit
                });

                btns.push({
                    text: gettextCatalog.getString('Update Cover Photos'),
                    action: $scope.onUpdateCover
                });
            }

            if ($scope.item.bCanJoin) {
                btns.push({
                    text: gettextCatalog.getString('Working Here'),
                    action: $scope.join
                });
            }

            if ($scope.item.bCanLeave) {
                btns.push({
                    text: gettextCatalog.getString('Leave This Business'),
                    action: $scope.leave
                });
            }

            if ($scope.item.bCanCheckin) {
                btns.push({
                    text: gettextCatalog.getString('Check-in Here'),
                    action: $scope.onCheckin
                });
            }

            if ($scope.item.bCanInvite) {
                btns.push({
                    text: gettextCatalog.getString('Invite Friends'),
                    action: $scope.onInvite
                });
            }

            if ($scope.item.bCanClaim) {
                btns.push({
                    text: gettextCatalog.getString('Claim This Business'),
                    action: $scope.claim
                });
            }

            return btns;
        };

        $scope.quickPost = function(api, sendData, onSuccess) {
            var _sendData = sendData || {
                iBusinessId: $scope.item.getId()
            };

            var _onSuccess = onSuccess || function(data) {
                if (data.error_code) {
                    $site.debug > 2 && console.warn(api, arguments);
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                if (data.message) {
                    $modal.toast(data.message);
                }

                angular.extend($scope.item, data.aItem);
                $scope.$broadcast('itemUpdate');
            };

            var onError = function() {
                $site.debug > 2 && console.warn(api, arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post(api, _sendData).success(_onSuccess).error(onError);
        };

        $scope.quickConfirm = function(msg, api, sendData, onSuccess) {
            $modal.confirm(msg, function(selected) {
                if (selected == 1) {
                    $scope.quickPost(api, sendData, onSuccess);
                }
            });
        };

        $scope.deleteClaim = function() {
            $scope.quickConfirm(gettextCatalog.getString('Are you sure you want to delete this claim?'), 'directory/delete_claim');
        };

        $scope.claim = function() {
            $scope.quickPost('directory/claim');
        };

        $scope.join = function() {
            $scope.quickPost('directory/join');
        };

        $scope.leave = function() {
            $scope.quickPost('directory/leave');
        };

        $scope.unfollow = function() {
            $scope.quickPost('directory/unfollow');
        };

        $scope.follow = function() {
            $scope.quickPost('directory/follow');
        };

        $scope.unfavourite = function() {
            $scope.quickPost('directory/unfavourite');
        };

        $scope.favourite = function() {
            $scope.quickPost('directory/favourite');
        };

        $scope.onInvite = function() {
            $scope.inviteModal && $scope.inviteModal.remove();

            $scope.inviteModal = $ionicModal.fromTemplate(require('text!tpl/directory/directory-invite.html'), {
                scope: $scope
            });

            $scope.inviteModal.show();
        };

        $scope.hideInviteModal = function() {
            $scope.inviteModal.hide();
        };

        $scope.$on('$destroy', function() {
            $scope.inviteModal && $scope.inviteModal.remove();
        });

        $scope.onCheckin = function() {
            $scope.quickPost('directory/checkin');
        };

        $scope.onEdit = function() {
            $location.path('app/directory/edit/' + $scope.item.getId());
        };

        $scope.onUpdateCover = function() {
            $location.path('app/directory/edit/' + $scope.item.getId() + '/cover');
        };

        $scope.close = function() {
            $scope.quickConfirm(gettextCatalog.getString('Are you sure you want to close this business?'), 'directory/close');
        };

        $scope.open = function() {
            $scope.quickConfirm(gettextCatalog.getString('Are you sure you want to published this business?'), 'directory/open');
        };

        $scope.onMessageOwner = function() {
            $location.path('app/messages/add/directory/' + $scope.item.getId() + '/' + $scope.item.getPosterId() + '/' + encodeURI($scope.item.getPosterTitle()));
        };

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure you want to delete this business?'),
            'directory/delete',
            function() {
                return {
                    iBusinessId: $scope.item.getId()
                };
            },
            function(data) {
                $scope.items.deleteItem($scope.item.getId());
            }
        );
    };
});
