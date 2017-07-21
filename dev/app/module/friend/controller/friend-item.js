define([
    'global/base/ItemController',
    'global/viewer',
],function(ItemController, Viewer){
    return function($scope, $injector, $http2, gettext, $location, $modal, $site, gettext, gettextCatalog, $state){
        $injector.invoke(ItemController, this, {$scope: $scope});

        $scope.onItemSetting= $scope._setting($scope,function(){

            var btns = new Array();
            var isOwner =  $scope.item.isOwner();console.log('isOwner', isOwner);
            var isFriend = $scope.item.isFriend;console.log('isFriend', isFriend);

            if(!isOwner && isFriend){
                btns.push({
                    text: gettextCatalog.getString('Send message'),
                    action: $scope.onItemSendMessage
                });
                btns.push({
                    text: gettextCatalog.getString('Remove Friend'),
                    action: $scope.onRemoveFriend,
                    destructive: true
                });
            }



            if(!isOwner && !isFriend){
                if($scope.item.isSentRequest){
                    btns.push({
                        text: gettextCatalog.getString('Cancel Request'),
                        action: $scope.onCancelFriendRequest
                    });
                }else if($scope.item.isSentRequestBy){
                    btns.push({
                        text: gettextCatalog.getString('Accept Friend Request'),
                        action: $scope.onAcceptFriendRequest
                    });
                    btns.push({
                        text: gettextCatalog.getString('Deny Friend Request'),
                        action: $scope.onDenyFriendRequest
                    });
                }else{
                    btns.push({
                        text: gettextCatalog.getString('Add Friend'),
                        action: $scope.onAddFriendRequest
                    });
                };
            };


            return btns;
        });

        $scope.onAddFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to add this member to your friend list'),
            'friend/add',
            function(){return {
                iUserId: $scope.item.getId()
            };},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed!'));
                $.extend($scope.item,{
                    isSentRequest: true,
                    isSentRequestBy: false
                });
            });

        $scope.onRemoveFriend = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to remove this member from your friend list'),
            'friend/delete',
            function(){return {
                iUserId: $scope.item.getId()
            };},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed!'));
                $.extend($scope.item,{
                    isFriend: false,
                    isSentRequest: false,
                    isSentRequestBy: false
                });
                if ($state.current.url == '/friends') { // Friend list
                    $scope.items.deleteItem($scope.item.getId());
                }
            });

        $scope.onAcceptFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to accept this friend request?'),
            'friend/confirm',
            function(){return {
                iUserId: $scope.item.getId()
            };},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed!'));
                $.extend($scope.item,{
                    isFriend: true,
                    isSentRequest: false,
                    isSentRequestBy: false,
                    isBlocked: false
                });
            });

        $scope.onDenyFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to deny this friend request?'),
            'friend/deny',
            function(){return {
                iUserId: $scope.item.getId()
            };},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed!'));
                $.extend($scope.item,{
                    isFriend: false,
                    isSentRequest: false,
                    isSentRequestBy: false
                });
            });

        $scope.onCancelFriendRequest = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to cancel friend request to this member?'),
            'friend/cancelrequest',
            function(){return {
                iUserId: $scope.item.getId()
            };},
            function(){
                $modal.toast(gettextCatalog.getString('Action completed!'));
                $.extend($scope.item,{
                    isFriend: false,
                    isSentRequest: false,
                    isSentRequestBy: false
                });
            });



        $scope.onBlockUser = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to block this member?'),
            'user/block',
            function() {
                return {
                    iUserId: $scope.item.getId()
                };
            },
            function() {
                $modal.toast(gettextCatalog.getString('Block successfully'));
                $.extend($scope.item, {
                    isBlocked: true,
                    isFriend: false
                });
            }
        );


        $scope.onUnblockUser = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to un-block this member?'),
            'user/unblock',
            function() {
                return {
                    iUserId: $scope.item.getId()
                };
            },
            function() {
                $modal.toast(gettextCatalog.getString('Un-Block successfully'));
                $.extend($scope.item, {
                    isBlocked: false,
                    isFriend: false,
                    isSentRequest: false,
                    isSentRequestBy: false
                });
            }
        );
    };
});
