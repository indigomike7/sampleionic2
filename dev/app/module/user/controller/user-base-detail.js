define([
    'user/controller/user-item',
    'user/model/user',
    'global/viewer',
], function(Ctrl, Model){

    var Viewer = require('global/viewer');

    return function($scope, $injector, $state, $modal, $http2, $site, gettext, gettextCatalog, $viewer, $location){

        var iUserId =  $state.params.iUserId;

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.onItemSetting = $scope._setting($scope, function(){

            var btns  = new Array();

            var isOwner = ($scope.item.iUserId ==  $viewer.get('iUserId'))?true:false;
            var isBlocked =  $scope.item.isBlocked;
            var isFriend = $scope.item.isFriend;

            if(isOwner)
                btns.push({
                    text: gettextCatalog.getString('Edit Personal Info'),
                    action: function(){
                        $location.path('app/user/edit-info/' + $scope.item.iUserId);
                        return true;
                    }
                });

            if(isOwner)
                btns.push({
                    text: gettextCatalog.getString('Edit My Photo'),
                    action: function(){
                        $location.path('app/user/edit-photo/' + $scope.item.iUserId);
                        return true;
                    }
                });


            if(!isOwner)
                btns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport,
                });

            if(!isOwner && !isBlocked)
                btns.push({
                    text: gettextCatalog.getString('Block'),
                    action: $scope.onDetailBlock,
                });

            if(!isOwner && isBlocked)
                btns.push({
                    text: gettextCatalog.getString('Un-Block'),
                    action: $scope.onDetailUnBlock,
                });

            if(!isOwner && isFriend){
                btns.push({
                    text: gettextCatalog.getString('Remove Friend'),
                    action: $scope.onDetailRemoveFriend,
                    destructive: true,
                });
            }

            if(!isOwner){
                btns.push({
                    text: gettextCatalog.getString('Send Message'),
                    action: $scope.onItemSendMessage,
                });
            }


            if(!isOwner && !isFriend){
                if($scope.item.isSentRequest){
                    btns.push({
                        text: gettextCatalog.getString('Cancel Request'),
                        action: $scope.onDetailCancelFriendRequest,
                    });
                }else if($scope.item.isSentRequestBy){
                    btns.push({
                        text: gettextCatalog.getString('Accept Friend Request'),
                        action: $scope.onDetailDenyFriendRequest,
                    });
                    btns.push({
                        text: gettextCatalog.getString('Deny Friend Request'),
                        action: $scope.onDetailAcceptFriendRequest,
                    });
                }else{
                    btns.push({
                        text: gettextCatalog.getString('Add Friend'),
                        action: $scope.onDetailAddFriendRequest,
                    });
                };
            }



            return btns;
        });

        $scope.onDetailAddFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to add this member to your friend list'),
            'friend/add',
            function(){return {iUserId: $scope.item.getId()}},
            function(data){
                $modal.toast(gettextCatalog.getString('Action completed'));
                $.extend($scope.item,{
                    isSentRequest: 1,
                    isSentRequestBy: 0
                });
            });

        $scope.onDetailRemoveFriend = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to remove this member from your friend list'),
            'friend/delete',
            function(){return {iUserId: $scope.item.getId()}},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed'));
                $.extend($scope.item,{
                    isFriend: 0,
                    isSentRequest: 0,
                    isSentRequestBy: 0
                });
            });

        $scope.onDetailAcceptFriendFriend = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to accept this friend request?'),
            'friend/confirm',
            function(){return {iUserId: $scope.item.getId()}},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed'));
                $.extend($scope.item,{
                    isFriend: 1,
                    isSentRequest: 0,
                    isSentRequestBy: 0
                });
            });

        $scope.onDetailDenyFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to deny this friend request?'),
            'friend/deny',
            function(){return {iUserId: $scope.item.getId()}},
            function(){
                $.extend($scope.item,{
                    isFriend: 0,
                    isSentRequest: 0,
                    isSentRequestBy: 0
                });
            });

        $scope.onDetailCancelFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to cancel friend request to this member?'),
            'friend/cancelrequest',
            function(){return {iUserId: $scope.item.getId()}},
            function(){
                $.extend($scope.item,{
                    isFriend: 0,
                    isSentRequest: 0,
                    isSentRequestBy: 0
                });
            });

        $scope.onDetailBlock  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to block this member?'),
            'user/block',
            function(){return {iUserId: $scope.item.getId()}},
            function(){
                $modal.toast(gettextCatalog.getString('Block successfully'));
                $.extend($scope.item, {
                    isBlocked: 1,
                    isFriend: 0,
                    isSentRequest: 0,
                    isSentRequestBy: 0
                });
            }
        );


        $scope.onDetailUnBlock  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to un-block this member?'),
            'user/unblock',
            function(){return {iUserId: $scope.item.getId()}},
            function(){
                $modal.toast(gettextCatalog.getString('Un-Block successfully'));
                $.extend($scope.item, {
                    isBlocked: false,
                    isFriend: false,
                    isSentRequest: false,
                    isSentRequestBy: false
                });
            }
        );

        $scope.loadInit = function(iUserId){

            $scope.item = $.extend({},Model);

            $scope.iUserId =  iUserId;

            $http2.get('profile/detail',{iUserId: iUserId})
                .success(function(data){
                    $.extend($scope.item, data.BasicInfo);

                    if(data.Details)
                        $scope.item.detail = data.Details;

                    if(data.About_Me && data.About_Me.About_Me)
                        $scope.item.about_me = data.About_Me.About_Me;
                })
                .error(function(){
                    console.log(arguments);
                })
                .finally(function(){
                    $scope.dataReady = true;
                });
        };
    };
});
