define([
    'page/model/page',
    'global/base/ItemController',
    'text!tpl/page/page-invite-list.html'
],function(Model, Ctrl){
    return function($scope, $site, $ionicModal, $injector, gettext, gettextCatalog, $location, $modal, $coreSettings){
        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemEdit = function(){
            $location.path('app/pages/edit/' + $scope.item.getId());
        };

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this page?'),
            'page/delete',
            function(){return {iPageId: $scope.item.getId()};},
            function(data){
                $scope.items.deleteItem($scope.item.getId());
            });


        $scope.onItemInvite = function(){

            $scope.inviteListModal && $scope.inviteListModal.remove();

            $scope.inviteListModal = $ionicModal.fromTemplate(require('text!tpl/page/page-invite-list.html'), {
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
                    text: gettextCatalog.getString('Manage Page'),
                    action: $scope.onPageManageMenu
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

        $scope.onPageManageMenu = $scope._setting($scope, function() {
            var btns = [];

            btns.push({
                text:gettextCatalog.getString('Edit Page Info'),
                action: $scope.onItemEdit
            });

            btns.push({
                text:gettextCatalog.getString('Edit Page Photo'),
                action: function() {
                    $location.path('app/pages/edit-avatar/' + $scope.item.getId());
                    return true;
                }
            });

            btns.push({
                text:gettextCatalog.getString('Edit Page Cover'),
                action: function() {
                    $location.path('app/pages/edit-cover/' + $scope.item.getId());
                    return true;
                }
            });

            return btns;
        });

        return $scope;
    };
});
