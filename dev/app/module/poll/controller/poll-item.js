define([
    'global/base/ItemController'
],function(Ctrl){

    return function($scope, $injector, gettext, gettextCatalog, $site, $http2, $modal, $location){

        $injector.invoke(Ctrl, this, {$scope: $scope});

        $scope.onItemDelete = $scope._itemConfirm(
            gettextCatalog.getString('Are you sure that you want to delete this poll?'),
            'poll/delete',
            function(){return {
                iPollId: $scope.item.getId()
            };},function(data){
                $scope.items.deleteItem($scope.item.getId());
            });

        $scope.onItemEdit = function(){
            $location.path('app/polls/edit/' + $scope.item.getId(),true);
        };

        $scope.onItemSetting = $scope._setting($scope, function(){

            var btns  = [];

            if(true){
                btns.push({
                    text: gettextCatalog.getString('Share'),
                    action: $scope.onItemShare
                });
            }

            if(!$scope.item.isOwner()){
                btns.push({
                    text: gettextCatalog.getString('Report'),
                    action: $scope.onItemReport
                });
            }

            if($scope.item.canEdit()) {
                btns.push({
                    text: gettextCatalog.getString('Edit Poll'),
                    action: $scope.onItemEdit
                });
            }

            if($scope.item.canDelete()) {
                btns.push({
                    text: gettextCatalog.getString('Delete Poll'),
                    destructive: true,
                    action: $scope.onItemDelete
                });
            }

            return btns;
        });

    };
});
