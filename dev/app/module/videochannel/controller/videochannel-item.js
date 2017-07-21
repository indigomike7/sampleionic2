define([
    'global/base/ItemController'
],function(Ctrl){
    return function($scope, $http2, $site, $injector, gettext, gettextCatalog, $location, $modal){

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemSetting = $scope._setting($scope, function() {

            var btns = [];

            btns.push({
                text: gettextCatalog.getString('Share'),
                action: $scope.onItemShare
            });

            if($scope.item.canDelete()){
                btns.push({
                    text: gettextCatalog.getString('Delete this video'),
                    action: $scope.onItemDelete,
                    destructive: true
                });
            }

            if($scope.item.canEdit()){
                btns.push({
                    text: gettextCatalog.getString('Edit this video'),
                    action: $scope.onItemEdit,
                });
            }

            if (!$scope.item.isOwner()) {
                btns.push({
                    text: gettextCatalog.getString('Report this video'),
                    action: $scope.onItemReport
                });
            }

            return btns;
        });

        $scope.onItemDelete  = $scope._itemConfirm(
            gettextCatalog.getString('Do you want to delete this video?'),
            'videochannel/delete',
            function(){return {iVideoId: $scope.item.getId()};},
            function(data){
                $modal.toast(data.message);
                $scope.items.deleteItem($scope.item.getId());
                $scope.$emit('videochannel:deleted', $scope.item.getId());
            }
        );

        $scope.onItemEdit  = function(){
            $location.path('app/videochannel/edit/'  + $scope.item.iVideoId);
        };

        $scope.onItemRate = $scope._rate($scope, function() {
            return {
                sItemType: $scope.item.getType(),
                iItemId: $scope.item.getId(),
                iRating: 0
            };
        });
    };

});
