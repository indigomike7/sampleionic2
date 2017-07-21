define([
    'group/model/group',
    'global/base/ItemController',
    'text!tpl/group/group-invite-list.html'
],function(Model, Ctrl){
    return function($scope, $site, $ionicModal, $injector, gettext, gettextCatalog, $location, $modal, $coreSettings){
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemEdit = function(){
            $location.path('app/groups/edit/' + $scope.item.getId());
        };

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this group?'),
            'group/delete',
            function(){return {iGroupId: $scope.item.getId()};},
            function(data){
                $scope.items.deleteItem($scope.item.getId());
            });


        $scope.onItemInvite = function(){

            $scope.inviteListModal && $scope.inviteListModal.remove();

            $scope.inviteListModal = $ionicModal.fromTemplate(require('text!tpl/group/group-invite-list.html'), {
                scope: $scope
            });

            $scope.inviteListModal.show();
        };

        $scope.hideInviteList = function() {

            $scope.inviteListModal.hide();
        };

        $scope.$on('$destroy', function() {

            $scope.inviteListModal && $scope.inviteListModal.remove();
        });

        //  setting menu items

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

        return $scope;
    };
});
