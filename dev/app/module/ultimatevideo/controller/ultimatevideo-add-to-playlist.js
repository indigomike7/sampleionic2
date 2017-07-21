define([
    'global/validator',
    'global/base/BaseController',
], function (Validator, Ctrl) {
    return function ($file, $injector, $scope, $ionicPopup, $viewer, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('UltimateVideoAddToPlaylistCtrl');
        $scope.canCreatePlaylist = true;
        $scope.iVideoId = $state.params.iVideoId;
        $scope.dataReady = false;
        $scope.isProcessing = false;
        $scope.noItems = false;
        $scope.data = {
            bIsFavorite: false
        };
        $scope.loadInit = function () {

            $http2.post('ultimatevideo/getAllPlaylistOfUser', {
                bPrivacyNoCustom: true,
                iVideoId: $scope.iVideoId
            })
                .success(function (data) {
                    if (data.error_code) {
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    } else {
                        $scope.dataReady = true;
                        $scope.data.aPlaylists = data.aPlaylists;
                        $scope.data.bIsFavorite = data.bIsFavorite;
                        if($scope.data.aPlaylists.length == 0)
                            $scope.noItems = true;
                    }

                }).error(function () {

            });
        };
        $scope.updatePlaylistVideo = function (index) {
            var apiUrl = 'ultimatevideo/addVideoToPlaylist';
            if (!$scope.data.aPlaylists[index].bIsAdded)
                apiUrl = 'ultimatevideo/removeVideoFromPlaylist';
            $http2.post(apiUrl, {
                bPrivacyNoCustom: true,
                iVideoId: $scope.iVideoId,
                iPlaylistId: $scope.data.aPlaylists[index].iPlaylistId,
            })
                .success(function (data) {
                    if (data.error_code) {
                        $modal.alert(data.error_message);
                    } else {
                        if ($scope.data.aPlaylists[index].bIsAdded)
                            $modal.toast('This video has been added to ' + $scope.data.aPlaylists[index].sTitle + ' successfully.');
                        else
                            $modal.toast('This video has been removed from ' + $scope.data.aPlaylists[index].sTitle + ' successfully.');
                    }

                }).error(function () {

            });
        };

        $scope.doAddToNewPlaylist = function () {
            $modal.prompt(gettextCatalog.getString('Add to new playlist'), function(result) {
                $scope.$$phase || $scope.$apply();

                if (result.buttonIndex == 2) {
                    return true;
                }
                if (!result.input1) {
                    $modal.toast('Please input playlist name');
                    return false;
                }
                // Get video information
                $http2.post('ultimatevideo/addPlaylistOnAction', {
                    sTitle: result.input1,
                    iVideoId: $scope.iVideoId
                })
                    .success(function(data){
                        if (data.error_code) {
                            $modal.alert(data.error_message);
                        }else{
                            $modal.toast('This video has been added to ' + result.input1 + ' successfully.');
                            $scope.data.aPlaylists.push({sTitle: result.input1, bIsAdded: true, iPlaylistId: data.iPlaylistId});
                        }
                    })
                    .error(function(){
                        console.log(arguments);
                    })
                    .finally(function(){
                        $scope.isProcessing = false;
                    });
            }, gettextCatalog.getString('Add to playlist'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };
        $scope.updateFavoriteState = function(){
            var apiUrl = 'ultimatevideo/addFavorite';
            if(!$scope.data.bIsFavorite) {
                apiUrl = 'ultimatevideo/deleteFavorite';
            }
            $http2.post(apiUrl, {iVideoId: $scope.iVideoId})
                .success(function (data) {
                    if (data.error_code) {
                        return $modal.alert(data.error_message);
                    } else {
                        if($scope.data.bIsFavorite)
                            $modal.toast('This video has been added to Favorite successfully.');
                        else $modal.toast('This video has been removed from Favorite successfully.');
                    }
                }).error(function () {
                console.error('updateFavoriteState', arguments);
            }).finally(function () {

            });
        };
        $scope.loadInit();
    };
});