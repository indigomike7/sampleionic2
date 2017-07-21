define([
    'global/base/ItemController'
], function (Ctrl) {
    return function ($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal) {
        $site.debug > 2 && console.log('UltimateVideoItemVideoController');
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemSetting = $scope._setting($scope, function () {

            var btns = [];

            if($scope.videoItemOptions.indexOf('share') >= 0) {
                btns.push({
                    text: gettextCatalog.getString('Share'),
                    action: $scope.onItemShare
                });
            }

            if($scope.videoItemOptions.indexOf('watch_later') >= 0) {
                if (!$scope.item.bIsWatchLater) {
                    btns.push({
                        text: gettextCatalog.getString('Watch later'),
                        action: $scope.onItemAddWatchLater
                    });
                } else {
                    btns.push({
                        text: gettextCatalog.getString('Unwatched'),
                        action: $scope.onItemDeleteWatchLater
                    });
                }
            }

            if($scope.videoItemOptions.indexOf('favorite') >= 0) {
                if (!$scope.item.bIsFavorite) {
                    btns.push({
                        text: gettextCatalog.getString('Favorite'),
                        action: $scope.onItemAddFavorite
                    });
                } else {
                    btns.push({
                        text: gettextCatalog.getString('Unfavorite'),
                        action: $scope.onItemDeleteFavorite
                    });
                }
            }

            if($scope.videoItemOptions.indexOf('add_to_playlist') >= 0) {
                btns.push({
                    text: gettextCatalog.getString('Add to playlist'),
                    action: $scope.onItemAddToPlaylist
                });
            }

            if($scope.videoItemOptions.indexOf('delete') >= 0) {
                if ($scope.item.canDelete()) {
                    btns.push({
                        text: gettextCatalog.getString('Delete this video'),
                        action: $scope.onItemDelete,
                        destructive: true
                    });
                }
            }

            if($scope.videoItemOptions.indexOf('edit') >= 0) {
                if ($scope.item.canEdit()) {
                    btns.push({
                        text: gettextCatalog.getString('Edit this video'),
                        action: $scope.onItemEdit,
                    });
                }
            }

            if($scope.videoItemOptions.indexOf('report') >= 0) {
                if (!$scope.item.isOwner()) {
                    btns.push({
                        text: gettextCatalog.getString('Report this video'),
                        action: $scope.onItemReport
                    });
                }
            }

            if($scope.videoItemOptions.indexOf('delete_history') >= 0) {
                btns.push({
                    text: gettextCatalog.getString('Remove'),
                    action: $scope.onDeleteHistory,
                });
            }


            return btns;
        });

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this video?'),
            'ultimatevideo/delete',
            function () {
                return {iVideoId: $scope.item.getId()};
            },
            function (data) {
                $modal.toast(data.message);
                $scope.items.deleteItem($scope.item.getId());
                $scope.$emit('video:deleted', $scope.item.getId());
            }
        );

        $scope.onDeleteHistory = function() {
            $http2.post('ultimatevideo/deleteHistory', {iItemId: $scope.item.getId(), iType: 0})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.items.deleteItem($scope.item.getId());
                        $modal.toast('This video has been removed from your History list.');
                    }
                }).error(function () {
                console.error('removeHistory', arguments);
            }).finally(function () {

            });
        };

        $scope.onItemEdit = function () {
            $location.path('app/ultimatevideo/edit/video/' + $scope.item.iVideoId);
        };

        $scope.onItemRate = $scope._rate($scope, function () {
            return {
                sItemType: $scope.item.getType(),
                iItemId: $scope.item.getId(),
                iRating: 0
            };
        });

        $scope.onItemAddWatchLater = function () {
            $http2.post('ultimatevideo/addWatchLater', {iVideoId: $scope.item.getId()})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.item.bIsWatchLater = true;
                        $modal.toast('This video has been added to your Watch later list successfully.');
                    }
                }).error(function () {
                console.error('addWatchLater', arguments);
            }).finally(function () {

            });
        };
        $scope.onItemDeleteWatchLater = function () {
            $http2.post('ultimatevideo/deleteWatchLater', {iVideoId: $scope.item.getId()})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.item.bIsWatchLater = false;
                        if((typeof $scope.videoRemoveOptions != 'undefined') && ($scope.videoRemoveOptions.indexOf('watch_later') >= 0))
                            $scope.items.deleteItem($scope.item.getId());
                        $modal.toast('This video has been removed from your Watch later list.');
                    }
                }).error(function () {
                console.error('deleteWatchLater', arguments);
            }).finally(function () {

            });
        };

        $scope.onItemAddFavorite = function () {
            $http2.post('ultimatevideo/addFavorite', {iVideoId: $scope.item.getId()})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.item.bIsFavorite = true;
                    }
                }).error(function () {
                console.error('addFavorite', arguments);
            }).finally(function () {

            });
        };
        $scope.onItemDeleteFavorite = function () {
            $http2.post('ultimatevideo/deleteFavorite', {iVideoId: $scope.item.getId()})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        $scope.item.bIsFavorite = false;
                        if((typeof $scope.videoRemoveOptions != 'undefined') && ($scope.videoRemoveOptions.indexOf('favorite') >= 0))
                            $scope.items.deleteItem($scope.item.getId());

                    }
                }).error(function () {
                console.error('deleteFavorite', arguments);
            }).finally(function () {

            });
        };

        $scope.removeFromPlaylist = function () {
            $scope.formData.aRemoveVideos.push($scope.item.getId());
            $scope.items.deleteItem($scope.item.getId());
        };

        $scope.onItemAddToPlaylist = function () {
            $scope.goToPage('app/ultimatevideo/add-to-playlist/' + $scope.item.iVideoId);
        };

    };

});
