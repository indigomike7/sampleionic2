define([
    'group/model/group',
    'group/controller/group-item',
    'text!tpl/group/group-member-list-modal.html'
], function(Model, Ctrl, GroupMemberListModal) {
    return function($scope, $state, $injector, $http2, $modal, gettext, $dislike, 
        gettextCatalog, $location, $ionicModal, $coreSettings, $ionicHistory) {

        var iGroupId =  $state.params.iGroupId;

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.membersGroup = {};

        $scope.item =  $.extend({
            iGroupId: iGroupId
        }, Model);

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this group?'),
            'groups/delete',
            function(){return {iGroupId: $scope.item.getId()};},
            function(data){
                $scope.goBack();
            });

        $scope.fetchData =  function(){

            var sendData = {
                iGroupId: $scope.item.getId()
                //fields:'id,timestamp,category,title,desc,imgNormal,imgFull,members,photos,actions,user,perms,statistics'
            };

            $http2.get('groups/info',sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    }else{
                        $.extend($scope.item, data);
                        if ($scope.item.bCanView) {
                            angular.element('.tab-hidden').removeClass('tab-hidden');
                        }
                        $scope.dataReady = true;

                        $scope.setVisibleTabs();
                    }
                })
                .error(function(){
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                    $scope.goBack();
                })
                .finally(function(){
                    $scope.isProcessingLike = false;
                });
        };

        $scope.setVisibleTabs = function() {
            $scope.is_show_info = true;
            $scope.is_show_activity = true;
            $scope.is_show_events = $scope.item.bEnabledEvents;
            $scope.is_show_photos = $scope.item.bEnabledPhotos;
        };

        $scope.likeGroup = function(){

            if ($scope.isProcessingLike) {
                return;
            }

            $scope.isProcessingLike = true;

            var isSignup = ($scope.item.iGroupType == '1' && $scope.item.iRegMethod == '1');
            var api = isSignup ? 'groups/signup' : 'like/like';

            function doLikeSuccess(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }else{
                    if (data.message && isSignup) {
                        $modal.alert(data.message);
                    }
                    $scope.fetchData();
                }
            }

            function doLikeError() {
                console.warn('likeError', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            var likeData = {
                iItemId: $scope.item.getId(),
                sItemType: 'groups'
            };

            $http2.post(api, likeData)
                .success(doLikeSuccess)
                .error(doLikeError)
                .finally(function() {
                    $scope.isProcessingLike = false;
                });
        };

        $scope.unlikeGroup = function(){

            if ($scope.isProcessingLike) {
                return;
            }

            $scope.isProcessingLike = true;

            var api = 'like/unlike';

            function doLikeSuccess(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }else{
                    // if (data.message) {
                    //     $modal.toast(data.message);
                    // }
                    $scope.fetchData();
                }
            }

            function doLikeError() {
                console.warn('likeError', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            var likeData = {
                iItemId: $scope.item.getId(),
                sItemType: 'groups'
            };

            $http2.post(api, likeData)
                .success(doLikeSuccess)
                .error(doLikeError)
                .finally(function() {
                    $scope.isProcessingLike = false;
                });
        };

        $scope.showMembers = function(sView) {
            switch (sView) {
                case 'liked':
                    $scope.membersGroup = {
                        title: gettextCatalog.getString('Members'),
                        sub_title: gettextCatalog.getString('MEMBERS'),
                        items: $scope.item.aMembers,
                        icon: 'ion-ios-people'
                    }
                    break;
                case 'admin':
                    $scope.membersGroup = {
                        title: gettextCatalog.getString('Group admins'),
                        items: $scope.item.aAdmins,
                        sub_title: gettextCatalog.getString('ADMIN'),
                        icon: 'ion-person'
                    }
                    break;
            }

            if (!$scope.membersGroup.items.length) {
                return;
            }

            if (!$scope.groupMembersModal) {
                $scope.groupMembersModal = $ionicModal.fromTemplate(GroupMemberListModal, {
                    scope : $scope
                });
            }

            $scope.groupMembersModal.show();
        }

        $scope.hideMemberListModal = function(){

            $scope.groupMembersModal.hide();
        }

        $scope.onItemSetting = $scope._setting($scope, function(){
            var btns = [];

            if ($coreSettings.get('like_allow_dislike')) {

                if ($scope.item.getTotalDislike() > 0) {
                    btns.push({
                        text: $dislike.getDislikeStat($scope.item),
                        action: $scope.onViewDisliked
                    });
                }

                if ($scope.item.canDislike()) {
                    btns.push({
                        text: $scope.item.isDisliked() ? gettextCatalog.getString('Remove Dislike') : gettextCatalog.getString('Dislike'),
                        action: $scope.onItemDislike
                    });
                }
            }

            if($scope.item.canInvite() || $scope.item.isOwner()){
                btns.push({
                    text:gettextCatalog.getString('Invite'),
                    action: $scope.onItemInvite
                });
            }

            btns.push({
                text: gettextCatalog.getString('Share'),
                action: $scope.onItemShare
            });

            if($scope.item.canEdit()) {
                btns.push({
                    text: gettextCatalog.getString('Manage Group'),
                    action: $scope.onGroupManageMenu
                });
            }

            if($scope.item.canDelete()){
                btns.push({
                    text:gettextCatalog.getString('Delete'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            return btns;
        });

        $scope.onGroupManageMenu = $scope._setting($scope, function() {
            var btns = [];

            btns.push({
                text:gettextCatalog.getString('Edit Group Info'),
                action: $scope.onItemEdit
            });

            btns.push({
                text:gettextCatalog.getString('Edit Group Photo'),
                action: function() {
                    $location.path('app/groups/edit-avatar/' + $scope.item.getId());
                    return true;
                }
            });

            btns.push({
                text:gettextCatalog.getString('Edit Group Cover'),
                action: function() {
                    $location.path('app/groups/edit-cover/' + $scope.item.getId());
                    return true;
                }
            });

            return btns;
        });

        $scope.goToPath = function(path) {
            $ionicHistory.nextViewOptions({
                disableAnimate: true
            });
            $location.path(path);
        };

        $scope.fetchData();

        $scope.$on('$destroy', function(){

            $scope.groupMembersModal && $scope.groupMembersModal.remove();
        });

        return $scope;
    };
});
