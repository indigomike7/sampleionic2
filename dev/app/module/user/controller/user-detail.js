define([
    'friend/controller/friend-item',
    'user/model/user',
], function(Ctrl, Model) {

    return function($scope, $injector, $state, $modal, $http2, $site, gettextCatalog, $viewer, $location, $q, $coreSettings) {

        var iUserId = $state.params.iUserId;

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        $scope.bAllowShowPoints = $coreSettings.get('bAllowShowPoints');
        $scope.item = $.extend({
            iUserId: iUserId
        }, Model);

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];

            var isOwner = ($scope.item.getId() == $viewer.get('iUserId'));
            var isBlocked = $scope.item.isBlocked;
            var isFriend = $scope.item.isFriend;

            if (isOwner) {
                btns.push({
                    text: gettextCatalog.getString('Edit Personal Info'),
                    action: function() {
                        $location.path('app/user/edit-info/' + $scope.item.getId());
                        return true;
                    }
                });

                btns.push({
                    text: gettextCatalog.getString('Edit My Photo'),
                    action: function() {
                        $location.path('app/user/edit-avatar/' + $scope.item.getId());
                        return true;
                    }
                });

                if ($site.getPerm('profile.can_change_cover_photo')) {
                    btns.push({
                        text: gettextCatalog.getString('Edit Cover Photo'),
                        action: function() {
                            $location.path('app/user/edit-cover/' + $scope.item.getId());
                            return true;
                        }
                    });
                }
            }

            if (!isOwner)
                btns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport
                });

            if (!isOwner && !isBlocked)
                btns.push({
                    text: gettextCatalog.getString('Block'),
                    action: $scope.onBlockUser
                });

            if (!isOwner && isBlocked)
                btns.push({
                    text: gettextCatalog.getString('Un-Block'),
                    action: $scope.onUnblockUser
                });

            if ($scope.item.bCanSendMessage) {
                btns.push({
                    text: gettextCatalog.getString('Send Message'),
                    action: $scope.onItemSendMessage
                });
            }

            if (!isOwner && isFriend) {
                btns.push({
                    text: gettextCatalog.getString('Remove Friend'),
                    action: $scope.onRemoveFriend,
                    destructive: true
                });
            }

            if (!isOwner && !isFriend) {
                if ($scope.item.isSentRequest) {
                    btns.push({
                        text: gettextCatalog.getString('Cancel Request'),
                        action: $scope.onCancelFriendRequest
                    });
                } else if ($scope.item.isSentRequestBy) {
                    btns.push({
                        text: gettextCatalog.getString('Accept Friend Request'),
                        action: $scope.onAcceptFriendRequest
                    });
                    btns.push({
                        text: gettextCatalog.getString('Deny Friend Request'),
                        action: $scope.onDenyFriendRequest
                    });
                } else {
                    btns.push({
                        text: gettextCatalog.getString('Add Friend'),
                        action: $scope.onAddFriendRequest
                    });
                }
            }

            return btns;
        });

        $scope.onEditAvatar = function() {

            var location = 'app/user/edit-avatar/' + $scope.item.getId();
            $scope.getPicture(location);
        };

        $scope.onEditCover = function() {

            var location = 'app/user/edit-cover/' + $scope.item.getId();
            $scope.getPicture(location);
        };

        $scope.onVisitFriends =  function(){
            if($scope.item.canView()){
                $location.path('app/user_friends/' + $scope.item.getId());
            }
        };

        $scope.onVisitPhotos =  function(){
            if($scope.item.canView()){
                $location.path('app/user_photos/' + $scope.item.getId());
            }
        };

        $scope.getPicture = function(location) {

            var success = function(fileURI) {
                $scope.$$phase || $scope.$apply();
                $location.path(location + '/' + btoa(fileURI));
            };

            var fail = function(msg) {
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
                $scope.$$phase || $scope.$apply();
            };

            if (typeof(navigator.camera) != 'undefined') {
                navigator.camera.getPicture(success, fail, {
                    quality: 50,
                    destinationType: Camera.DestinationType.FILE_URI,
                    sourceType: Camera.PictureSourceType.PHOTOLIBRARY,
                    encodingType: Camera.EncodingType.JPEG,
                    mediaType: Camera.MediaType.PICTURE,
                    correctOrientation: true,
                    targetWidth: $site.imgTargetSize,
                    targetHeight: $site.imgTargetSize
                });
            }
        };

        $scope.loadInit = function(iUserId) {

            $scope.iUserId = iUserId;

            var sendData = {
                iUserId: iUserId
            };

            $http2.get('profile/detail', sendData)
                .success(function(data) {
                    // extend user item by BasicInfo
                    $.extend($scope.item, data.BasicInfo);
                    // get About me field
                    $scope.item.about_me = data.About_Me.About_Me || '';

                    $scope.getAdditionalInfo();

                    $scope.dataReady = true;
                })
                .error(function() {
                    console.log(arguments);
                })
                .finally(function() {});
        };

        // get sample friends and photo
        $scope.getAdditionalInfo = function() {

            var sendDataFriend = {
                iUserId: iUserId,
            };

            var sendDataPhoto = {
                iUserId: iUserId,
                sOrder: 'latest',
                bIsUserProfile: true
            };

            $q.all([
                $http2.get('friend/get', sendDataFriend),
                $http2.get('photo/filter', sendDataPhoto)
            ]).then(function (data) {

                for (var i = 0,len = data.length; i < len; ++i) {
                    if (data[i].error_code){
                        return;
                    }
                }
                // assign data to item
                $scope.item.aFriends = data[0].data;
                $scope.item.aPhotos = data[1].data;
            });
        };

        $scope.loadInit($state.params.iUserId);
    };
});
